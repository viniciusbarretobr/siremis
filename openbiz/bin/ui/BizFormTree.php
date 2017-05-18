<?php
/**
 * BizFormTree class - extension of BizForm to support field selection from a popup form
 * 
 * @package BizView
 */
class BizFormTree extends BizForm 
{
   private $_parents = array();
   private $_directParentId = "";
   
   public function getSessionVars($sessCtxt)
	{
      $sessCtxt->getObjVar($this->m_Name, "DirectParent", $this->_directParentId);
      parent::getSessionVars($sessCtxt);
	}
	
   public function setSessionVars($sessCtxt)
	{
      $sessCtxt->setObjVar($this->m_Name, "DirectParent", $this->_directParentId);
      parent::setSessionVars($sessCtxt);
	}
   
   /**
    * Find all parents of a record
    * @param array $rec BizDataObj record array
    * @param boolean $includeSelf true if include this record in the return array
    * @return array array of the parent records
    */
   private function _getAllLevelParents($rec, $includeSelf=false)
   {
      $_pid = $rec['PId'];
      $parents = array();
      if ($includeSelf)
         $parents[] = $rec;
      do
      {
         $prt = $this->_getNodeRecord($_pid);
         if ($prt == null)
            break;
         else {
            $parents[] = $prt;
         }
         if ($prt['PId'] == null || $prt['PId'] == '')
            break;
         $_pid = $prt['PId'];
      } while ($pid == "");
      return $parents;
   }
   
   /**
    * Get the node record on given id field
    * @param string $id id value
    * @return array BizDataObj record array
    */
   private function _getNodeRecord($id)
   {
      $recordList = $this->getDataObj()->directFetch("[Id]='$id'");
      if (count($recordList) == 1)
         return $recordList[0];
      return null;
   }
   
   /**
    * Render all children records of a given record
    * @param string $id id value
    * @return void 
    */
   public function ListChildren($id)
   {
      $rec = $this->_getNodeRecord($id);
      $parents = $this->_getAllLevelParents($rec);
      $this->_parents[] = $rec;
      foreach ($parents as $prt)
         $this->_parents[] = $prt;
      
      $this->_directParentId = $rec["Id"];
      
      $this->m_SearchRule = "[PId] = '$id'";
      $this->m_ClearSearchRule = true;
      $this->m_CursorIndex = 0;
      return $this->rerender();
   }
   
   /*
    * Render all sibling records of a given record
    * @param string $id id value
    * @return void 
    */
   public function ListSiblings($id)
   {
      $rec = $this->_getNodeRecord($id);
      if ($rec['PId'] != null && $rec['PId'] != '')
         $this->_parents = $this->_getAllLevelParents($rec);
      //print_r($parents);
      $pid = (count($this->_parents) == 0) ? '' : $this->_parents[0]['Id'];
      $this->_directParentId = $pid;
      
      // set the search rule
      if ($pid == '')
         $this->m_SearchRule = "[PId] = '' or [PId] is NULL";
      else
         $this->m_SearchRule = "[PId] = '$pid'";
      $this->m_ClearSearchRule = true;
      $this->m_CursorIndex = 0;
      return $this->rerender();
   }
   
   /**
    * Create a new record by setting correct parent id
    * @return avoid
    */
   public function newRecord()
   {
      global $g_BizSystem;
      $this->SetDisplayMode(MODE_N);
      $recArr = $this->getDataObj()->newRecord();
      if (!$recArr) 
         return $this->processDataObjError();
      // add correct pid
      $recArr['PId'] = $this->_directParentId;
      $this->UpdateActiveRecord($recArr);
      return $this->rerender();
   }
   
   /** 
    * DeleteRecord() - allow delete only if no child node
    * @return avoid
    */
   public function deleteRecord()
   {
      $rec = $this->getActiveRecord();
      if (!$rec) return;
      $id = $rec['Id'];
      $recordList = $this->getDataObj()->directFetch("[PId]='$id'");
      if (count($recordList) > 0) 
      {
         global $g_BizSystem;
         $errorMsg = "Unable to delete the record that has 1 or more children nodes.";
         BizSystem::clientProxy()->showErrorMessage($errorMsg);
         return;
      }
      return parent::deleteRecord();
   }

   /**
    * Render html content of this form
    *
    * @return string - HTML text of this form's read mode
    */
   protected function renderHTML()
   {
      // TODO: need to consider history into the searchrule
      //echo "History:";
      //print_r($this->m_HistoryInfo);
      
      if ($this->_directParentId)
         $this->setSearchRule("[PId] = '".$this->_directParentId."'");
      else {
         $root_searchRule = $this->getParameter("Root_SearchRule");
         if (!$root_searchRule)
            $this->setSearchRule("[PId] = '' or [PId] is NULL");
         else
            $this->setSearchRule($root_searchRule);         
      }
      $this->m_ClearSearchRule = true;

      $dispmode = $this->GetDisplayMode();
	   $this->SetDisplayMode($dispmode->GetMode());
	   
      $smarty = BizSystem::getSmartyTemplate();
      $smarty->assign_by_ref("name", $this->m_Name);
      $smarty->assign_by_ref("title", $this->m_Title);
      $smarty->assign_by_ref("toolbar", $this->m_ToolBar->render());
      
      if ($dispmode->m_DataFormat == "array") // if dataFormat is array, call array render function
         $smarty->assign_by_ref("fields", $this->renderArray());
      else if ($dispmode->m_DataFormat == "table") // if dataFormat is table, call table render function.
         $smarty->assign_by_ref("table", $this->renderTable());
      else if ($dispmode->m_DataFormat == "block" && $dispmode->m_FormatStyle)
         $smarty->assign_by_ref("block", $this->renderFormattedTable());
      
      $smarty->assign_by_ref("navbar", $this->m_NavBar->render()); 
      
      if (count($this->_parents) == 0)
      {
         $rec = $this->getActiveRecord();
         if ($rec)
            $this->_parents = $this->_getAllLevelParents($rec);
         else
         {
            $rec = $this->_getNodeRecord($this->_directParentId);
            $this->_parents = $this->_getAllLevelParents($rec, true);
         }
      }
      $objname = $this->m_Name;
      $prts_txt = "";
      for ($i=count($this->_parents)-1; $i>=0; $i--)
      {
         $prtid = $this->_parents[$i]['Id'];
         $prtname = $this->_parents[$i]['Name'];
         if ($prts_txt == "")
            $prts_txt .= "<a href=\"javascript:CallFunction('$objname.ListSiblings($prtid))')\">$prtname</a>";
         else
            $prts_txt .= " > <a href=\"javascript:CallFunction('$objname.ListSiblings($prtid))')\">$prtname</a>";
      }
      $smarty->assign_by_ref("parents_links", $prts_txt); 
      
	   return $smarty->fetch(BizSystem::getTplFileWithPath($dispmode->m_TemplateFile, $this->m_Package))
	          . "\n" . $this->renderShortcutKeys()
	          . "\n" . $this->renderContextMenu();
   }
}
?>