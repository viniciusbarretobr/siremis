<?PHP
/**
 * HTMLMenus - class HTMLMenus is the base class of HTML menus
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public
 */
class HTMLMenus extends MetaObject implements iUIControl
{
    protected $m_MenuItemsXml = null;

    /**
     * Initialize HTMLMenus with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        global $g_BizSystem;
        BizSystem::clientProxy()->appendStyles("menu", "menu.css");
        BizSystem::clientProxy()->appendScripts("menu-ie-js", '<!--[if gte IE 5.5]>
		<script language="JavaScript" src="'.Resource::getJsUrl().'/ie_menu.js" type="text/JavaScript"></script>
		<![endif]-->', false); 

    }

    /**
     * Read Metadata from xml array
     * @param array $xmlArr
     */
    protected function readMetadata(&$xmlArr)
    {
        $this->m_Name = $xmlArr["MENU"]["ATTRIBUTES"]["NAME"];
        $this->m_Package = $xmlArr["MENU"]["ATTRIBUTES"]["PACKAGE"];
        $this->m_Class = $xmlArr["MENU"]["ATTRIBUTES"]["CLASS"];

        $this->m_MenuItemsXml = $xmlArr["MENU"]["MENUITEM"];
    }

    /**
     * Render the html menu
     * @return string html content of the menu
     */
    public function render()
    {
        // list all views and highlight the current view
        $sHTML = "<ul id='navmenu'>\n";
        $sHTML .= $this->renderMenuItems($this->m_MenuItemsXml);
        $sHTML .= "</ul>";
        return $sHTML;
    }

    /**
     * Render menu items
     * @param array $menuItemArray menu item array
     * @return string html content of the menu items
     */
    protected function renderMenuItems(&$menuItemArray)
    {
        $sHTML = "";
        if (isset($menuItemArray["ATTRIBUTES"]))
        {
            $sHTML .= $this->renderSingleMenuItem($menuItemArray);
        }
        else
        {
            foreach ($menuItemArray as $menuItem)
            {
                $sHTML .= $this->renderSingleMenuItem($menuItem);
            }
        }
        return $sHTML;
    }

    /**
     * Render single menu item
     * @param array $menuItem menu item metadata xml array
     * @return string html content of each menu item
     */
    protected function renderSingleMenuItem(&$menuItem)
    {


        global $g_BizSystem;
        $profile = $g_BizSystem->getUserProfile();
        $svcobj = BizSystem::getService("accessService");
        $role = isset($profile["ROLE"]) ? $profile["ROLE"] : null;

        if (array_key_exists('URL', $menuItem["ATTRIBUTES"]))
        {
            $url = $menuItem["ATTRIBUTES"]["URL"];
        } elseif (array_key_exists('VIEW', $menuItem["ATTRIBUTES"]))
        {
            $view = $menuItem["ATTRIBUTES"]["VIEW"];
            // menuitem's containing VIEW attribute is renderd if access is granted in accessservice.xml
            // menuitem's are rendered if no definition is found in accessservice.xml (default)
            if ($svcobj->allowViewAccess($view, $role))
            {
                $url="javascript:GoToView('".$view."')";
            } else
            {
                return '';
            }
        }

        $caption = I18n::getInstance()->translate($menuItem["ATTRIBUTES"]["CAPTION"]);
        $target = $menuItem["ATTRIBUTES"]["TARGET"];
        $icon = $menuItem["ATTRIBUTES"]["ICON"];
        $img = $icon ? "<img src='".Resource::getImageUrl()."/$icon' class=menu_img> " : "";

        if ($view)
            $url="javascript:GoToView('".$view."')";

        if ($target)
            $sHTML .= "<li><a href=\"".$url."\" target='$target'>$img".$caption."</a>";
        else
            $sHTML .= "<li><a href=\"".$url."\">$img".$caption."</a>";
        if ($menuItem["MENUITEM"])
        {
            $sHTML .= "\n<ul>\n";
            $sHTML .= $this->renderMenuItems($menuItem["MENUITEM"]);
            $sHTML .= "</ul>";
        }
        $sHTML .= "</li>\n";

        return $sHTML;
    }

    /**
     * Rerender the menu
     * @return string html content of the menu
     */
    public function rerender()
    {
        return $this->render();
    }
}

?>
