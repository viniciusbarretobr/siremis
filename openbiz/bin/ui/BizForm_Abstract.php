<?php
/**
 * BizForm_Abstract class - contains form object metadata functions
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

/* define modes constants */
define ("MODE_R", "READ");   //READ
define ("MODE_N", "NEW");    //NEW
define ("MODE_E", "EDIT");    //EDIT
define ("MODE_Q", "QUERY");   //QUERY

include_once(OPENBIZ_BIN."/ui/private/RecordRow.php");
include_once(OPENBIZ_BIN."/ui/private/DisplayMode.php");
include_once(OPENBIZ_BIN."/ui/private/ToolBar.php");
include_once(OPENBIZ_BIN."/ui/private/NavBar.php");
include_once(OPENBIZ_BIN."/ui/HTMLControl.php");
include_once(OPENBIZ_BIN."/ui/FieldControl.php");

abstract class BizForm_Abstract extends MetaObject implements iSessionObject
{
   // metadata vars are public, necessary for metadata inheritance
   public $m_Title;
   public $m_Description;
   public $m_jsClass;
   public $m_DataObjName;
   public $m_InheritFrom;
   public $m_Height = 640;
   public $m_Width = 800;
   public $m_Range = 10;
   public $m_FullPage = "Y";
   public $m_BaseSearchRule = null;
   public $m_SearchRule = null;
   public $m_DisplayModes = null;
   public $m_RecordRow = null;
   public $m_ToolBar = null;
   public $m_NavBar = null;
   public $m_Parameters = array();
   public $m_Style=null; //Get the style of a form - jmmz

   protected $m_DataObj;
   protected $m_Mode = MODE_R;
   protected $m_SubForms = null;
   protected $m_FixSearchRule = ""; // rename to FixSearchRule which is the search rule always applying on the search

   protected $m_PostActionOff = false;
   protected $m_ParentFormName;

   protected $m_Stateless = false;

   /**
    * Initialize BizForm with xml array
    *
    * @param array $xmlArr
    * @return void
    */
   function __construct(&$xmlArr)
   {
      global $g_BizSystem;

      $this->readMetadata($xmlArr);

      $this->inheritParentObj();

      $this->SetInitialDisplayMode();
   }

