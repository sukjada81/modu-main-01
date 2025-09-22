<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();
$id			= add_escape_re_string(checkGetVar('id'));

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$id) {
	$my_array[] = ['status' => 1, 'error' => '정보가 제대로 넘어오지 못했습니다.'];
	echo json_encode($my_array);
	exit;
}

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config = $mysql->one_row($sql);

if($member_config['member_unavailable_id']) {
	$unavailable_id = explode(",", $member_config['member_unavailable_id']);
	foreach($unavailable_id as $k => $v) {
		if($id == $v) {
			$my_array[] = ['status' => 1, 'error' => '사용할 수 없는 아이디 입니다.'];
			echo  json_encode($my_array);
			exit;
		}
	}	
}

$sql = "SELECT count(*) FROM mallRN_vendor WHERE id='{$id}'";
if($mysql->get_one($sql)>0) {
	$my_array[] = ['status' => 1, 'error' => '이미 등록된 아이디 입니다.'];
	echo  json_encode($my_array);
}

echo json_encode(array('satatus' => 2));

?>