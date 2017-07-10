<?php

/**
 * Customized JsonRPC client class
 *
 * @author Frederic Guillot (Initial Auhtoor)
 * @license Unlicense http://unlicense.org/
 * Adapted from https://github.com/fguillot/JsonRPC
 */
class JsonRPCClient
{
	/**
	 * URL of the server
	 *
	 * @access private
	 * @var string
	 */
	private $url;

	/**
	 * HTTP client timeout
	 *
	 * @access private
	 * @var integer
	 */
	private $timeout;

	/**
	 * Username for authentication
	 *
	 * @access private
	 * @var string
	 */
	private $username;

	/**
	 * Password for authentication
	 *
	 * @access private
	 * @var string
	 */
	private $password;

	/**
	 * True for a batch request
	 *
	 * @access public
	 * @var boolean
	 */
	public $is_batch = false;

	/**
	 * Batch payload
	 *
	 * @access public
	 * @var array
	 */
	public $batch = array();

	/**
	 * Enable debug output to the php error log
	 *
	 * @access public
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * Default HTTP headers to send to the server
	 *
	 * @access private
	 * @var array
	 */
	private $headers = array(
		'Connection: close',
		'Content-Type: application/json',
		'Accept: application/json'
	);

	/**
	 * Body of http response
	 *
	 * @access public
	 * @var string
	 */
	public $http_body = '';

	/**
	 * Code of http response
	 *
	 * @access public
	 * @var int
	 */
	public $http_code = 0;

	/**
	 * Error message
	 *
	 * @access public
	 * @var string
	 */
	public $error_msg = '';

	/**
	 * Error code
	 *
	 * @access public
	 * @var int
	 */
	public $error_code = 0;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  string    $url         Server URL
	 * @param  integer   $timeout     Server URL
	 * @param  array     $headers     Custom HTTP headers
	 */
	public function __construct($url, $timeout = 5, $headers = array())
	{
		$this->url = $url;
		$this->timeout = $timeout;
		$this->headers = array_merge($this->headers, $headers);
	}

	/**
	 * Automatic mapping of procedures
	 *
	 * @access public
	 * @param  string   $method   Procedure name
	 * @param  array    $params   Procedure arguments
	 * @return mixed
	 */
	public function __call($method, array $params)
	{
		// Allow to pass an array and use named arguments
		if (count($params) === 1 && is_array($params[0])) {
			$params = $params[0];
		}

		return $this->execute($method, $params);
	}

	/**
	 * Set authentication parameters
	 *
	 * @access public
	 * @param  string   $username   Username
	 * @param  string   $password   Password
	 */
	public function authentication($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Start a batch request
	 *
	 * @access public
	 * @return Client
	 */
	public function batch()
	{
		$this->is_batch = true;
		$this->batch = array();

		return $this;
	}

	/**
	 * Send a batch request
	 *
	 * @access public
	 * @return array
	 */
	public function send()
	{
		$this->is_batch = false;

		return $this->parseResponse(
			$this->doRequest($this->batch)
		);
	}

	/**
	 * Execute a procedure
	 *
	 * @access public
	 * @param  string   $procedure   Procedure name
	 * @param  array    $params      Procedure arguments
	 * @return mixed
	 */
	public function execute($procedure, array $params = array())
	{
		if ($this->is_batch) {
			$this->batch[] = $this->prepareRequest($procedure, $params);
			return $this;
		}

		return $this->parseResponse(
			$this->doRequest($this->prepareRequest($procedure, $params))
		);
	}

	/**
	 * Prepare the payload
	 *
	 * @access public
	 * @param  string   $procedure   Procedure name
	 * @param  array    $params      Procedure arguments
	 * @return array
	 */
	public function prepareRequest($procedure, array $params = array())
	{
		$payload = array(
			'jsonrpc' => '2.0',
			'method' => $procedure,
			'id' => mt_rand()
		);

		if (! empty($params)) {
			$payload['params'] = $params;
		}

		return $payload;
	}

	/**
	 * Parse the response and return the procedure result
	 *
	 * @access public
	 * @param  array     $payload
	 * @return mixed
	 */
	public function parseResponse(array $payload)
	{
		if ($this->isBatchResponse($payload)) {

			$results = array();

			foreach ($payload as $response) {
				$results[] = $this->getResult($response);
			}

			return $results;
		}

		return $this->getResult($payload);
	}

	/**
	 * Return true if we have a batch response
	 *
	 * @access public
	 * @param  array    $payload
	 * @return boolean
	 */
	private function isBatchResponse(array $payload)
	{
		return array_keys($payload) === range(0, count($payload) - 1);
	}

	/**
	 * Get a RPC call result
	 *
	 * @access public
	 * @param  array    $payload
	 * @return mixed
	 */
	public function getResult(array $payload)
	{
		if (isset($payload['error']['code'])) {
			$this->handleRpcErrors($payload['error']['code']);
		}

		return isset($payload['result']) ? $payload['result'] : null;
	}

	/**
	 * Throw an exception according the RPC error
	 *
	 * @access public
	 * @param  integer    $code
	 */
	public function handleRpcErrors($code)
	{
		$this->error_code = $code;
		switch ($code) {
			case -32601:
				$this->error_msg = 'Procedure not found';
			break;
			case -32602:
				$this->error_msg = 'Invalid arguments';
			break;
			default:
				$this->error_msg = 'Invalid request/response';
			break;
		}
	}

	/**
	 * Do the HTTP request
	 *
	 * @access public
	 * @param  string   $payload   Data to send
	 */
	public function doRequest($payload)
	{
		$this->error_code = 0;
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, 'JSON-RPC PHP Client');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

		if ($this->username && $this->password) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
		}

