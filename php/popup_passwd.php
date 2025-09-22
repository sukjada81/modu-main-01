<?php

$pop_title	= "비밀번호 확인";

include_once('../php/popup_init.php'); 

$uid		= checkGetVar('uid');

if(!$uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

$sql = "SELECT * FROM mallRN_inquiry WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) iframeViewError("등록된 문의가 없거나 삭제되었습니다.");

$SUBJECT = specialStrReplace3($data['subject']);

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_passwd.html");
$tpl->scan_area("main");

$TTL	= "비밀글";

if($my_level < 100) { 
	if(!$data['id']) $tpl->parse("is_guest");
	else if($data['id'] != $my_id) iframeViewError("회원정보가 일치하지 않습니다."); 
}		

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>