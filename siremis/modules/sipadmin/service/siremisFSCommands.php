<?php

/* configuration
<PluginService Name="siremisFSCommands" Package="asipto" Class="siremisFSCommands">
    <FSConfig name="FSConfig">
		<RSocket name="rsocket"
			address="127.0.0.1" port="8021"
			password="ClueCon"
			timeout="3" stimeout="0.5"/>
        <FSCommands>
            <cmd name="status" title="Status" command="status"/>
            <cmd name="help" title="Help" command="help"/>
        </FSCommands>
    </FSConfig>
</PluginService>
*/

class siremisFSCommands
{
   private $m_ConfigFile = "siremisFSCommands.xml";
   public $m_Name; 
   public $m_FSConfig; 
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_FSConfig = new MetaIterator($xmlArr["PLUGINSERVICE"]["FSCONFIG"],"FSConfig");
   }
   
   public function GetFSConfig()
   {
      foreach ($this->m_FSConfig as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetName() { return $this->m_Name; }
}

class FSConfig
{
   public $m_Name;
   public $m_Mode;
   public $m_RSocket;
   public $m_FSCommands;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Mode = $xmlArr["ATTRIBUTES"]["MODE"];
      $this->m_RSocket = new MetaIterator($xmlArr["RSOCKET"],"FSPeer");
      $this->m_FSCommands = new MetaIterator($xmlArr["FSCOMMANDS"]["CMD"],"FSCommand");
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
   public function GetFSCommands() { return $this->m_FSCommands; }
   public function GetFSCommand($micName)
   {
      foreach ($this->m_FSCommands as $micobj) {
         if ($micName == $micobj->GetName()) {
            return $micobj;
         }
      }
      return null;
   }
}

class FSPeer
{
   public $m_Name;
   public $m_Address;
   public $m_Port;
   public $m_Password;
   public $m_Timeout;
   public $m_StreamTimeout;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Address = $xmlArr["ATTRIBUTES"]["ADDRESS"];
      $this->m_Port = $xmlArr["ATTRIBUTES"]["PORT"];
      $this->m_Password = $xmlArr["ATTRIBUTES"]["PASSWORD"];
      $this->m_Timeout = $xmlArr["ATTRIBUTES"]["TIMEOUT"];
      $this->m_StreamTimeout = $xmlArr["ATTRIBUTES"]["STIMEOUT"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetAddress() { return $this->m_Address; }
   public function GetPort() { return $this->m_Port; }
   public function GetPassword() { return $this->m_Password; }
   public function GetTimeout() { return $this->m_Timeout; }
   public function GetStreamTimeout() { return $this->m_StreamTimeout; }
}

class FSCommand
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
