<?php 

include_once("../common/popup_top.php");

$order_num	= checkGetVar('order_num');
$status		= checkGetVar('status');
$status2	= substr($status, 1, 1);
$status		= substr($status, 0, 1);
$og_uid		= checkGetVar('og_uid');

if(!$order_num || !$status || !$status2) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_order_status_change2.html");
$tpl->scan_area("main");

$status_array		= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array		= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array	= array("A" => "미결제", "B" => "가상계좌발급완료", "C" => "결제완료", "D" => "결제실패");


$sql = "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
if(!$info = $mysql->one_row($sql)) {
	iframeViewError("해당주문이 삭제되었거나 존재하지 않습니다.");
}

if($status != 7) {
	if($info['pay_type'] == 'R') {
		if($status == 9 && (date("Y-m-d", $info['status_date']) == date("Y-m-d") || date("Y-m-d", $info['status_date'] + 86400) == date("Y-m-d"))) {
			$refund_bank_info = "실시간계좌이체 취소";			
		}
		else $tpl->parse("is_refund_bank");
	}
	else if($info['pay_type'] == 'H') {
		if($status == 9 && date("Y-m", $info['status_date']) == date("Y-m")) {
			$refund_bank_info = "휴대폰결제 취소";			
		}
		else $tpl->parse("is_refund_bank");
	}
	else if($info['pay_type'] == 'B' || $info['pay_type'] == 'V') $tpl->parse("is_refund_bank");
	else { 
		if($info['pay_type'] == 'M')  $refund_bank_info = "마일리지 환원";
		else if($info['pay_type'] == 'C') $refund_bank_info = "카드결제 취소";		
	}
}

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

if($og_uid) {
	$where			= " && uid = '{$og_uid}'";
	$readonly		= "";
}	
else {
	$where			= "";
	$readonly		= "readonly";
}

$sql = "SELECT g_name, price, qty, delivery_type, delivery_price, use_coupon, discount, vendor, status, status2 FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 {$where} ORDER BY vendor_delivery ASC, vendor ASC, uid DESC";
$mysql->query2($sql);

$refund_total2		= 0;

while($row = $mysql->fetch_array(2)){
	if($row['status2'])	$GOODS_STATUS = $status_array[$row['status']].$status2_array[$row['status2']];
	else				$GOODS_STATUS = $status_array[$row['status']];
	$GOODS_NAME		= stripslashes($row['g_name']);
	$GOODS_PRICE	= number_format($row['price']);
	$GOODS_QTY		= number_format($row['qty']);

	$row['delivery_price'] += $row['delivery_add_price'];
	if($row['delivery_type'] == 5) $row['delivery_price'] = $row['delivery_price'] * ceil($row['qty'] / $row['delivery_type_qty']);
	$DELIVERY	= number_format($row['delivery_price']);

	$refund_total2	+= ($row['price'] * $row['qty']) + $row['delivery_price'];

	$vendor			= $row['vendor'];
	
	$tpl->parse("loop_goods");	
}

$sql				= "SELECT order_cancel_info FROM  mallRN_configuration WHERE uid = 1";
$order_cancel_info	= $mysql->get_one($sql);

if($order_cancel_info) {
	$cancel_info = explode("|*|", $order_cancel_info);
	foreach($cancel_info as $k => $v) {
		$cancel_info2	= explode("|",$v);
		if($cancel_info2[1] != '1') continue;
		$cancel		= specialStrReplace($cancel_info2[0]);
		$tpl->parse("loop_cancel");
	}	
	unset($cancel_info, $cancel_info2, $cancel);
}

$tpl->parse("is_order_info");

if($status == "9") {
	
	if($og_uid) {	
		$sql				= "SELECT price FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$vendor}'";
		$vendor_delivery	= $mysql->get_one($sql);

		if($vendor_delivery) {
			$sql = "SELECT count(*) FROM mallRN_order_goods WHERE vendor_delivery = '{$vendor}' && order_num = '{$order_num}' && reals = 1 && uid != '{$og_uid}' && delivery_type = 1 && !((status = 8 || status = 9) && status2 = 5)";
			if($mysql->get_one($sql) > 0) {
				$vendor_delivery = 0;
			}
		}

		$refund_total		= $refund_total2 + $vendor_delivery;
		if($vendor_delivery) {
			$vendor_delivery	= number_format($vendor_delivery);
			$refund_total3		= number_format($refund_total2);
			$tpl->parse("is_vendor_delivery");			
		}

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
	
	$vendor_delivery2	= 0;
	$refund_mileage		= number_format($refund_mileage);
	
	$TTL	= "취소";
	$tpl->parse("is_cancel_info");
}
else if($status == '7') {
	$TTL	= "교환";
	$tpl->parse("is_exchange_info");
}
else if($status == '8') {
	$TTL	= "반품";
	$tpl->parse("is_exchange_info");
	$tpl->parse("is_refund_info");
}


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>