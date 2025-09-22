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
$tpl->define("main","exhibition_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "name";
$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_exhibition'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'discount_yn' => 1, 'status' => 1, 'cate_info' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'name' => '', 's_date' => 'function|discount_yn|e_date', 'discount_yn' => 'array', 'discount' => 'number', 'status'=>'array', 'cnts' => 'function|uid', 'signdate'=>'date');
$listPaging->field_where		= array('goods_name');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= "";
$discount_yn_array				= array("N" => "<i class='fas fa-times'></i>", "Y" => "<i class='far fa-circle'></i>"); 
$status_array					= array("0" => "-", "1" => "준비중", "2" => "진행중", "3" => "종료"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$s_date_function = function ($vls, $discount_yn, $e_date) {	
	if($discount_yn == 'N') return;
	return $vls.' ~ '.$e_date;
};

$cnts_function = function ($vls, $uid) {
	global $mysql;

	$sql = "SELECT count(*) FROM mallRN_exhibition_goods WHERE euid = '{$uid}'";
	return "<a href='exhibition_goods.php?exhibition={$uid}' class='underLine' title='모음전 상품관리로 이동'>".number_format($mysql->get_one($sql))."</a>";
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$cate_info_where = function ($vls) {
	if($vls == 1) return " && cate_info != ''";
	else return " && cate_info = ''";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

$goods_nameFieldWhere = function ($vls) {
	global $mysql;
;
	$sql = "SELECT uid FROM mallRN_goods WHERE INSTR(name, '{$vls}') && exhibition !=''";
	$uid = $mysql->get_one_jum($sql);

	$sql = "SELECT euid FROM mallRN_exhibition_goods WHERE guid IN('{$uid}')";
	$uid = $mysql->get_one_jum($sql);

	return "&& uid IN('{$uid}')";
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