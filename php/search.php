<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER', '../../image/icon');
include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "best";
if(!isset($_GET['limit']))  $_GET['limit']	= "12";

$def_keyword	= $keyword;
$keyword		= str_replace(" ", "|*|", $keyword);
$orig_keyword	= isset($_POST['orig_keyword']) ?  urldecode(trim($_POST['orig_keyword']))	:  ((isset($_GET['orig_keyword'])) ?  urldecode(trim($_GET['orig_keyword']))	: '');
if($orig_keyword)	{
	$keyword_arr	= explode("|*|", $orig_keyword);
	foreach($keyword_arr as $k => $v) {
		$tpl->parse("loop_keyword_list");
	}
	
	$orig_keyword .= '|*|'.$keyword;
}
else				$orig_keyword  = $keyword;
$print_keyword	= str_replace(" ", " + ", $def_keyword);

if(strlen($orig_keyword)) {
	$keyword_arr	= explode("|*|", $orig_keyword);
	foreach($keyword_arr as $k => $v) {
		if($k==0) $k2 = '';
		else $k2 = $k + 1;

		$_GET['keyword'.$k2]	= $v;
		$_GET['field'.$k2]		= "multi";
	}
}

$goods_field = array();
foreach($default_goods_field as $k => $v) {
	$goods_field[] = "c.{$v}";
}
$goods_field = join(", ", $goods_field);
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_goods'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'cate' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '',  'image2' => 'function|moddate', 'name_code_able' => '', 'name' => '', 'icon' => 'icon', 'price' => 'goods_price', 'consumer_price' => 'number', 'qty_type' => 'function|qty|sale_use|option_use|price|option_soldout');
$listPaging->defaultParam		= "channel={$channel}";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("name", "keyword", "uid", "goods_code");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$image2_function = function ($vls, $moddate) {
	return $vls."?t=".$moddate;
};

$GLOBALS['ORIG_PRICE']	= 0;
$GLOBALS['SALE']		= 0;

