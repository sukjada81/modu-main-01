<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$sql = "UPDATE mallRN_cart SET direct = '0' WHERE cart_id = '{$cart_id}'";
$mysql->query($sql);

$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_cart WHERE cart_id = '{$cart_id}' ORDER BY contact DESC, vendor_delivery ASC";
$mysql->query($sql);

$TOTAL						= 0;
$G_DELIVERY_TYPE4_CK_ARRAY	= array();
$LIMIT_QTY					= "";

while($row = $mysql->fetch_array()) {
	$VENDOR = $row['vendor_delivery'];
	$vendor_sell = 0;
	if(!$VENDOR) {
		$VENDOR_NAME = stripslashes($shop_config['basic_name']);
		
		if($shop_config['delivery_type'] == 'F')	$DELIVERY_MESSAGE	= "무료배송";
		else if($shop_config['delivery_type']=='D') $DELIVERY_MESSAGE	= "착불일 경우 예상금액 ".number_format($shop_config['delivery_d_price'])."원";
		else {
			$DELIVERY_MESSAGE = number_format($shop_config['delivery_p_price2'])."원 (조건부 무료상품 ".number_format($shop_config['delivery_p_price1'])."원 이상 구매시 무료)";
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
		
		$DELIVERY_ADD_MESSAGE = "";
		if($vshop_config['delivery_im_areas1_used'] == 1) $DELIVERY_ADD_MESSAGE .= "제주 추가 ".number_format($vshop_config['delivery_im_areas1_price'])."원";
		if($vshop_config['delivery_im_areas2_used'] == 1) {
			if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE .= ", ";			
			$DELIVERY_ADD_MESSAGE .= "제주 외 도서지역 추가 ".number_format($vshop_config['delivery_im_areas2_price'])."원";
		}

		$VENDOR_NAME		= ($vshop_config['basic_name'])	 ? stripslashes($vshop_config['basic_name']) : stripslashes($vinfo['comp_name']);
	}
	
	if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE = " / ".$DELIVERY_ADD_MESSAGE;

	$sql = "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' && vendor_delivery = '{$VENDOR}' ORDER BY vendor_delivery ASC, uid DESC";
	$mysql->query2($sql);

	$vendor_checked = 1;

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
		if($PRICE > 0)	$SUM_PRICE = number_format((str_replace(",", "", $PRICE) * $QTY), CONF_FLOAT_CNT);
		else			$SUM_PRICE = 0;
		$DELIVERY			= $data['delivery'];
		$G_DELIVERY_TYPE	= $data['delivery_type'];
		$G_DELIVERY_TYPE_QTY = $data['delivery_type_qty'];
		$G_DELIVERY_PRICE	= $data['delivery_price'];
		$MILEAGE			= $data['mileage'];

		if($data['delivery_type'] == 4) {
			if(in_array($data['uid'], $G_DELIVERY_TYPE4_CK_ARRAY)) {
				$DELIVERY	= "묶음배송";
				$G_DELIVERY_PRICE = 0;
			}
			else $G_DELIVERY_TYPE4_CK_ARRAY[] = $data['uid'];
		}

		if($row2['selects'] == 1)	$CHECKED = "checked='checked'";
		else {
			$CHECKED = "";
			if($data['disabled'] == 0) $vendor_checked = 0;
		}
		
		if($data['delivery_add']) {
			$DELIVERY_ADD		= $data['delivery_add'];
			$tpl->parse("is_delivery_add");
		}
		
		$OPTION_USE = 0;
		if($G_OPTION) {
			$OPTION_USE		= 1;
			$tpl->parse("is_option");
			@$tpl->parse("is_option_btn");
		}
		
		if($MILEAGE) {
			$MILEAGES = number_format($MILEAGE * $QTY, CONF_FLOAT_CNT);
			$tpl->parse("is_mileage");
		}
		
		$ORIG_PRICE = 0;
		if($SALE_PRICE > 0) {
			$ORIG_PRICE			= number_format((str_replace(",", "", $PRICE) + $SALE_PRICE), CONF_FLOAT_CNT);
			$SUM_ORIG_PRICE		= number_format((str_replace(",", "", $ORIG_PRICE) * $QTY), CONF_FLOAT_CNT);
			$tpl->parse("is_orig_price");
		}

		if($COUPON_PRICE > 0) $tpl->parse("is_coupon");

		if($my_discount || $data['event_discount']) {
			$DISCOUNT	= $my_discount + $data['event_discount'];
			$tpl->parse("is_discount");
		}
		
		$DISABLED = "";
		if($data['disabled'] == 1 || $vendor_sell == 1) {
			$CHECKED	= "";
			$DISABLED	= "disabled";
			$tpl->parse("is_soldout");
		}
		
		$WISH_SELECT = "";
		if($my_id) {
			$sql = "SELECT count(*) FROM mallRN_favorite_goods WHERE id = '{$my_id}' && g_uid = '{$G_UID}'";
			if($mysql->get_one($sql) > 0) $WISH_SELECT = "wishSelect";
		}

		if($data['limit_qty']) $LIMIT_QTY	= 1;

		$tpl->parse("loop_cart_goods");

		$TOTAL ++;
	}

	if($vendor_checked == 1)	$VENDOR_CHECKED = "checked='checked'";
	else						$VENDOR_CHECKED = "";
	
	$tpl->parse("loop_cart");	
}

if($TOTAL == 0) $tpl->parse("empty_list");

if(!$my_id) $tpl->parse("is_login");

######################## 네이버페이 버튼 #############################
if($shop_config['naverpay_used']) {
	$naverpay_key2		= $shop_config['naverpay_key2'];
	$naver_enable		= 'Y';	
	if($LIMIT_QTY) $naver_enable	= 'N';
	$tpl->parse("is_naver_pay");
}
######################## 네이버페이 버튼 #############################

?>