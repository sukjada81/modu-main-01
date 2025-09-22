<?php
header("Content-Type: text/html; charset=utf-8");

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

define('PATH_LIB', '../../lib');
define('PATH_INCLUDE', '../../include');

include_once(PATH_LIB.'/lib.Function.php');   
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

/*
##############################################
    ::: 로그인 쿠키 생성 :::          
    사용방법 : makeLogin('회원아이디', '쿠기시간', '체크값')	
##############################################
*/

function makeVLogin($id, $tm, $rand) { 

	$text	= $id.$rand;
	$id		= base64_encode($id); 
	SetCookie("v_my_id",		$id,		$tm, "/"); 
	SetCookie("v_sid",			md5($text), $tm, "/"); 

} 

############ 파라미터(값) 검사 ####################
if(preg_match("/:/i",$_SERVER['HTTP_HOST'])) {
	$tmps = explode(":",$_SERVER['HTTP_HOST']);
	$_SERVER['HTTP_HOST'] = $tmps[0];
	unset($tmps);
}

$mysql->msgType(1);

$id				= add_escape_re_string(checkPostVar('id'));
$passwd			= checkPostVar('passwd');
$save_id		= checkPostVar('save_id');
$signdate		= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER']))	logMsg('정상적으로 등록하세요.');
if($_SERVER['REQUEST_METHOD']=='GET')									logMsg('정상적으로 등록하세요.');
if(!$id)																logMsg('아이디를 입력하세요.');
if(!$passwd)															logMsg('비밀번호를 입력하세요.');

if($save_id == '1') SetCookie("v_s_id", previlEncode($id), time() + (86400 * 30), "/");
else				SetCookie("v_s_id","",-999, "/");

$sql = "SELECT * FROM mallRN_vendor WHERE id = '{$id}'";
if(!$data = $mysql->one_row($sql)) logMsg('아이디 또는 비밀번호가 일치하지 않습니다.');

####################### 로그인 연속 실패시 로그인 일시중지 ##########################
$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

if($data['fail_cnts'] >= $member_config['member_limit_count']) {
	if(($data['fail_time'] + ($member_config['member_limit_minute'] * 60)) > $signdate) {
		$able_time = date("Y-m-d H:i:s", $data['fail_time'] + ($member_config['member_limit_minute'] * 60));
		logMsg("죄송합니다.<br />비밀번호 {$member_config['member_limit_count']}회 연속실패로 로그인이 일시 중지 되었습니다.<br />[{$able_time}] 이후 다시 로그인 하시기 바랍니다.");
	}
}
###################### 로그인 연속 실패시 로그인 일시중지 ##########################

if($data['auth'] != 'Y') logMsg("죄송합니다! 아직 미승인 상태입니다!<br />관리자 승인처리가 되어야만 이용 하실 수 있습니다.<br />자세한 사항은 관리자에게 문의 바랍니다");

$id			= stripslashes($data['id']);
$db_passwd	= stripslashes($data['passwd']);
  
if(strcmp($db_passwd,md5($passwd))) {
	####################### 로그인 실패 카운팅 ##########################	
	if(($data['fail_time'] + ($member_config['member_limit_minute'] * 60)) < $signdate) {
		$fail_cnts = 1;	
	}
	else $fail_cnts = $data['fail_cnts'] + 1;

	$sql = "UPDATE mallRN_vendor SET fail_cnts = '{$fail_cnts}', fail_time = '{$signdate}' WHERE id = '{$id}'";
	$mysql->query($sql);
	
	logMsg('아이디 또는 비밀번호가 일치하지 않습니다.');
	####################### 로그인 실패 카운팅 ##########################	
}

####################### 로그인 실패 카운팅 리셋 ##########################	
$sql = "UPDATE mallRN_vendor SET fail_cnts = '0', fail_time = '0' WHERE id = '{$id}'";
$mysql->query($sql);
####################### 로그인 실패 카운팅 리셋 ##########################
   
makeVLogin($id, 0, CONF_KEY); 

################# 로그인 시간 기록 ####################
$sql = "UPDATE mallRN_vendor SET login_time = '{$signdate}' WHERE id = '{$id}'";
$mysql->query($sql);
################# 로그인 시간 기록 ####################

####################### 판매사 로그 ##########################
$sql = "INSERT INTO mallRN_vendor_log SET id = '{$id}', content = '{$data['comp_name']} 로그인', type = 0, acc_ip = '{$_SERVER['REMOTE_ADDR']}', signdate = '{$signdate}'";
$mysql->query($sql);
####################### 판매사 로그 ##########################

parentMovePage("index.php?login=1");	  

?>