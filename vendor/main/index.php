<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","index.html");
$tpl->scan_area("main");

if($widget_info) {
	$widget_info	= explode(",", $widget_info);

	foreach($widget_info as $k => $v) {
		$v	= trim($v);
		$tpl->parse("loop_widget1");
		$tpl->parse("loop_widget2");
	}
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>