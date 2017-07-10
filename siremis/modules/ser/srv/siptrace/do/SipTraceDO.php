<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');
include_once(OPENBIZ_BIN.'../../siremis/modules/sipadmin/common/libs/SERUtils.php');

class SipTraceDO extends BizDataObj
{
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
				$msgfields = array('msg','msg_detail');
				if(isset($recArray[$msgfields[0]]))
				{
					$rlist = serDataToHtmlArray($recArray[$msgfields[0]]); 
					$recArray[$msgfields[0]] = $rlist[0];
					$recArray[$msgfields[1]] = $rlist[1];

				}
                $resultRecords[] = $recArray;
            }
        }
        else
            return null;

        return $resultRecords;
    }
}
?>
