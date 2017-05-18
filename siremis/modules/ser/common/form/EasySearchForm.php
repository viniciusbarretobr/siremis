<?php

class EasySearchForm extends EasyForm 
{ 
	public function SwitchSearchForm($switchForm)
   	{
   		include_once(OPENBIZ_BIN."/easy/SearchHelper.php");
        $searchRule = "";
        foreach ($this->m_DataPanel as $element)
        {
            if (!$element->m_FieldName)
                continue;

            $value = BizSystem::clientProxy()->getFormInputs($element->m_Name);
            if($element->m_FuzzySearch=="Y")
            {
                $value="*$value*";
            }
            if ($value)
            {
                $searchStr = inputValToRule($element->m_FieldName, $value, $this);
                if ($searchRule == "")
                    $searchRule .= $searchStr;
                else
                    $searchRule .= " AND " . $searchStr;
            }
        }
        
        $searchRuleBindValues = QueryStringParam::getBindValues();
        
        $this->switchForm($switchForm);
   		$listFormObj = BizSystem::getObject($switchForm);
   		$listFormObj->setSearchRule($searchRule, $searchRuleBindValues);
   		$listFormObj->rerender();
   	}

	public function SwitchToView($switchView, $switchiRule)
   	{
		return BizSystem::clientProxy()->redirectView($switchView, $switchRule);
	}

	public function SwitchSearchFieldForm($switchForm, $fieldName, $fieldValue)
   	{
   		include_once(OPENBIZ_BIN."/easy/SearchHelper.php");

        $this->switchForm($switchForm);
   		$listFormObj = BizSystem::getObject($switchForm);

		QueryStringParam::reset();

		$searchRule = inputValToRule($fieldName, $fieldValue, $this);
        $searchRuleBindValues = QueryStringParam::getBindValues();

   		$listFormObj->setSearchRule($searchRule, $searchRuleBindValues);
   		$listFormObj->rerender();
	}
}
?>
