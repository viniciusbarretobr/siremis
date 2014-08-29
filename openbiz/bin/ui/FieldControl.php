<?PHP
include_once("HTMLControl.php");

/**
 * FieldControl - class FieldControl is the base class of field control who binds with a bizfield
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public
 */
class FieldControl extends HTMLControl
{
    public $m_BizFieldName;
    public $m_DisplayName;
    public $m_BizFormName;
    public $m_ValuePicker = null;
    public $m_PickerMap = null;
    public $m_DrillDownLink = null;
    //public $m_Hidden = "N";
    public $m_Enabled = "Y";
    public $m_Sortable = "N";
    public $m_DataType = null;
    public $m_DataFormat = null;
    public $m_SortFlag = null;
    public $m_Order;
    public $m_DefaultValue = "";
    public $m_Description;
    public $m_ColumnStyle;

    /**
     * Initialize FieldControl with xml array
     *
     * @param array $xmlArr xml array
     * @param BizForm $formObj BizForm instance
     * @return void
     */
    function __construct(&$xmlArr, $formObj)
    {
        parent::__construct($xmlArr, $formObj);
        $this->m_BizFormName = $formObj->m_Name;
        $this->m_BizFieldName = isset($xmlArr["ATTRIBUTES"]["FIELDNAME"]) ? $xmlArr["ATTRIBUTES"]["FIELDNAME"] : null;
        $this->m_DisplayName = isset($xmlArr["ATTRIBUTES"]["DISPLAYNAME"]) ? I18n::getInstance()->translate($xmlArr["ATTRIBUTES"]["DISPLAYNAME"])  : null;
        $this->m_Description = isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
        $this->m_ValuePicker  = isset($xmlArr["ATTRIBUTES"]["VALUEPICKER"]) ? $xmlArr["ATTRIBUTES"]["VALUEPICKER"] : null;
        $this->m_PickerMap  = isset($xmlArr["ATTRIBUTES"]["PICKERMAP"]) ? $xmlArr["ATTRIBUTES"]["PICKERMAP"] : null;
        if (isset($xmlArr["ATTRIBUTES"]["DRILLDOWNLINK"]))
            $this->_setDrillDownLink ($xmlArr["ATTRIBUTES"]["DRILLDOWNLINK"]);
        $this->m_Enabled = isset($xmlArr["ATTRIBUTES"]["ENABLED"]) ? $xmlArr["ATTRIBUTES"]["ENABLED"] : null;
        $this->m_Sortable = isset($xmlArr["ATTRIBUTES"]["SORTABLE"]) ? $xmlArr["ATTRIBUTES"]["SORTABLE"] : null;
        $this->m_DataType = isset($xmlArr["ATTRIBUTES"]["DATATYPE"]) ? $xmlArr["ATTRIBUTES"]["DATATYPE"] : null;
        $this->m_Order = isset($xmlArr["ATTRIBUTES"]["ORDER"]) ? $xmlArr["ATTRIBUTES"]["ORDER"] : null;
        $this->m_DefaultValue = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTVALUE"] : null;
        $this->m_ColumnStyle = isset($xmlArr["ATTRIBUTES"]["COLUMNSTYLE"]) ? $xmlArr["ATTRIBUTES"]["COLUMNSTYLE"] : null;
        $this->m_Mode = MODE_R;

        // if no class name, add default class name. i.e. NewRecord => ObjName.NewRecord
        $this->m_ValuePicker = $this->prefixPackage($this->m_ValuePicker);

        if (!$this->m_BizFieldName)
            $this->m_BizFieldName = $this->m_Name;
    }

    /**
     * Get default value of this field
     * @return string default value
     */
    public function getDefaultValue()
    {
        if ($this->m_DefaultValue == "")
            return "";
        $formobj = $this->getFormObj();
        $defValue = Expression::evaluateExpression($this->m_DefaultValue, $formobj);
        return $defValue;
    }

    /**
     * Set drilldown link array with DilldownLink attribute
     * @param string @ddLinkString DrillDownLink attribute from Field element
     * @return avoid
     */
    private function _setDrillDownLink($ddLinkString)
    {
        // linkTo string with format:otherView,otherForm.ctrl=my_ctrl
        if (strlen($ddLinkString) < 1)
            return;
        $pos = strpos($ddLinkString, "=");
        $this->m_DrillDownLink["my_ctrl"] = substr($ddLinkString, $pos + 1, strlen($ddLinkString) - $pos);
        $other = substr($ddLinkString, 0, $pos);
        $pos = strpos($other, ",");
        $pos1 = strrpos($other, ".", $pos + 1);
        $linkView = substr($other, 0, $pos);
        $this->m_DrillDownLink["link_view"] = $this->prefixPackage($linkView);
        $linkForm = substr($other, $pos + 1, $pos1 - $pos-1);
        $this->m_DrillDownLink["link_form"] = $this->prefixPackage($linkForm);
        $this->m_DrillDownLink["link_ctrl"] = substr($other, $pos1 + 1, strlen($other) - $pos1);
    }

    /**
     * Set the sort flag of the control
     *
     * @param integer $flag 1 or 0
     * @return void
     */
    public function setSortFlag($flag=null)
    {
        $this->m_SortFlag = $flag;
    }

