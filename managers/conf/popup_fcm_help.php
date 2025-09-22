<?php 

include_once("../common/popup_top.php");

$skin = ".";
// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_fcm_help.html");
$tpl->scan_area("main");


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>