<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$direct	= checkGetVar('direct');

if($direct) $where		= " && direct = 1";
else 		$where		= " && selects = 1";

############################### 상품수량 체크 ###################################
if($rtn = checkCartOrder($direct)) {
	if($rtn == 1) {
		if($direct == 1) {
			$sql = "SELECT count(*) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && direct = 1";
			if($mysql->get_one($sql) == 0)	alert("상품품절로 인해 주문 하실 수 없습니다.", "back");
			else							alert("상품재고수량 초과로 주문수량이 변경 되었습니다.", "{$Main}?channel=order&direct=1");
		}
		else {
			alert("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.", "{$Main}?channel=cart");
		}		
	}
	else if($rtn == 2) alert("선택된 장바구니 상품 정보가 없습니다.", "back");
}
############################### 상품수량 체크 ###################################

$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where} ORDER BY contact DESC, vendor_delivery ASC";
$mysql->query($sql);

$TOTAL						= 0;
$SUM_PRICES					= 0;
$SUM_DISCOUNT				= 0;
$SUM_DELIVERY				= 0;
$SUM_TOTAL					= 0;
$SUM_MILEAGE				= 0;
$CP_GOODS_NAME				= "";
$G_DELIVERY_TYPE4_CK_ARRAY	= array();

while($row = $mysql->fetch_array()) {
	$VENDOR				= $row['vendor_delivery'];
	$VENDOR_DELIVERY3	= "1"; //착불배송
	$vendor_sell = 0;
	if(!$VENDOR) {
		$VENDOR_NAME = stripslashes($shop_config['basic_name']);
		
		if($shop_config['delivery_type'] == 'F') {
			$DELIVERY_MESSAGE	= "무료배송";

		}
		else if($shop_config['delivery_type'] == 'D') $DELIVERY_MESSAGE	= "착불일 경우 예상금액 ".number_format($shop_config['delivery_d_price'])."원";
		else {
			$DELIVERY_MESSAGE	= number_format($shop_config['delivery_p_price2'])."원 (조건부 무료상품 ".number_format($shop_config['delivery_p_price1'])."원 이상 구매시 무료)";
		}

		$DELIVERY_TYPE		= $shop_config['delivery_type'];
		$DELIVERY_PRICE1	= $shop_config['delivery_p_price1'];
		$DELIVERY_PRICE2	= $shop_config['delivery_p_price2'];
		
		$DELIVERY_ADD_MESSAGE = "";
		if($shop_config['delivery_im_areas1_used'] == 1) $DELIVERY_ADD_MESSAGE .= "제주 추가 ".number_format($shop_config['delivery_im_areas1_price'])."원";
		if($shop_config['delivery_im_areas2_used'] == 1) {
			if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE .= ", ";			
			$DELIVERY_ADD_MESSAGE .= "제주 외 도서지역 추가 ".number_format($shop_config['delivery_im_areas2_price'])."원";
		}
	}
	else {
		$sql	= "SELECT comp_name, sell FROM mallRN_vendor WHERE id = '{$VENDOR}'";
		$vinfo	= $mysql->one_row($sql);
		
		if($vinfo['sell'] != 'A') $vendor_sell = 1;

		$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$VENDOR}'";
		$vshop_config	= $mysql->one_row($sql);

		if($vshop_config['delivery_type'] == 'F')		$DELIVERY_MESSAGE	= "무료배송";
		else if($vshop_config['delivery_type']=='D')	$DELIVERY_MESSAGE	= "착불일 경우 예상금액 ".number_format($vshop_config['delivery_d_price'])."원";
		else {
			$DELIVERY_MESSAGE = number_format($vshop_config['delivery_p_price2'])."원 (조건부 무료상품 ".number_format($vshop_config['delivery_p_price1'])."원 이상 구매시 무료)";
		}
		$DELIVERY_TYPE		= $vshop_config['delivery_type'];		
		$DELIVERY_PRICE1	= $vshop_config['delivery_p_price1'];
		$DELIVERY_PRICE2	= $vshop_config['delivery_p_price2'];		
		$VENDOR_NAME		= ($vshop_config['basic_name'])	 ? stripslashes($vshop_config['basic_name']) : stripslashes($vinfo['comp_name']);
		
		$DELIVERY_ADD_MESSAGE = "";
		if($vshop_config['delivery_im_areas1_used'] == 1) $DELIVERY_ADD_MESSAGE .= "제주 추가 ".number_format($vshop_config['delivery_im_areas1_price'])."원";
		if($vshop_config['delivery_im_areas2_used'] == 1) {
			if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE .= ", ";			
			$DELIVERY_ADD_MESSAGE .= "제주 외 도서지역 추가 ".number_format($vshop_config['delivery_im_areas2_price'])."원";
		}
	}
	
	if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE = " / ".$DELIVERY_ADD_MESSAGE;

	if($DELIVERY_TYPE != 'D') $VENDOR_DELIVERY3 = "0";

	$sql = "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' && vendor_delivery = '{$VENDOR}' {$where} ORDER BY vendor_delivery ASC, uid DESC";
	$mysql->query2($sql);

	$VENDOR_PRICE				= 0;
	$VENDOR_DISCOUNT			= 0;
	$VENDOR_DELIVERY			= 0;
	$VENDOR_TOTAL				= 0;
	$VENDOR_DELIVERY_CK_PRICE	= 0;
	$VENDOR_DELIVERY_FREE		= 0;
	$sum_delivery_option		= array();

	while($row2 = $mysql->fetch_array(2)) {
		$data				= getCartGoodsInfo($row2);
		
		$UID				= $row2['uid'];
		$QTY				= $data['qty'];
		$G_UID				= $data['uid'];
		$G_LINK				= $data['link'];
		$G_NAME				= $data['name'];
		$G_IMAGE			= $data['image'];
		$G_OPTION			= $data['option'];
		$PRICE				= $data['price'];
		$SUM_PRICE			= number_format((str_replace(",", "", $PRICE) * $QTY), CONF_FLOAT_CNT);
		$DELIVERY			= $data['delivery'];
		$G_DELIVERY_PRICE	= $data['delivery_price'];
		$MILEAGE			= $data['mileage']  * $QTY;

		if($data['delivery_type'] == 4) {
			if(in_array($data['uid'], $G_DELIVERY_TYPE4_CK_ARRAY)) {
				$DELIVERY	= "묶음배송";
				$G_DELIVERY_PRICE = 0;
			}
			else $G_DELIVERY_TYPE4_CK_ARRAY[] = $data['uid'];
		}
		
		if($data['delivery_add']) {
			$DELIVERY_ADD		= $data['delivery_add'];
			$tpl->parse("is_delivery_add");
		}

		if($G_OPTION) $tpl->parse("is_option");
		if($MILEAGE) {
			$MILEAGES = number_format($MILEAGE, CONF_FLOAT_CNT);
			$tpl->parse("is_mileage");
		}
		
		$ORIG_PRICE = 0;
		if($SALE_PRICE > 0) {
			$ORIG_PRICE			= number_format((str_replace(",", "", $PRICE) + $SALE_PRICE), CONF_FLOAT_CNT);
			$SUM_ORIG_PRICE		= number_format((str_replace(",", "", $ORIG_PRICE) * $QTY), CONF_FLOAT_CNT);
			$tpl->parse("is_orig_price");

			$VENDOR_PRICE		+= str_replace(",", "", $SUM_ORIG_PRICE);
		}
		else $VENDOR_PRICE		+= str_replace(",", "", $SUM_PRICE);

		if($COUPON_PRICE > 0) $tpl->parse("is_coupon");

		if($my_discount || $data['event_discount']) {
			$DISCOUNT	= $my_discount + $data['event_discount'];
			$tpl->parse("is_discount");
		}

		$QTY				= number_format($QTY);
		
		$tpl->parse("loop_cart_goods");

		if($data['delivery_type'] == 1 && $DELIVERY_TYPE == 'P') { 
			$VENDOR_DELIVERY_CK_PRICE += str_replace(",", "", $SUM_PRICE);
		}
		else if($data['delivery_type'] == 2) $VENDOR_DELIVERY_FREE = 1;
		else if($data['delivery_type'] == 5) {
			if($G_OPTION) {
				if(isset($sum_delivery_option[$G_UID]) && $sum_delivery_option[$G_UID] > 0) {
					$G_DELIVERY_PRICE	= 0;
				}
				else {
					$sql				= "SELECT SUM(qty) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && g_uid = '{$G_UID}' {$where}";
					$option_qty			= $mysql->get_one($sql);
					$G_DELIVERY_PRICE	= $data['delivery_price'] * ceil($option_qty / $data['delivery_type_qty']);
					$sum_delivery_option[$G_UID] = $option_qty;
				}
			}
			else {
				$G_DELIVERY_PRICE	= $data['delivery_price'] * ceil($row2['qty'] / $data['delivery_type_qty']);
			}
		}

		if($data['delivery_type'] != 3) $VENDOR_DELIVERY3 = "0";
		
		$VENDOR_DISCOUNT	+= $SALE_PRICE * $row2['qty'];
		$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;
		$SUM_MILEAGE		+= $MILEAGE;
				
		$TOTAL ++;		

		if(!$CP_GOODS_NAME) $CP_GOODS_NAME = $G_NAME;
	}
	
	if($VENDOR_DELIVERY_CK_PRICE && $VENDOR_DELIVERY_FREE == 0 && $DELIVERY_TYPE != 'F') {
		if($VENDOR_DELIVERY_CK_PRICE < $DELIVERY_PRICE1) {
			$VENDOR_DELIVERY += $DELIVERY_PRICE2;
		}
	}

	$SUM_PRICES			+= $VENDOR_PRICE;
	$SUM_DISCOUNT		+= $VENDOR_DISCOUNT;
	$SUM_DELIVERY		+= $VENDOR_DELIVERY;

	$VENDOR_TOTAL		= number_format($VENDOR_PRICE - $VENDOR_DISCOUNT + $VENDOR_DELIVERY, CONF_FLOAT_CNT);
	$VENDOR_PRICE		= number_format($VENDOR_PRICE, CONF_FLOAT_CNT);
	$VENDOR_DISCOUNT	= number_format($VENDOR_DISCOUNT, CONF_FLOAT_CNT);
	$VENDOR_DELIVERY	= number_format($VENDOR_DELIVERY, CONF_FLOAT_CNT);	
	
	$tpl->parse("loop_cart");	
}

