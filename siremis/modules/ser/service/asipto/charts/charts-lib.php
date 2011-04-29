<?php
// include_once("../../../../../bin/app.inc");
include_once(MODULE_PATH."/ser/pages/ofc/open-flash-chart.php");

   function siremis_get_chart_data($groupId, $chartId, $debug=0)
   {
		global $g_BizSystem;
		$cgobj = $g_BizSystem->GetService("ser.service.siremisCharts");
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
		// " ORDER BY id DESC LIMIT 30";
		$db = $g_BizSystem->GetDBConnection("Serdb");
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
		$ofcobj = new open_flash_chart();
		$ctitle = $chart->GetTitle();
		$rev = 0;
		if($chart->GetOrder() && $chart->GetOrder()=="reverse")
			$rev = 1;
		$x = new x_axis();
		$xstep = (int)($k/20);
		if($k%20!=0)
			$xstep = $xstep + 1;
		$x->set_steps( $xstep );
		if($xydata[0]->getXYType()=="timestamp")
		{
			if($rev==1) {
				$time_min = $xdata[$k-1];
				$time_max = $xdata[0];
			} else {
				$time_min = $xdata[0];
				$time_max = $xdata[$k-1];
			}
			$ctitle .= " - From ".date('Y-m-d H:i:s', $time_min); 
			$ctitle .= " To ".date('Y-m-d H:i:s', $time_max); 

			$time_x_labels = new x_axis_labels();
			$time_x_labels->rotate(20);
			$chart_lbls = array();
			for($i = 0; $i < $k; $i = $i + 1)
			{
				if($rev==0) {
					$chart_lbls[] = date('H:i', $xdata[$i]);
				} else {
					$chart_lbls[] = date('H:i', $xdata[$k- $i -1]);
				}
			}

			$time_x_labels->visible_steps($xstep);
			$time_x_labels->set_labels($chart_lbls);
			$x->set_labels($time_x_labels);
		} else {
			$time_x_labels->visible_steps($xstep);
			if($rev==1) {
				$ctitle .= " - From ".$xdata[$k-1]." To ".$xdata[0]; 
			} else {
				$ctitle .= " - From ".$xdata[0]." To ".$xdata[$k-1]; 
			}
		}
		$ofcobj->set_title( new title( $ctitle ) );
		$dot_style = new dot();
		$dot_style
			->size(3)
			->halo_size(1);
		for($i = 0; $i<$yn; $i++)
		{
			if($chart->GetChartType()=="area") {
				$line[$i] = new area();
				$line[$i]->set_fill_alpha( 0.30 );
				$line[$i]->set_default_dot_style($dot_style);
			} else if($chart->GetChartType()=="line_dot") {
				$line[$i] = new line_dot();
				$line[$i]->set_default_dot_style($dot_style);
			} else {
				$line[$i] = new line();
				$line[$i]->set_default_dot_style($dot_style);
			}
			if($xydata[$i+1]->GetXYColor() && $xydata[$i+1]->GetXYColor()!="")
				$line[$i]->set_colour( $xydata[$i+1]->GetXYColor() );
			if($xydata[$i+1]->GetXYTitle() && $xydata[$i+1]->GetXYTitle()!="")
				$line[$i]->set_key( $xydata[$i+1]->GetXYTitle() , 10 );
			else
				$line[$i]->set_key( "Key ".$i , 10 );
			if($rev==1) {
				$line[$i]->set_values( array_reverse( $ydata[$i] ) );
			} else {
				$line[$i]->set_values( $ydata[$i] );
			}
			$ofcobj->add_element( $line[$i] );
		}
		if($ymax>10)
		{
			$y = new y_axis();
			if($ymin>10)
				$y->set_range( $ymin-10, $ymax, (int)(($ymax-$ymin+10)/10) );
			else
				$y->set_range( 0, $ymax, (int)($ymax/10) );
			$ofcobj->set_y_axis( $y );
		}
		$ofcobj->set_x_axis( $x );
		if($chart->GetBGColor() && $chart->GetBGColor()!="")
			$ofcobj->set_bg_colour( $chart->GetBGColor() );
		// return $ofcobj->toPrettyString();
		return $ofcobj->toString();
   }
?> 
