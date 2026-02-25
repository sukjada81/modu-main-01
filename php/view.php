<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER',	'image/icon');
define('UPLOAD_FOLDER', 'image/goods/upload');

include_once(PATH_LIB.'/class.ListPaging.php');

$sql = "DELETE FROM mallRN_cart WHERE direct = '1' && cart_id = '{$cart_id}'";
$mysql->query($sql);

$sql		= "SELECT * FROM mallRN_goods WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) {
	if(@$_SERVER['HTTP_REFERER']) {
		if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) alert('해당상품이 삭제되었거나 존재하지 않습니다.', $Main);
	}
	alert('해당상품이 삭제되었거나 존재하지 않습니다.', 'back');
}

if($data['display_use'] == 0) {
	if(@$_SERVER['HTTP_REFERER']) {
		if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) alert('해당상품이 삭제되었거나 존재하지 않습니다.', $Main);
	}
	alert('해당상품이 삭제되었거나 존재하지 않습니다.', 'back');
}

if($data['auth_ck'] == 'N' && $my_level < 100) {
	if(@$_SERVER['HTTP_REFERER']) {
		if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) alert('해당상품이 삭제되었거나 존재하지 않습니다.', $Main);
	}
	alert('해당상품이 삭제되었거나 존재하지 않습니다.', 'back');
}

$VENDOR = $data['vendor'];

if($VENDOR) {
	$sql	= "SELECT comp_name, sell, delivery_type FROM mallRN_vendor WHERE id = '{$VENDOR}'";
	$vinfo	= $mysql->one_row($sql);
	
	if($vinfo['sell'] == 'N') {		
		if(@$_SERVER['HTTP_REFERER']) {
			if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) alert('해당상품이 삭제되었거나 존재하지 않습니다.', $Main);
		}
		alert('해당상품이 삭제되었거나 존재하지 않습니다.', 'back');
	}

	$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$VENDOR}'";
	$vshop_config	= $mysql->one_row($sql);

	if($vinfo['delivery_type'] == 0) {
		$shop_config['delivery_type']		= $vshop_config['delivery_type'];
		$shop_config['delivery_p_price1']	= $vshop_config['delivery_p_price1'];
		$shop_config['delivery_p_price2']	= $vshop_config['delivery_p_price2'];
	}

	$shop_config['goods_delivery_info']			= $vshop_config['goods_delivery_info'];
	$shop_config['goods_refund_info']			= $vshop_config['goods_refund_info'];
	$shop_config['goods_exchange_info']			= $vshop_config['goods_exchange_info'];
	$shop_config['goods_as_info']				= $vshop_config['goods_as_info'];
	$shop_config['delivery_im_areas1_used']		= $vshop_config['delivery_im_areas1_used'];
	$shop_config['delivery_im_areas1_price']	= $vshop_config['delivery_im_areas1_price'];
	$shop_config['delivery_im_areas2_used']		= $vshop_config['delivery_im_areas2_used'];
	$shop_config['delivery_im_areas2_price']	= $vshop_config['delivery_im_areas2_price'];

	$STORE_NAME		= ($vshop_config['basic_name'])	 ? stripslashes($vshop_config['basic_name']) : stripslashes($vinfo['comp_name']);

	unset($vinfo, $vshop_config);
}

$cate		= checkGetVar('cate');
if(!$cate)	$cate = $data['cate'];

$v_my_id	= "";
if(isset($_COOKIE['v_my_id'])) {
	$v_my_id			= base64_decode($_COOKIE['v_my_id']); 	
	if($v_my_id != $VENDOR) $v_my_id = "";
}

if(!$v_my_id) checkCateAccess($cate);

$is_member = ($my_id) ? 1 : 0;
$IS_LOGIN = ($my_id) ? 1 : 0;

