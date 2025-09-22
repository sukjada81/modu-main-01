<?php 

define('ICON_FOLDER', '../../image/icon');

$reset = isset($_POST['reset']) ? $_POST['reset'] : '';

if($reset == 1) {
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
$tpl->define("main","goods_list.html");
$tpl->scan_area("main");

######################## 분류 정보 #############################
$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
$mysql->query($sql);

while($row=$mysql->fetch_array()){    
	$cate		= specialStrReplace($row['cate']);
	$cate_name	= specialStrReplace($row['cate_name']);
	$tpl->parse("loop_cate1");
}
unset($cate, $cate_name);
######################## 분류 정보 #############################

######################## 판매사 정보 #############################
$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
$mysql->query($sql);

$vendor_array = array();
while($row = $mysql->fetch_array()){
	$vendor_id					= specialStrReplace($row['id']);
	$vendor_name				= specialStrReplace($row['comp_name']);
	$vendor_array[$vendor_id]	= $vendor_name;
	$tpl->parse("loop_vendor");
}
unset($vendor_id, $vendor_name);
######################## 판매사 정보 #############################

######################## 변수 정의 #############################
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(!isset($_GET['field2'])) $_GET['field2'] = "multi";
if(!isset($_GET['field3'])) $_GET['field3'] = "multi";
if(!isset($_GET['field4'])) $_GET['field4'] = "multi";
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}
if(isset($_GET['sort'])) $_GET['sort'] = "re_uid ASC";

$DATE1 = date("Y-m-d");
$DATE2 = date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3 = date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4 = date('Y-m-d', strtotime('-1 MONTH', time()));
$DATE5 = date('Y-m-d', strtotime('-3 MONTH', time()));
$DATE6 = date('Y-m-d', strtotime('-6 MONTH', time()));
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_goods'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'cate' => 3, 'date_type' => 5, 's_date' => 5 , 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'sort' => 0, 'limit' => 0, 'page' => 0, 'display_use' => 1, 'sale_use' => 1, 'option_use' => 1, 'mileage_type' => 1, 'delivery_type' => 1, 'engine_use' => 1, 'order_priority' => 1, 'qty_type' => 1, 'vendor' => 1, 'limit_qty' => 2, 'cate_hide' => 1, 'soldout' => 2, 'option_soldout' => 1, 'commission_type' => 1, 'information_use' => 1, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'image3' => 'function|moddate', 'name' => '', 'icon' => 'icon', 'main1_display1' => 'function|main1_display2|main1_display3|main2_display1|main2_display2|main2_display3', 'cate' => 'function|uid', 'vendor' => 'vendor', 'price' => 'goods_price', 'orig_price' => 'number', 'commission_type' => 'function', 'commission' => 'float', 'consumer_price' => 'number', 'display_use' => 'function|cate_hide', 'sale_use' => 'array', 'qty_type' => 'function|qty|uid|option_use|name', 'option_use' => 'function|uid|name', 'mileage_type' => 'array', 'delivery_type' => 'array', 'goods_code' => '', 'signdate' => 'date', 'moddate' => 'date');
######################## listPaging 정의 #############################

######################## 노출항목 설정 처리 #############################
$listPaging->type = 1;

$field_title_arr = array('no' => '번호', 'uid' => '상품번호', 'image' => '이미지 <i class="xi-external-link"></i>', 'name' => '상품명 <i class="xi-link"></i> / 대표분류 <i class="xi-link"></i>', 'vendor' => '판매사 <i class="xi-focus-frame"></i>', 'price' => '판매가', 'orig_price' => '공급가', 'commission' => '수수료(마진)율', 'consumer_price' => '소비자가', 'display_use' => '진열상태', 'sale_use' => '판매상태', 'qty' => '재고', 'option_use' => '옵션 <i class="xi-link"></i>', 'mileage_type' => '마일리지', 'delivery_type' => '배송비', 'goods_code' => '자체상품코드', 'signdate' => '등록일/수정일');
$field_width_arr = array('no' => '80', 'uid' => '80', 'image' => '100', 'name' => '420', 'vendor' => '140', 'price' => '110', 'orig_price' => '110', 'commission' => '120', 'consumer_price' => '110', 'display_use' => '80', 'sale_use' => '80', 'qty' => '80', 'option_use' => '100', 'mileage_type' => '120', 'delivery_type' => '120', 'goods_code' => '120', 'signdate' => '120');

$sql		= "SELECT fields FROM mallRN_list_show_config WHERE vendor = '' && name='goods'";
$data		= $mysql->get_one($sql);
if(!$data) {
	$data = "no|1|*|uid|1|*|image|1|*|name|1|*|vendor|0|*|price|1|*|orig_price|0|*|commission|0|*|consumer_price|0|*|option_use|0|*|display_use|1|*|sale_use|1|*|qty|1|*|mileage_type|0|*|delivery_type|0|*|goods_code|0|*|signdate|1";
}
$conf_data	= explode("|*|", $data);

