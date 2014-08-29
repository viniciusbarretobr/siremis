<?php
/**
 * BizFormPopup class - extension of BizForm to support field selection from a popup form
 * 
 * @package BizView
 */
class BizFormPopup extends BizForm 
{
   public $m_PrtFormCtrlName = "";
   
   /**
    * BizForm::GetSessionContext() - Retrieve Session data of this object
    *
    * @param SessionContext $sessCtxt
    * @return void
    */
   public function getSessionVars($sessCtxt)
   {
      parent::getSessionVars($sessCtxt);
      $sessCtxt->getObjVar($this->m_Name, "PrtFormCtrlName", $this->m_PrtFormCtrlName);
   }
   
   /**
    * BizForm::SetSessionContext() - Save Session data of this object
    *
    * @param SessionContext $sessCtxt
    * @return void
    */
   public function setSessionVars($sessCtxt)
   {
      parent::setSessionVars($sessCtxt);
      $sessCtxt->setObjVar($this->m_Name, "PrtFormCtrlName", $this->m_PrtFormCtrlName);
   }
   
   /**
    * Get view object of the popup form
    * @return BizView BizView instance
    */
   public function getViewObject() {
      // generate an xml attribute array for a dynamic bizview
      $xmlArr = BizView::GetPopupViewXML($this->m_Package, $this->m_Name);
      // create a BizViewPopup with the xml array
      global $g_BizSystem;
      $popupView = $g_BizSystem->getObjectFactory()->createObject("DynPopup",$xmlArr);
      return $popupView;
   }
	
	/**
    * Close the popup window
    * 
    * @return void
    */
	public function close()
	{
	   global $g_BizSystem;
	   $sessCtxt = $g_BizSystem->getSessionContext();
	   $this->ClearSessionVars($sessCtxt);
	   // clear the object sessio vars
	   return BizSystem::clientProxy()->closePopup();
	}
	
	/**
	 * Refresh parent (who trigger this popup window) form
	 * @return void
	 */
	public function RefreshParent()
	{
	   global $g_BizSystem;
      $prtForm = BizSystem::getObject($this->m_ParentFormName);
      return $prtForm->rerender();
	}
	
	/**
    * Join a record (popup) to parent form
    * 
    * @return void
    */
	public function joinToParent()
   {
      global $g_BizSystem;
      $prtForm = BizSystem::getObject($this->m_ParentFormName);
      $rec = $this->getActiveRecord();
      $updArray = array();
      
      // get the picker map of the control
      $pickerMap = $prtForm->GetControl($this->m_PrtFormCtrlName)->m_PickerMap;
      if ($pickerMap) {
         $pickerList = $this->_parsePickerMap($pickerMap);
         foreach ($pickerList as $ctrlPair) {
            $this_ctrl = $this->GetControl($ctrlPair[1]);
            if (!$this_ctrl)
               continue;
            $this_ctrl_val = $this_ctrl->getValue();
            $other_ctrl = $prtForm->GetControl($ctrlPair[0]);
            if ($other_ctrl)
               $updArray[$other_ctrl->m_Name] = $this_ctrl_val;
         }
      }
      else {
         // - set up the new record to be the active record?
         $retRecord = $prtForm->getDataObj()->joinRecord($this->getDataObj());
         /*
         // update the parent form fields on UI
         // !!! in case of new record, active record is not the new one 
         $rec = $prtForm->getActiveRecord();
         foreach ($retRecord as $fld=>$val) {
            $rec[$fld] = $val;
         }
         $prtForm->UpdateActiveRecord($rec);
         
         // !!! rerender parent form will lose the user input on parent form
         return $prtForm->rerender();
         */
         foreach ($retRecord as $fld=>$val) {
            $ctrl = $prtForm->m_RecordRow->GetControlByField($fld);
            if ($ctrl)
               $updArray[$ctrl->m_Name] = $val;
         }
      }
      BizSystem::clientProxy()->updateFormElements($prtForm->m_Name, $updArray);
      return;
   }
   
   /**
    * Parse Picker Map into an array
    * 
    * @param string $pickerMap pickerMap defined in metadata
    * @return array picker map array
    */
   private function _parsePickerMap($pickerMap)
   {
      $returnList = array();
      $pickerList = explode(",", $pickerMap);
      foreach ($pickerList as $pair)
      {
         $ctrlMap = explode(":", $pair);
         $ctrlMap[0] = trim($ctrlMap[0]);
         $ctrlMap[1] = trim($ctrlMap[1]);
         $returnList[] = $ctrlMap;
      }
      return $returnList;
   }
   
   /**
    * Add a record (popup) to the parent form.
    * M-M or M-1/1-1 popup OK button to add a record (popup) to the parent form
    * 
    * @return void
    */
   // todo: support multiple records
   public function addToParent()
   {
      global $g_BizSystem;
      
      // todo: if grandparent's mode is new, commit the new record first
      
      $prtForm = BizSystem::getObject($this->m_ParentFormName);
      $rec = $this->getActiveRecord();
      // add record to parent form's dataobj who is M-M or M-1/1-1 to its parent dataobj
      $ok = $prtForm->getDataObj()->addRecord($this->m_ActiveRecord, $bPrtObjUpdated);
      if (!$ok) 
         return $prtForm->processDataObjError($ok);

      $this->close();
      
      $html = "";
      // rerender parent form's driving form (its field is updated in M-1 case)
      if ($bPrtObjUpdated) { 
         $prt_prtForm = BizSystem::getObject($prtForm->GetParentForm());
         //$prt_prtForm->UpdateActiveRecord($prt_prtForm->getDataObj()->GetRecord(0));
         $html = $prt_prtForm->rerender();
      }
      // rerender the parent form
      // synch form with data
      //$prtForm->UpdateActiveRecord($prtForm->getDataObj()->GetRecord(0));
	   return $html . $prtForm->rerender();
   }
}
?>