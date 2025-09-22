<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$sql									= "SELECT member_mileage_order FROM mallRN_configuration WHERE uid = 2";		
$shop_config['member_mileage_order']	= $mysql->get_one($sql); 

$direct					= checkPostVar('direct');
$_POST['address1']		= checkPostVar('address1');

if($direct) $where		= " && direct = 1";
else 		$where		= " && selects = 1";

############################### 상품수량 체크 ###################################
if($rtn = checkCartOrder($direct)) {
	if($rtn == 1) {
		if($direct == 1) {
			$sql = "SELECT count(*) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && direct = 1";
			if($mysql->get_one($sql) == 0)	alertMsg("상품품절로 인해 주문 하실 수 없습니다.", "{$Main}?channel=cart");
			else							alertMsg("상품재고수량 초과로 주문수량이 변경 되었습니다.", "{$Main}?channel=order&direct=1");
		}
		else {
			alertMsg("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.", "{$Main}?channel=cart");
		}		
	}
	else if($rtn == 2) alertMsg("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
}
############################### 상품수량 체크 ###################################

############################### 주문금액 체크 ###################################
$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where} ORDER BY contact DESC, vendor_delivery ASC";
$mysql->query($sql);

$SUM_PRICES						= 0;
$SUM_DISCOUNT					= 0;
$SUM_DELIVERY					= 0;
$SUM_TOTAL						= 0;
$VENDOR_ARRAY					= array();
$VENDOR_CHECK_ARRAY				= array();
$VENDOR_DELIVERY_ARRAY			= array();
$VENDOR_DELIVERY_INFO_ARRAY		= array();
$VENDOR_DELIVERY_ADD_ARRAY		= array();
$VENDOR_COMMISSION_ARRAY		= array();
$G_DELIVERY_TYPE4_CK_ARRAY		= array();

