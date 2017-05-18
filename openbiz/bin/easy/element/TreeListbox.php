<?php 
include_once (OPENBIZ_BIN."/easy/element/InputElement.php");
class TreeListbox extends InputElement
{
    public $m_BlankOption;
    public $m_SelectFrom;
    public $m_SelectFieldName;

    /**
     * Read metadata info from metadata array and store to class variable
     *
     * @param array $xmlArr metadata array
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_BlankOption = isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;
        $this->m_SelectFrom = isset($xmlArr["ATTRIBUTES"]["SELECTFROM"]) ? $xmlArr["ATTRIBUTES"]["SELECTFROM"] : null;
    }

    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $fromList = array();
        $this->getFromList($fromList);
        $valueArray = explode(',', $this->m_Value);
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();

        $sHTML = "<SELECT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" $disabledStr $this->m_HTMLAttr $style $func>";

        if ($this->m_BlankOption) // ADD a blank option

        {
            $entry = explode(",",$this->m_BlankOption);
            $text = $entry[0];
            $value = ($entry[1]!= "") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text ));
            $fromList = array_merge($entryList, $fromList);
        }

        foreach ($fromList as $option)
        {
            $test = array_search($option['val'], $valueArray);
            if ($test === false)
            {
                $selectedStr = '';
            }
            else
            {
                $selectedStr = "SELECTED";
            }
            $sHTML .= "<OPTION VALUE=\"" . $option['val'] . "\" $selectedStr>" . $option['txt'] . "</OPTION>";
        }
        $sHTML .= "</SELECT>";
        /* editable combobox
        <div style="position: relative;">
        <select style="position: absolute; width: 146px; height: 18px; z-index: 1; clip: rect(auto, auto, auto, 127px);">
        <option value="" selected="selected"/>
        <option value="Homer">Homer</option>
        <option value="Marge">Marge</option>
        <option value="Bart">Bart</option>
        <option value="Lisa">Lisa</option>
        <option value="Maggie">Maggie</option>
        </select>
        <div>
        <input type="text" style="width: 128px; height: 20px;"/>
        </div>
        </div>
        */
        return $sHTML;
    }

    /**
     * Get from list
     *
     * @param array $list
     * @return <type>
     */
    public function getFromList(&$list)
    {
        $selectFrom = $this->getSelectFrom();

        // from XML file (Selection)
        $pos0 = strpos($selectFrom, "(");
        $pos1 = strpos($selectFrom, ")");
        if ($pos0>0 && $pos1 > $pos0)
        {  // select from xml file
            $xmlFile = substr($selectFrom, 0, $pos0);
            $tag = substr($selectFrom, $pos0 + 1, $pos1 - $pos0-1);
            $tag = strtoupper($tag);
            $xmlFile = BizSystem::GetXmlFileWithPath ($xmlFile);
            if (!$xmlFile) return;

            $xmlArr = &BizSystem::getXmlArray($xmlFile);
            if ($xmlArr)
            {
                $i = 0;
                if (!key_exists($tag, $xmlArr["SELECTION"]))
                    return;
                foreach($xmlArr["SELECTION"][$tag] as $node)
                {
                    $list[$i]['val'] = $node["ATTRIBUTES"]["VALUE"];
                    if ($node["ATTRIBUTES"]["TEXT"])
                    {
                        $list[$i]['txt'] = I18n::getInstance()->translate($node["ATTRIBUTES"]["TEXT"]) ;
                    }
                    else
                    {
                        $list[$i]['txt'] = I18n::getInstance()->translate($list[$i]['val']);
                    }
                    $i++;
                }
            }
            return;
        }

        // from Database
        $pos0 = strpos($selectFrom, "[");
        $pos1 = strpos($selectFrom, "]");

        if ($pos0 > 0 && $pos1 > $pos0)
        {  // select from bizObj
            // support BizObjName[BizFieldName] or BizObjName[BizFieldName4Text:BizFieldName4Value]
            $bizObjName = substr($selectFrom, 0, $pos0);
            $pos3 = strpos($selectFrom, ":");
            if($pos3 > $pos0 && $pos3 < $pos1)
            {
                $fieldName = substr($selectFrom, $pos0 + 1, $pos3 - $pos0 - 1);
                $fieldName_v = substr($selectFrom, $pos3 + 1, $pos1 - $pos3 - 1);
            }
            else
            {
                $fieldName = substr($selectFrom, $pos0 + 1, $pos1 - $pos0 - 1);
                $fieldName_v = $fieldName;
            }
            $this->m_SelectFieldName = $fieldName; 
            $commaPos = strpos($selectFrom, ",", $pos1);
            $commaPos2 = strpos($selectFrom, ",", $commaPos+1);
            
            if ($commaPos > $pos1)
            {
				if($commaPos2){
            		$searchRule = trim(substr($selectFrom, $commaPos + 1, ($commaPos2-$commaPos-1)));
				}
				else
				{
					$searchRule = trim(substr($selectFrom, $commaPos + 1));
				}
            }

            if ($commaPos2 > $commaPos)
                $rootSearchRule = trim(substr($selectFrom, $commaPos2 + 1));
                
            $bizObj = BizSystem::getObject($bizObjName);
            if (!$bizObj)
                return;

            $recList = array();

            $oldAssoc = $bizObj->m_Association;
            $bizObj->m_Association = null;

            if ($searchRule)
            {
                $searchRule = Expression::evaluateExpression($searchRule, $this->getFormObj());
            }
			
            if($rootSearchRule)
            {
            	$rootSearchRule = Expression::evaluateExpression($rootSearchRule, $this->getFormObj());            	
            }else{
            	$rootSearchRule = "[PId]=0 OR [PId]='' OR [PId] is NULL";
            }
            
            $recListTree = $bizObj->fetchTree($rootSearchRule,100,$searchRule);
            $bizObj->m_Association = $oldAssoc;

            if (!$recListTree) return; // bugfix : error if data blank

            foreach($recListTree as $recListTreeNode)
            {
                $this->tree2array($recListTreeNode, $recList);
            }

            foreach ($recList as $rec)
            {
                $list[$i]['val'] = $rec[$fieldName_v];
                $list[$i]['txt'] = $rec[$fieldName];
                $i++;
            }
            return;
        }

        // in case of a|b|c
        $recList = explode('|',$selectFrom);
        foreach ($recList as $rec)
        {
            $list[$i]['val'] = $rec;
            $list[$i]['txt'] = $rec;
            $i++;
        }
        return;
    }

    protected function getSelectFrom()
    {
        $formObj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_SelectFrom, $formObj);
    }

    private function tree2array($tree,&$array,$level=0)
    {
        if(!is_array($array))
        {
            $array=array();
        }

        $treeNodeArray = array(
                "Level" => $level,
                "Id" => $tree->m_Id,
                "PId" => $tree->m_PId,
        );
        foreach ($tree->m_Record as $key=>$value)
        {
            $treeNodeArray[$key] = $value;
        }
        $treeNodeArray[$this->m_SelectFieldName] = "+".str_repeat("--", $level).$treeNodeArray[$this->m_SelectFieldName];

        array_push($array, $treeNodeArray);
        $level++;
        if(is_array($tree->m_ChildNodes))
        {
            foreach($tree->m_ChildNodes as $treeNode)
            {
                $this->tree2array($treeNode, $array, $level);
            }
        }
        return $array;
    }
}
?>