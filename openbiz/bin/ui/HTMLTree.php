<?PHP
/**
 * HTMLTree - class HTMLTree is the base class of HTML tree
 * 
 * @package BizView
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public 
 */
class HTMLTree extends MetaObject implements iUIControl 
{
   protected $m_NodesXml = null;
      
   /**
    * Initialize HTMLTree with xml array
    *
    * @param array $xmlArr
    * @return void
    */
   function __construct(&$xmlArr)
   {
      $this->readMetadata($xmlArr);
   }
   
   /**
    * Read Metadata from xml array 
    * @param array $xmlArr
    */
   protected function readMetadata(&$xmlArr)
   {
      $this->m_Name = $xmlArr["TREE"]["ATTRIBUTES"]["NAME"];
      $this->m_Package = $xmlArr["TREE"]["ATTRIBUTES"]["PACKAGE"];
      $this->m_Class = $xmlArr["TREE"]["ATTRIBUTES"]["CLASS"];
      
      $this->m_NodesXml = $xmlArr["TREE"]["NODE"];
   }
   
   public function render()
   {
      // list all views and highlight the current view
      $sHTML = "<ul class='expanded'>\n";
      $sHTML .= $this->renderNodeItems($this->m_NodesXml);
      $sHTML .= "</ul>";
      return $sHTML;
   }
   
   /**
    * Render the html tree
    * @return string html content of the tree
    */
   protected function renderNodeItems(&$nodeItemArray)
   {
      $sHTML = "";
      foreach ($nodeItemArray as $nodeItem)
      {
         $url = $nodeItem["ATTRIBUTES"]["URL"];
         $caption = I18n::getInstance()->translate($nodeItem["ATTRIBUTES"]["CAPTION"]);
         $target = $nodeItem["ATTRIBUTES"]["TARGET"];
         //$img = $nodeItem["ATTRIBUTES"]["IMAGE"];
         if ($nodeItem["NODE"])
            $image = "<img src='".Resource::getImageUrl()."/plus.gif' class='collapsed' onclick='mouseClickHandler(this)'>";
         else 
            $image = "<img src='".Resource::getImageUrl()."/topic.gif'>";

         if ($target)
            if ($url)
               $sHTML .= "<li class='tree'>$image <a href=\"".$url."\" target='$target'>".$caption."</a>";
            else
               $sHTML .= "<li class='tree'>$image $caption";
         else
            if ($url)
               $sHTML .= "<li class='tree'>$image <a href=\"".$url."\">".$caption."</a>";
            else
               $sHTML .= "<li class='tree'>$image $caption";
         if ($nodeItem["NODE"]) {
            $sHTML .= "\n<ul class='collapsed'>\n";
            $sHTML .= $this->renderNodeItems($nodeItem["NODE"]);
            $sHTML .= "</ul>";
         }
         $sHTML .= "</li>\n";
      }
      return $sHTML;
   }
   
   /**
    * Rerender the menu
    * @return string html content of the menu 
    */
   public function rerender() 
   { 
   	  return $this->render(); 
   }
}

?>