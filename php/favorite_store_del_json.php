<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$uid		= checkPostVar('uid');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');
if(!$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql = "DELETE FROM mallRN_favorite_store WHERE uid = '{$uid}' && id = '{$my_id}'";
$mysql->query($sql);

echo json_encode(array('success' => '정상처리 되었습니다.'));

?>