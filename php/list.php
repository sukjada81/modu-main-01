<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER', '../../image/icon');
include_once(PATH_LIB.'/class.ListPaging.php');

$sql					= "SELECT * FROM mallRN_cate WHERE cate = '{$cate}'";
$cate_info				= $mysql->one_row($sql);

if(!$cate_info) alert("해당 분류가 삭제되었거나 존재하지 않습니다.", "back");

checkCateAccess($cate);

if($reset == 0) {	
	$SEC_CATE_NAME		= stripslashes($cate_info['cate_name']);
	$SEC_CATE_LOCATION	= "";
	if($cate_info['cate_dep'] > 1 || $mobile_header != '') $SEC_CATE_LOCATION	= getCateAllName($cate, 2);

	################ SUB CATEGORY ################
	if(($cate_info['cate_dep']) != 1 && $cate_info['cate_sub'] == 0 || $cate_info['cate_dep'] > 2) {
		if($cate_info['cate_dep'] == 4) {
			$sql = "SELECT * FROM mallRN_cate WHERE cate_parent = '".substr($cate_info['cate_parent'], 0, 6)."000000' && used = '1' ORDER BY sequence ASC";
		}
		else $sql = "SELECT * FROM mallRN_cate WHERE cate_parent = '{$cate_info['cate_parent']}' && used = '1' ORDER BY sequence ASC";
	}
	else {
		$sql = "SELECT * FROM mallRN_cate WHERE cate_parent = '{$cate}' && used = '1' ORDER BY sequence ASC";
	}
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		if($my_level < 100 && checkCateAccessThis($row['access_type'], $row['access_level'])) continue;
		
		$CATE_NAME	= $row['cate_name'];	
		$CATE		= $row['cate'];
		$selected	= '';
		if($row['cate'] == $cate || substr($row['cate'], 0, 9) == substr($cate, 0, 9)) $selected = "selected";

		if($row['cate_sub'] == 1) {
			$sql = "SELECT cate_name, cate, access_type, access_level FROM mallRN_cate WHERE cate_parent = '{$row['cate']}' && used = '1' ORDER BY sequence ASC";
			$mysql->query2($sql);
			
			while($row2 = $mysql->fetch_array(2)){
				if($my_level < 100 && checkCateAccessThis($row2['access_type'], $row2['access_level'])) continue;
				
				$SUB_CATE_NAME	= $row2['cate_name'];	
				$SUB_CATE		= $row2['cate'];
				$selected_sub	= '';
				if($row2['cate'] == $cate) $selected_sub = "selected";

				@$tpl->parse("loop_sub_cate");					
			}

			$tpl->parse("is_sub_cate");
		}	
		$tpl->parse("loop_cate");		
	}
	unset($acc_level, $CATE_NAME, $CATE, $selected, $SUB_CATE_NAME, $SUB_CATE, $selected_sub);
	################ SUB CATEGORY ################

	################ DISPLAY GOODS ################
	if($cate_info['cate_dep'] == 1) {
		$main2_display		= $shop_config['design_main2_display_order'] ? $shop_config['design_main2_display_order'] : 'reco, best, new, group';
		$main2_display_arr	= explode(",", $main2_display);
		$display_check_arr  = array('reco' => 2, 'best' => 1, 'new' => 3, 'group' => 4); 

		$goods_field = array();
		foreach($default_goods_field as $k => $v) {
			$goods_field[] = "{$v}";
		}
		$goods_field = join(", ", $goods_field);

		foreach($main2_display_arr as $k => $v) {
			$v = trim($v);

			$i = $display_check_arr[$v];
			if($shop_config['design_main2_display'.$i] == 0) continue;
			
			$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && main2_display{$i} = 1 && SUBSTRING(cate, 1, 3) = '".substr($cate, 0, 3)."' ORDER BY main2_display{$i}_sequence ASC";
			$mysql->query($sql);

			$ck = 0;
			while($row = $mysql->fetch_array()){
				getGoodsInfo($row, "goods_item");
				$ck++;
			}	
			
			if($shop_config['design_main2_display'.$i] == 3)	$tpl->parse("is_display_goods_type1_group");
			if($shop_config['design_main2_display'.$i] > 1)		$tpl->parse("is_display_goods_type1");
			else												$tpl->parse("is_display_goods_type0");

			if($ck > 0) $tpl->parse("is_display_goods_item");	
			
			$tpl->parse("loop_display_goods");

		}
	}
	################ DISPLAY GOODS ################
}

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "best";
if(!isset($_GET['limit']))  $_GET['limit']	= "12";

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
	$goods_field[] = "a.{$v}";
}
$goods_field = join(", ", $goods_field);
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_goods'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'cate' => 3, 'sort' => 0, 'limit' => 0, 'page' => 0, 'total_record' => 0);
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
$cate_where = function ($vls) {
	for($i = 3; $i < 10; $i = $i + 3) {
		if(substr($vls, $i, ($i + 3)) == '000') break;						
	}
	
	return "SUBSTRING(cate, 1, {$i}) = '".substr($vls, 0, $i)."' ";	
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## JOIN 사용시 함수 정의 #############################
$listPaging->squery = "squery";
$squeryTotal = function ($swhere, $where) {
	global $shop_config;
	
	$add_where = "";
	if($shop_config['goods_soldout'] == 2) $add_where = "&& !((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1)  || a.sale_use = 0)";

	return "SELECT COUNT(*) FROM mallRN_goods a WHERE a.uid IN ( SELECT DISTINCT(guid) FROM mallRN_goods_cate WHERE {$swhere} ) && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 {$add_where} {$where}";
};

$squeryPrint = function ($swhere, $where, $start_record, $page_record_num) {
	global $shop_config;
	
	$add_where	= "";
	$add_field	= "";
	$add_sort	= "";
	if($shop_config['goods_soldout'] == 2) $add_where = "&& !((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1)  || a.sale_use = 0)";
	else if($shop_config['goods_soldout'] == 1) {
		$add_field	= "IF((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1)  || a.sale_use = 0, 1, 0) as sold_out, ";
		$add_sort	= "sold_out ASC, ";
	}

	if($GLOBALS['sort'] == 'best')	$tmp_sort = "a.order_priority ASC, b.sqc ASC, a.order_cnt DESC, a.view_cnt DESC";
	else							$tmp_sort = "a.{$GLOBALS['sort']}";

	return "SELECT {$add_field} {$GLOBALS['goods_field']} FROM ( SELECT DISTINCT(guid), sequence{$GLOBALS['cate_info']['cate_dep']} as sqc FROM mallRN_goods_cate WHERE {$swhere} ) b JOIN mallRN_goods a ON b.guid = a.uid WHERE a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 {$add_where} {$where} ORDER BY {$add_sort} {$tmp_sort} LIMIT {$start_record}, {$page_record_num}";		
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
	$tpl->parse("empty_list2");
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