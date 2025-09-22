<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

if($reset == 0) {
	if(!isset($my_page)) {
		$sql = "SELECT * FROM mallRN_review WHERE best = '1' ORDER BY uid DESC LIMIT 4";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()) {
			$attach_array = explode("|", $row['files']);

			$ck = 0;			
			foreach($attach_array as $k => $v) {
				if($v) {
					$IMAGE = DEFAULT_PATH."image/review/{$row['uid']}/{$v}";
					$tpl->parse("is_image");
					$ck = 1;
					break;
				}
			}

			if($ck == 0) {
				$sql		= "SELECT image2, moddate FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
				$data		= $mysql->one_row($sql);

				if($data['image2'])	$IMAGE = DEFAULT_PATH."image/goods/img{$data['image2']}?t={$data['moddate']}";
				else				$IMAGE = DEFAULT_PATH."image/no_image.png";

				$tpl->parse("is_image");
			}
			
			$UID			= $row['uid'];
			$G_UID			= $row['g_uid'];
			$G_NAME			= stripslashes($row['g_name']);
			$row['name']	= specialStrReplace2($row['name']);
			$NAME			= mb_substr($row['name'], 0, 1, 'utf-8')." * ".mb_substr($row['name'], 2, mb_strlen($row['name'], 'utf-8'), 'utf-8');
			$DATE			= date("Y-m-d", $row['signdate']);
			$CONTENT		= ieHackCheck($row['content']);

			for($i = 0; $i < $row['stars']; $i ++) $tpl->parse("loop_stars");

			$tpl->parse("loop_best");
		}
	}
}

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "uid DESC";
if(!isset($_GET['limit']))  $_GET['limit']	= "10";	
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_review'); 
$listPaging->search_variable	= array('sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'g_uid' => '', 'g_name' => 'function|g_uid', 'content' => 'function', 'name' => 'function', 'stars' => 'function|op_name', 'files' => 'function|uid', 'signdate' => 'date');
$listPaging->defaultParam		= "channel={$channel}";
if(isset($my_page))	$listPaging->default_where		= "id = '{$my_id}'";
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
	return mb_substr($vls, 0, 1, 'utf-8')." * ".mb_substr($vls, 2, mb_strlen($vls, 'utf-8'), 'utf-8');
};

$G_IMAGE	= "";
$g_name_function = function ($vls, $g_uid) {
	global $mysql;

	$sql		= "SELECT image3, moddate FROM mallRN_goods WHERE uid = '{$g_uid}'";
	$data		= $mysql->one_row($sql);

	if($data['image3'])	$GLOBALS['G_IMAGE'] = DEFAULT_PATH."image/goods/img{$data['image3']}?t={$data['moddate']}";
	else				$GLOBALS['G_IMAGE'] = DEFAULT_PATH."image/no_image.png";

	return $vls;
};

$OP_NAME = "";
$stars_function = function ($vls) {
	global $tpl;
	
	for($i = 0; $i < $vls; $i ++) $tpl->parse("loop_stars");

	return;
};

$GLOBALS['attach_name'] = "";
$files_function = function ($vls, $uid) {
	global $tpl;
	
	if($vls) $tpl->parse("is_files");
	else return;

	$attach_array = explode("|", $vls);
	foreach($attach_array as $k => $v) {
		$GLOBALS['attach_name'] = $uid."/".$v;
		$tpl->parse("loop_attach");
	}
	unset($attach_array);
	$tpl->parse("is_attach");

	return;
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