while($row = $mysql->fetch_array()) {
	$VENDOR				= $row['vendor_delivery'];
	$VENDOR_ARRAY[]		= $VENDOR;

	$VENDOR_DELIVERY3	= "1"; //착불배송
	
	if(!$VENDOR) {
		$DELIVERY_TYPE		= $shop_config['delivery_type'];
		$DELIVERY_PRICE1	= $shop_config['delivery_p_price1'];
		$DELIVERY_PRICE2	= $shop_config['delivery_p_price2'];
		$DELIVERY_PRICE3	= $shop_config['delivery_d_price'];
		$IM_INFO1			= $shop_config['delivery_im_areas1_used'];
		$IM_INFO2			= $shop_config['delivery_im_areas1_price'];
		$IM_INFO3			= $shop_config['delivery_im_areas2_used'];
		$IM_INFO4			= $shop_config['delivery_im_areas2_price'];
	}
	else {
		$sql	= "SELECT commission FROM mallRN_vendor WHERE id = '{$VENDOR}'";
		$vinfo	= $mysql->one_row($sql);

		$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$VENDOR}'";
		$vshop_config	= $mysql->one_row($sql);

		$DELIVERY_TYPE		= $vshop_config['delivery_type'];		
		$DELIVERY_PRICE1	= $vshop_config['delivery_p_price1'];
		$DELIVERY_PRICE2	= $vshop_config['delivery_p_price2'];		
		$DELIVERY_PRICE3	= $vshop_config['delivery_d_price'];
		$IM_INFO1			= $vshop_config['delivery_im_areas1_used'];
		$IM_INFO2			= $vshop_config['delivery_im_areas1_price'];
		$IM_INFO3			= $vshop_config['delivery_im_areas2_used'];
		$IM_INFO4			= $vshop_config['delivery_im_areas2_price'];

		$VENDOR_COMMISSION_ARRAY[$VENDOR] = $vinfo['commission'];
	}

	$VENDOR_DELIVERY_INFO_ARRAY[$VENDOR] = $DELIVERY_TYPE."|".$DELIVERY_PRICE1."|".$DELIVERY_PRICE2."|".$DELIVERY_PRICE3."|".$IM_INFO1."|".$IM_INFO2."|".$IM_INFO3."|".$IM_INFO4;

	if($DELIVERY_TYPE != 'D') $VENDOR_DELIVERY3 = "0";

	$sql = "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' && vendor_delivery = '{$VENDOR}' {$where} ORDER BY vendor_delivery ASC, uid DESC";
	$mysql->query2($sql);

	$VENDOR_PRICE						= 0;
	$VENDOR_DELIVERY					= 0;
	$VENDOR_DELIVERY_CK_PRICE			= 0;
	$VENDOR_DELIVERY_FREE				= 0;
	$VENDOR_DELIVERY_ARRAY[$VENDOR]		= 0;
	$VENDOR_CHECK_ARRAY[$VENDOR]		= 0;
	$sum_delivery_option				= array();
	
	while($row2 = $mysql->fetch_array(2)) {
		$data				= getCartGoodsInfo($row2);
		
		$QTY				= $data['qty'];
		$G_UID				= $data['uid'];
		$PRICE				= str_replace("," ,"", $data['price']);
		$SUM_PRICE			= $PRICE * $QTY;
		$G_DELIVERY_PRICE	= $data['delivery_price'];
		
		if($data['delivery_type'] == 1 && $DELIVERY_TYPE == 'P') { 
			$VENDOR_DELIVERY_CK_PRICE += $SUM_PRICE;			
		}
		else if($data['delivery_type'] == 2) $VENDOR_DELIVERY_FREE = 1;
		else if($data['delivery_type'] == 4) { 
			if(in_array($data['uid'], $G_DELIVERY_TYPE4_CK_ARRAY)) {				
				$G_DELIVERY_PRICE = 0;
			}
			else $G_DELIVERY_TYPE4_CK_ARRAY[] = $data['uid'];
		}
		else if($data['delivery_type'] == 5) {
			if($data['option']) {
				if(isset($sum_delivery_option[$G_UID]) && $sum_delivery_option[$G_UID] > 0) {
					$G_DELIVERY_PRICE	= 0;
					$option_qty = 0;
				}
				else {
					$sql				= "SELECT SUM(qty) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && g_uid = '{$G_UID}' {$where}";
					$option_qty			= $mysql->get_one($sql);
					$G_DELIVERY_PRICE	= $data['delivery_price'] * ceil($option_qty / $data['delivery_type_qty']);
					$sum_delivery_option[$G_UID] = $option_qty;				
				}
			}
			else {				
				$G_DELIVERY_PRICE	= $data['delivery_price'] * ceil($QTY / $data['delivery_type_qty']);
			}
		}		

		if($data['delivery_type'] != 3) $VENDOR_DELIVERY3 = "0";
		
		if($data['delivery_type'] != 1) {
			$return_price2 = deliveryImAreasPrice($data, $VENDOR, $_POST['address1'], $_POST['postcode']);
			
			if($data['delivery_type'] == 5) {
				if($data['option']) {
					if($option_qty == 0) {
						$return_price2 = 0;
					}
					else {
						$return_price2 = $return_price2 * ceil($option_qty / $data['delivery_type_qty']);
					}
				}
				else {				
					$return_price2 = $return_price2 * ceil($QTY / $data['delivery_type_qty']);
				}
			}
			$G_DELIVERY_PRICE += $return_price2;
		}
		else $VENDOR_CHECK_ARRAY[$VENDOR]		= 1;

		$VENDOR_PRICE		+= $SUM_PRICE;
		$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;
	}
	
	if($VENDOR_DELIVERY_CK_PRICE && $VENDOR_DELIVERY_FREE == 0 && $DELIVERY_TYPE != 'F') {
		if($VENDOR_DELIVERY_CK_PRICE < $DELIVERY_PRICE1) {
			$VENDOR_DELIVERY				+= $DELIVERY_PRICE2;
			$VENDOR_DELIVERY_ARRAY[$VENDOR] += $DELIVERY_PRICE2;
		}
	}

	$SUM_PRICES			+= $VENDOR_PRICE;
	$SUM_DELIVERY		+= $VENDOR_DELIVERY;
}

