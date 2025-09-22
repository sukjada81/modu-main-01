<?php 

$reset	= isset($_POST['reset']) ? $_POST['reset'] : '';

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
$tpl->define("main","coupon_down_list.html");
$tpl->scan_area("main");

######################## 쿠폰 리스트 #############################
$sql = "SELECT * FROM mallRN_coupon_manager ORDER BY name ASC";
$mysql->query($sql);

$c_uid_array = array();
while($row = $mysql->fetch_array()) {
	
	$coupon_name				= specialStrReplace($row['name']);
	$coupon_uid					= specialStrReplace($row['uid']);
	$c_uid_array[$row['uid']]	= $coupon_name;
	
	$tpl->parse("loop_coupon");
}
######################## 쿠폰 리스트 #############################

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	
	$name						= specialStrReplace($row['name']);	
	$level_array[$row['level']] = $name;	
}
######################## 회원등급 #############################

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "id";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_coupon'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'c_uid' => 1, 'status' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'c_uid' => 'function|g_uid', 'id' => 'function', 'e_date' => '', 'status' => 'array', 'usedate' => 'datetime', 'signdate' => 'datetime');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array		= "";
$status_array		= array("0" => "발급완료", "1" => "사용완료", "2" => "기간만료");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$MEMBER_NAME = "";
$MEMBER_LVEL = "";
$id_function = function ($vls) {
	global $mysql, $level_array;

	$sql	= "SELECT name, level FROM mallRN_member WHERE id = '{$vls}'";
	$data	= $mysql->one_row($sql);

	$GLOBALS['MEMBER_NAME']		= stripslashes($data['name']);
	$GLOBALS['MEMBER_LEVEL']	= @$level_array[$data['level']];

	return $vls;	
};

$c_uid_function = function ($vls, $g_uid) {
	global $c_uid_array;

	if($g_uid)	return $c_uid_array[$vls]."({$g_uid})";
	else		return $c_uid_array[$vls];	
};
######################## list_variable : funtion일 경우 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($atotal_record) $listPaging->atotal_record = $atotal_record;
if($total_record) $listPaging->total_record = $total_record;
$listPaging->total_all_record();
######################## listPaging 파라미터 및 검색조건 처리 #############################

######################## 검색 조건 있는 경우 #############################
if($listPaging->check_where == 1) {	
	$tpl->parse("is_search");
}
else if($listPaging->check_where == 2) {
	$tpl->parse("is_search");
	$tpl->parse("is_search_open");
}
######################## 검색 조건 있는 경우 #############################

######################## 리스트 출력 및 페이징 처리 #############################
$PAGING_TYPE	= PAGING_TYPE;
if($listPaging->total_record > 0) {
	$listPaging->print_record();
	if(PAGING_TYPE==1) $PAGING = "";
	else $PAGING = $listPaging->print_page();	
	//$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset==1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml"=>$tpl->tprint("is_list_area",1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
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