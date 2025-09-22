<?php 

$reset = isset($_POST['reset']) ? $_POST['reset'] : '';

if($reset==1) {
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");

	include_once('../common/ad_init.php');
	include_once(PATH_LIB.'/class.Template.php');  

	$mysql->msgType(2);

	$my_array = array();
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
}
else include_once("../common/top.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_modify_list.html");
$tpl->scan_area("main");

######################## 분류 정보 #############################
$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
$mysql->query($sql);

while($row=$mysql->fetch_array()){    
	$cate		= specialStrReplace($row['cate']);
	$cate_name	= specialStrReplace($row['cate_name']);
	$tpl->parse("loop_cate1");
}
unset($cate_name);
######################## 분류 정보 #############################

######################## 변수 정의 #############################
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(!isset($_GET['field2'])) $_GET['field2'] = "multi";
if(!isset($_GET['field3'])) $_GET['field3'] = "multi";
if(!isset($_GET['field4'])) $_GET['field4'] = "multi";
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}

$DATE1 = date("Y-m-d");
$DATE2 = date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3 = date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4 = date('Y-m-d', strtotime('-1 MONTH', time()));
$DATE5 = date('Y-m-d', strtotime('-3 MONTH', time()));
$DATE6 = date('Y-m-d', strtotime('-6 MONTH', time()));
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_goods'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'cate' => 3, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'sort' => 0, 'limit' => 0, 'page' => 0, 'display_use' => 1, 'sale_use' => 1, 'option_use' => 1, 'delivery_type' => 1, 'commission_type' => 1, 'qty_type' => 1, 'limit_qty' => 2, 'cate_hide' => 1, 'soldout' => 2, 'option_soldout' => 1, 'commission_type' => 1, 'information_use' => 1, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'image3' => 'function|moddate', 'name' => '', 'cate' => 'cate', 'price' => 'goods_price', 'orig_price' => 'number', 'commission' => 'float', 'commission_type' => '', 'consumer_price' => 'number', 'display_use' => 'array', 'sale_use' => 'array', 'qty_type' => 'function|qty|uid|option_use|name');
$listPaging->default_where = "vendor = '{$v_my_id}'";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("name","uid","model","keyword","goods_code");
$display_use_array				= array("0"=>"<i class='fas fa-times'></i>","1"=>"<i class='far fa-circle'></i>"); 
$sale_use_array					= array("0"=>"<i class='fas fa-times'></i>","1"=>"<i class='far fa-circle'></i>"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$image3_function = function ($vls, $moddate) {
	return $vls."?t=".$moddate;
};

$qty_type_function = function ($vls, $qty, $uid, $option_use) {	
	global $mysql, $tpl;
	
	if($option_use==1) {
		$sql = "SELECT count(*) as cnt, sum(qty) as sum FROM mallRN_goods_option WHERE guid='{$uid}' && qty_type=0";
		$data = $mysql->one_row($sql);
		if($data['cnt']>0) $GLOBALS['QTYS'] = number_format($data['sum']);
		$GLOBALS['QTYS'] = "무제한";
		$tpl->parse("is_qty_type1");
	}
	else {
		$GLOBALS['QTY'] = number_format($qty);
		$tpl->parse("is_qty_type2");
	}
	return $vls;
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$cate_where = function ($vls) {
	for($i = 3; $i < 10; $i = $i + 3) {
		if(substr($vls, $i, ($i + 3)) == '000') break;						
	}
	
	return "SUBSTRING(cate, 1, {$i}) = '".substr($vls, 0, $i)."' ";	
};

$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59'";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59'";
};


$limit_qty_where = function ($vls) {
	return "&& a.limit_qty > '0' ";
};

$soldout_where = function ($vls) {
	return "&& a.qty_type = 0 && a.qty = 0 ";	
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## JOIN 사용시 함수 정의 #############################
$listPaging->squery = "squery";
$squeryTotal = function ($swhere, $where) {
	return "SELECT COUNT(*) FROM mallRN_goods a WHERE a.uid IN ( SELECT guid FROM mallRN_goods_cate WHERE {$swhere} ) {$where}";
};

$squeryPrint = function ($swhere, $where, $start_record, $page_record_num) {
	return "SELECT c.* FROM ( SELECT a.uid FROM mallRN_goods a WHERE a.uid IN ( SELECT guid FROM mallRN_goods_cate WHERE {$swhere} ) {$where} ) b JOIN mallRN_goods c ON b.uid=c.uid ORDER BY c.{$GLOBALS['sort']} LIMIT {$start_record}, {$page_record_num}";
};
######################## JOIN 사용시 함수 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($atotal_record) $listPaging->atotal_record = $atotal_record;
if($total_record) $listPaging->total_record = $total_record;
$listPaging->total_all_record();

${"checked_limit_qty_".$limit_qty}				= "checked='checked'";
${"checked_cate_hide_".$cate_hide}				= "checked='checked'";
${"checked_soldout_".$soldout}					= "checked='checked'";
${"checked_option_soldout_".$option_soldout}	= "checked='checked'";
${"checked_information_use_".$information_use}	= "checked='checked'";
######################## listPaging 파라미터 및 검색조건 처리 #############################

######################## 검색 조건 있는 경우 #############################
$cate1 = $cate2 = $cate3 = $cate4 = '';

if($listPaging->check_where == 1) {	
	$tpl->parse("is_search");
}
else if($listPaging->check_where == 2) {
	if($cate) {
		for($i = 3, $j = 1; $i < 10; $i = $i + 3) {
			if(substr($cate, ($i - 3), $i) != '000') {
				${"cate".$j} = substr($cate, 0, $i).sprintf("%0".(12-$i)."d", 0);						
			}			
			$j++;					
		}
	}

	$tpl->parse("is_search");
	$tpl->parse("is_search_open");
}
######################## 검색 조건 있는 경우 #############################

$sql = "SELECT goods_price_limit1, goods_price_limit2 FROM mallRN_configuration WHERE uid=1";
$multi_data = $mysql->one_row($sql);

$goods_price_limit1 = $multi_data['goods_price_limit1'];
$goods_price_limit2 = $multi_data['goods_price_limit2'];
unset($multi_data);

######################## 리스트 출력 및 페이징 처리 #############################
$PAGING_TYPE	= PAGING_TYPE;
if($listPaging->total_record > 0) {
	$listPaging->re_uid = 1;	
	$listPaging->print_record();
	if(PAGING_TYPE==1) $PAGING = "";
	else $PAGING = $listPaging->print_page();	
	//$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset==1) {
	$my_array[] = ["listHtml"=>$tpl->tprint("is_list_area",1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
}

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>