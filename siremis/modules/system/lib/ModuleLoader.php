<?php
// install openbiz modules

class ModuleLoader
{
    public $name;
    public $dbName;
	public $errors;
    public $logs;
    public $debug = 1;
    
    public function __construct($name, $dbName=null)
    {
    	$this->name = $name;
    	$this->dbName = $dbName;
    }
    
    public function DBConnection()
    {
    	return BizSystem::dbConnection($this->dbName);
    }
    
    public function loadModule($installSql=false)
    {
		$module = $this->name;
		$modfile = MODULE_PATH."/$module/mod.xml";
        if (!file_exists($modfile)) {
        	$this->errors = "$module is not loaded, mod.xml is not found in $module.";	
        	return false;
    	}
    	if (($db = $this->DBConnection()) == null) {
    		$this->errors = "ERROR: Cannot get database connection.";	
        	return false;
    	}

    	// dependency check
    	$depModules = $this->checkDependency();
    	$depCount = 0;
    	foreach ($depModules as $mod=>$val) {
    		if ($val == 1) {
    			$this->errors = "Dependent module $mod is NOT loaded.";
    			$depCount++;
    		}
    	}
    	if ($depCount > 0)
    		return false;
		
	    // install mod.sql
	    if ($installSql) {
        	if (!$this->installModuleSql())
            	return false;
	    }
	    else {
	    	if (!self::isModuleInstalled($module))	// check if the module has been installed
	    		if (!$this->installModuleSql())	// if not, install it anyway
            		return false;
	    }
        
    	// install mod.xml
        if (!$this->installModule())
            return false;
        /*
        // load metadata. Only needed by cubi studio (not yet implemented) 
        $this->installMetaDo();
        $this->installMetaForm();
        $this->installMetaView();
        */
        $this->log("$module is loaded.");
        return true;
    }
    