$goods_info             = getGoodsInfo($data);
$GOODS_IMAGE            = $goods_info['image1'];
$GOODS_ICON             = $goods_info['icon'];
$GOODS_NAME             = $goods_info['name'];
$GOODS_NAME_CODE_ABLE   = $goods_info['name_code_able'];

// 소비자가 "원본 숫자" 먼저 확보 (DB 필드 우선)
$consumer_raw = 0;
if(isset($data['consumer_price'])) {
    $consumer_raw = (int)$data['consumer_price'];
} else if(isset($goods_info['consumer_price'])) {
    // getGoodsInfo가 "12,000" 같은 문자열이면 숫자로 변환
    $consumer_raw = (int)str_replace(",", "", $goods_info['consumer_price']);
}

// 화면 표시용 소비자가(문자열)
$GOODS_CONSUMER_PRICE = ($consumer_raw > 0) ? number_format($consumer_raw, CONF_FLOAT_CNT) : "";

// 기본은 판매가(회원 가격)
$GOODS_PRICE = $goods_info['price'];

// 비회원이면 "소비자가만" 보여야 하므로, 메인/옵션/총금액 기준가를 소비자가로 강제
if(!$is_member && $consumer_raw > 0) {
    $GOODS_PRICE   = number_format($consumer_raw, CONF_FLOAT_CNT);
    $data['price'] = $consumer_raw; // 옵션/합계 계산에서 참조하는 경우 대비
}

$GOODS_DATAIL          = $goods_info['detail'];

$GOODS_CODE            = stripslashes($data['goods_code']);
$GOODS_MODEL           = stripslashes($data['model']);
$GOODS_MAKE            = stripslashes($data['make']);
$GOODS_ORIGIN          = stripslashes($data['origin']);
$GOODS_BRAND           = stripslashes($data['brand']);
$GOODS_ORIG_PRICE       = number_format($data['price'], CONF_FLOAT_CNT);
$GOODS_LIMIT_QTY        = $data['limit_qty'];
$GOODS_PRICE_MENT       = $data['price_ment'];
if($GOODS_PRICE_MENT != '') $data['sale_use'] = 0;

$SHARE_URL				= ABSOLUTE_PATH_SHOP."{$Main}?channel=view&uid={$uid}";
$SHARE_IMG				= ABSOLUTE_PATH_SHOP."img/goods{$data['image1']}";



######################## 쿠폰관련 #############################
if($is_member && $goods_info['coupon_price']) {
	$GOODS_COUPON_PRICE	= $GOODS_PRICE;
	$tmp_price1			= (int) str_replace(",", "", $GOODS_PRICE);
	$tmp_price2			= (int) str_replace(",", "", $goods_info['coupon_price']);
	$GOODS_PRICE		= number_format($tmp_price1 + $tmp_price2, CONF_FLOAT_CNT);
	$COUPON_MSG			= $goods_info['coupon_msg']." 쿠폰 다운";
	$COUPON_UID			= $goods_info['coupon_uid'];
	$tpl->parse("is_coupon_price");
	
	$COUPON_DOWN_YN = 0;
	if($my_id) {		
		$sql = "SELECT count(*) FROM mallRN_coupon WHERE g_uid = '{$uid}' && status = 0 && e_date > '".date("Y-m-d")."' && id = '{$my_id}'";
		if($mysql->get_one($sql) == 0) {
			couponIssuance($COUPON_UID, $my_id, $uid);
		}
		$COUPON_DOWN_YN = 1;
	}
}
######################## 쿠폰관련 #############################

if($is_member && $GOODS_ORIG_PRICE != $GOODS_PRICE) {
	$sale_msg_array = array();
	if($my_discount)	$sale_msg_array[] = "회원등급할인 {$my_discount}%";
	
	if($data['exhibition'] && $data['exhibition'] != ',') {	
		foreach($event_info_array as $k => $v) {
			if(preg_match("/,{$k},/i", $data['exhibition'])) {				
				$sale_msg_array[] = "이벤트할인 {$v}%";
				break;
			}
		}
	}

	$GOODS_SALE_MSG = join(", ", $sale_msg_array);

	$tpl->parse("is_goods_orig_price");
	unset($sale_msg_array);
}

