<?php 

include_once("../common/top.php");

define('IMAGE_FOLDER', '../../image/goods/img');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","order_info.html");
$tpl->scan_area("main");

$addstring			= "";
$search_variable	=  array('status', 'field', 'keyword','field2','keyword2','field3','keyword3','field4','keyword4', 'date_type', 's_date', 'e_date', 's_range1', 'e_range1', 'range1', 'member', 'mobile', 'pay_type', 'pay_status', 'cash_receipts', 'new', 'use_mileage', 'use_coupon', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$order_num = checkGetVar('order_num');
if(!$order_num) alert("필수 정보가 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
if(!$info = $mysql->one_row($sql)) {
	alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');
}

$delivery_info_array	= array();
$delivery_url_array		= array();
$sql = "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
if($delivery_info = $mysql->get_one($sql)){
	$delivery_info = explode("|*|", $delivery_info);

	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

			$delivery_info2 = explode("|", $delivery_info[$i]);
			
			if($delivery_info2[3] == 0) continue;

			$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];
			$delivery_url_array[$delivery_info2[0]] = $delivery_info2[2];
		}
	}
}

foreach($delivery_info_array as $k => $v) {
	$delivery_num	= $k;
	$delivery_name	= $v;
			
	$tpl->parse("loop_delivery");
}

$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor_delivery ASC";
$mysql->query($sql);

$TOTAL			= 0;
$SUM_PRICES		= 0;
$SUM_DISCOUNT	= 0;
$SUM_DELIVERY	= 0;
$SUM_TOTAL		= 0;
$SUM_MILEAGE	= 0;
$ORDER_PROCE	= 0;
$DISABLED		= "";
$status_array	= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array	= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
$ck_vendor		= 0;
$ck_hq			= 0;

