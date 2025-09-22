<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

$sql = "SELECT count(*) FROM mallRN_member WHERE id = '{$my_id}'";
if($mysql->get_one($sql) == 0) exit;

$sql			= "SELECT SUM(mileage) as mileage, SUM(use_mileage) as use_mileage FROM mallRN_mileage WHERE id = '{$my_id}'";
$data			= $mysql->one_row($sql);

$mileage1		= number_format($data['mileage'] - $data['use_mileage']);
$mileage2		= number_format($data['mileage']);
$mileage3		= number_format($data['use_mileage']);

######################## 변수 정의 #############################
if(!isset($_GET['limit']))  $_GET['limit']	= "10";	
if(!isset($_GET['type']))	$_GET['type']	= "1";	
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}
$sort		= "uid DESC";

${"type".$_GET['type']}		= "selected";

$DATE1		= date("Y-m-d");
$DATE2		= date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3		= date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4		= date('Y-m-d', strtotime('-1 MONTH', time()));
$DATE5		= date('Y-m-d', strtotime('-3 MONTH', time()));
$DATE6		= date('Y-m-d', strtotime('-6 MONTH', time()));
####################### 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_mileage'); 
$listPaging->search_variable	= array('type' => 2, 's_date' => 5, 'e_date' => 2, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'content' => '', 'mileage' => 'number', 'mileage2' => 'function|proc_mileage|mileage', 'use_mileage' => 'number', 'expired_date' => 'function', 'signdate' => 'datetime');
$listPaging->defaultParam		= "channel={$channel}&type={$_GET['type']}";
$listPaging->default_where		= "id = '{$my_id}'";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= "";
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$EXPIRED_DATE2	= "";
$expired_date_function = function ($vls, $proc_mileage, $mileage) {
	global $tpl;

	if($vls == '1000-01-01') {
		$GLOBALS['EXPIRED_DATE2']	= "-";
		return "-";
	}
	else {
		$GLOBALS['EXPIRED_DATE2']	= $vls;
		@$tpl->parse("is_expired_date");
		return $vls;
	}
};

$mileage2_function = function ($vls, $proc_mileage, $mileage) {
	return number_format($mileage - $proc_mileage);
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$type_where = function ($vls) {
	if($vls == 1)	return "&& a.mileage > 0";
	else if($vls == 2)	return "&& a.use_mileage > 0";
	else {
		return "&& expired_use = 1 && expired = 0 && proc_mileage < mileage";
	}
};

$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.signdate) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.signdate) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
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