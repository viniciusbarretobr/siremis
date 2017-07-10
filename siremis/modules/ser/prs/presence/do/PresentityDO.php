<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');
include_once(OPENBIZ_BIN.'../../siremis/modules/sipadmin/common/libs/SERUtils.php');

class PresentityDO extends BizDataObj
{
    var $fields = array('expires','received_time');
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
				$bodyfields = array('body','body_detail');
				if(isset($recArray[$bodyfields[0]]))
				{
					$rlist = serDataToHtmlArray($recArray[$bodyfields[0]]); 
					$recArray[$bodyfields[0]] = $rlist[0];
					$recArray[$bodyfields[1]] = $rlist[1];
				}
				foreach($this->fields as $field)
				{
					if(isset($recArray[$field]))
					{
						$time = $recArray[$field];
						$recArray[$field] = date('Y-m-d H:i:s',$time);
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
