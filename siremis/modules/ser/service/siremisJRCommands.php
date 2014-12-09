<?php

/* configuration
<PluginService Name="siremisJRCommands" Package="asipto" Class="siremisJRCommands">
    <JRConfig name="JRConfig">
		<RSocket name="rsocket"
                address="http://127.0.0.1:8021/RPC2" timeout="5"
                username="alice" password="wonderland"/>
        <JRCommands>
            <cmd name="status" title="Status" command="status"/>
            <cmd name="help" title="Help" command="help"/>
        </JRCommands>
    </JRConfig>
</PluginService>
*/

class siremisJRCommands
{
   private $m_ConfigFile = "siremisJRCommands.xml";
   public $m_Name; 
   public $m_JRConfig; 
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_JRConfig = new MetaIterator($xmlArr["PLUGINSERVICE"]["JRCONFIG"],"JRConfig");
   }
   
   public function GetJRConfig()
   {
      foreach ($this->m_JRConfig as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetName() { return $this->m_Name; }
}

class JRConfig
{
   public $m_Name;
   public $m_Mode;
   public $m_RSocket;
   public $m_JRCommands;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Mode = $xmlArr["ATTRIBUTES"]["MODE"];
      $this->m_RSocket = new MetaIterator($xmlArr["RSOCKET"],"JRPeer");
      $this->m_JRCommands = new MetaIterator($xmlArr["JRCOMMANDS"]["CMD"],"JRCommand");
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
   public function GetJRCommands() { return $this->m_JRCommands; }
   public function GetJRCommand($micName)
   {
      foreach ($this->m_JRCommands as $micobj) {
         if ($micName == $micobj->GetName()) {
            return $micobj;
         }
      }
      return null;
   }
}

class JRPeer
{
   public $m_Name;
   public $m_Address;
   public $m_Timeout;
   public $m_Username;
   public $m_Password;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Address = $xmlArr["ATTRIBUTES"]["ADDRESS"];
      $this->m_Timeout = $xmlArr["ATTRIBUTES"]["TIMEOUT"];
      $this->m_Username = $xmlArr["ATTRIBUTES"]["USERNAME"];
      $this->m_Password = $xmlArr["ATTRIBUTES"]["PASSWORD"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetPath() { return $this->m_Path; }
   public function GetAddress() { return $this->m_Address; }
   public function GetPort() { return $this->m_Port; }
   public function GetTimeout() { return $this->m_Timeout; }
   public function GetUsername() { return $this->m_Username; }
   public function GetPassword() { return $this->m_Password; }
}

class JRCommand
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
