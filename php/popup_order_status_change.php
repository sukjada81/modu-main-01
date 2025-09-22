<?php

include_once('../php/popup_init.php'); 

$order_num		= checkGetVar('order_num');
$uid			= checkGetVar('uid');
$status			= checkGetVar('status');

if(!$order_num || !$status) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

if($my_id) {
	$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && id = '{$my_id}' && reals = 1";
}
else {
	if(!isset($_COOKIE['guestOrder1'])) iframeViewError("먼저 로그인을 하시기 바랍니다.");
	
	$guestOrder1	= base64_decode($_COOKIE['guestOrder1']);
	$tmps			= explode("|", $guestOrder1);
	$cell			= $tmps[0];
	$name			= $tmps[1];
	$passwd			= previlDecode($_COOKIE['guestOrder2']);

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE name = '{$name}' && cell = '{$cell}' && passwd = '{$passwd}' && order_num = '{$order_num}' && reals = 1 ORDER BY uid DESC";
	if($mysql->get_one($sql) == 0) {
		iframeViewError("일치하는 정보가 존재 하지 않습니다.");
	}	
	$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
}
if($mysql->get_one($sql) == 0) iframeViewError('해당주문이 삭제되었거나 존재하지 않습니다.');

$status_array	= array("7" => "교환", "8" => "반품", "9" => "취소");
$pop_title		= "{$status_array[$status]}요청";
$TTL			= $status_array[$status];

$tpl			= new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_order_status_change.html");
$tpl->scan_area("main");

$sql			= "SELECT status FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 && uid = '{$uid}'";
$goods_status	= $mysql->get_one($sql);

if($goods_status == 2) {
	$tpl->parse("is_notice");
}

$sql			= "SELECT id, pay_status, cancel_total, pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
$data			= $mysql->one_row($sql);

if($shop_config['order_cancel_info']) {
	$cancel_info = explode("|*|", $shop_config['order_cancel_info']);
	foreach($cancel_info as $k => $v) {
		$cancel_info2	= explode("|",$v);
		if($cancel_info2[1] != '1') continue;
		$cancel		= specialStrReplace($cancel_info2[0]);
		$tpl->parse("loop_cancel");
	}	
	unset($cancel_info, $cancel_info2, $cancel);
}

if($status != 7) {
	if($data['pay_type'] == 'R') {		
		if($status == 9 && date("Y-m-d", $data['status_date']) == date("Y-m-d")) {
			$REFUND_TYPE = "계좌이체 취소";
			$tpl->parse("is_refund2");
		}
		else $tpl->parse("is_refund");
	}
	else if($data['pay_type'] == 'H') {		
		if($status == 9 && date("Y-m", $data['status_date']) == date("Y-m")) {
			$REFUND_TYPE = "휴대폰결제 취소";
			$tpl->parse("is_refund2");
		}
		else $tpl->parse("is_refund");
	}
	else if($data['pay_type'] == 'B' || $data['pay_type'] == 'V') $tpl->parse("is_refund");
	else { 
		if($data['pay_type'] == 'M') $REFUND_TYPE = "마일리지";
		else if($data['pay_type'] == 'C') $REFUND_TYPE = "카드결제취소";		
		$tpl->parse("is_refund2");
	}
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>