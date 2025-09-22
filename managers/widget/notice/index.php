<?php 

include_once("../widget_top.php");

$sql	= "SELECT count(*) FROM mallRN_inquiry WHERE answer = ''";
$CNT1	= number_format($mysql->get_one($sql));

if($CNT1 > 0) {
	$tpl->parse("is_inquiry");
}

$sql	= "SELECT uid FROM mallRN_board_counsel WHERE depth = 0";
$mysql->query($sql);

$CNT2	= 0;
while($row = $mysql->fetch_array()){
	$sql = "SELECT count(*) FROM mallRN_board_counsel WHERE depth = 1 && o_uid = '{$row['uid']}'";
	if($mysql->get_one($sql) == 0) $CNT2++;
}
$CNT2	= number_format($CNT2);

if($CNT2 > 0) {
	$tpl->parse("is_counsel");
}

$sql	= "SELECT uid FROM mallRN_board_vcounsel WHERE depth = 0";
$mysql->query($sql);

$CNT3	= 0;
while($row = $mysql->fetch_array()){
	$sql = "SELECT count(*) FROM mallRN_board_vcounsel WHERE depth = 1 && o_uid = '{$row['uid']}'";
	if($mysql->get_one($sql) == 0) $CNT3++;
}
$CNT3	= number_format($CNT3);

if($CNT3 > 0) {
	$tpl->parse("is_vcounsel");
}

$sql	= "SELECT count(*) FROM mallRN_order_status_change WHERE status2 = 1 && (status = 7 || status = 8)";
$CNT4	= number_format($mysql->get_one($sql));

if($CNT4 > 0) {
	$tpl->parse("is_order_status1");
}

$sql	= "SELECT count(*) FROM mallRN_order_status_change WHERE (status = 7 && (status2 = 2 || status2 = 3)) || (status = 8 && status2 = 2)";
$CNT5	= number_format($mysql->get_one($sql));

if($CNT5 > 0) {
	$tpl->parse("is_order_status2");
}


$sql	= "SELECT count(*) FROM mallRN_order_status_change WHERE (status = 9 && status2 = 1) || (status = 8 && status2 = 3)";
$CNT6	= number_format($mysql->get_one($sql));

if($CNT6 > 0) {
	$tpl->parse("is_order_status3");
}

$sql	= "SELECT status, order_num FROM mallRN_delivery_api_log WHERE uid > 0 ORDER BY uid DESC LIMIT 1";
if($data	= $mysql->one_row($sql)) {
	if($data['status'] == 2 && $data['order_num'] == '') $tpl->parse("is_delivery_api");	
}

$sql	= "SELECT count(*) FROM mallRN_goods WHERE uid > 0 && auth_ck = 'N'";
$CNT8	= number_format($mysql->get_one($sql));

if($CNT8 > 0) {
	$tpl->parse("is_vendor_goods");
}

$sql	= "SELECT count(*) FROM mallRN_order_cash_receipts WHERE uid > 0 && status = '0'";
$CNT9	= number_format($mysql->get_one($sql));

if($CNT9 > 0) {
	$tpl->parse("is_cash_receipts");
}

$sql	= "SELECT count(*) FROM mallRN_order_cancel_cp_log WHERE uid > 0 && status = 1 && proc = 0";
$CNT7	= number_format($mysql->get_one($sql));

if($CNT7 > 0) {
	$tpl->parse("is_order_cancel_cp");
}

if(!$CNT1 && !$CNT2 && !$CNT3 && !$CNT4 && !$CNT5 && !$CNT6 && !$CNT7 && !$CNT8 && !$CNT9 ) {
	$tpl->parse("empty_list");
}

include_once("../widget_bottom.php");

?>
