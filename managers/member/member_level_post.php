<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$s_date			= checkPostVar('s_date');
$e_date			= checkPostVar('e_date');
$signdate		= time();

if(!$s_date || !$e_date) logMsg('필수 정보가 넘어오지 못했습니다.');

######################## 회원 등급 #############################
$sql			= "SELECT * FROM mallRN_member_level WHERE level < 90 ORDER BY price DESC";
$mysql->query($sql);

$level_array	= array();
$default_level	= 1;
while($row = $mysql->fetch_array()) {
	$level_array[]	= array($row['level'], $row['price'], $row['coupon_uid']);
	$default_level	= $row['level'];
}
######################## 회원 등급 #############################

$where			= "&& from_unixtime(signdate) BETWEEN '{$s_date}' AND '{$e_date} 23:59:59' ";

$sql			= "SELECT id, order_time FROM mallRN_member WHERE uid > 0 && auth = 'Y' && level < 90";
$mysql->query($sql);

while($row = $mysql->fetch_array()){ 
	if($row['order_time'] == 0 || $row['order_time'] < strtotime($s_date)) {
		$order_sum	= 0;	
	}
	else {
		$sql		= "SELECT SUM(IF(status = 0, price, -price)) FROM mallRN_order_sales WHERE id = '{$row['id']}' && confirmation = '1' && (type = 0 || type = 4 || (type = 3 && g_uid != 0)) {$where}";	
		$order_sum	= $mysql->get_one($sql);	
		if(!$order_sum) $order_sum = 0;
	}
	
	$coupon			= 0;
	$level			= 1;
	foreach($level_array as $k => $v) {		
		if($order_sum >= $v[1]) {
			$level	= $v[0];
			$coupon	= $v[2];
			break;
		}
	}
	
	if(!$level) $level = $default_level;
	$sql			= "UPDATE mallRN_member SET level = '{$level}' WHERE id = '{$row['id']}'";
	$mysql->query2($sql);

	if($coupon) {
		couponIssuance($coupon, $row['id']);		
	}
}

$sql = "UPDATE mallRN_configuration SET member_level_time = '{$signdate}', member_level_date = '{$s_date} ~ {$e_date}' WHERE uid = 2";
$mysql->query($sql);

logMsg("회원등급평가가 완료 되었습니다.","success");

?>
