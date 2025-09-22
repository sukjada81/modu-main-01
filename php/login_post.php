<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

$id				= add_escape_re_string(checkPostVar('id'));
$passwd			= checkPostVar('passwd');
$save_id		= checkPostVar('save_id');
$channel_orig	= checkPostVar('channel_orig');
$uid			= checkPostVar('uid');
$signdate		= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER']))	logMsg('정상적으로 등록하세요.');
if(!$id)																logMsg('아이디를 입력하세요.');
if(!$passwd)															logMsg('비밀번호를 입력하세요.');

if($save_id == '1') SetCookie("s_id", previlEncode($id), time() + (86400 * 30), "/");
else				SetCookie("s_id","",-999, "/");

$sleep	= "";
$sql	= "SELECT * FROM mallRN_member WHERE id = '{$id}'";
if(!$data = $mysql->one_row($sql)) {
	$sql = "SELECT * FROM mallRN_member_sleep WHERE id = '{$id}'";
	if(!$data = $mysql->one_row($sql)) logMsg('아이디 또는 비밀번호가 일치하지 않습니다.');
	$sleep	= "_sleep";
}

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

if($data['auth'] == 'N') logMsg("죄송합니다! 아직 회원 미승인 상태입니다!<br />회원 승인처리가 되어야만 이용 하실 수 있습니다.<br />자세한 사항은 관리자에게 문의 바랍니다");

$id			= stripslashes($data['id']);
$db_passwd	= stripslashes($data['passwd']);
  
if(strcmp($db_passwd,md5($passwd))) {
	####################### 로그인 실패 카운팅 ##########################	
	if(($data['fail_time'] + ($member_config['member_limit_minute'] * 60)) < $signdate) {
		$fail_cnts = 1;	
	}
	else $fail_cnts = $data['fail_cnts'] + 1;

	$sql = "UPDATE mallRN_member{$sleep} SET fail_cnts = '{$fail_cnts}', fail_time = '{$signdate}' WHERE id = '{$id}'";
	$mysql->query($sql);
	
	logMsg('아이디 또는 비밀번호가 일치하지 않습니다.');
	####################### 로그인 실패 카운팅 ##########################	
}

####################### 로그인 실패 카운팅 리셋 ##########################	
$sql = "UPDATE mallRN_member{$sleep} SET fail_cnts = '0', fail_time = '0' WHERE id = '{$id}'";
$mysql->query($sql);
####################### 로그인 실패 카운팅 리셋 ##########################

makeLogin($id, 0, CONF_KEY); 

################# 로그인 시간 기록 ####################
$sql = "UPDATE mallRN_member SET cnts = cnts + 1, login_time = '{$signdate}' WHERE id = '{$id}'";
$mysql->query($sql);
################# 로그인 시간 기록 ####################

################# 장바구니 ####################
$sql = "UPDATE mallRN_cart SET cart_id = '".base64_encode($id)."' WHERE cart_id = '{$cart_id}'";
$mysql->query($sql);
SetCookie("cartId", base64_encode($id), 0, "/");
################# 장바구니 ####################

################# 최근본 상품 ####################
$sql = "UPDATE mallRN_goods_recent_view SET check_id = '".base64_encode($id)."' WHERE check_id = '{$cart_id}'";
$mysql->query($sql);
################# 최근본 상품 ####################

if($sleep == '_sleep') {
	$sql	= "INSERT INTO mallRN_member (SELECT * FROM mallRN_member_sleep WHERE uid = '{$data['uid']}' && id = '{$id}')";
	$mysql->query($sql);

	$sql	= "UPDATE mallRN_member SET sleep_time = '{$signdate}' WHERE uid = '{$data['uid']}' && id = '{$id}'";
	$mysql->query($sql);

	$sql	= "DELETE FROM mallRN_member_sleep WHERE uid = '{$data['uid']}' && id = '{$id}'";
	$mysql->query($sql);	
	
	parentMovePage("../{$Main}?channel=member_sleep");
}	

if($channel_orig)	{
	if($uid)	$add_uid = "&uid={$uid}";
	else		$add_uid = "";
	parentMovePage("../{$Main}?channel={$channel_orig}{$add_uid}");
}
else parentMovePage("../{$Main}");

?>