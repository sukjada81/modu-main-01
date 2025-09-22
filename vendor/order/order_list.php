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
$tpl->define("main","order_list.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$_GET['vendor'] = $v_my_id;
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['range1']))	$_GET['range1'] = "pay_total";
if(!isset($_GET['field'])) $_GET['field'] = "multi2";
if(!isset($_GET['field2'])) $_GET['field2'] = "multi2";
if(!isset($_GET['field3'])) $_GET['field3'] = "multi2";
if(!isset($_GET['field4'])) $_GET['field4'] = "multi2";
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
$listPaging = new listPaging('mallRN_order_info'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'mobile' => 1, 'pay_type' => 1, 'pay_status' => 1, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'signdate' => 'function', 'order_num' => 'function', 'name' => 'function|id', 'goods' => 'function|order_num', 'pay_type' => 'array', 'pay_status' => 'array');
$listPaging->field_where		= array('g_name', 'g_uid', 'g_code', 'g_delivery_info');
$listPaging->default_where		= "reals = 1";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= array('order_num', 'id', 'name', 'cell', 'email', 'name2', 'cell2', 'bank_info');
$multi2_array			= array("delivery_info", "g_name", "g_uid", "g_code");
$status_array			= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array			= array("0" => "", "1" => "요청", "2" => "회수중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
$pay_type_array			= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array		= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$signdate_function = function ($vls) {
	return date("Y-m-d H:i:s", $vls);	
};

$ORDER_NUM2		= "";

$order_num_function = function ($vls) {
	$GLOBALS['ORDER_NUM2'] = $vls;	
	return $vls;
};

$MEMBER_ID		= "";

$name_function = function ($vls, $id) {
	global $tpl;
	if($id) {
		$GLOBALS['MEMBER_ID']		= $id;		

		$tpl->parse("is_member");
	}
	return $vls;	
};

$GOODS_NAME		= "";
$GOODS_PRICE	= "";
$GOODS_QTY		= "";
$GOODS_STATUS	= "";
$GOODS_TOTAL	= 0;
$DELIVERY_TOTAL	= 0;
$PAY_TOTAL		= 0;

$goods_function = function ($vls, $order_num) {
	global $mysql, $tpl, $status_array, $status2_array, $v_my_id;

	$sql = "SELECT g_name, orig_price, qty, delivery_type, delivery_price, status, status2 FROM mallRN_order_goods WHERE vendor = '{$v_my_id}'&& order_num = '{$order_num}' && reals = 1 ORDER BY status ASC, uid DESC";
	$mysql->query2($sql);

	$return			= "";
	$ck_cnt			= 0;
	$goods_total	= 0;
	$delivery_price	= 0;
	$sum_delivery_option = array();
	
	while($row = $mysql->fetch_array(2)){
		if($row['status2'])	$status = $status_array[$row['status']].$status2_array[$row['status2']];
		else				$status = $status_array[$row['status']];
		if(!$return) $return		= "[{$status}] {$row['g_name']}";
		
		$GLOBALS['GOODS_NAME']		= stripslashes($row['g_name']);
		$GLOBALS['GOODS_PRICE']		= number_format($row['orig_price']);
		$GLOBALS['GOODS_QTY']		= number_format($row['qty']);
		$GLOBALS['GOODS_STATUS']	= $status;
		
		$goods_total				+= $row['orig_price'] * $row['qty'];
			
		$tpl->parse("loop_goods");

		$row['delivery_price'] += $row['delivery_add_price'];
		if($row['delivery_price']) {
			if($row['delivery_type'] == 5) {
				if($row['option']) {				
					if(isset($sum_delivery_option[$row['g_uid']]) && $sum_delivery_option[$row['g_uid']] > 0) {
						$G_DELIVERY_PRICE	= 0;					
					}
					else {
						$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$row['g_uid']}' && !(status = 9 && status2 = 5)";
						$option_qty			= $mysql->get_one($sql);
						$G_DELIVERY_PRICE	= $row['delivery_price'] * ceil($option_qty / $row['delivery_type_qty']);
						$sum_delivery_option[$row['g_uid']] = $option_qty;
					}
				}
				else {
					$G_DELIVERY_PRICE	= $row['delivery_price'] * ceil($row['qty'] / $row['delivery_type_qty']);
				}
				$delivery_price += $G_DELIVERY_PRICE;				
			}
			else							$delivery_price += $row['delivery_price'];		
		}
		$ck_cnt ++;
	}

	$GLOBALS['GOODS_TOTAL']		= number_format($goods_total);

	$sql	= "SELECT price FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$v_my_id}'";
	if($delivery_price2 = $mysql->get_one($sql)) {
		$delivery_price += $delivery_price2;
	}

	$GLOBALS['DELIVERY_TOTAL']		= number_format($delivery_price);
	$GLOBALS['PAY_TOTAL']			= number_format($goods_total + $delivery_price);
	
	if($ck_cnt > 1) $return .= "외 ".($ck_cnt - 1)." 건";

	return $return;
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## JOIN 사용시 함수 정의 #############################
$g_nameFieldWhere = function ($vls) {
	global $listPaging;
	if($listPaging->swhere) $listPaging->swhere = $listPaging->swhere." && INSTR(g_name, '{$vls}')";
	else					$listPaging->swhere = " INSTR(g_name, '{$vls}')";
};

$g_uidFieldWhere = function ($vls) {
	global $listPaging;
	if($listPaging->swhere) $listPaging->swhere = $listPaging->swhere." && INSTR(g_uid, '{$vls}')";
	else					$listPaging->swhere = " INSTR(g_uid, '{$vls}')";
};

$g_codeFieldWhere = function ($vls) {
	global $listPaging;
	if($listPaging->swhere) $listPaging->swhere = $listPaging->swhere." && INSTR(g_code, '{$vls}')";
	else					$listPaging->swhere = " INSTR(g_code, '{$vls}')";
};

$g_delivery_infoFieldWhere = function ($vls) {
	global $listPaging;
	if($listPaging->swhere) $listPaging->swhere = $listPaging->swhere." && INSTR(delivery_info, '{$vls}')";
	else					$listPaging->swhere = " INSTR(delivery_info, '{$vls}')";
};

$listPaging->squery = "squery";
$squeryTotal = function ($swhere, $where) {	
	return "SELECT COUNT(*) FROM mallRN_order_info a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_goods WHERE {$swhere} ) && a.reals = 1 {$where}";
};

$squeryPrint = function ($swhere, $where, $start_record, $page_record_num) {
	return "SELECT c.* FROM ( SELECT a.uid FROM mallRN_order_info a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_goods WHERE {$swhere} ) && a.reals = 1 {$where} ) b JOIN mallRN_order_info c ON b.uid=c.uid ORDER BY c.{$GLOBALS['sort']} LIMIT {$start_record}, {$page_record_num}";
};
######################## JOIN 사용시 함수 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->default_query = "SELECT COUNT(*) FROM mallRN_order_info a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_goods WHERE vendor = '{$v_my_id}' ) && a.reals = 1";
$listPaging->swhere = " vendor =  '{$v_my_id}'";	
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
	if(trim($listPaging->where) != '&& a.reals = 1' || trim($listPaging->swhere) != "vendor =  '{$v_my_id}'") {		
		$tpl->parse("is_search");
		$tpl->parse("is_search_open");
	}
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