if($VENDOR_DELIVERY3 == 0) {
	foreach($VENDOR_ARRAY as $k => $v) {
		if($VENDOR_CHECK_ARRAY[$v] == 1) {
			$sql = "SELECT * FROM mallRN_delivery_configuration WHERE vendor = '{$v}' && used = 1 ORDER BY uid ASC";
			$mysql->query($sql);

			$VENDOR_DELIVERY_ADD_ARRAY[$v] = 0;
		
			$shop_config2	= $shop_config;
			if($v) {
				$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$v}'";
				$vshop_config	= $mysql->one_row($sql);

				$shop_config2['delivery_im_areas1_used']		= $vshop_config['delivery_im_areas1_used'];
				$shop_config2['delivery_im_areas1_price']		= $vshop_config['delivery_im_areas1_price'];
				$shop_config2['delivery_im_areas2_used']		= $vshop_config['delivery_im_areas2_used'];
				$shop_config2['delivery_im_areas2_price']		= $vshop_config['delivery_im_areas2_price'];
			}
			
			$return_price	= 0;
			$return_price	+= deliveryImAreasPrice($shop_config2, $v, $_POST['address1'], $_POST['postcode']);

			while($row = $mysql->fetch_array()) {	
				$title = stripslashes($row['title']);
				if(preg_match("/{$title}/i", $_POST['address1'])) {			
					$return_price += $row['price'];
					break;
				}
			}

			$SUM_DELIVERY					+= $return_price;
			$VENDOR_DELIVERY_ARRAY[$v]		+= $return_price;
			$VENDOR_DELIVERY_ADD_ARRAY[$v]	= $return_price;
		}
	}
}

$SUM_TOTAL		= $SUM_PRICES + $SUM_DELIVERY;

if(!checkPostVar('use_mileage')) $_POST['use_mileage']	= 0;
else $_POST['use_mileage'] = str_replace(",", "", checkPostVar('use_mileage'));
if(!checkPostVar('use_coupon')) $_POST['use_coupon']	= 0;
else $_POST['use_coupon'] = str_replace(",", "", checkPostVar('use_coupon'));
if(!checkPostVar('coupon_uid')) $_POST['coupon_uid']	= 0;

if($_POST['use_mileage'] > 0) $SUM_TOTAL -= $_POST['use_mileage'];
if($_POST['use_coupon'] > 0) {
	$sql	= "SELECT * FROM mallRN_coupon WHERE uid = '{$_POST['coupon_uid']}' &&  id = '{$my_id}' && g_uid = 0 && status = 0 && e_date > '".date("Y-m-d")."'";
	if(!$data = $mysql->one_row($sql)) logMsg("장바구니 쿠폰이 유효하지 않습니다.");
	$SUM_TOTAL -= getCouponPrice($SUM_PRICES, $data['c_uid']);
}

if($SUM_TOTAL != checkPostVar('pay_total')) logMsg("결제금액이 일치하지 않아 주문 하실 수 없습니다.");

if($_POST['use_mileage']) {
	$sql		= "SELECT mileage FROM mallRN_member WHERE id = '{$my_id}'";
	$my_have_mileage	= $mysql->get_one($sql);
	if($my_have_mileage < $_POST['use_mileage']) logMsg("사용할 마일리지가 보유마일리지를 초과 하여 주문 하실 수 없습니다.");
}
############################### 주문금액 체크 ###################################
$_POST['new']			= 0;
if($my_id) {
	$_POST['id']		= $my_id;
	$_POST['name']		= $my_name;
	$_POST['email']		= $my_email;
	$_POST['passwd']	= "";

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE id = '{$my_id}'";
	if($mysql->get_one($sql) == 0) $_POST['new'] = 1;
}
else {
	$_POST['id']		= "";
	$_POST['name']		= checkPostVar('name');	
	$_POST['email']		= checkPostVar('email');
	$_POST['passwd']	= md5(checkPostVar('passwd'));

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE id = '' && name = '{$_POST['name']}' && email = '{$_POST['email']}'";
	if($mysql->get_one($sql) == 0) $_POST['new'] = 1;
}

$_POST['cell']			= checkPostVar('cell');
$_POST['name2']			= checkPostVar('name2');	
$_POST['cell2']			= checkPostVar('cell2');
$_POST['pay_type']		= checkPostVar('pay_type');
if($_POST['pay_type'] == 'B') {
	$_POST['bank_info']	= checkPostVar('remittance_bank')."|".checkPostVar('remittance_name');
}
else $_POST['bank_info'] = "";