while($row = $mysql->fetch_array()) {
	$VENDOR				= $row['vendor_delivery'];

	$sql	= "SELECT * FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$VENDOR}'";
	$data	= $mysql->one_row($sql);
	$delivery_config = explode("|", $data['info']);

	if($delivery_config[0] == 'F') $DELIVERY_MESSAGE	= "무료배송";
	else if($delivery_config[0] == 'D') $DELIVERY_MESSAGE	= "착불 (예상금액 : ".number_format($delivery_config[3])."원)";
	else $DELIVERY_MESSAGE	= number_format($delivery_config[2])."원 (주문금액 ".number_format($delivery_config[1])."원 이상 구매시 무료)";
	
	if(!$VENDOR) {
		$VENDOR_NAME	= "본사배송";
		$ck_hq			= 1;
	}
	else {
		$sql			= "SELECT comp_name, sell FROM mallRN_vendor WHERE id = '{$VENDOR}'";
		$vinfo			= $mysql->one_row($sql);
		$VENDOR_NAME	= stripslashes($vinfo['comp_name'])."배송";	
		$ck_vendor		= 1;
	}

	$tpl->parse("is_vendor_check1");

	$sql = "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && vendor_delivery = '{$VENDOR}' && reals = 1 ORDER BY vendor_delivery ASC, uid DESC";
	$mysql->query2($sql);

	$VENDOR_PRICE				= 0;
	$VENDOR_DISCOUNT			= 0;
	$VENDOR_DELIVERY			= 0;
	$VENDOR_TOTAL				= 0;
	$sum_delivery_option		= array();
	
	while($row2 = $mysql->fetch_array(2)) {
		
		$UID				= $row2['uid'];
		$QTY				= $row2['qty'];
		$G_UID				= $row2['g_uid'];

		$sql	= "SELECT cate, image3 FROM mallRN_goods WHERE uid = '{$G_UID}'";
		if(!$gdata	= $mysql->one_row($sql)) {
			$gdata['cate'] = "";
			$gdata['image3'] = "";
		}

		$G_LINK				= "{$SMain}?channel=view&uid={$G_UID}&cate={$gdata['cate']}";	
		$G_LINK2			= "../goods/goods_info.php?mode=modify&uid={$G_UID}";	
		$G_NAME				= stripslashes($row2['g_name']);
		if($gdata['image3'])	$G_IMAGE = IMAGE_FOLDER."{$gdata['image3']}";
		else					$G_IMAGE = "../../image/no_image.png";
		$G_OPTION			= stripslashes($row2['option_name']);
		$PRICE				= number_format($row2['price'], CONF_FLOAT_CNT);
		$SUM_PRICE			= number_format((str_replace(",", "", $PRICE) * $QTY), CONF_FLOAT_CNT);
		$SALE_PRICE			= $row2['use_coupon'] + $row2['discount'];
		$USE_COUPON			= number_format($row2['use_coupon'], CONF_FLOAT_CNT);

		switch($row2['delivery_type']) {
			case "1" : 
				if($delivery_config[0] == 'F')		$DELIVERY	= "무료배송";
				else if($delivery_config[0]=='D')	$DELIVERY	= "착불 (예상금액 : ".number_format($delivery_config[3])."원)";
				else								$DELIVERY	= "조건부 무료";			
			break;
			case "2" :
				$DELIVERY				= "무료배송";
			break;
			case "3" : 
				$DELIVERY				= "착불";
			break;
			case "4" :
				if($row2['delivery_price'] == 0)	$DELIVERY	= "묶음배송";
				else								$DELIVERY	= number_format($row2['delivery_price'])."원";		
			break;
			case "5" :
				$DELIVERY				= number_format($row2['delivery_price'])."원(".number_format($row2['delivery_type_qty'])."개당)";
			break;
		}
		$row2['delivery_price'] += $row2['delivery_add_price'];
		
		$G_DELIVERY_PRICE	= $row2['delivery_price'];
		$MILEAGE			= $row2['mileage'];
		
		if($row2['delivery_add_price'] > 0) {
			if($row2['delivery_type'] == 5) $DELIVERY_ADD	= number_format($row2['delivery_add_price'])."원(".number_format($row2['delivery_type_qty'])."개당)";
			else $DELIVERY_ADD	= number_format($row2['delivery_add_price'])."원";
			$tpl->parse("is_delivery_add");
		}

		if($row2['mileage']) {
			$MILEAGESONE = number_format($row2['mileage'] / $row2['qty']);
			$tpl->parse("is_mileage");
		}

		if($G_OPTION) $tpl->parse("is_option");
		
		$ORIG_PRICE = 0;
		if($SALE_PRICE > 0) {
			$ORIG_PRICE			= number_format((str_replace(",", "", $PRICE) + $SALE_PRICE), CONF_FLOAT_CNT);
			$SUM_ORIG_PRICE		= number_format((str_replace(",", "", $ORIG_PRICE) * $QTY), CONF_FLOAT_CNT);
			$tpl->parse("is_orig_price");

			$VENDOR_PRICE		+= str_replace(",", "", $SUM_ORIG_PRICE);
		}
		else $VENDOR_PRICE		+= str_replace(",", "", $SUM_PRICE);

		if($row2['use_coupon']) $tpl->parse("is_coupon");

		if($row2['discount_info']) {
			$DISCOUNT_INFO	= stripslashes($row2['discount_info']);
			$tpl->parse("is_discount_info");
		}
		
		if($row2['status2'])	$STATUS	= $status_array[$row2['status']].$status2_array[$row2['status2']];
		else					$STATUS	= $status_array[$row2['status']];

		if($row2['delivery_info']) {
			$tmps				= explode("|", $row2['delivery_info']);
			$DELIVERY_INFO		= $delivery_info_array[$tmps[0]]." : ".$tmps[1];
			$delivery_url		= $delivery_url_array[$tmps[0]];
			$delivery_number	= str_replace(array("-", " ", "\n", "\r"), "", $tmps[1]);
			$tpl->parse("is_delivery_info");
		}

		if($row2['status'] == 1 || $row2['status'] == 2) $tpl->parse("is_btn_status9");
		if($row2['status'] == 3 || $row2['status'] == 4 || $row2['status'] == 5) {
			$tpl->parse("is_btn_status8");
			$tpl->parse("is_btn_status7");
		}

		$STATUS_DATE	= date("m-d H:i", $row2['status_date']);

		if($row2['status'] == 0 || $row2['status'] > 3) $DISABLED = "disabled";
		else											$DISABLED = "";

		$tpl->parse("is_log");

		$tpl->parse("is_vendor_check2");

		$QTY				= number_format($QTY);
		
		$tpl->parse("loop_order_goods");

		if($row2['delivery_type'] == 5) {
			if($row2['option']) {				
				if(isset($sum_delivery_option[$G_UID]) && $sum_delivery_option[$G_UID] > 0) {
					$G_DELIVERY_PRICE	= 0;					
				}
				else {
					$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$G_UID}' && !(status = 9 && status2 = 5)";
					$option_qty			= $mysql->get_one($sql);
					$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($option_qty / $row2['delivery_type_qty']);
					$sum_delivery_option[$G_UID] = $option_qty;
				}
			}
			else {
				$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($row2['qty'] / $row2['delivery_type_qty']);
			}
		}

		$VENDOR_DISCOUNT	+= $SALE_PRICE * $row2['qty'];
		$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;
		$SUM_MILEAGE		+= $MILEAGE;
				
		$TOTAL ++;
	}
	
	if($data['price'] > 0) {
		$VENDOR_DELIVERY += $data['price'];
	}

	$SUM_PRICES			+= $VENDOR_PRICE;
	$SUM_DISCOUNT		+= $VENDOR_DISCOUNT;
	$SUM_DELIVERY		+= $VENDOR_DELIVERY;

	$VENDOR_TOTAL		= number_format($VENDOR_PRICE - $VENDOR_DISCOUNT + $VENDOR_DELIVERY, CONF_FLOAT_CNT);
	$VENDOR_PRICE		= number_format($VENDOR_PRICE, CONF_FLOAT_CNT);
	$VENDOR_DISCOUNT	= number_format($VENDOR_DISCOUNT, CONF_FLOAT_CNT);
	$VENDOR_DELIVERY	= number_format($VENDOR_DELIVERY, CONF_FLOAT_CNT);	
	
	$tpl->parse("loop_order");	
}

