<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array	= array();
$token		= checkPostVar('token');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');
if(!$token) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$is_mobile = preg_match('/'.MOBILE_AGENT.'/i', $_SERVER['HTTP_USER_AGENT']);

if($is_mobile == 1) $agent = "mobile";
else				$agent = "pc";

$sql		= "UPDATE mallRN_admin_configuration SET token_{$agent} = '{$token}' WHERE id = '{$my_id}'";
$mysql->query($sql);


echo json_encode(array('success' => '웹푸시(알림) 토큰이 등록 되었습니다.'));

?>