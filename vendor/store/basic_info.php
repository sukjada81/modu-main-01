<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","basic_info.html");
$tpl->scan_area("main");

$item_array = array('basic_name','basic_cs_time1','basic_cs_time2','basic_cs_time3','basic_cs_time4','comp_rtn_postcode','comp_rtn_address1','comp_rtn_address2');

$sql = "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$v_my_id}'";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>