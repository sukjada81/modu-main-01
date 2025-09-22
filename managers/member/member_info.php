<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","member_info.html");
$tpl->scan_area("main");

$mode				= "modify";
$addstring			= "";
$search_variable	= array('field' ,'keyword', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 's_range1', 'e_range1', 'range1', 's_range2', 'e_range2', 'range2', 's_range3', 'e_range3', 'range3', 'level', 'mailling', 'sms', 'auth', 'gender', 'marry', 'address1', 'mobile', 'sns_type', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM mallRN_member WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) alert("등록된 회원이 없거나 삭제되었습니다.","back");

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	
	$name	= specialStrReplace($row['name']);
	$levels	= specialStrReplace($row['level']);
	
	$tpl->parse("loop_level");
}
######################## 회원등급 #############################

$item_array		= array('name', 'tel', 'cell', 'postcode', 'address1', 'address2', 'email', 'birth', 'birth_sl', 'gender', 'marry', 'hobby', 'job', 'comp', 'comp_owner', 'comp_num', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'add1', 'add2', 'add3', 'add4', 'add5', 'reference', 'id', 'passwd', 'level', 'mileage', 'mailling', 'mailling_date', 'sms', 'sms_date', 'auth', 'mobile', 'cnts', 'login_time', 'signdate');
$birth_sl_array	= array("S" => "양력",		"L" => "음력",		"N" => "미선택"); 
$gender_array	= array("M" => "남성",		"F" => "여성",		"N" => "미선택"); 
$marry_array	= array("M" => "기혼",		"S" => "미혼",		"N" => "미선택"); 
$mailling_array	= array("Y" => "수신허용",		"N" => "수신안함"); 
$sms_array		= array("Y" => "수신허용",		"N" => "수신안함"); 
$mobile_array	= array("Y" => "모바일",		"N" => "PC"); 

foreach($item_array as $k => $v) {
	${$v} = specialStrReplace2($data[$v]);
}

$id				= add_escape_re_string($data['id']);
$birth			= $birth		? substr($birth, 0, 4)."년 ".substr($birth, 4, 2)."월 ".substr($birth, 6, 2)."일 "	: "";
$comp_num		= $comp_num		? substr($comp_num, 0, 3)."-".substr($comp_num, 3, 2)."-".substr($comp_num, 5, 5)	: "";
$birth_sl		= $birth_sl_array[$birth_sl];
$gender			= $gender_array[$gender];
$marry			= $marry_array[$marry];
$mailling		= $mailling_array[$mailling];
$sms			= $sms_array[$sms];
$mobile			= $mobile_array[$mobile];
$mailling_date	= date("Y-m-d H:i:s", $mailling_date);
$sms_date		= date("Y-m-d H:i:s", $sms_date);
if($login_time) $login_time	= date("Y-m-d H:i:s", $login_time);
else			$login_time = "-";	
$signdate		= date("Y-m-d H:i:s", $signdate);

if(strlen($tel) == 12)		$tel	= substr($tel, 0, 4)."-".substr($tel, 4, 4)."-".substr($tel, 8, 4);
else if(strlen($tel) == 11)	$tel	= substr($tel, 0, 3)."-".substr($tel, 3, 4)."-".substr($tel, 7, 4);
else if(strlen($tel) == 10)	$tel	= substr($tel, 0, 2)."-".substr($tel, 2, 4)."-".substr($tel, 6, 4);

if(strlen($cell) == 12)			$cell	= substr($cell, 0, 4)."-".substr($cell, 4, 4)."-".substr($cell, 8, 4);
else if(strlen($cell) == 11)	$cell	= substr($cell, 0, 3)."-".substr($cell, 3, 4)."-".substr($cell, 7, 4);
else if(strlen($cell) == 10)	$cell	= substr($cell, 0, 2)."-".substr($cell, 2, 4)."-".substr($cell, 6, 4);

$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

$item_array		= array('member_form_tel','member_form_cell','member_form_address','member_form_birth','member_form_gender','member_form_marry','member_form_job','member_form_hobby','member_form_job_info','member_form_hobby_info');

foreach($item_array as $k => $v) {
	if($member_config[$v] == 0) continue;	
	$v2	= str_replace("member_form_", "", $v);	
	if($v2 == 'job_info' || $v2 == 'hobby_info' ) continue;
	$tpl->parse("is_{$v2}");
}

for($i = 1; $i < 6; $i ++) {
	if($member_config['member_form_add'.$i] == 0) continue;
	$add_title	= stripslashes($member_config['member_form_add'.$i.'_title']);
	$add_value	= ${"add".$i};

	$tpl->parse("loop_add");
}

$item_array		= array('member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item');
$ck_comp		= 0;

foreach($item_array as $k => $v) {
	if($member_config[$v] == 0) continue;	
	$v2			= str_replace("member_form_", "", $v);	
	$ck_comp	= 1;
	$tpl->parse("is_{$v2}");
}

if($ck_comp == 1) $tpl->parse("is_comp_info");

####################### 관리자 로그 ##########################	
adminLog($my_id, "회원정보 - {$id}", 2);
####################### 관리자 로그 ##########################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>