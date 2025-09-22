<?php 

include_once("../common/top.php");

define('IMAGE_FOLDER', '../../image/goods/img');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","order_info.html");
$tpl->scan_area("main");

$addstring			= "";
$search_variable	=  array('status', 'field', 'keyword','field2','keyword2','field3','keyword3','field4','keyword4', 'date_type', 's_date', 'e_date', 's_range1', 'e_range1', 'range1', 'member', 'mobile', 'pay_type', 'pay_status', 'cash_receipts', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$order_num = checkGetVar('order_num');
if(!$order_num) alert("필수 정보가 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
if(!$info = $mysql->one_row($sql)) {
	alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');
}

$sql = "SELECT count(*) FROM mallRN_order_goods WHERE vendor_delivery = '{$v_my_id}' && order_num = '{$order_num}' && reals = 1";
if($mysql->get_one($sql) == 0) {
	alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');
}

######################## 배송업체 정보 #############################
$delivery_info_array	= array();
$delivery_url_array		= array();

$sql = "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
if($delivery_info = $mysql->get_one($sql)){
	$delivery_info = explode("|*|", $delivery_info);
}

$sql = "SELECT delivery_info FROM mallRN_vendor_configuration WHERE vendor = '{$v_my_id}'";
$delivery_info_vendor	= $mysql->get_one($sql);

if(!$delivery_info_vendor) {
	$tmp_array			= array();
	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i2 = 1, $cnt2 = count($delivery_info); $i2 < $cnt2; $i2 ++) {
			$delivery_info2 = explode("|", $delivery_info[$i2]);
			$tmp_array[] = "{$delivery_info2[0]}|1";			
		}
	}
	$delivery_info_vendor = join("|*|", $tmp_array);
	unset($tmp_array);
}

$delivery_info_vendor	= explode("|*|", $delivery_info_vendor);
for($i = 0, $cnt = count($delivery_info_vendor); $i < $cnt; $i++) {	
	$delivery_info_vendor2 = explode("|", $delivery_info_vendor[$i]);	
	if($delivery_info_vendor2[1] == '1') {
		if($delivery_info[1] && $delivery_info[1] != '|||') {
			for($i2 = 1, $cnt2 = count($delivery_info); $i2 < $cnt2; $i2 ++) {

				$delivery_info2 = explode("|", $delivery_info[$i2]);
				if($delivery_info2[3] == 0) continue;
				if($delivery_info_vendor2[0] != $delivery_info2[0]) continue;		

				$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];
				$delivery_url_array[$delivery_info2[0]] = $delivery_info2[2];
			}
		}
	}
}
######################## 배송업체 정보 #############################

$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && vendor_delivery = '{$v_my_id}' && reals = 1";
$mysql->query($sql);

$TOTAL			= 0;
$VENDOR_CANCEL	= 0;
$CANCEL_CNT		= 0;
$DISABLED		= "";
$status_array	= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array	= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 

