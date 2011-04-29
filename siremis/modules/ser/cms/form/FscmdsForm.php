<?php
include_once (MODULE_PATH.'/ser/service/asipto/libs/cmds/fsevs.php');
include_once (MODULE_PATH.'/ser/service/siremisFSCommands.php');

class FscmdsForm extends EasyForm 
{ 
   	protected $localService = "ser.service.siremisFSCommands";
   	
   	protected function renderHTML()
   	{
   		$fsobj = BizSystem::getObject($this->localService);
		$sHTML = ""; 
		$sHTML .= 
		//	'<script type="text/javascript" src="'.APP_URL.'/js/richtext.js"></script>';
			'<script type="text/javascript" src="'.APP_URL.'/modules/ser/js/orichtext.js"></script>';
		$sHTML .= '<br />
			<table id="micmds" align="center" width="100%">
			<tr align="center"><td align="center" colspan="2">
			';
		if(!$fsobj)
		{
			$sHTML .= 'FreeSWITCH Commands Service not configured';
			$sHTML .= '</td></tr></table>';
			return $sHTML;
		}

		$sHTML .= 
		'<strong>FreeSWITCH Command Panel</strong><br /><br />
		</td></tr>
		<tr><td width="10%"></td><td>
		<FORM id="micmds_form" action="" method="post">
			<INPUT align="type="text" name="cmd" size="40"/>
			<INPUT type="submit" name="submit" value="Run"/>
			<INPUT type="reset"/>
		</FORM>
		<br />';

		$micmds = $fsobj->GetFSConfig()->GetFSCommands();
		if($micmds)
		{
			$sHTML .= 
		'<br />
		<br />
		<FORM style="float:center;" action="" method="post">
			<SELECT name="cmdid">';

			foreach ($micmds as $micobj) {
				$sHTML .= 
				'<OPTION value="' . $micobj->GetName() . '">' . $micobj->GetTitle() . '</OPTION>';
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
				$vcmd = $fsobj->GetFSConfig()->GetFSCommand($vcmdid)->GetCommand();
			}
		}
		if($vcmd && $vcmd!="")
		{
			$fse = new fsevs(
						$fsobj->GetFSConfig()->GetRSocket()->GetAddress(),
						$fsobj->GetFSConfig()->GetRSocket()->GetPort(),
						$fsobj->GetFSConfig()->GetRSocket()->GetPassword(),
						$fsobj->GetFSConfig()->GetRSocket()->GetTimeout(),
						$fsobj->GetFSConfig()->GetRSocket()->GetStreamTimeout()
					);
			if ($fse->ready != true) {
				$fse->evs_close();
				die(printf("Unable to authenticate\r\n"));
			}
			$fsret = $fse->evs_api_exec($vcmd);

			$sHTML .= 
				'<hr size="1px" width="50%"/>
				<br />
				<span style="color: #AA8800;">Result For FreeSwitch Command: [ ' . $vcmd . ' ]</span>
				<br />
				<br />';

			if($fsret == true)
			{
				if($fsobj->GetFSConfig()->getMode()=="rich")
				{
			$sHTML .= 
'<script language="JavaScript" type="text/javascript">
<!--
//build richTextEditor
initRTE("", "", "", false);
document.writeln(\'<br><br>\');
var rte = new richTextEditor(\'rte\');
';
			$content = $fse->toRichStr();
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
' . $fse->toPlainStr() . '
</TEXTAREA>';


				}
			} else {
				echo "NO RESULT";
			}
			$fse->evs_close();
		}

		$sHTML .= '</td></tr></table>';
		return $sHTML;
   	}
}
?>
