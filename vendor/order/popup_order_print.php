<?php 

include_once("../common/popup_top.php");

define('IMAGE_FOLDER', '../../image/goods/img');

$order_num	= checkGetVar('order_num');

if(!$order_num) {
	$og_uid = checkGetVar('og_uid');

	if(!$og_uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

	$sql			= "SELECT order_num FROM mallRN_order_goods WHERE uid IN({$og_uid})";
	$order_num		= $mysql->get_one_jum($sql, "','");

	$og_uid_array	= explode(",", $og_uid);
}
else {
	if(!preg_match("/-/i", $order_num)) {
		$sql			= "SELECT order_num FROM mallRN_order_info WHERE uid IN({$order_num})";
		$order_num		= $mysql->get_one_jum($sql, "','");
	}
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_order_print.html");
$tpl->scan_area("main");

$sql	= "SELECT * FROM mallRN_order_info WHERE order_num IN ('{$order_num}') && reals = 1";
$mysql->query($sql);

while($info = $mysql->fetch_array()){
	if(!$info) alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');

	$order_num		= $info['order_num'];

	$sql			= "SELECT count(*) FROM mallRN_order_goods WHERE vendor_delivery = '{$v_my_id}' && order_num = '{$order_num}' && reals = 1";
	if($mysql->get_one($sql) == 0) alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');

	$TOTAL			= 0;
	$NUM			= 0;
	$status_array	= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
	$status2_array	= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
	$sum_delivery_option		= array();

	$sql = "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && vendor_delivery = '{$v_my_id}' && reals = 1 ORDER BY status ASC, uid DESC";
	$mysql->query2($sql);

	while($row2 = $mysql->fetch_array(2)) {

		if(isset($og_uid_array)) {
			if(!in_array($row2['uid'], $og_uid_array)) continue;
		}
		
		$UID				= $row2['uid'];
		$QTY				= $row2['qty'];
		$G_UID				= $row2['g_uid'];

		$sql	= "SELECT cate, image3 FROM mallRN_goods WHERE uid = '{$G_UID}'";
		if(!$gdata	= $mysql->one_row($sql)) {
			$gdata['cate'] = "";
			$gdata['image3'] = "";
		}

		$G_NAME				= stripslashes($row2['g_name']);
		if($gdata['image3'])	$G_IMAGE = IMAGE_FOLDER."{$gdata['image3']}";
		else					$G_IMAGE = "../../image/no_image.png";
		$G_OPTION			= stripslashes($row2['option_name']);
		$PRICE				= number_format($row2['price'], CONF_FLOAT_CNT);
		$SALE_PRICE			= $row2['use_coupon'] + $row2['discount'];
		
		$row2['delivery_price'] += $row2['delivery_add_price'];
		if($row2['delivery_type'] == 5) {			
			if($row2['option']) {				
				if(isset($sum_delivery_option[$G_UID]) && $sum_delivery_option[$G_UID] > 0) {
					$row2['delivery_price']	= 0;					
				}
				else {
					$sql					= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$G_UID}' && !(status = 9 && status2 = 5)";
					$option_qty				= $mysql->get_one($sql);
					$row2['delivery_price']	=  $row2['delivery_price'] * ceil($option_qty / $row2['delivery_type_qty']);
					$sum_delivery_option[$G_UID] = $option_qty;
				}
			}
			else {				
				$row2['delivery_price']	= $row2['delivery_price'] * ceil($row2['qty'] / $row2['delivery_type_qty']);
			}
		}		
		
		$DELIVERY			= number_format($row2['delivery_price'], CONF_FLOAT_CNT);
		
		if($G_OPTION) $tpl->parse("is_option");
		
		$ORIG_PRICE = 0;
		if($SALE_PRICE > 0) {
			$ORIG_PRICE			= number_format((str_replace(",", "", $PRICE) + $SALE_PRICE), CONF_FLOAT_CNT);		
		}
		else $ORIG_PRICE		= $PRICE;

		if($row2['status2'])	$STATUS	= $status_array[$row2['status']].$status2_array[$row2['status2']];
		else					$STATUS	= $status_array[$row2['status']];
		
		$colorLgray				= "";
		if(!($row2['status'] > 0 && $row2['status'] < 4)) $colorLgray = "colorLgray";

		$QTY			= number_format($QTY);
		$SUM_PRICE			= number_format(($row2['price'] * $QTY) + str_replace(",", "", $DELIVERY), CONF_FLOAT_CNT);
		$NUM			++;
		
		$tpl->parse("loop_order_goods");
				
		$TOTAL ++;		
	}

	$item_array			= array('name', 'id', 'cell', 'email', 'name2', 'cell2', 'postcode', 'address1', 'address2', 'message', 'memo', 'pay_type', 'pay_status', 'status_date', 'signdate');
	$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
	$pay_status_array	= array("A" => "미결제", "B" => "가상계좌발급완료", "C" => "결제완료", "D" => "결제실패");

	foreach($item_array as $k => $v) {
		${$v} = stripslashes($info[$v]);
	}

	$order_date			= date("Y-m-d H:i:s", $signdate);

	if(strlen($cell) == 12)			$cell	= substr($cell, 0, 4)."-".substr($cell, 4, 4)."-".substr($cell, 8, 4);
	else if(strlen($cell) == 11)	$cell	= substr($cell, 0, 3)."-".substr($cell, 3, 4)."-".substr($cell, 7, 4);
	else if(strlen($cell) == 10)	$cell	= substr($cell, 0, 2)."-".substr($cell, 2, 4)."-".substr($cell, 6, 4);

	if(strlen($cell2) == 12)		$cell2	= substr($cell2, 0, 4)."-".substr($cell2, 4, 4)."-".substr($cell2, 8, 4);
	else if(strlen($cell2) == 11)	$cell2	= substr($cell2, 0, 3)."-".substr($cell2, 3, 4)."-".substr($cell2, 7, 4);
	else if(strlen($cell) == 10)	$cell2	= substr($cell2, 0, 2)."-".substr($cell2, 2, 4)."-".substr($cell2, 6, 4);

	$pay_type			= $pay_type_array[$pay_type];
	$pay_status			= $pay_status_array[$pay_status];
	if($status_date) {
		$status_date	= date("Y-m-d H:i:s", $status_date);
		$tpl->parse("is_status_date");
	}

	if($id) {
		$MEMBER_ID		= $id;	
		$tpl->parse("is_member");
	}

	$tpl->parse("loop_order");
}


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>