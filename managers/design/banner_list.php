<?php 

define('UPLOAD_FOLDER', '../../image/banner');

$reset			= isset($_POST['reset']) ? $_POST['reset'] : '';
$image_folder	= UPLOAD_FOLDER;

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
include_once("../../{$use_skin}/info/skin_define.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","banner_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "name";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_banner'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'code' => 1, 'status' => 1, 'target' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'code' => 'function', 'moddate'=> '', 'image1' => '', 'name' => '', 's_date' => 'function|e_date', 'status'=>'array', 'signdate'=>'date', 'link1'=>'link');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array	= "";
$status_array	= array("0" => "사용함", "1" => "사용안함"); 

foreach($BANNER_DEFINE as $k => $v) {
	$TITLE			= $v[0];
	$CODE			= $k;
	$tpl->parse("loop_code");
}
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$code_function = function ($vls) {
	global $BANNER_DEFINE, $PHP_SELF;

	$code_name = $BANNER_DEFINE[$vls][0];	
	return "<a href='{$PHP_SELF}?code={$vls}' title='{$code_name} 배너보기' class='underLine'>{$code_name}</a>";
};

$s_date_function = function ($vls, $e_date) {
	if($vls == '1000-01-01 00:00:00') $vls = "";
	if($e_date == '1000-01-01 23:59:59' || $e_date == '1000-01-01 00:00:00') $e_date = "";

	if(!$vls && !$e_date) return;
	else return $vls.' ~ '.$e_date;
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
	$tpl->parse("is_search_open");
}
######################## 검색 조건 있는 경우 #############################

if($code && !$keyword && strlen($status) == 0 && strlen($target) == 0) {
	$TTL1							= "순서";
	$sort							= "sequence ASC";	
	$listPaging->page_record_num	= '1000';
	$type_name						= $BANNER_DEFINE[$code][0];
	$tpl->parse("is_sort1");
	$tpl->parse("is_sort2");
}
else {
	$TTL1 = "번호";
	$tpl->parse("is_default");
}

######################## 리스트 출력 및 페이징 처리 #############################
$PAGING_TYPE	= PAGING_TYPE;
if($listPaging->total_record > 0) {
	$listPaging->print_record();
	if(PAGING_TYPE == 1) $PAGING = "";
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