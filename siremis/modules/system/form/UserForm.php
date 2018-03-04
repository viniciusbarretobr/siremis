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

define ('HASH_ALG','sha1');

/**
 * UserForm class - implement the login of login form
 *
 * @package system.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class UserForm extends EasyForm
{
    /**
     * Create a user record
     *
     * @return void
     */
    public function CreateUser()
    {
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        if ($this->_checkDupUsername())
        {
            $errorMessage = $this->GetMessage("USERNAME_USED");
			$errors['fld_username'] = $errorMessage;
			$this->processFormObjError($errors);
			return;
        }
        
        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }
        $password = BizSystem::ClientProxy()->GetFormInputs("fld_password");            
		$recArr['password'] = hash(HASH_ALG, $password);
        $this->_doInsert($recArr);

        // if 'notify email' option is checked, send confirmation email to user email address
        // ...
        
        //$this->m_Notices[] = $this->GetMessage("USER_CREATED");
        
        //assign a default role to new user
        $userArr = $this->getActiveRecord();
        $user_id = $userArr["Id"];
        
        $RoleDOName = "system.do.RoleDO";
        $UserRoleDOName = "system.do.UserRoleDO";
        
        $roleDo = BizSystem::getObject($RoleDOName,1);
        $userRoleDo = BizSystem::getObject($UserRoleDOName,1);
        
        $roleDo->setSearchRule("[default]=1");
        $defaultRoles = $roleDo->fetch();
        foreach($defaultRoles as $role){
        	$role_id = $role['Id'];
        	$userRoleArr = array(
        		"user_id" => $user_id,
        		"role_id" => $role_id
        	);
        	$userRoleDo->insertRecord($userRoleArr);
        }
        
        
        $this->processPostAction();
    }
    
    /**
     * Update user record
     *
     * @return void
     */
    public function UpdateUser()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        
        $this->setActiveRecord($recArr);
        
        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }

        if (count($recArr) == 0)
            return;
		
        $password = BizSystem::ClientProxy()->GetFormInputs("fld_password");            
        if($password){
        	$recArr['password'] = hash(HASH_ALG, $password);
		}
        if ($this->_doUpdate($recArr, $currentRec) == false)
            return;
        
        // if 'notify email' option is checked, send confirmation email to user email address
        // ...
        
        //$this->m_Notices[] = $this->GetMessage("USER_DATA_UPDATED");
        $this->processPostAction();
    }
   
	/**
     * Validate form user inputs
     *
     * @return boolean
     */
    public function validateForm($cleanError = true)
    {	
    	// disable password validation if they are empty
    	$password = BizSystem::ClientProxy()->GetFormInputs("fld_password");
		$password_repeat = BizSystem::ClientProxy()->GetFormInputs("fld_password_repeat");
    	if (!$password_repeat)
    	    $this->getElement("fld_password")->m_Validator = null;
    	if (!$password)
    	    $this->getElement("fld_password_repeat")->m_Validator = null;
    	    
    	parent::ValidateForm();

		if($password != "" && ($password != $password_repeat))
		{
			$passRepeatElem = $this->getElement("fld_password_repeat");
			$errorMessage = $this->GetMessage("PASSOWRD_REPEAT_NOTSAME",array($passRepeatElem->m_Label));
			$this->m_ValidateErrors['fld_password_repeat'] = $errorMessage;
			throw new ValidationException($this->m_ValidateErrors);
			return false;
		}
	
        return true;
    }

    /**
     * check duplication of username
     *
     * @return boolean
     */
    protected function _checkDupUsername()
    {
        $username = BizSystem::ClientProxy()->GetFormInputs("fld_username");
        // query UserDO by the username
        $userDO = $this->getDataObj();
        $records = $userDO->directFetch("[username]='$username'",1);
        if (count($records)==1)
            return true;
        return false;
    }

    /**
     * check duplication of email address
     *
     * @return boolean
     */
    protected function _checkDupEmail()
    {
        $email = BizSystem::ClientProxy()->GetFormInputs("fld_email");
        $userDO = $this->getDataObj();
        $records = $userDO->directFetch("[email]='$email'",1);
        if (count($records)==1)
            return true;
        return false;
    }    

    
}  
?>
