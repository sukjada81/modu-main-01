<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
$_GET['sort']	= "uid DESC";
if(!isset($_GET['limit']))  $_GET['limit']	= "12";	
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_exhibition'); 
$listPaging->search_variable	= array('sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'name' => 'function|image1', 's_date' => 'function|discount_yn|e_date', 'discount_yn' => 'array', 'discount' => 'number', 'status'=>'array');
$listPaging->defaultParam		= "channel={$channel}";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= "";
$discount_yn_array				= array("N" => "<i class='fas fa-times'></i>", "Y" => "<i class='far fa-circle'></i>"); 
$status_array					= array("0" => "-", "1" => "준비중", "2" => "진행중", "3" => "종료"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$IMAGE	= "";
$NAME2	= "";
$name_function = function ($vls, $image) {	
	global $tpl, $t;
	
	$GLOBALS['NAME2'] = $vls;

	if($image) {
		$GLOBALS['IMAGE'] = $image."?t=".$t;
		$tpl->parse("is_image");
	}
	else {		
		$tpl->parse("is_text");
	}


	return $vls;
};

$s_date_function = function ($vls, $discount_yn, $e_date) {	
	if($discount_yn == 'N') return;
	return $vls.' ~ '.$e_date;
};
######################## list_variable : funtion일 경우 정의 #############################

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