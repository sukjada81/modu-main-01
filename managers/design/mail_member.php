<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","mail_member.html");
$tpl->scan_area("main");

$sql								= "SELECT send, content FROM mallRN_auto_mail WHERE type = 'join'";
$data1								= $mysql->one_row($sql);
${"checked_send1_".$data1['send']}	= "checked='checked'";
$content_code1						= stripslashes($data1['content']);
$content_code1						= add_escape_re_string($content_code1);

$sql								= "SELECT send, content FROM mallRN_auto_mail WHERE type = 'passwd'";
$data2								= $mysql->one_row($sql);
$content_code2						= stripslashes($data2['content']);
$content_code2						= add_escape_re_string($content_code2);

$sql								= "SELECT send, content FROM mallRN_auto_mail WHERE type = 'vjoin'";
$data3								= $mysql->one_row($sql);
${"checked_send3_".$data3['send']}	= "checked='checked'";
$content_code3						= stripslashes($data3['content']);
$content_code3						= add_escape_re_string($content_code3);

$sql								= "SELECT send, content FROM mallRN_auto_mail WHERE type = 'sleep'";
$data4								= $mysql->one_row($sql);
$content_code4						= stripslashes($data4['content']);
$content_code4						= add_escape_re_string($content_code4);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>