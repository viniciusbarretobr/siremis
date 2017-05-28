<?php
/**
 * Class for SER MI Socket
 */

class sermi {
	public $sock = false;
	public $ready = false;
	private $raddr;
	private $rport;
	private $cmd;
	private $hdrs;
	private $body;
	private $sto;
	private $uto;

	function sermi($laddr='127.0.0.1', $lport=8044,
			$timeout="3.0", $taddr='127.0.0.1', $tport=8033)
   	{
		if (!defined('BUFFER_SIZE')) {
			define('BUFFER_SIZE', 8192);
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
		$this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if(socket_bind($this->sock, $laddr, $lport)) {
			$this->ready=true;
		}
	}

	function smi_close()
	{
		if($this->sock) {
			socket_close($this->sock);
			$this->sock = false;
		}
	}

    private function smi_read() {
		unset($this->hdrs);
		unset($this->body);

		socket_set_option($this->sock,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$this->sto, "usec"=>$this->uto));
		$ret = @socket_recvfrom($this->sock, $rcvbuf, BUFFER_SIZE, 0, $faddr, $fport);

		if($ret<0) {
			return false;
		}

		$lines = explode("\n", $rcvbuf);
		$this->hdrs[] = $lines[0];
		for ($i=1; $i<count($lines); $i++) {
			$this->body[] = $lines[$i];
		}
		return true;
    }

	private function smi_write($input) {
		$len = strlen($input);
		if(socket_sendto($this->sock, $input, $len, 0,
				$this->raddr, $this->rport)==false)
			return false;
		return true;
	}

	public function smi_command($vcmd) {
		if(!$this->ready)
			return false;
		$words = explode(" ", $vcmd);
		$udpbuf = ":".$words[0].":\n";
		$this->cmd = $words[0];
		$c=count($words);
		for($i = 1; $i < $c; $i = $i + 1)
			$udpbuf .= $words[$i]."\n";
		if($this->smi_write($udpbuf)==false)
			return false;
		if($this->smi_read()==false)
			return false;
		return true;
    }

    function printText() {
		if (isset($this->hdrs)) {
			foreach ($this->hdrs as $key=>$val) {
				printf("%s\n", $val);
			}
			if (isset($this->body)) {
				printf("\n");
				foreach ($this->body as $key=>$val) {
					printf("%s\n", $val);
				}
			}
		} else {
			printf("[[NO CONTENT]]\n");
		}
	}

    function toPlainStr() {
		$output = "";
		if (isset($this->hdrs)) {
			foreach ($this->hdrs as $key=>$val) {
				$output .= $val."\n";
			}
			if (isset($this->body)) {
				$output .= "\n";
				foreach ($this->body as $key=>$val) {
					$output .= $val."\n";
				}
			}
		} else {
			$output .= "[[NO CONTENT]]\n";
		}
		return $output;
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

		//replace carriage returns & line feeds
		$tmpString = str_replace(chr(10), " ", $tmpString);
		$tmpString = str_replace(chr(13), " ", $tmpString);

		//replace < and >
		$tmpString = str_replace("<", "&#60;", $tmpString);
		$tmpString = str_replace(">", "&#62;", $tmpString);

		return $tmpString;
	}

	function bodyToRichStr() {
		$output = "";
		$output .= "<br/>";
		$output .= "<span style=\"color:#000066\">";
		switch($this->cmd) {
			case "which":
				$line = 0;
				foreach ($this->body as $key=>$val) {
					$output .= "<span style=\"color:#009933\">";
					$output .= $this->richSafe($val)."<br/>";
					$output .= "</span>";
				}
			break;
			default:
				foreach ($this->body as $key=>$val) {
					$output .= $this->richSafe($val)."<br/>";
				}
		}
		$output .= "</span>";
		return $output;
	}
	function toRichStr() {
		$output = "";
		if (isset($this->hdrs)) {
			$output .= "<span style=\"color:#663333;font-family:Arial;font-size:12px;\">";
			foreach ($this->hdrs as $key=>$val) {
				$output .= "<b>".$this->richSafe($val)."</b><br/>";
			}
			if (isset($this->body)) {
				$output .= $this->bodyToRichStr();
			}
			$output .= "</span>";
		} else {
			$output .= "<b>[[NO CONTENT]]</b><br/>";
		}
		return $output;
	}
}
?>
