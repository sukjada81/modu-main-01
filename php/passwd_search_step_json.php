<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();
$name		= checkPostVar('name');
$id			= add_escape_re_string(checkPostVar('id'));
$auth		= checkPostVar('auth');
$auth_code	= checkPostVar('auth_code');
$step		= checkPostVar('step');
$signdate	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if($step == 1) {
	if(!$name || !$id) json_error_msg('필수 정보가 넘어오지 못했습니다.');
}
else if($step == 2) {
	if(!$auth || !$id || !$name) json_error_msg('필수 정보가 넘어오지 못했습니다.');
}
else if($step == 3) {
	if(!$auth_code || !$id || !$name) json_error_msg('필수 정보가 넘어오지 못했습니다.');
}

$sleep	= "";
$sql	= "SELECT email, cell, auth_code, auth_code_time FROM mallRN_member WHERE id = '{$id}' && name = '{$name}'";
$data	= $mysql->one_row($sql);
if(!$data) {
	$sql	= "SELECT email, cell, auth_code, auth_code_time FROM mallRN_member_sleep WHERE id = '{$id}' && name = '{$name}'";
	$data	= $mysql->one_row($sql);
	if(!$data) json_error_msg('일치하는 회원정보가 없습니다.');
	$sleep  = "_sleep";
}

if($step == 1) {
	if(!$data) json_error_msg('일치하는 회원정보가 없습니다.');
	if(!$data['email']) json_error_msg('이메일주소가 등록되지 않았습니다.');

	$tmp	= explode('@', $data['email']);
	$tmp2	= substr($tmp[0], 0, 3);
	for($i = 3; $i < strlen($tmp[0]); $i ++) {
		$tmp2 .= "*"; 
	}
	$email	= $tmp2."@".$tmp[1];

	if($shop_config['sms_yn'] && $data['cell']) {
		$cell = substr($data['cell'], 0, 3)." - **** - ".substr($data['cell'], -4);
	}
	else $cell = "";

	$my_array[] = ["email" => $email, "cell" => $cell];
}
else if($step == 2) {
	$auth_code	= rand(100000, 999999);
	$sql		= "UPDATE mallRN_member{$sleep} SET auth_code = '{$auth_code}', auth_code_time = '{$signdate}' WHERE id = '{$id}'";
	$mysql->query($sql);

	if($auth == 1) {
		if(!$data['email']) json_error_msg('이메일주소가 등록되지 않았습니다.');
		
		############ 인증번호 메일 보내기 ############		
		$sql		= "SELECT content FROM mallRN_auto_mail WHERE type = 'passwd'";
		$content	= stripslashes($mysql->get_one($sql));
		$content	= str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$content);
		$content	= str_replace("{AUTHCODE}",	$auth_code,									$content);
		mallMailSend($data['email'], stripslashes($shop_config['basic_name'])." 인증번호를 안내해드립니다.", $content);	
		############ 인증번호 메일 보내기 ############
	}
	else if($auth == 2) {
		if($shop_config['sms_yn'] == 'N') json_error_msg('문자를 발송할 수 없는 상태 입니다.<br />고객센터에 문의 바랍니다.');
		if(!$data['cell']) json_error_msg('휴대폰번호가 등록되지 않았습니다.');

		$replace_code_array	= array('AUTHCODE' => $auth_code);
		mallSmsAuto('authcode', $data['cell'], $replace_code_array);
	}

	$my_array[] = ["auth_time1" => $signdate + 300, "auth_time2" => $signdate];
}
else if($step == 3) {
	if($data['auth_code'] != $auth_code) json_error_msg('인증번호가 일치 하지 않습니다.');
	if($data['auth_code_time'] + 300 < time()) json_error_msg('인증번호가 유효시간이 지났습니다.<br />다시 인증요청후 진행 하시기 바랍니다.');

	$my_array[] = ["success" => 1];
}

echo json_encode($my_array);

?>