<?php 

include_once("../common/popup_top.php");

$uid		= checkGetVar('uid');
$status		= checkGetVar('status');
$status2	= substr($status, 1, 1);
$status		= substr($status, 0, 1);

if(!$uid || !$status || !$status2) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_order_status_change.html");
$tpl->scan_area("main");

$status_array		= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array		= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array	= array("A" => "미결제", "B" => "가상계좌발급완료", "C" => "결제완료", "D" => "결제실패");

$sql	= "SELECT * FROM mallRN_order_status_change WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) {
	iframeViewError("해당 내역이 삭제되었거나 존재하지 않습니다.");
}

$order_num			= $data['order_num'];
$og_uid				= $data['og_uid'];
$refund_bank_info	= str_replace("|", " ", $data['bank_info']);

$sql = "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
if(!$info = $mysql->one_row($sql)) {
	iframeViewError("해당주문이 삭제되었거나 존재하지 않습니다.");
}

if($info['pay_type'] == 'M') $refund_bank_info = "마일리지 환원";
else if($info['pay_type'] == 'C') $refund_bank_info = "카드결제 취소";
else if($info['pay_type'] == 'R') $refund_bank_info = "실시간계좌이체 취소";

$orig_pay_total		= number_format($info['pay_total'] + $info['use_mileage'] + $info['use_coupon']);
$pay_total			= number_format($info['pay_total']);
$cancel_total		= number_format($info['cancel_total'] + $info['refund_total']);
$orig_use_mileage	= $info['use_mileage'];
$use_mileage		= number_format($info['use_mileage']);
$use_coupon			= number_format($info['use_coupon']);
$orig_use_coupon	= $info['use_coupon'];
$delivery_total		= number_format($info['delivery_total']);
$refund_total		= number_format($info['pay_total'] + $info['use_mileage'] + $info['use_coupon']);
$pay_type			= $pay_type_array[$info['pay_type']];
$pay_status			= $pay_status_array[$info['pay_status']];
$status_date		= date("Y-m-d H:i:s", $info['status_date']);

