{php}
BizSystem::clientProxy()->includeCalendarScripts();
BizSystem::clientProxy()->includeCKEditorScripts();
$includedScripts = BizSystem::clientProxy()->getAppendedScripts();
$this->_tpl_vars['scripts'] = $includedScripts;
$appendStyle = BizSystem::clientProxy()->getAppendedStyles();
$this->_tpl_vars['style_sheets'] = $appendStyle;
$left_menu = "sipuser.widget.SipUserMenu";
$this->assign('left_menu', $left_menu);
$this->assign('template_file', 'system_view.tpl.html');
{/php}
{include file=$template_file}
