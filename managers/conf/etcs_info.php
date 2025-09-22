<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","etcs_info.html");
$tpl->scan_area("main");

$item_array = array('order_cancel_info', 'order_message_info', 'order_auto_completed1','order_auto_completed2','order_auto_completed3','order_tracker_yn','order_tracker_key','sms_yn','sms_key','sms_secret', 'sms_pfid', 'sms_calling_number','sms_admin_number1','sms_admin_number2','sms_admin_number3');

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

if($order_cancel_info) {
	$cancel_info = explode("|*|",$order_cancel_info);
	foreach($cancel_info as $k => $v) {
		$cancel_info2 = explode("|",$v);
		$cancel_name = $cancel_info2[0];
		$cancel_used = $cancel_info2[1];
		if($cancel_used!='1') $cancel_used = "0";
		$tpl->parse("loop_cancel");
	}	
}
$cancel_name = $cancel_used = "";
$tpl->parse("loop_cancel");

if($order_message_info) {
	$message_info = explode("|*|",$order_message_info);
	foreach($message_info as $k => $v) {
		$message_info2 = explode("|",$v);
		$message_name = $message_info2[0];
		$message_used = $message_info2[1];
		if($message_used!='1') $message_used = "0";
		$tpl->parse("loop_message");
	}	
}
$message_name = $message_used = "";
$tpl->parse("loop_message");

${"checked_order_tracker_yn_".$order_tracker_yn}	= "checked='chedked'";
${"checked_sms_yn_".$sms_yn}						= "checked='chedked'";
if($sms_yn == 'Y') {
	$coolsms_key	= $sms_key;
	$coolsms_secret = $sms_secret;
	require_once(PATH_COOLSMS.'/lib/message.php');
	$res = get_balance();
	$coolsms_cash	= number_format($res->balance);
	$coolsms_point	= number_format($res->point);
	$tpl->parse("is_sms_Y");
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>