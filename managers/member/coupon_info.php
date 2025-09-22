<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","coupon_info.html");
$tpl->scan_area("main");

$mode				= isset($_GET['mode']) ? $_GET['mode'] : 'write';
	
$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_coupon_manager WHERE uid = '{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('name', 'type', 'discount', 'discount_type', 'discount_limit', 'use_type', 'use_s_date', 'use_e_date', 'use_day', 'use_limit', 'use_limit2', 'goods_order');
	
	foreach($item_array as $k => $v) {
		${$v} = stripslashes($data[$v]);
	}

	${"checked_type_".$type}			= "checked='checked'";
	${"checked_use_type_".$use_type}	= "checked='checked'";
	$use_s_date							= substr($use_s_date, 0, 10);
	$use_e_date							= substr($use_e_date, 0, 10);
	if($use_e_date < "1111-00-00") $use_e_date = "";
	
	$TTL								= "수정";	

	if($goods_order) $tpl->parse("is_goods_order");

}
else {

	$checked_type_0		= "checked='checked'";
	$checked_use_type_0	= "checked='checked'";
	$discount			= 0;
	$discount_type		= 'P';
	$discount_limit		= 0;
	$use_s_date			= date("Y-m-d");
	$use_day			= 0;
	$use_limit			= 0;
	$TTL				= "등록";
	
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>