if(checkPostVar('cash_receipts_type') && checkPostVar('cash_receipts_num')) {
	$_POST['cash_receipts']	= checkPostVar('cash_receipts_type')."|".checkPostVar('cash_receipts_num');
}
else $_POST['cash_receipts'] = "";

$_POST['delivery_total'] = $SUM_DELIVERY;

$_POST['memo']			= '';
$_POST['signdate']		= time();

if(checkPostVar('default_message') != 'input') $_POST['message'] = checkPostVar('default_message');

if($is_mobile == 1)	$_POST['mobile'] = 'Y';
else				$_POST['mobile'] = 'N';
		
if(!$_POST['name'] || !$_POST['name2'] || !$_POST['email'] || !$_POST['cell'] || !$_POST['cell2'] || !$_POST['address1']) {
	logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
}		

######################## 주문정보 등록  #########################		
$item_array			= array('id', 'name', 'cell', 'email', 'name2', 'cell2', 'passwd', 'postcode', 'address1', 'address2', 'message', 'memo', 'pay_total', 'delivery_total', 'pay_type', 'pay_status', 'bank_info', 'use_mileage', 'use_coupon', 'coupon_uid', 'cash_receipts', 'mobile', 'direct', 'new', 'signdate');
$item_able_value	= array('pay_type' => ['B', 'C', 'R', 'V', 'H', 'M'], 'pay_status' => ['A', 'B', 'C']);

$sql = "INSERT INTO mallRN_order_info SET";
foreach ($item_array as $k => $v) {
	if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
	else $_POST[$v] = checkPostVar($v);

	if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
	else $sql .= " {$v} = '{$_POST[$v]}',";
}
$mysql->query($sql);
$uid = $mysql->InsertNo();
######################## 주문정보 등록  #########################

######################## 주문번호 생성  #########################
if($uid > 99999)	$rand = substr($uid, -5);
else				$rand = str_pad($uid, "5", "0", STR_PAD_LEFT);
$order_num			= date("ymd-Hi",time()).'_'.$rand;
$_POST['order_num'] = $order_num;
######################## 주문번호 생성  #########################

$sql = "UPDATE mallRN_order_info SET order_num = '{$order_num}' WHERE uid = '{$uid}'";
$mysql->query($sql);

if($_POST['use_coupon'] && $_POST['coupon_uid']) {
	$sql = "UPDATE mallRN_coupon SET status = 1, usedate = '{$_POST['signdate']}' WHERE id = '{$my_id}' && uid = '{$_POST['coupon_uid']}'";
	$mysql->query($sql);
}

if($_POST['use_mileage']) useMileageChange ($my_id, $_POST['use_mileage'], "상품구입 마일리지 사용", $order_num);

$_POST['status']			= 0;
$_POST['status_date']		= time();

$sql						= "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where} ORDER BY vendor_delivery ASC, uid DESC";
$mysql->query($sql);

$G_DELIVERY_TYPE4_CK_ARRAY	= array();
$RELATIVE_GOODS_ARRAY		= array();

