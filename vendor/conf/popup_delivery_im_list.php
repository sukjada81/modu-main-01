<?php 
ob_start();

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
else include_once("../common/popup_top.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_delivery_im_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "multi";

$sql				= "SELECT p_uid FROM mallRN_im_areas_except WHERE vendor = '{$v_my_id}'";
$exception_array	= explode(",", $mysql->get_one_jum($sql));


######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_im_areas'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'base' => 1, 'exception' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'postcode' => '', 'address' => '', 'base' => 'function|uid', 'signdate' => 'date');
$listPaging->default_where		= "base = 0  || (base = 1 && vendor = '{$v_my_id}')";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array	= array("postcode", "address");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$EXCEPTION = "";
$base_function = function ($vls, $uid) {
	global $exception_array;
	if(in_array($uid, $exception_array)) $GLOBALS['EXCEPTION'] = "checked='checked'";
	else $GLOBALS['EXCEPTION'] = "";

	if($vls == "0") return "-";
	else return "<i class='xi-close masterTooltip btnDel' title='삭제하기' data-uid='{$uid}'></i>";
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$exception_where = function ($vls) {
	global $exception_array;

	$items	= join(",", $exception_array);
	if(!$items) $items = 0;
	
	if($vls == 1) return "&& a.uid IN ({$items})";
	else return "&& a.uid NOT IN ({$items})";
	
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
	$tpl->parse("is_paging_type_".PAGING_TYPE);
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

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>