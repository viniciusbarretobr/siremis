<?php

/* configuration
<PluginService Name="siremisMICommands" Package="asipto" Class="siremisMICommands">
    <MIConfig name="MIConfig" type="udpsocket">
        <Local name="local" data="127.0.0.1:8044"/>
        <Remote name="remote" data="127.0.0.1:8033"/>
        <MICommands>
            <cmd name="ps" title="List Processes" command="ps" params=""/>
            <cmd name="uptime" title="Show Uptime" command="uptime" params=""/>
        </MICommands>
    </MIConfig>
</PluginService>
*/

class siremisMICommands
{
   private $m_ConfigFile = "siremisMICommands.xml";
   public $m_Name; 
   public $m_MIConfig; 
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_MIConfig = new MetaIterator($xmlArr["PLUGINSERVICE"]["MICONFIG"],"MIConfig");
   }
   
   public function GetMIConfig()
   {
      foreach ($this->m_MIConfig as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetName() { return $this->m_Name; }
}

class MIConfig
{
   public $m_Name;
   public $m_Type;
   public $m_Mode;
   public $m_Local;
   public $m_Remote;
   public $m_MICommands;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Type = $xmlArr["ATTRIBUTES"]["TYPE"];
      $this->m_Mode = $xmlArr["ATTRIBUTES"]["MODE"];
      $this->m_Local = new MetaIterator($xmlArr["LOCAL"],"MIPeer");
      $this->m_Remote = new MetaIterator($xmlArr["REMOTE"],"MIPeer");
      $this->m_MICommands = new MetaIterator($xmlArr["MICOMMANDS"]["CMD"],"MICommand");
   }
   public function GetName() { return $this->m_Name; }
   public function GetType() { return $this->m_Type; }
   public function GetMode() { return $this->m_Mode; }
   public function GetLocal()
   {
      foreach ($this->m_Local as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetRemote()
   {
      foreach ($this->m_Remote as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetMICommands() { return $this->m_MICommands; }
   public function GetMICommand($micName)
   {
      foreach ($this->m_MICommands as $micobj) {
         if ($micName == $micobj->GetName()) {
            return $micobj;
         }
      }
      return null;
   }
}

class MIPeer
{
   public $m_Name;
   public $m_Address;
   public $m_Port;
   public $m_Timeout;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Address = $xmlArr["ATTRIBUTES"]["ADDRESS"];
      $this->m_Port = $xmlArr["ATTRIBUTES"]["PORT"];
      $this->m_Timeout = $xmlArr["ATTRIBUTES"]["TIMEOUT"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetAddress() { return $this->m_Address; }
   public function GetPort() { return $this->m_Port; }
   public function GetTimeout() { return $this->m_Timeout; }
}

class MICommand
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
