<?php

class SubscriberDO extends BizDataObj
{
	public function InsertRecord($recArr)
	{
		$recArr['ha1'] = md5($recArr['username'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		$recArr['ha1b'] = md5($recArr['username'] . '@' . $recArr['domain'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		return parent::InsertRecord($recArr);
	}

	public function UpdateRecord($recArr, $oldRec=null)
	{
		$recArr['ha1'] = md5($recArr['username'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		$recArr['ha1b'] = md5($recArr['username'] . '@' . $recArr['domain'] . ':' . $recArr['domain'] . ':' . $recArr['password']);
		return parent::UpdateRecord($recArr, $oldRec);
	}
}

?>
