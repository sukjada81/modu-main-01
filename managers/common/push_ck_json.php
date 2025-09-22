<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array	= array();
$type		= checkPostVar('type');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');
if(!$type) json_error_msg('필수 정보가 넘어오지 못했습니다.');

if(!isset($_SERVER['HTTPS'])) json_error_msg('보안서버(https)가 적용된 환경에서만 사용이 가능 합니다.');

$item_array = array('push_apiKey', 'push_authDomain', 'push_projectId', 'push_storageBucket', 'push_messagingSenderId', 'push_appId', 'push_server_key', 'push_server_key2');
$item_field	= join(", ", $item_array );

$sql		= "SELECT {$item_field} FROM mallRN_configuration WHERE uid = 1";
$data		= $mysql->one_row($sql);

$ck			= 0;
foreach($item_array as $k => $v) {
	if(!$data[$v]) {
		$ck = 1;
		break;
	}
}

if($ck == 1) json_error_msg('환경설정 > 웹푸시(알림)설정에서 먼저 정보를 입력 하시기 바랍니다.');

$sql		= "UPDATE mallRN_admin_configuration SET push_yn = '{$type}' WHERE id = '{$my_id}'";
$mysql->query($sql);

if($type == 'Y')	echo json_encode(array('success' => '웹푸시(알림)을 사용 합니다.'));
else				echo json_encode(array('success' => '웹푸시(알림)을 사용하지 않습니다.'));

?>