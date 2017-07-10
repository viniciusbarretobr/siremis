<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');

class WatchersDO extends BizDataObj
{
    var $fields = array('inserted_time');

    public function fetch()
    {
        $resultRecords = array();
        $resultSet = $this->_run_search($this->m_Limit);  // regular search or page search
        if ($resultSet !== null)
		{
            while ($recArray = $this->_fetch_record($resultSet))
			{
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
