<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","member_info.html");
$tpl->scan_area("main");

$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;

$item_array = array('member_auth','member_auth_email','member_auth_cell','member_unavailable_id','member_mileage_yn','member_mileage_validity_yn','member_mileage_validity','member_mileage_validity_type','member_mileage_join','member_mileage_order','member_limit_count','member_limit_minute','member_admin_auth','member_login_limit_minute','member_form_tel','member_form_cell','member_form_address','member_form_birth','member_form_gender','member_form_marry','member_form_job','member_form_hobby','member_form_job_info','member_form_hobby_info','member_form_mailling','member_form_sms','member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item','member_form_add1_title','member_form_add1','member_form_add2_title','member_form_add2','member_form_add3_title','member_form_add3','member_form_add4_title','member_form_add4','member_form_add5_title','member_form_add5');

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 2";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

if($member_auth_email == 0 && $member_auth_cell == 0) $member_auth_email = 1;

${"checked_auth_".$member_auth} = "checked='chedked'";	
${"checked_auth_email_".$member_auth_email} = "checked='chedked'";	
${"checked_auth_cell_".$member_auth_cell} = "checked='chedked'";	
${"checked_mileage_yn_".$member_mileage_yn} = "checked='chedked'";	
${"checked_mileage_validity_yn_".$member_mileage_validity_yn} = "checked='chedked'";	
${"checked_admin_auth_".$member_admin_auth} = "checked='chedked'";	
${"checked_form_tel_".$member_form_tel} = "checked='chedked'";	
${"checked_form_cell_".$member_form_cell} = "checked='chedked'";	
${"checked_form_address_".$member_form_address} = "checked='chedked'";	
${"checked_form_birth_".$member_form_birth} = "checked='chedked'";	
${"checked_form_gender_".$member_form_gender} = "checked='chedked'";	
${"checked_form_marry_".$member_form_marry} = "checked='chedked'";	
${"checked_form_job_".$member_form_job} = "checked='chedked'";	
${"checked_form_hobby_".$member_form_hobby} = "checked='chedked'";	
${"checked_form_mailling_".$member_form_mailling} = "checked='chedked'";	
${"checked_form_sms_".$member_form_sms} = "checked='chedked'";	
${"checked_form_comp_".$member_form_comp} = "checked='chedked'";	
${"checked_form_comp_num_".$member_form_comp_num} = "checked='chedked'";	
${"checked_form_comp_owner_".$member_form_comp_owner} = "checked='chedked'";	
${"checked_form_comp_address_".$member_form_comp_address} = "checked='chedked'";	
${"checked_form_comp_type_".$member_form_comp_type} = "checked='chedked'";	
${"checked_form_comp_item_".$member_form_comp_item} = "checked='chedked'";	

$social_array		= array("NAVER", "KAKAO", "GOOGLE", "PAYCO");
$social_msg1_array	= array("NAVER" => "Naver Developers에서 네이버 아이디로 로그인 애플리케이션 등록후 발급받은 Client ID를 입력 하시면 됩니다.", "KAKAO" => "Kakao Developers에서 카카오 로그인 애플리케이션 등록후 발급받은 REST API 키를 입력 하시면 됩니다.", "GOOGLE" => "GOOGLE CLOUD에서 프로젝트 등록후 사용자인증정보에서 발급받은 Client ID를 입력 하시면 됩니다.", "PAYCO" => "Payco Developers에서 애플리케이션 등록후 발급받은 Client ID를 입력 하시면 됩니다.");
$social_msg2_array	= array("NAVER" => "Naver Developers에서 네이버 아이디로 로그인 애플리케이션 등록후 발급받은 Client Secret을 입력 하시면 됩니다.", "KAKAO" => "Kakao Developers에서 카카오 로그인 애플리케이션 등록후 보안상태가 ON인경우만 발급받은 Client Secret을 입력 하시면 됩니다.", "GOOGLE" => "GOOGLE CLOUD에서 프로젝트 등록후 사용자인증정보에서 발급받은 Client Secret를 입력 하시면 됩니다.", "PAYCO" => "Payco Developers에서 애플리케이션 등록후 발급받은 Client Secret를 입력 하시면 됩니다.");
$social_url_array	= array("NAVER" => "https://developers.naver.com/main/", "KAKAO" => "https://developers.kakao.com/", "GOOGLE" => "https://console.developers.google.com/", "PAYCO" => "https://developers.payco.com/");

foreach($social_array as $k => $v) {
	$checked_social_used	= "";
	$social_api_id			= "";
	$social_api_key			= "";
	
	$sql	= "SELECT * FROM mallRN_configuration_social WHERE site = '{$v}'";
	if($data2 = $mysql->one_row($sql)) {
		if($data2['used'] == 1) $checked_social_used = "checked='chedked'";
		$social_api_id		= stripslashes($data2['api_id']);
		$social_api_key		= stripslashes($data2['api_key']);
	}	
	$social_callback		= $URL."plugin/social/".strtolower($v)."_login.php";
	$social_url				= $social_url_array[$v];
	$social_msg1			= $social_msg1_array[$v];
	$social_msg2			= $social_msg2_array[$v];

	$tpl->parse("loop_social");
}
unset($checked_social_used, $social_api_id, $social_api_key, $social_callback, $data2);

$sql = "SELECT sms_yn FROM mallRN_configuration WHERE uid = 1";
if($mysql->get_one($sql) == 'N')	$DISABLED = "disabled";
else								$DISABLED = "";

for($i=1;$i<6;$i++) {
	$checked_form_add_0 = $checked_form_add_1 = $checked_form_add_2 = "";
	${"checked_form_add_".${"member_form_add".$i}}	= "checked='chedked'";
	$member_form_add_title							= ${"member_form_add".$i."_title"};
	$tpl->parse("loop_add");
}


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>