<?php 

include_once("../widget_top.php");

$sql	= "SELECT count(*) FROM mallRN_inquiry WHERE vendor = '{$v_my_id}' && answer = ''";
$CNT1	= number_format($mysql->get_one($sql));

if($CNT1 > 0) {
	$tpl->parse("is_inquiry");
}

$sql	= "SELECT count(*) FROM mallRN_order_status_change WHERE vendor = '{$v_my_id}' && status2 = 1 && (status = 7 || status = 8)";
$CNT2	= number_format($mysql->get_one($sql));

if($CNT2 > 0) $tpl->parse("is_order_status1");

$sql	= "SELECT count(*) FROM mallRN_order_status_change WHERE vendor = '{$v_my_id}' && ((status = 7 && (status2 = 2 || status2 = 3)) || (status = 8 && status2 = 2))";
$CNT3	= number_format($mysql->get_one($sql));

if($CNT3 > 0) $tpl->parse("is_order_status2");

if(!$CNT1 && !$CNT2 && !$CNT3) {
	$tpl->parse("empty_list");
}


include_once("../widget_bottom.php");

?>