<?php 
/**
 * Openbiz Cubi 
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   system.form
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */


/**
 * UserForm class - implement the login of login form
 *
 * @package system.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class EventLogForm extends EasyForm
{
	public function fetchDataSet()
    {
		$resultRecords = parent::fetchDataSet();
		for($i=0;$i<count($resultRecords);$i++){			
			$resultRecords[$i]['event'] = $this->getMessage($resultRecords[$i]['event']);
			$resultRecords[$i]['message'] = $this->getMessage($resultRecords[$i]['message'],unserialize($resultRecords[$i]['comment']));			 
		}
 		return $resultRecords;
    }

    public function fetchData()
    {
    	$resultRecord = parent::fetchData();
    	$resultRecord['event'] = $this->getMessage($resultRecord['event']);
		$resultRecord['message'] = $this->getMessage($resultRecord['message'],unserialize($resultRecord['comment']));
 		return $resultRecord;
    }    
    public function ExportCSV()
    {
		$eventlogSvc = BizSystem::getService(EVENTLOG_SERVICE);	
		$eventlogSvc->ExportCSV();
		$this->runEventLog();
		return true;    	
    }
    
    public function ClearLog()	
	{
       if ($this->m_Resource != "" && !$this->allowAccess($this->m_Resource.".delete"))
            return BizSystem::clientProxy()->redirectView(ACCESS_DENIED_VIEW);

        try
        {
          $this->getDataObj()->deleteRecords();
        } 
        catch (BDOException $e)
        {
           $this->processBDOException($e);
           return;
        }
       
        if ($this->m_FormType == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
		return true;
	}   
	

}  
?>