<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');
include_once(OPENBIZ_BIN.'../../siremis/modules/sipadmin/common/libs/SERUtils.php');

class XcapDO extends BizDataObj
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
			$docfields = array('doc','docdetail');
            while ($recArray = $this->_fetch_record($resultSet))
			{
				if(isset($recArray[$docfields[0]]))
				{
					$rlist = serDataToHtmlArray($recArray[$docfields[0]]); 
					$recArray[$docfields[0]] = $rlist[0];
					$recArray[$docfields[1]] = $rlist[1];
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
