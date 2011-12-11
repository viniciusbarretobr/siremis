<?php
include_once (MODULE_PATH.'/ser/service/siremisCharts.php');

class SummaryStatsAccForm extends EasyForm 
{ 
   	protected $localService = "ser.service.siremisCharts";

   	protected function renderHTML()
	{
		global $g_BizSystem;

		/* load config file */
		include_once (MODULE_PATH.'/ser/config/cms.SummaryStatsAccCfg.php');

		$db = $g_BizSystem->GetDBConnection("Serdb");

		$acc_records = array();
		$acc_records['statstype'] = array();
		$acc_records['invite'] = array();
		$acc_records['bye'] = array();
		$acc_records['invite200'] = array();
		$acc_records['invite404'] = array();
		$acc_records['invite487'] = array();
		$acc_records['inviteXYZ'] = array();
		$acc_records['all'] = array();

		$idx = 0;

		foreach ($cfg_summary_acc_intervals as $i => $fetchInterval)
		{

			if($fetchInterval[1]==0) {
				$sql = "SELECT method, sip_code FROM acc WHERE"
					. "	(method='INVITE' OR method='BYE') AND (DATE_SUB(NOW(), INTERVAL "
					.$fetchInterval[0]. " HOUR) <= time)";
				//echo $sql;
			} else {
				$sql = "SELECT method, sip_code FROM acc WHERE"
					. "	(method='INVITE' OR method='BYE') AND (DATE_SUB(NOW(), INTERVAL "
					.$fetchInterval[0]. " HOUR) <= time AND DATE_SUB(NOW(), INTERVAL "
					.$fetchInterval[1]. " HOUR) > time)";
			}
			$resultSet = $db->query($sql);
			if ($resultSet === false) {
				$err = $db->ErrorMsg();
				echo $err;
				exit;
			}

			$acc_records['statstype'][$idx] = $fetchInterval[2];
			$acc_records['invite'][$idx]  = 0;
			$acc_records['bye'][$idx]     = 0;
			$acc_records['invite200'][$idx] = 0;
			$acc_records['invite404'][$idx] = 0;
			$acc_records['invite487'][$idx] = 0;
			$acc_records['inviteXYZ'][$idx] = 0;
	
			$yidx = 0;
			$ousr = 0;
			while(($row = $resultSet->fetch()))
			{
				$r_method   = $row[0];
				$r_sip_code = $row[1];

				if(isset($r_method))
				{
					if($r_method=="INVITE") {
						$acc_records['invite'][$idx]++;
						if($r_sip_code=="200") {
							$acc_records['invite200'][$idx]++;
						} else if($r_sip_code=="404") {
							$acc_records['invite404'][$idx]++;
						} else if($r_sip_code=="487") {
							$acc_records['invite487'][$idx]++;
						} else {
							$acc_records['inviteXYZ'][$idx]++;
						}
					} else if($r_method=="BYE") {
						$acc_records['bye'][$idx]++;
					}
				}

				$yidx = $yidx + 1;
			}
			$acc_records['all'][$idx] = $yidx;
			$idx++;
		}

		$sHTML = '';

		$sHTML .= 
			'
			<div align="center">
				<p><b>' . date("F j, Y, g:i a") . '<br />Timezone: '
					. date_default_timezone_get () . ' </b></p>
			</div>
			<br />
			<br />
			<br />
			<div align="center">
			';
		$sHTML .= 
			'
			<table border="0" cellpadding="0" cellspacing="0" class="form_table">
			<tr>
				<th>Period</th>
				<th>INVITE - ALL</th>
				<th>INVITE - 200</th>
				<th>INVITE - 404</th>
				<th>INVITE - 487</th>
				<th>INVITE - XYZ</th>
				<th>BYE - ALL</th>
				<th>ALL RECORDS</th>
			</tr>
			';
		for($i=0; $i < $idx; $i++)
		{
			$sHTML .= 
				'
				<tr>
					<td>' . $acc_records['statstype'][$i] . '</td>
					<td align="center">' . $acc_records['invite'][$i] . '</td>
					<td align="center">' . $acc_records['invite200'][$i] . '</td>
					<td align="center">' . $acc_records['invite404'][$i] . '</td>
					<td align="center">' . $acc_records['invite487'][$i] . '</td>
					<td align="center">' . $acc_records['inviteXYZ'][$i] . '</td>
					<td align="center">' . $acc_records['bye'][$i] . '</td>
					<td align="center">' . $acc_records['all'][$i] . '</td>
				</tr>
				';
		}
		$sHTML .= 
			'
			</table>
			';
		$sHTML .= 
			'
			</div>
			';
		return $sHTML;
   	}
}
