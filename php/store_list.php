<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

######################## 변수 정의 #############################
if(!isset($_GET['limit']))  $_GET['limit']	= "10";	
$sort		= "uid DESC";

$goods_field = array();
foreach($default_goods_field as $k => $v) {
	$goods_field[] = "{$v}";
}
$goods_field = join(", ", $goods_field);
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_vendor'); 
$listPaging->search_variable	= array('limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'id' => '', 'comp_name' => 'function|id');
$listPaging->defaultParam		= "channel={$channel}";
$listPaging->default_where		= "auth = 'Y' && sell = 'A'";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= "";
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$STORE_NAME		= "";
$FSTORE_CNT		= "";
$V_UID			= "";

$comp_name_function = function ($vls, $id) {
	global $mysql, $tpl, $goods_field, $my_id; 
	
	$sql					= "SELECT basic_name FROM mallRN_vendor_configuration WHERE vendor = '{$id}'";
	$vshop_config			= $mysql->one_row($sql);

	$GLOBALS['STORE_NAME']	= ($vshop_config['basic_name'])	 ? stripslashes($vshop_config['basic_name']) : stripslashes($vls);

	$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && vendor = '{$id}' ORDER BY store_display1 DESC, store_display2 DESC, store_display3 DESC, order_cnt DESC LIMIT 6";
	$mysql->query2($sql);
	
	$cnt = 0;
	while($row = $mysql->fetch_array(2)){
		getGoodsInfo($row, "vendor_goods");
		$cnt = 1;
	}	

	if($cnt == 0) $tpl->parse("empty_vendor_goods");

	$sql					= "SELECT count(*) FROM mallRN_favorite_store WHERE vendor = '{$id}'";
	$GLOBALS['FSTORE_CNT']	= number_format($mysql->get_one($sql));
	
	if($my_id) {
		$sql = "SELECT count(*) FROM mallRN_favorite_store WHERE id = '{$my_id}' && vendor = '{$id}'";
		if($mysql->get_one($sql) > 0) $tpl->parse("is_selected");
	}

	return $vls;
};
######################## list_variable : funtion일 경우 정의 #############################

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

if(!$my_id) $tpl->parse("is_login");

?>