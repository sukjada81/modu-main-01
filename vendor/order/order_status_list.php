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
$tpl->define("main","order_status_list.html");
$tpl->scan_area("main");

######################## 배송업체 정보 #############################
$delivery_info_array	= array();
$delivery_url_array		= array();

$sql = "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
if($delivery_info = $mysql->get_one($sql)){
	$delivery_info = explode("|*|", $delivery_info);
}

$sql = "SELECT delivery_info FROM mallRN_vendor_configuration WHERE vendor = '{$v_my_id}'";
$delivery_info_vendor	= $mysql->get_one($sql);

if(!$delivery_info_vendor) {
	$tmp_array			= array();
	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i2 = 1, $cnt2 = count($delivery_info); $i2 < $cnt2; $i2 ++) {
			$delivery_info2 = explode("|", $delivery_info[$i2]);
			$tmp_array[] = "{$delivery_info2[0]}|1";			
		}
	}
	$delivery_info_vendor = join("|*|", $tmp_array);
	unset($tmp_array);
}

$delivery_info_vendor	= explode("|*|", $delivery_info_vendor);
for($i = 0, $cnt = count($delivery_info_vendor); $i < $cnt; $i++) {	
	$delivery_info_vendor2 = explode("|", $delivery_info_vendor[$i]);	
	if($delivery_info_vendor2[1] == '1') {
		if($delivery_info[1] && $delivery_info[1] != '|||') {
			for($i2 = 1, $cnt2 = count($delivery_info); $i2 < $cnt2; $i2 ++) {

				$delivery_info2 = explode("|", $delivery_info[$i2]);
				if($delivery_info2[3] == 0) continue;
				if($delivery_info_vendor2[0] != $delivery_info2[0]) continue;		

				$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];
				$delivery_url_array[$delivery_info2[0]] = $delivery_info2[2];
			}
		}
	}
}	
	
foreach($delivery_info_array as $k => $v) {
	$delivery_num	= $k;
	$delivery_name	= $v;
			
	$tpl->parse("loop_delivery1");
	$tpl->parse("loop_delivery2");
}
######################## 배송업체 정보 #############################

######################## 변수 정의 #############################
define('IMAGE_FOLDER', '../../image/goods/img');

$status	= checkGetVar("status");
if(!isset($_GET['date_type']))  $_GET['date_type'] = "signdate";
if(!isset($_GET['range1']))	$_GET['range1'] = "pay_total";
if(!isset($_GET['field'])) $_GET['field'] = "multi3";
if(!isset($_GET['field2'])) $_GET['field2'] = "multi3";
if(!isset($_GET['field3'])) $_GET['field3'] = "multi3";
if(!isset($_GET['field4'])) $_GET['field4'] = "multi3";
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
$listPaging = new listPaging('mallRN_order_goods'); 
$listPaging->search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'pay_type' => 2, 'pay_status' => 2, 'mobile' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'order_num' => 'function|status|name|id', 'g_image' => 'function|g_uid', 'g_name' => '', 'option_name' => '', 'qty' => 'number', 'orig_price' => 'function|qty|delivery_type|delivery_type_qty|delivery_price|order_num|delivery_add_price|option|g_uid', 'delivery_info' => 'function|status|status_date', 'status' => 'function|status2', 'pay_type' => 'array', 'pay_status' => 'array', 'g_uid' => '', 'signdate' => 'datetime');
$listPaging->field_where		= array('id', 'name', 'cell', 'email', 'name2', 'cell2', 'bank_info');
if(strlen($status)) $listPaging->default_where = "vendor = '{$v_my_id}' && status = '{$status}' && reals = 1";
else				$listPaging->default_where = "vendor = '{$v_my_id}' && reals = 1";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= array('order_num', 'delivery_info', 'g_name', 'g_uid', 'g_code');
$multi3_array			= array('id', 'name', 'cell', 'email', 'name2', 'cell2');
$status_array			= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array			= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
$pay_type_array			= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array		= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$MEMBER_NAME	= "";
$MEMBER_ID		= "";
$MEMBER_LVEL	= "";
$DISABLED		= "";
$tmp_order_num	= "";
$BGCOLOR		= "#fff";
$sum_delivery_option = array();

$order_num_function = function ($vls, $status, $name, $id) {
	global $mysql, $tpl, $level_array;

	if($GLOBALS['tmp_order_num'] == "" || $GLOBALS['tmp_order_num'] != $vls) {
		if($GLOBALS['BGCOLOR'] == "#fff")	$GLOBALS['BGCOLOR'] = "#f3f3f3";
		else								$GLOBALS['BGCOLOR'] = "#fff";
		$GLOBALS['tmp_order_num'] = $vls;
	}	
	
	if($id) {
		$GLOBALS['MEMBER_ID']		= $id;
		$tpl->parse("is_member");
	}
	$GLOBALS['NAME']		= stripslashes($name);
	
	if($status == 0 || $status > 3)	$GLOBALS['DISABLED'] = "disabled";
	else							$GLOBALS['DISABLED'] = "";

	return $vls;	
};

