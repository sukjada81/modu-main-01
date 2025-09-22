<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

######################## 배송업체 정보 #############################
$delivery_info_array	= array();
$delivery_url_array		= array();

if($shop_config['delivery_info']) {
	$delivery_info = explode("|*|", $shop_config['delivery_info']);	
	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

			$delivery_info2 = explode("|", $delivery_info[$i]);
			
			if($delivery_info2[3] == 0) continue;

			$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];
			$delivery_url_array[$delivery_info2[0]] = $delivery_info2[2];
		}
	}
}
######################## 배송업체 정보 #############################

######################## 변수 정의 #############################
if(!isset($_GET['sort']))	$_GET['sort']	= "uid DESC";
if(!isset($_GET['limit']))  $_GET['limit']	= "10";	
if(isset($_GET['s_date'])) {
	if($_GET['s_date'] && !$_GET['e_date']) $_GET['e_date'] = date("Y-m-d");
}

$ck_status	= checkGetVar('status');
$DATE1		= date("Y-m-d");
$DATE2		= date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3		= date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4		= date('Y-m-d', strtotime('-1 MONTH', time()));
$DATE5		= date('Y-m-d', strtotime('-3 MONTH', time()));
$DATE6		= date('Y-m-d', strtotime('-6 MONTH', time()));
####################### 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging						= new listPaging('mallRN_order_info'); 
$listPaging->search_variable	= array('status' => 1, 's_date' => 5, 'e_date' => 2, 'sort' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'signdate' => 'datetime', 'order_num' => 'function|status|signdate|uid', 'g_uid' => 'function|option_name|use_coupon|uid|status', 'g_name' => '', 'price' => 'function|qty|delivery_type|delivery_type_qty|delivery_price|delivery_add_price|option|g_uid|order_num', 'qty' => 'number', 'status'=>'function|status2|status_date|order_num', 'delivery_info' => 'function|status|status2', 'signdate' => 'datetime');
$listPaging->defaultParam		= "channel={$channel}";
######################## listPaging 정의 #############################

######################## list_variable : array일 경우 정의 #############################
$multi_array			= "";
$status_array			= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array			= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
######################## list_variable : array일 경우 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$tmp_order_num	= "";
$ROWSPAN		= "";
$ORDER_NUM2		= "";
$SIGNDATE2		= "";
$START_UID		= "";
$START_OID		= "";
$proc_status	= "";

$order_num_function = function ($vls, $status, $signdate, $uid) {
	global $mysql, $tpl, $ck_status;

	if(!$GLOBALS['START_UID']) $GLOBALS['START_UID'] = $uid;

	if($GLOBALS['tmp_order_num'] == "" || $GLOBALS['tmp_order_num'] != $vls) {

		if(!$GLOBALS['START_OID']) $GLOBALS['START_OID'] = $vls;

		if($GLOBALS['START_OID'] != $vls) $add_queryS	= "";
		else $add_queryS = " && uid <= {$GLOBALS['START_UID']}";
		
		$add_query	= "";
		if(strlen($ck_status)) $add_query = "&& status = '{$ck_status}'";
		
		$sql	= "SELECT count(*) as cnt FROM mallRN_order_goods WHERE order_num = '{$vls}' && reals = 1 {$add_queryS} {$add_query}";
		$cnts	= $mysql->one_row($sql);		
		$GLOBALS['ROWSPAN'] = $cnts['cnt'];
		$GLOBALS['ORDER_NUM2'] = $vls;
		$GLOBALS['SIGNDATE2'] = date("Y.m.d H:i", $signdate);

		$sql	= "SELECT count(distinct(status)) FROM mallRN_order_goods WHERE order_num = '{$vls}'";
		if($mysql->get_one($sql) == 1) {
			$sql	= "SELECT pay_status, cancel_total, refund_total, pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$vls}' && reals = 1";
			$data	= $mysql->one_row($sql);

			if($status < 2) {
				if($data['pay_status'] != 'C') {
					$GLOBALS['proc_status'] = "";
					$tpl->parse("is_btn_cancel_all");
				}
				else {				
					if($data['pay_type'] == 'M' || $data['pay_type'] == 'C' || ($data['pay_type'] == 'R' &&  date("Y-m-d", $data['status_date']) == date("Y-m-d"))) {
						$GLOBALS['proc_status'] = "5";
						$tpl->parse("is_btn_cancel_all");
					}
					else $tpl->parse("is_btn_cancel_all2");
				}
			}
		}
		
		$tpl->parse("is_order_info");
		$GLOBALS['tmp_order_num'] = $vls;
	}	

	return $vls;
};

