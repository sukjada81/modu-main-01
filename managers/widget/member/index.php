<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$date5		= date("m-d");
$sql		= "SELECT count(*) as total, SUM(IF(mobile = 'Y' , 1 , 0)) as mtotal FROM mallRN_member WHERE INSTR(from_unixtime(signdate), '{$today}')";
$data		= $mysql->one_row($sql);
$value5		= $data['total'];
$TOTAL		= number_format($data['total']);
$PTOTAL		= number_format($data['total'] - $data['mtotal']);
$MTOTAL		= number_format($data['mtotal']);

$sql		= "SELECT count(*) FROM mallRN_member";
$CNT1		= number_format($mysql->get_one($sql));

$sql		= "SELECT count(*) FROM mallRN_member_withdrawal WHERE INSTR(from_unixtime(signdate), '{$today}')";
$CNT2		= number_format($mysql->get_one($sql));

for($i = 4, $i2 = 1; $i > 0; $i --) {
	$date			= date("Y-m-d", strtotime('-'.$i.' DAY', time()));
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT count(*) as total FROM mallRN_member WHERE INSTR(from_unixtime(signdate), '{$date}')";
	${"value".$i2}	= $mysql->get_one($sql);
	$i2 ++;
}

include_once("../widget_bottom.php");

?>