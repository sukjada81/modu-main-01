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
$tpl->define("main","vendor_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "multi";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_vendor'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'auth' => 1, 'sell' => 1, 'goods_auth' => 1, 'account_cycle' => 1, 'image1' => 4, 'image2' => 4, 'delivery_type' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'id' => '', 'comp_name' => '', 'comp_tel' => '', 'cont_name' => '', 'commission' => '', 'account_cycle' => 'array', 'auth' => 'array', 'sell' => 'array', 'goods_auth' => 'array', 'delivery_type' => 'array', 'signdate' => 'date');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("id", "comp_name", "comp_license_no", "cont_name");
$account_cycle_array			= array("","주 1회","월 2회","월 1회","자율");
$auth_array						= array("R"=>"승인요청","Y"=>"승인완료","N"=>"승인보류"); 
$sell_array						= array("A"=>"판매허용","R"=>"판매준비","N"=>"판매중지"); 
$goods_auth_array				= array("A"=>"자동승인","P"=>"수동승인"); 
$delivery_type_array			= array("0"=>"판매자","1"=>"본사"); 
######################## list_variable : array일 경우 정의 #############################

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
else {
	####################### 관리자 로그 ##########################	
	adminLog($my_id, '판매사리스트', 7);
	####################### 관리자 로그 ##########################
}

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>