<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

if($member_config['member_auth'] == 'P') {
	$tpl->parse("is_auth_N");	
}
else {
	$tpl->parse("is_auth_Y");
	$tpl->parse("is_auth_Y2");

	if($member_config['member_mileage_yn'] == 'Y' && $member_config['member_mileage_join'] > 0) {   // 회원가입시 지급 마일리지
		$JPOINT = number_format($member_config['member_mileage_join'], CONF_FLOAT_CNT);
		$tpl->parse("is_join_point");
	}
}

?>