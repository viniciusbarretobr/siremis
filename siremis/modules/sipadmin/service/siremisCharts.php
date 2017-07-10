<?php

/* configuration
<PluginService ...>
  <ChartGroup name="system">
    <Chart name="random" table="statistics" title="random">
      <XAxis data="random" type="timestamp"/>
    </Chart>
  </ChartGroup>
</PluginService>
*/

class siremisCharts
{
   private $m_ConfigFile = "siremisCharts.xml";
   public $m_Name; 
   public $m_ChartGroups; 
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_ChartGroups = new MetaIterator($xmlArr["PLUGINSERVICE"]["CHARTGROUP"],"ChartGroup");
   }
   
   public function GetChartGroup($groupName)
   {
      foreach ($this->m_ChartGroups as $grpobj) {
         if ($groupName == $grpobj->GetName()) {
            return $grpobj;
         }
      }
      return null;
   }
   public function GetChartGroupCount()
   {
	   $n = 0;
      foreach ($this->m_ChartGroups as $grpobj) {
		  $n = $n + 1;
      }
      return $n;
   }
   public function GetName() { return $this->m_Name; }
}

class ChartGroup
{
   public $m_Name;
   public $m_ChartList;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_ChartList = new MetaIterator($xmlArr["CHART"],"ChartData");
   }
   public function GetName() { return $this->m_Name; }
   public function GetChartList() { return $this->m_ChartList; }
   public function GetChart($chartName)
   {
      foreach ($this->m_ChartList as $crtobj) {
         if ($chartName == $crtobj->GetName()) {
            return $crtobj;
         }
      }
      return null;
   }
   public function GetChartCount()
   {
	   $n = 0;
      foreach ($this->m_ChartList as $crtobj) {
			$n = $n + 1;
      }
      return $n;
   }
}

class ChartData
{
   public $m_Name;
   public $m_Table;
   public $m_Title;
   public $m_BGColor;
   public $m_Order;
   public $m_OrderBy;
   public $m_ChartType;
   public $m_Limit;
   public $m_XAxisList;
   public $m_YAxisList;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Table = $xmlArr["ATTRIBUTES"]["TABLE"];
      $this->m_Title = $xmlArr["ATTRIBUTES"]["TITLE"];
      $this->m_ChartType = $xmlArr["ATTRIBUTES"]["TYPE"];
      $this->m_BGColor = $xmlArr["ATTRIBUTES"]["BGCOLOR"];
      $this->m_Order = $xmlArr["ATTRIBUTES"]["ORDER"];
      $this->m_OrderBy = $xmlArr["ATTRIBUTES"]["ORDERBY"];
      $this->m_Limit = $xmlArr["ATTRIBUTES"]["LIMIT"];
      $this->m_XAxisList = new MetaIterator($xmlArr["XAXIS"]["ITEM"],"XYItem");
      $this->m_YAxisList = new MetaIterator($xmlArr["YAXIS"]["ITEM"],"XYItem");
   }
   public function GetName() { return $this->m_Name; }
   public function GetTable() { return $this->m_Table; }
   public function GetTitle() { return $this->m_Title; }
   public function GetChartType() { return $this->m_ChartType; }
   public function GetBGColor() { return $this->m_BGColor; }
   public function GetOrder() { return $this->m_Order; }
   public function GetOrderBy() { return $this->m_OrderBy; }
   public function GetLimit() { return $this->m_Limit; }
   public function GetXAxisList() { return $this->m_XAxisList; }
   public function GetYAxisList() { return $this->m_YAxisList; }
   public function GetXAxisCount() {
	   $n = 0;
      foreach ($this->m_XAxisList as $xaobj) {
		  $n = $n + 1;
      }
      return $n;
   }
   public function GetYAxisCount() {
	   $n = 0;
      foreach ($this->m_YAxisList as $yaobj) {
		  $n = $n + 1;
      }
      return $n;
   }
}

class XYItem
{
   public $m_Name;
   public $m_Data;
   public $m_Type;
   public $m_Title;
   public $m_Color;
   public $m_BGColor;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Data = $xmlArr["ATTRIBUTES"]["DATA"];
      $this->m_Type = $xmlArr["ATTRIBUTES"]["TYPE"];
      $this->m_Title   = $xmlArr["ATTRIBUTES"]["TITLE"];
      $this->m_Color   = $xmlArr["ATTRIBUTES"]["COLOR"];
      $this->m_BGColor = $xmlArr["ATTRIBUTES"]["BGCOLOR"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetXYData() { return $this->m_Data; }
   public function GetXYType() { return $this->m_Type; }
   public function GetXYTitle() { return $this->m_Title; }
   public function GetXYColor() { return $this->m_Color; }
   public function GetXYBGColor() { return $this->m_BGColor; }
}
?>
