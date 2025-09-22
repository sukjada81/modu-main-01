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
$tpl->define("main","popup_member_coupon.html");
$tpl->scan_area("main");

######################## 쿠폰 리스트 #############################
$sql = "SELECT * FROM mallRN_coupon_manager WHERE type = 0 ORDER BY name ASC";
$mysql->query($sql);

$cnt = 0;
while($row = $mysql->fetch_array()) {
	
	$coupon_name				= specialStrReplace($row['name']);
	$coupon_uid					= specialStrReplace($row['uid']);	

	if($row['discount_type'] == 'P') {
		if($row['discount_limit'] > 0)	$coupon_info1 =  $row['discount']."% (최대 ".number_format($row['discount_limit'])."원)";
		else							$coupon_info1 =  $row['discount']."%";
	}
	else {
		$coupon_info1 = number_format($row['discount'])."원";
	}

	if($row['use_type'] == 0)	$coupon_info2 = substr($row['use_s_date'], 0, 10)." ~ ".substr($row['use_e_date'], 0, 10);
	else						$coupon_info2 = "발급 후 ".$row['use_day']."일";	

	$tpl->parse("loop_coupon");
	$tpl->parse("loop_coupon2");
	$cnt ++;
}

if($cnt == 0) iframeViewError("등록된 관리자 수동발급 쿠폰이 없습니다. 쿠폰등록 후 사용이 가능 합니다.");
######################## 쿠폰 리스트 #############################

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>