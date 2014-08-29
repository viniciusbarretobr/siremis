<?PHP
/**
 * BizForm class - BizForm is the base class that contains UI controls.
 * BizForm is a html form that is included in a BizView which is a html page.
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
include_once (OPENBIZ_BIN . "ui/BizForm_Abstract.php");
class BizForm extends BizForm_Abstract
{
    protected $m_CurrentPage = 1;
    protected $m_TotalPages = 0;
    protected $m_TotalRecords = 0;
    protected $m_OnSortField;
    protected $m_OnSortFlag;
    protected $m_RecordId = null;
    protected $m_ActiveRecord = null;
    protected $m_HistoryInfo;
    protected $m_NoHistoryInfo = false;
    protected $m_HistRecordId = null;
    protected $m_QueryONRender = true;
    protected $m_ClearSearchRule = false;
    protected $m_ReRenderOn = true;
    protected $m_PrtCommitPending = false;

    /**
     * BizForm::GetSessionContext() - Retrieve Session data of this object
     *
     * @param SessionContext $sessCtxt
     * @return void
     */
    //todo: pack session vars into a single array
    public function getSessionVars ($sessCtxt)
    {
        if ($this->m_Stateless == "Y")
            return;

        //If current request is a page reload, do not get the previous display mode
        //This means all page reloads revert back to the default display mode. (Usually READ)
        if (!$_GET['view'] AND !$_REQUEST['view'])
            $sessCtxt->getObjVar($this->m_Name, "Mode", $mode);

        $sessCtxt->getObjVar($this->m_Name, "SubForms", $this->m_SubForms);
        $sessCtxt->getObjVar($this->m_Name, "ParentFormName", $this->m_ParentFormName);
        $sessCtxt->getObjVar($this->m_Name, "PrtCommitPending", $this->m_PrtCommitPending);
        $sessCtxt->getObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
        $sessCtxt->getObjVar($this->m_Name, "OnSortField", $this->m_OnSortField);
        $sessCtxt->getObjVar($this->m_Name, "OnSortFlag", $this->m_OnSortFlag);
        $sessCtxt->getObjVar($this->m_Name, "RecordId", $this->m_RecordId);
        //$sessCtxt->getObjVar($this->m_Name, "ActiveRecord", $this->m_ActiveRecord);
        $sessCtxt->getObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
        $sessCtxt->getObjVar($this->m_Name, "TotalRecords", $this->m_TotalRecords);
        if ($this->m_CurrentPage == 0)
            $this->m_CurrentPage = 1;
        if ($this->m_Range > 0)
            $this->m_TotalPages = (int) ceil($this->m_TotalRecords / $this->m_Range);
        $this->SetDisplayMode($mode);
        if ($this->m_ActiveRecord)
            $this->m_RecordRow->setRecordArr($this->m_ActiveRecord);
        $this->SetSortFieldFlag($this->m_OnSortField, $this->m_OnSortFlag);
    }
    /**
     * BizForm::SetSessionContext() - Save Session data of this object
     *
     * @param SessionContext $sessCtxt
     * @return void
     */
    public function setSessionVars ($sessCtxt)
    {
        if ($this->m_Stateless == "Y")
            return;

        $sessCtxt->setObjVar($this->m_Name, "Mode", $this->m_Mode);

        $sessCtxt->setObjVar($this->m_Name, "SubForms", $this->m_SubForms);
        $sessCtxt->setObjVar($this->m_Name, "ParentFormName", $this->m_ParentFormName);
        $sessCtxt->setObjVar($this->m_Name, "PrtCommitPending", $this->m_PrtCommitPending);
        $sessCtxt->setObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
        $sessCtxt->setObjVar($this->m_Name, "OnSortField", $this->m_OnSortField);
        $sessCtxt->setObjVar($this->m_Name, "OnSortFlag", $this->m_OnSortFlag);
        $sessCtxt->setObjVar($this->m_Name, "RecordId", $this->m_RecordId);
        //$sessCtxt->setObjVar($this->m_Name, "ActiveRecord", $this->m_ActiveRecord);
        $sessCtxt->setObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
        $sessCtxt->setObjVar($this->m_Name, "TotalRecords", $this->m_TotalRecords);
        // before release object, set the view history
        $sessCtxt->setViewHistory($this->m_Name, $this->GetHistoryInfo());
    }
    /**
     * Clear session data
     */
    public function ClearSessionVars($sessCtxt)
    {
        $this->m_NoHistoryInfo = true;
    }
    /**
     * BizForm::GetHistoryInfo() - get history info array
     *
     * @return array
     */
    public function GetHistoryInfo()
    {
        if ($this->m_Stateless == "Y")
            return;
        if (! $this->m_NoHistoryInfo)
        {
            $histInfo[] = $this->m_RecordId;
            $histInfo[] = $this->m_CurrentPage;
            $histInfo[] = $this->m_SearchRule;
            $histInfo[] = $this->m_SortRule;
            return $histInfo;
        }
        return null;
    }
    /**
     * Set form history info array
     */
    protected function SetHistoryInfo($histInfo)
    {
        if ($histInfo && $histInfo[1] > 0)
        {
            $this->m_RecordId = $histInfo[0];
            $this->m_CurrentPage = $histInfo[1];
            $this->m_SearchRule = $histInfo[2];
            $this->m_SortRule = $histInfo[3];
        }
    }
    /**
     * BizForm::CleanHistoryInfo() - clear history info so that the data set is fresh
     *
     * @return array
     */
    public function CleanHistoryInfo()
    {
        $this->m_RecordId = null;
        $this->m_CurrentPage = 1;
        $this->m_SearchRule = null;
        $this->m_SortRule = null;
        $this->m_NoHistoryInfo = true;
    }
    /**
     * Turn off the post action
     */
    protected function SetPostActionOff()
    {
        $this->m_PostActionOff = true;
    }
    /**
     * Get post action. This method is called in ReRender() to determine the post action
     * @return string post action string
     */
    protected function GetPostAction()
    {
        if ($this->m_PostActionOff)
            return null;
        // check if the current rpc call has postaction specified
        global $g_BizSystem;
        // get the control that issues the call
        // __this is ctrlname:eventhandlername
        $ctrlname_ehname = BizSystem::clientProxy()->getFormInputs("__this");
        if (! $ctrlname_ehname)
            return null;
        list ($ctrlname, $ehname) = explode(":", $ctrlname_ehname);
        $ctrlobj = $this->GetControl($ctrlname);
        if (! $ctrlobj)
            return null;
        $postAction = $ctrlobj->GetPostAction($ehname); // need to get postaction of eventhandler
        if ($postAction)
            return $postAction;
        return null;
    }
    protected function GetPrtCommitPending()
    {
        return $this->m_PrtCommitPending;
    }
    protected function SetPrtCommitPending($flag)
    {
        $this->m_PrtCommitPending = $flag;
    }
    protected function CanShowData()
    {
        return ! $this->GetPrtCommitPending();
    } // parent form has new record pending
    /**
     * Get current page number
     * @return int page number
     */
    public function GetCurrentPageNumber()
    {
        return $this->m_CurrentPage;
    }
    /**
     * Get total page count
     * @return int total page count
     */
    public function GetTotalPageCount()
    {
        return $this->m_TotalPages;
    }
    /**
     * Get total records count
     * @return int total record count
     */
    public function GetTotalRecords()
    {
        return $this->m_TotalRecords;
    }
    /**
     * BizForm::UpdateActiveRecord() - update the active record with given record array
     *
     * @param array $recArr
     * @return void
     */
    final public function UpdateActiveRecord($recArr)
    {
        $this->m_ActiveRecord = $recArr;
        $this->m_RecordRow->setRecordArr($this->m_ActiveRecord); // needed ???
    }
    /**
     * Set the active working record Id
     * @param string $recordId record id
     */
    public function setActiveRecordId($recordId)
    {
        if ($recordId == null || $this->m_RecordId != $recordId)
        {
            $this->m_RecordId = $recordId;
            $this->m_ActiveRecord = null;
        }
    }

    /**
     * Get the active working record
     *
     * @return array record array
     */
    protected function getActiveRecord()
    {
        BizSystem::log(LOG_DEBUG, "FORMOBJ", $this->m_Name . "::getActiveRecord()");
        if (! $this->m_ActiveRecord)
        {
            if ($this->getDataObj() == null)
                return null;
            if ($this->m_RecordId)
                $this->getDataObj()->setActiveRecordId($this->m_RecordId);
            $this->m_ActiveRecord = $this->getDataObj()->getActiveRecord();
            // update the record row
            $this->m_RecordRow->setRecordArr($this->m_ActiveRecord);
        }
        return $this->m_ActiveRecord;
    }
    /**
     * Render the records in specific page
     * @param int $page the page index
     * @return void
     */
    public function gotoPage ($page = 1)
    {
        $tgtPage = $page;
        if ($tgtPage == 0)
            $tgtPage = 1;
        else
        if ($tgtPage < 0)
            $tgtPage = $this->m_TotalPages;
        else
        if ($tgtPage > $this->m_TotalPages)
            $tgtPage = $this->m_TotalPages;
        if ($tgtPage == $this->m_CurrentPage)
            return;
        $this->m_CurrentPage = $tgtPage;
        //if (!$this->getDataObj()->gotoPage($page))
        //   return;
        $this->rerender();
    }
    /**
     * BizForm::NextPage() - move to next page
     *
     * @return void
     */
    public function NextPage ()
    {
        if ($this->m_CurrentPage >= $this->m_TotalPages)
            $this->m_CurrentPage = $this->m_TotalPages;
        else
            $this->m_CurrentPage ++;
        $this->rerender();
    }
    /**
     * BizForm::PrevPage() - move to previous page
     *
     * @return void
     */
    public function PrevPage ()
    {
        if ($this->m_CurrentPage <= 1)
            $this->m_CurrentPage = 1;
        else
            $this->m_CurrentPage --;
        $this->rerender();
    }
    /**
     * BizForm::runSearch() - Run search on query mode, then go read mode
     *
     * @return void
     */
    public function runSearch ()
    {
        BizSystem::log(LOG_DEBUG, "FORMOBJ", $this->m_Name . "::runSearch()");
        global $g_BizSystem;
        $this->m_SearchRule = "";
        foreach ($this->m_RecordRow as $fldCtrl)
        {
            $value = BizSystem::clientProxy()->getFormInputs($fldCtrl->m_Name);
            if ($value !== null && $value !== '')
            {
                $searchStr = $this->inputValToRule($fldCtrl->m_BizFieldName, $value);
                if ($searchStr)
                {
                    $this->m_SearchRule .= $this->m_SearchRule == '' ? $searchStr : ' AND ' . $searchStr;
                } else
                {
                    // If it's emtpy; we will not alter anything
                }
            }
        }
        $this->SetDisplayMode(MODE_R);
        $this->gotoPage(1);
        $this->m_RecordId = null; // clean the current record id
        $this->m_ClearSearchRule = true;
        $this->rerender();
    }
    /**
     * BizForm::_run_search() - call RunSearch of its dataobj by applying its FixSearchRule and SearchRule
     * Its dataobj current search rule will be replaced by its FixSearchRule and SearchRule.
     *
     * @return void
     */
    public function _run_search (&$resultRecords, $clearSearchRule = true)
    {
        if (! $this->m_DataObjName)
            return;
        $dataobj = $this->getDataObj();
        if (strlen($this->m_FixSearchRule) > 0)
        {
            if (strlen($this->m_SearchRule) > 0)
                $this->m_SearchRule .= " AND " . $this->m_FixSearchRule;
            else
                $this->m_SearchRule = $this->m_FixSearchRule;
        }
        if ($clearSearchRule)
        {
            $dataobj->clearSearchRule();
        }
        $dataobj->setSearchRule($this->m_SearchRule);
        if ($this->m_Range && $this->m_Range > 0)
        {
            $this->m_TotalRecords = $dataobj->count();
            $this->m_TotalPages = ceil($this->m_TotalRecords / $this->m_Range);
            if ($this->m_TotalPages == 0)
                return true;
            if ($this->m_CurrentPage > $this->m_TotalPages)
                $this->m_CurrentPage = $this->m_TotalPages;
            $dataobj->setLimit($this->m_Range, ($this->m_CurrentPage - 1) * $this->m_Range);
        }
        $resultRecords = $dataobj->fetch();
        return true;
    }
    /**
     * BizForm::_DetectModal() - A function to detect if a modal presentation style
     * should be used in favor of OB's default handling.
     * Can also filter search critera to certain types of functions (ie. Edit, Query or New)
     * @param string Function string to filter by
     * @return boolean True/False
     */

    //New Version

    protected function _DetectModal ($functiontype = false)
    {
        $functiontype = strtoupper($functiontype);
        foreach ($this->m_ToolBar as $tool)
        {
            $m_FunctionType = strtoupper($tool->m_FunctionType);
            $m_Function = strtoupper($tool->m_Function);

            if ($functiontype == false)
            {
                if ($m_FunctionType == 'MODAL')
                    return true;
                if ($tool->m_EventHandlers)
                {
                    foreach ($tool->m_EventHandlers as $event)
                    {
                        if (strtoupper($event->m_FunctionType) == 'MODAL')
                            return true;
                    }
                }
            } else
            {
                if (strstr($m_Function, $functiontype) and $m_FunctionType == 'MODAL')
                    return true;
                if ($tool->m_EventHandlers)
                {
                    foreach ($tool->m_EventHandlers as $event)
                    {
                        $m_EvtFunctionType = strtoupper($event->m_FunctionType);
                        if (strstr($m_Function, $functiontype) and $m_EvtFunctionType == 'MODAL')
                            return true;
                    }
                }
            }
        }
        return false;
    }


    /**
     * BizForm::_DetectModal() - A function to detect if a modal presentation style
     * should be used in favor of OB's default handling.
     * Can also filter search critera to certain types of functions (ie. Edit, Query or New)
     *@param string Function string to filter by
     * @return boolean True/False
     */
    protected function _DetectWindow ($functiontype = false)
    {
        $functiontype = strtoupper($functiontype);
        foreach ($this->m_ToolBar as $tool)
        {
            $m_FunctionType = strtoupper($tool->m_FunctionType);
            $m_Function = strtoupper($tool->m_Function);

            if ($functiontype == false)
            {
                if ($m_FunctionType == 'WINDOW')
                    return true;
                if ($tool->m_EventHandlers)
                {
                    foreach ($tool->m_EventHandlers as $event)
                    {
                        if (strtoupper($event->m_FunctionType) == 'WINDOW')
                            return true;
                    }
                }
            } else
            {
                if (strstr($m_Function, $functiontype) and $m_FunctionType == 'WINDOW')
                    return true;
                if ($tool->m_EventHandlers)
                {
                    foreach ($tool->m_EventHandlers as $event)
                    {
                        $m_EvtFunctionType = strtoupper($event->m_FunctionType);
                        if (strstr($m_Function, $functiontype) and $m_EvtFunctionType == 'WINDOW')
                            return true;
                    }
                }
            }
        }
        return false;
    }
    /**
     * BizForm::SortRecord() - sort record on given column
     *
     * @param strinf $sort_col with format as "fieldControl,1|0" which means sorting on field by ASC|DESC
     * @return void
     */
    public function sortRecord ($sort_col)
    {
        $pos = strpos($sort_col, ",");
        if ($pos > 0)
            $reverse_flag = substr($sort_col, $pos + 1);
        $sortflag = ($reverse_flag == 1) ? "DESC" : "ASC";
        $sort_col = substr($sort_col, 0, $pos);
        // change the OnSortField
        $this->m_OnSortField = $sort_col;
        $this->m_OnSortFlag = $sortflag;
        // turn off the OnSort flag of the old onsort field
        $this->SetSortFieldFlag($this->m_OnSortField, null);
        // turn on the OnSort flag of the new onsort field
        $this->SetSortFieldFlag($this->m_OnSortField, $sortflag);
        // change the sort rule and issue the query
        $this->getDataObj()->setSortRule("[" . $this->GetControl($this->m_OnSortField)->m_BizFieldName . "] " . $sortflag);
        // move to 1st page
        $this->m_CurrentPage = 1;
        $this->rerender();
    }
    protected function SetSortFieldFlag ($sortFld, $sortFlag)
    {
        if ($sortFld)
        {
            $fldCtrl = $this->GetControl($sortFld);
            $fldCtrl->setSortFlag($sortFlag);
        }
    }
    /**
     * BizForm::selectRecord() - Select the record to selected row (if form show list of records)
     *
     * @return void
     */
    public function selectRecord ($recId)
    {
        $this->m_RecordId = $recId;
        $this->rerender(false); // not redraw the this form, but draw the subforms
    }
    /**
     * BizForm::SearchRecord() - show the query record mode
     *
     * @return void
     */
    public function SearchRecord ()
    {
        $this->UpdateActiveRecord(null);
        $this->m_QueryONRender = false;
        $this->SetDisplayMode(MODE_Q);
        $this->rerender(true, false);
    }
    /**
     * BizForm::RefreshQuery() - clear the search rule and do the original query when view first loaded
     *
     * @return void
     */
    public function RefreshQuery ()
    {
        if ($this->m_OnSortField)
        {
            $this->SetSortFieldFlag($this->m_OnSortField, null);
            $this->m_OnSortField = null;
            $this->getDataObj()->clearSortRule();
        }
        $this->SetDisplayMode(MODE_R);
        $this->m_ClearSearchRule = true;
        $this->rerender();
    }
    /**
     * Cancel current edit or query, then go read mode
     *
     * @return void
     */
    public function cancel ()
    {
        $prevMode = $this->m_Mode;
        $this->SetDisplayMode(MODE_R);
        //if ($prevMode == MODE_N) // NEW mode to READ mode, has record change, need to refresh the subforms
        $this->rerender(true, true);
        // EDIT to READ, no record change
        //$this->rerender(true, false);
    }
    /**
     * Show the new record mode
     *
     * @return void
     */
    public function newRecord ()
    {
        global $g_BizSystem;

        $this->SetDisplayMode(MODE_N);

        $recArr = $this->getNewRecord();
        $rerender = true; //by default
        if (! $recArr)
            return $this->processDataObjError();
        $this->UpdateActiveRecord($recArr);
        // TODO: popup message of new record successful created
        if ($this->_DetectModal("NEW") == TRUE OR $this->_DetectWindow("NEW") == TRUE)
            $rerender = false;
        $this->rerender('true', $rerender);
    }

    protected function getNewRecord ()
    {
        $recArr = $this->getDataObj()->newRecord();
        if (! $recArr)
            return null;
        // load default values if new record value is empty
        $default_recArr = $this->m_RecordRow->GetDefaultRecordArr();
        foreach ($recArr as $field => $val)
        {
            if ($val == "" && $default_recArr[$field] != "")
                $recArr[$field] = $default_recArr[$field];
        }
        return $recArr;
    }
    /**
     * Edit the record of current row
     *
     * @return void
     */
    public function editRecord ($recId = null)
    {
        global $g_BizSystem;
        if (! $recId)
            $recId = BizSystem::clientProxy()->getFormInputs('_selectedId');
        if ($recId != null)
            $this->setActiveRecordId($recId);
        $rec = $this->getActiveRecord();
        if (! $rec)
            return;
        $this->SetDisplayMode(MODE_E);
        $this->rerender(true, false);
    }
    /**
     * BizForm::readInputRecord() - read user input data from UI
     *
     * @param array - record array read in as output
     * @return boolean - indicate whether the input is read successfully
     */
    protected function readInputRecord (&$recArr)
    {
        global $g_BizSystem;
        foreach ($this->m_RecordRow as $fldCtrl)
        {
            if ($fldCtrl->canDisplayed())
            {
                $value = BizSystem::clientProxy()->getFormInputs($fldCtrl->m_Name);
                if ($value !== null)
                    $recArr[$fldCtrl->m_BizFieldName] = $value;
            }
        }
        return true;
    }
    /**
     * Validate input on BizForm level
     * default form validation do nothing.
     * developers need to override this method to implement their logic
     *
     * @return boolean
     */
    protected function validateForm ()
    {
        return true;
    }
    /**
     * Save current edited record with input
     *
     * @return void
     */
    public function saveRecord ()
    {
        // call ValidateForm()
        if ($this->ValidateForm() == false)
            return;
        $recArr = array();
        if ($this->readInputRecord($recArr) == false)
            return;
        if ($this->m_Mode == MODE_N)
            $dataRec = new DataRecord(null, $this->getDataObj());
        else
        if ($this->m_Mode == MODE_E)
            $dataRec = new DataRecord($this->getActiveRecord(), $this->getDataObj());
        foreach ($recArr as $k => $v)
            $dataRec[$k] = $v; // or $dataRec->$k = $v;
        $ok = $dataRec->save();
        if (! $ok)
            return $this->processDataObjError($ok);
        $this->UpdateActiveRecord($this->getDataObj()->getActiveRecord());
        $this->SetDisplayMode(MODE_R);
        // TODO: popup message of new record successful saved
        $this->rerender();
    }
    /**
     * BizForm::deleteRecord() - Delete the record of current row
     *
     * @return void
     */
    public function deleteRecord ()
    {
        // TODO: support delete multiple records
        // read the id array from the check box list _REQUEST['row_selections']
        global $g_BizSystem;
        $values = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($values)
        {
            foreach ($values as $id)
            {
                $recArray = $this->getDataObj()->fetchById($id);
                $dataRec = new DataRecord($recArray, $this->getDataObj());
                // take care of exception
                try
                {
                    $dataRec->delete();
                } catch (BDOException $e)
                {
                    // call $this->processBDOException($e);
                    $this->processBDOException($e);
                    return;
                }
            }
        } else // delete current focused record

        {
            $rec = $this->getActiveRecord();
            if (! $rec)
                return;
            global $g_BizSystem;
            //$recId = $this->m_ActiveRecord["Id"];
            $ok = $this->getDataObj()->deleteRecord($rec);
            if (! $ok)
                return $this->processDataObjError($ok);
        }
        $this->m_RecordId = null; // clean the current record id
        // TODO: adjust current page. if current page return no record, goto prev page
        $this->rerender();
    }
    /**
     * BizForm::removeRecord() - Remove the record out of the associate relationship
     *
     * @return void
     */
    public function removeRecord ()
    {
        $rec = $this->getActiveRecord();
        global $g_BizSystem;
        $ok = $this->getDataObj()->removeRecord($rec, $bPrtObjUpdated);
        if (! $ok)
            return $this->processDataObjError($ok);
        $html = "";
        // rerender parent form's driving form (its field is updated in M-1 case)
        if ($bPrtObjUpdated)
        {
            $prtForm = BizSystem::getObject($this->m_ParentFormName);
            $prtForm->rerender();
        }
        //$this->UpdateActiveRecord($this->getDataObj()->GetRecord(0));
        $this->rerender();
    }
    
    /**
     * Copy current record and paste to a new record     *
     * Note: copy record will error out if db table uses composite columns as its primary key
     *
     * @return void
     */
    public function copyRecord()
    {
        $rec = $this->getActiveRecord();
        if (! $rec)
            return;
        foreach ($rec as $k => $v)
            $rec[$k] = addslashes($v);
        global $g_BizSystem;
        if ($this->getDataObj()->m_IdGeneration == 'None')
        {
            $this->SetDisplayMode(MODE_N);
            $recArr = $this->getNewRecord();
            $rerender = true; //by default
            if (! $recArr)
                return $this->processDataObjError();
            $this->UpdateActiveRecord($rec);
            if ($this->_DetectModal('NewRecord') or $this->_DetectWindow('NewRecord'))
                $rerender = false;
            $this->rerender('true', $rerender);
        } else
        {
            // get new record array
            $recArr = $this->getDataObj()->newRecord();
            $rec["Id"] = $recArr["Id"]; // replace with new Id field. TODO: consider different ID generation type
            $this->m_RecordRow->setRecordArr($rec);
            $ok = $this->getDataObj()->insertRecord($rec);
            if (! $ok)
                return $this->processDataObjError($ok);
            $this->rerender();
        }
    }
    /**
     * Input value to rule, convert the user input on a given fieldcontrol in qeury mode to search rule
     *
     * @param string $field - fieldcontrol name
     * @param string $inputVal - use input text
     * @return string - searchRule
     */
    protected function inputValToRule ($field, $inputVal)
    {
        // todo: should check single quote for nonoperators clauses
        // find locations for all sql key words
        // search for starting ' and closing ' pair, check if sql key word in the pair
        global $g_BizSystem;
        $val = trim($inputVal);
        $bizFld = $this->getDataObj()->getField($field);
        //If we have a comma in the value, means that the HTMLControl
        //it's an array
        if (strpos($val, ",") !== FALSE)
        {
            //We get the values like an array and we trimming the *
            list ($sinceVal, $toVal) = explode(',', str_replace('*', '', $val));
            //If both are empty
            if ($sinceVal == '' && $toVal == '')
            {
                return NULL;
                //If one is empty, means that only one was entered
            } elseif ($sinceVal == '' || $toVal == '')
            {
                $val = str_replace(',', '', $val);
                //Successful data for make a between!
            } else
            {
                // unformat value to real value data
                $sinceVal = $g_BizSystem->getTypeManager()->formattedStringToValue($bizFld->m_Type, $bizFld->m_Format, $sinceVal);
                $toVal = $g_BizSystem->getTypeManager()->formattedStringToValue($bizFld->m_Type, $bizFld->m_Format, $toVal);
                return "[" . $field . "] " . 'BETWEEN' . "'$sinceVal' " . 'AND' . " '$toVal'";
            }
        } else
        {
            #
        }
        // check " AND ", " OR "
        if (($pos = strpos(strtoupper($val), " AND ")) !== false)
        {
            $inputArr = explodei(" AND ", $val);
            $retStr = null;
            foreach ($inputArr as $v)
                $retStr .= ($retStr) ? " AND " . $this->inputValToRule($field, $v) : $this->inputValToRule($field, $v);
            return $retStr;
        } else
        if (($pos = strpos(strtoupper($val), " OR ")) !== false)
        {
            $inputArr = explodei(" OR ", $val);
            $retStr = null;
            foreach ($inputArr as $v)
                $retStr .= ($retStr) ? " OR " . $this->inputValToRule($field, $v) : $this->inputValToRule($field, $v);
            return "(" . $retStr . ")";
        }
        // check >=, >, <=, <, =
        if (($pos = strpos($val, "<>")) !== false || ($pos = strpos($val, "!=")) !== false)
        {
            $opr = "<>";
            $oprlen = 2;
        } else
        if (($pos = strpos($val, ">=")) !== false)
        {
            $opr = ">=";
            $oprlen = 2;
        } else
        if (($pos = strpos($val, ">")) !== false)
        {
            $opr = ">";
            $oprlen = 1;
        } else
        if (($pos = strpos($val, "<=")) !== false)
        {
            $opr = "<=";
            $oprlen = 2;
        } else
        if (($pos = strpos($val, "<")) !== false)
        {
            $opr = "<";
            $oprlen = 1;
        } else
        if (($pos = strpos($val, "=")) !== false)
        {
            $opr = "=";
            $oprlen = 1;
        }
        if ($opr)
        {
            $val = trim(substr($val, $pos + $oprlen));
        }
        if (strpos($val, "*") !== false)
        {
            $opr = "LIKE";
            $val = str_replace("*", "%", $val);
        }
        //if (strpos($val, "'") !== false) {   // not needed since addslashes() is called before
        //   $val = str_replace("'", "\\'", $val);
        //}
        if (! $opr)
            $opr = "=";
        // unformat value to real value data
        $realVal = $g_BizSystem->getTypeManager()->formattedStringToValue($bizFld->m_Type, $bizFld->m_Format, $val);
        return "[" . $field . "] " . $opr . " '" . $realVal . "'";
    }
    // send user input to BizForm. synch up client to server Bizform's activeRecord
    public function SendUserInput ()
    {
        $this->readInputRecord($recArr);
        $this->UpdateActiveRecord($recArr);
    }
    /**
     * BizForm::ShowPopup() - Popup a selection BizForm in a dynamically generated BizView
     *
     * @param string $formName - the popup bizform
     * @return string - HTML text of popup view
     */
    public function showPopup ($formName, $ctrlName = "")
    {
        $this->SendUserInput();
        // generate an xml attribute array for a dynamic bizview
        $xmlArr = BizView::GetPopupViewXML($this->m_Package, $formName);
        // create a BizViewPopup with the xml array
        global $g_BizSystem;
        $popupView = $g_BizSystem->getObjectFactory()->createObject("DynPopup", $xmlArr);
        $formName = $this->prefixPackage($formName);
        // set the ParentFormName and ParentCtrlName of the popup form
        $popupForm = BizSystem::getObject($formName);
        $popupForm->setParentForm($this->m_Name);
        $popupForm->m_PrtFormCtrlName = $ctrlName;
        // set the dimension of the popup
        $w = $popupForm->m_Width ? $popupForm->m_Width : 640;
        $h = $popupForm->m_Height ? $popupForm->m_Height : 480;
        $popupView->SetPopupSize($w, $h);
        // clean history
        $popupView->CleanViewHistory();
        // render the popup
        $popupView->render();
        // exit;
    }

    /**
     *  Automatically search on the user input.
     *  - input is empty, delete all related controls values
     *  - input is no empty, query the DataObj of valuepicker BizForm.
     *    handle the cases of single/multiple/no records returned
     *
     * @param string $elementName
     * @return void
     */
    public function AutoPickValue($elementName)
    {
        $value = BizSystem::clientProxy()->getFormInputs($elementName);
        $elementObj = $this->GetControl($elementName);
        $valuePicker = $this->GetControl($elementName)->m_ValuePicker;
        if (! $valuePicker)
            return;
        // get valuePicker form and its dataobj
        $valuePickerForm = BizSystem::getObject($valuePicker);
        if (! $valuePickerForm)
            return;
        $valuePickerDO = $valuePickerForm->getDataObj();
        if (! $valuePickerDO)
            return;
        // get map of thisDOfield => joinDOfield
        $joinFields = $this->getDataObj()->getJoinFields($valuePickerDO);
        if (! $value)
        {
            // delete all related control values
            $joinFields = $this->getDataObj()->getJoinFields($valuePickerDO);
            $rec = $this->getActiveRecord();
            foreach ($joinFields as $tfield => $jfield)
            {
                $rec[$tfield] = "";
            }
            $this->UpdateActiveRecord($rec);
        } else
        {
            // query on valuePickerDO with current control value. searchrule as [fieldname]='value*'
            $jfield = $joinFields[$elementObj->m_BizFieldName];
            if (! $jfield)
                return;
            $searchRule = "[$jfield] LIKE '$value%'";
            $recordList = $valuePickerDO->directFetch($searchRule, 2); // fetch >1 records
            // if return single record, populate the fields
            if (count($recordList) == 1)
            {
                $rec = $this->getActiveRecord();
                foreach ($joinFields as $tfield => $jfield)
                {
                    $rec[$tfield] = $recordList[0][$jfield];
                }
                $this->UpdateActiveRecord($rec);
            } else
            {
                if (count($recordList) == 2)
                    $valuePickerDO->setSearchRule($searchRule);
                // if return > 1 records, show popup by calling clientProxy->showPopup()
                BizSystem::clientProxy()->showPopup($this->m_Name, $valuePicker);
                return;
            }
        }
        $this->rerender();
    }
    
    /**
     * Generate list for autosuggest listing.  Formatted for simple of hidden inputs
     *
     * @param string $input - the search string used to filter the list
     */
    public function autoSuggest ($input)
    {
        if (strpos($input, '_hidden'))
        {
            $real_input = str_replace('_hidden', '', $input);
        } else
        {
            $real_input = $input;
        }
        global $g_BizSystem;
        $value = BizSystem::clientProxy()->getFormInputs($input);
        // get the select from list of the control
        $ctrl = $this->GetControl($real_input);
        $ctrl->setValue($value);
        $fromlist = array();
        $ctrl->getFromList($fromlist);
        echo "<ul>";
        if ($fromlist)
        {
            if (strpos($input, '_hidden'))
            {
                foreach ($fromlist as $item)
                {
                    echo "<li id=" . $item['val'] . ">" . $item['txt'] . "</li>";
                }
            } else
            {
                foreach ($fromlist as $item)
                {
                    echo "<li>" . $item['txt'] . "</li>";
                }
            }
        }
        echo "</ul>";
    }
    /**
     * BizForm::HandlePostAction() - post action is the redirected page/view after an action is finished successfully
     *
     * @param string $postAction postaction can be view:xxx, url:xxx, mode:xxx
     * @return string - redirect page or view
     */
    public function HandlePostAction ($postAction)
    {
        global $g_BizSystem;
        $pos = strpos($postAction, ":");
        $tag = substr($postAction, 0, $pos);
        $content = substr($postAction, $pos + 1);
        if ($tag == "view")
            BizSystem::clientProxy()->ReDirectView($content);
        else
        if ($tag == "url")
            BizSystem::clientProxy()->ReDirectPage($content);
        else
        if ($tag == "mode")
        {
        } else
            return;
    }
    /**
     * Update form controls
     * @return void
     */
    public function updateForm ()
    {
        // set the input to form controls
        $recArr = array();
        $this->readInputRecord($recArr);
        $this->UpdateActiveRecord($recArr);
        // strait way of updating form - rerender
        $this->rerender();
    }
    /**
     * BizForm::render() - render this form (return html content), called by bizview's render method (called when form is loaded).
     * Query is issued before returning the html content.
     *
     * @return string - HTML text of this form's read mode
     */
    public function render ()
    {
        // when in NEW mode or when parent form in NEW mode, do nothing
        global $g_BizSystem;
        $prtMode = "";
        if ($this->m_ParentFormName)
        {
            $prtForm = BizSystem::getObject($this->m_ParentFormName);
            $prtMode = $prtForm->GetDisplayMode()->GetMode();
        }
        if ($this->m_Mode != MODE_N && $prtMode != MODE_N)
        {
            // get view history
            if (! $this->m_NoHistoryInfo)
                $this->SetHistoryInfo(BizSystem::sessionContext()->getViewHistory($this->m_Name));
        }
        if ($this->m_Mode == MODE_N)
        {
            //$this->UpdateActiveRecord($this->getDataObj()->newRecord());
            $this->UpdateActiveRecord($this->getNewRecord());
            $this->m_QueryONRender = false;
        }
        //Moved the renderHTML function infront of declaring subforms
        $renderedHTML = $this->renderHTML();
        global $g_BizSystem;
        // prepare the subforms' dataobjs, since the subform relates to parent form by dataobj association
        if ($this->m_SubForms)
        {
            foreach ($this->m_SubForms as $subForm)
            {
                $formObj = BizSystem::getObject($subForm);
                $dataObj = $this->getDataObj()->getRefObject($formObj->m_DataObjName);
                if ($dataObj)
                    $formObj->setDataObj($dataObj);
            }
        }
        $this->setClientScripts();
        return $renderedHTML;
    }
    /**
     * Set client scripts -> auto add javascripts code to the page
     */
    protected function setClientScripts ()
    {
        global $g_BizSystem;
        if ($this->m_jsClass != "jbForm")
            BizSystem::clientProxy()->appendScripts($this->m_jsClass, $this->m_jsClass . ".js");
        if ($this->_DetectModal())
        {
            BizSystem::clientProxy()->includeModalScripts();
        }
        if ($this->_DetectWindow())
        {
            BizSystem::clientProxy()->includeWindowScripts();
        }
        $this->SetFieldScripts();
        if ($this->m_Mode == 'READ')
        {
            $dispMode = $this->m_DisplayModes->get('READ');
            if ($dispMode->m_DataFormat == 'block')
                BizSystem::clientProxy()->appendScripts("tablekit", "tablekit.js");
        }
        //BizSystem::clientProxy()->appendScripts("fastinit", "fastinit.js");
    }
    /**
     * BizForm::SetFieldScripts - Add any required JS libraries to a page based on the BizForms fields
     * Left public so that ViewWizard objects can anticipate future BizForm requirements
     */
    public function SetFieldScripts ()
    {
        global $g_BizSystem;
        // scan all elements
        foreach ($this->m_RecordRow as $ctrl)
        {
            $cType = strtoupper($ctrl->m_Type);
            if ($cType == "RICHTEXT")
                BizSystem::clientProxy()->includeRTEScripts();
            else
            if ($cType == "DATETIME" || $cType == "DATE")
                BizSystem::clientProxy()->includeCalendarScripts();
            else
            if ($cType == "AUTOSUGGEST")
                BizSystem::clientProxy()->appendScripts("scriptaculous", "scriptaculous.js");
        }
    }
    /**
     * BizForm::rerender() - rerender this form (form is rendered already) .
     *
     * @param boolean $redrawForm - whether render this form again or not
     * @param boolean $hasRecordChange - if record change, need to render subforms
     * @return void
     */
    public function rerender ($redrawForm = true, $hasRecordChange = true)
    {
        if ($this->m_ReRenderOn == false)
            return;
        // consider the postAction
        $postAction = $this->GetPostAction();
        if ($postAction)
        {
            $this->HandlePostAction($postAction);
            return;
        }
        global $g_BizSystem;
        if ($redrawForm)
        {
            if (BizSystem::clientProxy()->hasFormRerendered($this->m_Name) == false)
                BizSystem::clientProxy()->redrawForm($this->m_Name, $this->renderHTML());
        }
        if ($hasRecordChange)
        {
            $this->rerenderSubForms();
        }
        return;
    }
    /**
     * BizForm::rerenderSubForms() - rerender sub forms who has dependecy on this form.
     * This method is called when parent form's change affect the sub forms
     *
     * @return void
     */
    protected function rerenderSubForms ()
    {
        if (! $this->m_SubForms)
            return;
        $this->m_ActiveRecord = $this->getActiveRecord();
        global $g_BizSystem;
        $mode = $this->GetDisplayMode()->GetMode();
        foreach ($this->m_SubForms as $subForm)
        {
            $formObj = BizSystem::getObject($subForm);
            $formObj->SetPostActionOff();
            if ($mode == MODE_N)
            { // parent form on new mode
                $formObj->SetPrtCommitPending(true);
            } else
            {
                $formObj->SetPrtCommitPending(false);
                $dataObj = $this->getDataObj()->getRefObject($formObj->m_DataObjName);
                if ($dataObj)
                    $formObj->setDataObj($dataObj);
            }
            // force the active row on the first row
            $formObj->setActiveRecordId(null);
            $formObj->rerender();
        }
        return;
    }
    /**
     * BizForm::renderHTML() - render html content of this form
     *
     * @return string - HTML text of this form's read mode
     */
    protected function renderHTML ($smarty = false)
    {
        $dispmode = $this->GetDisplayMode();
        $this->SetDisplayMode($dispmode->GetMode());
        //Added to support Auto Scripts
        $this->setClientScripts();
        if (! $smarty)
            $smarty = BizSystem::getSmartyTemplate();

        //Rendering to support either normal Form on in a window/modal
        if ($this->_DetectModal($dispmode->m_Name) == TRUE OR $this->_DetectWindow($dispmode->m_Name) == TRUE)
        {
            $pop_name = $this->m_Name . Popup_Suffix;
            $smarty->assign_by_ref("name", $pop_name);

            $temp_toolbar = $this->m_ToolBar->render();
            $temp_navbar = $this->m_NavBar->render();

            foreach ($temp_toolbar as $key=>$val)
            {
                $pop_toolbar[$key] = str_replace($this->m_Name, $pop_name, $val);
            }

            foreach ($temp_navbar as $key=>$val)
            {
                $pop_navbar[$key] = str_replace($this->m_Name, $pop_name, $val);
            }


            $smarty->assign_by_ref("toolbar", $pop_toolbar);
            $smarty->assign_by_ref("navbar", $pop_navbar);

        }  else
        {
            $smarty->assign_by_ref("name", $this->m_Name);
            $smarty->assign_by_ref("navbar", $this->m_NavBar->render());
            $smarty->assign_by_ref("toolbar", $this->m_ToolBar->render());
        }

        $smarty->assign_by_ref("title", $this->m_Title);
        $smarty->assign_by_ref("description", $this->m_Description); //added by Jixian

        if ($dispmode->m_DataFormat == "array") // if dataFormat is array, call array render function
            $smarty->assign_by_ref("fields", $this->renderArray());
        else
        if ($dispmode->m_DataFormat == "table") // if dataFormat is table, call table render function.

        {
            $smarty->assign_by_ref("table", $this->renderTable());
            $smarty->assign_by_ref("formobj", $this);
        } else
        if ($dispmode->m_DataFormat == "block" && $dispmode->m_FormatStyle)
            $smarty->assign_by_ref("block", $this->renderFormattedTable());

        return $smarty->fetch(BizSystem::getTplFileWithPath($dispmode->m_TemplateFile, $this->m_Package)) . "\n" . $this->renderShortcutKeys() . "\n" . $this->renderContextMenu();
    }
    /**
     * BizForm::renderArray() - Render form as array format using array template
     * @return string 1d array
     */
    protected function renderArray ()
    {
        if ($this->m_QueryONRender && ! $this->m_ActiveRecord && $this->m_DataObjName)
        {
            if (! $this->_run_search($resultRecords, $this->m_ClearSearchRule))
                return $this->processDataObjError($ok);
            $this->UpdateActiveRecord($resultRecords[0]);
        }
        $columns = $this->m_RecordRow->renderColumn();
        foreach ($columns as $key => $val)
        {
            $fields[$key]["label"] = $val;
            $fields[$key]["required"] = $this->GetControl($key)->m_Required;
            $fields[$key]["description"] = $this->GetControl($key)->m_Description;
            $fields[$key]["value"] = $this->GetControl($key)->m_Value;
        }
        $controls = $this->m_RecordRow->render();
        if ($this->CanShowData())
        {
            foreach ($controls as $key => $val)
            {
                $fields[$key]["control"] = $val;
            }
        }
        return $fields;
    }
    /**
     * BizForm::renderTable() - Render form as table format using table template
     * @return string 2d array
     */
    public function renderTable ()
    {
        if ($this->m_QueryONRender)
            if (! $this->_run_search($resultRecords, $this->m_ClearSearchRule))
                return $this->processDataObjError($ok);
        $records = array();
        $records[] = $this->m_RecordRow->renderColumn();
        $counter = 0;
        while (true)
        {
            if ($this->m_Range != null && $this->m_Range > 0 && $counter > $this->m_Range)
                break;
            if ($this->CanShowData())
                $arr = $resultRecords[$counter];
            if (! $arr)
                break;
            $this->m_RecordRow->setRecordArr($arr);
            $tblRow = $this->m_RecordRow->render();
            $records[] = $tblRow;
            $counter ++;
        }
        //print_r($records);
        return $records;
    }
    /**
     * BizForm::renderFormattedTable() - Render form as table format using table format style
     * Example as template->m_FormatStyle:table_style give the top style of the table.
     * head,rowodd,roweven,rowsel,cell in css file will be used in the table elements
     *
     * @return string HTML text
     */
    protected function renderFormattedTable ()
    {
        if ($this->m_QueryONRender)
            if (! $this->_run_search($resultRecords, $this->m_ClearSearchRule))
                return $this->processDataObjError($ok);
        $dispmode = $this->GetDisplayMode();
        $modaledit = $this->_DetectModal('EditRecord');
        $windowedit = $this->_DetectWindow('EditRecord');
        $hasSub = $this->m_SubForms ? 1 : 0;
        //$this->SetDisplayMode($dispmode->GetMode());
        $cls_tbl = strlen($dispmode->m_FormatStyle[0]) > 0 ? "class='" . $dispmode->m_FormatStyle[0] . "'" : "";
        $sHTML_tbl = "<table width=100% border=0 cellspacing=0 cellpadding=3 $cls_tbl>\n";
        //$sHTML_tby = "<tbody id='".$this->m_Name."_tbody' Highlighted='".$this->m_Name."_data_".($this->m_CursorIndex+1)."' SelectedRow='".($this->m_CursorIndex+1)."'>\n";
        // print column header
        $columns = $this->m_RecordRow->renderColumn();
        // get colummn style
        $columnStyle = $this->m_RecordRow->renderColumnStyle();
        $sHTML = "";
        foreach ($columns as $colname)
            $sHTML .= "<th class=head>$colname</th>\n";
        // print column data table
        $name = $this->m_Name;
        $counter = 0;
        $sHTML_rows = "";
        $selectedRecId = null;
        $selectedIndex = 0;
        while (true)
        {
            if ($this->m_Range != null && $this->m_Range > 0 && $counter >= $this->m_Range)
                break;
            if ($this->CanShowData())
                $arr = $resultRecords[$counter];
            else
                $arr = null;
            if (! $arr && $this->m_FullPage == "N")
                break;
            if (! $arr)
                $sHTML_rows .= "<tr><td colspan=99>&nbsp;</td></tr>\n";
            else
            {
                $recId = $arr["Id"];
                $this->m_RecordRow->setRecordArr($arr);
                $tblRow = $this->m_RecordRow->render();
                $rowHTML = "";
                foreach ($tblRow as $key => $cell)
                {
                    $cell_html = $cell == "" ? "&nbsp;" : $cell;
                    $rowHTML .= "<td valign=top class=cell style='" . $columnStyle[$key] . "'>$cell_html</td>\n";
                }
                $rownum = $counter + 1;
                $rowid = $name . "_data_" . $rownum;
                $attr = $rownum % 2 == 0 ? "normal=roweven select=rowsel" : "normal=rowodd select=rowsel";
                if ($this->m_HistRecordId != null)
                    $this->m_RecordId = $this->m_HistRecordId;
                if ($this->m_RecordId == null)
                    $this->m_RecordId = $recId;
                if ($this->m_RecordId == $recId)
                {
                    $style_class = "class=rowsel";
                    $selectedRecId = $recId;
                    $selectedIndex = $counter;
                } else
                if ($rownum % 2 == 0)
                    $style_class = "class=roweven";
                else
                    $style_class = "class=rowodd";
                if ($this->m_DisplayModes->get("EDIT") != null)
                {
                    if ($modaledit == TRUE)
                    {
                        $ondblclick = "ondblclick=\"CallFunction('$name.EditRecord($recId)', 'Modal');\"";
                    } elseif ($windowedit == TRUE)
                    {
                        $ondblclick = "ondblclick=\"CallFunction('$name.EditRecord($recId)', 'Window');\"";
                    } else
                    {
                        $ondblclick = "ondblclick=\"CallFunction('$name.EditRecord($recId)');\"";
                    }
                }
                $onclick = "onclick=\"CallFunction('$name.SelectRecord($recId,$hasSub)');\"";
                if ($rownum == 1)
                {
                    $sHTML_row1 = "<tr id='$name-$recId' $style_class $attr $onclick $ondblclick>\n$rowHTML</tr>\n";
                    $row1_id = $recId;
                } else
                    $sHTML_rows .= "<tr id='$name-$recId' $style_class $attr $onclick $ondblclick>\n$rowHTML</tr>\n";
            }
            $counter ++;
        } // while
        if ($selectedRecId == null)
        {
            $selectedRecId = $row1_id;
            $this->m_RecordId = $selectedRecId;
            $sHTML_row1 = str_replace("class=rowodd", "class=rowsel", $sHTML_row1);
        }
        $sHTML .= $sHTML_row1 . $sHTML_rows;
        $this->getDataObj()->setActiveRecord($resultRecords[$selectedIndex]);
        $sHTML_pre = "\n<input type='hidden' id='" . $this->m_Name . "_selectedId' name='_selectedId' value='$selectedRecId'/>\n";
        $sHTML_tby = "<tbody id='" . $this->m_Name . "_tbody'>\n";
        $sHTML = $sHTML_pre . $sHTML_tbl . $sHTML_tby . $sHTML . "</tbody></table>";
        return $sHTML;
    }
    /**
     * Render shortcut keys code
     * @return string html code for shortcut keys code
     */
    protected function renderShortcutKeys ()
    {
        $keymap = array();
        // scan toolbar navbar elements. if its eventhandler has shortcutkey attribute, print [key => function]
        foreach ($this->m_ToolBar as $ctrl)
        {
            $map = $ctrl->getSCKeyFuncMap();
            if (count($map) > 0)
                $keymap = array_merge($keymap, $map);
        }
        foreach ($this->m_NavBar as $ctrl)
        {
            $map = $ctrl->getSCKeyFuncMap();
            if (count($map) > 0)
                $keymap = array_merge($keymap, $map);
        }
        $str = "<div id='" . $this->m_Name . "_accelkeys' style='display:none'>";
        foreach ($keymap as $key => $func)
            $str .= "[$key:$func]";
        $str .= "</div>";
        return $str;
    }
    /**
     * Render context menu code
     * @return string html code for context menu
     */
    protected function renderContextMenu ()
    {
        $menulist = array();
        // scan toolbar navbar elements. if its eventhandler has shortcutkey attribute, print [key => function]
        foreach ($this->m_ToolBar as $ctrl)
        {
            $menus = $ctrl->getContextMenu();
            if (! $menus)
                continue;
            foreach ($menus as $m)
                $menulist[] = $m;
        }
        foreach ($this->m_NavBar as $ctrl)
        {
            $menus = $ctrl->getContextMenu();
            if (! $menus)
                continue;
            foreach ($menus as $m)
                $menulist[] = $m;
        }
        if (count($menulist) == 0)
            return "";
        $str = "<ul class='contextMenu' id='" . $this->m_Name . "_contextmenu'>";
        foreach ($menulist as $m)
        {
            $func = $m['func'];
            $str .= "<li><a href=\"javascript:$func\">" . $m['text'] . "  (" . $m['key'] . ")</a></li>";
        }
        $str .= "</ul>";
        return $str;
    }



}
?>
