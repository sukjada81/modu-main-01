<?php 

include_once("../common/top.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","exhibition_goods.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$exhibition	= checkGetVar('exhibition');
$cate		= checkGetVar('cate');
$sort		= "uid DESC";	
$where2		= "";
######################## 변수 정의 #############################

######################## 모음전 정보 #############################
$sql = "SELECT * FROM mallRN_exhibition ORDER BY uid DESC";
$mysql->query($sql);

$cate_info = "";
while($row = $mysql->fetch_array()){
	$exhibition_uid		= specialStrReplace($row['uid']);
	$exhibition_name	= specialStrReplace($row['name']);
	$tpl->parse("loop_exhibition1");
	$tpl->parse("loop_exhibition2");

	if(!$exhibition) $exhibition = $exhibition_uid;
	if($exhibition == $exhibition_uid) {
		$cate_info = $row['cate_info'];
	}
}
unset($exhibition_uid, $exhibition_name);

if($cate_info) {
	$cate_info = explode("|*|", $cate_info);
	$cate_max_num = $cate_info[0];
	if($cate_max_num > 100) {
		foreach($cate_info as $k => $v) {
			if($k == 0) continue;
			$cate_info2 = explode("|", $v);
			$cate_num	= specialStrReplace($cate_info2[0]);
			$cate_name	= specialStrReplace($cate_info2[1]);
			$tpl->parse("loop_cate1");
			$tpl->parse("loop_cate2");

			if(!$cate) $cate = $cate_num;		
		}
		$tpl->parse("is_cate1");
		$tpl->parse("is_cate2");
		unset($cate_info, $cate_info2, $cate_num, $cate_name);
	}
}

if($cate) $where2 = " && ecate = '{$cate}'";
######################## 모음전 정보 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_goods'); 
$listPaging->list_variable	= array('uid' => '', 'image3' => 'function|moddate', 'name' => '', 'cate' => 'cate_no', 'price' => 'goods_price', 'display_use' => 'array', 'sale_use' => 'array', 'qty_type' => 'function|qty|uid|option_use|name');
######################## listPaging 정의 #############################

######################## 판매사 정보 #############################
$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
$mysql->query($sql);

$vendor_array = array();
while($row = $mysql->fetch_array()){
	$vendor_id					= specialStrReplace($row['id']);
	$vendor_name				= specialStrReplace($row['comp_name']);
	$vendor_array[$vendor_id]	= $vendor_name;
}
unset($vendor_id, $vendor_name);
######################## 판매사 정보 #############################

######################## list_variable : array일 경우 정의 #############################
$display_use_array	= array("0"=>"<i class='fas fa-times'></i>","1"=>"<i class='far fa-circle'></i>"); 
$sale_use_array		= array("0"=>"<i class='fas fa-times'></i>","1"=>"<i class='far fa-circle'></i>"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$image3_function = function ($vls, $moddate) {
	return $vls."?t=".$moddate;
};

$qty_type_function = function ($vls, $qty, $uid, $option_use, $gname) {	
	global $mysql;
	
	if($option_use == 1) {
		$sql = "SELECT count(*) as cnt, sum(qty) as sum FROM mallRN_goods_option WHERE guid='{$uid}' && qty_type = 0";
		$data = $mysql->one_row($sql);
		$return = "<span class='size09'>옵션</span><br />";
		if($data['cnt'] > 0) return $return.number_format($data['sum']);
		else return $return."무제한";
	}
	else {
		if($vls == 1) return "무제한";
		else return number_format($qty);
	}
};
######################## list_variable : funtion일 경우 정의 #############################

######################## 독립 쿼리 사용시 함수 정의 #############################
$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {	
	return "SELECT COUNT(*) FROM mallRN_goods a WHERE a.uid IN ( SELECT guid FROM mallRN_exhibition_goods WHERE euid = '{$GLOBALS['exhibition']}' {$GLOBALS['where2']} ) {$where}";
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	if($where) $where = "WHERE ".substr($where, 3);

	return "SELECT d.* FROM ( SELECT guid, sequence FROM mallRN_exhibition_goods WHERE euid = '{$GLOBALS['exhibition']}' {$GLOBALS['where2']} ) b JOIN ( SELECT a.uid FROM mallRN_goods a {$where} ) c ON b.guid=c.uid JOIN mallRN_goods d ON c.uid=d.uid ORDER BY b.sequence ASC, d.uid DESC LIMIT {$start_record},{$page_record_num}";		
};
######################## 독립 쿼리 사용시 함수 정의 #############################

$listPaging->page_record_num = '1000';
$listPaging->total_record();

######################## 리스트 출력 및 초기 정렬 확인 #############################
if($listPaging->total_record > 0) {
	$listPaging->print_record();	
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 초기 정렬 확인 #############################

$tpl->parse("is_list_area");

$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>