		$this->http_body = curl_exec($ch);
		$this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($this->http_code === 401 || $this->http_code === 403) {
			$this->error_msg = 'Access denied';
			$this->error_code = 401;
			curl_close($ch);
			return array();
		}

		$response = json_decode($this->http_body, true);

		if ($this->debug) {
			error_log('==> Request: '.PHP_EOL.json_encode($payload, JSON_PRETTY_PRINT));
			error_log('==> Response: '.PHP_EOL.json_encode($response, JSON_PRETTY_PRINT));
		}

		curl_close($ch);

		return is_array($response) ? $response : array();
	}
}


/**
 * Class for JsonRPC over UDP/UnixSock client
 */

class JsonRPCUdpClient
{
	public $sock = false;
	public $ready = false;
	public $error_code = 0;
	public $error_msg = 'unknown';
	private $stype;
	private $spath = '';
	private $raddr;
	private $rport;
	private $cmd;
	public $http_body; /* match http jsonrcp class field */
	private $sto;
	private $uto;

	function __construct($stype='udp', $laddr='127.0.0.1', $lport=8044,
			$taddr='127.0.0.1', $tport=8033, $timeout='3.0')
	{
		if (!defined('BUFFER_SIZE')) {
			define('BUFFER_SIZE', 16384);
		}
		if(strpos($timeout, '.')) {
			$split = preg_split("/\./", $timeout);
			$this->sto = 0+$split[0];
			$this->uto = (0+$split[1])*100000;
		} else {
			$this->sto = $stimeout;
			$this->uto = 0;
		}
		$this->raddr = $taddr;
		$this->rport = $tport;
		$this->stype = $stype;
		if($this->stype=='udp') {
			// udp inet socket
			$this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			if($this->sock==false) {
				error_log ("socket create failed: " . socket_strerror(socket_last_error()));
				return;
			}
			if(socket_bind($this->sock, $laddr, $lport)==true) {
				$this->ready=true;
			} else {
				error_log ("socket bind failed: " . socket_strerror(socket_last_error($this->sock)));
			}
		} else {
			// unix socket file
			$this->spath = $laddr . "." . substr( md5(rand()), 0, 8);
			if (file_exists($this->spath)) {
				unlink($this->spath);
			}

			$this->sock = socket_create(AF_UNIX, SOCK_DGRAM, 0);
			if($this->sock==false) {
				error_log ("socket create failed: " . socket_strerror(socket_last_error()));
				return;
			}
			if(socket_bind($this->sock, $this->spath)==true) {
				$this->ready=true;
				if (file_exists($this->spath)) {
					chmod($this->spath, 0666);
				} else {
					error_log ("Unable to create JSONRPC client unix socket file: "
						. $this->spath);
				}
				// socket_connect($this->sock, $this->raddr);
			} else {
				error_log ("socket bind failed: " . socket_strerror(socket_last_error($this->sock)));
			}
		}
	}

