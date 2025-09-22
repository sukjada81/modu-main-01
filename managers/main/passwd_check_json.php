<?php

ob_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

header("Content-Type: text/html; charset=utf-8");

if(substr($_SERVER['HTTP_HOST'], -1) == '.') {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

if(!function_exists('userAbortFunc')){
	//메모리제거
	function userAbortFunc() {

		global $mysql,$pg,$tpl;
		if(is_object($mysql)) $mysql->close();
		if(is_object($tpl)) $tpl->close();
		if(is_object($pg)) $pg->close();
		
	}
}

@ignore_user_abort(true); 
@register_shutdown_function('userAbortFunc');

define('__MANAGERS__',		'1');
define('DEFAULT_PATH',		'');
define('PATH_LIB',			'../../lib');
define('PATH_INCLUDE',		'../../include');
define('PATH_PHPMAILER',	'../../plugin/PHPMailer');
define('PATH_COOLSMS',		'../../plugin/coolSMS');
define('PAGING_TYPE',		'1'); //0 : paging, 1 : pageline

include_once(PATH_LIB.'/lib.Function.php');   
include_once(PATH_LIB.'/lib.Shop.php');   
include_once(PATH_INCLUDE.'/config.php');   
include_once(PATH_INCLUDE.'/dbconfig.php');   
include_once(PATH_LIB.'/class.Mysql.php');   

$mysql = new mysqlClass();  

//==============================================================================
// SQL Injection 방어
//------------------------------------------------------------------------------
// magic_quotes_gpc 에 의한 backslashes 제거
if (7.4 > (float)phpversion()) {
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
		$_POST    = array_map_deep('stripslashes',  $_POST);
		$_GET     = array_map_deep('stripslashes',  $_GET);
		$_COOKIE  = array_map_deep('stripslashes',  $_COOKIE);
		$_REQUEST = array_map_deep('stripslashes',  $_REQUEST);
	}
}

// sql_escape_string 적용
$_POST    = array_map_deep('add_escape_string',  $_POST);
$_GET     = array_map_deep('add_escape_string',  $_GET);
$_COOKIE  = array_map_deep('add_escape_string',  $_COOKIE);
$_REQUEST = array_map_deep('add_escape_string',  $_REQUEST);
//==============================================================================

$mysql->msgType(2);

$my_array	= array();
$id			= checkPostVar('id');
$passwd		= checkPostVar('passwd');
$signdate	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$passwd || !$id) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql		= "SELECT * FROM mallRN_member WHERE id = '{$id}'";
$data		= $mysql->one_row($sql);
if(!$data) json_error_msg('아이디 또는 비밀번호가 일치하지 않습니다.');

####################### 로그인 연속 실패시 로그인 일시중지 ##########################
$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

if($data['fail_cnts'] >= $member_config['member_limit_count']) {
	if(($data['fail_time'] + ($member_config['member_limit_minute'] * 60)) > $signdate) {
		$able_time = date("Y-m-d H:i:s", $data['fail_time'] + ($member_config['member_limit_minute'] * 60));
		json_error_msg("죄송합니다.<br />비밀번호 {$member_config['member_limit_count']}회 연속실패로 로그인이 일시 중지 되었습니다.<br />[{$able_time}] 이후 다시 로그인 하시기 바랍니다.");
	}
}
###################### 로그인 연속 실패시 로그인 일시중지 ##########################

if($data['auth'] == 'N') json_error_msg("죄송합니다! 아직 회원 미승인 상태입니다!<br />회원 승인처리가 되어야만 이용 하실 수 있습니다.<br />자세한 사항은 관리자에게 문의 바랍니다");

$id			= stripslashes($data['id']);
$db_passwd	= stripslashes($data['passwd']);
  
if(strcmp($db_passwd,md5($passwd))) {
	####################### 로그인 실패 카운팅 ##########################	
	if(($data['fail_time'] + ($member_config['member_limit_minute'] * 60)) < $signdate) {
		$fail_cnts = 1;	
	}
	else $fail_cnts = $data['fail_cnts'] + 1;

	$sql = "UPDATE mallRN_member SET fail_cnts = '{$fail_cnts}', fail_time = '{$signdate}' WHERE id = '{$id}'";
	$mysql->query($sql);
	
	json_error_msg('아이디 또는 비밀번호가 일치하지 않습니다.');
	####################### 로그인 실패 카운팅 ##########################	
}

$admin_auth	= $member_config['member_admin_auth'];

$sql		= "SELECT * FROM mallRN_configuration WHERE uid=1";
$shop_config = $mysql->one_row($sql);

$auth_code	= rand(100000, 999999);
$sql		= "UPDATE mallRN_member SET auth_code = '{$auth_code}', auth_code_time = '{$signdate}' WHERE id = '{$id}'";
$mysql->query($sql);

if($admin_auth == 'H') {
	if($shop_config['sms_yn'] == 'N') json_error_msg('문자를 발송할 수 없는 상태 입니다.');
	if(!$data['cell']) json_error_msg('휴대폰번호가 등록되지 않았습니다.');

	$replace_code_array	= array('AUTHCODE' => $auth_code);
	mallSmsAuto('authcode', $data['cell'], $replace_code_array);
	
	if($shop_config['sms_yn'] && $data['cell']) {
		$cell = substr($data['cell'], 0, 3)." - **** - ".substr($data['cell'], -4);
	}
	else $cell = "";

	$my_array[] = ["cell" => $cell, "auth_time1" => $signdate + 300, "auth_time2" => $signdate];

}
else {
	if(!$data['email']) json_error_msg('이메일주소가 등록되지 않았습니다.');

	############ 인증번호 메일 보내기 ############		
	$sql		= "SELECT content FROM mallRN_auto_mail WHERE type = 'passwd'";
	$content	= stripslashes($mysql->get_one($sql));
	$content	= str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$content);
	$content	= str_replace("{AUTHCODE}",	$auth_code,									$content);
	//mallMailSend($data['email'], stripslashes($shop_config['basic_name'])." 인증번호를 안내해드립니다.", $content);	
	############ 인증번호 메일 보내기 ############

	$tmp	= explode('@', $data['email']);
	$tmp2	= substr($tmp[0], 0, 3);
	for($i = 3; $i < strlen($tmp[0]); $i ++) {
		$tmp2 .= "*"; 
	}
	$email	= $tmp2."@".$tmp[1];

	$my_array[] = ["email" => $email, "auth_time1" => $signdate + 300, "auth_time2" => $signdate];
}

echo json_encode($my_array);

?>