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
$tpl->define("main","member_list.html");
$tpl->scan_area("main");

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	
	$name						= specialStrReplace($row['name']);
	$levels						= specialStrReplace($row['level']);
	$level_array[$row['level']] = $name;
	
	$tpl->parse("loop_level");
	$tpl->parse("loop_level2");
	$tpl->parse("loop_level3");
}
######################## 회원등급 #############################

######################## 변수 정의 #############################
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['field'])) $_GET['field'] = "multi";
if(!isset($_GET['field2'])) $_GET['field2'] = "multi";
if(!isset($_GET['field3'])) $_GET['field3'] = "multi";
if(!isset($_GET['field4'])) $_GET['field4'] = "multi";
if(!isset($_GET['range1'])) $_GET['range1'] = "order_cnt";
if(!isset($_GET['range2'])) $_GET['range2'] = "order_cnt";
if(!isset($_GET['range3'])) $_GET['range3'] = "order_cnt";
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}

$DATE1 = date("Y-m-d");
$DATE2 = date("Y-m-d", strtotime('-3 DAY'));
$DATE3 = date('Y-m-d', strtotime('-1 WEEK'));
$DATE4 = date('Y-m-d', strtotime('-1 MONTH'));
$DATE5 = date('Y-m-d', strtotime('-3 MONTH'));
$DATE6 = date('Y-m-d', strtotime('-6 MONTH'));
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging = new listPaging('mallRN_member'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5 , 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 's_range1' => 5, 'e_range1' => 5, 'range1' => 2, 's_range2' => 5, 'e_range2' => 5, 'range2' => 2, 's_range3' => 5, 'e_range3' => 5, 'range3' => 2, 'level' => 1, 'mailling' => 1, 'sms' => 1, 'auth' => 1, 'gender' => 1, 'marry' => 1, 'address1' => 2, 'mobile' => 1, 'sns_type' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'name' => '', 'id' => '', 'level' => 'array', 'cell' => '', 'gender' => 'array', 'birth' => '', 'address1' => 'area', 'mileage' => 'number', 'order_cnt' => 'function|id', 'order_price' => 'function|id', 'cnts' => 'number', 'auth' => 'array', 'mailling' => 'array', 'sms' => 'array', 'mobile' => 'array', 'sleep_time' => 'date', 'order_time' => 'date', 'login_time' => 'date', 'signdate' => 'date');
######################## listPaging 정의 #############################

######################## 노출항목 설정 처리 #############################
$listPaging->type = 1;

$field_title_arr = array('no' => '번호', 'name' => '이름', 'id' => '아이디 <i class="xi-link"></i>', 'crm' => 'CRM <i class="xi-focus-frame"></i>', 'level' => '등급', 'cell' => '휴대폰번호', 'gender' => '성별', 'birth' => '생년월일', 'address1' => '거주지역', 'mileage' => '마일리지 <i class="xi-external-link"></i>', 'order_cnt' => '주문건수 <i class="xi-external-link"></i>', 'order_price' => '주문금액', 'cnts' => '방문횟수', 'auth' => '가입승인', 'mailling' => '메일수신', 'sms' => 'SMS수신', 'mobile' => '가입경로', 'nondormant_time' => '휴면해제일', 'order_time' => '최종주문일', 'login_time' => '최종로그인', 'signdate' => '가입일');
$field_width_arr = array('no' => '70', 'name' => '100', 'id' => '140', 'crm' => '80', 'level' => '100', 'cell' => '120', 'gender' => '80', 'birth' => '120', 'address1' => '100', 'mileage' => '100', 'order_cnt' => '90', 'order_price' => '100', 'cnts' => '80', 'auth' => '80', 'mailling' => '90', 'sms' => '90', 'mobile' => '100', 'nondormant_time' => '110', 'order_time' => '110', 'login_time' => '110', 'signdate' => '110');