	function JsonRPCUdpClient($stype='udp', $laddr='127.0.0.1', $lport=8044,
			$taddr='127.0.0.1', $tport=8033, $timeout='3.0')
	{
		self::__construct($stype, $laddr, $lport, $taddr, $tport, $timeout);
	}

	function sjr_close()
	{
		if($this->sock) {
			socket_close($this->sock);
			$this->sock = false;
		}
		if(strlen($this->spath)>0) {
			if (file_exists($this->spath)) {
				unlink($this->spath);
			}
		}
	}

	private function sjr_read()
	{
		unset($this->http_body);

		socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$this->sto, "usec"=>$this->uto));
		if($this->stype=='udp') {
			$ret = socket_recvfrom($this->sock, $rcvbuf, BUFFER_SIZE, 0,
						$faddr, $fport);
		} else {
			$ret = socket_recvfrom($this->sock, $rcvbuf, BUFFER_SIZE, 0,
						$faddr);
		}

		if($ret==false) {
			error_log($this->stype . " socket recv failed: " . socket_strerror(socket_last_error($this->sock)));
			return false;
		}

		$this->http_body = $rcvbuf;

		return true;
    }

	private function sjr_write($input)
	{
		$len = strlen($input);
		if($this->stype=='udp') {
			if(socket_sendto($this->sock, $input, $len, 0,
					$this->raddr, $this->rport)==false) {
				error_log("i socket send failed: " . socket_strerror(socket_last_error($this->sock)));
				return false;
			}
		} else {
			if(socket_sendto($this->sock, $input, $len, 0,
					$this->raddr)==false) {
				error_log("u socket send failed: " . socket_strerror(socket_last_error($this->sock)));
				return false;
			}
		}
		return true;
	}

	public function execute($vcmd, array $params = array())
	{
		if(!$this->ready) {
			$this->error_code = 500;
			$this->error_msg = "JSONRPC client not ready";
			return false;
		}
		$this->error_code = 0;
		/*
		{
			"jsonrpc": "2.0",
			"method": "cmd",
			"params": ["p1", p2, "p3"],
			"id": 1
		}
		*/
		$words = explode(" ", $vcmd);
		$udpbuf = "{\n  \"jsonrpc\": \"2.0\",\n  \"method\": \"";
		$udpbuf .= $vcmd . "\",\n";
		$c=count($params);
		if($c>0) {
			$udpbuf .= "  \"params\": [";
			for($i = 0; $i < $c; $i = $i + 1) {
				if(i>0) {
					$udpbuf .= ",";
				}
				if(is_string($params[$i])) {
					$udpbuf .= "\"" . $params[$i] . "\"";
				} else {
					$udpbuf .= $params[$i];
				}
			}
			$udpbuf .= "],\n";
		}
		$udpbuf .= "  \"id\": " . rand (1,10000) . "\n}\n";
		if($this->sjr_write($udpbuf)==false) {
			$this->error_code = 500;
			$this->error_msg = "JSONRPC client send failed";
			return false;
		}
		if($this->sjr_read()==false) {
			$this->error_code = 500;
			$this->error_msg = "JSONRPC client recv failed";
			return false;
		}
		return true;
    }
}

/**
 * Class for KAMAILIO JSONRPC
 */

class serjr
{
	public $client = false;
	public $ready = false;
	private $ctype = 'http';
	private $cmd;
	private $timeout;
	private $result;
	private $jmsg;

	function __construct($stype='udp', $laddr='127.0.0.1', $lport=8044,
			$taddr='127.0.0.1', $tport=8033, $timeout='3.0')
	{
		if (!defined('BUFFER_SIZE')) {
			define('BUFFER_SIZE', 8192);
		}
		$this->timeout = $timeout;
		$this->ctype = $stype;
		if($this->ctype == 'http') {
			$this->client = new JsonRPCClient($taddr, $timeout);
		} else {
			$this->client = new JsonRPCUdpClient($stype, $laddr, $lport,
							$taddr, $tport, $timeout);
		}

		if($this->client) {
			$this->ready=true;
		}
	}

