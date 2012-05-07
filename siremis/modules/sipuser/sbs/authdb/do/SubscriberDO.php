<?php

class SubscriberDO extends BizDataObj 
{
	public function InsertRecord($recArr)
	{
		$l_Profile = BizSystem::sessionContext()->getVar("_USER_PROFILE");
		if (!$l_Profile) {
			return false;
		}
		$recArr['username'] = $l_Profile['username'];
		$recArr['ha1'] = md5($recArr['username'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		$recArr['ha1b'] = md5($recArr['username'] . '@' . $recArr['domain'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		return parent::InsertRecord($recArr);
	}

	public function UpdateRecord($recArr, $oldRec=null)
	{
		$l_Profile = BizSystem::sessionContext()->getVar("_USER_PROFILE");
		if (!$l_Profile) {
			return false;
		}
		$recArr['username'] = $l_Profile['username'];
		$recArr['ha1'] = md5($recArr['username'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		$recArr['ha1b'] = md5($recArr['username'] . '@' . $recArr['domain'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		return parent::UpdateRecord($recArr, $oldRec);
	}
}

?>
