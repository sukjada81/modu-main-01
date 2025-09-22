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
$tpl->define("main","mileage_list.html");
$tpl->scan_area("main");

######################## 회원등급 정보 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE uid > 0 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = stripslashes($row['name']);
}
######################## 회원등급 정보 #############################

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
$listPaging						= new listPaging('mallRN_mileage'); 
$listPaging->search_variable	= array('id' => 1, 'field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5 , 'e_date' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'id' => 'function', 'content' => '', 'order_num' => 'function', 'mileage' => 'number', 'use_mileage' => 'number', 'expired_date' => 'function|expired', 'proc_id' => 'function|proc_acc_ip', 'signdate' => 'datetime');
$listPaging->field_where		= array('id');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= array("id", "content", "order_num");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$MEMBER_NAME	= "";
$MEMBER_LEVEL	= "";
$PROC_ID		= "";
$PROC_ACC_IP	= "";
$ORDER_NUM		= "";

$id_function = function ($vls) {
	global $mysql, $level_array;
	
	$vls	= add_escape_re_string($vls);
	$sql	= "SELECT name, level FROM mallRN_member WHERE id = '{$vls}'";
	$data	= $mysql->one_row($sql);
	$adds	= "";
	if(!$data) {
		$sql	= "SELECT name, level FROM mallRN_member_sleep WHERE id = '{$vls}'";
		$data	= $mysql->one_row($sql);
		
		if(!$data) {
			$GLOBALS['MEMBER_NAME']		= "";
			$GLOBALS['MEMBER_LEVEL']	= "";

			return $vls;
		}
		$adds	= "[휴면] ";
	}

	$GLOBALS['MEMBER_NAME']		= $adds.stripslashes($data['name']);
	$GLOBALS['MEMBER_LEVEL']	= @$level_array[$data['level']];

	return $vls;	
};

$order_num_function = function ($vls) {
	global $tpl;

	if($vls) {
		$GLOBALS['ORDER_NUM']	= $vls;

		$tpl->parse("is_order_num");
	}
};

$expired_date_function = function ($vls, $expired) {
	if($expired == 1)			return "기간만료";
	if($vls == '1000-01-01' || $vls == '0000-00-00')	return "-";
	else						return $vls;

};


$proc_id_function = function ($vls, $proc_acc_ip) {
	global $tpl;

	if($vls) {
		$GLOBALS['PROC_ID']		= $vls;
		$GLOBALS['PROC_ACC_IP']	= $proc_acc_ip;

		$tpl->parse("is_proc_id");
	}
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {

	if($GLOBALS['date_type'] == 'expired_date') {
		if(!$GLOBALS['s_date']) return "&& a.{$GLOBALS['date_type']} < '{$vls}' ";
		else return "&& a.{$GLOBALS['date_type']} BETWEEN '{$GLOBALS['s_date']}' AND '{$vls}' ";
	}
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

$idFieldWhere = function ($vls) {
	return " && id = '{$vls}'";
};

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

if($id) {	
	$sql		= "SELECT SUM(mileage) as mileage, SUM(use_mileage) as use_mileage FROM mallRN_mileage WHERE id = '{$id}'";
	$data		= $mysql->one_row($sql);
	$TOTAL1		= number_format($data['mileage']);
	$TOTAL2		= number_format($data['use_mileage']);
	$TOTAL3		= number_format($data['mileage'] - $data['use_mileage']);

	$tpl->parse("is_totals");
}

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>