if($data['detail_image_only'] == 0) {
	$GOODS_EXPLAINS = add_escape_re_string($data['explains']);
}
else {
	$GOODS_EXPLAINS = detailImageToTag($data['uid'], $data['detail_image_type'], $data['detail_image'], $data['moddate']);
}

if($data['require_info']) {
	$require_info = explode("|*|", $data['require_info']);
	foreach($require_info as $k => $v) {
		$require_info2	= explode("|", $v);
		$require_name	= $require_info2[0];
		$require_value	= $require_info2[1];		
		$tpl->parse("loop_require_info");
	}	
	unset($require_name, $require_value, $require_help, $require_info, $require_info2);
}

if($data['information_use'] == 1) {
	$GOODE_DELIVERY_INFO	= add_escape_re_string($shop_config['goods_delivery_info']);
	$GOODE_REFUND_INFO		= add_escape_re_string($shop_config['goods_refund_info']);
	$GOODE_EXCHANGE_INFO	= add_escape_re_string($shop_config['goods_exchange_info']);
	$GOODE_AS_INFO			= add_escape_re_string($shop_config['goods_as_info']);

}
else {
	$GOODE_DELIVERY_INFO	= add_escape_re_string($data['delivery_info']);
	$GOODE_REFUND_INFO		= add_escape_re_string($data['refund_info']);
	$GOODE_EXCHANGE_INFO	= add_escape_re_string($data['exchange_info']);
	$GOODE_AS_INFO			= add_escape_re_string($data['as_info']);
}

######################## 추가 이미지 #############################
$i2 = 0;
$tpl->parse("loop_image");
$img_block		= floor($uid / 10000);
$temp_upload	= $img_block.'/'.$uid;		

$other_upload = UPLOAD_FOLDER.'/'.$temp_upload;

if($data['other_image']) {
	$other_image = explode(",",$data['other_image']);
	for($i = 0, $cnt = count($other_image); $i < $cnt; $i ++) {
		if(!$other_image[$i]) continue;
		$GOODS_IMAGE	= $other_upload.'/'.$other_image[$i];
		$i2				= $i + 1;
		$tpl->parse("loop_image");
	}	
	$tpl->parse("is_image_swiper");
	unset($other_image);		
}
######################## 추가 이미지 #############################

$GOODS_MILEAGE = 0 ; 
switch($data['mileage_type']) {
	case "1" : 
		$GOODS_MILEAGE	= $shop_config['member_mileage_order'] + $my_mileage; 
	break;
	case "3" : 
		if($my_level) {
			$mileage_level = explode("|*|", $data['mileage_level']);
			foreach($mileage_level as $k => $v) {
				$mileage_level2 = explode("|", $v);
				if($my_level == $mileage_level2[0]) {
					$GOODS_MILEAGE = $mileage_level2[1];
					break;
				}
			}
		}
		unset($mileage_level, $mileage_level2);
	break;
	case "4" :
		if($my_level) $GOODS_MILEAGE = $data['mileage_common'];
	break;
}

$ck_infos	= 0;

if($GOODS_DATAIL)	$tpl->parse("is_goods_detail");
if($GOODS_MILEAGE)	$tpl->parse("is_goods_mileage");
if($is_member && $GOODS_CONSUMER_PRICE) {
	$tpl->parse("is_goods_consumer_price");
	$ck_infos	= 1;
}
if($GOODS_CODE) {
	$tpl->parse("is_goods_code");
	$ck_infos	= 1;
}
if($GOODS_MODEL) {
	$tpl->parse("is_goods_model");
	$ck_infos	= 1;
}
if($GOODS_MAKE) {
	$tpl->parse("is_goods_make");
	$ck_infos	= 1;
}
if($GOODS_ORIGIN) {
	$tpl->parse("is_goods_origin");
	$ck_infos	= 1;
}
if($GOODS_BRAND) {
	$tpl->parse("is_goods_brand");
	$ck_infos	= 1;
}

