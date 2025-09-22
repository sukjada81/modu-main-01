<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER', '../../image/icon');
include_once(PATH_LIB.'/class.ListPaging.php');

$vendor					= checkGetVar('vendor');
$cate					= checkGetVar('cate');

if(!$vendor) alert("필수 정보가 넘어오지 못했습니다.", "back");

if($reset == 0) {	
	$sql			= "SELECT * FROM mallRN_vendor WHERE id = '{$vendor}'";
	$vinfo			= $mysql->one_row($sql);

	if(!$vinfo) alert("해당 입점사가 삭제 되었거나 존재하지 않습니다.", "back");

	$COMP_NAME		= stripslashes($vinfo['comp_name']);
	$COMP_OWNER		= stripslashes($vinfo['comp_owner']);
	$COMP_FAX		= stripslashes($vinfo['comp_fax']);
	$COMP_TEL		= stripslashes($vinfo['comp_tel']);
	$COMP_EMAIL		= stripslashes($vinfo['comp_email']);
	$COMP_NUM		= stripslashes($vinfo['comp_license_no']);
	$COMP_ADDR		= stripslashes($vinfo['comp_address1']. " ".$vinfo['comp_address2']);

	$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$vendor}'";
	$cinfo			= $mysql->one_row($sql);

	$STORE_NAME		= ($cinfo['basic_name'])	 ? stripslashes($cinfo['basic_name']) : $COMP_NAME;
	$CS_TIME1		= ($cinfo['basic_cs_time1']) ? stripslashes($cinfo['basic_cs_time1']) : "09:00 ~ 18:00";
	$CS_TIME2		= ($cinfo['basic_cs_time2']) ? stripslashes($cinfo['basic_cs_time2']) : "휴무";
	$CS_TIME3		= ($cinfo['basic_cs_time3']) ? stripslashes($cinfo['basic_cs_time3']) : "휴무";
	$CS_TIME4		= ($cinfo['basic_cs_time4']) ? stripslashes($cinfo['basic_cs_time4']) : "12:00 ~ 13:00";
	$CO_RTN_ADDR	= stripslashes($cinfo['comp_rtn_address1'])." ".stripslashes($cinfo['comp_rtn_address2']);

	$sql			= "SELECT count(*) FROM mallRN_favorite_store WHERE vendor = '{$vendor}'";
	$FSTORE_CNT		= number_format($mysql->get_one($sql));

	$favStoreSelect = 0;
	if($my_id) {
		$sql = "SELECT count(*) FROM mallRN_favorite_store WHERE id = '{$my_id}' && vendor = '{$vendor}'";
		if($mysql->get_one($sql) > 0) $favStoreSelect = 1;
	}	
	
	$total_stars1	= 0;
	$total_stars2	= 0;
	for($i = 1; $i < 6; $i ++) {
		$sql			= "SELECT count(*) FROM mallRN_review WHERE vendor = '{$vendor}' && stars = '{$i}'";
		$cnt			= $mysql->get_one($sql);
		${"STARS".$i}	= number_format($cnt);

		$total_stars1	+= $cnt;
		$total_stars2	+= ($cnt * $i);
	}

	for($i = 1; $i < 6; $i ++) {
		$cnt			= str_replace(",", "", ${"STARS".$i});
		if($total_stars1 == 0)	${"WIDTH".$i} = 0;
		else 					${"WIDTH".$i} = (100 * $cnt) / $total_stars1;
	}

	if($total_stars1 > 0) {
		$STARSALL		= number_format($total_stars2 / $total_stars1, 1);
		$STARSPER		= $STARSALL * 20;
	}
	else {
		$STARSALL		= "0.0";
		$STARSPER		= 0;
	}

	unset($total_stars1, $total_stars2);

	$main_display		= $cinfo['design_main_display_order'] ? $cinfo['design_main_display_order'] : 'reco, code, best, new';
	$main_display_arr	= explode(",", $main_display);
	$display_check_arr  = array('reco' => 2, 'best' => 1, 'new' => 3, 'code' => 0); 

	$goods_field = array();
	foreach($default_goods_field as $k => $v) {
		$goods_field[] = "{$v}";
	}
	$goods_field = join(", ", $goods_field);

	foreach($main_display_arr as $k => $v) {
		$v = trim($v);

		if($display_check_arr[$v] > 0) {
			$i = $display_check_arr[$v];
			if($cinfo['design_main_display'.$i] == 0) continue;
			
			$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE vendor = '{$vendor}' && display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && store_display{$i} = 1 ORDER BY store_display{$i}_sequence ASC";
			$mysql->query($sql);

			$ck = 0;
			while($row = $mysql->fetch_array()){
				getGoodsInfo($row, "goods_item");
				$ck++;
			}	
			
			if($cinfo['design_main_display'.$i] == 3)	$tpl->parse("is_display_goods_type1_group");
			if($cinfo['design_main_display'.$i] > 1)	$tpl->parse("is_display_goods_type1");
			else										$tpl->parse("is_display_goods_type0");

			if($ck > 0) $tpl->parse("is_display_goods_item");	
			
			$tpl->parse("loop_display_goods");
		}		
		else if($v == 'code') {
			if($cinfo['design_main_custom_code'] == 0) continue;

			$CUSTOM_CODE = stripslashes($cinfo['design_main_custom_code_info']);
			$tpl->parse("is_custom_code");
			$tpl->parse("loop_display_goods");
		}
	}
}

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "best";
if(!isset($_GET['limit']))  $_GET['limit']	= "12";