$sql		= "SELECT fields FROM mallRN_list_show_config WHERE vendor = '' && name='member'";
$data		= $mysql->get_one($sql);
if(!$data) {
	$data = "no|1|*|name|1|*|id|1|*|crm|1|*|level|1|*|cell|0|*|gender|0|*|birth|0|*|address1|0|*|mileage|1|*|order_cnt|1|*|order_price|1|*|cnts|1|*|auth|1|*|mailling|0|*|sms|0|*|mobile|0|*|nondormant_time|0|*|login_time|1|*|signdate|1";
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
$multi_array	= array("name", "id", "email", "tel", "cell", "comp", "comp_num", "comp_owner");
$gender_array	= array("M" => "남성",		"F" => "여성",		"N" => "미선택"); 
$marry_array	= array("M" => "기혼",		"S" => "미혼",		"N" => "미선택"); 
$auth_array		= array("Y" => "승인완료",		"N" => "미승인"); 
$mailling_array	= array("Y" => "수신허용",		"N" => "수신안함"); 
$sms_array		= array("Y" => "수신허용",		"N" => "수신안함"); 
$mobile_array	= array("Y" => "모바일",		"N" => "PC");
######################## list_variable : array일 경우 정의 #############################


######################## list_variable : funtion일 경우 정의 #############################
$order_cnt_function = function ($vls, $id) {
	global $mysql;
	
	$sql = "SELECT count(*) FROM mallRN_order_info WHERE id = '{$id}' && pay_status = 'C'";
	return number_format($mysql->get_one($sql));

};

$order_price_function = function ($vls, $id) {
	global $mysql;
	
	$sql	= "SELECT SUM(pay_total) as pay_total, SUM(cancel_total) as cancel_total, SUM(refund_total) as refund_total FROM mallRN_order_info WHERE id = '{$id}' && pay_status = 'C'";
	$data	= $mysql->one_row($sql);

	return number_format($data['pay_total'] - $data['cancel_total'] - $data['refund_total']);

};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};

$range1_where = function ($vls) {
	if(!$GLOBALS['s_range1'] && !$GLOBALS['e_range1']) return;
	if(!$GLOBALS['s_range1']) return "&& a.{$vls} < {$GLOBALS['e_range1']} ";
	else if(!$GLOBALS['e_range1']) return "&& a.{$vls} > {$GLOBALS['s_range1']} ";
	else return "&& a.{$vls} BETWEEN '{$GLOBALS['s_range1']}' AND '{$GLOBALS['e_range1']}' ";
};

$range2_where = function ($vls) {
	if(!$GLOBALS['s_range2'] && !$GLOBALS['e_range2']) return;
	if(!$GLOBALS['s_range2']) return "&& a.{$vls} < {$GLOBALS['e_range2']} ";
	else if(!$GLOBALS['e_range2']) return "&& a.{$vls} > {$GLOBALS['s_range2']} ";
	else return "&& a.{$vls} BETWEEN '{$GLOBALS['s_range2']}' AND '{$GLOBALS['e_range2']}' ";
};

$range3_where = function ($vls) {
	if(!$GLOBALS['s_range3'] && !$GLOBALS['e_range3']) return;
	if(!$GLOBALS['s_range3']) return "&& a.{$vls} < {$GLOBALS['e_range3']} ";
	else if(!$GLOBALS['e_range3']) return "&& a.{$vls} > {$GLOBALS['s_range3']} ";
	else return "&& a.{$vls} BETWEEN '{$GLOBALS['s_range3']}' AND '{$GLOBALS['e_range3']}' ";
};

$address1_where = function ($vls) {
	return "&& a.address1 LIKE '{$vls}%'";
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
else {
	####################### 관리자 로그 ##########################	
	adminLog($my_id, '회원리스트', 2);
	####################### 관리자 로그 ##########################
}

$ATOTAL = number_format($ATOTAL);
$TOTAL = number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>