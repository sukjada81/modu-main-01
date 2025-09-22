<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

for($i = 0; $i < 10; $i ++) {
	if($i == 6) continue;
	$sql		= "SELECT COUNT(*) FROM mallRN_order_goods a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_info WHERE id = '{$my_id}' && reals = 1) && a.status = '{$i}' && a.reals = 1";
	${"STATUS".$i."_CNT"}	= number_format($mysql->get_one($sql));
}

$sql			= "SELECT name FROM mallRN_member_level WHERE level = '{$my_level}'";
$LEVEL_NAME		= stripslashes($mysql->get_one($sql));

$sql			= "SELECT mileage FROM mallRN_member WHERE id = '{$my_id}'";
$MILEAGE		= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_coupon WHERE id = '{$my_id}' && status = 0 && e_date > '".date("Y-m-d 23:59:59")."'";
$COUPON_CNT		= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_favorite_goods WHERE id = '{$my_id}'";
$FAVORITE_CNT	= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_favorite_store WHERE id = '{$my_id}'";
$FAVORITE_CNT2	= number_format($mysql->get_one($sql));
$tpl->parse("is_store");

$sql			= "SELECT count(*) FROM mallRN_goods_recent_view WHERE check_id = '{$cart_id}'";
$RECENT_VIEW_CNT = number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_board_counsel WHERE id = '{$my_id}'";
$COUNSEL_CNT	= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_review WHERE id = '{$my_id}'";
$REVIEW_CNT		= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_inquiry WHERE id = '{$my_id}'";
$INQUIRY_CNT	= number_format($mysql->get_one($sql));

if(!$my_sns_type) $tpl->parse("is_sns_type");

