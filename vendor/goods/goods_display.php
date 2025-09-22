<?php 

include_once("../common/top.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_display.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$type2	= checkGetVar('type2', 1, array(1, 2, 3)); 
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_goods'); 
$listPaging->list_variable	= array('uid' => '', 'image3' => 'function|moddate', 'name' => '', 'cate' => 'cate_no', 'price' => 'goods_price', 'display_use' => 'array', 'sale_use' => 'array', 'qty_type' => 'function|qty|uid|option_use|name');
######################## listPaging 정의 #############################

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
		$sql = "SELECT count(*) as cnt, sum(qty) as sum FROM mallRN_goods_option WHERE guid='{$uid}' && qty_type=0";
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

$where = "";

$sort = "store_display{$type2}_sequence ASC, c.uid DESC";	

$listPaging->where = " && a.vendor = '{$v_my_id}' && a.store_display{$type2} = '1' {$where}";	

$type2_arr	= array('', '인기상품', '추천상품', '신상품');
$type2_name	= $type2_arr[$type2];

######################## JOIN 사용시 함수 정의 #############################
$listPaging->squery = "squery";
$squeryTotal = function ($swhere, $where) {
	return "SELECT COUNT(*) FROM mallRN_goods a WHERE a.uid IN ( SELECT guid FROM mallRN_goods_cate WHERE {$swhere} ) {$where}";
};

$squeryPrint = function ($swhere, $where, $start_record, $page_record_num) {
	if($where) $where = "WHERE ".substr($where, 3);

	return "SELECT d.* FROM ( SELECT guid, {$GLOBALS['ssort']} FROM mallRN_goods_cate WHERE {$swhere} ) b JOIN ( SELECT a.uid FROM mallRN_goods a {$where} ) c ON b.guid=c.uid JOIN mallRN_goods d ON c.uid=d.uid ORDER BY b.{$GLOBALS['ssort']} ASC, d.uid DESC LIMIT {$start_record},{$page_record_num}";		
};
######################## JOIN 사용시 함수 정의 #############################

$listPaging->page_record_num = '1000';
$listPaging->total_record();

######################## 리스트 출력 및 초기 정렬 확인 #############################
if($listPaging->total_record > 0) {
	$listPaging->print_record();	
	
	$sql = "SELECT MIN(store_display{$type2}_sequence) FROM mallRN_goods WHERE vendor = '{$v_my_id}' && store_display{$type2} = '1' {$where}";
	if($mysql->get_one($sql) == 99999) $tpl->parse("is_first_proc");
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