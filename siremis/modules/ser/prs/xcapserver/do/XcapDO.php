<?PHP
include_once(OPENBIZ_BIN.'data/BizDataObj.php');

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
			$field = 'doc';
            while ($recArray = $this->_fetch_record($resultSet))
			{
				if(isset($recArray[$field]))
				{
					$tmp = $recArray[$field];
					$tmp = str_replace("\r\n", "%%EOL%%", $tmp);
					$tmp = str_replace("<", "&amp;lt;", $tmp);
					$tmp = str_replace(">", "&amp;gt;", $tmp);
					$tmp = preg_replace('#([A-Z]+ sip:[^ ]+ SIP/2.0)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
					$tmp = preg_replace('#(SIP/2.0 [1-6][0-9][0-9] [^%]+)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
					// echo "------ [[$tmp]]";
					$tmp = preg_replace('#%%([^ :%]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
					while($count>0)
						$tmp = preg_replace('#%%([^ :%<]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
					$tmp = str_replace("%%EOL%%", "<br />", $tmp);
					$recArray[$field] = $tmp;
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
