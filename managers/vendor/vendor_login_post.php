<?php

include_once('../common/ad_init.php');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) alert("정상적으로 등록하세요!", "back");

$id	= checkGetVar('id');

if(!$id) alert('아이디를 입력하세요!','back');

################## 로그인하기 위해 입력한 아이디와 비밀번호가 일치하는 레코드를 검색 ##################
$sql = "SELECT count(*) FROM mallRN_vendor WHERE id = '{$id}'";
if($mysql->get_one($sql) == 0) alert("판매사가 존재 하지 않습니다.",'back');
	  
session_start(); 

################# 로그아웃 ################
SetCookie("v_my_id",	"", -999,	"/"); 
SetCookie("v_sid",		"",	-999,	"/"); 

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


makeVLogin($id, 0, CONF_KEY);

echo "<script>location.href = '../../vendor';</script>";
exit;
?>