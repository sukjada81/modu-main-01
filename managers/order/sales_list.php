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
$tpl->define("main","sales_list.html");
$tpl->scan_area("main");

######################## 판매사 정보 #############################
$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
$mysql->query($sql);

$vendor_array = array();
while($row = $mysql->fetch_array()){
	$vendor_id					= specialStrReplace($row['id']);
	$vendor_name				= specialStrReplace($row['comp_name']);
	$vendor_array[$vendor_id]	= $vendor_name;
	$tpl->parse("loop_vendor");
}
unset($vendor_id, $vendor_name);
######################## 판매사 정보 #############################

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	
	$name						= specialStrReplace($row['name']);
	$levels						= specialStrReplace($row['level']);
	$level_array[$row['level']] = $name;
	
	$tpl->parse("loop_level");
}
######################## 회원등급 #############################

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['range1']))	$_GET['range1'] = "pay_total";
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
$listPaging						= new listPaging('mallRN_order_sales'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 's_range1' => 5, 'e_range1' => 5, 'range1' => 2, 'vendor' => 1, 'level' => 1, 'type' => 1, 'status' => 1, 'mobile' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => 'function|status', 'order_num' => '', 'title' => '', 'price' => 'number', 'type' => 'array', 'status' => 'array', 'signdate' => 'datetime');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= array("title", "order_num");
$type_array				= array("상품", "배송비", "마일리지", "쿠폰", "할인", "CP수수료");
$status_array			= array("매출발생", "매출차감");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$COLORS = "";
$uid_function = function ($vls, $status) {
	if($status == 0)	$GLOBALS['COLORS'] = "colorRed";
	else				$GLOBALS['COLORS'] = "colorBlue";

	return $vls;
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};

$range1_where = function ($vls) {
	if(!$GLOBALS['s_range1'] && !$GLOBALS['e_range1']) return;
	
	$s_range1 = str_replace(",", "", $GLOBALS['s_range1']);
	$e_range1 = str_replace(",", "", $GLOBALS['e_range1']);

	if(!$s_range1) return "&& a.{$vls} < {$e_range1} ";
	else if(!$e_range1) return "&& a.{$vls} > {$s_range1} ";
	else return "&& a.{$vls} BETWEEN '{$s_range1}' AND '{$e_range1}' ";
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