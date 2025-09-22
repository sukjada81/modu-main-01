<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('EXCEL_FOLDER', '../../image/excel');

include_once('../common/ad_init.php');

$mysql->msgType(2);

$type		= checkPostVar('type');
$uid		= checkPostVar('uid');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$type || !$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

if($type == 'price') {
	$price	= checkPostVar('price');
	$price	= str_replace(",", "", $price);

	$sql	= "UPDATE mallRN_member_level SET price = '{$price}' WHERE uid = '{$uid}'";
	$mysql->query($sql);
}
else if($type == 'coupon') {
	$coupon	= checkPostVar('coupon');
	if(!$coupon) $coupon = 0;

	$sql	= "UPDATE mallRN_member_level SET coupon_uid = '{$coupon}' WHERE uid = '{$uid}'";
	$mysql->query($sql);

}
else json_error_msg('필수 정보가 넘어오지 못했습니다.');

echo json_encode(array('success' => '1'));

?>