$G_UID2			= "";
$G_IMAGE		= "";
$G_LINK			= "";
$G_OPTION		= "";
$USE_COUPON		= "";

$g_uid_function = function ($vls, $option_name, $use_coupon, $uid, $status) {
	global $mysql, $tpl, $Main, $my_id;

	$sql	= "SELECT cate, image3 FROM mallRN_goods WHERE uid = '{$vls}'";
	$data	= $mysql->one_row($sql);

	if($data) {	
		if($data['image3']) $GLOBALS['G_IMAGE'] = DEFAULT_PATH."image/goods/img{$data['image3']}";
		else				$GLOBALS['G_IMAGE'] = DEFAULT_PATH."image/no_image.png";
		$GLOBALS['G_LINK']	= "{$Main}?channel=view&uid={$vls}&cate={$data['cate']}";	
		$GLOBALS['G_UID2']	= $vls;
	}
	else {
		$GLOBALS['G_IMAGE']	= DEFAULT_PATH."image/no_image.png";
		$GLOBALS['G_LINK']	= "";
		$GLOBALS['G_UID2']	= "";
	}

	if($option_name) {
		$GLOBALS['G_OPTION'] = $option_name;
		$tpl->parse("is_option");		
	}

	if($use_coupon) {
		$GLOBALS['USE_COUPON'] = number_format($use_coupon);
		$tpl->parse("is_coupon");		
	}
	
	if($status == 4 || $status == 5) {
		$sql = "SELECT count(*) FROM mallRN_review WHERE og_uid = '{$uid}'";
		if($mysql->get_one($sql) ==0) $tpl->parse("is_btn_review");
	}
	
	return $vls;
};

$DELIVERY_PRICE			= "";
$DELIVERY_ADD_PRICE		= 0;


$price_function = function ($vls, $qty, $delivery_type, $delivery_type_qty, $delivery_price, $delivery_add_price, $option, $g_uid, $order_num) {	
	global $mysql, $tpl;
	
	if($delivery_price) {
		if($delivery_type == 5) {
			if(!$delivery_type_qty) $delivery_type_qty = 1;
			if($option) {				
				if(isset($GLOBALS['sum_delivery_option'][$order_num."_".$g_uid]) && $GLOBALS['sum_delivery_option'][$order_num."_".$g_uid] > 0) {
					$GLOBALS['DELIVERY_PRICE']	= 0;	
					$option_qty = 0;
				}
				else {
					$sql						= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$g_uid}' && !(status = 9 && status2 = 5)";
					$option_qty					= $mysql->get_one($sql);
					$GLOBALS['DELIVERY_PRICE']	= number_format($delivery_price * ceil($option_qty / $delivery_type_qty));
					$GLOBALS['sum_delivery_option'][$order_num."_".$g_uid] = $option_qty;
				}
			}
			else {
				$GLOBALS['DELIVERY_PRICE'] = number_format($delivery_price  * ceil($qty / $delivery_type_qty));
			}
		}
		else $GLOBALS['DELIVERY_PRICE'] = number_format($delivery_price);

		if($delivery_add_price > 0) {
			if($delivery_type == 5) {
				if($option) {				
					if($option_qty == 0) {
						$GLOBALS['DELIVERY_ADD_PRICE']	= 0;					
					}
					else {
						$GLOBALS['DELIVERY_ADD_PRICE']	= number_format($delivery_add_price  * ceil($option_qty / $delivery_type_qty));
					}
				}
				else {
					$GLOBALS['DELIVERY_ADD_PRICE'] = number_format($delivery_add_price  * ceil($qty / $delivery_type_qty));
				}								
			}
			else $GLOBALS['DELIVERY_ADD_PRICE'] = number_format($delivery_add_price);
			$tpl->parse("is_delivery_add_price");		
		}

		$tpl->parse("is_delivery_price");		
	}

	return number_format($vls * $qty);
};
$delivery_url		= "";
$delivery_number	= "";

