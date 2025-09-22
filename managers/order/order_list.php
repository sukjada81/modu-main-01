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

######################## 회원등급 정보 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE uid > 0 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = stripslashes($row['name']);
}
######################## 회원등급 정보 #############################

######################## 판매사 정보 #############################
$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
$mysql->query($sql);

$vendor_array = array();
while($row = $mysql->fetch_array()){
	$vendor_id					= specialStrReplace($row['id']);
	$vendor_name				= specialStrReplace($row['comp_name']);
	$vendor_array[$vendor_id]	= $vendor_name;
	//$tpl->parse("loop_vendor");
}
unset($vendor_id, $vendor_name);
######################## 판매사 정보 #############################

######################## 변수 정의 #############################
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
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 's_range1' => 5, 'e_range1' => 5, 'range1' => 2, 'member' => 2, 'mobile' => 1, 'pay_type' => 2, 'pay_status' => 1, 'cash_receipts' => 2, 'new' => 1, 'use_mileage' => 2, 'use_coupon' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'signdate' => 'function|new', 'order_num' => 'function', 'name' => 'function|id', 'goods' => 'function|order_num|use_coupon|use_mileage', 'delivery_total' => 'number', 'pay_total' => 'number', 'cancel_total' => 'function|refund_total', 'pay_type' => 'array', 'pay_status' => 'array');
$listPaging->field_where		= array('g_name', 'g_uid', 'g_code', 'g_vendor', 'g_delivery_info');
$listPaging->default_where		= "reals = 1";
######################## listPaging 정의 #############################

######################## 노출항목 설정 처리 #############################
$listPaging->type = 1;

$field_title_arr = array('no' => '번호', 'signdate' => '주문일시', 'order_num' => '주문번호 <i class="xi-link"></i>', 'name' => '주문자 <i class="xi-external-link"></i>', 'goods' => '주문상품', 'goods_total' => '총상품금액', 'delivery_total' => '총배송비', 'discount_total' => '총할인금액', 'pay_total' => '총주문금액', 'cancel_total' => '취소금액', 'pay_type' => '결제수단', 'pay_status' => '결제상태', 'cnts1' => '미배송', 'cnts2' => '배송중', 'cnts3' => '배송완료', 'cnts4' => '취소', 'cnts5' => '교환', 'cnts6' => '반품');
$field_width_arr = array('no' => '80', 'signdate' => '120', 'order_num' => '120', 'name' => '140', 'goods' => '500', 'goods_total' => '100', 'delivery_total' => '80', 'discount_total' => '100', 'pay_total' => '110', 'cancel_total' => '100', 'pay_type' => '120', 'pay_status' => '80', 'cnts1' => '70', 'cnts2' => '70', 'cnts3' => '80', 'cnts4' => '60', 'cnts5' => '60', 'cnts6' => '60');

