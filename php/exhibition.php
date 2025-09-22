<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER', '../../image/icon');
include_once(PATH_LIB.'/class.ListPaging.php');

if(!$uid = checkGetVar('uid')) Error('필수 정보가 제대로 넘어오지 못했습니다.');	

$sql	= "SELECT * FROM mallRN_exhibition WHERE uid = '{$uid}'";
$data	= $mysql->one_row($sql);

if(!$data) alert("해당 모음전이 종료되었거나 존재하지 않습니다.", "back");

$TITLE	= stripslashes($data['name']);
if($data['discount_yn'] == 'Y' && $data['status'] == 3) alert("{$TITLE} 모음전이 종료 되었습니다.", "back");

if($data['detail_image_only'] == 0) {
	$EXPLAINS = add_escape_re_string($data['explains']);
}
else {
	$EXPLAINS = detailImageToTag2($data['uid'], $data['detail_image_type'], $data['detail_image']);
}

$cate_info = explode("|*|", $data['cate_info']);
$cate_max_num = $cate_info[0];
if($cate_max_num > 100) {
	
	$goods_field = array();
	foreach($default_goods_field as $k => $v) {
		$goods_field[] = "b.{$v}";
	}
	$goods_field = join(", ", $goods_field);
	
	$cate_array = array();
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);
		$cate_array[$cate_info2[0]] = $cate_info2[1];
	}

	foreach($cate_array as $k => $v) {

		foreach($cate_array as $cate_num => $cate_name) {
			if($cate_num == $k) $cate_selected = "selected";
			else				$cate_selected = "";
			$tpl->parse("loop_cate2");
		}
		
		$sql = "SELECT {$goods_field} FROM mallRN_exhibition_goods a, mallRN_goods b WHERE a.guid = b.uid && a.euid = '{$uid}' && a.ecate = '{$k}' && b.display_use = 1 && b.auth_ck = 'Y' && b.cate_hide = 0 ORDER BY a.sequence ASC, b.uid DESC";		
		$mysql->query($sql);
	
		while($row = $mysql->fetch_array()){
			getGoodsInfo($row, "goods");		
		}	

		$tpl->parse("loop_cate");
	}
	unset($cate_info, $cate_info2, $cate_name, $cate_num);
	$tpl->parse("is_cate");
}
else {

	######################## 변수 정의 #############################
	if(!isset($_GET['sort']))	$_GET['sort']	= "best";
	if(!isset($_GET['limit']))  $_GET['limit']	= "12";	
	$goods_field = array();
	foreach($default_goods_field as $k => $v) {
		$goods_field[] = "d.{$v}";
	}
	$goods_field = join(", ", $goods_field);
	######################## 변수 정의 #############################

	######################## listPaging 정의 #############################
	$listPaging						= new listPaging('mallRN_goods'); 
	$listPaging->search_variable	= array('sort' => 0, 'limit' => 0, 'page' => 0, 'total_record' => 0);
	$listPaging->list_variable		= array('uid' => '',  'image2' => 'function|moddate', 'name_code_able' => '', 'name' => '', 'icon' => 'icon', 'price' => 'goods_price', 'orig_price' => 'number', 'consumer_price' => 'number', 'qty_type' => 'function|qty|sale_use|option_use|price|option_soldout');
	$listPaging->defaultParam		= "channel={$channel}&uid={$uid}";
	######################## listPaging 정의 #############################

	######################## list_variable : funtion일 경우 정의 #############################
	$image2_function = function ($vls, $moddate) {
		return $vls."?t=".$moddate;
	};

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

	######################## JOIN 사용시 함수 정의 #############################
	$listPaging->cquery = "cquery";
	$cqueryTotal = function ($where) {	
		return "SELECT COUNT(*) FROM mallRN_goods a WHERE a.uid IN ( SELECT guid FROM mallRN_exhibition_goods WHERE euid = '{$GLOBALS['uid']}' ) && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0";
	};

	$cqueryPrint = function ($where, $start_record, $page_record_num) {
		if($where) $where = "WHERE ".substr($where, 3);

		return "SELECT {$GLOBALS['goods_field']} FROM ( SELECT guid, sequence FROM mallRN_exhibition_goods WHERE euid = '{$GLOBALS['uid']}' ) b JOIN ( SELECT a.uid FROM mallRN_goods a WHERE a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 ) c ON b.guid=c.uid JOIN mallRN_goods d ON c.uid=d.uid ORDER BY b.sequence ASC, d.uid DESC LIMIT {$start_record},{$page_record_num}";		
	};	
	######################## JOIN 사용시 함수 정의 #############################

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

	$tpl->parse("is_no_cate");
}
?>