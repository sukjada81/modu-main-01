<?php

$pop_title	= "SNS 간편가입";

include_once('../php/popup_init.php'); 

$sns_id		= checkPostVar('sns_id');
$name		= checkPostVar('name');
$email		= checkPostVar('email');
$cell		= str_replace("-", "", checkPostVar('cell'));
$gender		= checkPostVar('gender');
$birth		= checkPostVar('birth');
$sns_type	= checkPostVar('sns_type');

if(!$sns_id || !$name || !$sns_type) { 
	alert("필수 정보가 넘어오지 못했습니다.", "close");
}

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_sns_regist.html");
$tpl->scan_area("main");

$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

$item_array = array('member_form_cell','member_form_birth','member_form_gender','member_form_mailling','member_form_sms');

foreach($item_array as $k => $v) {
	if($member_config[$v] == 0) continue;
	if($member_config[$v] == 2) $required = 'required="required"';
	else					  $required = '';

	$v2	= str_replace("member_form_", "", $v);

	$tpl->parse("is_{$v2}");
}

$AGREEMENT = add_escape_re_string($member_config['agreement_info1']);
$AGREEMENT = str_replace("{COMPANY}",	stripslashes($shop_config['comp_name']),	$AGREEMENT);
$AGREEMENT = str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$AGREEMENT);
$AGREEMENT = str_replace("{SYEAR}",		date("Y", $shop_config['signdate']),		$AGREEMENT);
$AGREEMENT = str_replace("{SMONTH}",	date("m", $shop_config['signdate']),		$AGREEMENT);
$AGREEMENT = str_replace("{SDAY}",		date("d", $shop_config['signdate']),		$AGREEMENT);

$item_array		= array('member_form_cell','member_form_birth','member_form_gender');
$item_array2	= array('휴대폰번호','생년월일','성별'); 

$join_array = array();
foreach($item_array as $k => $v) {
	if($member_config[$v] == 0) continue;
	$join_array[] = $item_array2[$k];
}
if(count($join_array) > 0)	$join_form = join(", ", $join_array);
else						$join_form = "";	
unset($join_array);

$PRIVACY = add_escape_re_string($member_config['agreement_info3']);
$PRIVACY = str_replace("{JOINFORM}", $join_form, $PRIVACY);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>