<?php 

include_once("../common/top.php");

//$sql = "ALTER TABLE mallRN_member ADD `passwd_reset` int unsigned NOT NULL default 0 COMMENT '초기비밀번호변경여부 0 : 변경함, 1 : 변경안함' AFTER `reference`";
//$mysql->query($sql);

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","member_adds.html");
$tpl->scan_area("main");

$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

$item_array = array('member_form_tel','member_form_cell','member_form_address','member_form_birth','member_form_gender','member_form_marry','member_form_job','member_form_hobby','member_form_mailling','member_form_sms','member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item');

foreach($item_array as $k => $v) {
	if($member_config[$v] == 2) $required = '<i class="fas fa-pen-square masterTooltip" title="필수 입력사항 입니다."></i>';
	else						$required = '';
	
	$v2	= str_replace("member_form_", "", $v);

	if($v2 == 'job') {
		$jobs = stripslashes($member_config['member_form_job_info']);
	}
	else if($v2 == 'hobby') {
		$hobbys	= stripslashes($member_config['member_form_hobby_info']);
	}

	$tpl->parse("is_{$v2}");
}

for($i = 1; $i < 6; $i ++) {
	if($member_config['member_form_add'.$i] == 2)	$required = '<i class="fas fa-pen-square masterTooltip" title="필수 입력사항 입니다."></i>';
	else											$required = '';
	$ADD_TITLE	= stripslashes($member_config['member_form_add'.$i.'_title']);

	$tpl->parse("loop_add");
}

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE level < 99 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	
	$name						= specialStrReplace($row['name']);
	$levels						= specialStrReplace($row['level']);
	
	$tpl->parse("loop_level");	
}
######################## 회원등급 #############################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>