<?php
include_once (MODULE_PATH.'/sipadmin/service/siremisCharts.php');

class ChartsStatsUlsForm extends EasyForm 
{ 
   	protected $localService = "sipadmin.service.siremisCharts";
   	
   	protected function renderHTML()
	{
		global $g_BizSystem;

		$sHTML = '';

		$sql = "SELECT username, cflags, methods, user_agent, contact from location order by username";
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
		$ul_uas['yealink'] = 0;
		$ul_uas['csipsimple'] = 0;
		$ul_uas['zoiper'] = 0;
		$ul_uas['linphone'] = 0;
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
		$ul_proto['WS'] = 0;
		$ul_proto['WSS'] = 0;

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
				} else if(preg_match("/yealink/i", $r_user_agent)) {
					$ul_uas['yealink'] = $ul_uas['yealink'] + 1;
				} else if(preg_match("/csipsimple/i", $r_user_agent)) {
					$ul_uas['csipsimple'] = $ul_uas['csipsimple'] + 1;
				} else if(preg_match("/zoiper/i", $r_user_agent)) {
					$ul_uas['zoiper'] = $ul_uas['zoiper'] + 1;
				} else if(preg_match("/linphone/i", $r_user_agent)) {
					$ul_uas['linphone'] = $ul_uas['linphone'] + 1;
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
				} else if(preg_match("/;transport=wss/i", $r_contact)) {
					$ul_proto['WSS'] = $ul_proto['WSS'] + 1;
				} else if(preg_match("/;transport=ws/i", $r_contact)) {
					$ul_proto['WS'] = $ul_proto['WS'] + 1;
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
		$uachart = array();
		$ualabels = array();
		$ualegends = array();
		$uachart["title"] = array("text" => "UsrLoc User Agents",
				"textStyle"=>array("fontSize" => 12),
				"top"=>5, "left"=>20);

		$uavals = array();
		$uacolors = array();
		$i = 0;
		$ymax = 10;
		foreach($ul_uas as $key => $val) {
			if($val>0)
			{
				$uavals[$i] = array("value" => $val,
									"itemStyle" => array("normal" => array("color"=>$chart_colors[$i % $chart_colors_size])));
				$uacolors[$i] = $chart_colors[$i % $chart_colors_size];
				$ualabels[$i] = $key;
				if($ymax<$val)
					$ymax = $val;
				$i = $i + 1;
			}
		}
		$uaseries = array();
		$uaseries[0] = array();
		$uaseries[0]["name"] = "User Agents";
		$uaseries[0]["type"] = "bar";
		$uaseries[0]["data"] = $uavals;
		$uachart["tooltip"] = array("trigger" => "axis");
		$uachart["legend"] = array("data" => $ualegends, "top"=>25);
		$uachart["color"] = $uacolors;
		$uachart["xAxis"] = array("data" => $ualabels);
		$uachart["yAxis"] = new stdClass();
		$uachart["series"] = $uaseries;
		$uadata = json_encode($uachart);
	
		/* supported SIP Methods chart */
		$mtchart = array();
		$mtlabels = array();
		$mtlegends = array();
		$mtchart["title"] = array("text" => "UsrLoc SIP Methods",
				"textStyle"=>array("fontSize" => 12),
				"top"=>5, "left"=>20);

		$mtvals = array();
		$mtcolors = array();
		$i = 0;
		$ymax = 10;
		foreach($ul_methods as $key => $val) {
			if($val>0)
			{
				$mtvals[$i] = array("value" => $val,
									"itemStyle" => array("normal" => array("color"=>$chart_colors[$i % $chart_colors_size])));
				$mtcolors[$i] = $chart_colors[$i % $chart_colors_size];
				$mtlabels[$i] = $key;
				if($ymax<$val)
					$ymax = $val;
				$i = $i + 1;
			}
		}
		$mtseries = array();
		$mtseries[0] = array();
		$mtseries[0]["name"] = "SIP Methods";
		$mtseries[0]["type"] = "bar";
		$mtseries[0]["data"] = $mtvals;
		$mtchart["tooltip"] = array("trigger" => "axis");
		$mtchart["legend"] = array("data" => $mtlegends, "top"=>25);
		$mtchart["color"] = $mtcolors;
		$mtchart["xAxis"] = array("data" => $mtlabels);
		$mtchart["yAxis"] = new stdClass();
		$mtchart["series"] = $mtseries;
		$mtdata = json_encode($mtchart);

		/* bar stacks - contacts/user, nat stats, ... */

		$cnchart = array();
		$cnlabels = array();
		$cnlegends = array();
		$cnchart["title"] = array("text" => "UsrLoc Stats",
				"textStyle"=>array("fontSize" => 12),
				"top"=>5, "left"=>20);

		$cncolors = array();
		$c = 0;
		$sidx = 0;

		$cnlabels[0] = "v";

		$cnseries = array();

		$cnvals = array();
		$cnvals[0] = array("name" => "all", "value" => $yidx,
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "UsrLoc Records";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "all";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "online", "value" => $ousr,
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "Online Users";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "online";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "One Contact", "value" => $ul_contacts[1],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "User Contacts - One";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "ucontacts";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "Two Contacts", "value" => $ul_contacts[2],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "User Contacts - Two";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "ucontacts";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "Three Contacts", "value" => $ul_contacts[3],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "User Contacts - Three";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "ucontacts";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "Four Contacts", "value" => $ul_contacts[4],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "User Contacts - Four";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "ucontacts";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "Five Or More Contacts", "value" => $ul_contacts[5],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "User Contacts - Five Or More";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "ucontacts";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "NAT Users", "value" => $ul_nat['NATTED'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "NAT Users";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "unat";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "No NAT Users", "value" => $yidx - $ul_nat['NATTED'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "No NAT Users";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "unat";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "NAT SIP Ping", "value" => $ul_nat['SIPPING'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "NAT SIP Ping";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "unatsip";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "No NAT SIP Ping", "value" => $yidx - $ul_nat['SIPPING'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "No NAT SIP Ping";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "unatsip";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "UDP Contacts", "value" => $ul_proto['UDP'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "UDP Contacts";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "uproto";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "TCP Contacts", "value" => $ul_proto['TCP'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "TCP Contacts";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "uproto";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "TLS Contacts", "value" => $ul_proto['TLS'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "TLS Contacts";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "uproto";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "WS Contacts", "value" => $ul_proto['WS'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "WS Contacts";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "uproto";
		$cnseries[$sidx]["data"] = $cnvals;

		$sidx = $sidx + 1;
		$cnvals = array();
		$cnvals[0] = array("name" => "WSS Contacts", "value" => $ul_proto['WSS'],
									"itemStyle" => array("normal" => array("color"=>$chart_colors[($c++) % $chart_colors_size])));
		$cnseries[$sidx] = array();
		$cnseries[$sidx]["name"] = "WSS Contacts";
		$cnseries[$sidx]["type"] = "bar";
		$cnseries[$sidx]["stack"] = "uproto";
		$cnseries[$sidx]["data"] = $cnvals;

		$cnchart["tooltip"] = array("trigger" => "item");
		$cnchart["legend"] = array("data" => $cnlegends, "top"=>25);
		$cnchart["color"] = $mtcolors;
		$cnchart["xAxis"] = array("data" => $cnlabels);
		$cnchart["yAxis"] = new stdClass();
		$cnchart["series"] = $cnseries;
		$cndata = json_encode($cnchart);

		$sHTML .=
			'
			<div>
			<div align="center">
				<p><b>Processed ' . $yidx . ' Records.</b></p>
			</div>
			<br />
			';
			$sHTML .=
			'
			<div id="echarts" align="center">
				<br />
				<div id="echart_ulua" style="height:400px;"></div>
				<br />
				<br />
				<div id="echart_ulmt" style="height:400px;"></div>
				<br />
				<br />
				<div id="echart_ulcn" style="height:400px;"></div>
				<br />
			</div>
			';
		if($yidx>0) {
			$sHTML .=
				'
				<script type="text/javascript" src="'.APP_URL.'/modules/sipadmin/pages/echarts.min.js"></script>
				<script type="text/javascript">
				';
			$sHTML .=
				'
				var vChart_ulua = echarts.init(document.getElementById("echart_ulua"));
				var vOpts_ulua = JSON.parse(\''.$uadata.'\');
				vChart_ulua.setOption(vOpts_ulua);
				';
			$sHTML .=
				'
				var vChart_ulmt = echarts.init(document.getElementById("echart_ulmt"));
				var vOpts_ulmt = JSON.parse(\''.$mtdata.'\');
				vChart_ulmt.setOption(vOpts_ulmt);
				';
			$sHTML .=
				'
				var vChart_ulcn = echarts.init(document.getElementById("echart_ulcn"));
				var vOpts_ulcn = JSON.parse(\''.$cndata.'\');
				vChart_ulcn.setOption(vOpts_ulcn);
				';
		$sHTML .=
			'
			</script>';
		} /* if $yidx */
		$sHTML .=
			'
			</div>
			';
		return $sHTML;
   	}
}
