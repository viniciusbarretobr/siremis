<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

include_once("InputElement.php");

/**
 * InputText class is element for input text
 *
 * @package openbiz.bin.easy.element
 * @author Jixian W.
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class DropDownList extends InputElement
{
	
	public $m_ReadOnly;
	public $m_DefaultDisplayValue;
	public $m_cssHoverClass;
	
	protected function readMetaData(&$xmlArr){
		parent::readMetaData($xmlArr);
		$this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_text";
		$this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->m_cssClass."_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->m_cssClass."_focus";
		$this->m_cssHoverClass = isset($xmlArr["ATTRIBUTES"]["CSSHOVERCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSHOVERCLASS"] : $this->m_cssClass."_hover";
		$this->m_Value = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) ? I18n::getInstance()->translate($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) : null;        
		$this->m_ReadOnly = isset($xmlArr["ATTRIBUTES"]["READONLY"]) ? $xmlArr["ATTRIBUTES"]["READONLY"] : "N";
	}
    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $value = $this->m_Value ? $this->m_Value : $this->getText();
        $disabledStr = ($this->getEnabled() == "N") ? "READONLY=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        $optionList= $this->renderList();
        
        $htmlClass = Expression::evaluateExpression($this->m_cssClass, $formobj);
        $htmlClass = "CLASS='$htmlClass'";
        
        $sHTML .= "<div class=\"div_".$this->m_cssClass."\" style=\"float:left;\">";    	                
        if($this->m_ReadOnly=='Y')
        {
	        $display_input = "style=\"display:none;\"";
	        $display_span = "";
        }
        else
        {
	        $display_span = "style=\"display:none;\"";
	        $display_input = "";
        }
        
	        $sHTML .= "<div $display_span>";
	        $sHTML .= "<span ID=\"span_" . $this->m_Name ."\"  $this->m_HTMLAttr $style
		        			onclick=\"if($('".$this->m_Name."_list').visible()){\$('".$this->m_Name."_list').hide();$('".$this->m_Name."').className='".$this->m_cssClass."'}else{\$('".$this->m_Name."_list').show();$('".$this->m_Name."').className='".$this->m_cssFocusClass."'}\"
		        			onmouseover=\"$('span_".$this->m_Name."').className='".$this->m_cssHoverClass."'\"
		        			onmouseout=\"$('span_".$this->m_Name."').className='".$this->m_cssClass."'\"
		        			>".$this->m_DefaultDisplayValue."</span>";
	        $sHTML .= "</div>";
	        $sHTML .= "<div $display_input>";
	        $sHTML .= "<INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\" $disabledStr $this->m_HTMLAttr $style $func
		        			onclick=\"if($('".$this->m_Name."_list').visible()){\$('".$this->m_Name."_list').hide();$('".$this->m_Name."').className='".$this->m_cssClass."'}else{\$('".$this->m_Name."_list').show();$('".$this->m_Name."').className='".$this->m_cssFocusClass."'}\"
		        			onmouseover=\"$('".$this->m_Name."').className='".$this->m_cssHoverClass."'\"
		        			onmouseout=\"$('".$this->m_Name."').className='".$this->m_cssClass."'\"
		        			/>";
	        $sHTML .= "</div>";
        $sHTML .= $optionList;
        $sHTML .= "</div>";
        
        $sHTML .= "<script>$('".$this->m_Name."_list').hide()</script>";
        return $sHTML;
    }
    
    protected function renderList(){
    	$list = $this->getList();
    	$value = $this->m_Value ? $this->m_Value : $this->getText();
    	$sHTML = "<ul style=\"display:none\" id=\"".$this->m_Name."_list\" class=\"dropdownlist\">";
    	foreach($list as $item){
    		$val = $item['val'];
    		$txt = $item['txt'];
    		$pic = $item['pic'];
    		if($pic){
    			$str_pic="<img src=\"$pic\" />";
    		}else{
    			$pic = "";
    		}    		
    		$sHTML .= "<li
				onmouseover=\"this.className='selected'\"
				onmouseout=\"this.className=''\"
				onclick=\"$('".$this->m_Name."_list').hide();
							$('".$this->m_Name."').setValue('$val');
							$('span_".$this->m_Name."').innerHTML = this.innerHTML;
							$('".$this->m_Name."').triggerEvent('change');
							$('".$this->m_Name."').className='".$this->m_cssClass."'
							\"					
				>".$str_pic."<span>".$txt."</span></li>";
    		
    		if($val == $value){
    			$this->m_DefaultDisplayValue="".$str_pic."<span>".$txt."</span>";
    		}		
    	}
    	$sHTML .= "</ul>";
    	return $sHTML;
    }
    
    
    protected function getList(){
    	return array();
    }

}

?>
