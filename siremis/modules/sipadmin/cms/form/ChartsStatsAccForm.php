<?php
include_once (MODULE_PATH.'/sipadmin/service/siremisCharts.php');

class ChartsStatsAccForm extends EasyForm 
{ 
   	protected $localService = "sipadmin.service.siremisCharts";
   	
   	protected function renderHTML()
	{
		global $g_BizSystem;

		include_once (MODULE_PATH.'/sipadmin/config/cms.ChartsStatsAccCfg.php');

		$sHTML = '';

		$fetchInterval = $cfg_stats_acc_fetch_interval;

		$sql = "SELECT method, sip_code, time, UNIX_TIMESTAMP(time) as tstamp FROM acc WHERE DATE_SUB(NOW(), INTERVAL " .$fetchInterval. " HOUR) <= time";
		$db = $g_BizSystem->GetDBConnection("Sipdb");
		$resultSet = $db->query($sql);
		if ($resultSet === false) {
			$err = $db->ErrorMsg();
			echo $err;
			exit;
		}

		$chart_colors = array();
		$chart_colors[0]  = '#FF0000';
		$chart_colors[1]  = '#00FF00';
		$chart_colors[2]  = '#0000FF';
		$chart_colors[3]  = '#408080';
		$chart_colors[4]  = '#330000';
		$chart_colors[5]  = '#FDD017';
		$chart_colors[6]  = '#52D017';
		$chart_colors[7]  = '#6698FF';
		$chart_colors[8]  = '#00FFFF';
		$chart_colors[9]  = '#FF00FF';
		$chart_colors[10] = '#2554C7';
		$chart_colors[11] = '#806D7E';
		$chart_colors[12] = '#FF8040';
		$chart_colors[13] = '#C0C0C0';
		$chart_colors[14] = '#808000';
		$chart_colors[15] = '#800000';
		$chart_colors_size = 16;

		$acc_records = array();
		$acc_records['invite']  = array();
		$acc_records['bye']     = array();
		$acc_records['message'] = array();
		$acc_records['other']   = array();
		$acc_records['invite200'] = array();
		$acc_records['invite404'] = array();
		$acc_records['invite487'] = array();
		$acc_records['inviteXYZ'] = array();
	
		for($i = 0; $i<=$fetchInterval; $i++)
		{
			$acc_records['invite'][$i]  = 0;
			$acc_records['bye'][$i]     = 0;
			$acc_records['message'][$i] = 0;
			$acc_records['other'][$i]   = 0;
			$acc_records['invite200'][$i] = 0;
			$acc_records['invite404'][$i] = 0;
			$acc_records['invite487'][$i] = 0;
			$acc_records['inviteXYZ'][$i] = 0;
		}
		$ymax = 0;		
		$ymin = 0x0fffffff;
		$yidx = 0;
		$ousr = 0;
		$ctime = time();
		$stime = $ctime - 3600*$fetchInterval;
		while(($row = $resultSet->fetch()))
		{
			$r_method   = $row[0];
			$r_sip_code = $row[1];
			$r_time     = $row[2];
			$r_tstamp   = $row[3];

			$idx = (int)(($r_tstamp - $stime)/3600);

			/* method stats */
			if(isset($r_method))
			{
				if($r_method=="INVITE") {
					$acc_records['invite'][$idx] = $acc_records['invite'][$idx] + 1;
					if($r_sip_code=="200") {
						$acc_records['invite200'][$idx] = $acc_records['invite200'][$idx] + 1;
					} else if($r_sip_code=="404") {
						$acc_records['invite404'][$idx] = $acc_records['invite404'][$idx] + 1;
					} else if($r_sip_code=="487") {
						$acc_records['invite487'][$idx] = $acc_records['invite487'][$idx] + 1;
					} else {
						$acc_records['inviteXYZ'][$idx] = $acc_records['inviteXYZ'][$idx] + 1;
					}
				} else if($r_method=="BYE") {
					$acc_records['bye'][$idx] = $acc_records['bye'][$idx] + 1;
				} else if($r_method=="MESSAGE") {
					$acc_records['message'][$idx] = $acc_records['message'][$idx] + 1;
				} else {
					$acc_records['other'][$idx] = $acc_records['other'][$idx] + 1;
				}
			}

			$yidx = $yidx + 1;
		}

		$time_min = $stime;
		$time_max = $ctime;
		$cidx = 0;

		/* sip method types chart */

		$mtchart = array();
		$mtlabels = array();
		$mtlegends = array();
		$mtchart["title"] = array("text" => "SIP Methods",
				"textStyle"=>array("fontSize" => 12),
				"top"=>5, "left"=>20);

		for($i = 0; $i < $fetchInterval; $i = $i + 1) {
			$mtlabels[$i] = date('H:i', $stime + 3600*$i);
		}

		$mtvals = array();
		$mtcolors = array();

		$mtseries = array();
		$sidx = 0;
		$mtcolors[$sidx] = $chart_colors[($cidx++) % $chart_colors_size];
		$mtlegends[$sidx] = "INVITE";
		$mtseries[$sidx] = array();
		$mtseries[$sidx]["name"] = "INVITE";
		$mtseries[$sidx]["type"] = "line";
		$mtseries[$sidx]["smooth"] = 0;
		$mtseries[$sidx]["data"] = $acc_records['invite'];
		$sidx = $sidx + 1;
		$mtcolors[$sidx] = $chart_colors[($cidx++) % $chart_colors_size];
		$mtlegends[$sidx] = "BYE";
		$mtseries[$sidx] = array();
		$mtseries[$sidx]["name"] = "BYE";
		$mtseries[$sidx]["type"] = "line";
		$mtseries[$sidx]["smooth"] = 0;
		$mtseries[$sidx]["data"] = $acc_records['bye'];

		if($cfg_stats_acc_message)
		{
			$sidx = $sidx + 1;
			$mtcolors[$sidx] = $chart_colors[ ($cidx++) % $chart_colors_size];
			$mtlegends[$sidx] = "MESSAGE";
			$mtseries[$sidx] = array();
			$mtseries[$sidx]["name"] = "MESSAGE";
			$mtseries[$sidx]["type"] = "line";
			$mtseries[$sidx]["smooth"] = 0;
			$mtseries[$sidx]["data"] = $acc_records['message'];
		}
		if($cfg_stats_acc_other)
		{
			$sidx = $sidx + 1;
			$mtcolors[$sidx] = $chart_colors[ ($cidx++) % $chart_colors_size];
			$mtlegends[$sidx] = "OTHER";
			$mtseries[$sidx] = array();
			$mtseries[$sidx]["name"] = "OTHER";
			$mtseries[$sidx]["type"] = "line";
			$mtseries[$sidx]["smooth"] = 0;
			$mtseries[$sidx]["data"] = $acc_records['other'];
		}

		$mtchart["tooltip"] = array("trigger" => "axis");
		$mtchart["legend"] = array("data" => $mtlegends, "top"=>25);
		$mtchart["color"] = $mtcolors;
		$mtchart["xAxis"] = array("data" => $mtlabels);
		$mtchart["yAxis"] = new stdClass();
		$mtchart["series"] = $mtseries;
		$mtdata = json_encode($mtchart);

		/* sip invites chart */
		$inchart = array();
		$inlabels = array();
		$inlegends = array();
		$inchart["title"] = array("text" => "INVITEs",
				"textStyle"=>array("fontSize" => 12),
				"top"=>5, "left"=>20);

		for($i = 0; $i < $fetchInterval; $i = $i + 1) {
			$inlabels[$i] = date('H:i', $stime + 3600*$i);
		}

		$invals = array();
		$incolors = array();

		$inseries = array();
		$sidx = 0;
		$incolors[$sidx] = $chart_colors[($cidx++) % $chart_colors_size];
		$inlegends[$sidx] = "200";
		$inseries[$sidx] = array();
		$inseries[$sidx]["name"] = "200";
		$inseries[$sidx]["type"] = "line";
		$inseries[$sidx]["smooth"] = 0;
		$inseries[$sidx]["data"] = $acc_records['invite200'];
		$sidx = $sidx + 1;
		$incolors[$sidx] = $chart_colors[($cidx++) % $chart_colors_size];
		$inlegends[$sidx] = "404";
		$inseries[$sidx] = array();
		$inseries[$sidx]["name"] = "404";
		$inseries[$sidx]["type"] = "line";
		$inseries[$sidx]["smooth"] = 0;
		$inseries[$sidx]["data"] = $acc_records['invite404'];
		$sidx = $sidx + 1;
		$incolors[$sidx] = $chart_colors[($cidx++) % $chart_colors_size];
		$inlegends[$sidx] = "487";
		$inseries[$sidx] = array();
		$inseries[$sidx]["name"] = "487";
		$inseries[$sidx]["type"] = "line";
		$inseries[$sidx]["smooth"] = 0;
		$inseries[$sidx]["data"] = $acc_records['invite487'];
		$sidx = $sidx + 1;
		$incolors[$sidx] = $chart_colors[($cidx++) % $chart_colors_size];
		$inlegends[$sidx] = "XYZ";
		$inseries[$sidx] = array();
		$inseries[$sidx]["name"] = "XYZ";
		$inseries[$sidx]["type"] = "line";
		$inseries[$sidx]["smooth"] = 0;
		$inseries[$sidx]["data"] = $acc_records['inviteXYZ'];

		$inchart["tooltip"] = array("trigger" => "axis");
		$inchart["legend"] = array("data" => $inlegends, "top"=>25);
		$inchart["color"] = $incolors;
		$inchart["xAxis"] = array("data" => $inlabels);
		$inchart["yAxis"] = new stdClass();
		$inchart["series"] = $inseries;
		$indata = json_encode($inchart);

		$sHTML .= 
			'
			<div>
			<div align="center">
				<p>
				<b>Processed ' . $yidx . ' Records<br />Timezone: ' . date_default_timezone_get () . '</b>
				<br />
				('.date('Y-m-d H:i:s', $time_min).' - '.date('Y-m-d H:i:s', $time_max).')
				</p>
			</div>
			';
		if($yidx>0) {
			$sHTML .=
			'
			<div id="echarts" align="center">
				<br />
				<div id="echart_accmt" style="height:400px;"></div>
				<br />
				<br />
				<div id="echart_accin" style="height:400px;"></div>
				<br />
			</div>
			';
			$sHTML .=
				'
				<script type="text/javascript" src="'.APP_URL.'/modules/sipadmin/pages/echarts.min.js"></script>
				<script type="text/javascript">
				';
			$sHTML .=
				'
				var vChart_accmt = echarts.init(document.getElementById("echart_accmt"));
				var vOpts_accmt = JSON.parse(\''.$mtdata.'\');
				vChart_accmt.setOption(vOpts_accmt);
				';
			$sHTML .=
				'
				var vChart_accin = echarts.init(document.getElementById("echart_accin"));
				var vOpts_accin = JSON.parse(\''.$indata.'\');
				vChart_accin.setOption(vOpts_accin);
				';
			$sHTML .=
				'
				</script>
				';
		} /* if $yidx */
		$sHTML .=
			'
			</div>
			';
		return $sHTML;
   	}
}