$keyword		= isset($_POST['keyword'])		?  urldecode(trim($_POST['keyword']))		:  ((isset($_GET['keyword']))		?  urldecode(trim($_GET['keyword']))		: '');
$orig_keyword	= isset($_POST['orig_keyword']) ?  urldecode(trim($_POST['orig_keyword']))	:  ((isset($_GET['orig_keyword']))	?  urldecode(trim($_GET['orig_keyword']))	: '');
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
$listPaging->search_variable	= array('vendor' => 1, 'field' => 0, 'keyword' => 0, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'cate' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '',  'image2' => 'function|moddate', 'name_code_able' => '', 'name' => '', 'icon' => 'icon', 'price' => 'goods_price', 'consumer_price' => 'number', 'qty_type' => 'function|qty|sale_use|option_use|price|option_soldout');
$listPaging->defaultParam		= "channel={$channel}&vendor={$vendor}";
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
	
	return "&& SUBSTRING(a.cate, 1, {$i}) = '".substr($vls, 0, $i)."' ";	
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## JOIN 사용시 함수 정의 #############################
$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {	
	global $shop_config;
	
	$add_where = "";
	if($shop_config['goods_soldout'] == 2) $add_where = "&& !((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1)  || a.sale_use = 0)";

	return "SELECT COUNT(*) FROM mallRN_goods a WHERE a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 {$add_where} {$where}";
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	global $shop_config;
	
	$add_where	= "";
	$add_field	= "";
	$add_sort	= "";
	if($shop_config['goods_soldout'] == 2) $add_where = "&& !((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1)  || a.sale_use = 0)";
	else if($shop_config['goods_soldout'] == 1) {
		$add_field	= "IF((c.qty_type = 0 && c.qty = 0 && c.option_use = 0) || (c.option_soldout = 2 && c.option_use = 1) || c.sale_use = 0, 1, 0) as sold_out, ";
		$add_sort	= "sold_out ASC, ";
	}

	if($GLOBALS['sort'] == 'best')	$tmp_sort = "c.order_priority ASC, c.order_cnt DESC, c.view_cnt DESC";
	else							$tmp_sort = "c.{$GLOBALS['sort']}";			

	return "SELECT {$add_field} {$GLOBALS['goods_field']} FROM ( SELECT a.uid FROM mallRN_goods a WHERE a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0  && a.vendor_hide = 0 {$add_where} {$where} ) b JOIN mallRN_goods c ON b.uid = c.uid ORDER BY {$add_sort} {$tmp_sort} LIMIT {$start_record}, {$page_record_num}";		
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

$sql = "SELECT a.cate, a.cate_name, a.cate_dep, a.cate_sub, a.access_type, a.access_level, SUM(b.cnt) as cnts FROM ( SELECT cate, count(*) as cnt FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0  && vendor_hide = 0  && vendor = '{$vendor}' GROUP BY cate) b JOIN mallRN_cate a ON SUBSTRING(b.cate, 1, 3) = SUBSTRING(a.cate, 1, 3) WHERE a.cate_dep = '1' && a.used = '1' GROUP BY cate ORDER BY sequence ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	if($my_level < 100 && checkCateAccessThis($row['access_type'], $row['access_level'])) continue;

	$CATE_CNT	= $row['cnts'];
	if($CATE_CNT == 0) continue;
	$CATE		= $row['cate'];		
	$CATE_NAME	= stripslashes($row['cate_name']);	
	
	if($row['cate_sub'] == 1) {
		$sql = "SELECT a.cate, a.cate_name, a.access_type, a.access_level, SUM(b.cnt) as cnts FROM ( SELECT cate, count(*) as cnt FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0  && vendor_hide = 0  && vendor = '{$vendor}' && SUBSTRING(cate, 1, 3) = '".substr($row['cate'], 0, 3)."' GROUP BY cate) b JOIN mallRN_cate a ON SUBSTRING(b.cate, 1, 6) = SUBSTRING(a.cate, 1, 6) WHERE a.cate_parent = '{$row['cate']}' && a.used = '1' GROUP BY cate ORDER BY sequence ASC";
		$mysql->query2($sql);

		while($row2 = $mysql->fetch_array('2')) {
			if($my_level < 100 && checkCateAccessThis($row2['access_type'], $row2['access_level'])) continue;

			$SUB_CATE_CNT = $row2['cnts'];			
			if($SUB_CATE_CNT == 0) continue;
			$SUB_CATE		= $row2['cate'];	               
			$SUB_CATE_NAME	= stripslashes($row2['cate_name']);
			$tpl->parse("loop_sub_cate");			
		}
		$tpl->parse("is_sub_cate");
	}
	$tpl->parse("loop_cate");
}
unset($row2, $CATE, $CATE_NAME, $CATE_CNT, $SUB_CATE, $SUB_CATE_NAME, $SUB_CATE_CNT);
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
	$split_keyword_ek = ekKeyTypeConvert($orig_keyword);

	if($split_keyword_ek != $orig_keyword) {
		$sql = "SELECT c.keyword FROM ( SELECT a.uid FROM mallRN_keyword_autocomplete a WHERE ( a.split_keyword = '{$split_keyword_ek}' || a.split_keyword_ek = '{$split_keyword_ek}') ) b JOIN mallRN_keyword_autocomplete c ON b.uid = c.uid";
		$re_keyword = $mysql->get_one($sql);
		
		if($re_keyword) {
			$sql = "SELECT COUNT(*) FROM mallRN_goods a WHERE (INSTR(REPLACE(a.name, ' ', ''), '{$re_keyword}') || INSTR(a.keyword, ',{$re_keyword},'))";
			if($mysql->get_one($sql) > 0) {
				movePage("{$PHP_SELF}?channel=search&keyword={$re_keyword}");
			}
		}
	}

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

if(!$my_id) $tpl->parse("is_login");

?>