$SUM_TOTAL		= number_format($SUM_PRICES - $SUM_DISCOUNT + $SUM_DELIVERY, CONF_FLOAT_CNT);
$SUM_PRICES		= number_format($SUM_PRICES, CONF_FLOAT_CNT);
$SUM_DISCOUNT	= number_format($SUM_DISCOUNT, CONF_FLOAT_CNT);
$SUM_DELIVERY	= number_format($SUM_DELIVERY, CONF_FLOAT_CNT);	
$SUM_MILEAGE	= number_format($SUM_MILEAGE, CONF_FLOAT_CNT);	

if($TOTAL == 0) $tpl->parse("empty_list");

if($shop_config['order_message_info']) {
	$message_info = explode("|*|", $shop_config['order_message_info']);
	foreach($message_info as $k => $v) {
		$message_info2	= explode("|",$v);
		if($message_info2[1] != '1') continue;
		$message		= specialStrReplace($message_info2[0]);
		$tpl->parse("loop_message");
	}	
	unset($message_info, $message_info2, $message);
}

if($my_id) {
	$sql				= "SELECT * FROM mallRN_member WHERE id = '{$my_id}'";
	$mdata				= $mysql->one_row($sql);
	
	$cell				= str_replace("-", "", stripslashes($mdata['cell']));
	$postcode			= stripslashes($mdata['postcode']);
	$address1			= stripslashes($mdata['address1']);
	$address2			= stripslashes($mdata['address2']);
	$mileage			= number_format($mdata['mileage']);
	$mileage_disabled	= "";
	if($mileage == 0) $mileage_disabled = "disabled";
	$ck_address = 0;
	
	$sql = "SELECT * FROM mallRN_coupon WHERE id = '{$my_id}' && g_uid = 0 && status = 0 && e_date > '".date("Y-m-d")."'";
	$mysql->query($sql);
	
	$ck_coupon = 0;
	while($row = $mysql->fetch_array()){ 
		
		$coupon_uid			= $row['uid'];
		$coupon_discount	= getCouponPrice(str_replace(",", "", $SUM_PRICES), $row['c_uid']);
		if($coupon_discount == 0) continue;
		$coupon_msg			= $coupon_message1;

		$tpl->parse("loop_coupon");
		$ck_coupon = 1;
	}

	if($ck_coupon == 1) {
		$coupon_ttl = "장바구니 쿠폰을 선택하세요.";
		$coupon_bg	= "#fff";
	}
	else {
		$coupon_ttl = "사용가능한 장바구니 쿠폰이 없습니다.";
		$coupon_bg	= "#efefef";
	}
	unset($coupon_discount, $coupon_message1, $coupon_msg, $ck_coupon);

	$sql = "SELECT * FROM mallRN_order_info WHERE id = '{$my_id}' && reals = 1 GROUP BY address1 ORDER BY uid ASC";
	$mysql->query($sql);

	$ck_address = 0;
	$ck = 0;
	while($row = $mysql->fetch_array()) {
		$recent_name		= stripslashes($row['name2']);
		$recent_cell		= stripslashes($row['cell2']);
		$recent_postcode		= stripslashes($row['postcode']);
		$recent_address1	= stripslashes($row['address1']);
		$recent_address2	= stripslashes($row['address2']);
		$ck ++;
		
		$tpl->parse("loop_recent_address");
		$ck_address = 1;
	}
	
	if($ck_address == 1) {
		$ck_address1 = "select";
		$tpl->parse("is_recent_address");
		$tpl->parse("is_recent_address2");
	}
	else $ck_address0 = "select";
	
	$tpl->parse("is_member1");
	$tpl->parse("is_member2");

}
else {
	$tpl->parse("is_guest1");
}

