<?php 

$reset = isset($_POST['reset']) ? $_POST['reset'] : '';

if($reset == 1) {
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");

	include_once('../common/ad_init.php');
	include_once(PATH_LIB.'/class.Template.php');  

	$mysql->msgType(2);

	$my_array = array();
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
}
else include_once("../common/top.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","cash_receipts_list.html");
$tpl->scan_area("main");

######################## 회원등급 정보 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE uid > 0 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = stripslashes($row['name']);
}
######################## 회원등급 정보 #############################

######################## 변수 정의 #############################
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}

$DATE1 = date("Y-m-d");
$DATE2 = date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3 = date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4 = date('Y-m-d', strtotime('-1 MONTH', time()));
$DATE5 = date('Y-m-d', strtotime('-3 MONTH', time()));
$DATE6 = date('Y-m-d', strtotime('-6 MONTH', time()));
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_order_cash_receipts'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'status' => 1, 'cash_type' => 1, 'pay_type' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'order_num' => 'function|name|id', 'goods_name' => '', 'receipt_info' => '', 'receipt_error' => '', 'price' => 'function|partial_price', 'pay_type' => 'array', 'status' => 'function|receipt_no|order_num|price|receipt_cancel_no', 'status_date' => 'date', 'signdate' => 'date');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= '';
$status_array					= array("0" => "발급요청", "1" => "발급거절", "2" => "발급실패", "3" => "발급완료", "4" => "발급취소"); 
$pay_type_array					= array("B" => "무통장", "R" => "실시간계좌이체", "V" => "가상계좌이체"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$NAME				= "";
$MEMBER_ID			= "";
$MEMBER_LVEL		= "";
$GOODS				= "";
$RECEIPT_NO			= "";
$PRICE2				= "";
$RECEIPT_CANCEL_NO	= "";

$order_num_function = function ($vls, $name, $id) {
	global $mysql, $tpl, $level_array;

	if($id) {
		$sql	= "SELECT level FROM mallRN_member WHERE id = '{$id}'";
		$data	= $mysql->one_row($sql);

		$GLOBALS['MEMBER_ID']		= $id;
		$GLOBALS['MEMBER_LEVEL']	= $level_array[$data['level']];

		$tpl->parse("is_member");
	}

	$GLOBALS['NAME']		= stripslashes($name);

	return $vls;
};

$price_function = function ($vls, $partial_price) {
	if($partial_price) return number_format($vls - $partial_price)."<br />(".number_Format($vls)." - ".number_Format($partial_price).")";
	else return number_format($vls);
};


$status_function = function ($vls, $receipt_no, $order_num, $price, $receipt_cancel_no) {	
	global $tpl, $status_array;

	if($vls == 3) {
		$GLOBALS['RECEIPT_NO']	= $receipt_no;
		$GLOBALS['PRICE2']		= $price;

		if($receipt_cancel_no) {
			$GLOBALS['RECEIPT_CANCEL_NO']	= $receipt_cancel_no;
			$tpl->parse("is_bill2");		
		}
		
		$tpl->parse("is_bill");		
		$tpl->parse("is_btn_cancel");		
	}
	else if($vls == 2 || $vls == 4) {
		$tpl->parse("is_btn_resend");		
	}
	else if($vls == 0) {
		$tpl->parse("is_btn_apply");
		$tpl->parse("is_btn_reject");
	}

	if($vls == 1 || $vls == 4) {
		$tpl->parse("is_btn_delete");
	}

	return $status_array[$vls];
	
	
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$where2 = "";
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($atotal_record) $listPaging->atotal_record = $atotal_record;
if($total_record) $listPaging->total_record = $total_record;
$listPaging->total_all_record();
######################## listPaging 파라미터 및 검색조건 처리 #############################

######################## 검색 조건 있는 경우 #############################
if($listPaging->check_where == 1) {	
	$tpl->parse("is_search");
}
else if($listPaging->check_where == 2) {
	$tpl->parse("is_search");
	$tpl->parse("is_search_open");
}
######################## 검색 조건 있는 경우 #############################

######################## 리스트 출력 및 페이징 처리 #############################
$PAGING_TYPE	= PAGING_TYPE;
if($listPaging->total_record > 0) {	
	$listPaging->print_record();
	if(PAGING_TYPE==1) $PAGING = "";
	else $PAGING = $listPaging->print_page();	
	//$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset == 1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$ATOTAL				= number_format($ATOTAL);
$TOTAL				= number_format($TOTAL);

$sql				= "SELECT payment_shop_id, payment_cp FROM mallRN_configuration WHERE uid = 1";
$shop_config		= $mysql->one_row($sql);

switch($shop_config['payment_cp']) {
	case "KCP" :
		if($shop_config['payment_shop_id'] == "T0007")	$bill_url = "test";
		else											$bill_url = "";	

		$tpl->parse("is_cash_bill_kcp");
	break;
	case "NICEPAY" :
		$tpl->parse("is_cash_bill_nicepay");
	break;
	case "INICIS" :
		$tpl->parse("is_cash_bill_inicis");
	break;
}

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>