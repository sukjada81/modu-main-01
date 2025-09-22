<?php 

include_once("../common/popup_top.php");

$code = checkGetVar('code');
$num = checkGetVar('num');

$sql			= "SELECT order_tracker_key FROM mallRN_configuration WHERE uid = 1";
$tracker_key	= $mysql->get_one($sql);

if(!$code || !$num || !$tracker_key) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_tracker.html");
$tpl->scan_area("main");


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>