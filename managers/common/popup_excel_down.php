<?php 

include_once("../common/popup_top.php");

$pgType	= checkGetVar('pgType');
if($pgType) {
	$type = $pgType;
}
else $type	= checkGetVar('type');

if(!$type) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

switch($type) {
	case 'goods' :		
		$search_variable	= array('field', 'keyword', 'cate', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 'display_use', 'sell_use', 'option_use', 'milage_type', 'delivery_type', 'engine_use', 'sort', 'sort','order_priority','qty_type','vendor','limit_qty','cate_hide','soldout','option_soldout','commission_type','information_use');	
	break;

	case 'member' :
		$search_variable	= array('field' ,'keyword', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 's_range1', 'e_range1', 'range1', 's_range2', 'e_range2', 'range2', 's_range3', 'e_range3', 'range3', 'level', 'mailling', 'sms', 'auth', 'gender', 'marry', 'address1', 'mobile', 'sns_type', 'sort');		
	break;

	case 'order' :
		$search_variable	= array('field' ,'keyword', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 's_range1', 'e_range1', 'range1', 'member', 'mobile', 'pay_type', 'pay_status', 'cash_receipts', 'new', 'use_mileage', 'use_coupon', 'sort');		
	break;

	case 'order2' : case 'order3' :
		$search_variable	= array('field' ,'keyword', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 'pay_type', 'pay_status', 'status', 'vendor', 'sort');		
	break;

	case 'sale' : case 'margin' :
		$search_variable	= array('field', 'keyword', 'date_type', 's_date', 'e_date', 's_range1', 'e_range1', 'range1', 'vendor', 'level', 'type', 'status', 'mobile', 'sort');
	break;

	case 'vendor_sale' : case 'vendor_calculate' :
		$search_variable	= array('field', 'keyword', 'date_type', 's_date', 'e_date', 'vendor', 'type', 'status', 'sort');
	break;

}

$addstring	= "";
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if(strlen($value)>0) $addstring .= "&{$v}={$value}";
}		

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_excel_down.html");
$tpl->scan_area("main");

$name = "{$type}_table_".date("Ymd");

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>