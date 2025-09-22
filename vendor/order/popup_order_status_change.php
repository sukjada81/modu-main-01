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

$pay_total			= number_format($info['pay_total']);
$cancel_total		= number_format($info['cancel_total'] + $info['refund_total']);
$use_mileage		= number_format($info['use_mileage']);
$delivery_total		= number_format($info['delivery_total']);
$refund_total		= number_format($info['pay_total'] + $info['use_mileage']);
$pay_type			= $pay_type_array[$info['pay_type']];
$pay_status			= $pay_status_array[$info['pay_status']];
$status_date		= date("Y-m-d H:i:s", $info['status_date']);

if($info['pay_type'] == 'B') {
	$tmps				= explode("|", $info['bank_info']);
	$bank_info			= $tmps[0];
	$remittance_name	= $tmps[1];

	$tpl->parse("is_pay_type_B");
}

if($info['cash_receipts'])	$cash_receipts_yn = "신청";
else						$cash_receipts_yn = "미신청";

$where				= "";
if($og_uid) $where	= " && uid = '{$og_uid}'";

$sql = "SELECT g_name, price, qty, delivery_type, delivery_price, use_coupon, discount, vendor, status, status2 FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 {$where} ORDER BY vendor_delivery ASC, vendor ASC, uid DESC";
$mysql->query2($sql);

$refund_total2		= 0;

while($row = $mysql->fetch_array(2)){
	if($row['status2'])	$GOODS_STATUS = $status_array[$row['status']].$status2_array[$row['status2']];
	else				$GOODS_STATUS = $status_array[$row['status']];
	$GOODS_NAME		= stripslashes($row['g_name']);
	$GOODS_PRICE	= number_format($row['price']);
	$GOODS_QTY		= number_format($row['qty']);

	if($row['delivery_type'] == 5) $row['delivery_price'] = $row['delivery_price'] * ceil($row['qty'] / $row['delivery_type_qty']);
	$DELIVERY	= number_format($row['delivery_price']);

	$refund_total2	+= ($row['price'] * $row['qty']) + $row['delivery_price'];
		
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

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>