<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
if(!isset($_GET['limit']))  $_GET['limit']	= "10";	
if(!isset($_GET['type']))	$_GET['type']	= "1";	
$sort		= "uid DESC";

${"type".$_GET['type']}		= "selected";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_coupon'); 
$listPaging->search_variable	= array('type' => 2, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'c_uid' => 'function|status|usedate|g_uid|e_date', 'e_date' => 'function', 'signdate' => 'date');
$listPaging->defaultParam		= "channel={$channel}&type={$_GET['type']}";
$listPaging->default_where		= "id = '{$my_id}'";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= "";
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$COUPON_MESSAGE1	= "";
$COUPON_MESSAGE2	= "";
$COUPON_MESSAGE3	= "";
$COUPON_MESSAGE4	= "";
$COUPON_MESSAGE5	= "";
$COUPON_MESSAGE6	= "";
$COUPON_TYPE		= "";
$G_UID				= "";

$c_uid_function = function ($vls, $status, $usedate, $g_uid, $e_date) {
	global $mysql, $tpl; 

	$GLOBALS['COUPON_MESSAGE3'] = $GLOBALS['COUPON_MESSAGE4'] = "";
	$sql = "SELECT * FROM mallRN_coupon_manager WHERE uid = '{$vls}'";
	if($data = $mysql->one_row($sql)) {
		$GLOBALS['COUPON_MESSAGE1']	= stripslashes($data['name']);

		if($data['type'] == 4)	{
			$GLOBALS['COUPON_TYPE']			= "상품할인 쿠폰";
			$sql = "SELECT name FROM mallRN_goods WHERE uid = '{$g_uid}'";
			$GLOBALS['G_UID']				= $g_uid;
			$GLOBALS['COUPON_MESSAGE5']		= stripslashes($mysql->get_one($sql));
			$tpl->parse("is_goods");
		}
		else {
			$GLOBALS['COUPON_TYPE']			= "장바구니 쿠폰";	
			$GLOBALS['COUPON_MESSAGE5']		= "";
		}

		if($data['discount_type'] == 'P') {
			$GLOBALS['COUPON_MESSAGE2']	= "{$data['discount']}%";	
			if($data['discount_limit']) {				
				$GLOBALS['COUPON_MESSAGE3'] = "(최대 ".number_format($data['discount_limit'])."원)";
			}
		}
		else {
			$GLOBALS['COUPON_MESSAGE2']	= number_format($data['discount'])."원";				
		}
		
		if($data['use_limit']) {
			$GLOBALS['COUPON_MESSAGE4'] = "<br />총 상품금액 ".number_format($data['use_limit'])."원 이상일 구매시";
		}
	}
	$GLOBALS['COUPON_MESSAGE6']	= "";
	if($status == 1)							$GLOBALS['COUPON_MESSAGE6'] = "사용완료 ".date("Y.m.d", $usedate);
	else if($status == 2)						$GLOBALS['COUPON_MESSAGE6'] = "기간만료";
	else if($e_date < date("Y-m-d 23:59:59"))	$GLOBALS['COUPON_MESSAGE6'] = "기간만료";
	
	if($GLOBALS['COUPON_MESSAGE6']) $tpl->parse("is_status");

	return "";
};

$e_date_function = function ($vls) {
	return substr($vls, 0, 10);
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$type_where = function ($vls) {
	if($vls == 1)	return "&& (a.status = 0 && a.e_date > '".date("Y-m-d 23:59:59")."')";
	else			return "&& (a.status != 0 || a.e_date < '".date("Y-m-d 23:59:59")."')";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($total_record) $listPaging->total_record = $total_record;
$listPaging->total_record();

$lastPage = checkPostVar('lastPage', 0);
if($lastPage == 1 || ($lastPage == 2 && $TOTAL == 0)) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["total" => number_format($TOTAL), "lastPage" => $total_page];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}
######################## listPaging 파라미터 및 검색조건 처리 #############################

######################## 리스트 출력 및 페이징 처리 #############################
$PAGING			= "";
$PAGING_TYPE	= PAGING_TYPE;
if($listPaging->total_record > 0) {
	$listPaging->print_record();
	if(PAGING_TYPE == 0) $PAGING = $listPaging->print_page();	
	$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else {
	$tpl->parse("empty_list");
}
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset == 1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$TOTAL = number_format($TOTAL);

?>