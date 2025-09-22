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
$tpl->define("main","popup_member_search.html");
$tpl->scan_area("main");

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
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(!isset($_GET['field2'])) $_GET['field2'] = "multi";
if(!isset($_GET['field3'])) $_GET['field3'] = "multi";
if(!isset($_GET['field4'])) $_GET['field4'] = "multi";
if(!isset($_GET['range1'])) $_GET['range1'] = "order_cnt";
if(!isset($_GET['range2'])) $_GET['range2'] = "order_cnt";
if(!isset($_GET['range3'])) $_GET['range3'] = "order_cnt";
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
$listPaging = new listPaging('mallRN_member'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5 , 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 's_range1' => 5, 'e_range1' => 5, 'range1' => 2, 's_range2' => 5, 'e_range2' => 5, 'range2' => 2, 's_range3' => 5, 'e_range3' => 5, 'range3' => 2, 'level' => 1, 'mailling' => 1, 'sms' => 1, 'auth' => 1, 'gender' => 1, 'marry' => 1, 'address1' => 1, 'mobile' => 1, 'sns_type' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0, 'type' => 6);
$listPaging->list_variable		= array('uid' => '', 'name' => '', 'id' => 'function|name|cell|email', 'level' => 'array', 'cell' => 'function', 'reference' => '', 'signdate' => 'date');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array	= array("name", "id", "email", "tel", "cell", "comp", "comp_num", "comp_owner", "reference");
$gender_array	= array("M" => "남성",		"F" => "여성",		"N" => "미선택"); 
$marry_array	= array("M" => "기혼",		"S" => "미혼",		"N" => "미선택"); 
$auth_array		= array("Y" => "승인완료",		"N" => "미승인"); 
$mailling_array	= array("Y" => "수신허용",		"N" => "수신안함"); 
$sms_array		= array("Y" => "수신허용",		"N" => "수신안함"); 
$mobile_array	= array("Y" => "모바일",		"N" => "PC");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$ID2			= "";
$id_function = function ($vls, $name, $cell, $email) {
	$GLOBALS['ID2'] = "{$name}|{$cell}|{$email}";

	return $vls;
};

$cell_function = function ($vls) {
	if(strlen($vls) == 11)		return substr($vls, 0, 3)."-".substr($vls, 3, 4)."-".substr($vls, 7, 4);
	else						return substr($vls, 0, 2)."-".substr($vls, 2, 4)."-".substr($vls, 6, 4);
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};
$range1_where = function ($vls) {
	if(!$GLOBALS['s_range1'] && !$GLOBALS['e_range1']) return;
	if(!$GLOBALS['s_range1']) return "&& a.{$vls} < {$GLOBALS['e_range1']} ";
	else if(!$GLOBALS['e_range1']) return "&& a.{$vls} > {$GLOBALS['s_range1']} ";
	else return "&& a.{$vls} BETWEEN '{$GLOBALS['s_range1']}' AND '{$GLOBALS['e_range1']}' ";
};

$range2_where = function ($vls) {
	if(!$GLOBALS['s_range2'] && !$GLOBALS['e_range2']) return;
	if(!$GLOBALS['s_range2']) return "&& a.{$vls} < {$GLOBALS['e_range2']} ";
	else if(!$GLOBALS['e_range2']) return "&& a.{$vls} > {$GLOBALS['s_range2']} ";
	else return "&& a.{$vls} BETWEEN '{$GLOBALS['s_range2']}' AND '{$GLOBALS['e_range2']}' ";
};

$range3_where = function ($vls) {
	if(!$GLOBALS['s_range3'] && !$GLOBALS['e_range3']) return;
	if(!$GLOBALS['s_range3']) return "&& a.{$vls} < {$GLOBALS['e_range3']} ";
	else if(!$GLOBALS['e_range3']) return "&& a.{$vls} > {$GLOBALS['s_range3']} ";
	else return "&& a.{$vls} BETWEEN '{$GLOBALS['s_range3']}' AND '{$GLOBALS['e_range3']}' ";
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

if($type == 'aaa') {
	$multi = 1;
	$disabled = "";
}
else {
	$multi = 0;
	$disabled = "disabled='disabled'";
}

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