$SUM_TOTAL		= number_format($SUM_PRICES - $SUM_DISCOUNT + $SUM_DELIVERY - $info['cancel_total'] - $info['refund_total'] - $info['use_mileage'] - $info['use_coupon'], CONF_FLOAT_CNT);
$SUM_PRICES		= number_format($SUM_PRICES, CONF_FLOAT_CNT);
$SUM_DISCOUNT	= number_format($SUM_DISCOUNT, CONF_FLOAT_CNT);
$SUM_DELIVERY	= number_format($SUM_DELIVERY, CONF_FLOAT_CNT);	
$SUM_MILEAGE	= number_format($SUM_MILEAGE, CONF_FLOAT_CNT);	
$SUM_MILEAGE2	= number_format($info['use_mileage'], CONF_FLOAT_CNT);	
$SUM_COUPON		= number_format($info['use_coupon'], CONF_FLOAT_CNT);	
$SUM_CANCEL		= number_format($info['cancel_total'], CONF_FLOAT_CNT);	
$SUM_REFUND		= number_format($info['refund_total'], CONF_FLOAT_CNT);	

$item_array			= array('name', 'id', 'cell', 'email', 'name2', 'cell2', 'postcode', 'address1', 'address2', 'message', 'memo', 'pay_type', 'bank_info', 'pay_status', 'status_date', 'cash_receipts', 'signdate');
$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array	= array("A" => "미결제", "B" => "가상계좌발급완료", "C" => "결제완료", "D" => "결제실패");

foreach($item_array as $k => $v) {
	${$v} = stripslashes($info[$v]);
}

$message			= specialStrReplace2($message);
$address2			= specialStrReplace2($address2);
$order_date			= date("Y-m-d H:i:s", $signdate);

if(strlen($cell) == 12)			$cell	= substr($cell, 0, 4)."-".substr($cell, 4, 4)."-".substr($cell, 8, 4);
else if(strlen($cell) == 11)	$cell	= substr($cell, 0, 3)."-".substr($cell, 3, 4)."-".substr($cell, 7, 4);
else if(strlen($cell) == 10)	$cell	= substr($cell, 0, 2)."-".substr($cell, 2, 4)."-".substr($cell, 6, 4);

if(strlen($cell2) == 12)		$cell2	= substr($cell2, 0, 4)."-".substr($cell2, 4, 4)."-".substr($cell2, 8, 4);
else if(strlen($cell2) == 11)	$cell2	= substr($cell2, 0, 3)."-".substr($cell2, 3, 4)."-".substr($cell2, 7, 4);
else if(strlen($cell) == 10)	$cell2	= substr($cell2, 0, 2)."-".substr($cell2, 2, 4)."-".substr($cell2, 6, 4);

