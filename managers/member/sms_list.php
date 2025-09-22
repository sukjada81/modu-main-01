<?php 

$reset = isset($_POST['reset']) ? $_POST['reset'] : '';

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
$tpl->define("main","sms_list.html");
$tpl->scan_area("main");

$sql			= "SELECT sms_yn, sms_key, sms_secret FROM mallRN_configuration WHERE uid = 1";
$data			= $mysql->one_row($sql);
if($data['sms_yn'] == 'Y') {
	$coolsms_key	= $data['sms_key'];
	$coolsms_secret = $data['sms_secret'];

	require_once(PATH_COOLSMS.'/lib/message.php');
}

######################## 변수 정의 #############################
if(!isset($_GET['field'])) $_GET['field'] = "cell";
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
$listPaging						= new listPaging('mallRN_sms_list'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 's_date' => 5 , 'e_date' => 2, 'result_code' => 2, 'type' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'cell' => '', 'type' => 'array', 'message' => '', 'result' => '', 'result_code' => '', 'received_date' =>'function', 'status' =>'function|messageId|result_code|uid', 'signdate'=>'datetime');
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array	= "";
$type_array		= array("SMS", "LMS");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$GLOBALS['RECEIVED_DATE']	= "";

$status_function = function ($vls, $messageId, $result_code, $uid) {
	global $mysql, $tpl; 
	if($vls == 0 && $messageId && ($result_code == "2000" || $result_code == "3000")) {
		$params = array("messageId" => $messageId);
		$res	= get_messages($params);

		foreach ($res->messageList as $key => $val) {
			
			if($val->status == 'COMPLETE' || $val->statusCode == '4000') {
				$tmps			= explode(".", $val->dateReceived);
				$tmps			= str_replace("T", "", $tmps[0]);
				$received_date	= strtotime($tmps) + (3600 * 9);
				$add_query	= ", status = 1, received_date = '{$received_date}'";
			}
			$sql = "UPDATE mallRN_sms_list SET result_code = '{$val->statusCode}', result = '{$val->reason}' {$add_query} WHERE uid = '{$uid}'";
			$mysql->query2($sql);

			$GLOBALS['RESULT_CODE']		= $val->statusCode;
			$GLOBALS['RESULT']			= $val->reason;
			$GLOBALS['RECEIVED_DATE']	= date("Y-m-d H:i:s", $received_date);
			$tpl->parse("is_received");
		}		
	}
};

$received_date_function = function ($vls) {
	global $tpl; 
	if($vls) {
		$GLOBALS['RECEIVED_DATE']	= date("Y-m-d H:i:s", $vls);
		$tpl->parse("is_received");
	}
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.signdate) < '{$vls} 23:59:59'";
	else					return "&& from_unixtime(a.signdate) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59'";
};

$result_code_where = function ($vls) {
	if($vls == 'X') return " && (result_code != '2000' && result_code != '3000' && result_code != '4000')";
	else			return " && result_code = '{$vls}'";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($atotal_record)	$listPaging->atotal_record = $atotal_record;
if($total_record)	$listPaging->total_record = $total_record;
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
	if(PAGING_TYPE==1)	$PAGING = "";
	else				$PAGING = $listPaging->print_page();	
	//$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset==1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$ATOTAL	= number_format($ATOTAL);
$TOTAL	= number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>