while($row = $mysql->fetch_array()) {
	$data						= getCartGoodsInfo($row);

	$_POST['vendor']			= $row['vendor'];
	$_POST['vendor_delivery']	= $row['vendor_delivery'];
	
	$_POST['commission']		= 0;
	if($row['vendor']) {
		if($data['commission_type'] == 0)	$_POST['commission'] = $VENDOR_COMMISSION_ARRAY[$row['vendor']];
		else								$_POST['commission'] = $data['commission'];

		if($data['option_price'] != 0) {
			$option_orig_price	= priceLimit($data['option_price'] * ((100 - $_POST['commission']) / 100));
			$data['orig_price'] += $option_orig_price;
		}
	}
	else {
		$_POST['commission'] = $data['commission'];

		if($data['orig_price'] > 0) {
			if($data['option_price'] != 0) {
				$option_orig_price	= priceLimit($data['option_price'] * ((100 - $_POST['commission']) / 100));
				$data['orig_price'] += $option_orig_price;
			}
		}
	}

	$_POST['g_uid']				= $row['g_uid'];
	$_POST['g_cate']			= $data['cate'];
	$_POST['g_name']			= addslashes($data['name']);
	$_POST['g_code']			= $data['g_code'];
	$_POST['price']				= str_replace(",", "", $data['price']);
	$_POST['orig_price']		= $data['orig_price'];
	$_POST['qty']				= $row['qty'];
	$_POST['mileage']			= $data['mileage'] * $row['qty'];
	$_POST['option']			= $row['option'];
	$_POST['option_name']		= addslashes($data['option']);
	$_POST['delivery_type']		= $data['delivery_type'];
	$_POST['delivery_type_qty'] = $data['delivery_type_qty'];
	$_POST['delivery_price']	= $data['delivery_price'];
	$_POST['delivery_add_price']	= 0;
	
	if($data['delivery_type'] == 4) {
		if(in_array($data['uid'], $G_DELIVERY_TYPE4_CK_ARRAY)) {			
			$_POST['delivery_price'] = 0;
		}
		else $G_DELIVERY_TYPE4_CK_ARRAY[] = $data['uid'];
	}
	
	if($data['delivery_type'] != 1) {
		$_POST['delivery_add_price'] = deliveryImAreasPrice($data, $_POST['vendor_delivery'], $_POST['address1'], $_POST['postcode']);
	}

	if($COUPON_DISCOUNT > 0) {
		$_POST['use_coupon']	= $COUPON_DISCOUNT;
		$_POST['coupon_uid']	= $COUPON_UID;
	}
	else {
		$_POST['use_coupon']	= 0;
		$_POST['coupon_uid']	= 0;
	}

	$_POST['discount']			= 0;
	$_POST['discount_info']		= '';	

	if($my_discount || $data['event_discount']) {
		$sale_msg_array = array();
		if($my_discount)			$sale_msg_array[] = "회원등급할인 : {$my_discount}%";
		if($data['event_discount'])	$sale_msg_array[] = "이벤트할인 :{$data['event_discount']}%";				
		$_POST['discount_info'] = join(", ", $sale_msg_array);
		$_POST['discount']		= $SALE_PRICE - $COUPON_DISCOUNT;
	}	

	######################## 주문상품 정보 등록  #########################
	$item_array		= array('vendor', 'vendor_delivery', 'commission', 'order_num', 'g_uid', 'g_cate', 'g_name', 'g_code', 'price', 'orig_price', 'qty', 'mileage', 'option', 'option_name', 'delivery_type', 'delivery_type_qty', 'delivery_price', 'delivery_add_price', 'use_coupon', 'coupon_uid', 'discount', 'discount_info', 'status', 'status_date', 'signdate');
	
	$sql = "INSERT INTO mallRN_order_goods SET";
	foreach ($item_array as $k => $v) {
		$_POST[$v] = checkPostVar($v);

		if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
		else $sql .= " {$v} = '{$_POST[$v]}',";
	}
	$mysql->query2($sql);
	######################## 주문상품 정보 등록  #########################

	if($_POST['use_coupon'] && $_POST['coupon_uid']) {
		$sql = "UPDATE mallRN_coupon SET status = 1, usedate = '{$_POST['signdate']}' WHERE id = '{$my_id}' && uid = '{$_POST['coupon_uid']}'";
		$mysql->query($sql);
	}

	if(!in_array($_POST['g_uid'], $RELATIVE_GOODS_ARRAY)) $RELATIVE_GOODS_ARRAY[] = $_POST['g_uid'];
}

######################## 동시구매상품 정보 등록  #########################
$RELATIVE_GOODS	= join(",", $RELATIVE_GOODS_ARRAY);
$sql			= "INSERT INTO mallRN_order_related_goods SET goods = ',{$RELATIVE_GOODS},', signdate = '{$_POST['signdate']}'";
$mysql->query($sql);
######################## 동시구매상품 정보 등록  #########################

######################## 배송비 정보 등록  #########################
foreach($VENDOR_DELIVERY_ARRAY as $k => $v) {
	if(!isset($VENDOR_DELIVERY_ADD_ARRAY[$k])) $VENDOR_DELIVERY_ADD_ARRAY[$k] = 0;
	$sql		= "INSERT INTO mallRN_order_delivery SET order_num = '{$order_num}', vendor = '{$k}', price = '{$v}', adds = '{$VENDOR_DELIVERY_ADD_ARRAY[$k]}', info = '{$VENDOR_DELIVERY_INFO_ARRAY[$k]}', signdate = '{$_POST['signdate']}'";
	$mysql->query($sql);
}
######################## 배송비 정보 등록  #########################

