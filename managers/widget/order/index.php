<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$date5		= date("m-d");
$sql		= "SELECT count(*) as total, SUM(IF(mobile = 'Y' , 1 , 0)) as mtotal FROM mallRN_order_info WHERE reals = 1 && INSTR(from_unixtime(signdate), '{$today}') && !((cancel_total > 0 || refund_total > 0) && pay_total = cancel_total + refund_total)";
$data		= $mysql->one_row($sql);

$value5		= $data['total'];
$TOTAL		= number_format($data['total']);
$PTOTAL		= number_format($data['total'] - $data['mtotal']);
$MTOTAL		= number_format($data['mtotal']);


$sql		= "SELECT count(*) as total, SUM(IF(pay_status != 'C' , 1 , 0)) as cnt1, SUM(IF(new = '1' , 1 , 0)) as cnt2 FROM mallRN_order_info WHERE reals = 1 && INSTR(from_unixtime(signdate), '{$today}') && !((cancel_total > 0 || refund_total > 0) && pay_total = cancel_total + refund_total)";
$data		= $mysql->one_row($sql);
$CNT1		= number_format($data['cnt1']);
$CNT2		= number_format($data['total'] - $data['cnt1']);
$CNT3		= number_format($data['cnt2']);
$CNT4		= number_format($data['total'] - $data['cnt2']);

for($i = 4, $i2 = 1; $i > 0; $i --) {
	$date			= date("Y-m-d", strtotime('-'.$i.' DAY', time()));
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT count(*) FROM mallRN_order_info WHERE reals = 1 && INSTR(from_unixtime(signdate), '{$date}') && !((cancel_total > 0 || refund_total > 0) && pay_total = cancel_total + refund_total)";
	${"value".$i2}	= $mysql->get_one($sql);
	$i2 ++;
}

include_once("../widget_bottom.php");

?>