$delivery_info_function = function ($vls, $status, $status2) {
	global $tpl, $delivery_info_array, $delivery_url_array;

	if(!$vls) return;

	$tmps			= explode("|", $vls);

	$GLOBALS['delivery_url']	= $delivery_url_array[$tmps[0]];
	$GLOBALS['delivery_number']	= str_replace(array("-", " ", "\n", "\r"), "", $tmps[1]);

	if($status == 3) $tpl->parse("is_btn_delivery");
	if($status == 7 && $status2 == 4) {
		$tpl->parse("is_btn_delivery");
		$tpl->parse("is_btn_recipiency");
	}	
};

$TTL_STATUS		= "";

$status_function = function ($vls, $status2, $status_date, $order_num) {
	global $mysql, $tpl, $status_array, $status2_array, $shop_config;
	
	if($vls == 0) $tpl->parse("is_btn_cancel");
	else if($vls == 1) {
		$sql	= "SELECT pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		$data	= $mysql->one_row($sql);
		
		if($data['pay_type'] == 'M' || $data['pay_type'] == 'C' || ($data['pay_type'] == 'R' &&  date("Y-m-d", $data['status_date']) == date("Y-m-d"))) {
			$sql = "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$order_num}'";
			if($mysql->get_one($sql) == 1)	$tpl->parse("is_btn_cancel3");
			else							$tpl->parse("is_btn_cancel2");
		}
		else $tpl->parse("is_btn_cancel2");
	}
	else if($vls == 2) {
		$tpl->parse("is_btn_cancel2");
	}
	else if($vls == 3) $tpl->parse("is_btn_recipiency");
	else if($vls == 4) $tpl->parse("is_btn_confirmation");

	if($vls == 3 || $vls == 4) {
		$tpl->parse("is_btn_exchange");
		$tpl->parse("is_btn_return");
	}

	if($vls == 4 || $vls == 5) {
		if($vls == 4) {
			$GLOBALS['TTL4'] = "예정";
			$GLOBALS['CONFIRM_DATE'] = date("Y-m-d", $status_date + (86400 * $shop_config['order_auto_completed2']));
		}
		else {
			$GLOBALS['TTL4'] = "";
			$GLOBALS['CONFIRM_DATE'] = date("Y-m-d", $status_date);
		}
		$tpl->parse("is_date_confirmation");
	}

	if($status2 == 1) {
		$GLOBALS['TTL_STATUS'] = $status_array[$vls];	
		$tpl->parse("is_btn_status_cancel");
	}

	if($status2)	return $status_array[$vls].$status2_array[$status2];
	else			return $status_array[$vls];

};

######################## list_variable : funtion일 경우 정의 #############################

######################## search_variable : 2, 3일 경우 함수 정의 #############################
$e_date_where = function ($vls) {
	if(!$GLOBALS['s_date']) return "&& from_unixtime(a.signdate) < '{$vls} 23:59:59' ";
	else return "&& from_unixtime(a.signdate) BETWEEN '{$GLOBALS['s_date']}' AND '{$vls} 23:59:59' ";
};
######################## search_variable : 2, 3일 경우 함수 정의 #############################

######################## 독립 쿼리 사용시 함수 정의 #############################
$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {	
	global $channel, $my_id, $guest_where;

	if($channel == "order_list_guest" && $guest_where)	$cwhere = $guest_where;
	else												$cwhere = "id = '{$my_id}'";					

	return "SELECT COUNT(*) FROM mallRN_order_goods a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_info WHERE {$cwhere} && reals = 1 ) {$where}";	
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	global $channel, $my_id, $guest_where;

	if($channel == "order_list_guest" && $guest_where)	$cwhere = $guest_where;
	else												$cwhere = "id = '{$my_id}'";

	if($where)	$where = str_replace("a.", "c.", $where)." && reals = 1";
	else		$where = "WHERE reals = 1";

	return "SELECT c.* FROM ( SELECT a.order_num FROM mallRN_order_info a WHERE {$cwhere} && reals = 1 ) b JOIN mallRN_order_goods c ON b.order_num = c.order_num {$where} ORDER BY c.order_num DESC, c.uid DESC, c.status ASC LIMIT {$start_record}, {$page_record_num}";		
};
######################## 독립 쿼리 사용시 함수 정의 #############################

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

?>