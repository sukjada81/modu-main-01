<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","mail_order.html");
$tpl->scan_area("main");

$sql								= "SELECT send, content FROM mallRN_auto_mail WHERE type = 'order'";
$data1								= $mysql->one_row($sql);
${"checked_send1_".$data1['send']}	= "checked='checked'";
$content_code1						= stripslashes($data1['content']);
$content_code1						= add_escape_re_string($content_code1);

$sql								= "SELECT send, content FROM mallRN_auto_mail WHERE type = 'delivery'";
$data2								= $mysql->one_row($sql);
$content_code2						= stripslashes($data2['content']);
$content_code2						= add_escape_re_string($content_code2);


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>