if(!$mobile_header) {

	$sql			= "SELECT c.* FROM ( SELECT a.order_num FROM mallRN_order_info a WHERE id = '{$my_id}' && reals = 1 && signdate > ".strtotime('-1 MONTH', time())." ORDER BY uid DESC LIMIT 10 ) b JOIN mallRN_order_goods c ON b.order_num = c.order_num WHERE c.reals = 1 ORDER BY c.uid DESC";
	$mysql->query($sql);

	$status_array	= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
	$status2_array	= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
	$tmp_order_num	= "";

	while($row = $mysql->fetch_array()) {
		
		$SIGNDATE	= date("Y-m-d H:i:s", $row['signdate']);
		$G_NAME		= stripslashes($row['g_name']);
		$PRICE		= number_format($row['price'] * $row['qty']);
		$QTY		= number_format($row['qty']);
		$UID		= $row['uid'];
		$G_UID2		= $row['g_uid'];
		
		if($row['status2'])	$STATUS	= $status_array[$row['status']].$status2_array[$row['status2']];
		else				$STATUS	= $status_array[$row['status']];

		if($tmp_order_num == "" || $tmp_order_num != $row['order_num']) {
			$sql		= "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$row['order_num']}' && reals = 1";
			$ROWSPAN	= $mysql->get_one($sql);
			$ORDER_NUM2	= $row['order_num'];

			$sql	= "SELECT count(distinct(status)) FROM mallRN_order_goods WHERE order_num = '{$row['order_num']}'";
			if($mysql->get_one($sql) == 1) {
				$sql	= "SELECT pay_status, cancel_total, refund_total, pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$row['order_num']}' && reals = 1";
				$data	= $mysql->one_row($sql);

				$proc_status	= "";

				if($row['status'] < 2) {
					if($data['pay_status'] != 'C') {						
						$tpl->parse("is_btn_cancel_all");
					}
					else {
						if($data['pay_type'] == 'M' || $data['pay_type'] == 'C' || ($data['pay_type'] == 'R' &&  date("Y-m-d", $data['status_date']) == date("Y-m-d"))) {
							$GLOBALS['proc_status'] = "5";
							$tpl->parse("is_btn_cancel_all");
						}
						else $tpl->parse("is_btn_cancel_all2");
					}					
				}
			}
				
			$tpl->parse("is_order_info");
			$tmp_order_num = $row['order_num'];
		}	

		$sql			= "SELECT cate, image3 FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
		$data			= $mysql->one_row($sql);

		if($data) {
			if($data['image3']) $G_IMAGE = DEFAULT_PATH."image/goods/img{$data['image3']}";
			else				$G_IMAGE = DEFAULT_PATH."image/no_image.png";
			$G_LINK			= "{$Main}?channel=view&uid={$row['g_uid']}&cate={$data['cate']}";	
		}
		else {
			$G_IMAGE		= DEFAULT_PATH."image/no_image.png";
			$G_LINK			= "";
		}

		if($row['option_name']) {
			$G_OPTION	= $row['option_name'];
			$tpl->parse("is_option");		
		}

		if($row['use_coupon']) {
			$USE_COUPON = number_format($row['use_coupon']);
			$tpl->parse("is_coupon");		
		}

		if($row['delivery_price']) {
			if($row['delivery_type'] == 5)	$DELIVERY_PRICE = number_format($row['delivery_price'] * $row['qty']);
			else							$DELIVERY_PRICE = number_format($row['delivery_price']);
			$tpl->parse("is_delivery_price");		
		}

		if($row['delivery_info']) {
			$tmps			= explode("|", $row['delivery_info']);
			$delivery		= $tmps[0];
			
			$delivery_info = explode("|*|", $shop_config['delivery_info']);	
			for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {
				$delivery_info2 = explode("|", $delivery_info[$i]);
				if($delivery_info2[0] == $delivery) {
					$delivery_url = $delivery_info2[2];		
					break;
				}
			}	
			$delivery_number = str_replace(array("-", " "), "", $tmps[1]);

			if($row['status'] == 3) $tpl->parse("is_btn_delivery");			
			if($row['status'] == 7 && $row['status2'] == 4) {
				$tpl->parse("is_btn_delivery");
				$tpl->parse("is_btn_recipiency");
			}	
		}

		if($row['status'] == 0) $tpl->parse("is_btn_cancel");
		else if($row['status'] == 1) {
			$sql	= "SELECT pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$row['order_num']}' && reals = 1";
			$data	= $mysql->one_row($sql);
			
			if($data['pay_type'] == 'M' || $data['pay_type'] == 'C' || ($data['pay_type'] == 'R' &&  date("Y-m-d", $data['status_date']) == date("Y-m-d"))) {
				$sql = "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$row['order_num']}'";
				if($mysql->get_one($sql) == 1)	$tpl->parse("is_btn_cancel3");
				else							$tpl->parse("is_btn_cancel2");
			}
			else $tpl->parse("is_btn_cancel2");		
		}
		else if($row['status'] == 2) {
			$tpl->parse("is_btn_cancel2");
		}
		else if($row['status'] == 3) $tpl->parse("is_btn_recipiency");
		else if($row['status'] == 4) $tpl->parse("is_btn_confirmation");

		if($row['status'] == 3 || $row['status'] == 4) {
			$tpl->parse("is_btn_exchange");
			$tpl->parse("is_btn_return");
		}

		if($row['status'] == 4 || $row['status'] == 5) {
			$sql = "SELECT count(*) FROM mallRN_review WHERE og_uid = '{$row['uid']}'";
			if($mysql->get_one($sql) == 0) $tpl->parse("is_btn_review");			

			if($row['status'] == 4) {
				$TTL4			= "예정";
				$CONFIRM_DATE	= date("Y-m-d", $row['status_date'] + (86400 * $shop_config['order_auto_completed2']));
			}
			else {
				$TTL4			= "";
				$CONFIRM_DATE	= date("Y-m-d", $row['status_date']);
			}
			$tpl->parse("is_date_confirmation");
		}

		$tpl->parse("loop_list");
	}
}


?>