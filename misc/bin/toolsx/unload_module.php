<?php
/*
 * cron job controller script
 * it reads the cronjob table and runs command based on the command settings 
 */

if ($argc<2) {
	echo "usage: php load_module.php module_name\n";
	exit;
}

include_once ("../app.inc");
if(!defined("CLI")){
	exit;
}
include_once (MODULE_PATH."/system/lib/ModuleLoader.php");

$moduleName = $argv[1];

$loader = new ModuleLoader($moduleName);
echo "Start unloading $moduleName module ...\n";
$loader->unLoadModule();
//echo $loader->logs . "\n";
echo "End unloading $moduleName module\n";

?>
