<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');

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
					$tmp = $recArray[$msgfields[0]];
					$tmp = str_replace("<", "&lt;", $tmp);
					$tmp = str_replace(">", "&gt;", $tmp);
					$recArray[$msgfields[0]] = $tmp;
					$tmp = preg_replace('#([A-Z]+ sip:[^ ]+ SIP/2.0)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
					$tmp = preg_replace('#(SIP/2.0 [1-6][0-9][0-9] [^%]+)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
					$tmp = preg_replace('#%%([^ :%]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
					while($count>0)
						$tmp = preg_replace('#%%([^ :%<]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
					$recArray[$msgfields[1]] = "<pre>" . $tmp . "</pre>";
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
