<?php

include_once('../common/ad_init.php');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) alert("정상적으로 등록하세요!", "back");

$id	= add_escape_re_string(checkGetVar('id'));

if(!$id) alert('아이디를 입력하세요!','back');

################## 로그인하기 위해 입력한 아이디와 비밀번호가 일치하는 레코드를 검색 ##################
$sql = "SELECT level FROM mallRN_member WHERE id = '{$id}'";
if(!$level = $mysql->get_one($sql)) alert("회원이 존재 하지 않습니다.",'back');
	  
if($level > $my_level) alert("해당 아이디 로그인 권한이 없습니다.",'back');

session_start(); 

################# 로그아웃 ################
SetCookie("my_id",	"",	-999,	"/"); 
SetCookie("sid",	"",	-999,	"/"); 
SetCookie("tempid",	"",	-999,	"/");

makeLogin($id, 0, CONF_KEY); 

SetCookie("cartId", base64_encode($id), 0, "/");

echo "<script>location.href = '../../';</script>";
exit;
?>