	function serjr($stype='udp', $laddr='127.0.0.1', $lport=8044,
			$taddr='127.0.0.1', $tport=8033, $timeout="3.0")
	{
		self::__construct($stype, $laddr, $lport, $taddr, $tport, $timeout);
	}

	function sjr_close()
	{
		if($this->client) {
			if($this->ctype == 'unixsock') {
				$this->client->sjr_close();
			}
			$this->client = false;
			$this->ready = false;
		}
	}

	public function sjr_command($vcmd)
	{
		if(!$this->ready)
			return false;
		unset($this->result);
		unset($this->jmsg);

		$words = explode(" ", $vcmd);
		$this->cmd = $words[0];
		$c=count($words);
		$jparams = array();
		$jtype = 'string';
		$jidx = 0;
		for($i = 1; $i < $c; $i = $i + 1) {
			switch($words[$i]) {
				case '-i':
					$jtype = 'int';
				break;
				case '-s':
					$jtype = 'string';
				break;
				default:
					if($jtype == 'string') {
						$jparams[$jidx] = "" . $words[$i];
					} else {
						$jparams[$jidx] = intval($words[$i]);
					}
					$jidx++;
					$xtype = 'string';
			}
		}
		$this->result = $this->client->execute($this->cmd, $jparams);
		if($this->client->error_code != 0)
			return false;
		return true;
    }

	function toPlainStr()
	{
		$output = "";
		if (!empty($this->result)) {
			if ($this->client->error_code == 0) {
				$output .= $this->client->http_body . "\n";
			} else {
				$output .= "Fault Code: " . $this->client->error_code . "\n";
				$output .= "Fault Reason: " . $this->client->error_msg . "\n";
			}
		} else {
			$output .= "[[NO CONTENT]]\n";
		}
		return $output;
	}

	function printText()
	{
		if (!empty($this->result)) {
			if ($this->client->error_code == 0) {
				printf("%s\n", $this->client->http_body);
			} else {
				echo 'Fault Code: ' . $this->client->error_code . "\n";
				echo 'Fault Reason: ' . $this->client->error_msg . "\n";
			}
		} else {
			printf("[[NO CONTENT]]\n");
		}
	}

	function richSafe($strText)
	{
		//returns safe code for preloading in the RTE
		$tmpString = $strText;

		//convert all types of single quotes
		$tmpString = str_replace(chr(145), chr(39), $tmpString);
		$tmpString = str_replace(chr(146), chr(39), $tmpString);
		$tmpString = str_replace("'", "&#39;", $tmpString);

		//convert all types of double quotes
		$tmpString = str_replace(chr(147), chr(34), $tmpString);
		$tmpString = str_replace(chr(148), chr(34), $tmpString);

		//replace < and >
		$tmpString = str_replace("<", "&#60;", $tmpString);
		$tmpString = str_replace(">", "&#62;", $tmpString);

		//replace carriage returns & line feeds
		$tmpString = str_replace(chr(10), "<br/>", $tmpString);
		$tmpString = str_replace(chr(13), " ", $tmpString);

		return $tmpString;
	}

	function toRichStr()
	{
		$output = "";
		if (!empty($this->result)) {
			$output .= "<span style=\"color:#663333;font-family:Arial;font-size:12px;\">";
			if ($this->client->error_code == 0) {
				switch($this->cmd) {
					case "system.listMethods":
					case "mi":
					default:
						$output .= "<pre>";
						$output .= $this->richSafe(json_encode(json_decode($this->client->http_body), JSON_PRETTY_PRINT));
						$output .= "</pre>";
				}
			} else {
				$output .= "Fault Code: " . $this->client->error_code . "<br/>";
				$output .= "Fault Reason: " . $this->richSafe($this->client->error_msg) . "<br/>";
			}
			$output .= "</span>";
		} else {
			$output .= "<b>[[NO CONTENT]]</b><br/>";
		}
		return $output;
	}
}
