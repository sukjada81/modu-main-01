<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$vendor		= checkPostVar('vendor');
$signdate	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');
if(!$vendor) json_error_msg('필수 정보가 넘어오지 못했습니다.');
if(!$my_id) json_error_msg('먼저 로그인을 하시기 바랍니다.');

$sql = "SELECT count(*) FROM mallRN_favorite_store WHERE id = '{$my_id}' && vendor = '{$vendor}'";
if($mysql->get_one($sql) == 0) {

	$sql = "SELECT count(*) FROM mallRN_favorite_store WHERE id = '{$my_id}'";
	if($mysql->get_one($sql) == 100) {
		$sql = "DELETE FROM mallRN_favorite_store WHERE id = '{$my_id}' ORDER BY uid ASC LIMIT 1";
		$mysql->query($sql);
	}

	$sql = "INSERT INTO mallRN_favorite_store SET id = '{$my_id}', vendor = '{$vendor}', signdate = '{$signdate}'";
	$mysql->query($sql);

	echo json_encode(array('success' => '1'));
}
else {

	$sql = "DELETE FROM mallRN_favorite_store WHERE id = '{$my_id}' && vendor = '{$vendor}'";
	$mysql->query($sql);

	echo json_encode(array('success' => '2'));
}

?>