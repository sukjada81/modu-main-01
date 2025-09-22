<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$uid		= checkPostVar('uid');
$c_uid		= checkPostVar('c_uid');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$my_id) json_error_msg('먼저 로그인을 하시기 바랍니다.');
if(!$uid || !$c_uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql = "SELECT * FROM mallRN_coupon_manager WHERE uid = '{$c_uid}' && type = 4";
if(!$data = $mysql->one_row($sql)) json_error_msg('발급발을 쿠폰이 존재하지 않거나 삭제 되었습니다.');

if($data['use_limit2'] > 0) {
	$sql		= "SELECT count(*) FROM mallRN_coupon WHERE c_uid = '{$c_uid}' && g_uid = '{$g_uid}' && id = '{$my_id}'";
	$down_cnt	= $mysql->get_one($sql);

	if($data['use_limit2'] <= $down_cnt) json_error_msg('쿠폰발급가능수량을 초과하였습니다.');
}

couponIssuance($c_uid, $my_id, $uid);

echo json_encode(array('success' => '1'));

?>