if($data['making_info']) {	
	$making_info = explode("|*|", $data['making_info']);
	foreach($making_info as $k => $v) {
		$making_info2	= explode("|", $v);
		if($making_info2[0]) {
			$making_name	= $making_info2[0];
			$making_value	= $making_info2[1];
			$tpl->parse("loop_making_info");
		}
	}	
	unset($making_name, $making_value, $making_info, $making_info2);
}

$GOODS_ABLE_QTY	= 0;
if($GOODS_LIMIT_QTY) {
	if($my_id) {
		$GOODS_ABLE_QTY	= $GOODS_LIMIT_QTY - getOrderQty($uid);
		if($GOODS_ABLE_QTY < 0) $GOODS_ABLE_QTY = 0;
	}
	$tpl->parse("is_limit_qty");	
}

if($ck_infos == 1) @$tpl->parse("is_goods_infos");
######################## 배송비 #############################
switch($data['delivery_type']) {
	case "1" : 
		if($shop_config['delivery_type'] == 'F') $DELIVERY_MESSAGE = "무료배송";
		else if($shop_config['delivery_type']=='D') $DELIVERY_MESSAGE = "착불 (".number_format($shop_config['delivery_d_price'])."원)";
		else {
			$DELIVERY_MESSAGE = number_format($shop_config['delivery_p_price2'])."원 (주문금액 ".number_format($shop_config['delivery_p_price1'])."원 이상 구매시 무료)";
		}
	break;
	case "2" :
		$DELIVERY_MESSAGE = "무료배송";
	break;
	case "3" : 
		$DELIVERY_MESSAGE = "착불";
	break;
	case "4" :
		$DELIVERY_MESSAGE = number_format($data['delivery_price'])."원";
	break;
	case "5" :		
		$DELIVERY_MESSAGE = number_format($data['delivery_price'])."원(".number_format($data['delivery_type_qty'])."개당)";
	break;
}

if($data['delivery_type'] == 1) {
	$DELIVERY_ADD_MESSAGE = "";
	if($shop_config['delivery_im_areas1_used'] == 1) $DELIVERY_ADD_MESSAGE .= "제주 추가 ".number_format($shop_config['delivery_im_areas1_price'])."원";
	if($shop_config['delivery_im_areas2_used'] == 1) {
		if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE .= ", ";			
		$DELIVERY_ADD_MESSAGE .= "제주 외 도서지역 추가 ".number_format($shop_config['delivery_im_areas2_price'])."원";
	}
}
else {
	$DELIVERY_ADD_MESSAGE = "";
	if($data['delivery_im_areas1_used'] == 1) $DELIVERY_ADD_MESSAGE .= "제주 추가 ".number_format($data['delivery_im_areas1_price'])."원";
	if($data['delivery_im_areas2_used'] == 1) {
		if($DELIVERY_ADD_MESSAGE) $DELIVERY_ADD_MESSAGE .= ", ";			
		$DELIVERY_ADD_MESSAGE .= "제주 외 도서지역 추가 ".number_format($data['delivery_im_areas2_price'])."원";
	}
}
if($DELIVERY_ADD_MESSAGE) $tpl->parse("is_add_delivery");
######################## 배송비 #############################

######################## 품절확인 #############################
$sold_out = 0;
if($data['sale_use'] == 0) $sold_out = 1;
else if($data['option_use'] == 1) {
	$sql		= "SELECT count(*) FROM mallRN_goods_option WHERE guid = '{$uid}' && used = 1";
	$op_total1	= $mysql->get_one($sql);
	$sql		= "SELECT count(*) FROM mallRN_goods_option WHERE guid = '{$uid}' && used = 1 && qty_type = 0 && qty < 1";
	$op_total2	= $mysql->get_one($sql);
	if($op_total1 - $op_total2 == 0)  $sold_out = 1;
}
else if($data['qty_type'] == 0 && $data['qty'] < 1) $sold_out = 1;