$pay_type			= $pay_type_array[$pay_type];
$pay_status			= $pay_status_array[$pay_status];
if($status_date) {
	$status_date	= date("Y-m-d H:i:s", $status_date);
	$tpl->parse("is_status_date");
}

$is_cancel = 0;
if($info['pay_total'] == 0) {
	if($info['use_mileage'] == 0) $is_cancel = 1;
}
else if($info['pay_total'] - $info['cancel_total'] - $info['refund_total'] == 0) $is_cancel = 1;

if($is_cancel == 1) $tpl->parse("is_delete");

switch($info['pay_type']) {
	case "B" :
		$tmps				= explode("|", $bank_info);
		$bank_info			= $tmps[0];
		$remittance_name	= $tmps[1];

		$tpl->parse("is_pay_type_B");

		if($info['pay_status'] == 'A' && $is_cancel == 0) {
			$tpl->parse("is_status1");
		}
	break;

	case "V" : case "C" : case "M" : case "R" : case "H" :
		if($info['pay_type'] == 'M')	$PAY_INFO = "마일리지 사용";
		else							$PAY_INFO	= stripslashes($info['pay_info']);
		
		$tpl->parse("is_pay_type_CP");	
	break;
}

if($info['cash_receipts']) {
	$cstatus_array		= array("0" => "발급요청", "1" => "발급거절", "2" => "발급실패", "3" => "발급완료", "4" => "발급취소"); 

	$sql				= "SELECT status, status_date FROM mallRN_order_cash_receipts WHERE order_num = '{$order_num}'";
	$cinfo				= $mysql->one_row($sql);

	$cash_status		= $cstatus_array[$cinfo['status']];
	$cash_date			= date("Y-m-d H:i:s", $cinfo['status_date']);
	$tpl->parse("is_cash_info");	
	unset($cash_status, $cash_date, $cstatus_array, $cinfo);
}

if($id) {
	$sql = "SELECT a.name FROM mallRN_member_level a, mallRN_member b WHERE a.level = b.level && b.id = '{$id}'";
	$member_level = stripslashes($mysql->get_one($sql));

	$MEMBER_ID		= $id;
	$MEMBER_LEVEL	= $member_level;

	$tpl->parse("is_member");
}

$sql = "SELECT * FROM mallRN_order_status_change WHERE order_num = '{$order_num}' && (status = 8 || status = 9) && status2 = 5 ORDER BY uid DESC";
$mysql->query($sql);

$re_num			= 1;
while($row = $mysql->fetch_array()){
	if($row['og_uid']) {
		$sql		= "SELECT g_name, option_name FROM mallRN_order_goods WHERE uid = '{$row['og_uid']}' && reals = 1";
		$g_info		= $mysql->one_row($sql);
		$re_name	= stripslashes($g_info['g_name']);

		if($g_info['option_name']) {
			$re_op_name	= stripslashes($g_info['option_name']);
			$tpl->parse("is_op_name");
		}
	}
	else $re_name = "전체상품";

	$re_goods_price		= ($row['refund'] + $row['mileage'] + $row['coupon'])  - $row['delivery'];
	$re_goods			= number_format($re_goods_price);
	$re_refund			= number_format($row['refund']);
	$re_delivery		= number_format($row['delivery']);	
	$re_delivery2		= number_format($row['delivery2']);
	$re_mileage			= number_format($row['mileage']);
	$re_coupon			= number_format($row['coupon']);
	$re_refund_fee		= number_format($row['refund_fee']);
	$re_refund_total	= number_format($re_goods_price + $row['delivery']);
	$re_refund_total2	= number_format($row['refund'] + $row['mileage'] + $row['coupon'] - $row['refund_fee'] - $row['delivery2']);
	$re_status_date		= date("Y-m-d H:i:s", $row['status_date']);
	
	$tpl->parse("loop_refund");
	$re_num ++;
}

if($re_num > 1) $tpl->parse("is_refund");

if($ck_hq == 1 && $ck_vendor == 1) $tpl->parse("is_print_hq");

####################### 관리자 로그 ##########################	
adminLog($my_id, "주문정보 - {$order_num}", 5);
####################### 관리자 로그 ##########################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>