<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');

class DispatcherDO extends BizDataObj
{
	/**
	 * Fetches SQL result rows and replaces flags with relevent text description.
	 * 
	 * @return array of the records
	 */
	 
	private function addFlag(&$recArray,$label,$value){
		if (isset($recArray['flags_text_label']))
		{
			$recArray['flags_text_label'] .= ', ';
		} else {
			$recArray['flags_text_label'] = '';
		}
		$recArray['flags_text_label'] .= $label;
		
		if (isset($recArray['flags']))
		{
			$recArray['flags'] .= ', ';
		} else {
			$recArray['flags'] = '';
		}
		$recArray['flags'] .= $value;
	}
	
	public function fetch()
	{
		$resultRecords = array();
		$resultSet	= $this->_run_search($this->m_Limit);
		if ($resultSet !== null)
		{
			$field = 'flags';
			while ($recArray = $this->_fetch_record($resultSet))
			{
				if (isset($recArray[$field]))
				{
					$flags = decbin($recArray[$field]);
					
					// Make a mask for bitwise AND
					$sflagsMask = '';		// Settable Flags
					$oflagsMask = '';		// Other Flags
					
					for ($i = 0 ; $i < strlen($flags)-2 ; $i++)
					{
						$sflagsMask .= '0';
						$oflagsMask .= '1';
					}
					
					$sflagsMask .= '11';
					$oflagsMask .= '00';
					
					$sflags = $sflagsMask & $flags;
					$oflags = $oflagsMask & $flags;					
					
					$recArray['flags_other'] = $oflags;
					$recArray['flags'] = "";
					// Set flags
					for ($i = 0 ; $i < strlen($sflags) ; $i++)
					{
						$rindex = strlen($sflags) - $i;
						if ($rindex == 2 && $sflags[$i] === '1')
						{
							$this->addFlag($recArray,"Probing","2");
						}
						
						if ($rindex == 1 && $sflags[$i] === '1')
						{
							$this->addFlag($recArray,"Inactive","1");
						}
					}
					if (!isset($recArray['flags_text_label']))
					{
						$recArray['flags_text_label'] .= '-';
					}
				}
				
				if (isset($recArray['attrs'])) 
				{
					$attrs = explode(';', $recArray['attrs']);
					if(count($attrs) > 1){
						for ( $i = 0 ; $i < count($attrs) ; $i++){
							$recArray['attrs_other'] = '';
							$attr = explode('=',$attrs[$i]);
							if ($attr[0] == "weight")
							{
								$recArray['attrs'] = $attrs[$i];
							}
							else
							{
								$recArray['attrs_other'] .= $attrs[$i];
							}
						}
					}else{
						$attr = explode('=',$recArray['attrs']);
						if($attr[0] != 'weight'){
							$recArray['attrs_other'] = $recArray['attrs'];
							unset($recArray['attrs']);
						}
					}
				}
				$resultRecords[] = $recArray;
			}
		}
		else
			return null;
			
		return $resultRecords;
	}
	
	public function updateRecord($recArr, $oldRecord=null)
	{
		$oflags = $recArr['flags_other'];

		if ($recArr['flags'][0] === ","){
			$recArr['flags'] = "00";
		}

		$inactive = "0";
		$probing = "0";
		if(strlen($recArr['flags'])>0){
			$sflagsArr = explode(",",$recArr['flags']);
			$inactive = (array_search("1",$sflagsArr) !== FALSE) ? "1" : "0";
			$probing = (array_search("2",$sflagsArr) !== FALSE) ? "1" : "0";
		} 
		
		$sflags = $probing . $inactive;
		
		$recArr['flags'] = (string)(bindec($oflags) | bindec($sflags));
		
		$attributes = explode(',',$recArr['attrs']);
		
		if(strlen($attributes[0]) == 0){
			$recArr['attrs'] = '';
		}else{
			if(strlen($attributes[1]) > 0){
				$recArr['attrs'] = str_replace(',','=',$recArr['attrs']);
				$recArr['attrs'].= ';';
			}else{
				$recArr['attrs'] = $attributes[0] . '=0;';
			}
		}
		$recArr['attrs'] .= $recArr['attrs_other'];

		if(substr($recArr['attrs'],-1) == ';'){
			$recArr['attrs'] = substr($recArr['attrs'],0,-1);
		}
		
		return parent::updateRecord($recArr, $oldRecord);
	}
	
	public function insertRecord($recArr)
	{
		$oflags = $recArr['flags_other'];

		if ($recArr['flags'][0] == ","){
			$recArr['flags'] = "00";
		}

		$inactive = "0";
		$probing = "0";
		if(strlen($recArr['flags'])>0){
			$sflagsArr = explode(",",$recArr['flags']);
			$inactive = (array_search("1",$sflagsArr) !== FALSE) ? "1" : "0";
			$probing = (array_search("2",$sflagsArr) !== FALSE) ? "1" : "0";
		} 
		
		$sflags = $probing . $inactive;
		
		$recArr['flags'] = (string)(bindec($oflags) | bindec($sflags));
		
		$attributes = explode(',',$recArr['attrs']);
		
		if(strlen($attributes[0]) == 0){
			$recArr['attrs'] = '';
		}else{
			if(strlen($attributes[1]) > 0){
				$recArr['attrs'] = str_replace(',','=',$recArr['attrs']);
				$recArr['attrs'].= ';';
			}else{
				$recArr['attrs'] = $attributes[0] . '=0;';
			}
		}
		$recArr['attrs'] .= $recArr['attrs_other'];

		if(substr($recArr['attrs'],-1) == ';'){
			$recArr['attrs'] = substr($recArr['attrs'],0,-1);
		}
		
		return parent::insertRecord($recArr);
	}
}

?>
