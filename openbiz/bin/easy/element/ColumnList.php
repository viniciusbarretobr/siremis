<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

include_once("OptionElement.php");

/**
 * ColumnList class is element that show description from "Selection.xml" on column/table view
 *
 * @package openbiz.bin.easy.element
 * @author Agus Suhartono, Rocky Swen (original ListBox and ColumnText author)
 * @copyright Copyright (c) 2009
 * @access public
 */
class ColumnList extends OptionElement
{
    public $m_Sortable;
    public $m_ColumnStyle;

    /**
     * Read metadata info from metadata array and store to class variable
     *
     * @param array $xmlArr metadata array
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_Sortable = isset($xmlArr["ATTRIBUTES"]["SORTABLE"]) ? $xmlArr["ATTRIBUTES"]["SORTABLE"] : null;
        $this->m_ColumnStyle = $this->m_Style;
    }

    /**
     * set the sort flag of the element
     *
     * @param integer $flag 1 or 0
     * @return void
     */
    public function setSortFlag($flag=null)
    {
        $this->m_SortFlag = $flag;
    }

    /**
     * Get link that evaluated by Expression::evaluateExpression
     *
     * @return string link
     */
    protected function getLink()
    {
        if ($this->m_Link == null)
            return null;
        $formobj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_Link, $formobj);
    }


    /**
     * When render table, it return the table header; when render array, it return the display name
     *
     * @return string HTML text
     */
    public function renderLabel()
    {
        if ($this->m_Sortable == "Y")
        {
            //$rule = "[" . $this->m_BizFieldName . "]";
            $rule = $this->m_Name;
            $function = $this->m_FormName . ".SortRecord(" . $rule . ")";
            $sHTML = "<a href=javascript:CallFunction('" . $function . "')>" . $this->m_Label . "</a>";
            if ($this->m_SortFlag == "ASC")
                $sHTML .= "<img src=\"".Resource::getImageUrl()."/up_arrow.gif\" />";
            elseif ($this->m_SortFlag == "DESC")
                $sHTML .= "<img src=\"".Resource::getImageUrl()."/down_arrow.gif\" />";
        }
        else
        {
            $sHTML = $this->m_Label;
        }
        return $sHTML;
    }

    /**
     * Draw/Render the element to show description
     *
     * @return string HTML text
     */
    public function render()
    {
        $fromList   = array();
        $this->getFromList($fromList);
        $value_arr  = explode(',', $this->m_Value);
        $style      = $this->getStyle();
        $func       = $this->getFunction();

        $selectedStr = '';

        $selectedStr = $this->m_Value;

        foreach ($fromList as $opt)
        {
            $test = array_search($opt['val'], $value_arr);
            if (!($test === false))
            {
                $selectedStr = $opt['txt'] ;
                break;
            }
        }

        if ($this->m_Link)
        {
            $link = $this->getLink();
            $sHTML = "<a href=\"$link\" $func $style>" . $selectedStr . "</a>";
        }
        else
            $sHTML = "<span $func $style>" . $selectedStr . "</span>";

        return $sHTML;
    }
}

?>