   /**
    * Read Metadata from xml array
    * @param array $xmlArr
    */
   protected function readMetadata(&$xmlArr)
   {
      parent::readMetaData($xmlArr);
      $this->m_InheritFrom = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["INHERITFROM"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["INHERITFROM"] : null;
      $this->m_Title = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["TITLE"]) ? I18n::getInstance()->translate($xmlArr["BIZFORM"]["ATTRIBUTES"]["TITLE"]) : null;
      $this->m_Description = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["DESCRIPTION"]) ? I18n::getInstance()->translate($xmlArr["BIZFORM"]["ATTRIBUTES"]["DESCRIPTION"]) : null; //added by Jixian
      $this->m_SearchRule = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["SEARCHRULE"] : null;
      $this->m_BaseSearchRule = $this->m_SearchRule;
      $this->m_jsClass = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["JSCLASS"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["JSCLASS"] : null;
      $this->m_Height = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["HEIGHT"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["HEIGHT"] : null;
      $this->m_Width = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["WIDTH"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["WIDTH"] : null;
      $this->m_Range = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["PAGESIZE"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["PAGESIZE"] : null;
      $this->m_FullPage = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["FULLPAGE"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["FULLPAGE"] : null;
      $this->m_Stateless = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["STATELESS"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["STATELESS"] : null;
      $this->m_Style = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["STYLE"])?$xmlArr["BIZFORM"]["ATTRIBUTES"]["STYLE"]:'display: block'; //Get the style of a form from xml --jmmz

      $this->m_Name = $this->prefixPackage($this->m_Name);
      $this->m_DataObjName = $this->prefixPackage($xmlArr["BIZFORM"]["ATTRIBUTES"]["BIZDATAOBJ"]);

      $this->m_DisplayModes = new MetaIterator($xmlArr["BIZFORM"]["DISPLAYMODES"]["MODE"],"DisplayMode");
      $this->m_RecordRow = new RecordRow($xmlArr["BIZFORM"]["BIZCTRLLIST"]["BIZCTRL"],"FieldControl",$this);
      $this->m_ToolBar = new ToolBar($xmlArr["BIZFORM"]["TOOLBAR"]["CONTROL"],"HTMLControl",$this);
      $this->m_NavBar = new NavBar($xmlArr["BIZFORM"]["NAVBAR"]["CONTROL"],"HTMLControl",$this);
      $this->m_Parameters = new MetaIterator($xmlArr["BIZFORM"]["PARAMETERS"]["PARAMETER"],"Parameter");
   }

   /**
    * Inherit from parent object. Name, Package, Class cannot be inherited
    */
   protected function inheritParentObj()
   {
      if (!$this->m_InheritFrom) return;
      global $g_BizSystem;
      $prtObj = BizSystem::getObject($this->m_InheritFrom);

      $this->m_Title = $this->m_Title ? $this->m_Title : $prtObj->m_Title;
      $this->m_Description = $this->m_Description ? $this->m_Description : $prtObj->m_Description;
      $this->m_SearchRule = $this->m_SearchRule ? $this->m_SearchRule : $prtObj->m_SearchRule;
      $this->m_BaseSearchRule = $this->m_SearchRule;
      $this->m_jsClass = $this->m_jsClass ? $this->m_jsClass: $prtObj->m_jsClass;
      $this->m_Height = $this->m_Height ? $this->m_Height: $prtObj->m_Height;
      $this->m_Width = $this->m_Width ? $this->m_Width: $prtObj->m_Width;
      $this->m_Range = $this->m_Range ? $this->m_Range: $prtObj->m_Range;
      $this->m_FullPage = $this->m_FullPage ? $this->m_FullPage: $prtObj->m_FullPage;
      $this->m_Stateless = $this->m_Stateless ? $this->m_Stateless: $prtObj->m_Stateless;
      $this->m_BizObjName = $this->m_BizObjName ? $this->m_BizObjName: $prtObj->m_BizObjName;

      $this->m_DisplayModes->merge($prtObj->m_DisplayModes);
      $this->m_RecordRow->merge($prtObj->m_RecordRow, $this->m_Name);
      foreach ($this->m_RecordRow as $ctrl) $ctrl->AdjustBizFormName($this->m_Name);
      $this->m_ToolBar->merge($prtObj->m_ToolBar, $this->m_Name);
      foreach ($this->m_ToolBar as $ctrl) $ctrl->AdjustBizFormName($this->m_Name);
      $this->m_NavBar->merge($prtObj->m_NavBar, $this->m_Name);
      foreach ($this->m_NavBar as $ctrl) $ctrl->AdjustBizFormName($this->m_Name);
      $this->m_Parameters->merge($prtObj->m_Parameters);
   }

   /**
    * Get session variables
    * @param SessionContext sessCtxt
    */
   public function getSessionVars($sessCtxt) {}

   /**
    * Set session variables
    * @param SessionContext sessCtxt
    */
   public function setSessionVars($sessCtxt) {}

   /**
    * Set displaymode to READ first, otherwise set to first valid mode
    * @return void
    */
   protected function SetInitialDisplayMode()
   {
      $valid_mode = $this->m_DisplayModes->get("READ");
      if ($valid_mode === null) {
         $this->m_DisplayModes->rewind();
         $valid_mode = $this->m_DisplayModes->current();
      }
      $this->m_Mode = $valid_mode->m_Name;
   }

   /**
    * Get the property of the object. Used in expression language
    * @param string $propertyName name of the property
    * @return string property value
    */
   public function getProperty($propertyName)
	{
	   $ret = parent::getProperty($propertyName);
	   if ($ret) return $ret;
	   if ($propertyName == "Title") return $this->m_Title;
	   if ($propertyName == "jsClass") return $this->m_jsClass;
	   if ($propertyName == "DataObjName") return $this->m_DataObjName;
      // get control object if propertyName is "Control[ctrlname]"
      $pos1 = strpos($propertyName, "[");
      $pos2 = strpos($propertyName, "]");
      if ($pos1>0 && $pos2>$pos1) {
         $propType = substr($propertyName, 0, $pos1);
         $ctrlname = substr($propertyName, $pos1+1,$pos2-$pos1-1);
         if ($propType == "param") {   // get parameter
            return $this->m_Parameters->get($ctrlname);
         }
         return $this->GetControl($ctrlname);
      }
	}

   /**
    * get obejct parameter value
    * @param string $paramName name of the parameter
    * @return string parameter value
    */
	public function getParameter($paramName)
	{
	   //return $this->m_Parameters[$paramName]->m_Value;
	   return $this->m_Parameters->get($paramName)->m_Value;
	}


	/**
    * Get display mode object
    *
    * @return DisplayMode
    */
   final public function GetDisplayMode()
   {
      if (($dispmode = $this->m_DisplayModes->get($this->m_Mode)))
         return $dispmode;
      foreach ($this->m_DisplayModes as $dispmode) {
         return $dispmode;
      }
      $errmsg = BizSystem::getMessage("BFM_ERROR_INVALID_DISPMODE",array($this->m_Name));
      trigger_error($errmsg, E_USER_ERROR);
   }
   /**
    * Set display mode as given mode
    *
    * @param string $mode - MODE_R|MODE_N|MODE_E|MODE_Q
    * @return void
    */
   public function SetDisplayMode($mode)
   {
      if (!$mode AND !$this->m_Mode) {
         $mode = MODE_R;
      } elseif (!$mode) {
         $mode=$this->m_Mode;
      };
      $this->m_Mode = $mode;
      $dispmode = $this->m_DisplayModes->get($this->m_Mode);
      $this->m_RecordRow->SetMode($mode, $dispmode->m_DataFormat);
      $this->m_ToolBar->SetMode($mode, $dispmode->m_DataFormat);
      $this->m_NavBar->SetMode($mode, $dispmode->m_DataFormat);
   }

	/**
    * Get object instance of BizDataObj defined in its metadata file
    *
    * @return BizDataObj
    */
	final public function getDataObj()
	{
	   global $g_BizSystem;
	   if (!$this->m_DataObj) {
	     if ($this->m_DataObjName)
           $this->m_DataObj = BizSystem::getObject($this->m_DataObjName);
           if($this->m_DataObj)
            $this->m_DataObj->m_BizFormName = $this->m_Name;
	   }
      return $this->m_DataObj;
	}

	/**
	 * Set data object
	 * @param BizDataObj $dataObj
	 */
	final public function setDataObj($dataObj)
	{
	   $this->m_DataObj = $dataObj;
	}

	/**
    * Handle the error from DataObj method, report the error as an alert window
    *
    * @param int $errCode
    * @return string
    */
	public function processDataObjError($errCode=0)
	{
	   global $g_BizSystem;
      $errorMsg = $this->getDataObj()->getErrorMessage();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "DataObj error = ".$errorMsg);
      BizSystem::clientProxy()->showErrorMessage($errorMsg);
	}

	/**
    * Handle the exception from DataObj method, report the error as an alert window
    *
    * @param int $errCode
    * @return string
    */
	public function processBDOException($e)
	{
	   global $g_BizSystem;
      $errorMsg = $e->getMessage();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "DataObj error = ".$errorMsg);
      //BizSystem::clientProxy()->showErrorMessage($errorMsg);
      BizSystem::clientProxy()->showErrorMessage($errorMsg);
	}

	/**
    * Set the search rule of the bizform, this search rule will apply on its bizdataobj
    *
    * @param string $rule - search rule has format "[fieldName1] opr1 Value1 AND/OR [fieldName2] opr2 Value2"
    * @param boolean $overwrite specify if this rule should overwrite any existing rule
    * @return void
    */
   public function setSearchRule($rule = null, $overwrite=false)
	{
	   if (!$rule) {
         return;
      } elseif (!$this->m_SearchRule or $overwrite == true) {
         $this->m_SearchRule = $rule;
      } elseif (strpos($this->m_SearchRule, $rule) === false) {
            $this->m_SearchRule .= " AND " . $rule;
      }
      echo $this->m_SearchRule;

      BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name." SetSearch() ". $this->m_SearchRule);
	}

   /**
    * Set the dependent search rule of the bizform, this search rule will apply on its bizdataobj.
    * The dependent search rule (session var) will always be with bizform until it get set to other value
    *
    * @param string $rule - search rule has format "[fieldName1] opr1 Value1 AND/OR [fieldName2] opr2 Value2"
    * @return void
    */
	public function setFixSearchRule($rule = null, $cleanActualRule = FALSE)
	{
		
	  if($cleanActualRule) {
	  	$this->m_FixSearchRule = "";
	  } else {
	  	// We don't touch the actual FixSearchRule
	  }
	  
      if ($this->m_FixSearchRule && $rule)
      {
         if (strpos($this->m_FixSearchRule, $rule) === false)
            $this->m_FixSearchRule = $this->m_FixSearchRule . " AND " . $rule;
      }
      if (!$this->m_FixSearchRule && $rule)
         $this->m_FixSearchRule = $rule;
	}

   /**
    * Set the sub forms of this form. This form is parent of other forms
    *
    * @param string $subForms - sub controls string with format: ctrl1;ctrl2...
    * @return void
    */
	final public function setSubForms($subForms)
   {
      // sub controls string with format: ctrl1;ctrl2...
      if (!$subForms || strlen($subForms) < 1) {
         $this->m_SubForms = null;
         return;
      }
      $subFormArr = explode(";", $subForms);
      unset($this->m_SubForms);
      foreach ($subFormArr as $subForm) {
         $this->m_SubForms[] = $this->prefixPackage($subForm);
      }
   }

   /**
    * Get BizView object
    * @return BizView view object
    */
   public function getViewObject() {
      global $g_BizSystem;
      $viewName = $g_BizSystem->getCurrentViewName();     
      $viewobj = BizSystem::getObject($viewName);
      return $viewobj;
   }

   /**
    * Get sub forms objects
    * @return array array of BizForm objects
    */
   public function getSubForms() { return $this->m_SubForms; }

   /**
    * Get parent form name
    * @return string parent form name
    */
   public function GetParentForm() { return $this->m_ParentFormName; }

   /**
    * Set parent form name
    * @param string $prtFormName parent form name
    */
   public function setParentForm($prtFormName) { $this->m_ParentFormName = $prtFormName; }

   /**
    * Get a control (FieldControl or HTMLControl) object
    *
    * @param string $ctrlname - name of the control
    * @return HTMLControl or FieldControl
    */
	public function GetControl($ctrlname)
	{
	   if ($this->m_RecordRow->get($ctrlname)) return $this->m_RecordRow->get($ctrlname);
	   if ($this->m_ToolBar->get($ctrlname)) return $this->m_ToolBar->get($ctrlname);
	   if ($this->m_NavBar->get($ctrlname)) return $this->m_NavBar->get($ctrlname);
	}

   /**
    * Get an array of control names based on the supplied mode, if no mode is supplie use the bizform's current displaymode
    * @param string $mode - filter for this mode
    * @return Array of Control objects
    */
	public function GetDisplayControls($mode=null)
	{
	   if ($mode){
	      $old_mode = $this->m_Mode;
	      $this->SetDisplayMode($mode);
	   }
	      
	   $ctr_List = $this->m_RecordRow->GetSortControlKeys();
	   foreach ($ctr_List as $value) {
	      $ctrl = $this->m_RecordRow->get($value);
	      if  ($ctrl->canDisplayed()) {
	         $displayControls[] = $value;
	      }
	   }
	   
	   if ($mode)
	      $this->SetDisplayMode($old_mode);
	      	   
      return $displayControls;
	}

   /**
    * Invoke service method, this bizform name is passed to the method
    *
    * @param string $class
    * @param string $method
    * @return mixed - return value of the service method
    */
   public function callService($class, $method)
   {
      $serviceObj = BizSystem::getService($class);
      return $serviceObj->$method($this->m_Name);
   }

	/**
	 * Render the form in html
	 * @return string html
	 */
	abstract public function render();

}
?>
