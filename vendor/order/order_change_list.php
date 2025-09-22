<?php 

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
$tpl->define("main","order_change_list.html");
$tpl->scan_area("main");

######################## 사유 정보 #############################
$sql = "SELECT distinct(reason) FROM mallRN_order_status_change WHERE vendor = '{$v_my_id}'";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	$reason_name				= specialStrReplace($row['reason']);
	$tpl->parse("loop_reason");
}
unset($vendor_reason);
######################## 사유 정보 #############################

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
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
$listPaging = new listPaging('mallRN_order_status_change'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'status' => 2, 'reason' => '1', 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'order_num' => 'function|name|id|og_uid', 'message' => '', 'reason' => 'reason', 'manager' => '', 'status' => 'function|status2', 'status_date' => 'datetime', 'signdate' => 'datetime');
$listPaging->default_where		= "vendor = '{$v_my_id}'";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array					= array("order_num", "id", "name");
$status_array					= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array					= array("0" => "", "1" => "요청", "2" => "회수중", "3" => "회수완료", "4" => "발송완료", "5" => "완료", "9" => "거부"); 
$proc_status_array				= array("72" => "교환승인", "73" => "회수완료", "82" => "반품승인", "83" => "회수완료"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$NAME			= "";
$MEMBER_ID		= "";
$GOODS			= "";

$order_num_function = function ($vls, $name, $id, $og_uid) {
	global $mysql, $tpl;

	if($id) {
		$GLOBALS['MEMBER_ID']		= $id;
		$tpl->parse("is_member");
	}
	$GLOBALS['NAME']		= stripslashes($name);

	if(!$og_uid) {
		$GLOBALS['GOODS']	= "전체상품";	
	}
	else {
		$sql	= "SELECT g_name, option_name FROM mallRN_order_goods WHERE uid = '{$og_uid}' && reals = 1";
		$data	= $mysql->one_row($sql);

		$GLOBALS['GOODS']	= stripslashes($data['g_name'])." ".stripslashes($data['option_name']);
	}
	
	return $vls;	
};

$reason_function = function ($vls) {
	return specialStrReplace2($vls);
};

$PROC_STATUS	= "";
$PROC_TTL		= "";

$status_function = function ($vls, $status2) {	
	global $tpl, $status_array, $status2_array, $proc_status_array;
	
	if(($vls == 7 && $status2 == 1) || ($vls == 7 && $status2 == 2) || ($vls == 8 && $status2 == 1) || ($vls == 8 && $status2 == 2)) {
		$GLOBALS['PROC_STATUS']	= $vls.($status2 + 1);		
		$GLOBALS['PROC_TTL']	= $proc_status_array[$GLOBALS['PROC_STATUS']];

		$tpl->parse("is_status_default");
	}	
	if($vls == 7 && $status2 == 3) $tpl->parse("is_status_74");

	if($status2 == 1) {
		$GLOBALS['PROC_TTL2']	= $status_array[$vls];
		$tpl->parse("is_status_cancel");
	}

	if($status2)	return $status_array[$vls].$status2_array[$status2];
	else			return $status_array[$vls];	
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$where2 = "";
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};


$status_where = function ($vls) {
	if($vls == 1) {
		return "&& a.status2 = 1";
	}	
	else if($vls == 2) {
		return "&& ((a.status = 7 && (a.status2 = 2 || a.status2 = 3)) || (a.status = 8 && a.status2 = 2))";
	}
	else {
		$status	= substr($vls, 0, 1);

		return "&& a.status = '{$status}'";
	}	
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

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