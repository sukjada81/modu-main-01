<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","agree3_info.html");
$tpl->scan_area("main");

$sql		= "SELECT agreement_info3, agreement_info4, agreement_info5 FROM mallRN_configuration WHERE uid = 2";
$data		= $mysql->one_row($sql);
$explains1	= stripslashes($data['agreement_info3']);
$explains2	= stripslashes($data['agreement_info4']);
$explains3	= stripslashes($data['agreement_info5']);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>