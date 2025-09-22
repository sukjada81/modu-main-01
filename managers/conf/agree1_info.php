<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","agree1_info.html");
$tpl->scan_area("main");

$sql		= "SELECT agreement_info1 FROM mallRN_configuration WHERE uid = 2";
$explains	= stripslashes($mysql->get_one($sql));

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>