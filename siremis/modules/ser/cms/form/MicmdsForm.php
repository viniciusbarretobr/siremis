<?php
include_once (MODULE_PATH.'/ser/service/asipto/libs/cmds/sermi.php');
include_once (MODULE_PATH.'/ser/service/siremisMICommands.php');

class MicmdsForm extends EasyForm 
{ 
   	protected $localService = "ser.service.siremisMICommands";
   	
   	protected function renderHTML()
   	{
   		$miobj = BizSystem::getObject($this->localService);
		$sHTML = ""; 
		$sHTML .= 
		//	'<script type="text/javascript" src="'.APP_URL.'/js/richtext.js"></script>';
			'<script type="text/javascript" src="'.APP_URL.'/modules/ser/js/orichtext.js"></script>';
		$sHTML .= '<br />
			<table id="micmds" align="center" width="100%">
			<tr align="center"><td align="center" colspan="2">
			';
		if(!$miobj)
		{
			$sHTML .= 'MI Commands Service not configured';
			$sHTML .= '</td></tr></table>';
			return $sHTML;
		}

		$sHTML .= 
		'<strong>MI Command Panel</strong><br /><br />
		</td></tr>
		<tr><td width="10%"></td><td>
		<FORM id="micmds_form" action="" method="post">
			<INPUT align="type="text" name="cmd" size="40"/>
			<INPUT type="submit" name="submit" value="Run"/>
			<INPUT type="reset"/>
		</FORM>
		<br />';

		$micmds = $miobj->GetMIConfig()->GetMICommands();
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
				$vcmd = $miobj->GetMIConfig()->GetMICommand($vcmdid)->GetCommand();
			}
		}
		if($vcmd && $vcmd!="")
		{
			$smi = new sermi(
					$miobj->GetMIConfig()->GetLocal()->GetAddress(),
					$miobj->GetMIConfig()->GetLocal()->GetPort(),
					$miobj->GetMIConfig()->GetLocal()->GetTimeout(),
					$miobj->GetMIConfig()->GetRemote()->GetAddress(),
					$miobj->GetMIConfig()->GetRemote()->GetPort()
				);
			if ($smi->ready != true) {
				$smi->smi_close();
				die(printf("Unable to create local socket\r\n"));

			}
			$miret = $smi->smi_command($vcmd);

			$sHTML .= 
				'<hr size="1px" width="50%"/>
				<br />
				<span style="color: #AA8800;">Result For MI Command: [ ' . $vcmd . ' ]</span>
				<br />
				<br />';

			if($miret == true)
			{
				if($miobj->GetMIConfig()->getMode()=="rich")
				{
			$sHTML .= 
'<script language="JavaScript" type="text/javascript">
<!--
//build richTextEditor
initRTE("pages/rte/", "pages/rte/", "", false);
document.writeln(\'<br><br>\');
var rte = new richTextEditor(\'rte\');
';
			$content = $smi->toRichStr();
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
' . $smi->toPlainStr() . '
</TEXTAREA>';


				}
			} else {
				echo "NO RESULT";
			}
			$smi->smi_close();
		}

		$sHTML .= '</td></tr></table>';
		return $sHTML;
   	}
}
?>
