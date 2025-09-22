<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","inquiry_conf.html");
$tpl->scan_area("main");

$cate_max_num		= 100;

$item_array = array('inquiry_cate_info', 'inquiry_secret_type', 'inquiry_privacy_type', 'inquiry_access_write');

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
$data = $mysql->one_row($sql);

for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
	${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
}
	
${"checked_inquiry_privacy_type_".$inquiry_privacy_type}		= "checked='checked'";
${"checked_inquiry_secret_type_".$inquiry_secret_type}			= "checked='checked'";
${"checked_inquiry_access_write_".$inquiry_access_write}		= "checked='checked'";

if($inquiry_cate_info) {
	$cate_info = explode("|*|",$inquiry_cate_info);
	$cate_max_num = $cate_info[0];
	if($cate_max_num > 100) {
		for($i=1,$cnt=count($cate_info); $i<$cnt; $i++) {
			$cate_info2 = explode("|",$cate_info[$i]);

			$cate_num = $cate_info2[0];
			$cate_name = $cate_info2[1];
			
			$tpl->parse("loop_cate");
		}
	}
	$cate_num = $cate_name = "";
}
$cate_max_num		= 100;
$tpl->parse("loop_cate");

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>