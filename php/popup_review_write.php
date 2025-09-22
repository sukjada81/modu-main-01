<?php

$pop_title	= "구매후기등록";

include_once('../php/popup_init.php'); 

$og_uid	= checkGetVar('og_uid');
$g_uid	= checkGetVar('g_uid');

if(!$og_uid || !$g_uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_review_write.html");
$tpl->scan_area("main");

$sql = "SELECT name FROM mallRN_goods WHERE uid = '{$g_uid}'";
if(!$g_name = stripslashes($mysql->get_one($sql))) iframeViewError("해당 상품이 삭제되었거나 존재하지 않습니다.");

$sql = "SELECT count(*) FROM mallRN_order_goods WHERE uid = '{$og_uid}' && reals = 1";
if($mysql->get_one($sql) == 0) iframeViewError("상품구매회원만 등록이 가능 합니다.");

$sql = "SELECT option_name FROM mallRN_order_goods WHERE uid = '{$og_uid}' && reals = 1";
$option_name = stripslashes($mysql->get_one($sql));

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

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>