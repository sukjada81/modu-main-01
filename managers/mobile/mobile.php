<?php 

include_once("../common/top.php");

define('UPLOAD_FOLDER', '../../image/mobile');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","mobile.html");
$tpl->scan_area("main");

$item_array			= array('mobile_yn', 'mobile_icon');

$sql				= "SELECT mobile_yn, mobile_icon FROM mallRN_configuration WHERE uid = 1";
$data				= $mysql->one_row($sql);

for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
	${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
}

${"checked_mobile_yn_".$mobile_yn}	= "checked='checked'";

if($mobile_icon) {	
	$i = 1;
	$image	= UPLOAD_FOLDER.'/'.$mobile_icon."?t={$t}";
	$tpl->parse("loop_image");
}


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>