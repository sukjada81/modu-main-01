<?php 

include_once("../common/top.php");

include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_display.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$type	= checkGetVar('type', 1, array(1, 2, 3));
$type2	= checkGetVar('type2', 1, array(1, 2, 3)); 
$cate	= checkGetVar('cate');
######################## 변수 정의 #############################

if($type == 2 || $type == 3) {
	######################## 분류 정보 #############################
	$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
	$mysql->query($sql);

	while($row=$mysql->fetch_array()){    
		$cate_num	= specialStrReplace($row['cate']);
		$cate_name	= specialStrReplace($row['cate_name']);
		$tpl->parse("loop_cate1");
		$tpl->parse("loop_cate2");
		$tpl->parse("loop_cate3");

		if(!$cate) $cate = $cate_num;
	}
	unset($cate_name);
	$tpl->parse("is_cate1");
	$tpl->parse("is_cate2");	
	######################## 분류 정보 #############################
}

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_goods'); 
$listPaging->list_variable	= array('uid' => '', 'image3' => 'function|moddate', 'name' => '', 'vendor' => 'vendor_isset', 'cate' => 'cate_no', 'price' => 'goods_price', 'display_use' => 'array', 'sale_use' => 'array', 'qty_type' => 'function|qty|uid|option_use|name');
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
$type_arr			= array('', '메인진열', '분류메인진열', '분류상품진열');
$type_name			= $type_arr[$type];
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
if($type == 3) {
	$cate_dep	= strlen(str_replace("000", "", $cate)) / 3;
	$ssort		= "sequence{$cate_dep}";
		
	for($i = 3; $i < 13; $i = $i + 3) {
		if(substr($cate, $i, ($i + 3)) == '000') break;						
	}					
		
	$listPaging->swhere	= "SUBSTRING(cate, 1, {$i}) = '".substr($cate, 0, $i)."'";
	$listPaging->where	= "&& a.order_priority = 0";
	$where = "&& SUBSTRING(b.cate, 1, {$i}) = '".substr($cate, 0, $i)."' && a.order_priority = 0";

	if($cate) {
		for($i = 3, $j = 1; $i < 13; $i = $i + 3) {
			if(substr($cate, ($i - 3), $i) != '000') {
				if($j == 4) $cate4 = $cate;
				else ${"cate".$j} = substr($cate, 0, $i).sprintf("%0".(12 - $i)."d", 0);	
			}			
			$j++;					
		}
	}

	$type2_name = getCateAllName($cate, 0);
	$post_type = "cate";
	
	$tpl->parse("is_cate_goods1");
	$tpl->parse("is_cate_goods3");
	$tpl->parse("is_cate_goods4");
	
}
else {
	if($type == 2) $where = " && SUBSTRING(cate, 1,3) = '".substr($cate, 0, 3)."'";
	$sort = "main{$type}_display{$type2}_sequence ASC, c.uid DESC";	
	
	$listPaging->where = " && a.main{$type}_display{$type2} = '1' {$where}";	

	$type2_arr	= array('', '인기상품', '추천상품', '신상품');
	$type2_name	= $type2_arr[$type2];
	$post_type = "main";

	$tpl->parse("is_main_goods1");
	$tpl->parse("is_main_goods2");
}

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
	
	if($type == 3)	$sql = "SELECT MIN(b.sequence{$cate_dep}) FROM mallRN_goods a, mallRN_goods_cate b WHERE a.uid=b.guid {$where}";
	else			$sql = "SELECT MIN(main{$type}_display{$type2}_sequence) FROM mallRN_goods WHERE main{$type}_display{$type2} = '1' {$where}";

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