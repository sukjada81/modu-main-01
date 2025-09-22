<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

$name		= checkPostVar('name');
$email		= checkPostVar('email');
$signdate	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER']))	logMsg('정상적으로 등록하세요.');
if(!$name)																logMsg('이름을 입력하세요.');
if(!$email)																logMsg('이메일주소를 입력하세요.');

$sql = "SELECT id FROM mallRN_member WHERE name = '{$name}' && email = '{$email}'";
if(!$search_id = $mysql->get_one($sql)) {
	$sql = "SELECT id FROM mallRN_member_sleep WHERE name = '{$name}' && email = '{$email}'";
	if(!$search_id = $mysql->get_one($sql)) logMsg('일치하는 회원 정보가 없습니다.');
}

?>

<script LANGUAGE="JavaScript">
<!--
	
	parent.idSearch('<?=$search_id?>');

//-->
</script>