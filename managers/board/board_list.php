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
$tpl->define("main","board_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "multi";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_board_manager'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'id' => '', 'name' => '', 'skin' => '', 'count' => 'function|id', 'form_add1' => 'function|form_add2|form_add3|form_add4|form_add5', 'signdate'=>'date');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array	= array('id', 'name');
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$GLOBALS['DISABLED'] = "";
$count_function = function ($vls, $id) {
	global $mysql, $SMain, $tpl;

	$default_board = array('faq', 'notice', 'counsel', 'vnotice', 'vcounsel');
	
	if($id != 'vnotice' && $id != 'vcounsel') $tpl->parse("is_pc");

	if(in_array($id, $default_board))	$GLOBALS['DISABLED'] = "disabled";
	else								$GLOBALS['DISABLED'] = "";		

	$sql = "SELECT count(*) FROM mallRN_board_{$id}";
	return $mysql->get_one($sql);
};

$form_add1_function = function ($vls, $vls2, $vls3, $vls4, $vls5) {
	$cnt = 0;
	if($vls > 0)	$cnt ++;
	if($vls2 > 0)	$cnt ++;
	if($vls3 > 0)	$cnt ++;
	if($vls4 > 0)	$cnt ++;
	if($vls5 > 0)	$cnt ++;

	if($cnt == 0)	return "미사용";
	else			return "{$cnt}개 사용";
};
######################## list_variable : funtion일 경우 정의 #############################

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