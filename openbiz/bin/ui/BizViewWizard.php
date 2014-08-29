<?PHP
/**
 * BizViewWizard class - BizViewWizard is the class that controls the wizard forms
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class BizViewWizard extends BizView
{
   protected $m_WizardFormsData = array();  // should be saved in session
   protected $m_WizardFormIndex = 0;
   protected $m_DataObjectsInfo = array();   // keep track of DO's isQueried, isCommitted flags
   protected $m_DataObjectsData = array();

   public function __construct(&$xmlArr)
   {
      parent::__construct($xmlArr);
   }

   public function getSessionVars($sessCtxt)
	{
	   $sessCtxt->getObjVar($this->m_Name, "WizardFormsData", $this->m_WizardFormsData);
	   $sessCtxt->getObjVar($this->m_Name, "WizardFormIndex", $this->m_WizardFormIndex);
	   $sessCtxt->getObjVar($this->m_Name, "DataObjectsData", $this->m_DataObjectsData);
	   $sessCtxt->getObjVar($this->m_Name, "DataObjectsInfo", $this->m_DataObjectsInfo);
	}

	public function setSessionVars($sessCtxt)
	{
	   $sessCtxt->setObjVar($this->m_Name, "WizardFormsData", $this->m_WizardFormsData);
	   $sessCtxt->setObjVar($this->m_Name, "WizardFormIndex", $this->m_WizardFormIndex);
	   $sessCtxt->setObjVar($this->m_Name, "DataObjectsData", $this->m_DataObjectsData);
	   $sessCtxt->setObjVar($this->m_Name, "DataObjectsInfo", $this->m_DataObjectsInfo);
	}

   /**
    * Do not initiate the all forms
    */
   protected function initAllForms() {}

   /**
    * Render wizard form
    * @param string $formName name of the form
    * @param boolean $isFirstTime true if the form is rendered for the first time
    * @return mixed html content for first time render, void otherwise 
    */
   public function renderWizardForm($formName, $isFirstTime=false)
   {
      global $g_BizSystem;
      $smarty = BizSystem::getSmartyTemplate();

      $formobj = BizSystem::getObject($formName);

      if ($formobj->m_DataObjName && !key_exists($formobj->m_DataObjName, $this->m_DataObjectsData))
         $this->setDataObjState($formobj->m_DataObjName, "");

      $htmlContainer = "\n<div id='" . $formobj->m_Name . "_container'>\n" . $formobj->render() . "\n</div>\n";
      $newClntObj = "NewObject('" . $formobj->m_Name . "','" . $formobj->m_jsClass . "');";
      if ($isFirstTime)
      {
         $sHTML = "\n<script>\n" . $newClntObj . "\n</script>\n" . $htmlContainer;
         return $sHTML;
      }
      else   // call clientproxy redrawForm
      {
         BizSystem::clientProxy()->runClientScript($newClntObj);
         BizSystem::clientProxy()->redrawForm($this->m_Name, $htmlContainer);
         return;
      }
   }

   /**
    * Get the form name of the current wizard page
    * @return string name of the current form
    */
   public function GetCurWizardForm()
   {
      $formName = $this->m_MetaChildFormList[$this->m_WizardFormIndex]["FORM"];
      return $this->prefixPackage($formName);
   }

   /**
    * Get the form name of the next wizard page
    * @return string name of the form in next wizard page
    */
   public function GetNextWizardForm()
   {
      $this->m_WizardFormIndex++;
      if ($this->m_WizardFormIndex >= count($this->m_MetaChildFormList))
         $this->m_WizardFormIndex = count($this->m_MetaChildFormList) - 1;
      $formName = $this->m_MetaChildFormList[$this->m_WizardFormIndex]["FORM"];
      return $this->prefixPackage($formName);
   }

   /**
    * Get the form name of the previous wizard page
    * @return string name of the form in previous wizard page
    */
   public function GetPrevWizardForm()
   {
      $this->m_WizardFormIndex--;
      if ($this->m_WizardFormIndex < 0)
         $this->m_WizardFormIndex = 0;
      $formName = $this->m_MetaChildFormList[$this->m_WizardFormIndex]["FORM"];
      return $this->prefixPackage($formName);
   }

   /**
    * Get dataobject state. Wizard form can set data object state in the view session
    * @param string $dataobjName dataobject name
    */
   public function getDataObjState($dataobjName)
   {
      return $this->m_DataObjectsInfo[$dataobjName];
   }

   /**
    * Set dataobject state. Wizard form can set data object state in the view session
    * @param string $dataobjName dataobject name
    * @param mixed $state state variable
    * @return mixed dataobject state
    */
   public function setDataObjState($dataobjName, $state)
   {
      return $this->m_DataObjectsInfo[$dataobjName] = $state;
   }

   /**
    * Get dataobject field data
    * @param string $dataobjName dataobject name
    * @param string $fldName dataobject field name
    * @return mixed dataobject field value
    */
   public function getDataObjectsData($dataobjName, $fldName)
   {
      return $this->m_DataObjectsData[$dataobjName][$fldName];
   }

   /**
    * Set dataobject field value
    * @param string $dataobjName dataobject name
    * @param array $recArray dataobject record array
    * @return void
    */
   public function setDataObjectsData($dataobjName, &$recArray)
   {
      foreach ($recArray as $fldName=>$fldValue)
         $this->m_DataObjectsData[$dataobjName][$fldName] = $fldValue;
   }

   /**
    * Get wizard form data
    * @param string $wizardForm form name
    * @param string $controlName form control name
    * @return mixed form control value if controlName is given, record otherwise
    */
   public function GetWizardFormData($wizardForm, $controlName=null)
   {
      if ($controlName == null)
         return $this->m_WizardFormsData[$wizardForm];
      return $this->m_WizardFormsData[$wizardForm][$controlName];
   }

   /**
    * Validate wizard form data
    * @param string $wizardForm form name
    * @param array $dataArray form data array
    * @return boolean true if validation passes
    */
   public function validateWizardFormData($dataobjName, $dataArray)
   {
      global $g_BizSystem;
      $dataObj = BizSystem::getObject($dataobjName);
      $dataObj->m_BizRecord->setInputRecord($dataArray);
      return $dataObj->validateInput();
   }

   /**
    * Set wizard form data
    * @param string $wizardForm form name
    * @param array $dataArray form data array
    * @return void
    */
   public function SetWizardFormData($wizardForm, &$dataArray)
   {
      foreach ($dataArray as $ctrlName=>$ctrlValue)
         $this->m_WizardFormsData[$wizardForm][$ctrlName] = $ctrlValue;
   }

   /**
    * Save wizard data into database or other storage
    * @return void
    */
   public function commitWizardData()
   {
      // commit all dataobjects of wizard forms
      global $g_BizSystem;

      foreach($this->m_DataObjectsData as $dataobjName=>$dataArray)
      {
         $dataobj = BizSystem::getObject($dataobjName);
         //Reload dataArray to account for new ObjRef generated values
         $dataArray = $this->m_DataObjectsData[$dataobjName];

         if ($this->m_DataObjectsInfo[$dataobjName] == "IS_CREATED") {
         	//Create an empty array before inserting data from wizard
         	$newRec = $dataobj->newRecord();
         	foreach ($dataArray as $key => $field) { if ($field != null) $newRec[$key] = $field; }               
         	$dataobj->insertRecord($dataArray);
         } else {
            $dataobj->updateRecord($dataArray, $oldRec);
         }
         $this->_updateReferences($dataobj, $dataArray);
      }
   }

   /**
    * Update data object reference objects
    */
   private function _updateReferences($dataObj, $dataArray) {
      //Check for object references
      $objRef = $dataObj->m_ObjReferences;
      foreach($this->m_DataObjectsData as $dataobjName=>$someArray)
      {
         $match = $objRef->get($dataobjName);
         if($match) {
            $this->m_DataObjectsData[$dataobjName][$match->m_Column] = $dataArray[$match->m_FieldRef];
         }

      }
   }

  public function renderStepListBar($bReRender = false) {
		$formName = $this->GetCurWizardForm();		
		if(count($this->m_MetaChildFormList)){
			if($bReRender==false){
  				$sHTML.="<div id=\"steplist_bar\">\n";
  			}
			$sHTML.="<ol class=\"steplist_bar\">\n";
			foreach($this->m_MetaChildFormList as $ctrl){
				if($ctrl['FORM']===$formName){
					$sHTML.="\t<li><strong>".$ctrl['DESCRIPTION']."</strong></li>\n";	
				}else{
					$sHTML.="\t<li>".$ctrl['DESCRIPTION']."</li>\n";
				}
			}
			$sHTML.="</ol>\n";	
			if($bReRender==false){
  				$sHTML.="</div>\n";
  			}
	 		return $sHTML;	
		}else{
			return false;
		}
  }

   /**
    * Render progress bar of the wizard. Not implemented yet.
    */
   public function renderProgressBar($bReRender = false) {
		$formName = $this->GetCurWizardForm();	
		$total_step = count($this->m_MetaChildFormList);
						
		for($i=0; $i<count($this->m_MetaChildFormList);$i++){
			$ctrl=$this->m_MetaChildFormList[$i];
			 
			if($ctrl['FORM']===$formName){
				$current_step= $i+1;
				break;
			}
			
		}
		if($bReRender==false){
			$sHTML.="<div id=\"progress_bar\">\n";
		}		
		$sHTML.="$current_step/$total_step \n";
		if($bReRender==false){
  			$sHTML.="</div>\n";
  		}		
 		return $sHTML;	
		
   }

   /**
    * Render the wizard view
    * @return mixed either print html content, or return html content
    */
   public function render($bReRender=false, $smarty=false)
   {
	   if($smarty == false)
         $smarty = BizSystem::getSmartyTemplate();
      
	   global $g_BizSystem;
	   
      $this->setClientScripts(); 
	   
      // render progress bar

      // render only current wizard form, not all forms
      $formName = $this->GetCurWizardForm();
      // ouput the form into the wizard container
      $sHTML = $this->renderWizardForm($formName, true);
      $controls[] = "<div id='" . $this->m_Name . "'>" . $sHTML . "</div>\n";

	  //added by Jixian , Render progress bar and step list bar
	  $sProgressBar = $this->renderProgressBar($bReRender);
	  $sStepListBar = $this->renderStepListBar($bReRender);
      
      //Add any required scripts that will be needed in future forms
      foreach ($this->m_MetaChildFormList as $form) {
         global $g_BizSystem;
         $formobj = BizSystem::getObject($form['FORM']);
         $formobj->SetFieldScripts();
      }      
      
      // add clientProxy scripts 
      if ($bReRender == false)
      {
         $smarty->assign("scripts", BizSystem::clientProxy()->getAppendedScripts());
         $smarty->assign("style_sheets", BizSystem::clientProxy()->getAppendedStyles());         
      }        

      $smarty->assign_by_ref("view_description", $this->m_Description);

      $smarty->assign_by_ref("controls", $controls);      
      
      //added by Jixian , Render progress bar and step list bar
      $smarty->assign_by_ref("progress_bar", $sProgressBar);
      $smarty->assign_by_ref("steplist_bar", $sStepListBar);
      
	  if($bReRender){
	  	BizSystem::clientProxy()->redrawForm('steplist_bar', $sStepListBar);
	  	BizSystem::clientProxy()->redrawForm('progress_bar', $sProgressBar);	  	
	  }
	  
      if ($this->m_ConsoleOutput)
         $smarty->display(BizSystem::getTplFileWithPath($this->m_Template, $this->m_Package));
      else
         return $smarty->fetch(BizSystem::getTplFileWithPath($this->m_Template, $this->m_Package));
   }
}

?>