while($row = $mysql->fetch_array()) {
	$VENDOR				= $row['vendor_delivery'];

	$sql	= "SELECT * FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && vendor = '{$VENDOR}'";
	$data	= $mysql->one_row($sql);
	$delivery_config = explode("|", $data['info']);

	if($delivery_config[0] == 'F') $DELIVERY_MESSAGE	= "무료배송";
	else if($delivery_config[0] == 'D') $DELIVERY_MESSAGE	= "착불 (예상금액 : ".number_format($delivery_config[3])."원)";
	else $DELIVERY_MESSAGE	= number_format($delivery_config[2])."원 (주문금액 ".number_format($delivery_config[1])."원 이상 구매시 무료)";
	
	$sql	= "SELECT comp_name, sell FROM mallRN_vendor WHERE id = '{$VENDOR}'";
	$vinfo	= $mysql->one_row($sql);
	$VENDOR_NAME = stripslashes($vinfo['comp_name'])."배송";	
	foreach($delivery_info_array as $k => $v) {
		$delivery_num	= $k;
		$delivery_name	= $v;
				
		$tpl->parse("loop_delivery");
	}

	$sql = "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && vendor_delivery = '{$VENDOR}' && reals = 1 ORDER BY vendor_delivery ASC, status ASC, uid DESC";
	$mysql->query2($sql);

	$VENDOR_PRICE				= 0;
	$VENDOR_DISCOUNT			= 0;
	$VENDOR_DELIVERY			= 0;
	$VENDOR_TOTAL				= 0;
	$sum_delivery_option		= array();
	
	while($row2 = $mysql->fetch_array(2)) {
		
		$UID				= $row2['uid'];
		$QTY				= $row2['qty'];
		$G_UID				= $row2['g_uid'];

		$sql	= "SELECT cate, image3 FROM mallRN_goods WHERE uid = '{$G_UID}'";
		if(!$gdata	= $mysql->one_row($sql)) {
			$gdata['cate'] = "";
			$gdata['image3'] = "";
		}

		$G_LINK				= "{$SMain}?channel=view&uid={$G_UID}&cate={$gdata['cate']}";	
		$G_LINK2			= "../goods/goods_info.php?mode=modify&uid={$G_UID}";	
		$G_NAME				= stripslashes($row2['g_name']);
		if($gdata['image3'])	$G_IMAGE = IMAGE_FOLDER."{$gdata['image3']}";
		else					$G_IMAGE = "../../image/no_image.png";
		$G_OPTION			= stripslashes($row2['option_name']);
		$PRICE				= number_format($row2['orig_price'], CONF_FLOAT_CNT);
		$SUM_PRICE			= number_format((str_replace(",", "", $PRICE) * $QTY), CONF_FLOAT_CNT);
		
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
				if($row2['delivery_price'] == 0)	$DELIVERY	= "묶음배송";
				else								$DELIVERY	= number_format($row2['delivery_price'])."원";
			break;
			case "5" :
				$DELIVERY				= number_format($row2['delivery_price'])."원(".number_format($row2['delivery_type_qty'])."개당)";			
			break;
		}
		$row2['delivery_price'] += $row2['delivery_add_price'];
		$G_DELIVERY_PRICE	= $row2['delivery_price'];
		
		if($G_OPTION) $tpl->parse("is_option");
		
		if($row2['delivery_add_price'] > 0) {
			if($row2['delivery_type'] == 5) $DELIVERY_ADD	= number_format($row2['delivery_add_price'])."원(".number_format($row2['delivery_type_qty'])."개당)";
			else $DELIVERY_ADD	= number_format($row2['delivery_add_price'])."원";
			$tpl->parse("is_delivery_add");
		}
		
		$VENDOR_PRICE		+= str_replace(",", "", $SUM_PRICE);

		if($row2['status2'])	$STATUS	= $status_array[$row2['status']].$status2_array[$row2['status2']];
		else					$STATUS	= $status_array[$row2['status']];

		if($row2['delivery_info']) {
			$tmps				= explode("|", $row2['delivery_info']);
			$DELIVERY_INFO		= $delivery_info_array[$tmps[0]]." : ".$tmps[1];
			$delivery_url		= $delivery_url_array[$tmps[0]];
			$delivery_number	= $tmps[1];
			$tpl->parse("is_delivery_info");
		}

		$STATUS_DATE	= date("m-d H:i", $row2['status_date']);

		if($row2['status'] > 3) $DISABLED = "disabled";
		else					$DISABLED = "";

		$tpl->parse("is_log");

		$QTY			= number_format($QTY);
		
		$tpl->parse("loop_order_goods");

		if($row2['delivery_type'] == 5) {
			if($row2['option']) {				
				if(isset($sum_delivery_option[$G_UID]) && $sum_delivery_option[$G_UID] > 0) {
					$G_DELIVERY_PRICE	= 0;					
				}
				else {
					$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$G_UID}' && !(status = 9 && status2 = 5)";
					$option_qty			= $mysql->get_one($sql);
					$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($option_qty / $row2['delivery_type_qty']);
					$sum_delivery_option[$G_UID] = $option_qty;
				}
			}
			else {
				$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($row2['qty'] / $row2['delivery_type_qty']);
			}
		}

		if(($row2['status'] == 8 || $row2['status'] == 9) && $row2['status2'] == 5) {
			$tmp_price		= str_replace(",", "", $SUM_PRICE);
			$VENDOR_CANCEL	+= $tmp_price + $G_DELIVERY_PRICE;
			$CANCEL_CNT		++;
			unset($tmp_price);
		}

		$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;
				
		$TOTAL ++;
	}
	
	if($data['price'] > 0) {
		$VENDOR_DELIVERY += $data['price'];
	}

	$VENDOR_TOTAL		= number_format($VENDOR_PRICE - $VENDOR_DISCOUNT + $VENDOR_DELIVERY, CONF_FLOAT_CNT);
	$VENDOR_PRICE		= number_format($VENDOR_PRICE, CONF_FLOAT_CNT);
	$VENDOR_DELIVERY	= number_format($VENDOR_DELIVERY, CONF_FLOAT_CNT);	

	if($TOTAL == $CANCEL_CNT) {
		$VENDOR_CANCEL		= $VENDOR_TOTAL;
		$VENDOR_TOTAL		= 0;
	}
	else {
		$tmp_price			= str_replace(",", "", $VENDOR_TOTAL);
		$VENDOR_TOTAL		= number_format($tmp_price - $VENDOR_CANCEL, CONF_FLOAT_CNT);
		$VENDOR_CANCEL		= number_format($VENDOR_CANCEL, CONF_FLOAT_CNT);	

		unset($tmp_price);
	}
	
	if($v_my_delivery_type == 0) $tpl->parse("is_delivery");

	$tpl->parse("loop_order");	
}

$item_array			= array('name', 'id', 'cell', 'email', 'name2', 'cell2', 'postcode', 'address1', 'address2', 'message', 'memo', 'pay_type', 'pay_status', 'status_date', 'signdate');
$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
$pay_status_array	= array("A" => "미결제", "B" => "가상계좌발급완료", "C" => "결제완료", "D" => "결제실패");

foreach($item_array as $k => $v) {
	${$v} = stripslashes($info[$v]);
}

$message			= specialStrReplace2($message);
$address2			= specialStrReplace2($address2);
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

####################### 관리자 로그 ##########################	
vendorLog($v_my_id, "주문정보 - {$order_num}", 2);
####################### 관리자 로그 ##########################


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>