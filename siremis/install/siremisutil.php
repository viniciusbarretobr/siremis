<?php

function siremisConnectDB($noDB=false)
{
    require_once 'Zend/Db.php';

    // Automatically load class Zend_Db_Adapter_Pdo_Mysql and create an instance of it.
    $param = array(
        'host'     => $_REQUEST['db1HostName'],
        'username' => $_REQUEST['db1UserName'],
        'password' => $_REQUEST['db1Password'],
        'port'     => $_REQUEST['db1HostPort'],
        'dbname'   => $_REQUEST['db1Name']
    );
    if ($noDB) $param['dbname'] = '';

    try {
        $db = Zend_Db::factory($_REQUEST['db1type'], $param);
        $conn = $db->getConnection();
    } catch (Zend_Db_Adapter_Exception $e) {
        // perhaps a failed login credential, or perhaps the RDBMS is not running
        echo 'ERROR: '.$e->getMessage();
        exit;
    } catch (Zend_Exception $e) {
        // perhaps factory() failed to load the specified Adapter class
        echo 'ERROR: '.$e->getMessage(); exit;
    }
    return $conn;
}

function siremisFillDB()
{
    siremisReplaceDbConfig();
	BizSystem::log(LOG_DEBUG, "SIREMIS", "install module siremis sql - " . $_REQUEST['db1type']);
	if($_REQUEST['db1type']=="Pdo_Pgsql" || $_REQUEST['db1type']=="pdo_pgsql") {
		$sqlfile = MODULE_PATH."/sipadmin/mod.install.siremis.pgsql.sql";
	} else {
		$sqlfile = MODULE_PATH."/sipadmin/mod.install.siremis.sql";
	}
    if (!file_exists($sqlfile))
       	return true;

    // Getting the SQL file content
    $query = trim(file_get_contents($sqlfile));
    if (empty($query))
       	return true;

    // $db = BizSystem::dbConnection("Sipdb");
	$db = siremisConnectDB();
    include_once (MODULE_PATH."/system/lib/MySQLDumpParser.php");
    $queryArr = MySQLDumpParser::parse($query);
    foreach($queryArr as $query){
		try {
			$db->exec($query);
		 } catch (Exception $e) {
			 BizSystem::log(LOG_DEBUG, "SIREMIS",$e->getMessage());
			echo 'ERROR: ' . $e->getMessage();
		    exit;
	   	}
	}
	return true;
}

function siremisReplaceDbConfig()
{
   $filename = APP_HOME.'/Config.xml';
   $xml = simplexml_load_file($filename);
   $xml->DataSource->Database[1]['Driver'] = $_REQUEST['db1type'];
   $xml->DataSource->Database[1]['Server'] = $_REQUEST['db1HostName'];
   $xml->DataSource->Database[1]['User'] = $_REQUEST['db1UserName'];
   $xml->DataSource->Database[1]['Password'] = $_REQUEST['db1Password'];
   $xml->DataSource->Database[1]['DBName'] = $_REQUEST['db1Name'];
   $xml->DataSource->Database[1]['Port'] = $_REQUEST['db1HostPort'];
   $fp = fopen ($filename, 'w');
   if (fwrite($fp, $xml->asXML()) === FALSE) {
        echo "Cannot write to file ($filename)";
        exit;
    }
    fclose($fp);
	return true;
}

function getSiremisDB()
{
	$filename = APP_HOME.'/Config.xml';
   	$xml = simplexml_load_file($filename);
   	$db['Name'] = $xml->DataSource->Database[1]['Name'];
   	$db['Driver'] = $xml->DataSource->Database[1]['Driver'];
   	$db['Server'] = $xml->DataSource->Database[1]['Server'];
   	$db['User'] = $xml->DataSource->Database[1]['User'];
   	$db['Password'] = $xml->DataSource->Database[1]['Password'];
   	$db['DBName'] = $xml->DataSource->Database[1]['DBName'];
   	$db['Port'] = $xml->DataSource->Database[1]['Port'];
   	return $db;
}

function showSiremisDBConfig()
{
   $xml = simplexml_load_file(APP_HOME.'/Config.xml');
   //print_r($xml);
   echo "<b>Current setting of SER Database:</b>";
   echo '<table><tr>';
   echo '<th>Name</th><th>Driver</th><th>Server</th><th>Port</th><th>DBName</th><th>User</th><th>Password</th></tr>';
   echo '<tr>';
   echo '<td>'.$xml->DataSource->Database[1]['Name'].'</td>';
   echo '<td>'.$xml->DataSource->Database[1]['Driver'].'</td>';
   echo '<td>'.$xml->DataSource->Database[1]['Server'].'</td>';
   echo '<td>'.$xml->DataSource->Database[1]['Port'].'</td>';
   echo '<td>'.$xml->DataSource->Database[1]['DBName'].'</td>';
   echo '<td>'.$xml->DataSource->Database[1]['User'].'</td>';
   echo '<td>'.$xml->DataSource->Database[1]['Password'].'</td>'; 
   echo '</tr></table>';  
}

?>
