<?php
/**
 * BizFormWizard class - extension of BizForm to support wizard form
 * 
 * @package BizView
 */
class BizFormWizard extends BizForm 
{
   protected $m_FormData = null;
   
   public function getSessionVars($sessCtxt)
	{
      //$sessCtxt->getObjVar($this->m_Name, "FormData", $this->m_FormData);
	}

	public function setSessionVars($sessCtxt)
	{
      //$sessCtxt->setObjVar($this->m_Name, "FormData", $this->m_FormData);
	}
   
   /**
    * Go to next wizard page
    * @param boolean $commit true if need to commit current form data
    * @return void
    */
   public function goNext($commit=false) //- call RenderNextWizardForm() by default
   {
      $viewobj = $this->getViewObject();
      $nextFormName = $viewobj->GetNextWizardForm();
      if ($nextFormName != $this->m_Name) {
         $this->SetFormData();
         $viewobj->renderWizardForm($nextFormName);
      }
   }
   
   /**
    * Go to previous wizard page
    * @return void
    */
   public function GoPrev() //- call RenderPrevWizardForm() by default
   {
      $viewobj = $this->getViewObject();
      $prevFormName = $viewobj->GetPrevWizardForm();
      if ($prevFormName != $this->m_Name) {
         $this->SetFormData();
         $viewobj->renderWizardForm($prevFormName);
      }
   }
   
   /**
    * Finish the wizard process
    * @return void
    */
   public function doFinish() //- call FinishWizard() by default
   {
      global $g_BizSystem;

      $postAction = $this->GetPostAction();
      if (!$postAction) {
         BizSystem::clientProxy()->showErrorMessage("Your wizard process cannot be finished due to invalid PostAction of the finish action");
         return;
      }
      else {
         // commit wizard data
         $this->SetFormData();
         $this->getViewObject()->commitWizardData();
         
         $this->HandlePostAction($postAction);
         return;
      }
   }
   
   /**
    * Cancel the wizard process
    * @return void
    */
   public function doCancel() //- call CancelWizard() by default
   {
      global $g_BizSystem;
      // get postaction of the cancel button
      $postAction = $this->GetPostAction();
      if (!$postAction) {
         BizSystem::clientProxy()->showErrorMessage("Your wizard process cannot be canceled due to invalid PostAction of the cancel action");
         return;
      }
      else {
         $this->HandlePostAction($postAction);
         return;
      }
   }
   
   /**
    * Get form data from wizard view session and set data to form controls
    * @return void
    */
   protected function GetFormData()
   {
      $this->m_FormData = $this->getViewObject()->GetWizardFormData($this->m_Name);
      $viewobj = $this->getViewObject();
      // if Formdata is empty, get data from dataobj
      if (!$this->m_FormData) {
         foreach ($this->m_RecordRow as $fldCtrl) {
            if ($fldCtrl->m_BizFieldName) { // if control based on field
               if ($viewobj->getDataObjState($this->m_DataObjName) != "IS_QUERIED") {
                  $ok = $this->_run_search($resultRecords);
                  if (!$ok) 
                     return $this->processDataObjError($ok);
                  $recArray = $resultRecords[0];
                  $viewobj->setDataObjState($this->m_DataObjName, "IS_QUERIED");
                  $viewobj->setDataObjectsData($this->m_DataObjName, $recArray);
               }
               $this->m_FormData[$fldCtrl->m_Name] = $viewobj->getDataObjectsData($this->m_DataObjName, $fldCtrl->m_BizFieldName);
            }
            else
               $this->m_FormData[$fldCtrl->m_Name] = "";
         }
         //$this->$viewobj->SetWizardFormData($this->m_Name, $this->m_FormData);
      }
      
      foreach ($this->m_RecordRow as $fldCtrl) {
         $fldCtrl->setValue($this->m_FormData[$fldCtrl->m_Name]);
      }
   }
   
   /**
    * Set form data to wizard view session
    * @return void
    */
   protected function SetFormData()
   {
      global $g_BizSystem;
      $recArray = array();
      foreach ($this->m_RecordRow as $fldCtrl) {
         $value = BizSystem::clientProxy()->getFormInputs($fldCtrl->m_Name);
         $this->m_FormData[$fldCtrl->m_Name] = $value;
         if ($fldCtrl->m_BizFieldName)
            $recArray[$fldCtrl->m_BizFieldName] = $value;
      }
      $this->getViewObject()->SetWizardFormData($this->m_Name, $this->m_FormData);
      $this->getViewObject()->setDataObjectsData($this->m_DataObjName, $recArray);
   }
   
   /**
    * Render the form 
    */
   public function render()
   {
      return $this->renderHTML();
   }
   
   /**
    * Render form data in an array
    * @return array array of the control html content
    */
   protected function renderArray()
   {
      $columns = $this->m_RecordRow->renderColumn();
      foreach($columns as $key=>$val)
         $fields[$key]["label"] = $val;

      $this->GetFormData();
      $controls = $this->m_RecordRow->render(); 
      if ($this->CanShowData()) {
         foreach($controls as $key=>$val) {
            $fields[$key]["control"] = $val;
         }
      }
      return $fields;
   }
   
   /**
    * Get control value
    * @parame string $controlName name of the control
    * @return mixed value of the control
    */
   public function GetControlValue($controlName) 
   {
      return $this->m_FormData[$controlName];
   }
}
?>