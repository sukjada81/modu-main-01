<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

if($channel == 'member_modify') {
	
	$sql = "SELECT * FROM mallRN_member WHERE id='{$my_id}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 회원이 아니거나 삭제되었습니다.","back");

	$item_array		= array('name', 'tel', 'cell', 'postcode', 'address1', 'address2', 'email', 'birth', 'birth_sl', 'gender', 'marry', 'hobby', 'job', 'comp', 'comp_owner', 'comp_num', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'add1', 'add2', 'add3', 'add4', 'add5', 'mailling', 'mailling_date', 'sms', 'sms_date');

	foreach($item_array as $k => $v) {
		${$v} = specialStrReplace3($data[$v]);
	}

	${"checked_birth_sl_".$birth_sl}		= "checked = 'checked'";
	${"checked_gender_".$gender}			= "checked = 'checked'";
	${"checked_marry_".$marry}				= "checked = 'checked'";
	$sms_date								= date("Y-m-d H:i:s", $sms_date);
	$mailling_date							= date("Y-m-d H:i:s", $mailling_date);
	if($sms == 'Y')	$checked_sms			= "checked = 'checked'";
	else			$checked_sms			= "";
	if($mailling == 'Y')	$checked_mailling	= "checked = 'checked'";
	else					$checked_mailling	= "";

	if(!$my_sns_type) $tpl->parse("is_sns_type");
}
else {

	$social_array		= array("NAVER", "KAKAO", "GOOGLE", "PAYCO");
	foreach($social_array as $k => $v) {
		$sql	= "SELECT * FROM mallRN_configuration_social WHERE site = '{$v}'";
		if($data = $mysql->one_row($sql)) {
			if($data['used'] == 1) $tpl->parse("is_".strtolower($v));
		}	
	}

	$job = $hobby = $add1 = $add2 = $add3 = $add4 = $add5 = "";
}

$item_array = array('member_form_tel','member_form_cell','member_form_address','member_form_birth','member_form_gender','member_form_marry','member_form_job','member_form_hobby','member_form_job_info','member_form_hobby_info','member_form_mailling','member_form_sms','member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item');

$ck_comp = 0;
foreach($item_array as $k => $v) {
	if($member_config[$v] == 0) continue;
	if($member_config[$v] == 2) $required = 'required="required"';
	else					  $required = '';

	$v2	= str_replace("member_form_", "", $v);
	if(preg_match('/comp/i', $v2)) $ck_comp = 1;

	if($v2 == 'job') {
		$job_arr = explode(",", $member_config['member_form_job_info']);
		foreach($job_arr as $k3 => $v3) {
			if($job == $v3) $checked = "checked = 'checked'";
			else			$checked = "";
			$tpl->parse("loop_job");
		}
	}
	else if($v2 == 'hobby') {
		$hobby		= explode("|", $hobby);
		$hobby_arr	= explode(",", $member_config['member_form_hobby_info']);
		foreach($hobby_arr as $k3 => $v3) {
			if(in_array($v3, $hobby))	$checked = "checked = 'checked'";
			else						$checked = "";
			$tpl->parse("loop_hobby");
		}
	}
	else if($v2 == 'birth') {
		$readonly_birth = $disabled_birth = "";
		if($member_config[$v] == 2) {
			$readonly_birth = "readonly";
			$disabled_birth = "disabled";
		}		
	}
	else if($v2 == 'job_info' || $v2 == 'hobby_info' ) continue;

	$tpl->parse("is_{$v2}");
}

if($ck_comp == 1) {
	$tpl->parse("is_comp_title");
	$tpl->parse("is_comp_search");
}

for($i = 1; $i < 6; $i ++) {
	if($member_config['member_form_add'.$i] == 0) continue;
	if($member_config['member_form_add'.$i] == 2) $add_required = 'required="required"';
	else										$add_required = '';
	$ADD_TITLE	= stripslashes($member_config['member_form_add'.$i.'_title']);
	$ADD_VALUE	= ${"add".$i};

	$tpl->parse("loop_add");
}

unset($item_array);

if($channel == 'regist') {
	$AGREEMENT = add_escape_re_string($member_config['agreement_info1']);
	$AGREEMENT = str_replace("{COMPANY}",	stripslashes($shop_config['comp_name']),	$AGREEMENT);
	$AGREEMENT = str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$AGREEMENT);
	$AGREEMENT = str_replace("{SYEAR}",		date("Y", $shop_config['signdate']),		$AGREEMENT);
	$AGREEMENT = str_replace("{SMONTH}",	date("m", $shop_config['signdate']),		$AGREEMENT);
	$AGREEMENT = str_replace("{SDAY}",		date("d", $shop_config['signdate']),		$AGREEMENT);

	$item_array		= array('member_form_tel','member_form_cell','member_form_address','member_form_birth','member_form_gender','member_form_marry','member_form_job','member_form_hobby','member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item','member_form_add1','member_form_add2','member_form_add3','member_form_add4','member_form_add5');
	$item_array2	= array('전화번호','휴대폰번호','주소','생년월일','성별','결혼여부','직업','관심분야','회사명','사업자등록번호','대표자명','사업자주소','업태','종목', stripslashes($member_config['member_form_add1_title']), stripslashes($member_config['member_form_add2_title']), stripslashes($member_config['member_form_add3_title']), stripslashes($member_config['member_form_add4_title']), stripslashes($member_config['member_form_add5_title'])); 

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
}

?>