<?php
   function siremis_get_chart_data($groupId, $chartId, $debug=0)
   {
		global $g_BizSystem;
		$cgobj = $g_BizSystem->GetService("sipadmin.service.siremisCharts");
		$cgrp = $cgobj->GetChartGroup($groupId);
		if($debug==1)
			echo "Chart found $id :: ".$cgobj->GetChartGroupCount()." ::: ";
		$chart = $cgrp->GetChart($chartId);
		if(!$chart)
		{
			echo "Chart not found $id\n";
			exit;
		}
		if($debug==1)
			echo "Chart :: ".$cgrp->GetChartCount()." ::: ";
		$sql = "SELECT * ";
		$xydata = array();
		// XAxis
		$xax = $chart->GetXAxisList();
		foreach ($xax as $it) {
			$sql = "SELECT ".$it->GetXYData();
			$xydata[0] = $it;
		}
		// YAxis
		$yax = $chart->GetYAxisList();
		$yn = 0;
		foreach ($yax as $it) {
			$sql .= ",".$it->GetXYData();
			$yn = $yn + 1;
			$xydata[$yn] = $it;
		}
		if($debug==1)
			echo "::::::: $yn ::: ".$chart->GetYAxisCount();
		$sql .= " FROM ".$chart->GetTable();
		if($chart->GetOrderBy() && $chart->GetOrderBy()!="")
			$sql .= " ".$chart->GetOrderBy();
		if($chart->GetLimit() && $chart->GetLimit()!="")
			$sql .= " ".$chart->GetLimit();
		// echo " - sql: " . $sql;
		$db = $g_BizSystem->GetDBConnection("Sipdb");
		$resultSet = $db->query($sql);
		if ($resultSet === false) {
			 $err = $db->ErrorMsg();
			 echo $err;
			 exit;
		}
		$xdata = array();
		$ydata = array();		
		for($i = 0; $i<$yn; $i=$i+1){
			$ydata[$i] = array();
		}
		$k = 0;
		$ymin = 0;
		$ymax = 0;

		while(($row = $resultSet->fetch()))
		{
			$xdata[$k] = $row[0];
			for($i = 0; $i < $yn; $i = $i + 1)
			{
				$ydata[$i][$k] = 0 + $row[$i+1];
				if($k==0) {
					$ymin = $ydata[$i][$k];
					$ymax = $ydata[$i][$k];
				} else {
					if($ydata[$i][$k]<$ymin)
						$ymin = $ydata[$i][$k];
					if($ydata[$i][$k]>$ymax)
						$ymax = $ydata[$i][$k];
				}
			}
			$k = $k + 1;
		}

		$ctitle = $chart->GetTitle();
		$ecdata = array();
		$clabels = array();
		$clegends = array();
		$ccolors = array("#FF7588","#40C7CA","#FFA87D");

		$rev = 0;
		if($chart->GetOrder() && $chart->GetOrder()=="reverse")
			$rev = 1;
		$xstep = (int)($k/20);
		if($k%20!=0)
			$xstep = $xstep + 1;
		if($xydata[0]->getXYType()=="timestamp")
		{
			if($rev==1) {
				$time_min = $xdata[$k-1];
				$time_max = $xdata[0];
			} else {
				$time_min = $xdata[0];
				$time_max = $xdata[$k-1];
			}
			$ctitle .= " (".date('Y-m-d H:i:s', $time_min);
			$ctitle .= " - ".date('Y-m-d H:i:s', $time_max).")";

			for($i = 0; $i < $k; $i = $i + 1)
			{
				if($rev==0) {
					$clabels[] = date('H:i', $xdata[$i]);
				} else {
					$clabels[] = date('H:i', $xdata[$k- $i -1]);
				}
			}
		} else {
			if($rev==1) {
				$ctitle .= " (".$xdata[$k-1]." - ".$xdata[0].")";
			} else {
				$ctitle .= " (".$xdata[0]." - ".$xdata[$k-1].")";
			}
			for($i = 0; $i < $k; $i = $i + 1)
			{
				if($rev==0) {
					$clabels[] = $xdata[$i];
				} else {
					$clabels[] = $xdata[$k- $i -1];
				}
			}
		}

		$series = array();
		for($i = 0; $i<$yn; $i++) {
			$series[$i] = array();
			if($xydata[$i+1]->GetXYTitle() && $xydata[$i+1]->GetXYTitle()!="") {
				$series[$i]["name"] = $xydata[$i+1]->GetXYTitle();
				$clegends[$i] = $xydata[$i+1]->GetXYTitle();
			} else {
				$series[$i]["name"] = "Key ".$i;
				$clegends[$i] = "Key ".$i;
			}
			if($xydata[$i+1]->GetXYColor() && $xydata[$i+1]->GetXYColor()!="")
				$ccolors[$i] = $xydata[$i+1]->GetXYColor();
			$series[$i]["type"] = "line";
			$series[$i]["smooth"] = 0;
			if($chart->GetChartType()=="area") {
				$series[$i]["itemStyle"] = array("normal" => array("areaStyle" => array("type" => "default")));
			} else if($chart->GetChartType()=="line_dot") {
				//
			} else {
				//
			}
			if($rev==1) {
				$series[$i]["data"] = array_reverse( $ydata[$i] );
			} else {
				$series[$i]["data"] = $ydata[$i] ;
			}
		}

		$ecdata["title"] = array("text" => $ctitle,
				"textStyle"=>array("fontSize" => 12),
				"top"=>5, "left"=>20);
		$ecdata["tooltip"] = array("trigger" => "axis");
		$ecdata["legend"] = array("data" => $clegends, "top"=>25);
		$ecdata["color"] = $ccolors;
		$ecdata["xAxis"] = array("data" => $clabels);
		$ecdata["yAxis"] = new stdClass();
		$ecdata["series"] = $series;
		// return json_encode($ecdata, JSON_PRETTY_PRINT);
		return json_encode($ecdata);
   }
?> 
