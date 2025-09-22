<?php 

$reset = isset($_POST['reset']) ? $_POST['reset'] : '';

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
$tpl->define("main","db_table_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}

if(!isset($_GET['sort'])) $_GET['sort'] = "TABLE_NAME ASC";

$DATE1 = date("Y-m-d");
$DATE2 = date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3 = date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4 = date('Y-m-d', strtotime('-1 MONTH', time()));
$DATE5 = date('Y-m-d', strtotime('-3 MONTH', time()));
$DATE6 = date('Y-m-d', strtotime('-6 MONTH', time()));
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('INFORMATION_SCHEMA.TABLES'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 's_date' => 5 , 'e_date' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('TABLE_NAME' => '', 'ENGINE' => '', 'VERSION' => '', 'TABLE_ROWS' => '', 'TABLE_COLLATION' => '', 'TABLE_COMMENT'=>'', 'CREATE_TIME'=>'');
$listPaging->default_where		= "TABLE_SCHEMA = '".MYSQL_DB."'";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array	= array("TABLE_NAME", "TABLE_COMMENT");
######################## list_variable : array일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& CREATE_TIME < '{$vls} 23:59:59'";
	else					return "&& CREATE_TIME BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59'";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## 독립 쿼리 사용시 함수 정의 #############################
$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {	
	if($where) $where = "WHERE ".substr(str_replace("a.", "", $where), 3);
	return "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES {$where} ORDER BY TABLE_NAME";
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	if($where) $where = "WHERE ".substr(str_replace("a.", "", $where), 3);

	return "SELECT * FROM INFORMATION_SCHEMA.TABLES {$where} ORDER BY {$GLOBALS['sort']} LIMIT {$start_record},{$page_record_num}";		
};
######################## 독립 쿼리 사용시 함수 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($atotal_record)	$listPaging->atotal_record = $atotal_record;
if($total_record)	$listPaging->total_record = $total_record;
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
	if(PAGING_TYPE==1)	$PAGING = "";
	else				$PAGING = $listPaging->print_page();	
	//$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset==1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$ATOTAL	= number_format($ATOTAL);
$TOTAL	= number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>