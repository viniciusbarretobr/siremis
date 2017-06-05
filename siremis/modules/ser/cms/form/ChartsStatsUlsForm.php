<?php
include_once (MODULE_PATH.'/ser/service/siremisCharts.php');
include_once (MODULE_PATH.'/ser/service/asipto/charts/charts-lib.php');

class ChartsStatsUlsForm extends EasyForm 
{ 
   	protected $localService = "ser.service.siremisCharts";
   	
   	protected function renderHTML()
	{
		global $g_BizSystem;

		$sql = "SELECT username, cflags, methods, user_agent, contact from location order by username";
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

		$ul_contacts = array();
		for($i = 0; $i<=5; $i++)
			$ul_contacts[$i] = 0;
		$prevuser = "...";
		$prevcnt = 1;

		$ul_uas = array();
		$ul_uas['asterisk'] = 0;
		$ul_uas['audiocodes'] = 0;
		$ul_uas['freeswitch'] = 0;
		$ul_uas['x-lite'] = 0;
		$ul_uas['eyebeam'] = 0;
		$ul_uas['bria'] = 0;
		$ul_uas['ekiga'] = 0;
		$ul_uas['twinkle'] = 0;
		$ul_uas['snom'] = 0;
		$ul_uas['cisco'] = 0;
		$ul_uas['linksys'] = 0;
		$ul_uas['nokia'] = 0;
		$ul_uas['grandstream'] = 0;
		$ul_uas['polycom'] = 0;
		$ul_uas['draytek'] = 0;
		$ul_uas['avm'] = 0;
		$ul_uas['sipura'] = 0;
		$ul_uas['mitel'] = 0;
		$ul_uas['others'] = 0;

		$ul_methods = array();
		$ul_methods['INVITE'] = 0;
		$ul_methods['CANCEL'] = 0;
		$ul_methods['ACK'] = 0;
		$ul_methods['BYE'] = 0;
		$ul_methods['REGISTER'] = 0;
		$ul_methods['OPTIONS'] = 0;
		$ul_methods['UPDATE'] = 0;
		$ul_methods['PRACK'] = 0;
		$ul_methods['SUBSCRIBE'] = 0;
		$ul_methods['NOTIFY'] = 0;
		$ul_methods['PUBLISH'] = 0;
		$ul_methods['MESSAGE'] = 0;
		$ul_methods['INFO'] = 0;
		$ul_methods['REFER'] = 0;
		$ul_methods['OTHERS'] = 0;
		$ul_methods['NONE'] = 0;

		$ul_nat = array();
		$ul_nat['NATTED'] = 0;
		$ul_nat['SIPPING'] = 0;

		$ul_proto = array();
		$ul_proto['UDP'] = 0;
		$ul_proto['TCP'] = 0;
		$ul_proto['TLS'] = 0;
		$ul_proto['SCTP'] = 0;

		$yidx = 0;
		$ousr = 0;
		while(($row = $resultSet->fetch()))
		{
			$r_username   = $row[0];
			$r_cflags     = $row[1];
			$r_methods    = $row[2];
			$r_user_agent = $row[3];
			$r_contact    = $row[4];

			/* statistics for contacts per user */
			if($yidx == 0)
			{
				$prevuser = $r_username;
				$prevcnt = 1;
			} else {
				if($r_username == $prevuser)
				{
					$prevcnt = $prevcnt + 1;
				} else {
					$ousr++;
					if($prevcnt < 5)
					{
						$ul_contacts[$prevcnt] = $ul_contacts[$prevcnt] + 1;
					} else {
						$ul_contacts[5] = $ul_contacts[5] + 1;
					}
					$prevuser = $r_username;
					$prevcnt = 1;
				}
			}
			/* known UA stats */
			if(isset($r_user_agent))
			{
				if(preg_match('/asterisk/i', $r_user_agent)) {
					$ul_uas['asterisk'] = $ul_uas['asterisk'] + 1;
				} else if(preg_match("/audiocodes/i", $r_user_agent)) {
					$ul_uas['audiocodes'] = $ul_uas['audiocodes'] + 1;
				} else if(preg_match("/freeswitch/i", $r_user_agent)) {
					$ul_uas['freeswitch'] = $ul_uas['freeswitch'] + 1;
				} else if(preg_match("/x-lite/i", $r_user_agent)) {
					$ul_uas['x-lite'] = $ul_uas['x-lite'] + 1;
				} else if(preg_match("/bria/i", $r_user_agent)) {
					$ul_uas['bria'] = $ul_uas['bria'] + 1;
				} else if(preg_match("/ekiga/i", $r_user_agent)) {
					$ul_uas['ekiga'] = $ul_uas['ekiga'] + 1;
				} else if(preg_match("/twinkle/i", $r_user_agent)) {
					$ul_uas['twinkle'] = $ul_uas['twinkle'] + 1;
				} else if(preg_match("/snom/i", $r_user_agent)) {
					$ul_uas['snom'] = $ul_uas['snom'] + 1;
				} else if(preg_match("/cisco/i", $r_user_agent)) {
					$ul_uas['cisco'] = $ul_uas['cisco'] + 1;
				} else if(preg_match("/linksys/i", $r_user_agent)) {
					$ul_uas['linksys'] = $ul_uas['linksys'] + 1;
				} else if(preg_match("/nokia/i", $r_user_agent)) {
					$ul_uas['nokia'] = $ul_uas['nokia'] + 1;
				} else if(preg_match("/grandstream/i", $r_user_agent)) {
					$ul_uas['grandstream'] = $ul_uas['grandstream'] + 1;
				} else if(preg_match("/polycom/i", $r_user_agent)) {
					$ul_uas['polycom'] = $ul_uas['polycom'] + 1;
				} else if(preg_match("/draytek/i", $r_user_agent)) {
					$ul_uas['draytek'] = $ul_uas['draytek'] + 1;
				} else if(preg_match("/avm/i", $r_user_agent)) {
					$ul_uas['avm'] = $ul_uas['avm'] + 1;
				} else if(preg_match("/sipura/i", $r_user_agent)) {
					$ul_uas['sipura'] = $ul_uas['sipura'] + 1;
				} else if(preg_match("/mitel/i", $r_user_agent)) {
					$ul_uas['mitel'] = $ul_uas['mitel'] + 1;
				} else {
					$ul_uas['others'] = $ul_uas['others'] + 1;
				}
			}

			/* transports */
			if(isset($r_contact))
			{
				if(preg_match('/;transport=tcp/i', $r_contact)) {
					$ul_proto['TCP'] = $ul_proto['TCP'] + 1;
				} else if(preg_match("/;transport=tls/i", $r_contact)) {
					$ul_proto['TLS'] = $ul_proto['TLS'] + 1;
				} else if(preg_match("/;transport=sctp/i", $r_contact)) {
					$ul_proto['SCTP'] = $ul_proto['SCTP'] + 1;
				} else {
					$ul_proto['UDP'] = $ul_proto['UDP'] + 1;
				}
			}

			/* supported SIP methods stats */
			if(isset($r_methods) && $r_methods!=0)
			{
				/* 1 - 2^0 INVITE */
				if($r_methods & 1) {
					$ul_methods['INVITE'] = $ul_methods['INVITE'] + 1;
				}
				/* 2 - 2^1 CANCEL */
				if($r_methods & 2) {
					$ul_methods['CANCEL'] = $ul_methods['CANCEL'] + 1;
				}
				/* 3 - 2^2 ACK */
				if($r_methods & 4) {
					$ul_methods['ACK'] = $ul_methods['ACK'] + 1;
				}
				/* 4 - 2^3 BYE */
				if($r_methods & 8) {
					$ul_methods['BYE'] = $ul_methods['BYE'] + 1;
				}
				/* 5 - 2^4 INFO */
				if($r_methods & 16) {
					$ul_methods['INFO'] = $ul_methods['INFO'] + 1;
				}
				/* 6 - 2^5 REGISTER */
				if($r_methods & 32) {
					$ul_methods['REGISTER'] = $ul_methods['REGISTER'] + 1;
				}
				/* 7 - 2^6 SUBSCRIBE */
				if($r_methods & 64) {
					$ul_methods['SUBSCRIBE'] = $ul_methods['SUBSCRIBE'] + 1;
				}
				/* 8 - 2^7 NOTIFY */
				if($r_methods & 128) {
					$ul_methods['NOTIFY'] = $ul_methods['NOTIFY'] + 1;
				}
				/* 9 - 2^8 MESSAGE */
				if($r_methods & 256) {
					$ul_methods['MESSAGE'] = $ul_methods['MESSAGE'] + 1;
				}
				/* 10 - 2^9 OPTIONS */
				if($r_methods & 512) {
					$ul_methods['OPTIONS'] = $ul_methods['OPTIONS'] + 1;
				}
				/* 11 - 2^10 PRACK */
				if($r_methods & 1024) {
					$ul_methods['PRACK'] = $ul_methods['PRACK'] + 1;
				}
				/* 12 - 2^11 UPDATE */
				if($r_methods & 2048) {
					$ul_methods['UPDATE'] = $ul_methods['UPDATE'] + 1;
				}
				/* 13 - 2^12 REFER */
				if($r_methods & 4096) {
					$ul_methods['REFER'] = $ul_methods['REFER'] + 1;
				}
				/* 14 - 2^13 PUBLISH */
				if($r_methods & 8192) {
					$ul_methods['PUBLISH'] = $ul_methods['PUBLISH'] + 1;
				}
				/* 15 - 2^14 OTHER */
				if($r_methods & 16384) {
					$ul_methods['OTHERS'] = $ul_methods['OTHERS'] + 1;
				}
			} else {
				$ul_methods['NONE'] = $ul_methods['NONE'] + 1;
			}

			/* supported NAT stats */
			if(isset($r_cflags) && $r_cflags!=0)
			{
				if($r_cflags & (1<<6))
					$ul_nat['NATTED'] = $ul_nat['NATTED'] + 1;
				if($r_cflags & (1<<7))
					$ul_nat['SIPPING'] = $ul_nat['SIPPING'] + 1;
			}

			$yidx = $yidx + 1;
		}

		if($yidx>0)
		{
			$ousr++;
			if($prevcnt < 5)
			{
				$ul_contacts[$prevcnt] = $ul_contacts[$prevcnt] + 1;
			} else {
				$ul_contacts[5] = $ul_contacts[5] + 1;
			}
		}
		/* user agents chart */
		$ua_title = new title( 'User Agents' );
		$ua_x_labels = new x_axis_labels();
		$ua_x_labels->rotate(20);
		$ua_bar = new bar_glass();
		$chart_vals = array();
		$chart_lbls = array();
		$i = 0;
		$ymax = 10;
		foreach($ul_uas as $key => $val) {
			if($val>0)
			{
				$chart_vals[$i] = new bar_value($val);
				$chart_vals[$i]->set_colour($chart_colors[$i % $chart_colors_size]);
				$chart_vals[$i]->set_tooltip( $key.'<br>#val#' );
				$chart_lbls[$i] = $key;
				if($ymax<$val)
					$ymax = $val;
				$i = $i + 1;
			}
		}
		$ua_bar->set_values( $chart_vals );	
		$ua_x_labels->set_labels($chart_lbls);
		$x = new x_axis();
		$x->set_labels($ua_x_labels);
		$y = new y_axis();
		$y->set_range( 0, $ymax, $ymax/10 );
		$ul_uas_chart = new open_flash_chart();
		$ul_uas_chart->set_title( $ua_title );
		$ul_uas_chart->add_element( $ua_bar );
		$ul_uas_chart->set_x_axis( $x );
		$ul_uas_chart->add_y_axis( $y );
	
		/* supported SIP Methods chart */
		$mt_title = new title( 'Supported SIP Methods' );
		$mt_x_labels = new x_axis_labels();
		$mt_x_labels->rotate(20);
		$mt_bar = new bar_glass();
		$chart_vals = array();
		$chart_lbls = array();
		$i = 0;
		$ymax = 10;
		foreach($ul_methods as $key => $val) {
			if($val>0)
			{
				$chart_vals[$i] = new bar_value($val);
				$chart_vals[$i]->set_colour($chart_colors[$i % $chart_colors_size]);
				$chart_vals[$i]->set_tooltip( $key.'<br>#val#' );
				$chart_lbls[$i] = $key;
				if($ymax<$val)
					$ymax = $val;
				$i = $i + 1;
			}
		}
		$mt_bar->set_values( $chart_vals );	
		$mt_x_labels->set_labels($chart_lbls);
		$x = new x_axis();
		$x->set_labels($mt_x_labels);
		$y = new y_axis();
		$y->set_range( 0, $ymax, $ymax/10 );
		$mt_chart = new open_flash_chart();
		$mt_chart->set_title( $mt_title );
		$mt_chart->add_element( $mt_bar );
		$mt_chart->set_x_axis( $x );
		$mt_chart->add_y_axis( $y );

		/* bar stacks - contacts/user, nat stats, ... */

		$cn_title = new title('Contacts and NAT Stats');
		$cn_x_labels = new x_axis_labels();
		$cn_x_labels->rotate(20);

		$bar_stack = new bar_stack();
		$bar_stack->set_colours( $chart_colors );
		$chart_lbls = array();
		$c = 0;
		$lidx = 0;
	
		$chart_vals = array();
		$i = 0;
		$chart_vals[$i] = new bar_stack_value($yidx,
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'Records<br>#val#' );
		$bar_stack->append_stack($chart_vals);
		$chart_lbls[$lidx++] = 'All Records';

		$chart_vals = array();
		$i = 0;
		$chart_vals[$i] = new bar_stack_value($ousr,
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'Online<br>#val#' );
		$bar_stack->append_stack($chart_vals);
		$chart_lbls[$lidx++] = 'Online Users';

		$chart_vals = array();
		$i = 0;
		$chart_vals[$i] = new bar_stack_value($ul_contacts[1],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( '1 contact<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_contacts[2],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( '2 contacts<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_contacts[3],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( '3 contacts<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_contacts[4],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( '4 contacts<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_contacts[5],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( '&gt;=5 contacts <br>#val#' );
		$i = $i + 1;
		$bar_stack->append_stack($chart_vals);
		$chart_lbls[$lidx++] = 'Contacts per AoR';

		$chart_vals = array();
		$i = 0;
		$chart_vals[$i] = new bar_stack_value($ul_nat['NATTED'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'Natted<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($yidx - $ul_nat['NATTED'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'Not-Natted<br>#val#' );
		$bar_stack->append_stack($chart_vals);
		$chart_lbls[$lidx++] = 'Natted';

		$chart_vals = array();
		$i = 0;
		$chart_vals[$i] = new bar_stack_value($ul_nat['SIPPING'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'SIP Ping<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($yidx - $ul_nat['SIPPING'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'No SIP Ping<br>#val#' );
		$bar_stack->append_stack($chart_vals);
		$chart_lbls[$lidx++] = 'SIP Ping';

		$chart_vals = array();
		$i = 0;
		$chart_vals[$i] = new bar_stack_value($ul_proto['UDP'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'UDP<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_proto['TCP'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'TCP<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_proto['TLS'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'TLS<br>#val#' );
		$i = $i + 1;
		$chart_vals[$i] = new bar_stack_value($ul_proto['SCTP'],
							$chart_colors[($c++) % $chart_colors_size]);
		$chart_vals[$i]->set_tooltip( 'SCTP<br>#val#' );
		$bar_stack->append_stack($chart_vals);
		$chart_lbls[$lidx++] = 'Transports';

		$cn_x_labels->set_labels($chart_lbls);
		$x = new x_axis();
		$x->set_labels($cn_x_labels);
		$y = new y_axis();
		$y->set_range( 0, $yidx, $yidx/10 );
		$cn_chart = new open_flash_chart();
		$cn_chart->set_title( $cn_title );
		$cn_chart->add_element( $bar_stack );
		$cn_chart->set_x_axis( $x );
		$cn_chart->add_y_axis( $y );

		$sHTML = '';

		$sHTML .= 
			'
			<div align="center">
				<p><b>Processed ' . $yidx . ' records.</b></p>
			</div>
			';
		if($yidx>0) {
			$sHTML .= 
			'
			<script type="text/javascript" src="'.APP_URL.'/js/swfobject.js"></script>
			<script type="text/javascript">
				swfobject.embedSWF(
					"'.APP_URL.'/modules/ser/pages/open-flash-chart.swf",
				   	"div_chart_ul_uas",
					"600", "300", "9.0.0", "expressInstall.swf",
					{"get-data":"get_data_ul_uas"} );
				swfobject.embedSWF(
					"'.APP_URL.'/modules/ser/pages/open-flash-chart.swf",
				   	"div_chart_ul_met",
					"600", "300", "9.0.0", "expressInstall.swf",
					{"get-data":"get_data_ul_met"} );
				swfobject.embedSWF(
					"'.APP_URL.'/modules/ser/pages/open-flash-chart.swf",
				   	"div_chart_ul_cns",
					"600", "300", "9.0.0", "expressInstall.swf",
				{"get-data":"get_data_ul_cns"} );
			</script> 
			';

			$sHTML .= 
			'
			<br />
			<div align="center">
				<div id="div_chart_ul_uas">
				</div>
				<br />
				<br />
				<div id="div_chart_ul_met">
				</div>
				<br />
				<br />
				<div id="div_chart_ul_cns">
				</div>
				<br />
				<br />
			</div>
			';

			$sHTML .= 
			'
			<script type="text/javascript">
				function get_data_ul_uas()
				{
					data = \'' . $ul_uas_chart->toString() . '\';
					return data;
				}

				function get_data_ul_met()
				{
					data = \'' . $mt_chart->toString() . '\';
					return data;
				}

				function get_data_ul_cns()
				{
					data = \'' . $cn_chart->toString() . '\';
					return data;
				}

			</script>
			';
		} /* if $yidx */
		return $sHTML;
   	}
}
