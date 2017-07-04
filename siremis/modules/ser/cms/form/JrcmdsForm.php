<?php
include_once (MODULE_PATH.'/ser/service/asipto/libs/cmds/serjr.php');
include_once (MODULE_PATH.'/ser/service/siremisJRCommands.php');

class JrcmdsForm extends EasyForm
{
   	protected $localService = "ser.service.siremisJRCommands";

   	protected function renderHTML()
   	{
   		$jrobj = BizSystem::getObject($this->localService);
		$sHTML = "";
		$sHTML .=
		//	'<script type="text/javascript" src="'.APP_URL.'/js/richtext.js"></script>';
			'<script type="text/javascript" src="'.APP_URL.'/modules/ser/js/orichtext.js"></script>';
		$sHTML .= '<br />
			<table id="micmds" align="center" width="100%">
			<tr align="center"><td align="center" colspan="2">
			';
		if(!$jrobj)
		{
			$sHTML .= 'JSONRPC Commands Service not configured';
			$sHTML .= '</td></tr></table>';
			return $sHTML;
		}

		$sHTML .=
		'<strong>JSONRPC Command Panel</strong><br /><br />
		</td></tr>
		<tr><td width="10%"></td><td>
		<FORM id="micmds_form" action="" method="post">
			<INPUT type="text" name="cmd" size="40"/>
			<INPUT type="submit" name="submit" value="Run"/>
			<INPUT type="reset"/>
		</FORM>
		<br />';

		$micmds = $jrobj->GetJRConfig()->GetJRCommands();
		if($micmds)
		{
			$sHTML .=
		'<br />
		<br />
		<FORM style="float:center;" action="" method="post">
			<SELECT name="cmdid">';

			$selcmd = $_GET["jcmdid"];
			foreach ($micmds as $micobj) {
				if($selcmd != $micobj->GetName())
					$sHTML .=
					'<OPTION value="' . $micobj->GetName() . '">' . $micobj->GetTitle() . '</OPTION>';
				else
					$sHTML .=
					'<OPTION value="' . $micobj->GetName() . '" selected>' . $micobj->GetTitle() . '</OPTION>';
			}
			$sHTML .=
			'</SELECT>
			<INPUT type="submit" name="submit" value="Run"/>
		</FORM>
		<br />';

		}

		$sHTML .=
			'</td></tr><tr><td align="center" colspan="2">';

		$vcmd = $_POST["cmd"];
		if(!$vcmd || $vcmd=="")
		{
			$vcmdid = $_POST["cmdid"];
			// die("Command: ".$vcmdid."\r\n");
			if($vcmdid && $vcmdid!="")
			{
				$vcmd = $jrobj->GetJRConfig()->GetJRCommand($vcmdid)->GetCommand();
			}
		}
		if($vcmd && $vcmd!="")
		{
			if($jrobj->GetJRConfig()->GetType()=="http") {
				$jre = new serjr("http",
							"", 0, /* no locl address:port */
							$jrobj->GetJRConfig()->GetRSocket()->GetAddress(), 0,
							$jrobj->GetJRConfig()->GetRSocket()->GetTimeout()
						);
			} else if($jrobj->GetJRConfig()->GetType()=="udp") {
				$jre = new serjr("udp",
							$jrobj->GetJRConfig()->GetUDPLocal()->GetAddress(),
							$jrobj->GetJRConfig()->GetUDPLocal()->GetPort(),
							$jrobj->GetJRConfig()->GetUDPRemote()->GetAddress(),
							$jrobj->GetJRConfig()->GetUDPRemote()->GetPort(),
							$jrobj->GetJRConfig()->GetUDPRemote()->GetTimeout()
						);
			} else if($jrobj->GetJRConfig()->GetType()=="unixsock") {
				$jre = new serjr("unixsock",
							$jrobj->GetJRConfig()->GetUnixSockLocal()->GetAddress(),
							0,
							$jrobj->GetJRConfig()->GetUnixSockRemote()->GetAddress(),
							0,
							$jrobj->GetJRConfig()->GetUnixSockRemote()->GetTimeout()
						);
			} else {
				die("Unknown JSONRPC client type\r\n");
			}
			if ($jre->ready != true) {
				$jre->sjr_close();
				die("Unable to create JSONRPC client\r\n");
			}
			$jrret = $jre->sjr_command($vcmd);

			$sHTML .=
				'<hr size="1px" width="50%"/>
				<br />
				<span style="color: #AA8800;">Result For JSONRPC Command: [ ' . $vcmd . ' ]</span>
				<br />
				<br />';

			if($jrret == true)
			{
				if($jrobj->GetJRConfig()->getMode()=="rich")
				{
			$sHTML .=
'<script language="JavaScript" type="text/javascript">
<!--
//build richTextEditor
initRTE("", "", "", false);
document.writeln(\'<br><br>\');
var rte = new richTextEditor(\'rte\');
';
			$content = $jre->toRichStr();
			$sHTML .=
'rte.html = \'' . $content .'\';
rte.width = 600;
rte.height = 400;
// rte.readOnly = true;
rte.toolbar1 = false;
rte.toolbar2 = false;
rte.toggleSrc = false;
rte.build();
//-->
</script>
';
				} else {
			$sHTML .=
'<TEXTAREA name="mitext" rows="20" cols="80" readonly>
' . $jre->toPlainStr() . '
</TEXTAREA>';


				}
			} else {
				$sHTML .= 'NO RESULT';
			}
			$jre->sjr_close();
		}

		$sHTML .= '</td></tr></table>';
		return $sHTML;
   	}
}
?>
