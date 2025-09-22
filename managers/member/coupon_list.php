<?php 

$reset	= isset($_POST['reset']) ? $_POST['reset'] : '';

if($reset==1) {
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
$tpl->define("main","coupon_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "name";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_coupon_manager'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'name' => '', 'type' => 'array', 'discount' => 'function|discount_type|discount_limit', 'use_type' => 'function|use_s_date|use_e_date|use_day', 'goods_order' => 'function', 'coupon' => 'function|uid', 'signdate' => 'date');
$listPaging->field_where		= array('id');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array		= "";
$type_array			= array("0" => "관리자 수동발급", "1" => "회원가입시 자동발급", "2" => "첫주문시 자동발급", "3" => "생일쿠폰 자동발급", "4" => "상품상세페이지 다운로드");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$discount_function = function ($vls, $discount_type, $discount_limit) {
	if($discount_type == 'P') {
		if($discount_limit > 0) return $vls."% (최대 ".number_format($discount_limit)."원)";
		else					return $vls."%";
	}
	else {
		return number_format($vls)."원";
	}
};

$use_type_function = function ($vls, $use_s_date, $use_e_date, $use_day) {
	if($vls == 0)	return substr($use_s_date, 0, 10)." ~ ".substr($use_e_date, 0, 10);
	else			return "발급 후 ".$use_day."일";		
};

$goods_order_function = function ($vls) {
	return count(explode(",", $vls))."개";
};

$coupon_function = function ($vls, $uid) {
	global $mysql;

	$sql = "SELECT count(*) FROM mallRN_coupon WHERE c_uid = '{$uid}'";
	return number_format($mysql->get_one($sql));
};
######################## list_variable : funtion일 경우 정의 #############################

$idFieldWhere = function ($vls) {
	return " && id = '{$vls}'";
};

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

if($reset==1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml"=>$tpl->tprint("is_list_area",1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>