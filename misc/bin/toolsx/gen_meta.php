<?php
/*
 * Generate metadata from given database table
 * usage: php gen_meta.php dbname table [modulename]
 * Example:
 * # php gen_meta.php Default trac_ticket trac.ticket
    ---------------------------------------
	Please select metadata naming:
	1. module path: D:\Apache2\htdocs\ob\cubi\modules\trac\ticket, object name: TracTicket
	2. module path: D:\Apache2\htdocs\ob\cubi\modules\trac\ticket, object name: Ticket
	Please select: [1/2] 2
	---------------------------------------
	Target dir: D:\Zend\ServerCE\Apache2\htdocs\ob\cubi\modules\trac\ticket
	Medata file to create:
	  do/TicketDO.xml
	  form/Ticket...Form.xml
	  view/TicketView.xml
	Do you want to continue? [y/n] y
	...
 */

if ($argc<3) {
	echo "usage: php gen_meta.php dbname table [modulename] [metadata template set]".PHP_EOL;
	exit;
}

$dbname = $argv[1];
$table = $argv[2];
$module = isset($argv[3]) ? $argv[3] : $table;
$metatpl = isset($argv[4]) ? $argv[4] : "metatpl";

define("META_TPL",$metatpl);

include_once ("../app.inc");
include_once ("gen_meta.inc.php");
if(!defined("CLI")){
	exit;
}
$moduleDir = MODULE_PATH.DIRECTORY_SEPARATOR.str_replace(".",DIRECTORY_SEPARATOR,$module);

// help user to set the metadata namings
$namings[0] = array($moduleDir, getCompName($table));
$namings[1] = array($moduleDir, getCompName($table,1));
echo "---------------------------------------".PHP_EOL;
echo "Please select metadata naming:".PHP_EOL;
for ($i=0; $i<count($namings); $i++) {
	echo ($i+1).". module path: ".str_replace(MODULE_PATH,"",$namings[$i][0]).", object name: ".$namings[$i][1].PHP_EOL;
	$ques[]= $i+1;
}
echo "Please select: [".implode("/",$ques)."] (1) : ";
$n=0;
while(1) {
	$answer = intval(trim(fgets(STDIN)))-1;	
	$answer = $answer>-1?$answer:0;
	if (!isset($namings[$answer]) && $n++ < 3)
		echo "Please select again: [".implode("/",$ques)."] : ";
	else 
		break;
}
if ($n > 3) exit;
$opts = $namings[$answer];

// check if the /modules/table is already there
echo "---------------------------------------".PHP_EOL;
echo "Target dir: $opts[0]".PHP_EOL;
echo "Medata file to create: ".PHP_EOL;
echo "  do/$opts[1]DO.xml".PHP_EOL;
echo "  form/$opts[1]...Form.xml".PHP_EOL;
echo "  view/$opts[1]View.xml".PHP_EOL;
echo "Do you want to continue? [y/n] (y) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) != 'y')
	exit;

$metaGen = new MetaGenerator($module, $dbname, $table, $opts);

// create do xml
echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate data Object? [y/n] (y) : ";
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	echo "Generate Data Object metadata file ...".PHP_EOL;
	$metaGen->genDOMeta();
}

// create forms xml
echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate form Object? [y/n] (y) : ";
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	echo "Generate Form Object metadata files ...".PHP_EOL;
	$metaGen->genFormMeta();
}

// create view xml
echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate view Object? [y/n] (y) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	echo "Generate view Object metadata files ...".PHP_EOL;	
	$metaGen->genViewMeta();
}

echo "---------------------------------------".PHP_EOL;
echo "Do you want to override mod.xml? [y/n] (n) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"n";
if (strtolower($answer) == 'y'){
	// create mod.xml
	echo "Generate mod.xml ...".PHP_EOL;
	$metaGen->genModXML();
}

?>
