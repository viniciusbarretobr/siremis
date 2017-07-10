<?php

class EasySiremisForm extends EasyForm 
{ 
	public $x_SiremisCmdPage = '';

	public function GetSiremisCmdPage()
	{
		/* load config file */
		include_once (MODULE_PATH.'/sipadmin/config/common.Main.php');
		$this->x_SiremisCmdPage = $cfg_siremis_cmd_page;
		$this->cancel();
	}
}
?>