    public function unLoadModule()
    {
		$module = $this->name;
    	$db = $this->DBConnection();
		
    	// check all modules depending on this module
    	try {
    		$sql ="SELECT name FROM module WHERE depend_on LIKE '%$module%'";
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        if ($rs && count($rs)>0) {
        	foreach ($rs as $r) {
        		$mods[]= $r[0][0];
        	}
        	$modList = implode(",",$mods);
        	$this->log("The module cannot be unloaded because it module '$modList' depending on it.");
        	return false;
        }
		
		// delete all records
    	try {
    		$sql ="DELETE FROM menu WHERE module='$module'; ";
    		$sql .="DELETE FROM meta_view WHERE module='$module'; ";
    		$sql .="DELETE FROM meta_form WHERE module='$module'; ";
    		$sql .="DELETE FROM meta_do WHERE module='$module'; ";
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
    	
    	// uninstall Sql
    	$this->log("Install Module Sql.");
    	$sqlfile = MODULE_PATH."/".$this->name."/mod.uninstall.sql";
        if (!file_exists($sqlfile))
        	return true;
        
    	// Getting the SQL file content        
        $query = file_get_contents($file);
		try {
	    	$db->exec($query);
	    } catch (Exception $e) {
	        $this->errors = $e->getMessage();
	        return;
	   	}
    }
    
    static public function isModuleInstalled($module, $dbName=null)
    {

    	$db = BizSystem::DBConnection($dbName);
        $sql = "SELECT * from module where name='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e)
        {
            //$this->errors = $e->getMessage();
            var_dump($e->getMessage());
            return false;
        }
        if (count($rs)>0) {
        	return true;
        }
        return false;
    }
    
    protected function checkDependency()
    {
    	$modfile = MODULE_PATH."/".$this->name."/mod.xml";
        
    	$xml = simplexml_load_file($modfile);
    	
    	$depModules = array();
    	if (isset($xml->Dependency) && isset($xml->Dependency->Module))
    	{
    		foreach ($xml->Dependency->Module as $mod)
    		{
    			$modName = trim($mod['Name']);
    			if (!self::isModuleInstalled($modName,$this->dbName))
    				$depModules[$modName] = 1;
    			else {
    			 	$depModules[$modName] = 0;
    			}
    		}
    	}
    	return $depModules;
    }
    
    protected function installModuleSql()
    {
        $this->log("Install Module Sql.");
    	$sqlfile = MODULE_PATH."/".$this->name."/mod.install.sql";
        if (!file_exists($sqlfile))
        	return true;
        
    	// Getting the SQL file content        
        $query = trim(file_get_contents($sqlfile));
        if (empty($query))
        	return true;

        $db = $this->DBConnection();
        include_once (MODULE_PATH."/system/lib/MySQLDumpParser.php");
        
        $queryArr = MySQLDumpParser::parse($query);
        foreach($queryArr as $query){
			try {
		    	$db->exec($query);
		    } catch (Exception $e) {
		        $this->errors = $e->getMessage();
		        $this->log($e->getMessage());
		        return false;
		   	}
	    }
	   	return true;
    }
        
    protected function installModule()
    {
        $this->log("Install Module.");
    	$modfile = MODULE_PATH."/".$this->name."/mod.xml";
        
    	$xml = simplexml_load_file($modfile);
        
        $db = $this->DBConnection();
        
        // write mod info in module table
        $modName = $xml['Name'];
        $modDesc = $xml['Description'];
        $modAuthor = $xml['Author'];
        $modVersion = $xml['Version'];
        $modObVersion = $xml['OpenbizVersion'];
        $depModules = $this->checkDependency();
        $depModString = implode(",",array_keys($depModules));
        $sql = "SELECT * from module where name='$modName'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        if (count($rs)>0)
            $sql = "UPDATE module SET description='$modDesc', version='$modVersion', author='$modAuthor', openbiz_version='$modObVersion' WHERE name='$modName'";
        else
            $sql = "INSERT INTO module (name, description, version, author, openbiz_version, depend_on) VALUE ('$modName','$modDesc','$modVersion','$modAuthor','$modObVersion','$depModString');";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
    
        // install ACL
        $this->installACL($xml);
        
        // install Menu
        $this->installMenu($xml);
        
        return true;
    }
    
    protected function installMenu($xml)
    {
    	$this->log("Install Module Menu.");
    	$module = $this->name;
    	if (isset($xml->Menu) && isset($xml->Menu->MenuItem))
    	{
	    	// delete all menu item first
	    	$db = $this->DBConnection();
            $sql = "DELETE FROM menu WHERE module='$module'";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
	        //clean menu obj cache
	        $menuTreeObj = BizSystem::getObject("menu.do.MenuTreeDO");
			$menuTreeObj->CleanCache();	
			
			$menuObj = BizSystem::getObject("menu.do.MenuDO");
			$menuObj->CleanCache();
			
            foreach ($xml->Menu->MenuItem as $m) {
            	if ($this->loadMenuItem($m) == false) return false;
            } 
		
            
    	}
    	return true;
    }
    
    protected function loadMenuItem($menuItem, $parentMenuName='')
    {
    	$module = $this->name;
    	$db = $this->DBConnection();
    	$name = $menuItem['Name'];
    	$title = $menuItem['Title'];
    	$link = $menuItem['URL'];
        $access = $menuItem['Access'];
    	$order = isset($menuItem['Order']) ? $menuItem['Order'] : 10;
    	if (isset($menuItem['Parent']) && $menuItem['Parent']!="")
    		$parentMenuName = $menuItem['Parent'];
    	// IconImage and IconCssClass
    	$icon = $menuItem['IconImage'];
    	$icon_css = $menuItem['IconCssClass']; 
    	$description = $menuItem['Description'];   	
    	
    	$sql = "INSERT INTO menu (`name`,description,module,title,link,parent,access,ordering,icon,icon_css,published) ";
    	$sql .= "VALUE ('$name','$description','$module','$title','$link','$parentMenuName','$access','$order','$icon','$icon_css','1');";
    	try {
        	//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
        	$this->errors = $e->getMessage();
        	echo $e->getMessage();
            return false;
        }
        foreach ($menuItem->MenuItem as $m)
        {
        	if ($this->loadMenuItem($m,$name) == false) return false;
        }
        return true;
    }
    
    protected function installACL($xml)
    {
    	$this->log("Install Module ACL.");
    	$modName = $this->name;
    	if (isset($xml->ACL) && isset($xml->ACL->Resource))
        {
			$db = $this->DBConnection();
        	// write mod/acl in acl_action table
            foreach ($xml->ACL->Resource as $res)
            {
                $resName = $res['Name'];
                foreach ($res->Action as $act)
                {
                    $actName = $act['Name'];
                    $actDesc = $act['Description'];
                    $sql = "SELECT * FROM acl_action WHERE module='$modName' AND resource='$resName' AND action='$actName'";
                    try {
                        //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
                        $rs = $db->fetchAll($sql);
                        
                        if (count($rs)>0) {
                        	$actionIds[] = $rs[0][0];
                        	$sql = "UPDATE acl_action SET description='$actDesc' WHERE module='$modName' AND resource='$resName' AND action='$actName'";
                        	//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
                        	$db->query($sql);
                        }
	                    else {
    	                    $insertSqls[] = "INSERT INTO acl_action (module, resource, action, description) VALUE ('$modName', '$resName','$actName', '$actDesc');";
	                    }
                    }
                    catch (Exception $e) {
                        $this->errors = $e->getMessage();
                        return false;
                    }
                }
            }
            if (isset($actionIds)) {
	            // delete old records from acl_role_action and acl_action who are not in the action list
	            $actionIdList = implode(",", $actionIds);
				$sql = "SELECT * FROM acl_action WHERE module='$modName' AND id NOT IN ($actionIdList)";
	        	try {
	        	    //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	    			$rs = $db->fetchAll($sql);
					if (count($rs)>0) {
						foreach ($rs as $r)
	                		$delIds[] = $r[0];
						$delIdList = implode(",",$delIds);
						$sql = "DELETE FROM acl_role_action WHERE action_id IN ($delIdList)";
						//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
		                $db->query($sql);
		                $sql = "DELETE FROM acl_action WHERE id IN ($delIdList)";
						//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
		                $db->query($sql);
					}
				}
				catch (Exception $e) {
				    $this->errors = $e->getMessage();
				    return false;
				}
            }
			
			// insert new records
			if (isset($insertSqls) && count($insertSqls)>0) {
				foreach ($insertSqls as $sql) {
					try {
						//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	                    $db->query($sql);
					}
					catch (Exception $e) {
					    $this->errors = $e->getMessage();
					    return false;
					}
				}
			}
        }
    }
    
    protected function installMetaDo()
    {
    	$this->log("Install Module DO metadata.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
    	global $g_MetaFiles;
    	$g_MetaFiles = array();
        php_grep("<BizDataObj", $modulePath);
        if (empty($g_MetaFiles))
        	return;
        
        $db = $this->DBConnection();
    	$sql = "DELETE FROM meta_do WHERE module='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
            return false;
        }
        foreach ($g_MetaFiles as $metaFile)
        {
            $metaName = str_replace('/','.',str_replace(array(MODULE_PATH.'/','.xml'),'', $metaFile));
	    	// load do
	    	$xml = simplexml_load_file($metaFile);

	        // write mod info in module table
	        $name = $xml['Name'];
	        $class = $xml['Class'];
	        $dbName = $xml['DBName'];
	        $table = $xml['Table'];
	        $data = addslashes(file_get_contents($metaFile));
	        unset($fields); $fields = array();
	        if (!isset($xml->BizFieldList) || !isset($xml->BizFieldList->BizField))
	        	continue;
	        foreach ($xml->BizFieldList->BizField as $fld)
	        	$fields[] = $fld['Name'];
	        $fieldStr = implode(',',$fields);
	    	$sql = "INSERT INTO meta_do (`name`,`module`,`class`,`dbname`,`table`,`data`,`fields`) 
	    			VALUE ('$metaName','$module','$class','$dbName','$table','$data','$fieldStr');";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
        }
    }

	protected function installMetaForm()
    {
    	$this->log("Install Module Form metadata.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
    	global $g_MetaFiles;
    	$g_MetaFiles = array();
        php_grep("<EasyForm", $modulePath);
        if (empty($g_MetaFiles))
        	return;
        
        $db = $this->DBConnection();
    	$sql = "DELETE FROM meta_form WHERE module='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
            return false;
        }
        foreach ($g_MetaFiles as $metaFile)
        {
            $metaName = str_replace('/','.',str_replace(array(MODULE_PATH.'/','.xml'),'', $metaFile));
	    	// load do
	    	$xml = simplexml_load_file($metaFile);

	        // write mod info in module table
	        $name = $xml['Name'];
	        $class = $xml['Class'];
	        $dataobj = $xml['BizDataObj'];
	        $template = $xml['TemplateFile'];
	        $data = addslashes(file_get_contents($metaFile));
	        unset($elems); $elems = array();
	        if (!isset($xml->DataPanel) || !isset($xml->DataPanel->Element))
	        	continue;
	        if ($xml->DataPanel->Element) {
	        	foreach ($xml->DataPanel->Element as $elem)
	        		$elems[] = $elem['Name'];
	        }
	        $elemStr = implode(',',$elems);
	    	$sql = "INSERT INTO meta_form (`name`,`module`,`class`,`dataobj`,`template`,`data`,`elements`) 
	    			VALUE ('$metaName','$module','$class','$dataobj','$template','$data','$elemStr');";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
        }
    }
    
	protected function installMetaView()
    {
    	$this->log("Install Module View metadata.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
    	global $g_MetaFiles;
    	$g_MetaFiles = array();
        php_grep("<EasyView", $modulePath);
        if (empty($g_MetaFiles))
        	return;
        
        $db = $this->DBConnection();
    	$sql = "DELETE FROM meta_view WHERE module='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
            return false;
        }
        foreach ($g_MetaFiles as $metaFile)
        {
            $metaName = str_replace('/','.',str_replace(array(MODULE_PATH.'/','.xml'),'', $metaFile));
	    	// load do
	    	$xml = simplexml_load_file($metaFile);

	        // write mod info in module table
	        $name = $xml['Name'];
	        $class = $xml['Class'];
	        $template = $xml['TemplateFile'];
	        $data = addslashes(file_get_contents($metaFile));
	        unset($refs); $refs = array();
	        if (!isset($xml->FormReferences) || !isset($xml->FormReferences->Reference))
	        	continue;
	        if ($xml->FormReferences->Reference) {
	        	foreach ($xml->FormReferences->Reference as $ref)
	        		$refs[] = $ref['Name'];
	        }
	        $refStr = implode(',',$refs);
	    	$sql = "INSERT INTO meta_view (`name`,`module`,`class`,`template`,`data`,`forms`) 
	    			VALUE ('$metaName','$module','$class','$template','$data','$refStr');";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
        }
    }
    
    protected function log($message)
    {
    	$date = date('c', time());
    	if ($this->debug)
    		echo "[$date] $message\n";
    	$this->logs .= "[$date] $message \n"; 
    }
}

$g_MetaFiles = array();

function php_grep($q, $path)
{    
    global $g_MetaFiles;
    $fp = opendir($path);
    while($f = readdir($fp))
    {
    	if ( preg_match("#^\.+$#", $f) ) continue; // ignore symbolic links
    	$file_full_path = $path.'/'.$f;
    	if(is_dir($file_full_path)) 
    	{
    		php_grep($q, $file_full_path);
    	} 
    	else 
    	{
    		$path_parts = pathinfo($f);
    		if ($path_parts['extension'] != 'xml') continue; // consider only xml files
    		
    		//echo file_get_contents($file_full_path); exit;
    		if( stristr(file_get_contents($file_full_path), $q) ) 
    		    $g_MetaFiles[] = $file_full_path;
    	}
    }
}

?>
