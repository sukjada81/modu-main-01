<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

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

$delivery_array = array();
if($shop_config['delivery_info']) {
	$delivery_info = explode("|*|", $shop_config['delivery_info']);
	if($delivery_info[1]) {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {
			$delivery_info2 = explode("|", $delivery_info[$i]);
			if($delivery_info2[3] != '1') continue;			
			$delivery_array[] = $delivery_info2[1];
		}
	}
}
if(count($delivery_array) > 0)	$delivery_name = join(", ", $delivery_array);
else							$delivery_name = "";	
unset($delivery_array);

$PRIVACY = stripslashes($member_config['agreement_info2']);
$PRIVACY = str_replace("{COMPANY}",			stripslashes($shop_config['comp_name']),	$PRIVACY);
$PRIVACY = str_replace("{JOINFORM}",		$join_form,									$PRIVACY);
$PRIVACY = str_replace("{DELIVERYNAME}",	$delivery_name,								$PRIVACY);
$PRIVACY = str_replace("{PGNAME}",			'NHN한국사이버결제 주식회사',						$PRIVACY);
$PRIVACY = str_replace("{MANAGERNAME}",		stripslashes($shop_config['basic_admin']),	$PRIVACY);
$PRIVACY = str_replace("{MANAGERTEL}",		stripslashes($shop_config['comp_tel']),		$PRIVACY);
$PRIVACY = str_replace("{MANAGEREMAIL}",	stripslashes($shop_config['basic_email']),	$PRIVACY);

?>