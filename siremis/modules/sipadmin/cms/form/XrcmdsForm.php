<?php
include_once (MODULE_PATH.'/sipadmin/service/asipto/libs/cmds/serxr.php');
include_once (MODULE_PATH.'/sipadmin/service/siremisXRCommands.php');

class XrcmdsForm extends EasyForm 
{ 
   	protected $localService = "sipadmin.service.siremisXRCommands";
   	
   	protected function renderHTML()
   	{
   		$xrobj = BizSystem::getObject($this->localService);
		$sHTML = ""; 
		$sHTML .= 
		//	'<script type="text/javascript" src="'.APP_URL.'/js/richtext.js"></script>';
			'<script type="text/javascript" src="'.APP_URL.'/modules/sipadmin/js/orichtext.js"></script>';
		$sHTML .= '<br />
			<table id="micmds" align="center" width="100%">
			<tr align="center"><td align="center" colspan="2">
			';
		if(!$xrobj)
		{
			$sHTML .= 'XMLRPC Commands Service not configured';
			$sHTML .= '</td></tr></table>';
			return $sHTML;
		}

		$sHTML .= 
		'<strong>XMLRPC Command Panel</strong><br /><br />
		</td></tr>
		<tr><td width="10%"></td><td>
		<FORM id="micmds_form" action="" method="post">
			<INPUT type="text" name="cmd" size="40"/>
			<INPUT type="submit" name="submit" value="Run"/>
			<INPUT type="reset"/>
		</FORM>
		<br />';

		$micmds = $xrobj->GetXRConfig()->GetXRCommands();
		if($micmds)
		{
			$sHTML .= 
		'<br />
		<br />
		<FORM style="float:center;" action="" method="post">
			<SELECT name="cmdid">';

			$selcmd = $_GET["xcmdid"];
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
			// die(printf("Command: ".$vcmdid."\r\n"));
			if($vcmdid && $vcmdid!="")
			{
				$vcmd = $xrobj->GetXRConfig()->GetXRCommand($vcmdid)->GetCommand();
			}
		}
		if($vcmd && $vcmd!="")
		{
			$xre = new serxr(
						$xrobj->GetXRConfig()->GetRSocket()->GetPath(),
						$xrobj->GetXRConfig()->GetRSocket()->GetAddress(),
						$xrobj->GetXRConfig()->GetRSocket()->GetPort(),
						$xrobj->GetXRConfig()->GetRSocket()->GetTimeout()
					);
			if ($xre->ready != true) {
				$xre->sxr_close();
				die(printf("Unable to create XMLRPC client\r\n"));
			}
			$xrret = $xre->sxr_command($vcmd);

			$sHTML .= 
				'<hr size="1px" width="50%"/>
				<br />
				<span style="color: #AA8800;">Result For XMLRPC Command: [ ' . $vcmd . ' ]</span>
				<br />
				<br />';

			if($xrret == true)
			{
				if($xrobj->GetXRConfig()->getMode()=="rich")
				{
			$sHTML .= 
'<script language="JavaScript" type="text/javascript">
<!--
//build richTextEditor
initRTE("", "", "", false);
document.writeln(\'<br><br>\');
var rte = new richTextEditor(\'rte\');
';
			$content = $xre->toRichStr();
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
' . $xre->toPlainStr() . '
</TEXTAREA>';


				}
			} else {
				$sHTML .= 'NO RESULT';
			}
			$xre->sxr_close();
		}

		$sHTML .= '</td></tr></table>';
		return $sHTML;
   	}
}
?>
