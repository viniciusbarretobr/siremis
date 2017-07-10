<?php

/**
 * Intervals for ACC Summary
 * - array of: (start hours ago, end hours ago, description)
 */
$cfg_summary_acc_intervals = array (
				array (72, 0, 'Last 72 Hours'),
				array (48, 0, 'Last 48 Hours'),
				array (24, 0, 'Last 24 Hours'),
				array (5, 4, '5 To 4 Hours Ago'),
				array (4, 3, '4 To 3 Hours Ago'),
				array (3, 2, '3 To 2 Hours Ago'),
				array (2, 1, '2 To 1 Hours Ago'),
				array (1, 0, 'Last Hour')
			);

$cfg_summary_acc_ranks = 5;

$cfg_summary_acc_categories = array (
				array ('src_user', 'Top Caller'),
				array ('dst_user', 'Top Callee')
			);
?>
