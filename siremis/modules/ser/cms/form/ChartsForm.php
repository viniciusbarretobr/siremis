<?php
include_once (MODULE_PATH.'/ser/service/siremisCharts.php');
include_once (MODULE_PATH.'/ser/service/asipto/charts/charts-lib.php');

class ChartsForm extends EasyForm 
{ 
   	protected $localService = "ser.service.siremisCharts";
   	
   	protected function renderHTML()
   	{
   		$cgobj = BizSystem::getObject($this->localService);
		$cgrp = $cgobj->GetChartGroup($_GET["cg"]);

		$sHTML = ""; 

		$sHTML .= '<br />
			<table id="micmds" align="center" width="100%">
			<tr align="center"><td align="center" colspan="2">
			';
		if(!$cgobj)
		{
			$sHTML .= 'Charts Service not configured';
			$sHTML .= '</td></tr></table>';
			return $sHTML;
		}

		$clist = $cgrp->GetChartList();
		
		$sHTML .= 
			'<strong>Charts Service Panel</strong><br /><br />
			</td></tr><tr><td align="center" colspan="2">
			';


		$sHTML .= 
			'
			<script type="text/javascript" src="'.APP_URL.'/js/swfobject.js"></script>
			<script type="text/javascript">
			';

		foreach ($clist as $chartobj) {
			$sHTML .= 
				'// chart: '.$chartobj->GetName(). '
				';

			$sHTML .= 
				'swfobject.embedSWF(
					"'.APP_URL.'/modules/ser/pages/open-flash-chart.swf",
					"div_chart_' . $chartobj->GetName() .'",
					"640", "300",
					"9.0.0", "",
					{"get-data":"get_data_'.$chartobj->GetName().'"} );
				';
		}
		$sHTML .= 
			'
			</script>';

		if($cgrp)
		{
			$sHTML .= 
				'<div align="center">';
			$clist = $cgrp->GetChartList();
			foreach ($clist as $chart => $chartobj) {
				$sHTML .= 
					'<div id="div_chart_'.$chartobj->GetName().'">
					</div>
					<br />
					<br />';
			}
			$sHTML .= 
				'</div>';
		} else {
			echo "No charts for ".$_GET["cg"];
		}

		$sHTML .= 
			'
			<script type="text/javascript">
			';

		foreach ($clist as $chartobj) {
			$sHTML .= 
				'
				function get_data_'.$chartobj->GetName().'()
				{
					data = \''.siremis_get_chart_data($_GET["cg"], $chartobj->GetName()).'\';
					return data;
				}
				';
		}

		$sHTML .= 
			'
		</script>';
		
		$sHTML .= 
			'<br />
			';
	
		$sHTML .= '</td></tr></table>';
		return $sHTML;
   	}
}
?>
