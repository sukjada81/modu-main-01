<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$AGREEMENT = stripslashes($member_config['agreement_info1']);
$AGREEMENT = str_replace("{COMPANY}",	stripslashes($shop_config['comp_name']),	$AGREEMENT);
$AGREEMENT = str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$AGREEMENT);
$AGREEMENT = str_replace("{SYEAR}",		date("Y", $shop_config['signdate']),		$AGREEMENT);
$AGREEMENT = str_replace("{SMONTH}",	date("m", $shop_config['signdate']),		$AGREEMENT);
$AGREEMENT = str_replace("{SDAY}",		date("d", $shop_config['signdate']),		$AGREEMENT);

?>