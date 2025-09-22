<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');

include_once('init.php');

include_once(DEFAULT_PATH.PATH_LIB.'/class.Template.php'); 

$referer	= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$access_ip	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$order_num	= checkPostVar('order_num');

if(!$referer || !$access_ip || $access_ip != $_SERVER['SERVER_ADDR'] || !$order_num)	{
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
$info	= $mysql->one_row($sql);
if(!$info) exit(0);

//if($info['mail_ok'] == 0) exit(0);

$PAY_TOTAL	= number_format($info['pay_total']);

if($info['pay_status'] == 'C')	{
	$ORDER_TYPE		= "결제가";
	$ORDER_TYPE2	= "결제완료";
}
else {
	$ORDER_TYPE		= "주문이";
	$ORDER_TYPE2	= "입금대기중";
}

$name		= stripslashes($info['name']);

$sql			= "SELECT content, send FROM mallRN_auto_mail WHERE type = 'order'";
$data			= $mysql->one_row($sql);

if($data['send'] == 1) {

	$content		= stripslashes($data['content']);
	$content		= str_replace("{",	"{{",	$content);
	$content		= str_replace("}",	"}}",	$content);
	$content		= str_replace("{{LOOP_VENDOR_START}}",	'<!-- DYNAMIC @loop_order@ -->',		$content);
	$content		= str_replace("{{LOOP_VENDOR_END}}",	'<!-- DYNAMIC @loop_order@ -->',		$content);
	$content		= str_replace("{{LOOP_GOODS_START}}",	'<!-- DYNAMIC @loop_order_goods@ -->',	$content);
	$content		= str_replace("{{LOOP_GOODS_END}}",		'<!-- DYNAMIC @loop_order_goods@ -->',	$content);

	$ORDER_DATE		= date("Y-m-d H:i:s");
	$ORDER_NUM		= $order_num;
	$ORDER_LINK		= ABSOLUTE_PATH_SHOP."index.php?channel=order_list";
	
	$tpl		= new classTemplate;
	$tpl->define2("main", $content);
	$tpl->scan_area("main");

	$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor_delivery ASC";
	$mysql->query($sql);

	$TOTAL			= 0;
	$SUM_PRICES		= 0;
	$SUM_DISCOUNT	= 0;
	$SUM_DELIVERY	= 0;
	$SUM_TOTAL		= 0;
	$SUM_MILEAGE	= 0;
	$ADD_DELIVERY	= 0;

	while($row = $mysql->fetch_array()) {
		$VENDOR				= $row['vendor_delivery'];

		$sql	= "SELECT * FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$VENDOR}'";
		$data	= $mysql->one_row($sql);
		$delivery_config = explode("|", $data['info']);

		$ADD_DELIVERY += $data['adds'];

		if($delivery_config[0] == 'F') $DELIVERY_MESSAGE	= "무료배송";
		else if($delivery_config[0] == 'D') $DELIVERY_MESSAGE	= "착불 (예상금액 : ".number_format($delivery_config[3])."원)";
		else $DELIVERY_MESSAGE	= number_format($delivery_config[2])."원 (주문금액 ".number_format($delivery_config[1])."원 이상 구매시 무료)";
		
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

			$G_LINK				= ABSOLUTE_PATH_SHOP."index.php?channel=view&uid={$G_UID}&cate={$gdata['cate']}";	
			$G_NAME				= stripslashes($row2['g_name']);
			$G_IMAGE			= ABSOLUTE_PATH_SHOP."image/goods/img{$gdata['image3']}";
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
					$DELIVERY				= number_format($row2['delivery_price'])."원";				
				break;
				case "5" :
					$DELIVERY				= number_format($row2['delivery_price'])."원(개당)";
				break;
			}
			$G_DELIVERY_PRICE	= $row2['delivery_price'];

			$DELIVERY			= "<div>배송비 {$DELIVERY}</div>";
						
			$MILEAGE			= "";
			if($row2['mileage']) {
				$MILEAGE		= "<div style='float:left;'>{$row2['mileage']}</div>";
			}			

			if($G_OPTION) {
				$G_OPTION		.= "<div>{$G_OPTION}</div>";
			}
			
			$ORIG_PRICE = 0;
			if($SALE_PRICE > 0) {
				$ORIG_PRICE			= number_format((str_replace(",", "", $PRICE) + $SALE_PRICE), CONF_FLOAT_CNT);
				$SUM_ORIG_PRICE		= "<div style='float:left;'>".number_format((str_replace(",", "", $ORIG_PRICE) * $QTY), CONF_FLOAT_CNT)."</div>";

				$VENDOR_PRICE		+= str_replace(",", "", $ORIG_PRICE) * $QTY;
			}
			else $VENDOR_PRICE		+= str_replace(",", "", $SUM_PRICE);

			if($row2['use_coupon']) {
				$COUPON_INFO	= "쿠폰할인 ".number_format(stripslashes($row2['use_coupon']))."원";
			}

			if($row2['discount_info']) {
				$DISCOUNT_INFO	= stripslashes($row2['discount_info']);				
			}
			
			$QTY				= number_format($QTY);

			$tpl->parse("loop_order_goods");

			if($row2['delivery_type'] == 5) $G_DELIVERY_PRICE	= $row2['delivery_price'] * $row2['qty'];
			
			$VENDOR_DISCOUNT	+= $SALE_PRICE * $row2['qty'];
			$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;
			$SUM_MILEAGE		+= $row2['mileage'];
					
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
	$SUM_CANCEL		= number_format($info['cancel_total'] + $info['refund_total'], CONF_FLOAT_CNT);	

	$GOODS_TOTAL	= $TOTAL;

	switch($info['pay_type']) {
		case "B" :
			$tmps		= explode("|", stripslashes($info['bank_info']));
			$BANK_INFO1	= $tmps[0];
			$BANK_INFO2	= $tmps[1];

			$PAY_INFO	= "<p>{$BANK_INFO1} 계좌에 {$BANK_INFO2} 으로 <font>{$SUM_TOTAL}</font>원을 입금 해 주시면 됩니다.</p>";
			$PAY_INFO	.= "<span class='msg'>{$BANK_DATE}까지 미입금시 자동으로 주문이 취소됩니다.</span>";				
		break;

		case "M" : 
			$PAY_INFO	= "마일리지 사용";
		break;

		case "C" : case "R" : case "V" : case "H" :
			$PAY_INFO	= stripslashes($info['pay_info']);
		break;
	}
	
	$name2			= stripslashes($info['name2']);
	$name2			= mb_substr($name2, 0, 1, 'utf-8')." * ".mb_substr($name2, 2, mb_strlen($name2, 'utf-8'), 'utf-8');
	$RECIEVER_INFO	= $name2." / ".substr($info['cell2'], 0, -4)."**** / ".stripslashes($info['postcode'])." ".stripslashes($info['address1'])." ******";

	$tpl->parse("main");
	$content	= $tpl->tprint("main", "1");
	$tpl->close();

	mallMailSend($info['email'], "[".stripslashes($shop_config['basic_name'])."] {$name}님 {$ORDER_TYPE} 완료 되었습니다.", $content);	
	
	$sql			= "UPDATE mallRN_order_info SET mail_ok = 1 WHERE order_num = '{$order_num}'";
	$mysql->query($sql);
}

$sql = "SELECT DISTINCT(vendor) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor ASC";
$mysql->query($sql);

$vendor_array = array();

while($row = $mysql->fetch_array()){
	if(!$row['vendor']) continue;
	$vendor_array[] = $row['vendor'];
}

switch($info['pay_type']) {
	case "B" :
		$tmps				= explode("|", stripslashes($info['bank_info']));			
		$replace_code_array	= array('ACCOUNT' => "{$tmps[0]} 계좌에 {$tmps[1]}", 'PRICE' => $PAY_TOTAL, 'ORDER_NAME' => $name, 'ORDER_NUM' => $order_num);
		mallSmsAuto('order', $info['cell'], $replace_code_array);			
	break;

	case "M" : case "C" : case "R" : case "V" : case "H" :
		$replace_code_array	= array('PRICE' => $PAY_TOTAL, 'ORDER_NAME' => $name, 'ORDER_NUM' => $order_num);
		mallSmsAuto('pay_ok', $info['cell'], $replace_code_array);

		foreach($vendor_array as $k => $v) {
			$sql	= "SELECT cont_cell FROM mallRN_vendor WHERE id = '{$v}'";
			$cell	= $mysql->get_one($sql);

			if($cell) mallSmsAuto('pay_ok2', $cell, $replace_code_array);
		}
		
	break;
}

if(count($vendor_array) > 0)	$vendor = join("|", $vendor_array);
else							$vendor = "";

fcmSend("신규 주문 알림!", "{$name}님의 주문이 접수 되었습니다.\n[{$ORDER_TYPE2}] [{$PAY_TOTAL}원] [{$order_num}]", $vendor);

?>
