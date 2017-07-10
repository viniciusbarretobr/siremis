<?php

class SipTraceSearchForm extends EasyForm 
{ 
   	protected $localListForm = "sipadmin.srv.siptrace.form.SipTraceListForm";
	
	public function searchSipTrace($id=null)
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
        
   		$listFormObj = BizSystem::getObject($this->localListForm);
   		$listFormObj->setSearchRule($searchRule, $searchRuleBindValues);
   		$listFormObj->rerender();
   	}
}
?>
