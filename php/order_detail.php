<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$order_num = checkGetVar('order_num');
if(!$order_num) alert("필수 정보가 넘어오지 못했습니다.", "back");

if($my_id) {
	$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && id = '{$my_id}' && reals = 1";
}
else {
	if(!isset($_COOKIE['guestOrder1'])) alert("먼저 로그인을 하시기 바랍니다.", "back");
	
	$guestOrder1	= base64_decode($_COOKIE['guestOrder1']);
	$tmps			= explode("|", $guestOrder1);
	$cell			= $tmps[0];
	$name			= $tmps[1];
	$passwd			= previlDecode($_COOKIE['guestOrder2']);

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE name = '{$name}' && cell = '{$cell}' && passwd = '{$passwd}' && order_num = '{$order_num}' && reals = 1 ORDER BY uid DESC";
	if($mysql->get_one($sql) == 0) {
		alert("일치하는 정보가 존재 하지 않습니다.", "back");
	}	
	$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
}
if(!$info = $mysql->one_row($sql)) {
	alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');
}

######################## 파라미터 링크 추가  #########################
$addstring			= "";
$search_variable	=  array('status', 's_date', 'e_date', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}
######################## 파라미터 링크 추가  #########################

$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor_delivery ASC";
$mysql->query($sql);

