<?php

class RowSelector extends FieldControl 
{
   public function renderHeader()
   {
      $formname = $this->m_BizFormName;
      $name = $this->m_Name.'[]';
      $sHTML = "<INPUT TYPE=\"CHECKBOX\" onclick=\"checkAll(this, $('$formname')['$name']);\"/>";
      return $sHTML;
   }
   
   public function render()
   {
      $value = $this->m_Value;
      $name = $this->m_Name.'[]';
      $sHTML = "<INPUT TYPE=\"CHECKBOX\" NAME=\"$name\" VALUE='$value' onclick=\"event.cancelBubble=true;\"/>";
      return $sHTML;
   }
}

?>