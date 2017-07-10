<?PHP

// include_once("Listbox.php");

class ListboxInputText extends Listbox
{
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
		
		// Get style class names
		$this->m_cssErrorClass	= isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]	: $this->m_cssClass."_error";
		$this->m_cssFocusClass	= isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]	: $this->m_cssClass."_focus";
		$this->m_cssClass		= isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) 		? $xmlArr["ATTRIBUTES"]["CSSCLASS"]			: "input_text";
    }

    public function render()
    {
		// Get Values for element
		$name = $this->m_Name;
		$style = $this->getStyle();
        $function = $this->getFunction();
		$value = $this->m_Value;
		$valueArray = explode('=', $value);
		
		// Set class information for states
    	if($this->GetFormObj()->m_Errors[$this->m_Name])
			$function .= "onchange=\"this.className='$this->m_cssClass'\"";
		else
			$function .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->m_cssClass'\"";
        
		// Set values for dropdown element
		$this->m_cssClass = $this->m_cssErrorClass = $this->m_cssErrorClass = '';
		$this->m_Name .= '[]';				//for array (dropdown, textbox)
		$this->m_Style = 'float:left; width:95px; margin-right:7px;';
		$this->m_Value = $valueArray[0];	//first element of array

		// Render dropdown element
        $html = parent::render();
		
		// Generate textbox
		if(!$valueArray[1]) $valueArray[1] = $this->getDefaultValue();
        $html .= ' <INPUT NAME="' . $this->m_Name . '" ID="' . $this->m_Name .'" VALUE="' . $valueArray[1] . '"' . " $style $function />";
		
		// Restore Values and Name for rendering total element
		$this->m_Value = $value;
		$this->m_Name = $name;
        
		return $html;
    }
}

?>
