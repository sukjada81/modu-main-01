<?php 

include_once("../common/popup_top.php");

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';

if(!$uid) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_goods_option.html");
$tpl->scan_area("main");

$sql = "SELECT goods_option_info FROM mallRN_configuration WHERE uid=1";
$multi_data = $mysql->one_row($sql);

$option_data = $multi_data['goods_option_info'];
if($option_data) {
	$option_data = explode("|*|", $option_data);
	foreach($option_data as $k => $v) {
		$option_data2	= explode("|", $v);
		if($option_data2[1]!=1) continue;
		$option_name	= specialStrReplace($option_data2[0]);
		$option_info	= specialStrReplace($option_data2[2]);
		$tpl->parse("loop_option_select");
	}
	unset($option_data, $option_data2, $option_name, $option_info);
}

$sql = "SELECT option_info FROM mallRN_goods WHERE uid='{$uid}'";
if(!$option_info = $mysql->get_one($sql)) iframeViewError("등록된 정보가 없거나 삭제되었습니다.");

$option_info = explode("|*|", $option_info);
foreach($option_info as $k => $v) {
	$option_info2	= explode("|", $v);
	$option_name	= $option_info2[0];
	$option_value	= $option_info2[1];
	$tpl->parse("loop_option_info");
}	
unset($option_name, $option_value, $option_info, $option_info2);

$sql = "SELECT * FROM mallRN_goods_option WHERE guid='{$uid}' ORDER BY sequence ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	$option_value		= explode("|",stripslashes($row['value']));
	for($j=0, $i=1, $cnt=count($option_value); $j<$cnt; $j++) {
		$option_value2 = $option_value[$j];
		$tpl->parse("loop_option_table_value");					
		$i++;
	}

	$option_price		= number_format(stripslashes($row['price']));
	$option_qty_type	= stripslashes($row['qty_type']);
	$option_qty			= number_format(stripslashes($row['qty']));
	$option_used		= stripslashes($row['used']);
	$option_code		= stripslashes($row['code']);
	$option_uid			= stripslashes($row['uid']);

	$tpl->parse("loop_option_table");			
}
unset($option_value, $option_price, $option_qty_type, $option_qty, $option_used, $option_code);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>