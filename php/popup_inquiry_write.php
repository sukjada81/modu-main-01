<?php

$pop_title	= "상품문의등록";

include_once('../php/popup_init.php'); 

$uid	= checkGetVar('uid');

if(!$uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_inquiry_write.html");
$tpl->scan_area("main");

$sql = "SELECT name FROM mallRN_goods WHERE uid = '{$uid}'";
if(!$g_name = stripslashes($mysql->get_one($sql))) iframeViewError("해당 상품이 삭제되었거나 존재하지 않습니다.");

if($shop_config['inquiry_secret_type'] > 0) {
	$secret_disable = "";
	if($shop_config['inquiry_secret_type'] == 1) {
		$checked_secret_1	= "checked='checked'";
		$secret_disable		= "disabled";
	}
	$tpl->parse("is_secret");	
}

######################## 분류 설정 #############################
$cate_info = explode("|*|", $shop_config['inquiry_cate_info']);
if($cate_info[0] > 100) {
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);

		$cate_num = $cate_info2[0];
		$cate_name = $cate_info2[1];
		
		$tpl->parse("loop_cate");
	}
	$tpl->parse("is_cate");	
}
unset($cate_info, $cate_info2, $cate_name, $cate_num);
######################## 분류 설정 #############################

######################## 첨부파일 및 관련링크 #############################
for($i = 1; $i < 6; $i ++) {
	if($i > 1)	$display = "displayNone";
	else		$display = "";
	
	$i2 = $i - 1;

	$tpl->parse("loop_attach");
}
######################## 첨부파일 및 관련링크 #############################

if(!$my_id) {
	$sql			= "SELECT agreement_info5 FROM mallRN_configuration WHERE uid = 2";
	$agreement_info	= stripslashes($mysql->get_one($sql));
	$PP =  "";

	$tpl->parse("is_guest");
	$tpl->parse("is_agreement");
}
else {
	$sql		= "SELECT cell FROM mallRN_member WHERE id = '{$my_id}'";
	$cell		= stripslashes($mysql->get_one($sql));
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>