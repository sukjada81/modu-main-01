<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","member_level.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$DATE1			= date('Y-m-01', strtotime('-1 MONTH', time()));
$DATE2			= date('Y-m-t', strtotime('-1 MONTH', time()));
$DATE3			= date('Y-m-01', strtotime('-2 MONTH', time()));
$DATE4			= date('Y-m-01', strtotime('-3 MONTH', time()));
$DATE5			= date('Y-m-01', strtotime('-6 MONTH', time()));
######################## 변수 정의 #############################

######################## 쿠폰 리스트 #############################
$sql = "SELECT * FROM mallRN_coupon_manager WHERE type = 0 ORDER BY name ASC";
$mysql->query($sql);

$coupon_array = array();
while($row = $mysql->fetch_array()) {
	
	$coupon_name				= specialStrReplace($row['name']);
	$coupon_uid					= specialStrReplace($row['uid']);	

	$coupon_array[$coupon_uid] = $coupon_name;
}
######################## 쿠폰 리스트 #############################

######################## 회원 등급 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE level < 90 ORDER BY level ASC";
$mysql->query($sql);

$i = 0;
while($row = $mysql->fetch_array()) {

	$uid		= $row['uid'];
	$level		= $row['level'];
	$name		= stripslashes($row['name']);
	$price		= number_format($row['price']);

	if($row['coupon_uid'] == 0) $coupon = "";
	else						$coupon = $row['coupon_uid'];

	foreach($coupon_array as $k => $v) {
		$coupon_uid		= $k;
		$coupon_name	= $v;
		$tpl->parse("loop_coupon");
	}

	$zindex		= 100 - $level;

	if($i == 0) $readonly = "readonly";
	else		$readonly = "";

	$i ++;
	
	$tpl->parse("loop_level");
}
######################## 회원 등급 #############################

$sql	= "SELECT member_level_time, member_level_date FROM mallRN_configuration WHERE uid = 2";
$data	= $mysql->one_row($sql);
if($data['member_level_time']) {
	$member_level_time	= date("Y-m-d H:i:s", $data['member_level_time']);
	$member_level_date	= stripslashes($data['member_level_date']);

	$tpl->parse("is_level_time");
}

$s_date = $DATE1;	
$e_date = $DATE2;

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>