<?php 

include_once("../widget_top.php");

$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array	= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");

$sql = "SELECT * FROM mallRN_order_info WHERE reals = 1 ORDER BY uid DESC LIMIT 4";
$mysql->query($sql);

$i = 0;
while($row = $mysql->fetch_array()) {
	$ORDER_NUM		= $row['order_num'];
	$NAME			= stripslasheS($row['name']);
	$PAY_TOTAL		= number_format($row['pay_total']);
	$PAY_TYPE		= $pay_type_array[$row['pay_type']];
	$PAY_STATUS		= $pay_status_array[$row['pay_status']];

	if($row['pay_total'] = $row['cancel_total'] + $row['refund_total']) $PAY_STATUS	= "주문취소"; 

	$sql			= "SELECT g_name, count(*) as cnt FROM mallRN_order_goods WHERE order_num = '{$row['order_num']}' && reals = 1";
	$data			= $mysql->one_row($sql);

	$G_NAME			= stripslashes($data['g_name']);

	if($data['cnt'] > 1) $G_NAME .= "외 ".($data['cnt'] - 1)." 건";
	
	$tpl->parse("loop_list");
	$i ++;
}

if($i == 0) $tpl->parse("empty_list");

include_once("../widget_bottom.php");

?>