if($info['pay_type'] == 'B') {
	$tmps				= explode("|", $info['bank_info']);
	$bank_info			= $tmps[0];
	$remittance_name	= @$tmps[1];

	$tpl->parse("is_pay_type_B");
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

$where				= "";
if($og_uid) $where	= " && uid = '{$og_uid}'";

$sql = "SELECT g_name, price, qty, delivery_type, delivery_price, use_coupon, discount, vendor, vendor_delivery, status, status2 FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 {$where} ORDER BY vendor_delivery ASC, vendor ASC, uid DESC";
$mysql->query2($sql);

$refund_total2		= 0;

while($row = $mysql->fetch_array(2)){
	if($row['status2'])	$GOODS_STATUS = $status_array[$row['status']].$status2_array[$row['status2']];
	else				$GOODS_STATUS = $status_array[$row['status']];
	$GOODS_NAME		= stripslashes($row['g_name']);
	$GOODS_PRICE	= number_format($row['price']);
	$GOODS_QTY		= number_format($row['qty']);

	if($row['vendor_delivery']) {
		$sql	= "SELECT comp_name FROM mallRN_vendor WHERE id = '{$row['vendor_delivery']}'";
		$vinfo	= stripslashes($mysql->get_one($sql));
		$GOODS_VENDOR_CARR = $vinfo;
	}
	else						$GOODS_VENDOR_CARR = "본사베송";			

	$row['delivery_price'] += $row['delivery_add_price'];
	if($row['delivery_type'] == 5) $row['delivery_price'] = $row['delivery_price'] * ceil($row['qty'] / $row['delivery_type_qty']);
	$DELIVERY	= number_format($row['delivery_price']);

	$refund_total2	+= ($row['price'] * $row['qty']) + $row['delivery_price'];

	$vendor			= $row['vendor_delivery'];
	$vendor_delivery_type	= $row['delivery_type'];
		
	$tpl->parse("loop_goods");	
}

if($status == 7 && $status2 == 4) {
	$sql		= "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
	if($delivery_info = $mysql->get_one($sql)){
		$delivery_info = explode("|*|", $delivery_info);

		if($delivery_info[1] && $delivery_info[1] != '|||') {
			for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

				$delivery_info2 = explode("|", $delivery_info[$i]);
				
				if($delivery_info2[3] == 0) continue;

				$delivery_num	= $delivery_info2[0];
				$delivery_name	= $delivery_info2[1];
				
				$tpl->parse("loop_delivery");
			}
		}
	}

	$tpl->parse("is_delivery_info");
}
else {
	$tpl->parse("is_order_info");
		
	if($og_uid) {	
		$sql					= "SELECT price FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$vendor}'";
		$vendor_delivery		= $mysql->get_one($sql);

		$vendor_add_delivery	= 0;

		if($vendor_delivery) {
			$sql = "SELECT count(*) FROM mallRN_order_goods WHERE vendor_delivery = '{$vendor}' && order_num = '{$order_num}' && reals = 1 && uid != '{$og_uid}' && delivery_type = 1 && !((status = 8 || status = 9) && status2 = 5)";
			if($mysql->get_one($sql) > 0) {
				$vendor_delivery = 0;
			}
		}
		else {			
			if($vendor_delivery_type == 1) {
				if($vendor) {
					$sql	= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$vendor}'";
				}
				else {
					$sql	= "SELECT delivery_type, delivery_p_price1, delivery_p_price2, delivery_d_price FROM mallRN_configuration WHERE uid = 1";					
				}
				$vshop_config = $mysql->one_row($sql);

				if($vshop_config['delivery_type'] == 'P') {

					$sql = "SELECT count(*)	FROM mallRN_order_goods WHERE vendor_delivery = '{$vendor}' && order_num = '{$order_num}' && reals = 1 && uid != '{$og_uid}' && delivery_type = 1 && !((status = 8 || status = 9) && status2 = 5)";

					if($mysql->get_one($sql) > 0) {

						$sql = "SELECT * FROM mallRN_order_goods WHERE vendor_delivery = '{$vendor}' && order_num = '{$order_num}' && reals = 1 && uid != '{$og_uid}' && delivery_type = 1 && !((status = 8 || status = 9) && status2 = 5) ORDER BY uid DESC";
						$mysql->query2($sql);

						$VENDOR_PRICE			= 0;
						
						while($row2 = $mysql->fetch_array(2)) {
							$QTY				= $row2['qty'];
							$PRICE				= $row2['price'];
							$VENDOR_PRICE		+= $PRICE * $QTY;					
						}
						
						if($VENDOR_PRICE < $vshop_config['delivery_p_price1']) {
							$vendor_add_delivery	= $vshop_config['delivery_p_price2'];
						}
					}
				}
			}
		}

		$refund_total		= $refund_total2 + $vendor_delivery;
		if($vendor_delivery) {
			$vendor_delivery	= number_format($vendor_delivery);
			$refund_total3		= number_format($refund_total2);
			$tpl->parse("is_vendor_delivery");			
		}

		$vendor_delivery2	= number_format($vendor_add_delivery);

		$refund_coupon		= 0;
		if($refund_total >= $orig_use_mileage) {
			$refund_mileage	= $orig_use_mileage;
			
			if($orig_use_coupon) {
				$check_total = (int) str_replace(",", "", $orig_pay_total) - (int) str_replace(",", "", $cancel_total);
				if($check_total - $refund_total <= $orig_use_coupon) {			
					if(($refund_total - $orig_use_mileage) >= $orig_use_coupon) $refund_coupon	= $orig_use_coupon;
					else														$refund_coupon	= $refund_total - $orig_use_coupon;
				}
			}
		}
		else {
			$refund_mileage	= $refund_total;
		}

		$pay_total2			= number_format($refund_total - $refund_mileage - $refund_coupon);		
		$refund_total		= number_format($refund_total);
	}
	else {
		$vendor_delivery	= 0;
		$pay_total2			= $pay_total;
		$refund_mileage		= (int) str_replace(",", "", $use_mileage);
		$refund_coupon		= $use_coupon;
	}

	$refund_mileage		= number_format($refund_mileage);

	$tpl->parse("is_refund_info");
}

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>