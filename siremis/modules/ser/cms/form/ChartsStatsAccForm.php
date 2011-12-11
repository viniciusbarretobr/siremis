<?php
include_once (MODULE_PATH.'/ser/service/siremisCharts.php');
include_once (MODULE_PATH.'/ser/service/asipto/charts/charts-lib.php');
include_once (MODULE_PATH.'/ser/config/cms.ChartsStatsAccCfg.php');

class ChartsStatsAccForm extends EasyForm 
{ 
   	protected $localService = "ser.service.siremisCharts";
   	
   	protected function renderHTML()
	{
		global $g_BizSystem;

		$fetchInterval = 24;

		$sql = "SELECT method, sip_code, time, UNIX_TIMESTAMP(time) as tstamp FROM acc WHERE DATE_SUB(NOW(), INTERVAL " .$fetchInterval. " HOUR) <= time";
		$db = $g_BizSystem->GetDBConnection("Serdb");
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

		/* sip method types chart */
		$mtsobj = new open_flash_chart();
		$ctitle = "SIP Method Types";
		$x = new x_axis();
		$xstep = (int)($fetchInterval / 12);
		if($fetchInterval % 2!=0)
			$xstep = $xstep + 1;
		$x->set_steps( $xstep );
		
		$time_min = $stime;
		$time_max = $ctime;
				
		$ctitle .= " - From ".date('Y-m-d H:i:s', $time_min); 
		$ctitle .= " To ".date('Y-m-d H:i:s', $time_max); 

		$time_x_labels = new x_axis_labels();
		$time_x_labels->rotate(20);
		$chart_lbls = array();
		for($i = 0; $i < $fetchInterval; $i = $i + 1)
		{
			$chart_lbls[] = date('H:i', $stime + 3600*$i);
		}

		$time_x_labels->visible_steps($xstep);
		$time_x_labels->set_labels($chart_lbls);
		$x->set_labels($time_x_labels);
		$mtsobj->set_title( new title( $ctitle ) );
		$dot_style = new dot();
		$dot_style
			->size(3)
			->halo_size(1);

		$clr = 0;
		$i = 0;
		$line[$i] = new line();
		$line[$i]->set_default_dot_style($dot_style);
		$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
		$line[$i]->set_key( "INVITE" , 10 );
		$line[$i]->set_values( $acc_records['invite'] );
		$mtsobj->add_element( $line[$i] );
		$i++;
		$line[$i] = new line();
		$line[$i]->set_default_dot_style($dot_style);
		$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
		$line[$i]->set_key( "BYE" , 10 );
		$line[$i]->set_values( $acc_records['bye'] );
		$mtsobj->add_element( $line[$i] );
		$i++;
		if($cfg_stats_acc_message)
		{
			$line[$i] = new line();
			$line[$i]->set_default_dot_style($dot_style);
			$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
			$line[$i]->set_key( "MESSAGE" , 10 );
			$line[$i]->set_values( $acc_records['message'] );
			$mtsobj->add_element( $line[$i] );
			$i++;
		}
		if($cfg_stats_acc_other)
		{
			$line[$i] = new line();
			$line[$i]->set_default_dot_style($dot_style);
			$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
			$line[$i]->set_key( "OTHER" , 10 );
			$line[$i]->set_values( $acc_records['other'] );
			$mtsobj->add_element( $line[$i] );
			$i++;
		}

		$val = max($acc_records['invite']);
		if($ymax<$val) $ymax = $val;
		$val = max($acc_records['bye']);
		if($ymax<$val) $ymax = $val;
		if($cfg_stats_acc_message)
		{
			$val = max($acc_records['message']);
			if($ymax<$val) $ymax = $val;
		}
		if($cfg_stats_acc_other)
		{
			$val = max($acc_records['other']);
			if($ymax<$val) $ymax = $val;
		}

		$val = min($acc_records['invite']);
		if($ymin>$val) $ymin = $val;
		$val = min($acc_records['bye']);
		if($ymin>$val) $ymin = $val;
		if($cfg_stats_acc_message)
		{
			$val = min($acc_records['message']);
			if($ymin>$val) $ymin = $val;
		}
		if($cfg_stats_acc_other)
		{
			$val = min($acc_records['other']);
			if($ymin>$val) $ymin = $val;
		}

		if($ymax>10)
		{
			$y = new y_axis();
			if($ymin>10)
				$y->set_range( $ymin-10, $ymax, (int)(($ymax-$ymin+10)/10) );
			else
				$y->set_range( 0, $ymax, (int)($ymax/10) );
			$mtsobj->set_y_axis( $y );
		}
		$mtsobj->set_x_axis( $x );
		$mtsobj->set_bg_colour( "#A0C0B0" );

		/* sip invites chart */
		$ivsobj = new open_flash_chart();
		$ctitle = "SIP INVITEs";
		$ctitle .= " - From ".date('Y-m-d H:i:s', $time_min); 
		$ctitle .= " To ".date('Y-m-d H:i:s', $time_max); 
		$ivsobj->set_title( new title( $ctitle ) );
		$ymax = 0;
		$ymin = 0x0fffffff;

		$i = 0;
		$line[$i] = new line();
		$line[$i]->set_default_dot_style($dot_style);
		$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
		$line[$i]->set_key( "200" , 10 );
		$line[$i]->set_values( $acc_records['invite200'] );
		$ivsobj->add_element( $line[$i] );
		$i++;
		$line[$i] = new line();
		$line[$i]->set_default_dot_style($dot_style);
		$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
		$line[$i]->set_key( "404" , 10 );
		$line[$i]->set_values( $acc_records['invite404'] );
		$ivsobj->add_element( $line[$i] );
		$i++;
		$line[$i] = new line();
		$line[$i]->set_default_dot_style($dot_style);
		$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
		$line[$i]->set_key( "487" , 10 );
		$line[$i]->set_values( $acc_records['invite487'] );
		$ivsobj->add_element( $line[$i] );
		$i++;
		$line[$i] = new line();
		$line[$i]->set_default_dot_style($dot_style);
		$line[$i]->set_colour( $chart_colors[($clr++) % $chart_colors_size] );
		$line[$i]->set_key( "XYZ" , 10 );
		$line[$i]->set_values( $acc_records['inviteXYZ'] );
		$ivsobj->add_element( $line[$i] );
		$i++;

		$val = max($acc_records['invite200']);
		if($ymax<$val) $ymax = $val;
		$val = max($acc_records['invite404']);
		if($ymax<$val) $ymax = $val;
		$val = max($acc_records['invite487']);
		if($ymax<$val) $ymax = $val;
		$val = max($acc_records['inviteXYZ']);
		if($ymax<$val) $ymax = $val;

		$val = min($acc_records['invite200']);
		if($ymin>$val) $ymin = $val;
		$val = min($acc_records['invite404']);
		if($ymin>$val) $ymin = $val;
		$val = min($acc_records['invite487']);
		if($ymin>$val) $ymin = $val;
		$val = min($acc_records['inviteXYZ']);
		if($ymin>$val) $ymin = $val;

		if($ymax>10)
		{
			$y = new y_axis();
			if($ymin>10)
				$y->set_range( $ymin-10, $ymax, (int)(($ymax-$ymin+10)/10) );
			else
				$y->set_range( 0, $ymax, (int)($ymax/10) );
			$ivsobj->set_y_axis( $y );
		}
		$ivsobj->set_x_axis( $x );
		$ivsobj->set_bg_colour( "#C0C0A0" );

		$sHTML = '';

		$sHTML .= 
			'
			<div align="center">
				<p><b>Processed ' . $yidx . ' records. <br />Timezone: ' . date_default_timezone_get () . ' </b></p>
			</div>
			';
		if($yidx>0) {
			$sHTML .= 
			'
			<script type="text/javascript" src="'.APP_URL.'/js/swfobject.js"></script>
			<script type="text/javascript">
				swfobject.embedSWF(
					"'.APP_URL.'/modules/ser/pages/open-flash-chart.swf",
				   	"div_chart_acc_methods",
					"600", "300", "9.0.0", "expressInstall.swf",
					{"get-data":"get_data_acc_methods"} );
				swfobject.embedSWF(
					"'.APP_URL.'/modules/ser/pages/open-flash-chart.swf",
				   	"div_chart_acc_invites",
					"600", "300", "9.0.0", "expressInstall.swf",
					{"get-data":"get_data_acc_invites"} );
			</script> 
			';

			$sHTML .= 
			'
			<br />
			<div align="center">
				<div id="div_chart_acc_methods">
				</div>
				<br />
				<br />
				<div id="div_chart_acc_invites">
				</div>
				<br />
				<br />
			</div>
			';

			$sHTML .= 
			'
			<script type="text/javascript">
				function get_data_acc_methods()
				{
					data = \'' . $mtsobj->toString() . '\';
					return data;
				}
				function get_data_acc_invites()
				{
					data = \'' . $ivsobj->toString() . '\';
					return data;
				}
			</script>
			';
		} /* if $yidx */
		return $sHTML;
   	}
}
