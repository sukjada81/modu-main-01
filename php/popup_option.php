<?php

$pop_title	= "옵션변경";

include_once('../php/popup_init.php'); 

$uid		= checkGetVar('uid');
$g_uid		= checkGetVar('g_uid');

if(!$uid || !$g_uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

$sql = "SELECT * FROM mallRN_goods WHERE uid = '{$g_uid}'";
if(!$data = $mysql->one_row($sql)) iframeViewError("상품이 없거나 삭제되었습니다.");

$sql = "SELECT * FROM mallRN_cart WHERE uid = '{$uid}'";
if(!$data2 = $mysql->one_row($sql)) iframeViewError("장바구니에 없거나 삭제되었습니다.");

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_option.html");
$tpl->scan_area("main");

$goods_info				= getGoodsInfo($data);

$GOODS_NAME_CODE_ABLE	= $goods_info['name_code_able'];
$GOODS_PRICE			= $goods_info['price'];
$GOODS_LIMIT_QTY		= $data['limit_qty'];

$GOODS_ABLE_QTY	= 0;
if($GOODS_LIMIT_QTY && $my_id) {
	$GOODS_ABLE_QTY	= $GOODS_LIMIT_QTY - getOrderQty($uid);
}

######################## 쿠폰관련 #############################
if($goods_info['coupon_price']) {
	$GOODS_COUPON_PRICE	= $goods_info['coupon_price'];
	
	$COUPON_DOWN_YN = 0;
	if($my_id) {
		$sql = "SELECT count(*) FROM mallRN_coupon WHERE g_uid = '{$g_uid}' && id = '{$my_id}'";
		if($mysql->get_one($sql) > 0) $COUPON_DOWN_YN = 1;
	}
}
######################## 쿠폰관련 #############################

######################## 옵션 #############################	
$option_info	= explode("|*|", $data['option_info']);	
$OPTION_CNT		= count($option_info);

foreach($option_info as $k => $v) {
	
	$option_info2	= explode("|", $v);
	$OPTION_NAME	= $option_info2[0];

	if($k == 0) {
		$sql = "SELECT value FROM mallRN_goods_option WHERE guid = '{$g_uid}' && used = 1 ORDER BY sequence ASC";
		$option_value_info	= $mysql->get_one_jum($sql, "|*|");
		$option_value_info2 = explode("|*|", $option_value_info);
		
		$option_value_array = array();
		foreach($option_value_info2 as $k2 => $v2) {
			$option_value_info3 = explode("|", $v2);
			if(!in_array($option_value_info3[0], $option_value_array)) $option_value_array[] = $option_value_info3[0];
		}

		foreach($option_value_array as $k2 => $v2) {
			$OPTION_INFO	= "";
			$OPTION_VALUE	= $v2;
			if($OPTION_CNT == 1) {
					$sql	= "SELECT * FROM mallRN_goods_option WHERE guid = '{$g_uid}' && used = 1 && value like '{$v2}' ORDER BY sequence ASC";
					$op_row	= $mysql->one_row($sql);

					if($op_row['price'] > 0)		$OPTION_VALUE .= " ( +" .number_format($op_row['price']). "원)";
					else if($op_row['price'] < 0)	$OPTION_VALUE .= " ( " .number_format($op_row['price']). "원)";

					if(($op_row['qty_type'] == 0 && $op_row['qty'] < 1) || $data['sale_use'] == 0)	{
						$option_soldout = "soldout";
						$OPTION_VALUE .= " [품절]";
					}
					else $option_soldout = "";

					$op_goods_price = getGoodsOptionPrice($op_row['price'], $uid);

					$OPTION_INFO = $op_row['uid']."|".$op_row['price']."|".$op_row['qty_type']."|".$op_row['qty']."|".$op_goods_price;
				}
			$tpl->parse("loop_option_value");
		}
	}

	$tpl->parse("loop_option");
}

$sql	= "SELECT * FROM mallRN_goods_option WHERE uid = '{$data2['option']}'";
$data3	= $mysql->one_row($sql);

$goods_price = getGoodsOptionPrice($data3['price'], $g_uid);		

if($data3['price'] > 0)			$option_value = $data3['value']." ( +" .number_format($data3['price']). "원)";
else if($data3['price'] < 0)	$option_value = $data3['value']." ( " .number_format($data3['price']). "원)";
else							$option_value = $data3['value'];
$option_info	= $data3['uid']."|".$data3['price']."|".$data3['qty_type']."|".$data3['qty']."|".$goods_price;
$option_qty		= $data2['qty'];
$option_uid		= $data3['uid'];
######################## 옵션 #############################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>