if($sold_out == 1)	{
	$tpl->parse("is_sold_out1");
	@$tpl->parse("is_sold_out2");
}
else {	
	if($data['limit_qty'] > 0 && !$my_id) {
		$LIMIT_MSG = "회원만 구매 가능 합니다.";		
		$tpl->parse("is_member_only1");
		@$tpl->parse("is_member_only2");
	}
	else if($data['limit_qty'] > 0 && $GOODS_ABLE_QTY == 0) {
		$LIMIT_MSG = "구매 하실 수 없습니다.";		
		$tpl->parse("is_member_only1");
		@$tpl->parse("is_member_only2");
	}
	else {
		$tpl->parse("is_not_sold_out1");
		@$tpl->parse("is_not_sold_out2");
	}
}
######################## 품절확인 #############################

$GOODS_PRICETT = $GOODS_PRICE;
if($data['option_use'] == 1) {
	######################## 옵션 #############################	

	$option_info	= explode("|*|", $data['option_info']);	
	$OPTION_CNT		= count($option_info);

	foreach($option_info as $k => $v) {
		
		$option_info2	= explode("|", $v);
		$OPTION_NAME	= $option_info2[0];

		if($k == 0) {
			$sql = "SELECT value FROM mallRN_goods_option WHERE guid = '{$uid}' && used = 1 ORDER BY sequence ASC";
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
					$sql	= "SELECT * FROM mallRN_goods_option WHERE guid = '{$uid}' && used = 1 && value like '{$v2}' ORDER BY sequence ASC";
					$op_row	= $mysql->one_row($sql);

					if(!$op_row) continue;

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
	
	$tpl->parse("is_goods_option");
	######################## 옵션 #############################
	$QTY_TYPE	= "";
	$SAVE_QTY	= "";
	$GOODS_PRICETT = 0;
}
else {
	$OPTION_CNT = 0;
	$QTY_TYPE	= $data['qty_type'];
	$SAVE_QTY	= $data['qty'];

	if($QTY_TYPE == 0)	{
		$SAVE_QTYS = number_format($SAVE_QTY);
		$tpl->parse("is_qty_ment");
	}

	$tpl->parse("is_goods_default");
}

######################## 연관상품 #############################
if($data['related_goods_type'] > 0) {

	$goods_field = array();
	foreach($default_goods_field as $k => $v) {
		$goods_field[] = "{$v}";
	}
	$goods_field	= join(", ", $goods_field);

	$ck_goods_array	= array();

	switch($data['related_goods_type']) {
		case "1" : case "2" :		
			$sql		= "SELECT goods FROM mallRN_order_related_goods WHERE INSTR(goods, ',{$uid},') ORDER BY uid DESC LIMIT 100";
			$mysql->query($sql);
			
			$related_goods	= array();
			while($row2 = $mysql->fetch_array()){ 
				$goods_tmp		= str_replace(",{$uid},", ",", $row2['goods']);
				$goods_tmp		= substr($goods_tmp, 1, -1);
				$related_goods	= array_merge($related_goods, explode(",", $goods_tmp));
			}

			if($data['related_goods_type'] == 1) {
				$sql = "SELECT uid FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && cate = '{$cate}'";
				$mysql->query($sql);

				while($row2 = $mysql->fetch_array()){ 
					$related_goods[] = $row2['uid'];
				}
			}

			shuffle($related_goods);
			$related_goods		= array_count_values($related_goods);
			arsort($related_goods);			
			foreach($related_goods as $k => $v) {				
				$sql	= "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && uid = '{$v}'";
				if($data2 = $mysql->one_row($sql)) {
					getGoodsInfo($data2, "related_goods");
					$ck_goods_array[] = $data2['uid'];
				}

				if(count($ck_goods_array) >= $SKIN_DEFINE['related_goods']) break;
			}

		break;

		case "3" :
			$re_where	= " && cate = '{$cate}'";
			$re_order	= "order_cnt DESC";
		break;
		case "4" :
			if($data['related_goods']) {
				$re_where	= " && uid IN ( {$data['related_goods']} ) ";
			}
			else {
				$re_where	= " && cate = '{$cate}'";		
			}
		break;
	}

	if($data['related_goods_type'] > 2) {
		$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 {$re_where} ORDER BY order_cnt DESC LIMIT {$SKIN_DEFINE['related_goods']}";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()){
			getGoodsInfo($row, "related_goods");
			$ck_goods_array[] = $row['uid'];
		}	
	}

	if(count($ck_goods_array) < $SKIN_DEFINE['related_goods']) {
		$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 ORDER BY order_cnt DESC LIMIT 10";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()){
			if(in_array($row['uid'], $ck_goods_array)) continue;
			getGoodsInfo($row, "related_goods");
			$ck_goods_array[] = $row['uid'];
			if(count($ck_goods_array) >= $SKIN_DEFINE['related_goods']) break;
		}	
	}

	$tpl->parse("is_related_goods");
	unset($ck_goods_array, $related_goods);
}
######################## 연관상품 #############################

