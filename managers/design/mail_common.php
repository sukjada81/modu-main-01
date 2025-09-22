<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","mail_common.html");
$tpl->scan_area("main");

$sql			= "SELECT content FROM mallRN_auto_mail WHERE type = 'common'";
$content_code	= stripslashes($mysql->get_one($sql));
$content_code	= add_escape_re_string($content_code);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>