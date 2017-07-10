<?php
// ob_start();
include_once("../../../../../bin/app.inc");
function checkAccess() {
    global $g_BizSystem;
	if(!$g_BizSystem->GetSessionContext())
	{
		die(printf("no session\n"));
		header("Location: ".APP_URL);
		exit;
	}
	if($g_BizSystem->GetSessionContext()->IsTimeout())
	{
		die(printf("session timeout \n"));
		header("Location: ".APP_URL);
		exit;
	}
	if(!$g_BizSystem->GetSessionContext()->IsUserValid())
	{
		echo "--- ".$g_BizSystem->GetUserProfile();
		die(printf("no valid user\n"));
		header("Location: ".APP_URL);
		exit;
	}
}
?>
