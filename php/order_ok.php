<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$order_num = checkGetVar('order_num');
if(!$order_num) alert('해당주문이 삭제되었거나 존재하지 않습니다.', $Main);

$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
$data	= $mysql->one_row($sql);
if(!$data) alert('해당주문이 삭제되었거나 존재하지 않습니다.', $Main);

$sql	= "SELECT count(*) as cnt, g_name FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1";
$data2	= $mysql->one_row($sql);

$GOODS_NAME	= stripslashes($data2['g_name']);
if($data2['cnt'] > 1) $GOODS_NAME .= "외 {$data2['cnt']}건";

$PAY_TOTAL	= number_format($data['pay_total']);
$TTL		= "주문이";

if($shop_config['order_auto_completed3'] > 0) {
	$BANK_DATE = date("Y년 m월 d일", strtotime("+{$shop_config['order_auto_completed3']} DAY", $data['signdate']));
}

switch($data['pay_type']) {
	case "B" : 
		$tmps		= explode("|", stripslashes($data['bank_info']));
		$BANK_INFO1	= $tmps[0];
		$BANK_INFO2	= $tmps[1];

		if($BANK_DATE) $tpl->parse("is_bank_date");

		$tpl->parse("is_bank_info");
	break;

	case "C" : case "M" : case "R" : case "H" :
		if($data['pay_status'] == 'C') {
			$TTL		= "결제가";

			if($data['pay_type'] == 'M')	$PAY_INFO = "마일리지 사용";
			else							$PAY_INFO	= stripslashes($data['pay_info']);

			$tpl->parse("is_pay_info");
		}
	break;

	case "V" :
		$tmps		= explode(",", stripslashes($data['pay_info']));
		$tmps1		= explode(" : ", stripslashes($tmps[0]));
		$tmps2		= explode(" : ", stripslashes($tmps[1]));
		$tmps3		= explode(" : ", stripslashes($tmps[2]));
		
		$BANK_INFO1	= $tmps1[1];
		$BANK_INFO2	= $tmps2[1];
		$BANK_INFO3	= $tmps3[1];

		if($BANK_DATE) $tpl->parse("is_bank_date2");

		$tpl->parse("is_bank_info2");
	break;
}

############ 주문완료 메일/SMS 보내기 ############		
if($data['mail_ok'] == 0) {
	$URL = ABSOLUTE_PATH_SHOP."php/async_order_mail.php?order_num={$order_num}";
	socketPost($URL, 'POST', 0); //비동기 실행	
}
############ 주문완료 메일/SMS 보내기 ############		

?>