######################## 판매자의 인기상품 #############################
if($VENDOR) {
	
	if(!isset($goods_field)) {
		$goods_field = array();
		foreach($default_goods_field as $k => $v) {
			$goods_field[] = "{$v}";
		}
		$goods_field = join(", ", $goods_field);
	}

	$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && vendor = '{$VENDOR}' && uid != '{$uid}' ORDER BY store_display1 DESC, store_display2 DESC, store_display3 DESC, order_cnt DESC LIMIT 6";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		getGoodsInfo($row, "vendor_goods");
	}	

	$sql			= "SELECT count(*) FROM mallRN_favorite_store WHERE vendor = '{$VENDOR}'";
	$FSTORE_CNT		= number_format($mysql->get_one($sql));

	$tpl->parse("is_vendor_goods");
}
######################## 판매자의 인기상품 #############################

######################## 최근본 상품 등록 #############################
$signdate = time();
$sql = "SELECT uid FROM mallRN_goods_recent_view WHERE check_id = '{$cart_id}' && g_uid = '{$uid}'";
if($recent_uid = $mysql->get_one($sql)) {
	$sql = "UPDATE mallRN_goods_recent_view SET signdate = '{$signdate}' WHERE uid = '{$recent_uid}'";	
}
else {
	$sql = "SELECT count(*) FROM mallRN_goods_recent_view WHERE check_id = '{$cart_id}'";
	$rcnt = $mysql->get_one($sql);
	if($rcnt > 29) {
		$rcnt2 = $rcnt - 29;
		$sql = "DELETE FROM mallRN_goods_recent_view WHERE check_id = '{$cart_id}' ORDER BY uid ASC LIMIT {$rcnt2}";
		$mysql->query($sql);
	}
	$sql = "INSERT INTO mallRN_goods_recent_view SET check_id = '{$cart_id}', g_uid = '{$uid}', signdate = '{$signdate}'";
}
$mysql->query($sql);
######################## 최근본 상품 등록 #############################

######################## 조회수 증가, 본 상품 카운트 #############################
$check_date = strtotime(date("Y-m-d", time()));
$sql = "SELECT count(*) FROM mallRN_goods_view WHERE check_id = '{$cart_id}' && g_uid = '{$uid}' && signdate > '{$check_date}'";
if($mysql->get_one($sql) == 0) {
	$sql	= "UPDATE mallRN_goods SET view_cnt = view_cnt + 1 WHERE uid = '{$uid}'";	
	$mysql->query($sql);

	$mobile	= $is_mobile ? "Y" : "N";
     
	$sql	= "INSERT INTO mallRN_goods_view SET check_id = '{$cart_id}', g_uid = '{$uid}', vendor = '{$VENDOR}', mobile = '{$mobile}', signdate = '{$signdate}'";		
	$mysql->query($sql);	
}
unset($check_date);
######################## 조회수 증가, 본 상품 카운트 #############################

