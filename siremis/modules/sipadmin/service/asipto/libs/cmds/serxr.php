<?php
require_once('XML/RPC.php');

/**
 *	This class overides method parseResponseFile() from class XML_RPC_Message
 *	
 *	- support to read even server does not close connection
 */
class XML_RPC_Message2 extends XML_RPC_Message {
	public $responseHeaders2;
	public $responseBody2;

    function parseResponseFile($fp) {
		$continue_reading = true;
		$read_body = false;
		$content_length = null;
		$last_line = "";
		$received = 0;
		$max_recv_len = 8192;
		$recv_len = $max_recv_len;
        $ipd = '';

        while ($continue_reading) {
        	if (false === ($data = @fread($fp, $recv_len))) break;
        	if (! strlen($data)) break;
            $ipd .= $data;
			if ($read_body) {
				$received += strlen($data);
			} else {
				$data = $last_line.$data;
				$lines = explode("\n", $data);
				$last_line = $lines[count($lines)-1];
				unset ($lines[count($lines)-1]);
				foreach($lines as $k => $v) {
					if (trim($v) == "") {
						$read_body = true;
						continue;
					}
					if($read_body) {
						$this->responseBody2[] = $v;
						$received += strlen($v);
						$received++;
						continue;
					}
					$this->responseHeaders2[] = trim($v);
					if (preg_match('/^Content-Length:(.*)$/i', $v, $regs)) {
						$regs[1] = trim($regs[1]);
						if (!is_numeric($regs[1])) continue;
						
						$content_length = $regs[1];
					}
				}
				if($read_body) {
					$received += strlen($last_line);
				}
			}
			if (!is_null($content_length) and ($received >= $content_length)) {
				$continue_reading = false;
			}
			if (!is_null($content_length) and ($content_length - $received < $max_recv_len)) {
				$recv_len = $content_length - $received;
			}
        }
        return $this->parseResponse($ipd);
    }
}


/**
 * Class for SER XMLRPC
 */

class serxr {
	public $client = false;
	public $ready = false;
	private $cmd;
	private $timeout;
	private $result;
	private $xmsg;

	function serxr($path='/', $addr='127.0.0.1', $port=8082, $timeout=3)
   	{
		if (!defined('BUFFER_SIZE')) {
			define('BUFFER_SIZE', 8192);
		}
		$this->timeout = $timeout;
		$this->client = new XML_RPC_Client($path, $addr, $port);
		if($this->client) {
			$this->ready=true;
		}
	}

	function sxr_close()
	{
		if($this->client) {
			$this->client = false;
			$this->ready = false;
		}
	}

	public function sxr_command($vcmd) {
		if(!$this->ready)
			return false;
		unset($this->result);
		unset($this->xmsg);

		$words = explode(" ", $vcmd);
		$udpbuf = ":".$words[0].":\n";
		$this->cmd = $words[0];
		$c=count($words);
		$xparams = array();
		$xtype = 'string';
		for($i = 1; $i < $c; $i = $i + 1) {
			switch($words[$i]) {
				case '-i':
					$xtype = 'int';
				break;
				case '-s':
					$xtype = 'string';
				break;
				default:
					$xparams[] = new XML_RPC_Value($words[$i], $xtype);
					$xtype = 'string';
			}
		}
		$this->xmsg = new XML_RPC_Message2($this->cmd, $xparams);
		if($this->xmsg==false)
			return false;
		$this->result = $this->client->send($this->xmsg, $this->timeout);
		if($this->result==false)
			return false;
		return true;
    }

    function toPlainStr() {
		$output = "";
		if (isset($this->result)) {
			if (!$this->result->faultCode()) {
				$output .= $this->result->serialize() . "\n";
			} else {
				$output .= "Fault Code: " . $this->result->faultCode() . "\n";
				$output .= "Fault Reason: " . $this->result->faultString() . "\n";
			}
		} else {
			$output .= "[[NO CONTENT]]\n";
		}
		return $output;
	}

    function printText() {
		if (isset($this->result)) {
			if (!$this->result->faultCode()) {
				printf("%s\n", $this->result->serialize());
			} else {
				echo 'Fault Code: ' . $this->result->faultCode() . "\n";
				echo 'Fault Reason: ' . $this->result->faultString() . "\n";
			}
		} else {
			printf("[[NO CONTENT]]\n");
		}
	}

	function richSafe($strText) {
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

	function toRichStr() {
		$output = "";
		if (isset($this->result)) {
			$output .= "<span style=\"color:#663333;font-family:Arial;font-size:12px;\">";
			if (!$this->result->faultCode()) {
				switch($this->cmd) {
					case "system.listMethods":
						$data = XML_RPC_decode($this->result->value());
						foreach ($data as $key=>$val) {
							$output .= "<b>".$this->richSafe($val)."</b><br/>";
						}
					break;
					case "mi":
						foreach ($this->xmsg->responseBody2 as $key=>$val) {
							$val = trim($val);
							if(substr($val, 0, 15)=="<value><string>") {
								$output .= substr($val, 15, -14) . "<br/>";
							}
						}
					break;
					default:
						$output .= "<pre>";
						$output .= $this->richSafe($this->result->serialize());
						$output .= "</pre>";
				}
			} else {
				$output .= "Fault Code: " . $this->result->faultCode() . "<br/>";
				$output .= "Fault Reason: " . $this->richSafe($this->result->faultString()) . "<br/>";
			}
			$output .= "</span>";
		} else {
			$output .= "<b>[[NO CONTENT]]</b><br/>";
		}
		return $output;
	}

}
?>
