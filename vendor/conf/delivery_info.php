<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","delivery_info.html");
$tpl->scan_area("main");


$sql			= "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
$delivery_info	= stripslashes($mysql->get_one($sql));

$delivery_info_array = array();
if($delivery_info) {
	$delivery_info = explode("|*|", $delivery_info);
	$delivery_max_num = $delivery_info[0];

	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

			$delivery_info2 = explode("|", $delivery_info[$i]);

			$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1]."|".$delivery_info2[2];
		}
	}
}
else {
	$delivery_max_num = 1;
}

$item_array = array('delivery_type','delivery_d_price','delivery_p_type','delivery_p_price1','delivery_p_price2','delivery_info','delivery_im_areas1_used','delivery_im_areas1_price','delivery_im_areas2_used','delivery_im_areas2_price');

$sql = "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$v_my_id}'";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

${"checked_type_".$delivery_type} = "checked='chedked'";	
${"checked_delivery_im_areas1_used_".$delivery_im_areas1_used} = "checked='chedked'";	
${"checked_delivery_im_areas2_used_".$delivery_im_areas2_used} = "checked='chedked'";

if($delivery_info) {
	$delivery_info	= explode("|*|", $delivery_info);
	$ck_num_array	= array();
	for($i = 0, $cnt = count($delivery_info); $i < $cnt; $i++) {
		$delivery_info2 = explode("|", $delivery_info[$i]);
		$delivery_info3 = explode("|", @$delivery_info_array[$delivery_info2[0]]);

		if(!$delivery_info3[0]) continue;
		
		$delivery_num	= $delivery_info2[0];
		$delivery_name	= $delivery_info3[0];
		$delivery_url	= $delivery_info3[1];
		$delivery_used	= $delivery_info2[1];
		if($delivery_used!='1') $delivery_used = "0";

		$ck_num_array[]	= $delivery_num;

		$tpl->parse("loop_delivery");			
	}	

	foreach($delivery_info_array as $k => $v) {
		if(!in_array($k, $ck_num_array)) {

			$delivery_info3 = explode("|", $v);
			
			$delivery_num	= $k;
			$delivery_name	= $delivery_info3[0];
			$delivery_url	= $delivery_info3[1];
			$delivery_used	= 0;

			$tpl->parse("loop_delivery");
		}
	}
}
else {
	foreach($delivery_info_array as $k => $v) {
		$delivery_info3 = explode("|", $v);
		
		$delivery_num	= $k;
		$delivery_name	= $delivery_info3[0];
		$delivery_url	= $delivery_info3[1];
		$delivery_used	= 1;

		$tpl->parse("loop_delivery");				
	}
}
unset($delivery_num, $delivery_name, $delivery_url, $delivery_used);

$num = 3;
$sql = "SELECT * FROM mallRN_delivery_configuration WHERE vendor = '{$v_my_id}' ORDER BY uid ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {

	$uid = $row['uid'];
	$title = stripslashes($row['title']);
	$price = number_format(stripslashes($row['price']));

	if($row['used']==1) $checked = "checked='checked'";
	else $checked = "";
	
	$tpl->parse("loop_conf");
	$num++;
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>