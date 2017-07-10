<?php
function serDataToHtmlArray($data)
{
	$tmp = $data;
	$tmp = str_replace("<", "&lt;", $tmp);
	$tmp = str_replace(">", "&gt;", $tmp);
	$elem1 = $tmp;
	$tmp = str_replace("\r\n", "%%EOL%%", $tmp);
	$tmp = str_replace("&lt;", "&amp;lt;", $tmp);
	$tmp = str_replace("&gt;", "&amp;gt;", $tmp);
	$tmp = preg_replace('#([A-Z]+ sip:[^ ]+ SIP/2.0)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
	$tmp = preg_replace('#(SIP/2.0 [1-6][0-9][0-9] [^%]+)%%#i', '<font color=#336600>${1}</font>%%', $tmp, -1);
	$tmp = preg_replace('#%%([^ :%]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
	while($count>0)
	$tmp = preg_replace('#%%([^ :%<]+): (.+)%%#im', '%%<font color=red>$1</font>: $2%%', $tmp, -1, $count);
	$tmp = str_replace("%%EOL%%", "<br />", $tmp);
	$elem2 = $tmp;
	$rlist = array($elem1, $elem2);
	return $rlist;
}
?>