    /**
     * FieldControl::renderHeader() -  When render table, it return the table header; when render array, it return the display name
     *
     * @return string HTML text
     */
    public function renderHeader()
    {
        if ($this->m_Hidden == "Y")
            return null;
        if ($this->m_DataFormat != "array" && $this->m_Sortable == "Y")
        {
            //$rule = "[" . $this->m_BizFieldName . "]";
            $rule = $this->m_Name;
            $function = $this->m_BizFormName . ".SortRecord(" . $rule . ")";
            $val = "<a href=javascript:CallFunction('" . $function . "')>" . $this->m_DisplayName . "</a>";
            if ($this->m_SortFlag == "ASC")
                $val .= "<img src=\"".Resource::getImageUrl()."/up_arrow.gif\">";
            else if ($this->m_SortFlag == "DESC")
                $val .= "<img src=\"".Resource::getImageUrl()."/down_arrow.gif\">";
        } else
        {
            $val = $this->m_DisplayName;
        }
        return $val;
    }

    /**
     * FieldControl::render() - Draw the control according to the mode
     *
     * @returns stirng HTML text
     */
    public function render()
    {
        $val = $this->m_Value;
        $temp = ($this->m_FunctionType==null) ? "" : ",'".$this->m_FunctionType."'";
        if ($this->m_Image)
            $val = "<img src=\"".Resource::getImageUrl()."/".$this->m_Image."\" border=0> $val";

        // todo: don't use deperated m_Function and m_FunctionType
        if ($val!==null && $this->m_Function)
        {
            $funcExpr = Expression::evaluateExpression($this->m_Function, $this->getFormObj());
            $val = "<a href=\"javascript:CallFunction('" . $funcExpr . "'$temp)\">$val</a>";
        }

        //if ($this->m_Mode != MODE_E && $this->m_Mode != MODE_N && $this->m_Mode != MODE_Q)
        //   $tmpMode = 'READ';
        if($this->m_Mode == null)
        {
            $tmpMode = 'READ';
        } else
        {
            $tmpMode = $this->m_Mode;
        }

        if (($val===null || $val==="") && $tmpMode == MODE_R)
        {
            //$val = "&nbsp;";
            $val = "";
        }

        if ($tmpMode == MODE_R && $this->m_Link)
        {
            $link = $this->getLink();
            $val = "<a href=\"$link\">" . $val . "</a>";
        }

        if ($tmpMode == MODE_R && $this->m_DrillDownLink)
        {
            $otherCtrl = $this->getFormObj()->GetControl($this->m_DrillDownLink["my_ctrl"]);
            $this->m_DrillDownLink["my_ctrl_val"] = $otherCtrl->getValue();
            $rule = $this->m_DrillDownLink["link_form"] . "." . $this->m_DrillDownLink["link_ctrl"] . "=\'" . $this->m_DrillDownLink["my_ctrl_val"] . "\'";
            $val = "<a href=javascript:DrillDownToView('" . $this->m_DrillDownLink["link_view"] . "','$rule')>" . $val . "</a>";
        }
        if ($tmpMode == MODE_R)
            $val = nl2br($val);

        if ($tmpMode != MODE_R)
        {
            $ctrlName = $this->m_Name;
            $cType = strtoupper($this->m_Type);
            if ($cType == "DATE")   $val = $this->renderDate();
            else if ($cType == "DATETIME")   $val = $this->renderDatetime();
            else
            {
                $val = parent::render();
                if ($this->m_ValuePicker != null)
                {
                    $function = $this->m_BizFormName . ".ShowPopup(" . $this->m_ValuePicker . "," . $ctrlName . ")";
                    $val .= " <input type=button onClick=\"CallFunction('$function','Popup');\" value=\"...\" style='width:20px;'>";
                }
            }
        }

        return $val;
    }

    /**
     * Render date type field
     * @return string html content of this control
     */
    protected function renderDate()
    {
        // get the raw date value by unformatting it or geting the raw data from dataobj
        $format = $this->getFormObj()->getDataObj()->getField($this->m_BizFieldName)->m_Format;
        $val = parent::render();
        $showTime = 'false';
        $img = "<img src=\"".Resource::getImageUrl()."/calendar.gif\" border=0 title=\"Select date...\" align='absoultemiddle' hspace='2'>";
        $val .= "<a href=\"javascript: void(0);\" onclick=\"return showCalendar('".$this->m_Name."', '".$format."', ".$showTime.", true); return false;\"  onmousemove='window.status=\"Select a date\"' onmouseout='window.status=\"\"'>" . $img . "</a>";
        return $val;
    }

    /**
     * Render datetime type field
     * @return string html content of this control
     */
    protected function renderDatetime()
    {
        // get the raw date value by unformatting it or geting the raw data from dataobj
        $format = $this->getFormObj()->getDataObj()->getField($this->m_BizFieldName)->m_Format;
        $val = parent::render();
        $showTime = "'24'";
        $img = "<img src=\"".Resource::getImageUrl()."/calendar.gif\" border=0 title=\"Select date...\" align='absoultemiddle' hspace='2'>";
        $val .= "<a href=\"javascript: void(0);\" onclick=\"return showCalendar('".$this->m_Name."', '".$format."', ".$showTime.", true); return false;\"  onmousemove='window.status=\"Select a datetime\"' onmouseout='window.status=\"\"'>" . $img . "</a>";
        return $val;
    }

}

?>
