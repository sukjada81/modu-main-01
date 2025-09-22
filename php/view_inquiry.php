<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "uid DESC";
if(!isset($_GET['limit']))  $_GET['limit']	= "10";	
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_inquiry'); 
$listPaging->search_variable	= array('g_uid' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'g_name' => '', 'subject' => 'function|secret|id|answer', 'content' => 'function', 'name' => 'function', 'files' => 'function|uid', 'signdate' => 'date');
$listPaging->defaultParam		= "channel={$channel}";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= "";
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$content_function = function ($vls) {		
	return ieHackCheck($vls);
};

$name_function = function ($vls) {		
	$vls	= specialStrReplace2($vls);
	if($GLOBALS['shop_config']['inquiry_privacy_type'] == 1) return mb_substr($vls, 0, 1, 'utf-8')." * ".mb_substr($vls, 2, mb_strlen($vls, 'utf-8'), 'utf-8');
	else return $vls;	
};

$attach_name = "";
$files_function = function ($vls, $uid) {
	global $tpl;
	
	if(!$vls) return;

	$attach_array = explode("|", $vls);
	foreach($attach_array as $k => $v) {
		$GLOBALS['attach_name'] = $uid."/".$v;
		$tpl->parse("loop_attach");
	}
	unset($attach_array);
	$tpl->parse("is_attach");

	return;
};

$ANSWER_TYPE	= "";
$ANSWER			= "";

$subject_function = function ($vls, $secret, $id, $answer) {	
	global $my_level, $my_id, $tpl;
	
	$GLOBALS['SECRET']			= "";
	
	if($secret == 1) {		
		if($my_level < 100) {			
			if(!$id) $GLOBALS['SECRET'] = 1;
			else if($id != $my_id) $GLOBALS['SECRET'] = 2;
		}

		$tpl->parse("is_secret");
	}
		
	$GLOBALS['ANSWER_TYPE'] = "검토중";
	if(trim($answer)) {
		$GLOBALS['ANSWER']		= nl2br(stripslashes($answer));
		$GLOBALS['ANSWER_TYPE'] = "답변완료";
		$tpl->parse("is_answer");
	}

	return specialStrReplace2($vls);
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
	@$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else {
	@$tpl->parse("empty_list");
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