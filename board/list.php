<?php

if(!defined('_B2BMALL_BOARD_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "S";
if(!isset($_GET['limit']))  $_GET['limit'] = !empty($board_info['record_num']) ? $board_info['record_num'] : 10;	
$cate			= checkGetVar('cate');
$ATOTAL			= 0;
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_board_'.$b_id); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'cate' => 1, 'id' => 1 , 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$default_list_array				= array('uid' => 'function|files|subject', 'depth' => 'function', 'cate' => 'array', 'subject' => 'function|secret|id|depth|o_id|count_comment', 'name' => 'function', 'count' => 'number', 'signdate' => 'function|notice|secret', 'dels' => 'function');
$add_list_array					= array('content' => '');
$listPaging->defaultParam		= "channel=cs_board&b_id={$b_id}";
$listPaging->is_board			= "1";

if($board_info['types'] == 1)	$listPaging->list_variable = array_merge($default_list_array, $add_list_array);
else							$listPaging->list_variable = $default_list_array;			
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("subject", "content", "name", "id");

$cate_info = explode("|*|", $board_info['cate_info']);
if($cate_info[0] > 100) {
	if(!$cate) $cate_selected_all = "selected";

	$cate_array = array();
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);

		$cate_num = $cate_info2[0];
		$cate_name = $cate_info2[1];

		$cate_array[$cate_num] = $cate_name;

		if($cate == $cate_num)	$cate_selected = "selected";
		else					$cate_selected = "";
		
		$tplBo->parse("loop_cate");
	}
	$tplBo->parse("is_cate");	
}
else $cate_array = array('');
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$SECRET			= "";
$COMMENT_COUNT	= "";
$IMAGE			= "";
$SUBJECT2		= "";
$EMPTY			= "";
$COUNT			= "";
$SIGNDATE		= "";

$depth_function = function ($vls) {	
	global $tplBo;
	if($vls == 0) return;
	
	for($i = 1; $i < $vls; $i ++) $GLOBALS['EMPTY'] .= "&nbsp;&nbsp;&nbsp;&nbsp;";
	$tplBo->parse("is_depth");

	return ;
};

$subject_function = function ($vls, $secret, $id, $depth, $o_id, $count_comment) {	
	global $tplBo, $my_level, $my_id;
	
	if($secret == 1) {		
		if($my_level < 100) {			
			if($depth > 0) {	
				if(!$o_id) $GLOBALS['SECRET'] = 1;
				else if($o_id != $my_id) $GLOBALS['SECRET'] = 2;
			}
			else {
				if(!$id) $GLOBALS['SECRET'] = 1;
				else if($id != $my_id) $GLOBALS['SECRET'] = 2;
			}
		}
	}
		
	if($count_comment > 0) {
		$GLOBALS['COMMENT_COUNT'] = $count_comment;
		$tplBo->parse("is_comment");
	}
	return specialStrReplace2($vls);
};

$name_function = function ($vls) {	
	$vls	= specialStrReplace2($vls);
	if(defined('__MANAGERS__')) return $vls;
	if($GLOBALS['board_info']['privacy_type'] == 1) return mb_substr($vls, 0, 1, 'utf-8')." * ".mb_substr($vls, 2, mb_strlen($vls, 'utf-8'), 'utf-8');
	else return $vls;
};

$signdate_function = function ($vls, $notice, $secret) {	
	global $tplBo;

	if(!$vls) return "-";

	if($notice == 0) $tplBo->parse("is_notice");
	if($secret == 1) $tplBo->parse("is_secret");
	
	if($vls > (time() - ($GLOBALS['board_info']['new_icon'] * 3600))) $tplBo->parse("is_new_icon");
	if($vls > strtotime(date("Y-m-d"))) return date("H:i:s", $vls);
	else return date("Y-m-d", $vls);
};

$dels_function = function ($vls) {
	global $tplBo;
	
	if($vls == 1) {
		$tplBo->parse("is_delete_article");
		$GLOBALS['COUNT'] = $GLOBALS['SIGNDATE'] = "";
	}
	return;
};


$uid_function = function ($vls, $files, $subject) {	
	global $tplBo, $board_info, $b_id;	
	
	if($board_info['types'] != '3')	return $vls;

	$GLOBALS['SUBJECT2'] = stripslashes($subject);
	if(!$files) $tplBo->parse("is_text");
	else {	
		$attach_array		= explode("|", $files);
		
		if(!trim($attach_array[0])) $tplBo->parse("is_text");
		else {
			if(defined('__MANAGERS__'))	$GLOBALS['IMAGE']	= "../../board/data/{$b_id}/{$vls}/".$attach_array[0];
			else						$GLOBALS['IMAGE']	= DEFAULT_PATH."board/data/{$b_id}/{$vls}/".$attach_array[0];
			$times				= @filectime($GLOBALS['IMAGE']);
			$GLOBALS['IMAGE']	.= "?v=".$times;
			$tplBo->parse("is_image");
		}
	}
	return $vls;
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
	$tplBo->parse("is_paging_type_".PAGING_TYPE);
}
else $tplBo->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tplBo->parse("is_list_area");

if($reset == 1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tplBo->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$ATOTAL		= number_format($ATOTAL);
$TOTAL		= number_format($TOTAL);

################## 버튼 권한 ##################
if($my_level == 100) {
	$tplBo->parse("is_write");
	@$tplBo->parse("is_admins");
}
else {
	if($board_info['access_write'] > 0) {
		if($board_info['access_write'] == 2) {
			if($my_id) $tplBo->parse("is_write");
		}
		else if($board_info['access_write'] == 3) {
			$access_level	= explode(",", $board_info['access_write_level']);
			if($my_id && in_array($my_level, $access_level)) $tplBo->parse("is_write");
		}
		else if($board_info['access_write'] == 4) {
			if($v_my_id) $tplBo->parse("is_write");
		}
	}
	else $tplBo->parse("is_write");
}
################## 버튼 권한 ##################

?>