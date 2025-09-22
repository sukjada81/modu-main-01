<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$date5		= date("m-d");
$sql		= "SELECT count(*) as total FROM mallRN_goods WHERE vendor = '{$v_my_id}' && INSTR(from_unixtime(signdate), '{$today}')";
$data		= $mysql->one_row($sql);
$value5		= $data['total'];
$TOTAL		= number_format($data['total']);

$sql		= "SELECT count(*) FROM mallRN_goods WHERE vendor = '{$v_my_id}'";
$CNT1		= number_format($mysql->get_one($sql));

$sql		= "SELECT count(*) FROM mallRN_goods WHERE vendor = '{$v_my_id}' && ((qty_type = 0 && qty = 0 && option_use = 0) || option_soldout = 2)";
$CNT2		= number_format($mysql->get_one($sql));

for($i = 4, $i2 = 1; $i > 0; $i --) {
	$date			= date("Y-m-d", strtotime('-'.$i.' DAY', time()));
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT count(*) as total FROM mallRN_goods WHERE vendor = '{$v_my_id}' && INSTR(from_unixtime(signdate), '{$date}')";
	${"value".$i2}	= $mysql->get_one($sql);
	$i2 ++;
}

include_once("../widget_bottom.php");

?>