<?php 
/**
 * Openbiz Cubi 
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   system.form
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

include_once MODULE_PATH."/system/lib/ModuleLoader.php";
//include_once MODULE_PATH."/install/ModuleLoader.php";

/**
 * ModuleForm class - implement the login of login form
 *
 * @package system.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class ModuleForm extends EasyForm
{
    /**
     * load new modules from the modules/ directory
     *
     * @return void
     */
    public function loadNewModules()
    {
        $skipOld = true;
       	$mods = array();
        $dir = MODULE_PATH;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $filepath = $dir.'/'.$file;
                if (is_dir($filepath)) {
                    $modfile = $filepath.'/mod.xml';
                    if (file_exists($modfile))
                        $mods[] = $file;
                }
            }
            closedir($dh);
        }
        
        // find all modules
        foreach ($mods as $mod)
        {
            if ($skipOld && ModuleLoader::isModuleInstalled($mod))
                continue;
            $loader = new ModuleLoader($mod);
            $loader->debug = false;
            if (!$loader->loadModule()) {
            	$this->m_Errors[] = nl2br($this->GetMessage("MODULE_LOAD_ERROR")."\n".$loader->errors."\n".$loader->logs);
            }
            else {
            	$this->m_Notices[] = $this->GetMessage("MODULE_LOAD_COMPLETE");	//." ".$loader->logs;
            }
        }
        $this->rerender();
    }
     
    /**
     * load module from the modules/$module/ directory
     *
     * @return void
     */
    public function loadModule($module)
    {
        $loader = new ModuleLoader($module);
        $loader->debug = false;
    	if (!$loader->loadModule()) {
            $this->m_Errors[] = nl2br($this->GetMessage("MODULE_LOAD_ERROR")."\n".$loader->errors."\n".$loader->logs);
        }
        else {
            $this->m_Notices[] = $this->GetMessage("MODULE_LOAD_COMPLETE");	//." ".$loader->logs;
        }
        $this->rerender();
    }
}  
?>