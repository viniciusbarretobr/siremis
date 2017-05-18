<?php
class profileService
{
    protected $m_Name = "ProfileService";
    protected $m_Profile;    
    protected $m_profileObj = "contact.do.ContactDO";    
    protected $m_userDataObj = "system.do.UserDO";
    protected $m_user_roleDataObj = "system.do.UserRoleDO";

    public function __construct(&$xmlArr)
    {
        //$this->readMetadata($xmlArr);
    }

    protected function readMetadata(&$xmlArr)
    {
        //$this->m_profileObj = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    public function InitProfile($userName)
    {
        $this->m_Profile = $this->InitDBProfile($userName);
        BizSystem::sessionContext()->setVar("_USER_PROFILE", $this->m_Profile);
        return $this->m_Profile;
    }

    public function getProfile($attr=null)
    {
        if (!$this->m_Profile)
        {
            $this->m_Profile = BizSystem::sessionContext()->getVar("_USER_PROFILE");
        }
        if (!$this->m_Profile)
        {
            $this->getProfileByCookie();
            if (!$this->m_Profile)
        		return null;
        }

        if ($attr && isset($this->m_Profile[$attr]))
            return $this->m_Profile[$attr];
        return $this->m_Profile;
    }

    public function SetProfile($profile)
    {
        $this->m_Profile = $profile;
    }
    
    protected function getProfileByCookie()
    {
    	if (isset($_COOKIE["SYSTEM_SESSION_USERNAME"]) && isset($_COOKIE["SYSTEM_SESSION_PASSWORD"]))
		{
			$username = $_COOKIE["SYSTEM_SESSION_USERNAME"];
			$password = $_COOKIE["SYSTEM_SESSION_PASSWORD"];
			
			$svcobj = BizSystem::getService(AUTH_SERVICE);
			if ($svcobj->authenticateUserByCookies($username,$password)) 
			{
				$this->InitProfile($username);
			}
			else {
				setcookie("SYSTEM_SESSION_USERNAME",null,time()-100,"/");
    	 		setcookie("SYSTEM_SESSION_PASSWORD",null,time()-100,"/");
			}
		}
		return null;
    }

    protected function InitDBProfile($username)
    {
        // fetch user record
        $do = BizSystem::getObject($this->m_userDataObj);
        if (!$do)
            return false;

        $rs = $do->directFetch("[username]='$username'", 1);
        if (!$rs)
            return null;

        // set the profile array
        $userId = $rs[0]['Id'];
        $profile = $rs[0];
        $profile['password'] = null;
        $profile['enctype'] = null;
        
    	$do = BizSystem::getObject($this->m_profileObj);
        if (!$do)
            return false;

        $rs = $do->directFetch("[user_id]='$userId'", 1);
      
        if ($rs)
        {
        	$rs = $rs[0];
        	foreach ($rs as $key => $value)
        	{        		
        			$profile["profile_".$key] = $value;        	
        	}	
        }
        // fetch roles and set profile roles
        $do = BizSystem::getObject($this->m_user_roleDataObj);
        $rs = $do->directFetch("[user_id]='$userId'");
        if ($rs)
        {
            foreach ($rs as $rec)
            {
                $profile['roles'][] = $rec['role_id'];
                $profile['roleNames'][] = $rec['role_name'];
                $profile['roleStartpage'][] = $rec['role_startpage'];                
            }
        }
        return $profile;
    }

    public function GetProfileName($account_id){
    	$do = BizSystem::getObject($this->m_userDataObj);
        if (!$do)
            return false;

        $rs = $do->directFetch("[Id]='$account_id'", 1);
        if (!$rs)
            return null;
        
        $rs = $rs[0];
        $name = $rs['username']." &lt;".$rs['email']."&gt;";
        return $name;
    }
}

?>