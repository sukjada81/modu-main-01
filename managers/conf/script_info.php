<?php 

include_once("../common/top.php");

define('UPLOAD_FOLDER', '../../image/common');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","script_info.html");
$tpl->scan_area("main");

$item_array = array('script_naver_tag', 'script_google_analytics', 'script_top_code', 'script_bottom_code');

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}


$script_top_code	= add_escape_re_string($script_top_code);
$script_bottom_code	= add_escape_re_string($script_bottom_code);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>