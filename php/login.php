<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$s_id = isset($_COOKIE['s_id']) ? $_COOKIE['s_id'] : '';
if($s_id) {
	$SAVEID		= previlDecode($s_id);
	$checkedsid = "checked='checked'";
}

$social_array		= array("NAVER", "KAKAO", "GOOGLE", "PAYCO");
foreach($social_array as $k => $v) {
	$sql	= "SELECT * FROM mallRN_configuration_social WHERE site = '{$v}'";
	if($data = $mysql->one_row($sql)) {
		if($data['used'] == 1) $tpl->parse("is_".strtolower($v));
	}	
}

$channel2	= checkGetVar('channel2');
if($channel2) $channel_orig = $channel2;
$uid		= checkGetVar('uid');
$direct		= checkGetVar('direct');

if($channel2 == 'order') {
	$tpl->parse("is_guest_order");
}
else {
	$tpl->parse("is_guest_detail");
}

?>