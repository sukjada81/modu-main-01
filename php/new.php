<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER', '../../image/icon');
include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "re_uid ASC";
if(!isset($_GET['limit']))  $_GET['limit']	= "12";

$_GET['main1_display3'] = 1;

$keyword= isset($_POST['keyword'])?				urldecode(trim($_POST['keyword']))		:  ((isset($_GET['keyword']))		?  urldecode(trim($_GET['keyword']))		: '');
$orig_keyword= isset($_POST['orig_keyword']) ?  urldecode(trim($_POST['orig_keyword']))	:  ((isset($_GET['orig_keyword']))	?  urldecode(trim($_GET['orig_keyword']))	: '');

if(strlen($keyword)) {
	if($orig_keyword)	$orig_keyword .= '|*|'.$keyword;
	else				$orig_keyword  = $keyword;
}
if(strlen($orig_keyword)) {
	$keyword_arr	= explode("|*|", $orig_keyword);
	foreach($keyword_arr as $k => $v) {
		if($k==0) $k2 = '';
		else $k2 = $k + 1;

		$_GET['keyword'.$k2]	= $v;
		$_GET['field'.$k2]		= "multi";	

		$tpl->parse("loop_keyword_list");
	}	
	unset($keyword_arr);
}

$goods_field = array();
foreach($default_goods_field as $k => $v) {
	$goods_field[] = "c.{$v}";
}
$goods_field = join(", ", $goods_field);
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_goods'); 
$listPaging->search_variable	= array('main1_display3' => 2, 'field' => 0, 'keyword' => 0, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'sort' => 0, 'limit' => 0, 'page' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '',  'image2' => 'function|moddate', 'name_code_able' => '', 'name' => '', 'icon' => 'icon', 'price' => 'goods_price', 'consumer_price' => 'number', 'qty_type' => 'function|qty|sale_use|option_use|price|option_soldout');
$listPaging->defaultParam		= "channel={$channel}";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("name", "keyword");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$image2_function = function ($vls, $moddate) {
	return $vls."?t=".$moddate;
};

$GLOBALS['ORIG_PRICE']	= 0;
$GLOBALS['SALE']		= 0;

$qty_type_function = function ($vls, $qty, $sale_use, $option_use, $price, $option_soldout) {
	global $tpl, $my_discount;

	$sold_out			= 0;
	$orig_price			= 0;
	$GLOBALS['SALE']	= 0;

	if($sale_use == 0) $sold_out = 1;
	else if($option_use == 1) {
		if($option_soldout == 2) $sold_out = 1;
	}
	else if($vls == 0 && $qty < 1) $sold_out = 1;
	
	if($sold_out == 1) $tpl->parse("is_list_soldout");

	if($GLOBALS['COUPON_PRICE'] > 0) {
		$orig_price = 1;
		$tpl->parse("is_list_coupon");
	}

	if($GLOBALS['EVENT_DISCOUNT'] > 0) {		
		$GLOBALS['SALE']	= $GLOBALS['EVENT_DISCOUNT'];		
	}

	if($my_discount > 0) {
		$GLOBALS['SALE']	+= $my_discount;
	}

	if($GLOBALS['SALE'] > 0) {
		$orig_price			= 1;
		$tpl->parse("is_list_sale");
	}

	if($orig_price == 1) {
		$GLOBALS['ORIG_PRICE']	= number_format($price, CONF_FLOAT_CNT);
		$tpl->parse("is_list_orig_price");
	}	
	
	return;

};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$main1_display3_where = function ($vls) {
	return "&& (main1_display3 = 1 || main2_display3 = 1) && display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 ";
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