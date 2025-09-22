<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","calculate_info.html");
$tpl->scan_area("main");

$mode = checkGetVar('mode');
if(!$mode) $mode = 'write';

$addstring			= "";
$search_variable	= array('field', 'keyword', 'date_type', 's_date', 'e_date', 'vendor', 'tax_bill', 'status', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify') {
	
	$uid		= $_GET['uid'];
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다." ,"back");

	$sql		= "SELECT * FROM mallRN_sales_calculate WHERE uid='{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array	= array('vendor', 'vendor_name', 's_date', 'e_date', 'sum', 'price1', 'price2', 'price3', 'bank_name', 'bank_num', 'bank_owner');

	for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
		${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
	}

	$TTL		= "수정";	
}
else {
	$mode		= "write";
	$sum		= 0;
	$price1		= 0;
	$price2		= 0;
	$price3		= 0;
	$tax_bill	= 0;
	$status		= 0;
	
	$TTL		= "등록";
}

######################## 판매사 정보 #############################
$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
$mysql->query($sql);

$vendor_commission = array();
while($row = $mysql->fetch_array()){
	$vendor_id				= specialStrReplace($row['id']);
	$vendor_name			= specialStrReplace($row['comp_name']);
	$vbank_name				= specialStrReplace($row['bank_name']);
	$vbank_num				= specialStrReplace($row['bank_num']);
	$vbank_owner			= specialStrReplace($row['bank_owner']);
	$tpl->parse("loop_vendor");
}
unset($vendor_id, $vendor_name);
######################## 판매사 정보 #############################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>