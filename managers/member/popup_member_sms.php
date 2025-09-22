<?php 

include_once("../common/popup_top.php");

$search_variable	= array('field' ,'keyword', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 's_range1', 'e_range1', 'range1', 's_range2', 'e_range2', 'range2', 's_range3', 'e_range3', 'range3', 'level', 'mailling', 'sms', 'auth', 'gender', 'marry', 'address1', 'mobile', 'sns_type', 'sort');

$addstring	= "";
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if(strlen($value)>0) $addstring .= "&{$v}={$value}";
}		

$where	= memberTypeWhere(2, '');

$sql = "SELECT count(*) FROM mallRN_member a WHERE a.uid > 0";
$TOTALS2 = $mysql->get_one($sql);	

if($where) {
	$sql = "SELECT count(*) FROM mallRN_member a WHERE a.uid > 0 {$where}";
	$TOTALS1 = $mysql->get_one($sql);
}
else {
	$TOTALS1 = $TOTALS2;
}
$TOTALS1 = number_format($TOTALS1);
$TOTALS2 = number_format($TOTALS2);

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_member_sms.html");
$tpl->scan_area("main");

$item_array = array('sms_yn','sms_key','sms_secret');

$sql = "SELECT * FROM mallRN_configuration WHERE uid=1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

if($sms_yn == 'Y') {
	$coolsms_key	= $sms_key;
	$coolsms_secret = $sms_secret;
	require_once(PATH_COOLSMS.'/lib/message.php');
	$res = get_balance();
	$coolsms_cash	= number_format($res->balance);
	$coolsms_point	= number_format($res->point);
}
else {
	iframeViewError("SMS미사용으로 사용 하실 수 없습니다.");
}

for($i = 0; $i < 24; $i++) {
	$hour = sprintf("%02d", $i);
	$tpl->parse("loop_hour");
}

for($i = 0; $i < 60; $i++) {
	$minute = sprintf("%02d", $i);
	$tpl->parse("loop_minute");
}

$this_day		= date("Y-m-d", time() + 600);
$this_hour		= date("H", time() + 600);
$this_minute	= date("i", time() + 600);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>