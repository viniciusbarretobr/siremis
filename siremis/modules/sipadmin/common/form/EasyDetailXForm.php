<?php
class EasyDetailXForm extends EasyForm 
{ 
    public function fetchDataSet()
	{
		return parent::fetchDataSet();
	}
    public function fetchData()
	{
		$resultRecords = parent::fetchDataSet();
        return $resultRecords[0];
	}
}
?>
