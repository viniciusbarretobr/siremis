<?php 
require_once(OPENBIZ_BIN."easy/element/Listbox.php");
class RegionListbox extends Listbox{
	public function getFromList(&$list, $selectFrom = NULL)
    {    	
		require_once('Zend/Locale.php');
		$locale = new Zend_Locale(I18n::getInstance()->getCurrentLanguage());
		$code2name = $locale->getTranslationList('territory',$locale,2);
		$list = array();
		$i=0;
		foreach ($code2name as $key => $value){	
			if((int)$key==0){
				$list[$i]['val'] = $key;
	            $list[$i]['txt'] = $value;
	            $i++;  		
			}	
			
		}
		return $list;
    }
}
?>
