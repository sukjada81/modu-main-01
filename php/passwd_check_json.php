<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('../php/init.php');

$mysql->msgType(2);

$my_array	= array();
$uid		= checkPostVar('uid');	
$passwd		= checkPostVar('passwd');	

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$uid || !$passwd) json_error_msg('필수정보가 제대로 넘어오지 못했습니다.');

$sql = "SELECT * FROM mallRN_inquiry WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) json_error_msg('등록된 글이 없거나 삭제되었습니다.');

if($my_level < 100) {
	if(!$data['id']) {
		$passwd = md5($passwd);
		if($passwd != $data['passwd']) json_error_msg('비밀번호가 일치하지 않습니다.');
	}
	else if($data['id'] != $my_id) json_error_msg('회원정보가 일치하지 않습니다.');	
}

$my_array[] = ["suess" => "1"];
	
echo json_encode($my_array);

?>