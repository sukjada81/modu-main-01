<?php 

include_once("../common/top.php");

define('UPLOAD_FOLDER', '../../image/common');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","base_info.html");
$tpl->scan_area("main");

$item_array = array('basic_url','basic_name','basic_admin','basic_email','basic_cs_time1','basic_cs_time2','basic_cs_time3','basic_cs_time4','basic_title','basic_description','basic_image','basic_keyword','basic_real_keyword','comp_name','comp_owner','comp_license_no1','comp_license_no2','comp_type','comp_item','comp_email','comp_tel','comp_fax','comp_postcode','comp_address1','comp_address2','comp_rtn_postcode','comp_rtn_address1','comp_rtn_address2');

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

if($basic_image) {	
	$i = 1;
	$image	= UPLOAD_FOLDER.'/'.$basic_image."?t={$t}";
	$tpl->parse("loop_image");
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>