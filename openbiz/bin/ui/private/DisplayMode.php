<?php

/**
 * DisplayMode class - contains the BizForm display mode information
 *
 * @package BizView
 */
class DisplayMode
{
   public $m_Name;
   public $m_DataFormat;
   public $m_FormatStyle = null;
   public $m_InitMode;
   public $m_TemplateFile;

   function __construct(&$xmlArr)
   {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_DataFormat = isset($xmlArr["ATTRIBUTES"]["DATAFORMAT"]) ? $xmlArr["ATTRIBUTES"]["DATAFORMAT"] : null;
      $this->m_TemplateFile = isset($xmlArr["ATTRIBUTES"]["TEMPLATEFILE"]) ? $xmlArr["ATTRIBUTES"]["TEMPLATEFILE"] : null;
      $this->m_InitMode = isset($xmlArr["ATTRIBUTES"]["INITMODE"]) ? $xmlArr["ATTRIBUTES"]["INITMODE"] : null;
      if (isset($xmlArr["ATTRIBUTES"]["FORMATSTYLE"])) {
         $this->m_FormatStyle = array();
         $this->m_FormatStyle = explode(",",$xmlArr["ATTRIBUTES"]["FORMATSTYLE"]);
      }
   }

   public function GetMode()
   {
      switch ($this->m_Name) {
         case "READ": $mode = MODE_R; break;
         case "EDIT": $mode = MODE_E; break;
         case "NEW": $mode = MODE_N; break;
         case "QUERY": $mode = MODE_Q; break;
         default: $mode = $this->m_Name;
      }
      return $mode;
   }
}
?>