if($_POST['pay_type'] == 'B' || $_POST['pay_type'] == 'M') {
	
	$sql	= "UPDATE mallRN_order_info SET reals = 1 WHERE order_num = '{$order_num}'";
	$mysql->query($sql);

	$sql	= "UPDATE mallRN_order_goods SET reals = 1 WHERE order_num = '{$order_num}'";
	$mysql->query($sql);

	goodsOrderQtyChange($order_num);

	if($_POST['pay_type'] == 'M' && $SUM_TOTAL == 0) orderStatus1($order_num, $my_id);

	$sql = "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where}";
	$mysql->query($sql);

	parentMovePage("../{$Main}?channel=order_ok&order_num={$order_num}");
}
else {	
	
	$goods_info			= '';
	$ediDate			= '';
	$hashString			= '';

	if($_POST['pay_type'] == 'V') {
		
		function str_fromcharcode() {
			return implode(array_map('chr', func_get_args()));
		}

		switch($shop_config['payment_cp']) {
			case "KCP" :

				$sql = "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}'";
				$mysql->query($sql);
				
				$no			= 1;
				while($row = $mysql->fetch_array()) {
					if ($no != 1) $goods_info .= str_fromcharcode(30);

					$goods_info .= 'seq='.$no.str_fromcharcode(31).'ordr_numb='.$order_num.'_'.$row['uid'].str_fromcharcode(31).'good_name='.stripslashes($row['g_name']).str_fromcharcode(31).'good_cntx='.$row['qty'].str_fromcharcode(31).'good_amtx='.$row['price'].str_fromcharcode(31);
					
					$no++;
				}
			break;

			case "NICEPAY" :
				
			break;

			case "INICIS" :
				
			break;
		}
	}

	$SHOP_ID			= trim($shop_config['payment_shop_id']);
	$SHOP_KEY			= add_escape_re_string(trim($shop_config['payment_shop_key']));

	switch($shop_config['payment_cp']) {
		case "KCP" :
			$payment_escrow_yn	= 'N';			
			if($_POST['pay_type'] == 'V' && $shop_config['payment_escrow_v'] == 1) $payment_escrow_yn = 'Y';
		break;

		case "NICEPAY" :
			$payment_escrow_yn	= '0';
			if($_POST['pay_type'] == 'V' && $shop_config['payment_escrow_v'] == 1) {
				$payment_escrow_yn = '1';
				$sql = "UPDATE mallRN_order_info SET escrow = '1' WHERE order_num = '{$order_num}'";
				$mysql->query($sql);
			}

			$ediDate			= date("YmdHis");
			$hashString			= bin2hex(hash('sha256', $ediDate.$SHOP_ID.$SUM_TOTAL.$SHOP_KEY, true));			
		break;

		case "INICIS" :
			$payment_escrow_yn	= '0';
			if($_POST['pay_type'] == 'V' && $shop_config['payment_escrow_v'] == 1) {
				$payment_escrow_yn = '1';
				$sql = "UPDATE mallRN_order_info SET escrow = '1' WHERE order_num = '{$order_num}'";
				$mysql->query($sql);
			}
			
			if($is_mobile == 0) {
				require_once('../plugin/inicis/libs/INIStdPayUtil.php');
				$SignatureUtil = new INIStdPayUtil();

				$timestamp 		= $SignatureUtil->getTimestamp();   			// util에 의해서 자동생성

				$params = array(
					"oid" => $order_num,
					"price" => $SUM_TOTAL,
					"timestamp" => $timestamp
				);

				$sign   = $SignatureUtil->makeSignature($params);
				
				$ediDate			= $timestamp;
				$hashString			= $sign;	
			}
		break;
	}

	$addInfo	= previlEncode("{$direct}|{$my_id}|{$order_num}");

	echo "<script>";
	echo "parent.{$mobile_header}cp_proc('{$order_num}', '{$SUM_TOTAL}', '".$shop_config['payment_cp']."', '{$goods_info}', '{$addInfo}', '{$payment_escrow_yn}', '{$ediDate}', '{$hashString}'); ";
	echo "parent.only_num_formatCk();";
	echo "</script>";
}

?>