$filed_able_arr = array();
foreach ($conf_data as $k => $v) {
	$v2 = explode("|",$v);
	$name		= $v2[0];
	$checked	= $v2[1];

	if($checked==1) {
		$list_title	=  $field_title_arr[$name];
		$list_width	=  $field_width_arr[$name];

		$tpl->parse("loop_list_title");
		$tpl->parse("loop_list_width");

		$filed_able_arr[] = $name;
	}	
}
$listPaging->list_show_variable = $filed_able_arr;
$list_cnt = count($filed_able_arr)+2;
unset($conf_data, $k, $v, $v2, $name, $checked, $list_title, $list_width, $filed_able_arr);
######################## 노출항목 설정 처리 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("name","uid","model","keyword","goods_code");
$display_use_array				= array("0"=>"진열안함","1"=>"진열함"); 
$sale_use_array					= array("0"=>"판매안함","1"=>"판매함"); 
$mileage_type_array				= array("1"=>"환경설정 사용","2"=>"없음","3"=>"별도설정(회원등급별)","4"=>"별도설정(회원공통)");
$delivery_type_array			= array("1"=>"환경설정 사용","2"=>"무료배송","3"=>"착불","4"=>"별도책정(고정)","5"=>"별도책정(개당)");
$engine_use_array				= array("0"=>"사용안함","1"=>"사용안함");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$image3_function = function ($vls, $moddate) {
	return $vls."?t=".$moddate;
};

$qty_type_function = function ($vls, $qty, $uid, $option_use, $gname) {	
	global $mysql;
	
	if($option_use==1) {
		$sql = "SELECT count(*) as cnt, sum(qty) as sum FROM mallRN_goods_option WHERE guid='{$uid}' && qty_type=0";
		$data = $mysql->one_row($sql);
		$return = "<span class='optionView underLine size09' data-uid='{$uid}' gname='{$gname}' title='{$gname} 옵션보기'>옵션</span> <i class='xi-link'></i><br />";
		if($data['cnt']>0) return $return.number_format($data['sum']);
		else return $return."무제한";
	}
	else {
		if($vls == 1) return "무제한";
		else return number_format($qty);
	}
};

$display_use_function = function ($vls, $cate_hide) {
	global $display_use_array;
	
	if($cate_hide=='1') {
		return $display_use_array[$vls]."<br /><span class='size09 colorBlue'>분류숨김</span>";	
	}
	return $display_use_array[$vls];

};

$option_use_function = function ($vls, $uid, $gname) {
	if($vls == 0) return "사용안함";
	else return "<span class='optionView underLine' data-uid='{$uid}' gname='{$gname}' title='{$gname} 옵션보기'>옵션보기</span>";	
};

$GLOBALS['DISPLAYS'] = "";
$main1_display1_function = function ($vls, $vls2, $vls3, $vls4, $vls5, $vls6) {
	global $tpl;

	$return	= array();
	if($vls) $return[] = "<div class='barTitle1'>메인</div><div class='barName'>인기</div>";
	if($vls2) $return[] = "<div class='barTitle1'>메인</div><div class='barName'>추천</div>";
	if($vls3) $return[] = "<div class='barTitle1'>메인</div><div class='barName'>신</div>";
	if($vls4) $return[] = "<div class='barTitle2'>분류</div><div class='barName'>인기</div>";
	if($vls5) $return[] = "<div class='barTitle2'>분류</div><div class='barName'>추천</div>";
	if($vls6) $return[] = "<div class='barTitle2'>분류</div><div class='barName'>신</div>";

	if(count($return) > 0) {
		$GLOBALS['DISPLAYS'] = join("",$return);
		$tpl->parse("is_displays");
	}

	return;
};

$commission_type_function = function ($vls) {
	if($vls)	return "<div class='barName2'>개별</div>";
	else		return '';
};

$GLOBALS['CATE_MORE'] = "";
$cate_function = function ($vls, $uid) {
	global $mysql, $tpl;

	$sql = "SELECT * FROM mallRN_goods_cate WHERE guid='{$uid}' ORDER BY cate_rep DESC, cate ASC";
	$mysql->query2($sql);

	$return			= "";
	$cate_more	= array();	
	while($row = $mysql->fetch_array(2)){
		if(!$return) $return	= getCateAllName($row['cate'], 1);
		else $cate_more[]	= getCateAllName($row['cate'], 1);
	}

	if(count($cate_more) > 0) {
		$GLOBALS['CATE_MORE'] = join("<br />",$cate_more);
		$tpl->parse("is_cate_more");
	}

	return $return;
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
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};

$limit_qty_where = function ($vls) {
	return "&& a.limit_qty > '0' ";
};

$soldout_where = function ($vls) {
	return "&& ((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1))";
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
${"checked_commission_type_".$commission_type}	= "checked='checked'";
${"checked_information_use_".$information_use}	= "checked='checked'";
######################## listPaging 파라미터 및 검색조건 처리 #############################

######################## 검색 조건 있는 경우 #############################
$cate1 = $cate2 = $cate3 = $cate4 = '';

if($listPaging->check_where == 1) {	
	$tpl->parse("is_search");
}
else if($listPaging->check_where == 2) {
	if($cate) {
		for($i = 3, $j = 1; $i < 13; $i = $i + 3) {
			if(substr($cate, ($i - 3), $i) != '000') {
				if($j == 4) ${"cate".$j} = $cate;
				else		${"cate".$j} = substr($cate, 0, $i).sprintf("%0".(12 - $i)."d", 0);						
			}			
			$j++;					
		}
	}

	$tpl->parse("is_search");
	$tpl->parse("is_search_open");
}
######################## 검색 조건 있는 경우 #############################

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

if($reset == 1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>