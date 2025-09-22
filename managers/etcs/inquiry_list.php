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
$tpl->define("main","inquiry_list.html");
$tpl->scan_area("main");

######################## 회원등급 정보 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE uid > 0 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = stripslashes($row['name']);
}
######################## 회원등급 정보 #############################

######################## 분류 정보 #############################
$sql		= "SELECT inquiry_cate_info FROM mallRN_configuration WHERE uid = 1";
$cate_info	= $mysql->get_one($sql);

$cate_info	= explode("|*|", $cate_info);
$cate_array = array();
if($cate_info[0] > 100) {
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);

		$cate_num	= $cate_info2[0];
		$cate_name	= stripslashes($cate_info2[1]);
		
		$tpl->parse("loop_cate");

		$cate_array[$cate_num] = $cate_name;
	}	
}
unset($cate_info, $cate_info2, $cate_name, $cate_num);
######################## 분류 설정 #############################

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
if(!isset($_GET['field'])) $_GET['field'] = "name";
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_inquiry'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'vendor' => 1, 'cate' => 1, 'answer' => 4, 'id' => 4, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'cate' => 'array', 'vendor' => 'vendor', 'g_name' => '', 'g_uid' => '', 'subject' => 'function', 'name' => 'function|id', 'answer' => 'function',  'signdate' => 'date');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= array("g_name", "name", "id");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$MEMBER_ID		= "";
$MEMBER_LVEL	= "";
$name_function = function ($vls, $id) {
	global $mysql, $tpl, $level_array;
	
	if($id) {
		$sql	= "SELECT level FROM mallRN_member WHERE id = '{$id}'";
		$data	= $mysql->one_row($sql);

		$GLOBALS['MEMBER_ID']		= $id;
		$GLOBALS['MEMBER_LEVEL']	= $level_array[$data['level']];

		$tpl->parse("is_member");
	}
	
	return $vls	= specialStrReplace2($vls);
};

$subject_function = function ($vls) {
	return specialStrReplace2($vls);
};

$answer_function = function ($vls) {
	if(trim($vls))	return "답변완료";
	else		return "미답변";
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