$g_image_function = function ($vls, $g_uid) {	
	global $mysql;

	$sql	= "SELECT image3, moddate FROM mallRN_goods WHERE uid = '{$g_uid}'";
	$data	= $mysql->one_row($sql);

	if($data['image3'])	$G_IMAGE = IMAGE_FOLDER."{$data['image3']}?t=".$data['moddate'];
	else				$G_IMAGE = "../../image/no_image.png";

	return $G_IMAGE;
};

$DELIVERY_PRICE		= 0;
$DELIVERY_CK_ARRAY	= array();

$orig_price_function = function ($vls, $qty, $delivery_type, $delivery_type_qty, $delivery_price, $order_num, $delivery_add_price, $option, $g_uid) {	
	global $mysql, $tpl, $v_my_id, $DELIVERY_CK_ARRAY;
	
	$delivery_price += $delivery_add_price;
	if($delivery_price) {
		if($delivery_type == 5) {
			if($option) {								
				if(isset($GLOBALS['sum_delivery_option'][$order_num."_".$g_uid]) && $GLOBALS['sum_delivery_option'][$order_num."_".$g_uid] > 0) {
					$DELIVERY_PRICE	= 0;					
				}
				else {
					$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$g_uid}' && !(status = 9 && status2 = 5)";
					$option_qty			= $mysql->get_one($sql);
					$DELIVERY_PRICE		= $delivery_price * ceil($option_qty / $delivery_type_qty);
					$GLOBALS['sum_delivery_option'][$order_num."_".$g_uid] = $option_qty;
				}				
			}
			else {
				$DELIVERY_PRICE	= $delivery_price * ceil($qty / $delivery_type_qty);
			}
			$GLOBALS['DELIVERY_PRICE'] = number_format($DELIVERY_PRICE);
		}	
		else $GLOBALS['DELIVERY_PRICE'] = number_format($delivery_price);	
	}
	else {
		$GLOBALS['DELIVERY_PRICE'] = 0;

		if(!in_array($order_num, $DELIVERY_CK_ARRAY)) {
			$sql	= "SELECT price FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$v_my_id}'";
			if($price = $mysql->get_one($sql)) $GLOBALS['DELIVERY_PRICE'] = number_format($price);
			$DELIVERY_CK_ARRAY[]	= $order_num;
		}
	}

	return number_format($vls * $qty);
};

$status_function = function ($vls, $status2) {	
	global $status_array, $status2_array;

	if($status2)	return $status_array[$vls].$status2_array[$status2];
	else			return $status_array[$vls];
};

$delivery_url		= "";
$delivery_number	= "";
$DELIVERY_INFO		= "";

$delivery_info_function = function ($vls, $status, $status_date) {
	global $tpl, $delivery_info_array, $delivery_url_array; 

	if(!$vls) return;

	if($status_date < time() - (86400 * 7))	return;	
	
	$tmps						= explode("|", $vls);
	$GLOBALS['DELIVERY_INFO']	= $delivery_info_array[$tmps[0]]." : ".$tmps[1];
	$GLOBALS['delivery_url']	= $delivery_url_array[$tmps[0]];
	$GLOBALS['delivery_number']	= str_replace(array("-", " ", "\n", "\r"), "", $tmps[1]);
	$tpl->parse("is_delivery_info");
};
######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$where2 = "";
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.{$GLOBALS['date_type']}) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.{$GLOBALS['date_type']}) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};

$pay_type_where = function ($vls) {
	$GLOBALS['where2'] .= "&& pay_type = '{$vls}'";
};

$pay_status_where = function ($vls) {
	$GLOBALS['where2'] .= "&& pay_staus = '{$vls}'";
};

$mobile_where = function ($vls) {
	$GLOBALS['where2'] .= "&& mobile = '{$vls}'";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## 독립 쿼리 사용시 함수 정의 #############################
$idFieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(id, '{$vls}')";
};

$cellFieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(cell, '{$vls}')";
};

$emailFieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(email, '{$vls}')";
};

$name2FieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(name2, '{$vls}')";
};

$cell2FieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(cell2, '{$vls}')";
};

$nameFieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(name, '{$vls}')";
};

$bank_infoFieldWhere = function ($vls) {
	global $listPaging;
	$GLOBALS['where2'] .= "&& INSTR(bank_info, '{$vls}')";
};

$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {	
	global $where2;
	
	return "SELECT COUNT(*) FROM mallRN_order_goods a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_info WHERE reals = 1 {$where2} ) {$where}";
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	global $where2;

	if($where) $where = str_replace("a.", "c.", $where);

	return "SELECT c.*, b.name, b.id, b.pay_type, b.pay_status FROM ( SELECT a.order_num, a.name, a.id, a.pay_type, a.pay_status FROM mallRN_order_info a WHERE reals = 1 {$where2} ) b JOIN mallRN_order_goods c ON b.order_num = c.order_num {$where} ORDER BY c.{$GLOBALS['sort']} LIMIT {$start_record}, {$page_record_num}";	
};
######################## 독립 쿼리 사용시 함수 정의 #############################

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

if($v_my_delivery_type == 0) {
	$tpl->parse("is_delivery1");
	$tpl->parse("is_delivery2");
}

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>