//if($shop_config['payment_escrow_c'] == 1) $tpl->parse("is_escrowC");
//if($shop_config['payment_escrow_r'] == 1) $tpl->parse("is_escrowR");
if($shop_config['payment_escrow_v'] == 1) $tpl->parse("is_escrowV");

if($shop_config['payment_type_b'] == 1) $tpl->parse("is_pay_typeB");
if($shop_config['payment_type_c'] == 1) $tpl->parse("is_pay_typeC");
if($shop_config['payment_type_r'] == 1) $tpl->parse("is_pay_typeR");
if($shop_config['payment_type_v'] == 1) $tpl->parse("is_pay_typeV");
if($shop_config['payment_type_h'] == 1) $tpl->parse("is_pay_typeH");

if($shop_config['payment_bank_info']) {
	$bank_info = explode("|*|", $shop_config['payment_bank_info']);
	foreach($bank_info as $k => $v) {
		$bank_info2 = explode("|", $v);		

		if($bank_info2[3] == 0) continue;

		$bank_infos	= $bank_info2[0]." ".$bank_info2[1]." ".$bank_info2[2];

		$tpl->parse("loop_bank");
	}
}

if($shop_config['cash_receipts_used'] == 1)	{
	$cash_receipts_require = "";
	if($shop_config['cash_receipts_require'] == 1) $cash_receipts_require = "required='required'";		

	$tpl->parse("is_cash_receipts");

}

