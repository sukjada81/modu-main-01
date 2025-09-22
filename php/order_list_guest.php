<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

if(!isset($_COOKIE['guestOrder1'])) {
	$name			= checkPostVar('name');
	$passwd			= checkPostVar('passwd');
	$cell			= checkPostVar('cell');

	if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER']))	alert('정상적으로 등록하세요.');
	if(!$name)																alert('이름을 입력하세요.');
	if(!$cell)																alert('핸드폰번호를 입력하세요.');
	if(!$passwd)															alert('비밀번호를 입력하세요.');
	$passwd = md5($passwd);

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE name = '{$name}' && cell = '{$cell}' && passwd = '{$passwd}' && reals = 1 ORDER BY uid DESC";
	if($mysql->get_one($sql) == 0) {
		alert("일치하는 정보가 존재 하지 않습니다.", "back");
	}

	$guestOrder1	= base64_encode("{$cell}|{$name}");
	$guestOrder2	= previlEncode("{$passwd}");

	SetCookie("guestOrder1", $guestOrder1, 0, "/");
	SetCookie("guestOrder2", $guestOrder2, 0, "/");
}
else {
	$guestOrder1	= base64_decode($_COOKIE['guestOrder1']);
	$tmps			= explode("|", $guestOrder1);
	$cell			= $tmps[0];
	$name			= $tmps[1];
	$passwd			= previlDecode($_COOKIE['guestOrder2']);

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE name = '{$name}' && cell = '{$cell}' && passwd = '{$passwd}' && reals = 1 ORDER BY uid DESC";
	if($mysql->get_one($sql) == 0) {
		alert("일치하는 정보가 존재 하지 않습니다.", "back");
	}	
}

$guest_where	= "name = '{$name}' && cell = '{$cell}' && passwd = '{$passwd}'";
$GUEST			= "비회원";

include_once('php/order_list.php');

?>