<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');

class RlsPresentityDO extends BizDataObj
{
    var $fields = array('expires','updated');
    /**
     * Fetches SQL result rows as a sequential array according the query rules set before.
     *
     * @return array array of records
     */
    public function fetch()
    {
        $resultRecords = array();
        $resultSet = $this->_run_search($this->m_Limit);  // regular search or page search
        if ($resultSet !== null)
		{
            while ($recArray = $this->_fetch_record($resultSet))
			{
				$pstatefields = array('presence_state','presence_state_detail');
				if(isset($recArray[$pstatefields[0]]))
				{
					$tmp = $recArray[$pstatefields[0]];
					$tmp = str_replace("<", "&lt;", $tmp);
					$tmp = str_replace(">", "&gt;", $tmp);
					$recArray[$pstatefields[0]] = $tmp;
					$tmp = preg_replace('#([A-Z]+ sip:[^ ]+ SIP/2.0)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
					$tmp = preg_replace('#(SIP/2.0 [1-6][0-9][0-9] [^%]+)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
					$tmp = preg_replace('#%%([^ :%]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
					while($count>0)
						$tmp = preg_replace('#%%([^ :%<]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
					$recArray[$pstatefields[1]] = "<pre>" . $tmp . "</pre>";
				}
				foreach($this->fields as $datefield)
				{
					if(isset($recArray[$datefield]))
					{
						$time = $recArray[$datefield];
						$recArray[$datefield] = date('Y-m-d H:i:s',$time);
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
		foreach($this->fields as $field)
		{
			if(isset($recArr[$field]))
			{
				$datetime = explode(' ',$recArr[$field]);
				$date = explode('-',$datetime[0]);
				$time = explode(':',$datetime[1]);
				$recArr[$field] = mktime((int)$time[0],(int)$time[1],(int)$time[2],(int)$date[1],(int)$date[2],(int)$date[0]);
			}
		}
		return parent::updateRecord($recArr, $oldRecord);
	}
	
	public function insertRecord($recArr)
	{
		foreach($this->fields as $field)
		{
			if(isset($recArr[$field]))
			{
				$datetime = explode(' ',$recArr[$field]);
				$date = explode('-',$datetime[0]);
				$time = explode(':',$datetime[1]);
				$recArr[$field] = mktime((int)$time[0],(int)$time[1],(int)$time[2],(int)$date[1],(int)$date[2],(int)$date[0]);
			}
		}
		return parent::insertRecord($recArr);
	}
}
?>
