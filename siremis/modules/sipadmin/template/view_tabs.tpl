{php}
BizSystem::clientProxy()->includeCKEditorScripts();
$includedScripts = BizSystem::clientProxy()->getAppendedScripts();
$this->_tpl_vars['scripts'] = $includedScripts;
$appendStyle = BizSystem::clientProxy()->getAppendedStyles();
$left_menu = "sipadmin.widget.SipAdminMenu";
$this->assign('left_menu', $left_menu);
$this->_tpl_vars['style_sheets'] = $appendStyle;
$this->assign('template_file', 'system_view_tabs.tpl.html');
{/php}
{include file=$template_file}