$sql		= "SELECT fields FROM mallRN_list_show_config WHERE vendor = '' && name='order'";
$data		= $mysql->get_one($sql);
if(!$data) {
	$data = "no|1|*|signdate|1|*|order_num|1|*|name|1|*|goods|1|*|goods_total|0|*|delivery_total|0|*|discount_total|0|*|pay_total|1|*|cancel_total|1|*|pay_type|1|*|pay_status|1|*|cnts1|0|*|cnts2|0|*|cnts3|0|*|cnts4|0|*|cnts5|0|*|cnts6|0";
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
$list_cnt = count($filed_able_arr) + 1;
unset($conf_data, $k, $v, $v2, $name, $checked, $list_title, $list_width, $filed_able_arr);
######################## 노출항목 설정 처리 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= array('order_num', 'id', 'name', 'cell', 'email', 'name2', 'cell2', 'bank_info');
$multi2_array			= array("delivery_info", "g_name", "g_uid", "g_code", "vendor");
$status_array			= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array			= array("0" => "", "1" => "요청", "2" => "회수중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
$pay_type_array			= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array		= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$signdate_function = function ($vls, $new) {
	global $tpl;
	
	if($new == 1) {
		$tpl->parse("is_new");
	}
	
	return date("Y-m-d H:i:s", $vls);	
};

$ORDER_NUM2		= "";

$order_num_function = function ($vls, $status) {
	global $mysql, $tpl;

	$GLOBALS['ORDER_NUM2'] = $vls;

	$sql	= "SELECT count(distinct(status)) as cnt, status FROM mallRN_order_goods WHERE order_num = '{$vls}'";
	$data	= $mysql->one_row($sql);
	if($data['cnt'] == 1 && ($data['status'] == 1 || $data['status'] == 2)) {
		$sql	= "SELECT pay_status, cancel_total, refund_total, pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$vls}' && reals = 1";
		$data	= $mysql->one_row($sql);

		if($data['pay_status'] == 'C') {
			if($data['pay_type'] == 'R') {
				if(date("Y-m-d", $data['status_date']) == date("Y-m-d") || date("Y-m-d", $data['status_date'] + 86400) == date("Y-m-d")) {
					$tpl->parse("is_btn_cancel_all");							
				}
				else {
					$tpl->parse("is_btn_cancel_all2");
				}			
			}

			if($data['pay_type'] == 'M' || $data['pay_type'] == 'C') {			
				$tpl->parse("is_btn_cancel_all");
			}
			else if($data['pay_type'] == 'B' || $data['pay_type'] == 'V') {
				$tpl->parse("is_btn_cancel_all2");
			}
		}
	}

	return $vls;
};

$MEMBER_ID		= "";
$MEMBER_LEVEL	= "";
$name_function = function ($vls, $id) {
	global $mysql, $tpl, $level_array;
	
	if($id) {
		$sql	= "SELECT level FROM mallRN_member WHERE id = '{$id}'";
		$data	= $mysql->one_row($sql);

		$GLOBALS['MEMBER_ID']		= $id;
		$GLOBALS['MEMBER_LEVEL']	= @$level_array[$data['level']];

		$tpl->parse("is_member");
	}
	
	return $vls;	
};

$GOODS_NAME		= "";
$GOODS_PRICE	= "";
$GOODS_QTY		= "";
$GOODS_STATUS	= "";
$GOODS_TOTAL	= 0;
$DISCOUNT_TOTAL	= 0;
$CNTS_1 = $CNTS_2 = $CNTS_3 = $CNTS_4 = $CNTS_5 = $CNTS_6 = 0;

$goods_function = function ($vls, $order_num, $use_coupon, $use_mileage) {
	global $mysql, $tpl, $status_array, $status2_array, $vendor_array;

	$sql = "SELECT g_name, price, qty, delivery_price, use_coupon, discount, vendor, status, status2 FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor_delivery ASC, vendor ASC, status ASC, uid DESC";
	$mysql->query2($sql);

	$return			= "";
	$ck_cnt			= 0;
	$goods_total	= 0;
	$discount_total	= 0;
	$GLOBALS['CNTS_1'] = $GLOBALS['CNTS_2'] = $GLOBALS['CNTS_3'] = $GLOBALS['CNTS_4'] = $GLOBALS['CNTS_5'] = $GLOBALS['CNTS_6'] = 0;

	while($row = $mysql->fetch_array(2)){
		if($row['status2'])	$status = $status_array[$row['status']].$status2_array[$row['status2']];
		else				$status = $status_array[$row['status']];
		if(!$return) $return		= "[{$status}] {$row['g_name']}";
		$GLOBALS['GOODS_NAME']		= stripslashes($row['g_name']);
		$GLOBALS['GOODS_PRICE']		= number_format($row['price']);
		$GLOBALS['GOODS_QTY']		= number_format($row['qty']);
		$GLOBALS['GOODS_STATUS']	= $status;
		if($row['vendor'])	$GLOBALS['GOODS_VENDOR'] = " / ".$vendor_array[$row['vendor']];
		else				$GLOBALS['GOODS_VENDOR'] = "";
		
		$goods_total				+= ($row['price'] + $row['use_coupon'] + $row['discount']) * $row['qty'];
		$discount_total				+= ($row['use_coupon'] + $row['discount']) * $row['qty'];
			
		$tpl->parse("loop_goods");

		if($row['status'] < 3) $GLOBALS['CNTS_1'] ++;
		else if($row['status'] == 4) $GLOBALS['CNTS_2'] ++;
		else if($row['status'] == 5) $GLOBALS['CNTS_3'] ++;
		else if($row['status'] == 7) $GLOBALS['CNTS_5'] ++;
		else if($row['status'] == 8) $GLOBALS['CNTS_6'] ++;
		else if($row['status'] == 9) $GLOBALS['CNTS_4'] ++;

		$ck_cnt ++;
	}

	$GLOBALS['GOODS_TOTAL']		= number_format($goods_total);
	$GLOBALS['DISCOUNT_TOTAL']	= number_format($discount_total + $use_coupon + $use_mileage);

	if($ck_cnt > 1) $return .= "외 ".($ck_cnt - 1)." 건";

	return $return;
};

$cancel_total_function = function ($vls, $refund_total) {
	return number_format($vls + $refund_total);	
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};

$range1_where = function ($vls) {
	if(!$GLOBALS['s_range1'] && !$GLOBALS['e_range1']) return;
	
	$s_range1 = str_replace(",", "", $GLOBALS['s_range1']);
	$e_range1 = str_replace(",", "", $GLOBALS['e_range1']);

	if(!$s_range1) return "&& a.{$vls} < {$e_range1} ";
	else if(!$e_range1) return "&& a.{$vls} > {$s_range1} ";
	else return "&& a.{$vls} BETWEEN '{$s_range1}' AND '{$e_range1}' ";
};

$member_where = function ($vls) {
	if($vls == 1) return "&& a.id != '' ";	
	else if($vls == 2) return "&& a.id = '' ";	
};

$cash_receipts_where = function ($vls) {
	if($vls == 1) return "&& a.cash_receipts != '' ";	
	else if($vls == 2) return "&& a.cash_receipts = '' ";	
};

$pay_type_where = function ($vls) {
	if($vls == 'TAX') return "&& (a.pay_type = 'B' || a.pay_type = 'R' || a.pay_type = 'V')";	
	else return "&& a.pay_type = '{$vls}' ";	
};

$use_mileage_where = function ($vls) {
	if($vls == 1) return "&& a.use_mileage > 0 ";	
	else if($vls == 2) return "&& a.use_mileage = 0 ";	
};

$use_coupon_where = function ($vls) {
	if($vls == 1) return "&& a.use_coupon > 0 ";	
	else if($vls == 2) return "&& a.use_coupon = 0 ";	
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

$g_vendorFieldWhere = function ($vls) {
	global $listPaging;
	if($listPaging->swhere) $listPaging->swhere = $listPaging->swhere." && INSTR(vendor, '{$vls}')";
	else					$listPaging->swhere = " INSTR(vendor, '{$vls}')";
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