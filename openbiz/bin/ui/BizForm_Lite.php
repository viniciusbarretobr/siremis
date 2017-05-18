<?PHP

/**
 * BizForm_Lite class - BizForm_Lite is light verion of BizForm. It handles readonly actions.
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

include_once(OPENBIZ_BIN."/ui/BizForm_Abstract.php");

class BizForm_Lite extends BizForm_Abstract
{
   protected $m_CurrentPage = 1; 
   protected $m_TotalPages = 0; 
   protected $m_TotalRecords = 0; 

   protected $m_OnSortField;
   protected $m_OnSortFlag; 

   protected $m_ClearSearchRule = true;
	
   /**
    * Get current page number
    * @return int page number
    */
   public function GetCurrentPageNumber() { return $this->m_CurrentPage; }
   
   /**
    * Get total page count
    * @return int total page count
    */
   public function GetTotalPageCount() { return $this->m_TotalPages; }
   
   /**
    * Get total records count
    * @return int total record count
    */
   public function GetTotalRecords() { return $this->m_TotalRecords; }
	
   /**
    * Render the records in specific page
    * @param int $page the page index
    * @return void
    */
   public function gotoPage($page=1)
   {
      global $g_BizSystem;
      $this->m_CurrentPage = BizSystem::clientProxy()->getFormInputs('_currentPage');
      $this->m_TotalPages = BizSystem::clientProxy()->getFormInputs('_totalPage');
      $tgtPage = $page;
      if ($tgtPage == 0) $tgtPage = 1;
      else if ($tgtPage < 0) $tgtPage = $this->m_TotalPages;
      else if ($tgtPage > $this->m_TotalPages) $tgtPage = $this->m_TotalPages;
      if ($tgtPage == $this->m_CurrentPage)
         return;
      $this->m_CurrentPage = $tgtPage;
      $this->rerender();
   }

   /**
    * Move to next page
    *
    * @return void
    */
   public function NextPage()
   {
      global $g_BizSystem;
      $this->m_CurrentPage = BizSystem::clientProxy()->getFormInputs('_currentPage');
      $this->m_TotalPages = BizSystem::clientProxy()->getFormInputs('_totalPage');
      if ($this->m_CurrentPage >= $this->m_TotalPages)
         $this->m_CurrentPage = $this->m_TotalPages;
      else
         $this->m_CurrentPage++;
      $this->rerender();
   }
   /**
    * Move to previous page
    *
    * @return void
    */
   public function PrevPage()
   {
      global $g_BizSystem;
      $this->m_CurrentPage = BizSystem::clientProxy()->getFormInputs('_currentPage');
      $this->m_TotalPages = BizSystem::clientProxy()->getFormInputs('_totalPage');
      if ($this->m_CurrentPage <= 1)
         $this->m_CurrentPage = 1;
      else 
         $this->m_CurrentPage--;
      $this->rerender();
   }
   
   /**
    * Run search on query mode, then go read mode
    *
    * @return void
    */
   public function runSearch($targetForm=null)
   {
      BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name."::runSearch(), SearchRule=".$this->m_SearchRule);
      
      global $g_BizSystem;
      
      $searchRule = "";
      foreach ($this->m_RecordRow as $fldCtrl) {
         $value = BizSystem::clientProxy()->getFormInputs($fldCtrl->m_Name);
         if ($value) {
            $searchStr = $this->inputValToRule($fldCtrl->m_BizFieldName, $value);
            if ($searchRule == "")
               $searchRule .= $searchStr;
            else
               $searchRule .= " AND " . $searchStr;
         }
      }
      
      if ($targetForm)
      {
         $tgtForm = BizSystem::objectFactory()->getObject($targetForm);
         if ($tgtForm)
         {
            $tgtForm->setSearchRule($searchRule);
            return $tgtForm->rerender();
         }
         return;
      }
      
      $this->m_SearchRule = $searchRule;

      //$this->SetDisplayMode (MODE_R);
      $this->gotoPage(1);
      $this->m_ClearSearchRule = true;
      $this->rerender();
   }
   
   /**
    * Call RunSearch of its dataobj by applying its FixSearchRule and SearchRule
    * Its dataobj current search rule will be replaced by its FixSearchRule and SearchRule.
    *
    * @return void
    */
	public function _run_search(&$resultRecords, $clearSearchRule=true)
   {
      if (!$this->m_DataObjName)
         return;
      $dataobj = $this->getDataObj();
      if (strlen($this->m_FixSearchRule) > 0) {
         if (strlen($this->m_SearchRule) > 0)
            $this->m_SearchRule .= " AND " . $this->m_FixSearchRule;
         else
            $this->m_SearchRule = $this->m_FixSearchRule;
      }
      if ($clearSearchRule)
         $dataobj->clearSearchRule();
      $dataobj->setSearchRule($this->m_SearchRule);
      $dataobj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
      $resultRecords = $dataobj->fetch();
      $this->m_TotalRecords = $dataobj->count();
      if ($this->m_Range && $this->m_Range > 0)
         $this->m_TotalPages = ceil($this->m_TotalRecords/$this->m_Range);
      return true;
   }
   
   /**
    * Sort record on given column
    *
    * @param strinf $sort_col with format as "fieldControl,1|0" which means sorting on field by ASC|DESC
    * @return void
    */
   public function sortRecord($sort_col)
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

   /**
    * Set sort field flag
    * @param string $sortFld sort field name
    * @param int $sortFlag 1 or 0
    * @return void
    */
   protected function SetSortFieldFlag($sortFld, $sortFlag)
   {
      if ($sortFld) {
         $fldCtrl = $this->GetControl($sortFld);
         $fldCtrl->setSortFlag($sortFlag);
      }
   }

   /**
    * Read user input data from UI
    *
    * @param array - record array read in as output
    * @return boolean - indicate whether the input is read successfully
    */
   protected function readInputRecord(&$recArr)
   {
      global $g_BizSystem;
      foreach ($this->m_RecordRow as $fldCtrl) {
         if ($fldCtrl->canDisplayed()) {
            $value = BizSystem::clientProxy()->getFormInputs($fldCtrl->m_Name);
            $recArr[$fldCtrl->m_BizFieldName] = $value;
         }
      }
      return true;
   }
   
   /**
    * Clear the search rule and do the original query when view first loaded
    *
    * @return string - HTML text of this form's read mode
    */
   public function RefreshQuery()
   {
      /*if ($this->m_OnSortField) {
         $this->SetSortFieldFlag($this->m_OnSortField, null);
         $this->m_OnSortField = null;
         $this->getDataObj()->clearSortRule();
      }*/
      $this->m_ClearSearchRule = true;
      $this->rerender();
   }

   /**
    * Convert the user input on a given fieldcontrol in qeury mode to search rule
    *
    * @param string $field - fieldcontrol name
    * @param string $inputVal - use input text
    * @return string - searchRule
    */
   protected function inputValToRule($field, $inputVal)
   {
      // todo: should check single quote for nonoperators clauses
      // find locations for all sql key words
      // search for starting ' and closing ' pair, check if sql key word in the pair

      $val = strtoupper(trim($inputVal));
      // check " AND ", " OR "
      if (($pos=strpos($val, " AND "))!==false) {
         $inputArr = explode(" AND ", $val);
         $retStr = null;
         foreach($inputArr as $v)
            $retStr .= ($retStr) ? " AND ".$this->inputValToRule($field, $v) : $this->inputValToRule($field, $v);
         return $retStr;
      }
      else if (($pos=strpos($val, " OR "))!==false) {
         $inputArr = explode(" OR ", $val);
         $retStr = null;
         foreach($inputArr as $v)
            $retStr .= ($retStr) ? " OR ".$this->inputValToRule($field, $v) : $this->inputValToRule($field, $v);
         return "(".$retStr.")";
      }
      // check >=, >, <=, <, =
      if (($pos=strpos($val, "<>"))!==false || ($pos=strpos($val, "!="))!==false) {
         $opr = "<>"; $oprlen = 2;
      }
      else if (($pos=strpos($val, ">="))!==false) {
         $opr = ">="; $oprlen = 2;
      }
      else if (($pos=strpos($val, ">"))!==false) {
         $opr = ">"; $oprlen = 1;
      }
      else if (($pos=strpos($val, "<="))!==false) {
         $opr = "<="; $oprlen = 2;
      }
      else if (($pos=strpos($val, "<"))!==false) {
         $opr = "<"; $oprlen = 1;
      }
      else if (($pos=strpos($val, "="))!==false) {
         $opr = "="; $oprlen = 1;
      }
      if ($opr) {
         $val = trim(substr($val, $pos+$oprlen));
      }

      if (strpos($val, "*") !== false) {
         $opr = "LIKE";
         $val = str_replace("*", "%", $val);
      }
      //if (strpos($val, "'") !== false) {   // not needed since addslashes() is called before
      //   $val = str_replace("'", "\\'", $val);
      //}
      if (!$opr)
         $opr = "=";

      // unformat value to real value data
      $bizFld = $this->getDataObj()->getField($field);
      global $g_BizSystem;
      $realVal = $g_BizSystem->getTypeManager()->formattedStringToValue($bizFld->m_Type, $bizFld->m_Format, $val);

      return "[" . $field . "] " . $opr . " '" . $realVal . "'";
   }

   /**
    * Render this form (return html content), called by bizview's render method (called when form is loaded).
    * Query is issued before returning the html content.
    *
    * @return string - HTML text of this form's read mode
    */
	public function render()
	{

      //Moved the renderHTML function infront of declaring subforms
      $renderedHTML = $this->renderHTML();

      return $renderedHTML;
	}

   /**
    * Rerender this form (form is rendered already) .
    * @return void
    */
	public function rerender()
	{
	   global $g_BizSystem; 
	   BizSystem::clientProxy()->redrawForm($this->m_Name, $this->renderHTML());
	}

   /**
    * Render html content of this form
    *
    * @return string - HTML text of this form's read mode
    */
	protected function renderHTML()
	{
	   $dispmode = $this->GetDisplayMode();
	   $this->SetDisplayMode($dispmode->GetMode());

      $smarty = BizSystem::getSmartyTemplate();
      $smarty->assign_by_ref("name", $this->m_Name);
      $smarty->assign_by_ref("title", $this->m_Title);
      $smarty->assign_by_ref("toolbar", $this->m_ToolBar->render());
      
      //print_r ($this->m_SearchBox->render());

      if ($dispmode->m_DataFormat == "array") // if dataFormat is array, call array render function
         $smarty->assign_by_ref("fields", $this->renderArray());
      else if ($dispmode->m_DataFormat == "table") // if dataFormat is table, call table render function.
      {
         $smarty->assign_by_ref("table", $this->renderTable());
         $smarty->assign_by_ref("formobj", $this);
      }

      $smarty->assign_by_ref("navbar", $this->m_NavBar->render());

	   return $smarty->fetch(BizSystem::getTplFileWithPath($dispmode->m_TemplateFile, $this->m_Package));
	}

   /**
    * Render form as array format using array template
    * @return string 1d array
    */
   protected function renderArray()
   {
      if ($this->m_Mode != MODE_Q && $this->m_DataObjName) {
         $this->m_Range = 1;
         if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
            return $this->processDataObjError($ok);
      }

      $columns = $this->m_RecordRow->renderColumn();
      foreach($columns as $key=>$val) {
         $fields[$key]["label"] = $val;
         $fields[$key]["required"] = $this->GetControl($key)->m_Required;
         $fields[$key]["description"] = $this->GetControl($key)->m_Description;
         $fields[$key]["value"] = $this->GetControl($key)->m_Value;	 
      }

      $controls = $this->m_RecordRow->render();

      foreach($controls as $key=>$val) {
         $fields[$key]["control"] = $val;
      }

      return $fields;
   }

   /**
    * Render form as table format using table template
    * @return string 2d array
    */
   protected function renderTable()
   {
      if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
         return $this->processDataObjError($ok);

      $records = array();
      $records[] = $this->m_RecordRow->renderColumn();
      $counter = 0;
      while (true) {
         if ($this->m_Range != null && $this->m_Range > 0 && $counter > $this->m_Range)
            break;

         $arr = $resultRecords[$counter];
         
         if (!$arr)
            break;
         $this->m_RecordRow->setRecordArr($arr);
         $tblRow = $this->m_RecordRow->render();
         $records[] = $tblRow;
         $counter++;
      }
      return $records;
   }

}

?>
