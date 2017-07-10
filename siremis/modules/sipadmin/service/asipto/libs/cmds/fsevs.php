<?php
/**
 * Class for FreeSWITCH Event Socket
 */

class fsevs {
	public $sock = false;
	public $ready = false;
	private $cmd;
	private $hdrs;
	private $body;
	private $sto;
	private $mto;

	function fsevs($addr='127.0.0.1', $port=8021, $passwd='ClueCon',
			$timeout=10, $stimeout=0.5)
   	{
		if (!defined('BUFFER_SIZE')) {
			define('BUFFER_SIZE', 4096);
		}
		if(strpos($stimeout, '.')) {
			$split = preg_split("/\./", $stimeout);
			$this->sto = 0+$split[0];
			$this->mto = (0+$split[1])*100000;
		} else {
			$this->sto = $stimeout;
			$this->mto = 0;
		}
		$this->evs_connect($addr, $port, $timeout+0);
		$this->evs_read();
		if (isset($this->hdrs) && $this->hdrs['Content-Type']=='auth/request') {
			$this->evs_auth($passwd);
		}
	}

	function evs_close()
	{
		if($this->sock) {
			$this->evs_command('exit');
			fclose($this->sock);
			$this->sock = false;
		}
	}

	function evs_connect($addr, $port, $timeout=10)
	{
		$this->sock = fsockopen($addr, $port, $errno, $errstr, $timeout);
        if (!$this->sock) {
			echo("Error Creating Socket: $errno/$errstr\n");
            return false;
        }

		stream_set_timeout($this->sock, $this->sto, $this->mto);
		return true;      		
	}

	function evs_auth($passwd)
	{
		if($this->sto<1)
			stream_set_timeout($this->sock, 1);
		$this->evs_command("auth $passwd");
		stream_set_timeout($this->sock, $this->sto, $this->mto);
		if (isset($this->hdrs) && array_key_exists('Reply-Text', $this->hdrs)
				&& preg_match('/^\+?OK/', $this->hdrs['Reply-Text']))
		{
            $this->ready = true;
        } else {
            $this->ready = false;
        }
    }

    private function evs_write($input) {
        fputs($this->sock, $input);
    }

    private function evs_read() {
		unset($this->hdrs);
		unset($this->body);
		$orig_line = fgets($this->sock, BUFFER_SIZE);
		if(!orig_line)
			return false;
		do {
            $trim_line = trim($orig_line);
            if (strlen($trim_line) > 0) {
                if (strpos($trim_line, ':')!=false) {
                    $split = preg_split('/:/', $trim_line);
                    $this->hdrs[trim($split[0])] = trim($split[1]);
                }
			} else {
				/* empty line -- body? */
				if (isset($this->hdrs)
						&& array_key_exists('Content-Length', $this->hdrs)) {
					$this->evs_read_body($this->hdrs['Content-Length']);
					break;
				} else {
					break;
				}
			}
			$orig_line = fgets($this->sock, BUFFER_SIZE);
		} while ($orig_line);
        return true;
    }

    private function evs_read_body($size) {
        $len = 0;
        $content = null;
		while ($orig_line = fgets($this->sock, BUFFER_SIZE)) {
            $len += strlen($orig_line);
			$trim_line = trim($orig_line);
			if(strlen($trim_line)>0) {
				$this->body[] = urldecode($trim_line);
			}
            if ($len >= $size) {
                break;
            }
        }
        return $len;
    }

    public function evs_command($vcmd) {
        $cmd_split = preg_split('/ /', $vcmd);
		$this->cmd = $cmd_split[0];
		if($this->cmd=='api' || $this->cmd=='bgapi')
			$this->cmd = $cmd_split[1];
		if ($this->ready!= true && $this->cmd != 'auth'
				&& $this->cmd != 'exit') {
			unset($this->cmd);
            return false;
        }
        $this->evs_write("$vcmd\r\n\r\n", $this->sock);
        if ($this->cmd != 'exit') {
            $this->evs_read($sock);
        } else {
            unset($this->hdrs);
            unset($this->body);
        }
        return true;
    }

	function evs_api_exec($cmd_str) {
		$reply = $this->evs_command("api $cmd_str");
		$try = 0;
		while ($reply==true && $try<5) {
			if(isset($this->hdrs)
					&& $this->hdrs['Content-Type'] == 'api/response') {
				return true;
			}

			$try++;
            $reply = $this->evs_read();
        }
        return false;
    }

    function toPlainStr() {
		$output = "";
		if (isset($this->hdrs)) {
			foreach ($this->hdrs as $key=>$val) {
				$output .= "* " . $key . ": " . $val ."\n";
			}
			if (isset($this->body)) {
				$output .= "\n";
				foreach ($this->body as $key=>$val) {
					$output .= "+ " . $val . "\n";
				}
			}
		} else {
			$output .= "[[NO CONTENT]]\n";
		}
		return $output;
	}

    function printText() {
		if (isset($this->hdrs)) {
			foreach ($this->hdrs as $key=>$val) {
				printf("* %s: %s\n", $key, $val);
			}
			if (isset($this->body)) {
				printf("\n");
				foreach ($this->body as $key=>$val) {
					printf("+ %s\n", $val);
				}
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
			case "help":
				$line = 0;
				foreach ($this->body as $key=>$val) {
					if($line==0) {
						$output .= "<span style=\"color:#009933\">";
						$output .= $this->richSafe($val)."<br/><br/>";
						$output .= "</span>";
					} else {
						$split = preg_split('/,/', $val);
						$output .= "&nbsp;&nbsp;<b>".$this->richSafe($split[0])."</b><br/>";
						$output .= "&nbsp;&nbsp;&nbsp;&nbsp;".$this->richSafe($split[1])."<br/>";
						$output .= "&nbsp;&nbsp;&nbsp;&nbsp;".$this->richSafe($split[2])."<br/>";
						$output .= "&nbsp;&nbsp;&nbsp;&nbsp;".$this->richSafe($split[3])."<br/>";
						$output .= "<br/>";
					}
					$line++;
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
				$output .= "<b>".$key."</b>: <i>".$this->richSafe($val)
							."</i><br/>";
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
