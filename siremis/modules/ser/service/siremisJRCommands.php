<?php

/* configuration
<PluginService Name="siremisJRCommands" Package="asipto" Class="siremisJRCommands">
    <JRConfig name="JRConfig" type="http" mode="rich">
		<!-- used for type="http" -->
		<RSocket name="rsocket" address="http://127.0.0.1:5060/RPC2" timeout="3"
                 username="alice" password="wonderland"/>
		<!-- used for type="udp" -->
		<UDPLocal name="udplocal" address="127.0.0.1" port="8044" timeout="3.0"/>
		<UDPRemote name="udpremote" address="127.0.0.1" port="8033" timeout="3.0"/>
		<!-- used for type="unixsock" -->
		<UnixSockLocal name="unixsocklocal" address="/tmp/siremis-kamailio-rpc.sock" timeout="3.0"/>
		<UnixSockRemote name="unixsockremote" address="/var/run/kamailio/kamailio_rpc.sock" timeout="3.0"/>>
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
   public $m_Type;
   public $m_Mode;
   public $m_RSocket;
   public $m_UDPLocal;
   public $m_UDPRemote;
   public $m_UnixSockLocal;
   public $m_UnixSockRemote;
   public $m_JRCommands;

   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Type = $xmlArr["ATTRIBUTES"]["TYPE"];
      $this->m_Mode = $xmlArr["ATTRIBUTES"]["MODE"];
      $this->m_RSocket = new MetaIterator($xmlArr["RSOCKET"],"JRHTTPPeer");
      $this->m_UDPLocal = new MetaIterator($xmlArr["UDPLOCAL"],"JRUDPPeer");
      $this->m_UDPRemote = new MetaIterator($xmlArr["UDPREMOTE"],"JRUDPPeer");
      $this->m_UnixSockLocal = new MetaIterator($xmlArr["UNIXSOCKLOCAL"],"JRUnixSockPeer");
      $this->m_UnixSockRemote = new MetaIterator($xmlArr["UNIXSOCKREMOTE"],"JRUnixSockPeer");
      $this->m_JRCommands = new MetaIterator($xmlArr["JRCOMMANDS"]["CMD"],"JRCommand");
   }
   public function GetName() { return $this->m_Name; }
   public function GetType() { return $this->m_Type; }
   public function GetMode() { return $this->m_Mode; }
   public function GetRSocket()
   {
      foreach ($this->m_RSocket as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetUDPLocal()
   {
      foreach ($this->m_UDPLocal as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetUDPRemote()
   {
      foreach ($this->m_UDPRemote as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetUnixSockLocal()
   {
      foreach ($this->m_UnixSockLocal as $micobj) {
          return $micobj;
      }
      return null;
   }
   public function GetUnixSockRemote()
   {
      foreach ($this->m_UnixSockRemote as $micobj) {
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

class JRHTTPPeer
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

class JRUDPPeer
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

class JRUnixSockPeer
{
   public $m_Name;
   public $m_Address;
   public $m_Timeout;

   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Address = $xmlArr["ATTRIBUTES"]["ADDRESS"];
      $this->m_Timeout = $xmlArr["ATTRIBUTES"]["TIMEOUT"];
   }
   public function GetName() { return $this->m_Name; }
   public function GetAddress() { return $this->m_Address; }
   public function GetTimeout() { return $this->m_Timeout; }
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
