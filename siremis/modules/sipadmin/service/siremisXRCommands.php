<?php

/* configuration
<PluginService Name="siremisXRCommands" Package="asipto" Class="siremisXRCommands">
    <XRConfig name="XRConfig">
		<RSocket name="rsocket"
			address="127.0.0.1" port="8021"/>
        <XRCommands>
            <cmd name="status" title="Status" command="status"/>
            <cmd name="help" title="Help" command="help"/>
        </XRCommands>
    </XRConfig>
</PluginService>
*/

class siremisXRCommands
{
   private $m_ConfigFile = "siremisXRCommands.xml";
   public $m_Name; 
   public $m_XRConfig; 
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_XRConfig = new MetaIterator($xmlArr["PLUGINSERVICE"]["XRCONFIG"],"XRConfig");
   }
   
   public function GetXRConfig()
   {
      foreach ($this->m_XRConfig as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetName() { return $this->m_Name; }
}

class XRConfig
{
   public $m_Name;
   public $m_Mode;
   public $m_RSocket;
   public $m_XRCommands;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Mode = $xmlArr["ATTRIBUTES"]["MODE"];
      $this->m_RSocket = new MetaIterator($xmlArr["RSOCKET"],"XRPeer");
      $this->m_XRCommands = new MetaIterator($xmlArr["XRCOMMANDS"]["CMD"],"XRCommand");
   }
   public function GetName() { return $this->m_Name; }
   public function GetMode() { return $this->m_Mode; }
   public function GetRSocket()
   {
      foreach ($this->m_RSocket as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetXRCommands() { return $this->m_XRCommands; }
   public function GetXRCommand($micName)
   {
      foreach ($this->m_XRCommands as $micobj) {
         if ($micName == $micobj->GetName()) {
            return $micobj;
         }
      }
      return null;
   }
}

class XRPeer
{
   public $m_Name;
   public $m_Path;
   public $m_Address;
   public $m_Port;
   public $m_Timeout;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Path = $xmlArr["ATTRIBUTES"]["PATH"];
      $this->m_Address = $xmlArr["ATTRIBUTES"]["ADDRESS"];
      $this->m_Port = $xmlArr["ATTRIBUTES"]["PORT"];
      $this->m_Timeout = $xmlArr["ATTRIBUTES"]["Timeout"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetPath() { return $this->m_Path; }
   public function GetAddress() { return $this->m_Address; }
   public function GetPort() { return $this->m_Port; }
   public function GetTimeout() { return $this->m_Timeout; }
}

class XRCommand
{
   public $m_Name;
   public $m_Title;
   public $m_Command;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Title = $xmlArr["ATTRIBUTES"]["TITLE"];
      $this->m_Command = $xmlArr["ATTRIBUTES"]["COMMAND"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetTitle() { return $this->m_Title; }
   public function GetCommand() { return $this->m_Command; }
}
?>