$TOTAL			= 0;
$SUM_PRICES		= 0;
$SUM_DISCOUNT	= 0;
$SUM_DELIVERY	= 0;
$SUM_TOTAL		= 0;
$SUM_MILEAGE	= 0;
$ADD_DELIVERY	= 0;
$status_array	= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array	= array("0" => "", "1" => "요청", "2" => "회수중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 

while($row = $mysql->fetch_array()) {
	$VENDOR				= $row['vendor_delivery'];

	$sql	= "SELECT * FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$VENDOR}'";
	$data	= $mysql->one_row($sql);
	$delivery_config = explode("|", $data['info']);

	$ADD_DELIVERY += $data['adds'];

	if($delivery_config[0] == 'F') $DELIVERY_MESSAGE	= "무료배송";
	else if($delivery_config[0] == 'D') $DELIVERY_MESSAGE	= "착불 (예상금액 : ".number_format($delivery_config[3])."원)";
	else $DELIVERY_MESSAGE	= number_format($delivery_config[2])."원 (주문금액 ".number_format($delivery_config[1])."원 이상 구매시 무료)";
	
	$DELIVERY_ADD_MESSAGE = "";
	if(@$delivery_config[4] == 1) $DELIVERY_ADD_MESSAGE .= "제주 추가 ".number_format($delivery_config[5])."원";
	if(@$delivery_config[6] == 1) {
		if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE .= ", ";			
		$DELIVERY_ADD_MESSAGE .= "제주 외 도서지역 추가 ".number_format($delivery_config[7])."원";
	}
	if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE = " / ".$DELIVERY_ADD_MESSAGE;
	
	if(!$VENDOR) {
		$VENDOR_NAME = stripslashes($shop_config['basic_name']);
	}
	else {
		$sql	= "SELECT comp_name FROM mallRN_vendor WHERE id = '{$VENDOR}'";
		$vinfo	= $mysql->one_row($sql);
		$VENDOR_NAME = stripslashes($vinfo['comp_name']);	
		
		$sql			= "SELECT basic_name FROM mallRN_vendor_configuration WHERE vendor = '{$VENDOR}'";
		$vshop_config	= $mysql->one_row($sql);

		$VENDOR_NAME		= ($vshop_config['basic_name'])	 ? stripslashes($vshop_config['basic_name']) : stripslashes($vinfo['comp_name']);
	}

	$sql = "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && vendor_delivery = '{$VENDOR}' && reals = 1 ORDER BY vendor_delivery ASC, uid DESC";
	$mysql->query2($sql);

	$VENDOR_PRICE				= 0;
	$VENDOR_DISCOUNT			= 0;
	$VENDOR_DELIVERY			= 0;
	$VENDOR_TOTAL				= 0;
	
	while($row2 = $mysql->fetch_array(2)) {
		
		$UID				= $row2['uid'];
		$QTY				= $row2['qty'];
		$G_UID				= $row2['g_uid'];

		$sql	= "SELECT cate, image3 FROM mallRN_goods WHERE uid = '{$G_UID}'";
		$gdata	= $mysql->one_row($sql);

		if($gdata) {
			if($gdata['image3'])	$G_IMAGE = DEFAULT_PATH."image/goods/img{$gdata['image3']}";
			else					$G_IMAGE = DEFAULT_PATH."image/no_image.png";
			$G_LINK				= "{$Main}?channel=view&uid={$G_UID}&cate={$gdata['cate']}";	
		}
		else {
			$G_IMAGE			= DEFAULT_PATH."image/no_image.png";
			$G_LINK				= "";
		}

		$G_NAME				= stripslashes($row2['g_name']);		
		$OPTION				= stripslashes($row2['option']);
		$G_OPTION			= stripslashes($row2['option_name']);
		$PRICE				= number_format($row2['price'], CONF_FLOAT_CNT);
		$SUM_PRICE			= number_format((str_replace(",", "", $PRICE) * $QTY), CONF_FLOAT_CNT);
		$SALE_PRICE			= $row2['use_coupon'] + $row2['discount'];

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
		
		if($row2['delivery_add_price'] > 0) {
			if($row2['delivery_type'] == 5) $DELIVERY_ADD	= number_format($row2['delivery_add_price'])."원(".number_format($row2['delivery_type_qty'])."개당)";
			else $DELIVERY_ADD	= number_format($row2['delivery_add_price'])."원";
			$tpl->parse("is_delivery_add");
		}
		
		$MILEAGE			= $row2['mileage'];
		if($row2['mileage']) {`
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

		if($row2['use_coupon']) {
			$USE_COUPON	= number_format(stripslashes($row2['use_coupon']));
			$tpl->parse("is_coupon");
		}

		if($row2['discount_info']) {
			$DISCOUNT_INFO	= stripslashes($row2['discount_info']);
			$tpl->parse("is_discount_info");
		}
		
		if($row2['status2'])	$STATUS	= $status_array[$row2['status']].$status2_array[$row2['status2']];
		else					$STATUS	= $status_array[$row2['status']];

		$QTY				= number_format($QTY);

		$tpl->parse("loop_order_goods");

		$row2['delivery_price'] += $row2['delivery_add_price'];
		$G_DELIVERY_PRICE	= $row2['delivery_price'];
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

$sum_total_orig = $SUM_PRICES - $SUM_DISCOUNT + $SUM_DELIVERY - $info['cancel_total'] - $info['refund_total'] - $info['use_mileage'] - $info['use_coupon'];
$SUM_TOTAL		= number_format($sum_total_orig, CONF_FLOAT_CNT);
$SUM_PRICES		= number_format($SUM_PRICES, CONF_FLOAT_CNT);
$SUM_DISCOUNT	= number_format($SUM_DISCOUNT, CONF_FLOAT_CNT);
$SUM_DELIVERY	= number_format($SUM_DELIVERY, CONF_FLOAT_CNT);	
$SUM_MILEAGE	= number_format($SUM_MILEAGE, CONF_FLOAT_CNT);	
$SUM_MILEAGE2	= number_format($info['use_mileage'], CONF_FLOAT_CNT);	
$SUM_COUPON		= number_format($info['use_coupon'], CONF_FLOAT_CNT);	
$SUM_CANCEL		= number_format($info['cancel_total'] + $info['refund_total'], CONF_FLOAT_CNT);	

$FIRST_PAY_TOTAL	= number_format($info['pay_total']);


if($ADD_DELIVERY) {
	$ADD_DELIVERY	= number_format($ADD_DELIVERY, CONF_FLOAT_CNT);	
	$tpl->parse("is_add_delivery");
}

if($shop_config['order_auto_completed3'] > 0) {
	$BANK_DATE = date("Y년 m월 d일", strtotime("+{$shop_config['order_auto_completed3']} DAY", $info['signdate']));
}

if($info['cash_receipts']) {
	$sql				= "SELECT status, status_date, receipt_no FROM mallRN_order_cash_receipts WHERE order_num = '{$order_num}'";
	if($cinfo = $mysql->one_row($sql)) {
	
		$cash_date			= date("Y-m-d", $cinfo['status_date']);
		$cash_tno			= $cinfo['receipt_no'];
		
		$tpl->parse("is_cash_status{$cinfo['status']}");
			
		if($cinfo['status'] == '3') $tpl->parse("is_btn_cash_bill");

		$tpl->parse("is_cash_info");	
		unset($cash_status, $cash_date, $cstatus_array, $cinfo);
	}
}

switch($info['pay_type']) {
	case "B" :
		if($info['status_date']) {
			$PAY_DATE = date("Y년 m월 d일", $info['status_date']);
			$tpl->parse("is_pay_ok");
		}
		else {
			$tmps		= explode("|", stripslashes($info['bank_info']));
			$BANK_INFO1	= $tmps[0];
			$BANK_INFO2	= $tmps[1];

			if($BANK_DATE) $tpl->parse("is_bank_date");
			
			$tpl->parse("is_pay_ing");
		}	

		$tpl->parse("is_pay_type_B");
	break;

	case "V" :
		if($info['status_date']) {
			$PAY_DATE = date("Y년 m월 d일", $info['status_date']);
			$tpl->parse("is_pay_ok2");
		}
		else {
			$tmps		= explode(",", stripslashes($info['pay_info']));
			$tmps1		= explode(" : ", stripslashes($tmps[0]));
			$tmps2		= explode(" : ", stripslashes($tmps[1]));
			$tmps3		= explode(" : ", stripslashes($tmps[2]));
			
			$BANK_INFO1	= $tmps1[1];
			$BANK_INFO2	= $tmps2[1];
			$BANK_INFO3	= $tmps3[1];

			if($BANK_DATE) $tpl->parse("is_bank_date2");

			$tpl->parse("is_pay_ing2");
		}

		$tpl->parse("is_pay_type_V");
	break;
	case "C" : 
		$ck_cancel	= 0;
		if($info['pay_info']) {
			$sql  = "SELECT price, status, signdate FROM mallRN_order_cancel_cp_log WHERE order_num = '{$order_num}' ORDER BY uid ASC";
			$mysql->query($sql);			
			
			while($row = $mysql->fetch_array()){
				$CANCEL_PRICE	= number_format($row['price']);
				$CANCEL_DATE	= date("Y년 m월 d일", $row['signdate']);

				$tpl->parse("loop_cancel_ok{$row['status']}");
				$ck_cancel = 1;
			}
		}
		
		$tno	= $info['pay_number'];
		if($ck_cancel == 0) $tpl->parse("is_btn_card_bill");

	case "M" : case "R" : case "H" :
		if($info['pay_type'] == 'M')	$PAY_INFO = "마일리지 사용";
		else							$PAY_INFO	= stripslashes($info['pay_info']);
		
		$PAY_DATE = date("Y년 m월 d일", $info['status_date']);

		$tpl->parse("is_pay_type_CP");	
	break;
}

switch($shop_config['payment_cp']) {
	case "KCP" :
		if($shop_config['payment_shop_id'] == "T0007")	$bill_url = "test";
		else											$bill_url = "";	

		$tpl->parse("is_card_bill_kcp");
		$tpl->parse("is_cash_bill_kcp");

	break;
	case "NICEPAY" :
		$tpl->parse("is_card_bill_nicepay");
		$tpl->parse("is_cash_bill_nicepay");
	break;

	case "INICIS" :
		$tpl->parse("is_card_bill_inicis");
		$tpl->parse("is_cash_bill_inicis");
	break;
}

$item_array		= array('name2', 'cell2', 'postcode', 'address1', 'address2', 'message');

foreach($item_array as $k => $v) {
	${$v} = stripslashes($info[$v]);
}

if($message) $tpl->parse("is_message");

?>