if(!$my_id) {	
	$sql			= "SELECT agreement_info1, agreement_info4 FROM mallRN_configuration WHERE uid = 2";
	$member_config	= $mysql->one_row($sql);

	$AGREEMENT = add_escape_re_string($member_config['agreement_info1']);
	$AGREEMENT = str_replace("{COMPANY}",	stripslashes($shop_config['comp_name']),	$AGREEMENT);
	$AGREEMENT = str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$AGREEMENT);
	$AGREEMENT = str_replace("{SYEAR}",		date("Y", $shop_config['signdate']),		$AGREEMENT);
	$AGREEMENT = str_replace("{SMONTH}",	date("m", $shop_config['signdate']),		$AGREEMENT);
	$AGREEMENT = str_replace("{SDAY}",		date("d", $shop_config['signdate']),		$AGREEMENT);

	$PRIVACY = add_escape_re_string($member_config['agreement_info4']);

	$tpl->parse("is_guest2");
}

$cp_check	= "";
if($shop_config['payment_type_c'] == 1 || $shop_config['payment_type_r'] == 1 || $shop_config['payment_type_v'] == 1 || $shop_config['payment_type_h'] == 1) {
	
	if($shop_config['payment_cp'] && $shop_config['payment_shop_id'] && $shop_config['payment_shop_key']) {	
		if($TOTAL > 1) $CP_GOODS_NAME .= "외 ".($TOTAL - 1)."건";
		$payment_install_range = $shop_config['payment_install_range'];

		switch($shop_config['payment_cp']) {
			case "KCP" :				
				$cp_check = "kcp";				
			break;
			case "NICEPAY" :
				if(strlen($payment_install_range) == 1) $payment_install_range = "0".$payment_install_range;
				$cp_check = "nicepay";
			break;
			case "INICIS" :
				if($payment_install_range != '0') {
					$payment_install_range2 = array();
					for($i = 1; $i <= $payment_install_range; $i ++) {
						$payment_install_range2[] = $i;
					}
					$payment_install_range2 = join(":", $payment_install_range2);
				}
				else $payment_install_range2 = "";
				$cp_check = "inicis";
			break;
		}
	}
}

if($cp_check) {
	if($mobile_header != "") $tpl->parse("is_cp_HFrm");
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

if($cp_check) {
	if($mobile_header == "") include_once("plugin/{$cp_check}/order.php");
}

?>