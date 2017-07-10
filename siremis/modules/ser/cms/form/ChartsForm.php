<?php
include_once (MODULE_PATH.'/sipadmin/service/siremisCharts.php');
include_once (MODULE_PATH.'/sipadmin/service/asipto/charts/charts-lib.php');

class ChartsForm extends EasyForm 
{ 
   	protected $localService = "sipadmin.service.siremisCharts";
   	
   	protected function renderHTML()
   	{
   		$cgobj = BizSystem::getObject($this->localService);
		$cgrp = $cgobj->GetChartGroup($_GET["cg"]);

		$sHTML = ""; 

		$sHTML .= '<br />
			<div id="echarts" align="center">
			';
		if(!$cgobj)
		{
			$sHTML .= '<p>Charts Service Not Configured</p>';
			$sHTML .= '</div>';
			return $sHTML;
		}

		$clist = $cgrp->GetChartList();
		
		$sHTML .= 
			'<p><strong>Charts Service Panel</strong></p><br />
			';

		foreach ($clist as $chart => $chartobj) {
			$sHTML .=
				'
				<br />
				<div id="echart_'.$chartobj->GetName().'" style="height:400px;">
				</div>
				<br />
				';
		}

		$sHTML .= 
			'
			<script type="text/javascript" src="'.APP_URL.'/modules/sipadmin/pages/echarts.min.js"></script>
			<script type="text/javascript">
			';

		foreach ($clist as $chartobj) {
			$sHTML .= 
				'// echart: '.$chartobj->GetName(). '
				';

			$sHTML .= 
				'
				var vChart_'.$chartobj->GetName().' = echarts.init(document.getElementById("echart_'.$chartobj->GetName().'"));
				var vOpts_'.$chartobj->GetName().' = JSON.parse(\''.siremis_get_chart_data($_GET["cg"], $chartobj->GetName()).'\');
				vChart_'.$chartobj->GetName().'.setOption(vOpts_'.$chartobj->GetName().');
				';
		}
		$sHTML .= 
			'
			</script>';

		$sHTML .= '</div>';
		return $sHTML;
   	}
}
?>