$qty_type_function = function ($vls, $qty, $sale_use, $option_use, $price, $option_soldout) {
	global $mysql, $tpl, $my_discount;

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
	$vls_arr	= explode("|*|", $vls);
	$return		= array();
	foreach($vls_arr as $k => $v) {		
		for($i = 3; $i < 10; $i = $i + 3) {
			if(substr($v, $i, ($i + 3)) == '000') break;						
		}
		$return[] = "SUBSTRING(a.cate, 1, {$i}) = '".substr($v, 0, $i)."'";
	}
	$return		= join(" || ", $return);

	return "&&  ( {$return} ) ";	
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## JOIN 사용시 함수 정의 #############################
$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {
	global $shop_config, $my_level;

	$add_acc = "";
	if($my_level < 99) {
		if($my_level == 0) $add_acc = "&& b.access_type = 0";
		else $add_acc = "&& (b.access_type = 0 || b.access_type = 1 || (b.access_type = 2 && INSTR(b.access_level, ',{$my_level},')))";		
	}
	
	$add_where = "";
	if($shop_config['goods_soldout'] == 2) $add_where = "&& !((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1)  || a.sale_use = 0)";

	return "SELECT COUNT(*) FROM mallRN_goods a, mallRN_cate b WHERE a.cate = b.cate && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 {$add_where} {$where} {$add_acc}";
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	global $shop_config, $my_level;

	$add_acc = "";
	if($my_level < 99) {
		if($my_level == 0) $add_acc = "&& b.access_type = 0";
		else $add_acc = "&& (b.access_type = 0 || b.access_type = 1 || (b.access_type = 2 && INSTR(b.access_level, ',{$my_level},')))";		
	}
	
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

	return "SELECT {$add_field} {$GLOBALS['goods_field']} FROM ( SELECT a.uid FROM mallRN_goods a, mallRN_cate b WHERE a.cate = b.cate && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0  && a.vendor_hide = 0 {$add_where} {$where} {$add_acc} ) b JOIN mallRN_goods c ON b.uid = c.uid ORDER BY {$add_sort} {$tmp_sort} LIMIT {$start_record}, {$page_record_num}";		
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

$tmp_where = str_replace("a.", "", $listPaging->where);
$sql = "SELECT a.cate, a.cate_name, cate_sub, SUM(b.cnt) as cnts FROM ( SELECT cate, count(*) as cnt FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0  && vendor_hide = 0 {$tmp_where} GROUP BY cate) b JOIN mallRN_cate a ON SUBSTRING(b.cate, 1, 3) = SUBSTRING(a.cate, 1, 3) WHERE a.cate_dep = '1' && a.used = '1' GROUP BY cate ORDER BY sequence ASC";		
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$CATE_CNT = $row['cnts'];
	if($CATE_CNT == 0) continue;
	$CATE		= $row['cate'];		
	$CATE_NAME	= stripslashes($row['cate_name']);	
	
	if($row['cate_sub'] == 1) {		
		$sql = "SELECT a.cate, a.cate_name, SUM(b.cnt) as cnts FROM ( SELECT cate, count(*) as cnt FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0  && vendor_hide = 0 && SUBSTRING(cate, 1, 3) = '".substr($row['cate'], 0, 3)."' {$tmp_where} GROUP BY cate) b JOIN mallRN_cate a ON SUBSTRING(b.cate, 1, 6) = SUBSTRING(a.cate, 1, 6) WHERE a.cate_parent = '{$row['cate']}' && a.used = '1' GROUP BY cate ORDER BY sequence ASC";
		$mysql->query2($sql);

		while($row2 = $mysql->fetch_array('2')) {
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

if($cate) {
	$cate_arr = explode("|*|", $cate);
	foreach($cate_arr as $k => $v) {
		$tpl->parse("loop_cate_list");
	}
	unset($cate_arr);
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
	
	$orig_keyword2		= explode("|*|", $orig_keyword);
	$orig_keyword2		= $orig_keyword2[0];
	$split_keyword_ek	= ekKeyTypeConvert($orig_keyword2);

	if($split_keyword_ek != $orig_keyword2) {
		$sql = "SELECT c.keyword FROM ( SELECT a.uid FROM mallRN_keyword_autocomplete a WHERE ( a.split_keyword = '{$split_keyword_ek}' || a.split_keyword_ek = '{$split_keyword_ek}') ) b JOIN mallRN_keyword_autocomplete c ON b.uid = c.uid";
	
		$re_keyword = $mysql->get_one($sql);
		
		if($re_keyword && $re_keyword != $orig_keyword2) {
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

if($page == 1 && $TOTAL > 0) {
	
	$check_keyword	= isset($_COOKIE['check_keyword']) ? $_COOKIE['check_keyword'] : '';

	if($check_keyword != $def_keyword) {

		SetCookie("check_keyword", $def_keyword, 0, "/");

		$check_date	= date("Y-m-d");
	
		######################## 검색어 저장 #############################
		$sql = "SELECT uid FROM mallRN_keyword_search WHERE keyword = '{$def_keyword}' && date = '{$check_date}'";	
		if($data = $mysql->get_one($sql)) {		
			$sql = "UPDATE mallRN_keyword_search SET count = count + 1 WHERE uid = '{$data}'";			
		}
		else {
			$sql = "INSERT INTO mallRN_keyword_search (keyword, count, date) VALUES('{$def_keyword}', '1', '{$check_date}')";
		}
		$mysql->query($sql);
		######################## 검색어 저장 #############################

		######################## 인기상품 5개 추출 후 자동완성 검색어 등록 #############################	
		$check_keyword  = array();
		$where			= "WHERE ".substr($listPaging->where, 3);		

		$sql = "SELECT c.name, c.keyword FROM ( SELECT a.uid FROM mallRN_goods a {$where} ) b JOIN mallRN_goods c ON b.uid = c.uid ORDER BY c.order_cnt DESC, c.view_cnt DESC LIMIT 5";
		$mysql->query($sql);
		
		while($row = $mysql->fetch_array()){

			if(count($keyword_arr) > 1) {
				
				for($i = count($keyword_arr); $i > 1; $i--) {
					
					$orig_keyword2 = array();
					for($j = 0; $j < $i; $j++) {
						$orig_keyword2[] = $keyword_arr[$j];
					}
					$orig_keyword2 = join(" ", $orig_keyword2);

					if(strlen($orig_keyword2) < 2) continue;

					$split_keyword		= getJamoStr($orig_keyword2);
					$split_keyword_ek	= ekKeyTypeConvert($orig_keyword2);
					
					if(!in_array($orig_keyword2, $check_keyword)) {
						if(preg_match("/".addslashes(preg_quote($orig_keyword2, '/'))."/i", $row['name'])) {
							$sql = "SELECT count(*) FROM mallRN_keyword_autocomplete WHERE keyword = '{$orig_keyword2}'";
							if($mysql->get_one($sql) == 0) {
								$split_keyword		= getJamoStr($orig_keyword2);
								$split_keyword_ek	= ekKeyTypeConvert($orig_keyword2);
								$sql = "INSERT INTO mallRN_keyword_autocomplete (keyword, split_keyword, split_keyword_ek, count, signdate) VALUES('{$orig_keyword2}', '{$split_keyword}', '{$split_keyword_ek}', 1, ".time().")";	
								$check_keyword[] = $orig_keyword2;
							}
							else {
								$sql = "UPDATE mallRN_keyword_autocomplete SET count = count + 1 WHERE keyword = '{$orig_keyword2}'";
								$check_keyword[] = $orig_keyword2;
							}
							$mysql->query2($sql);
						}
					}
				}
			}
			else {
				$orig_keyword2 = $def_keyword;
			}
			
			$name_arr = explode(" ", trim($row['name']));
			if($row['keyword']) $name_arr = array_merge($name_arr, explode(",", substr($row['keyword'], 1, -1)));

			foreach($name_arr as $k => $v) {

				foreach($keyword_arr as $k2 => $v2) {

					if(strlen($v) < 2) continue;

					if(@preg_match("/".addslashes(preg_quote($v2, '/'))."/i", $v)) {
						if(!in_array($v, $check_keyword)) {
							$sql = "SELECT count(*) FROM mallRN_keyword_autocomplete WHERE keyword = '{$v}'";
							if($mysql->get_one($sql) == 0) {
								$split_keyword		= getJamoStr($v);
								$split_keyword_ek	= ekKeyTypeConvert($v);
								$sql = "INSERT INTO mallRN_keyword_autocomplete (keyword, split_keyword, split_keyword_ek, count, signdate) VALUES('{$v}', '{$split_keyword}', '{$split_keyword_ek}', 1, ".time().")";								
								$check_keyword[] = $v;
							}
							else {
								$sql = "UPDATE mallRN_keyword_autocomplete SET count = count + 1 WHERE keyword = '{$v}'";
								$check_keyword[] = $orig_keyword2;
							}
							$mysql->query2($sql);
						}
					}

				}
			}		
		}	
		######################## 인기상품 5개 추출 후 자동완성 검색어 등록 #############################
	}

	unset($where, $k, $v, $k2, $v2, $check_date, $name_arr, $split_keyword, $check_keyword, $orig_keyword2);
}

$TOTAL = number_format($TOTAL);

?>