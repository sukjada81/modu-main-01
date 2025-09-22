<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$AGREEMENT = add_escape_re_string($member_config['agreement_info6']);
$AGREEMENT = str_replace("{COMPANY}",	stripslashes($shop_config['comp_name']),	$AGREEMENT);
$AGREEMENT = str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$AGREEMENT);
$AGREEMENT = str_replace("{SYEAR}",		date("Y", $shop_config['signdate']),		$AGREEMENT);
$AGREEMENT = str_replace("{SMONTH}",	date("m", $shop_config['signdate']),		$AGREEMENT);
$AGREEMENT = str_replace("{SDAY}",		date("d", $shop_config['signdate']),		$AGREEMENT);

$join_form = "업체명, 사업자등록번호, 대표자명, 사업장주소, 업태, 종목, 대표이메일, 대표전화번로, 대표팩스번로, 담당자명, 담당자 연락처, 담당자 부서, 담당자 직책";	
$PRIVACY = add_escape_re_string($member_config['agreement_info3']);
$PRIVACY = str_replace("{JOINFORM}", $join_form, $PRIVACY);

?>