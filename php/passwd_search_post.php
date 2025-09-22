<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

$id			= add_escape_re_string(checkPostVar('id'));
$auth_code	= checkPostVar('auth_code');
$passwd		= checkPostVar('passwd');

$signdate	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER']))	logMsg('정상적으로 등록하세요.');
if(!$id)																logMsg('필수정보가 넘어오지 못했습니다.');
if(!$auth_code)															logMsg('필수정보가 넘어오지 못했습니다.');
if(!$passwd)															logMsg('신규비밀번호를 입력하세요.');

$sleep	= "";
$sql	= "SELECT auth_code, auth_code_time FROM mallRN_member WHERE id = '{$id}'";
if(!$data = $mysql->one_row($sql)) {
	$sql	= "SELECT auth_code, auth_code_time FROM mallRN_member_sleep WHERE id = '{$id}'";
	if(!$data = $mysql->one_row($sql)) logMsg('일치하는 회원 정보가 없습니다.');
	$sleep	= "_sleep";
}

if($data['auth_code'] != $auth_code) logMsg('인증번호가 일치 하지 않습니다.');
if($data['auth_code_time'] + 300 < $signdate) logMsg('인증번호 유효시간이 경과되었습니다.');

$passwd	= md5($passwd);

$sql = "UPDATE mallRN_member{$sleep} SET passwd = '{$passwd}', auth_code = '', auth_code_time = '' WHERE id = '{$id}'";
$mysql->query($sql);

alertMsg("비밀번호가 변경 되었습니다. 로그인 하시기 바랍니다.", "../{$Main}?channel=login");

?>