######################## 구매후기 #############################
$sql		= "SELECT count(*) FROM mallRN_review WHERE g_uid = '{$uid}'";
$REVIEW_CNT = $mysql->get_one($sql);

$STARSALL	= "0.0";
$STARSPER	= 0;

if($REVIEW_CNT > 0) {	
	
	$total_stars1	= 0;
	$total_stars2	= 0;
	for($i = 1; $i < 6; $i ++) {
		$sql			= "SELECT count(*) FROM mallRN_review WHERE g_uid = '{$uid}' && stars = '{$i}'";
		$cnt			= $mysql->get_one($sql);
		${"STARS".$i}	= number_format($cnt);
		${"WIDTH".$i} = (100 * $cnt) / $REVIEW_CNT;

		$total_stars1	+= $cnt;
		$total_stars2	+= ($cnt * $i);
	}

	if($total_stars1 > 0) {
		$STARSALL		= number_format($total_stars2 / $total_stars1, 1);
		$STARSPER		= $STARSALL * 20;
	}

	$lastPage = ceil($REVIEW_CNT / 10);
	$tpl->parse("is_review_list");
}
else {
	for($i = 1; $i < 6; $i ++) {
		${"STARS".$i} = ${"WIDTH".$i} = 0;
	}
	$lastPage = 0;
	$tpl->parse("is_review_empty");
	$tpl->parse("is_review_empty2");
}
$REVIEW_CNT	= number_format($REVIEW_CNT);
######################## 구매후기 #############################

######################## 상품문의 #############################
$sql = "SELECT count(*) FROM mallRN_inquiry WHERE g_uid = '{$uid}'";
$INQUIRY_CNT = $mysql->get_one($sql);

if($INQUIRY_CNT > 0) {	
	$lastPage2 = ceil($INQUIRY_CNT / 10);
	$tpl->parse("is_inquiry_list");
}
else {
	$lastPage2 = 0;
	$tpl->parse("is_inquiry_empty");
	$tpl->parse("is_inquiry_empty2");
}
$INQUIRY_CNT	= number_format($INQUIRY_CNT);

if($shop_config['inquiry_access_write'] == 1) $tpl->parse("is_inquiry_write");
######################## 상품문의 #############################

$favGoodsSelect = 0;
$favStoreSelect = 0;
if(!$my_id) $tpl->parse("is_login");
else {
	$sql = "SELECT count(*) FROM mallRN_favorite_goods WHERE id = '{$my_id}' && g_uid = '{$uid}'";
	if($mysql->get_one($sql) > 0) $favGoodsSelect = 1;

	if($VENDOR) {
		$sql = "SELECT count(*) FROM mallRN_favorite_store WHERE id = '{$my_id}' && vendor = '{$VENDOR}'";
		if($mysql->get_one($sql) > 0) $favStoreSelect = 1;
	}
}

$sql			= "SELECT count(*) FROM mallRN_favorite_goods WHERE g_uid = '{$uid}'";
$favGoodsCnt	= number_format($mysql->get_one($sql));

######################## 네이버페이 버튼 #############################
if($shop_config['naverpay_used']) {
	$naverpay_key2		= $shop_config['naverpay_key2'];
	$naver_enable		= 'Y';
	$naver_proc			= "buy_nc";
	if($sold_out == 1)	{
		$naver_enable	= 'N';
		$naver_proc		= "not_buy_nc";
	}
	if($GOODS_LIMIT_QTY) $naver_enable	= 'N';
	@$tpl->parse("is_naver_pay");
}
######################## 네이버페이 버튼 #############################

?>