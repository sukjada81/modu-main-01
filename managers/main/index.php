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

$sql	= "SELECT * FROM mallRN_patch_check WHERE confirm = 0 ORDER BY uid ASC LIMIT 1";
$data	= $mysql->one_row($sql);
if($data) {
	$patch_uid		= $data['b_uid'];
	$patch_date		= date("Y-m-d", $data['signdate']);
	$patch_subject	= stripslashes($data['subject']);

	$tpl->parse("is_patch");
}


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>