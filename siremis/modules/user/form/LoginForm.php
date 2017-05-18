<?php 
/**
 * Openbiz Cubi 
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   user.form
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

/**
 * LoginForm class - implement the logic of login form
 *
 * @package user.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class LoginForm extends EasyForm
{
    protected $username;
    protected $password;
  
	public function fetchData()
	{
		
		if(isset($_COOKIE["SYSTEM_SESSION_USERNAME"]) && isset($_COOKIE["SYSTEM_SESSION_PASSWORD"]))
		{
			$this->username = $_COOKIE["SYSTEM_SESSION_USERNAME"];
			$this->password = $_COOKIE["SYSTEM_SESSION_PASSWORD"];
			
			global $g_BizSystem;
			$svcobj 	= BizSystem::getService(AUTH_SERVICE);
			$eventlog 	= BizSystem::getService(EVENTLOG_SERIVCE);
			if ($svcobj->authenticateUserByCookies($this->username,$this->password)) 
    		{
                // after authenticate user: 1. init profile
    			$profile = $g_BizSystem->InitUserProfile($this->username);
    			
    			// after authenticate user: 2. insert login event
    			$logComment=array(	$this->username, $_SERVER['REMOTE_ADDR']);
    			$eventlog->log("LOGIN", "MSG_LOGIN_FETCH_SUCCESSFUL", $logComment);
    			
    			// after authenticate user: 3. update login time in user record
    	   	    if (!$this->UpdateloginTime())
    	   	        return false;
    
    	   	    $redirectPage = APP_INDEX.$profile['roleStartpage'][0];
    	   	    
    			if($profile['roleStartpage'][0]){
       	        	BizSystem::clientProxy()->ReDirectPage($redirectPage);	
       	        }else{
					$errorMessage['login_status'] = "login failure - no role for this account - contact admin";
					$this->processFormObjError($errorMessage);
       	        }
       	        return ;
    		}
		}
	}    
    /**
     * login action
     *
     * @return void
     */
    public function Login()
    {
	  	$recArr = $this->readInputRecord();
	  	try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }
	  	
	  	// get the username and password
	
		$this->username = BizSystem::ClientProxy()->getFormInputs("username");
		$this->password = BizSystem::ClientProxy()->getFormInputs("password");

		global $g_BizSystem;
		$svcobj 	= BizSystem::getService(AUTH_SERVICE);
		$eventlog 	= BizSystem::getService(EVENTLOG_SERIVCE);
		try {
    		if ($svcobj->authenticateUser($this->username,$this->password)) 
    		{
                // after authenticate user: 1. init profile
    			$profile = $g_BizSystem->InitUserProfile($this->username);
    			
    			// after authenticate user: 2. insert login event
    			$logComment=array(	$this->username, $_SERVER['REMOTE_ADDR']);
    			$eventlog->log("LOGIN", "MSG_LOGIN_SUCCESSFUL", $logComment);
    			
    			// after authenticate user: 3. update login time in user record
    	   	    if (!$this->UpdateloginTime())
    	   	        return false;
    
    	   	    $redirectPage = APP_INDEX.$profile['roleStartpage'][0];
    	   	                    		
    	   	    $cookies = BizSystem::ClientProxy()->getFormInputs("session_timeout");
    	   	    if($cookies)
    	   	    {
    	   	    	$password = $this->password;    	   	    	
    	   	    	$password = md5(md5($password.$this->username).md5($profile['create_time']));
    	   	    	setcookie("SYSTEM_SESSION_USERNAME",$this->username,time()+(int)$cookies,"/");
    	   	    	setcookie("SYSTEM_SESSION_PASSWORD",$password,time()+(int)$cookies,"/");
    	   	    	
    	   	    }
    	   	    
       	        if($profile['roleStartpage'][0]){
       	        	BizSystem::clientProxy()->ReDirectPage($redirectPage);	
				}else{
					$errorMessage['login_status'] = "login failure - no role for this account - contact admin";
					$this->processFormObjError($errorMessage);
       	        }
    		    return true;
    		}
    		else
    		{ 
    			$logComment=array($this->username,
    								$_SERVER['REMOTE_ADDR'],
    								$this->password);
    			$eventlog->log("LOGIN", "MSG_LOGIN_FAILED", $logComment);
    			    			
    			$errorMessage['password'] = $this->getMessage("PASSWORD_INCORRECT");
    			$errorMessage['login_status'] = $this->getMessage("LOGIN_FAILED");
    			$this->processFormObjError($errorMessage);
    		}
    	}
    	catch (Exception $e) {
    	    BizSystem::ClientProxy()->showErrorMessage($e->getMessage());
    	}
    }
   
    /**
     * Update login time
     *
     * @return void
     */
    protected function UpdateloginTime()
    {
        $userObj = BizSystem::getObject('system.do.UserDO');
        try {
            $curRecs = $userObj->directFetch("[username]='".$this->username."'", 1);
            $dataRec = new DataRecord($curRecs[0], $userObj);
            $dataRec['lastlogin'] = date("Y-m-d H:i:s");
            $ok = $dataRec->save();
            if (! $ok) {
                $errorMsg = $userObj->getErrorMessage();
                BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
                BizSystem::ClientProxy()->showErrorMessage($errorMsg);
                return false;
            }
        } 
        catch (BDOException $e) 
        {
            $errorMsg = $e->getMessage();
            BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
            BizSystem::ClientProxy()->showErrorMessage($errorMsg);
            return false;
        }
        return true;
   }
   


}  
?>
