<?php 

include_once("../widget_top.php");

$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array	= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");

$sql = "SELECT * FROM mallRN_order_goods a WHERE vendor = '{$v_my_id}' && reals = 1 ORDER BY uid DESC LIMIT 4";
$mysql->query($sql);

$i = 0;
while($row = $mysql->fetch_array()) {
	$ORDER_NUM		= $row['order_num'];
	$G_NAME			= stripslashes($row['g_name']);
	$PAY_TOTAL		= number_format($row['orig_price'] * $row['qty']);
	
	$sql			= "SELECT name, pay_type, pay_status FROM mallRN_order_info WHERE order_num = '{$row['order_num']}' && reals = 1";
	$data			= $mysql->one_row($sql);
	$NAME			= stripslasheS($data['name']);
	$PAY_TYPE		= $pay_type_array[$data['pay_type']];
	$PAY_STATUS		= $pay_status_array[$data['pay_status']];
	
	$tpl->parse("loop_list");
	$i ++;
}

if($i == 0) $tpl->parse("empty_list");

include_once("../widget_bottom.php");

?>