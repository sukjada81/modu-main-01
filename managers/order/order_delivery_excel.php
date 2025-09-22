<?php 
ob_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","order_delivery_excel.html");
$tpl->scan_area("main");

######################## 배송업체 정보 #############################
$delivery_info_array	= array();
$delivery_url_array		= array();
$sql = "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
if($delivery_info = $mysql->get_one($sql)){
	$delivery_info = explode("|*|", $delivery_info);

	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

			$delivery_info2 = explode("|", $delivery_info[$i]);
			
			if($delivery_info2[3] == 0) continue;

			$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];
			$delivery_url_array[$delivery_info2[0]] = $delivery_info2[2];
		}
	}
}

foreach($delivery_info_array as $k => $v) {
	$delivery_num	= $k;
	$delivery_name	= $v;
			
	$tpl->parse("loop_delivery");
}
######################## 배송업체 정보 #############################


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>