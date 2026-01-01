<?php

/******************************************************************************
 * shopping library 
 *
 * 쇼핑몰 전용 라이브러리 입니다.
 *
 * 
 *
 ******************************************************************************/

/*
##############################################
    ::: 로그인 쿠키 생성 :::          
    사용방법 : makeLogin('회원아이디', '쿠기시간', '체크값')	
##############################################
*/

function makeLogin($id, $tm, $rand) { 

	$text	= $id.$rand ;
	$id		= base64_encode($id); 
	SetCookie("my_id",	$id,		$tm, "/"); 
	SetCookie("sid",	md5($text), $tm, "/"); 

} 


/*
##############################################
    ::: 관리자 로그 :::          
    사용방법 : adminLog('회원아이디', '내용', '타입')	
##############################################
*/

function adminLog($id, $content, $type) { 
	global $mysql;

	$sql		= "SELECT * FROM mallRN_admin_log WHERE id = '{$id}' ORDER BY uid DESC LIMIT 1";
	$data		= $mysql->one_row($sql);
	if($data['content'] != $content) {
		$signdate	= time();
		$sql		= "INSERT INTO mallRN_admin_log SET id = '{$id}', content = '{$content}', type = {$type}, acc_ip = '{$_SERVER['REMOTE_ADDR']}', signdate = '{$signdate}'";
		$mysql->query2($sql);
	}

} 


/*
##############################################
    ::: 판매사 로그 :::          
    사용방법 : vendorLog('판매사아이디', '내용', '타입')	
##############################################
*/

function vendorLog($id, $content, $type) { 
	global $mysql;

	$sql		= "SELECT * FROM mallRN_vendor_log WHERE id = '{$id}' ORDER BY uid DESC LIMIT 1";
	$data		= $mysql->one_row($sql);
	if($data['content'] != $content) {
		$signdate	= time();
		$sql		= "INSERT INTO mallRN_vendor_log SET id = '{$id}', content = '{$content}', type = {$type}, acc_ip = '{$_SERVER['REMOTE_ADDR']}', signdate = '{$signdate}'";
		$mysql->query2($sql);
	}

} 



/*
##############################################
    ::: 정바구니번호 생성 :::          
    사용방법 : getCartId('회원아이디')	
##############################################
*/

function getCartId($id) {	
	$cartId = isset($_COOKIE['cartId']) ? $_COOKIE['cartId'] : '';
	if(!$cartId) {
		if($id) $cartId = base64_encode($id);
		else	$cartId = md5(uniqid(rand()));
		SetCookie("cartId", $cartId, 0, "/");
	} 

	return $cartId;
}


/*
##############################################
    ::: 분류 접근권한 체크 :::          
    사용방법 : checkCateAccess('상품분류번호', '에러 메세지 타입')	
##############################################
*/

function checkCateAccess($cate, $type = "") {	
	global $mysql, $my_level;
	
	if($my_level == 100) return;

	for($i = 3, $j = 1; $i < 10; $i = $i + 3) {
		$tmps_cate = substr($cate, 0, $i);
		if(substr($cate, ($i - 3), $i) != '000') {		    	
			$sql = "SELECT access_type, access_level FROM mallRN_cate WHERE used = 1 && cate_dep='{$j}' && SUBSTRING(cate, 1, {$i}) = '{$tmps_cate}'";			
			if(!$data = $mysql->one_row($sql)) {
				if($type == 1) {
					echo json_encode(array('error' => '해당 분류가 삭제 되었거나 존재 하지 않습니다.'));
					exit;
				}
				else Error('해당 분류가 삭제 되었거나 존재 하지 않습니다.');
			}

			if($data['access_type'] == '1' && $my_level == '0') {
				if($type == 1) {
					echo json_encode(array('error' => '해당 분류에 접속 권한이 없습니다.'));
					exit;
				}
				else Error('해당 분류에 접속 권한이 없습니다.');
			}
			if($data['access_type'] == '2') {
				$acc_level = explode(",", $data['access_level']);
				if(!in_array($my_level, $acc_level)) {
					if($type == 1) {
						echo json_encode(array('error' => '해당 분류에 접속 권한이 없습니다.'));
						exit;
					}
					else Error('해당 분류에 접속 권한이 없습니다.');
				}
			}
			$j ++;
		}
	}
}

/*
##############################################
    ::: 현재 분류 접근권한 체크 :::          
    사용방법 : checkCateAccessThis('접근권한', '접근회원등급')	
##############################################
*/

function checkCateAccessThis($access_type, $access_level) {	
	global $my_level;
	
	if($my_level == 100) return 1;

	if($access_type == '1' && $my_level == '0') return 1;
	if($access_type == '2') {
		$acc_level = explode(",", $access_level);
		if(!in_array($my_level, $acc_level)) return 1;
	}

	return;
}

/*
##############################################
    ::: 상품분류명 전체 표시 :::          
    사용방법 : getCateAllName('카테고리번호', '타입')	
##############################################
*/

function getCateAllName($cate, $link = "", $sepa = 0) {
	global $mysql, $Main;
	if(!$cate) return false;
	
	$cate_name_arr = array();
	for($i = 3, $j = 1; $i < 13; $i = $i + 3) {
		$tmps_cate = substr($cate, 0, $i);
		if(substr($cate, ($i-3), $i)!='000') {
			$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE cate_dep='{$j}' && SUBSTRING(cate,1,{$i}) = '{$tmps_cate}'";
			if($row = $mysql->one_row($sql)) $cate_name_arr[$row['cate']] = $row['cate_name'];
		}
		$j++;
	}
	
	$cate_name = array();
	foreach($cate_name_arr as $k  =>  $v) {
		if($link == 1) $cate_name[] = "<a href='?cate={$k}' class='underLine' title='{$v} 상품보기'>".$v."</a>";
		else if($link == 2) $cate_name[] = "<a href='{$Main}?channel=list&cate={$k}' title='{$v} 상품보기'>".$v."</a>";
		else $cate_name[] = $v;
	}

	if($sepa == 1)	$sepa = "";
	else				$sepa = "&nbsp;&nbsp;<i class='xi-angle-right-thin'></i>&nbsp;";
	return join($sepa, $cate_name);
}


/*
##############################################
    :::  쿠폰적용가 :::
    사용방법 : getCouponPrice('상품가', '쿠폰uid')	
##############################################
*/
function getCouponPrice($price, $uid) {	
	global $mysql;

	$GLOBALS['coupon_message1'] = "";
	$GLOBALS['coupon_message2'] = "";

	$sql = "SELECT * FROM mallRN_coupon_manager WHERE uid = '{$uid}'";
	if($data = $mysql->one_row($sql)) {
		$coupon_message			= stripslashes($data['name']);

		if($data['use_type'] == 0) {
			if($data['use_s_date'] > date("Y-m-d H:i:s")) return 0;
			if($data['use_e_date'] < date("Y-m-d H:i:s")) return 0;
		}

		if($data['use_limit'] && $data['type'] != '4') {			
			if($price < $data['use_limit']) return 0;
		}

		if($data['discount_type'] == 'P') {
			$coupon_message2	= "{$data['discount']}%";	
			$coupon_discount	= priceLimit(($price * $data['discount']) / 100);
			if($data['discount_limit'] > 0) {
				if($coupon_discount > $data['discount_limit']) {
					$coupon_discount = $data['discount_limit'];
					$coupon_message2 .= ", 최대 ".number_format($data['discount_limit'])."원";				
				}
			}
		}
		else {
			$coupon_message2	= number_format($data['discount'])."원";	
			$coupon_discount = $data['discount'];
		}
		
		$GLOBALS['coupon_message1'] = $coupon_message."({$coupon_message2})";
		$GLOBALS['coupon_message2'] = $coupon_message2;

		return $coupon_discount;
	}
	return 0;
}


/*
##############################################
    ::: 상품가격 공통사용 (할인, 쿠폰 적용):::
    사용방법 : getGoodsPrice('상품가', '상품uid', '모음전')	
##############################################
*/
function getGoodsPrice($price, $uid, $exhibition = "") {	
	global $mysql, $my_id, $my_discount, $coupon_goods_array, $coupon_uid_array, $event_info_array;
	
	$GLOBALS['SALE_PRICE']			= 0;
	$GLOBALS['EVENT_DISCOUNT']		= 0;
	$GLOBALS['COUPON_PRICE']		= 0;	
	$GLOBALS['COUPON_DISCOUNT']		= 0;
	$GLOBALS['COUPON_UID']			= 0;

	$sql = "SELECT price_ment FROM mallRN_goods WHERE uid = '{$uid}'";
	if($price_ment = $mysql->get_one($sql)) {
		return stripslashes($price_ment);
	}
	
	$c_uid							= 0;
	$orig_price						= $price;	
	
	if(isset($event_info_array)) {
		if($exhibition && $exhibition != ',') {
			foreach($event_info_array as $k => $v) {
				if(preg_match("/,{$k},/i", $exhibition)) {				
					$event_discount				= priceLimit(($orig_price * $v) / 100);	
					$GLOBALS['SALE_PRICE']		+= $event_discount;
					$GLOBALS['EVENT_DISCOUNT']	= $v;
					$price						= $price - $event_discount;				
					break;
				}
			}
		}
	}
	else return number_format($price, CONF_FLOAT_CNT);

	if($my_id) {		
		
		if($my_discount) {
			$member_discount		= priceLimit(($orig_price * $my_discount) / 100);
			$GLOBALS['SALE_PRICE']	+= $member_discount;
			$price					= $price - $member_discount;
		}

		$sql	= "SELECT c_uid FROM mallRN_coupon WHERE g_uid = '{$uid}' && status = 0 && e_date > '".date("Y-m-d")."' && id = '{$my_id}'";
		$c_uid	= $mysql->get_one($sql);

		if($c_uid) {
			$coupon_discount = getCouponPrice($price, $c_uid);
			if($coupon_discount > 0) {
				$coupon_price = $price - $coupon_discount;
				$GLOBALS['COUPON_PRICE']	= number_format($coupon_price, CONF_FLOAT_CNT);
				$GLOBALS['SALE_PRICE']		+= $coupon_discount;
				$GLOBALS['COUPON_DISCOUNT']	= $coupon_discount;
				$GLOBALS['COUPON_UID']		= $c_uid;

				return number_format($coupon_price, CONF_FLOAT_CNT);
			}
		}
	}

	$coupon_price = $price;

	if($c_uid == 0) {		
		$coupon_uid	= 0;

		if($coupon_goods_array) {
			######################## 상품 쿠폰 설정 #############################	
			$tmp_coupon_price			= 0;			
			foreach($coupon_goods_array as $k => $v) {
				$check_coupon = explode(",", $v);
				if(in_array($uid, $check_coupon)) {
					$coupon_discount = getCouponPrice($price, $coupon_uid_array[$k]);
					if($coupon_discount > 0) {
						$coupon_price = $price - $coupon_discount;

						if($tmp_coupon_price > $coupon_price || $tmp_coupon_price == 0) {
							$tmp_coupon_price = $coupon_price;					
							$coupon_uid		  = $coupon_uid_array[$k];
							$GLOBALS['COUPON_PRICE']	= number_format($coupon_price, CONF_FLOAT_CNT);
							$GLOBALS['SALE_PRICE']		+= $coupon_discount;
							$GLOBALS['COUPON_DISCOUNT']	= $coupon_discount;
							$GLOBALS['COUPON_UID']		= $coupon_uid_array[$k];
						}
					}
				}
			}	
			unset($tmp_coupon_price, $check_coupon);	
			######################## 상품 쿠폰 설정 #############################
		}

		if($my_id && $coupon_uid > 0) {
			couponIssuance($coupon_uid, $my_id, $uid);
		}
	}

	return number_format($coupon_price, CONF_FLOAT_CNT);
	
};


/*
##############################################
    ::: 상품옵션가격 공통사용 (할인, 쿠폰 적용):::
    사용방법 : getGoodsOptionPrice('옵션가상품가', '상품uid')	
##############################################
*/
function getGoodsOptionPrice($price, $uid, $exhibition = "") {
	global $mysql, $my_id, $my_discount, $coupon_goods_array, $coupon_uid_array, $event_info_array;

	$sql			= "SELECT price FROM mallRN_goods WHERE uid = '{$uid}'";
	$goods_price	= $mysql->get_one($sql);

	######################## 상품 쿠폰 설정 #############################
	$GLOBALS['SALE_PRICE']			= 0;
	$GLOBALS['COUPON_PRICE']		= 0;
	$GLOBALS['COUPON_DISCOUNT']		= 0;
	$GLOBALS['COUPON_UID']			= 0;
	
	$orig_price						= $goods_price;
	
	if($exhibition && $exhibition != ',') {		
		foreach($event_info_array as $k => $v) {
			if(preg_match("/,{$k},/i", $exhibition)) {				
				$event_discount			= priceLimit(($orig_price * $v) / 100);	
				$GLOBALS['SALE_PRICE']	+= $event_discount;
				$goods_price			= $goods_price - $event_discount;				
				break;
			}
		}
	}
	
	if($my_id) {

		if($my_discount) {
			$member_discount		= priceLimit(($orig_price * $my_discount) / 100);
			$GLOBALS['SALE_PRICE']	+= $member_discount;
			$goods_price			= $goods_price - $member_discount;
		}		

		$sql	= "SELECT c_uid FROM mallRN_coupon WHERE g_uid = '{$uid}' && status = 0 && e_date > '".date("Y-m-d")."' && id = '{$my_id}'";
		$c_uid	= $mysql->get_one($sql);

		if($c_uid) {
			$coupon_discount = getCouponPrice($goods_price + $price, $c_uid);
			if($coupon_discount > 0) {
				$coupon_price = $goods_price + $price - $coupon_discount;
				$GLOBALS['COUPON_PRICE']	= $coupon_price;
				$GLOBALS['SALE_PRICE']		+= $coupon_discount;
				$GLOBALS['COUPON_DISCOUNT']	= $coupon_discount;
				$GLOBALS['COUPON_UID']		= $c_uid;
				return $coupon_price;
			}
		}		
	}

	$tmp_coupon_price = 0;		
	if($coupon_goods_array) {
		$coupon_uid = 0;
		foreach($coupon_goods_array as $k => $v) {
			$check_coupon = explode(",", $v);
			if(in_array($uid, $check_coupon)) {					
				$coupon_discount = getCouponPrice($goods_price + $price, $coupon_uid_array[$k]);
				if($coupon_discount > 0) {
					$coupon_price = $goods_price + $price - $coupon_discount;
					if($tmp_coupon_price > $coupon_price || $tmp_coupon_price == 0) {
						$tmp_coupon_price			= $coupon_price;
						$coupon_uid					= $coupon_uid_array[$k];
						$GLOBALS['SALE_PRICE']		+= $coupon_discount;
						$GLOBALS['COUPON_PRICE']	= $coupon_price;
						$GLOBALS['COUPON_DISCOUNT']	= $coupon_discount;
						$GLOBALS['COUPON_UID']		= $coupon_uid_array[$k];
					}
				}
			}
		}	
		unset($coupon_price, $check_coupon);	

		if($my_id && $coupon_uid > 0) {
			couponIssuance($coupon_uid, $my_id, $uid);
		}
		
		if($tmp_coupon_price) return $tmp_coupon_price;
		else return $goods_price + $price;
	}		
	######################## 상품 쿠폰 설정 #############################	

	return $goods_price + $price;	
	
};


/*
##############################################
    ::: 상품아이콘 생성 :::          
    사용방법 : getIcon('상품아이콘')	
##############################################
*/

function getIcon($icon) {
	global $t;

	if(!$icon) return false;
	
	$icon = explode("|",$icon);
	$return = array();
	for($i=0, $cnt=count($icon); $i<$cnt; $i++) {
		$return[] = "<img src='".ICON_FOLDER."/{$icon[$i]}?t={$t}' alt='icon' />";
	}

	return join(" ",$return);
}


/*
##############################################
    ::: 구매수량 확인 :::          
    사용방법 : getOrderQty('상품고유번호')	
##############################################
*/

function getOrderQty($uid) {
	global $mysql, $cart_id, $my_id;

	if(!$my_id) return 0;

	$sql			= "SELECT SUM(b.qty) FROM mallRN_order_info a, mallRN_order_goods b WHERE a.order_num = b.order_num && a.id = '{$my_id}' && b.g_uid = '{$uid}' && !(b.status > 7 && b.status2 = 5)";
	$prev_qty1		= $mysql->get_one($sql);
	$sql			= "SELECT SUM(qty) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && g_uid = '{$uid}'";
	$prev_qty2		= $mysql->get_one($sql);

	return $prev_qty1 + $prev_qty2;
}


/*
##############################################
    ::: 상품상세설명이미지태그자동생성 :::          
    사용방법 : detailImageToTag('상품uid', '이미지간공백유무', '상세이미지들')	
##############################################
*/
function detailImageToTag($uid, $detail_image_type, $detail_image, $moddate = "") {
	if(!$detail_image) return;
	if($detail_image_type==1) $gap_tag = "<div style='height:20px; overflow:hidden;'>&nbsp;</div>";
	else $gap_tag = "";

	$img_block		= floor($uid/10000);
	$temp_upload	= $img_block.'/'.$uid;		
	$detail_upload	= CONF_ROOT.'image/goods/upload/'.$temp_upload;
	
	$adds			= "";
	if($moddate) $adds = "?t={$moddate}";
	
	$tags = array();
	$detail_image = explode(",",$detail_image);
	for($i=0,$cnt=count($detail_image); $i<$cnt; $i++) {
		if(!$detail_image[$i]) continue;
		$i2 = $i + 1;
		$image	= $detail_upload.'/'.$detail_image[$i];
		$size	= getImageSize($image);
		$tags[]	= "<img src='{$image}{$adds}' alt='상세이미지 #{$i2}' width='{$size[0]}' />".$gap_tag;		
	}

	return join("\r\n", $tags);
}

function detailImageToTag2($uid, $detail_image_type, $detail_image, $folder = "exhibition") {
	if(!$detail_image) return;
	if($detail_image_type==1) $gap_tag = "<div style='height:20px; overflow:hidden;'>&nbsp;</div>";
	else $gap_tag = "";

	$detail_upload	= "image/{$folder}/{$uid}";
	
	$tags = array();
	$detail_image = explode(",",$detail_image);
	for($i=0,$cnt=count($detail_image); $i<$cnt; $i++) {
		if(!$detail_image[$i]) continue;
		$i2 = $i + 1;
		$image	= $detail_upload.'/'.$detail_image[$i];
		$tags[]	= "<img src='{$image}' alt='상세이미지 #{$i2}' />".$gap_tag;		
	}

	return join("\r\n", $tags);
}


/*
##############################################
    ::: 변경 범위에 따른 상품조건 where 정의 :::          
    사용방법 : goodsTypeWhere('타입(1:선택, 2:검색결과, 3:전체)', '선택된 상품uid, 상품uid, 상품uid.. ')
##############################################
*/
function goodsTypeWhere($type, $items) {
	
	$search_variable	= array('field' => 0, 'keyword' => 0, 'cate' => 1, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'display_use' => 1, 'sale_use' => 1, 'option_use' => 1, 'milage_type' => 1, 'delivery_type' => 1, 'engine_use' => 1, 'order_priority' => 1, 'commission_type' => 1, 'qty_type' => 1, 'vendor' => 1, 'limit_qty' => 1, 'cate_hide' => 1, 'soldout' => 1, 'option_soldout' => 1, 'commission_type' => 1, 'information_use' => 1);
	$multi_array		= array("name", "uid", "model", "keyword", "goods_code");

	$where = "";
	switch($type) {
		case '1' :
			if(!$items) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			$where = " && a.uid IN ({$items})";
		break;
		
		case '2' :				
			foreach ($search_variable as $k  =>  $v) {
				if(preg_match("/keyword/i",$k)) $value = isset($_GET[$k]) ?  urldecode($_GET[$k]) : '';
				else $value = isset($_GET[$k]) ? $_GET[$k] : '';
				
				if($v == 1 && strlen($value) > 0) {
					if($k=='cate') {
						for($i=3; $i<10; $i=$i+3) {
							if(substr($value, $i, ($i+3))=='000') break;						
						}
						
						$GLOBALS['swhere'] = "SUBSTRING({$k}, 1, {$i}) = '".substr($value, 0, $i)."' ";
					}
					else if($k=='limit_qty') $where	.= "&& a.{$k} > '0' ";
					else if($k=='vendor') {
						if($value == 'X') $where	.= "&& a.{$k} = '' ";
						else $where	.= "&& a.{$k} = '{$value}' ";
					}
					else if($k=='soldout') $where	.= "&& ((a.qty_type = 0 && a.qty = 0 && a.option_use = 0) || (a.option_soldout = 2 && a.option_use = 1))";
					else $where	.= "&& a.{$k} = '{$value}' ";
				}
				else if($v == 2 && strlen($value) > 0) {
					if($k == 'e_date') {
						$date_type	= checkGetVar('date_type');
						$s_date		= checkGetVar('s_date');

						if(!$s_date) $where .= "&& from_unixtime(a.{$date_type}) < '{$value} 23:59:59' ";
						else $where .= "&& from_unixtime(a.{$date_type}) BETWEEN '{$s_date}' AND '{$value} 23:59:59' ";
					}
				}
			}
			
			for($i=1; $i<5; $i++) {
				if($i==1) $i2 = '';
				else $i2 = $i;
				
				if(isset($_GET['field'.$i2]) && isset($_GET['keyword'.$i2])) {
					if($_GET['field'.$i2]=='multi') {

						$where2 = array();
						foreach($multi_array as $k  =>  $v) {
							$where2[] = "INSTR(a.{$v}, '".$_GET['keyword'.$i2]."')";
						}
						$where	.= "&& (".JOIN(" || ",$where2).") ";
					}
					else $where	.= "&& INSTR(a.".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
				}
			}
		break;		
	}

	return $where;
}

function memberTypeWhere($type, $items) {
	
	$search_variable	= array('field' => 0, 'keyword' => 0, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'range1' => 2, 'range2' => 2, 'range3' => 2, 'level' => 1, 'mailling' => 1, 'sms' => 1, 'auth' => 1, 'gender' => 1, 'marry' => 1, 'address1' => 1, 'mobile' => 1, 'sns_type' => 1);
	$multi_array		= array("name", "id", "email", "tel", "cell", "comp", "comp_num", "comp_owner", "reference");

	$where = "";
	switch($type) {
		case '1' :
			if(!$items) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			$where = " && a.uid IN ({$items})";
		break;
		
		case '2' :				
			foreach ($search_variable as $k  =>  $v) {
				if(preg_match("/keyword/i",$k)) $value = isset($_GET[$k]) ?  urldecode($_GET[$k]) : '';
				else $value = isset($_GET[$k]) ? $_GET[$k] : '';
				
				if($v==1 && strlen($value)>0) {
					if($k=='cate') {
						for($i=3; $i<10; $i=$i+3) {
							if(substr($value, $i, ($i+3))=='000') break;						
						}
						
						$where	.= "&& SUBSTRING(b.{$k}, 1, {$i}) = '".substr($value, 0, $i)."' ";
					}
					else $where	.= "&& a.{$k} = '{$value}' ";
				}
				else if($v == 2 && strlen($value) > 0) {
					if($k == 'e_date') {
						$date_type	= checkGetVar('date_type');
						$s_date		= checkGetVar('s_date');

						if(!$s_date) $where .= "&& from_unixtime(a.{$date_type}) < '{$value} 23:59:59' ";
						else $where .= "&& from_unixtime(a.{$date_type}) BETWEEN '{$s_date}' AND '{$value} 23:59:59' ";
					}
					else if($k == 'range1') {
						$s_range1	= checkGetVar('s_range1');
						$e_range1	= checkGetVar('e_range1');		

						if(!$s_range1 && !$e_range1) continue;
						if(!$s_range1) $where .= "&& a.{$value} < {$e_range1} ";
						else if(!$e_range1) $where .=  "&& a.{$value} > {$s_range1} ";
						else $where .= "&& a.{$value} BETWEEN '{$s_range1}' AND '{$e_range1}' ";
					}
					else if($k == 'range2') {
						$s_range2	= checkGetVar('s_range2');
						$e_range2	= checkGetVar('e_range2');		

						if(!$s_range2 && !$e_range2) continue;
						if(!$s_range2) $where .= "&& a.{$value} < {$e_range2} ";
						else if(!$e_range2) $where .=  "&& a.{$value} > {$s_range2} ";
						else $where .= "&& a.{$value} BETWEEN '{$s_range2}' AND '{$e_range2}' ";
					}
					else if($k == 'range3') {
						$s_range3	= checkGetVar('s_range3');
						$e_range3	= checkGetVar('e_range3');		

						if(!$s_range3 && !$e_range3) continue;
						if(!$s_range3) $where .= "&& a.{$value} < {$e_range3} ";
						else if(!$e_range3) $where .=  "&& a.{$value} > {$s_range3} ";
						else $where .= "&& a.{$value} BETWEEN '{$s_range3}' AND '{$e_range3}' ";
					}
				}
			}
			
			for($i=1; $i<5; $i++) {
				if($i==1) $i2 = '';
				else $i2 = $i;
				
				if(isset($_GET['field'.$i2]) && isset($_GET['keyword'.$i2])) {
					if($_GET['field'.$i2]=='multi') {

						$where2 = array();
						foreach($multi_array as $k  =>  $v) {
							$where2[] = "INSTR(a.{$v}, '".$_GET['keyword'.$i2]."')";
						}
						$where	.= "&& (".JOIN(" || ",$where2).") ";
					}
					else $where	.= "&& INSTR(a.".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
				}
			}
		break;		
	}

	return $where;
}

function orderTypeWhere($type, $items) {
	global $swhere;
	
	$search_variable	= array('field' => 0, 'keyword' => 0, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'range1' => 2, 'member' => 2, 'mobile' => 1, 'pay_type' => 1, 'pay_status' => 1, 'cash_receipts' => 2, 'new' => 1, 'use_mileage' => 2, 'use_coupon' => 2);
	$multi_array	= array('order_num', 'id', 'name', 'cell', 'email', 'name2', 'cell2', 'bank_info');
	$multi2_array	= array("delivery_info", "g_name", "g_uid", "g_code", "vendor");

	$where = "";
	switch($type) {
		case '1' :
			if(!$items) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			$where = " && a.uid IN ({$items})";
		break;
		
		case '2' :				
			foreach ($search_variable as $k  =>  $v) {
				if(preg_match("/keyword/i",$k)) $value = isset($_GET[$k]) ?  urldecode($_GET[$k]) : '';
				else $value = isset($_GET[$k]) ? $_GET[$k] : '';
				
				if($v == 1 && strlen($value) > 0) {
					$where	.= "&& a.{$k} = '{$value}' ";
				}
				else if($v == 2 && strlen($value) > 0) {
					if($k == 'e_date') {
						$date_type	= checkGetVar('date_type');
						$s_date		= checkGetVar('s_date');

						if(!$s_date) $where .= "&& from_unixtime(a.{$date_type}) < '{$value} 23:59:59' ";
						else $where .= "&& from_unixtime(a.{$date_type}) BETWEEN '{$s_date}' AND '{$value} 23:59:59' ";
					}
					else if($k == 'range1') {
						$s_range1	= checkGetVar('s_range1');
						$e_range1	= checkGetVar('e_range1');		

						if(!$s_range1 && !$e_range1) continue;
						if(!$s_range1) $where .= "&& a.{$value} < {$e_range1} ";
						else if(!$e_range1) $where .=  "&& a.{$value} > {$s_range1} ";
						else $where .= "&& a.{$value} BETWEEN '{$s_range1}' AND '{$e_range1}' ";
					}
					else if($k == 'member') {
						if($vls == 1) $where .= "&& a.id != '' ";	
						else if($vls == 2) $where .= "&& a.id = '' ";	
					}
					else if($k == 'cash_receipts') {
						if($vls == 1) $where .= "&& a.cash_receipts != '' ";	
						else if($vls == 2) $where .= "&& a.cash_receipts = '' ";	
					}
					else if($k == 'use_mileage') {
						if($vls == 1) $where .= "&& a.use_mileage > 0 ";	
						else if($vls == 2) $where .= "&& a.use_mileage = 0 ";	
					}
					else if($k == 'use_coupon') {
						if($vls == 1) $where .= "&& a.use_coupon > 0 ";	
						else if($vls == 2) $where .= "&& a.use_coupon = 0 ";	
					}
				}
			}
			
			for($i=1; $i<5; $i++) {
				if($i==1) $i2 = '';
				else $i2 = $i;
				
				if(isset($_GET['field'.$i2]) && isset($_GET['keyword'.$i2])) {
					if($_GET['field'.$i2] == 'multi2') {
						$where1 = array();
						foreach($multi_array as $k => $v) {
							if($v == 'name') $where1[] = "INSTR(REPLACE(a.{$v}, ' ', ''), '".str_replace(' ' , '', $_GET['keyword'.$i2])."')";
							else if($v == 'id') $where1[] = "a.{$v} = '".add_escape_re_string($_GET['keyword'.$i2])."'";
							else if($v == 'keyword') $where1[] = "INSTR(a.{$v}, ',".$_GET['keyword'.$i2].",')";
							else $where1[] = "INSTR(a.{$v}, '".$_GET['keyword'.$i2]."')";
						}
						
						$where2 = array();
						foreach($multi2_array as $k => $v) {
							if($v == 'vendor') $where2[] = "{$v} = '".add_escape_re_string($_GET['keyword'.$i2])."'";
							else $where2[] = "INSTR({$v}, '".$_GET['keyword'.$i2]."')";
						}

						$where1[] = "a.order_num IN ( SELECT order_num FROM mallRN_order_goods WHERE ".JOIN(" || ",$where2)." )";
						$where.= "&& (".JOIN(" || ",$where1).") ";	
						unset($where1, $where2);
					}
					else if($_GET['field'.$i2] == 'g_name' || $_GET['field'.$i2] == 'g_uid' || $_GET['field'.$i2] == 'g_code') {
						if($swhere) $swhere = $swhere." && INSTR(".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
						else		$swhere = "&& INSTR(".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
					}
					else if($_GET['field'.$i2] == 'g_vendor') {
						if($swhere) $swhere = $swhere." && INSTR(vendor, '".$_GET['keyword'.$i2]."') ";
						else		$swhere = "&& INSTR(vendor, '".$_GET['keyword'.$i2]."') ";
					}
					else $where	.= "&& INSTR(a.".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
				}
			}
		break;		
	}

	return $where;
}


function orderTypeWhere2($type, $items) {
	global $swhere;
	
	$search_variable	= array('field' => 0, 'keyword' => 0, 'e_date' => 2, 'field2' => 0, 'keyword2' => 0, 'field3' => 0, 'keyword3' => 0, 'field4' => 0, 'keyword4' => 0, 'pay_type' => 2, 'pay_status' => 2, 'mobile' => 2, 'status' => 1, 'vendor' => 1);		
	$multi_array		= array('id', 'name', 'cell', 'email', 'name2', 'cell2', 'bank_info');
	$multi2_array		= array('order_num', 'id', 'name', 'cell', 'email', 'name2', 'cell2', 'bank_info');
	$multi3_array		= array("delivery_info", "g_name", "g_uid", "g_code");

	$where = "";
	switch($type) {
		case '1' :
			if(!$items) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			$where = " && c.uid IN ({$items})";
		break;
		
		case '2' :				
			foreach ($search_variable as $k  =>  $v) {
				if(preg_match("/keyword/i",$k)) $value = isset($_GET[$k]) ?  urldecode($_GET[$k]) : '';
				else $value = isset($_GET[$k]) ? $_GET[$k] : '';
				
				if($v == 1 && strlen($value) > 0) {
					$where	.= "&& c.{$k} = '{$value}' ";
				}
				else if($v == 2 && strlen($value) > 0) {
					if($k == 'e_date') {
						$date_type	= checkGetVar('date_type');
						$s_date		= checkGetVar('s_date');

						if(!$s_date) $where .= "&& from_unixtime(c.{$date_type}) < '{$value} 23:59:59' ";
						else $where .= "&& from_unixtime(c.{$date_type}) BETWEEN '{$s_date}' AND '{$value} 23:59:59' ";
					}
					else if($k == 'pay_type') {
						$swhere		.= "&& pay_type = '{$value}'";
					}
					else if($k == 'pay_staus') {
						$swhere		.= "&& pay_staus = '{$value}'";
					}
					else if($k == 'mobile') {
						$swhere		.= "&& mobile = '{$value}'";
					}					
				}
			}
			
			for($i=1; $i<5; $i++) {
				if($i==1) $i2 = '';
				else $i2 = $i;
				
				if(isset($_GET['field'.$i2]) && isset($_GET['keyword'.$i2])) {
					if($_GET['field'.$i2] == 'multi3') {
						$where1 = array();
						foreach($multi2_array as $k => $v) {
							if($v == 'name') $where1[] = "INSTR(REPLACE({$v}, ' ', ''), '".str_replace(' ' , '', $_GET['keyword'.$i2])."')";
							else if($v == 'id') $where1[] = "{$v} = '".add_escape_re_string($_GET['keyword'.$i2])."'";
							else if($v == 'keyword') $where1[] = "INSTR({$v}, ',".$_GET['keyword'.$i2].",')";
							else $where1[] = "INSTR({$v}, '".$_GET['keyword'.$i2]."')";
						}

						$where2 = array();

						$where2[] = "c.order_num IN ( SELECT order_num FROM mallRN_order_info WHERE ".JOIN(" || ",$where1)." )";
												
						foreach($multi3_array as $k => $v) {
							if($v == 'vendor') $where2[] = "c.{$v} = '".add_escape_re_string($_GET['keyword'.$i2])."'";
							else $where2[] = "INSTR(c.{$v}, '".$_GET['keyword'.$i2]."')";
						}
						
						$where.= "&& (".JOIN(" || ",$where2).") ";	
						unset($where1, $where2);
					}
					else if(in_array($_GET['field'.$i2], $multi_array)) {
						$swhere .= "&& INSTR(".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
					}
					else $where	.= "&& INSTR(c.".$_GET['field'.$i2].", '".$_GET['keyword'.$i2]."') ";
				}
			}
		break;		
	}

	return $where;
}

/*
##############################################
	 ::: 모음전 상품 해제 :::          
    사용방법 : exhibitionGoodsDel('상품uid', '모음전uid', $where)
##############################################
*/
function exhibitionGoodsDel($uid, $exhibition, $where) {
	global $mysql;

	$sql = "DELETE FROM mallRN_exhibition_goods WHERE euid = '{$exhibition}' && guid = '{$uid}' {$where}";
	$mysql->query3($sql);

	$sql = "SELECT exhibition FROM mallRN_goods WHERE uid = '{$uid}'";
	$goods_exhibition = $mysql->get_one($sql);
	
	$goods_exhibition = str_replace($exhibition, "", $goods_exhibition);
	$goods_exhibition = str_replace(",,", ",", $goods_exhibition);
	if($goods_exhibition == ', ') $goods_exhibition = '';		
	
	$sql = "UPDATE mallRN_goods SET exhibition = '{$goods_exhibition}' WHERE uid = '{$uid}'";
	$mysql->query3($sql);

}


/*
##############################################
    ::: 금액 절사 조건 처리 :::          
    사용방법 : priceLimit('처리 자리수(0:사용안함, 1, 10, 100)', '타입(1:버림, 2:반올림, 3:올림)', '가격')
##############################################
*/
function priceLimit($price) {
	global $goods_price_limit1, $goods_price_limit2, $mysql;

	if(!@$goods_price_limit2) {
		$sql = "SELECT goods_price_limit1, goods_price_limit2 FROM mallRN_configuration WHERE uid = 1";
		$data = $mysql->one_row($sql);

		$goods_price_limit1 = $data['goods_price_limit1'];
		$goods_price_limit2 = $data['goods_price_limit2'];
	}

	$type1 = $goods_price_limit1;
	$type2 = $goods_price_limit2;
	
	if(!$type1 || !$type2) return $price;
	
	$rtn = 0;		
	
	if($type2==1) {
		$rtn = floor($price / (10 * $type1));
		$rtn = $rtn * (10 * $type1);
	}
	else if($type2==2) {
		$rtn = round($price / (10 * $type1));
		$rtn = $rtn * (10 * $type1);
	}
	else if($type2==3) {
		$rtn = ceil($price / (10 * $type1));
		$rtn = $rtn * (10 * $type1);
	}

	return $rtn;
}

/*
##############################################
    ::: 배너 출력 :::          
    사용방법 : commonBannerCheck('출력위치')
##############################################
*/
function commonBannerCheck($channel) {
	global $tpl, $mysql, $mobile_header, $BANNER_DEFINE;

	if(!isset($BANNER_DEFINE)) return;
	if(!$BANNER_DEFINE) return;

	$today = date("Y-m-d");

	foreach($BANNER_DEFINE as $k => $v) {
		if($v[3] == $channel) {
			
			if($v[4] > 0) $limit = "LIMIT {$v[4]}";
			else $limit = "";
			
			$sql = "SELECT * FROM mallRN_{$mobile_header}banner WHERE code = '{$k}' && status = '0' ORDER BY sequence ASC {$limit}";
			$mysql->query($sql);
			
			$ck_cnt = 0;
			while($row = $mysql->fetch_array()) {

				if($row['s_date'] != '1000-01-01 00:00:00' && $row['s_date'] > $today) continue;
				if(substr($row['e_date'], 0, 10) != '1000-01-01' && $row['e_date'] < $today) continue;

				$GLOBALS['B_NAME'] = stripslashes($row['name']);
				$GLOBALS['B_LINK'] = returnCheckLink($row['link1']);
				if($row['target'] == '1') $GLOBALS['B_TARGET'] = "target='_blank'";
				else $GLOBALS['B_TARGET'] = "";
				$b_width = explode("px", $v[1]);
				
				$GLOBALS['B_IMAGE_URL']	= BANNER_FOLDER .$row['uid'].'/'.$row['image1'];
				$GLOBALS['B_IMAGE'] = imgSizeCh(BANNER_FOLDER .$row['uid'].'/', $row['image1'], '', '', $b_width[0], $GLOBALS['B_NAME'], $row['moddate']);

				$tpl->parse("loop_banner_{$k}");
				$ck_cnt ++;
			}

			if($ck_cnt > 0) @$tpl->parse("is_banner_{$k}");

		}
	}
}

/*
##############################################
    ::: 상품 정보 공통 출력 :::          
    사용방법 : getGoodsInfo('상품필드정보')
##############################################
*/
function getGoodsInfo($row, $tname = ''){
	global $Main, $my_id, $tpl, $mysql, $coupon_goods_array, $coupon_uid_array, $my_discount;
	
	if(!$row) return;	

	$return = Array();
	
	$return['name']				= stripslashes($row['name']);
	$return['name_code_able']	= stripslashes($row['name_code_able']);
	$return['detail']			= stripslashes($row['detail']);
	$return['link']				= "{$Main}?channel=view&uid={$row['uid']}&cate={$row['cate']}";	
	if(isset($row['image1']))	$return['image1'] = DEFAULT_PATH."image/goods/img{$row['image1']}?t={$row['moddate']}";
	else						$return['image1'] = DEFAULT_PATH."image/no_image.png";
	$return['image']			= DEFAULT_PATH."image/goods/img{$row['image2']}?t={$row['moddate']}";
	if(isset($row['image3']))	$return['image3'] = DEFAULT_PATH."image/goods/img{$row['image3']}?t={$row['moddate']}";
	else						$return['image3'] = DEFAULT_PATH."image/no_image.png";

	$return['make']				= stripslashes($row['make']);
	$return['view_cnt']			= number_format($row['view_cnt']);
	$return['order_cnt']		= number_format($row['order_cnt']);
	if($row['consumer_price'] && $row['consumer_price'] > 0){
		$return['consumer_price'] = number_format($row['consumer_price'], CONF_FLOAT_CNT);
	}
	else $return['consumer_price'] = '';
	
	$return['coupon_price']	= 0;

	if($row['price_ment']) {
		$default_price				= 0;
		$return['price']			= stripslashes($row['price_ment']);
		$GLOBALS['EVENT_DISCOUNT']	= 0;
	}
	else {
		$default_price			= $row['price'];
		$row['price']			= str_replace(",", "", getGoodsPrice($row['price'], $row['uid'], $row['exhibition']));
		$return['price']		= number_format($row['price'], CONF_FLOAT_CNT);

		if($GLOBALS['COUPON_DISCOUNT']) {
			$return['coupon_uid']		= $GLOBALS['COUPON_UID'];
			$return['coupon_price']		= $GLOBALS['COUPON_DISCOUNT'];
			$return['coupon_msg']		= $GLOBALS['coupon_message2'];
		}

	}
	
	$return['icon'] = "";
	if($row['icon']){
		$icon_arr	= explode("|", $row['icon']);
		$icon		= array();
		foreach($icon_arr as $k => $v) {
		   $icon[] = "<img src='".DEFAULT_PATH."image/icon/{$v}' alt='icon' />";
		}
		if(count($icon) > 0) $return['icon'] = join(" ", $icon);
	}
	
	if(!$tname) return $return;
	else {
		foreach($return as $k => $v) {
			 $GLOBALS[strtoupper($k)] = $v;
		}
		
		if($tname == 'goods_item' || $tname == 'related_goods' || $tname == 'list' || $tname == 'goods')	$ck_num = "";
		else																								$ck_num = "2";
		$sold_out			= 0;
		$orig_price			= 0;
		$GLOBALS['SALE']	= 0;

		if($row['sale_use'] == 0) $sold_out = 1;
		else if($row['option_use'] == 1) {
			if($row['option_soldout'] == 2) $sold_out = 1;
		}
		else if($row['qty_type'] == 0 && $row['qty'] < 1) $sold_out = 1;

		if($sold_out == 1) $tpl->parse("is_soldout{$ck_num}");

		if($return['coupon_price'] > 0) {
			$orig_price = 1;
			$tpl->parse("is_coupon{$ck_num}");
		}

		if($GLOBALS['EVENT_DISCOUNT'] > 0) {		
			$GLOBALS['SALE']	= $GLOBALS['EVENT_DISCOUNT'];		
		}

		if($my_discount > 0) {
			$GLOBALS['SALE']	+= $my_discount;
		}

		if($GLOBALS['SALE'] > 0) {
			$orig_price			= 1;
			$tpl->parse("is_sale{$ck_num}");
		}

		if($orig_price == 1) {
			$GLOBALS['ORIG_PRICE']	= number_format($default_price, CONF_FLOAT_CNT);
			$tpl->parse("is_orig_price{$ck_num}");
		}

		$tpl->parse("loop_{$tname}");
	}
}


/*
##############################################
    ::: 장바구니 상품 정보 공통 출력 :::          
    사용방법 : getCartGoodsInfo('장바구니필드정보')
##############################################
*/
function getCartGoodsInfo($row){
	global $Main, $my_id, $my_level, $my_mileage, $tpl, $mysql, $shop_config, $vshop_config, $cart_id, $event_info_array;
	
	if(!$row) return;	
	
	$return = Array();

	$sql	= "SELECT * FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
	$data	= $mysql->one_row($sql);
	
	$return['uid']				= $data['uid'];
	$return['cate']				= $data['cate'];
	$return['name']				= stripslashes($data['name']);
	$return['g_code']			= stripslashes($data['goods_code']);
	$return['link']				= "{$Main}?channel=view&uid={$data['uid']}&cate={$data['cate']}";	
	
	if($data['image3']) $return['image']	= DEFAULT_PATH."image/goods/img{$data['image3']}?t={$data['moddate']}";
	else 				$return['image']	= DEFAULT_PATH."image/no_image.png";

	$return['delivery_price']	= 0;
	$return['commission_type']	= $data['commission_type'];
	$return['commission']		= $data['commission'];
	$return['orig_price']		= $data['orig_price'];
	$return['limit_qty']		= $data['limit_qty'];

	if($data['limit_qty']) {
		if(!$my_id) {
			$sql	= "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
			$mysql->query($sql);
			alert("회원전용상품이 있어 장바구니에서 삭제 되었습니다.", "{$Main}?channel=cart");
		}

		$able_qty	= $data['limit_qty'] - getOrderQty($row['g_uid']) + $row['qty'];
		if($row['qty'] > $able_qty) {
			$sql	= "UPDATE mallRN_cart SET qty = '{$able_qty}' WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
			$mysql->query3($sql);
			$row['qty']	= $able_qty;
		}
	}

	if($row['qty'] < 1) {
		$sql	= "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
		$mysql->query($sql);
		alert("주문수량이 없는 상품이 있어 장바구니에서 삭제 되었습니다.", "{$Main}?channel=cart");
	}

	$return['qty']			= $row['qty'];
	$return['delivery_im_areas1_used']	= $data['delivery_im_areas1_used'];
	$return['delivery_im_areas1_price']	= $data['delivery_im_areas1_price'];
	$return['delivery_im_areas2_used']	= $data['delivery_im_areas2_used'];
	$return['delivery_im_areas2_price']	= $data['delivery_im_areas2_price'];
	$return['delivery_type_qty']		= $data['delivery_type_qty'];
	
	$ADD_PRICE_MSG = "";
	switch($data['delivery_type']) {
		case "1" : 
			if($row['vendor_delivery']) {
				if($vshop_config['delivery_type'] == 'F') $return['delivery']	= "무료배송";
				else if($vshop_config['delivery_type']=='D') $return['delivery'] = "착불 (예상금액 : ".number_format($vshop_config['delivery_d_price'])."원)";
				else $return['delivery']										= "조건부 무료";
			}
			else {
				if($shop_config['delivery_type'] == 'F') $return['delivery']	= "무료배송";
				else if($shop_config['delivery_type']=='D') $return['delivery'] = "착불 (예상금액 : ".number_format($shop_config['delivery_d_price'])."원)";
				else $return['delivery']										= "조건부 무료";
			}
		break;
		case "2" :
			$return['delivery']				= "무료배송";
		break;
		case "3" : 
			$return['delivery']				= "착불";
		break;
		case "4" :
			$return['delivery']				= number_format($data['delivery_price'])."원";
			$return['delivery_price']		= $data['delivery_price'];
		break;
		case "5" :
			$ADD_PRICE_MSG					= "(".number_format($data['delivery_type_qty'])."개당)";
			$return['delivery']				= number_format($data['delivery_price'])."원{$ADD_PRICE_MSG}";
			$return['delivery_price']		= $data['delivery_price'];			
		break;
	}
	
	$return['delivery_add'] = "";
	if($data['delivery_type'] != 1) {			
		if($data['delivery_im_areas1_used'] == 1) $return['delivery_add'] .= "제주 추가 ".number_format($data['delivery_im_areas1_price'])."원{$ADD_PRICE_MSG}";
		if($data['delivery_im_areas2_used'] == 1) {
			if($return['delivery_add']) $return['delivery_add'] .= ", ";			
			$return['delivery_add'] .= "제주 외 도서지역 추가 ".number_format($data['delivery_im_areas2_price'])."원{$ADD_PRICE_MSG}";
		}
	}

	$return['delivery_type']	= $data['delivery_type'];	
	
	$change_qty			= 0;
	$return['disabled'] = 0;

	if($row['option']) {
		$sql	= "SELECT * FROM mallRN_goods_option WHERE uid = '{$row['option']}' && guid = '{$row['g_uid']}'";
		$data2	= $mysql->one_row($sql);

		if($data2['price'] > 0)	{
			$add_price = " (+".number_format($data2['price'], CONF_FLOAT_CNT)."원)";			
		}
		else if($data2['price'] < 0) {
			$add_price = " (".number_format($data2['price'], CONF_FLOAT_CNT)."원)";
		}
		else $add_price = "";

		$return['option_price'] = $data2['price'];

		$option_info	= explode("|*|", $data['option_info']);		
		$value_info		= explode("|", $data2['value']);
		
		$option_array	= array();
		foreach($option_info as $k => $v) {
			$option_info2	= explode("|", $v);
			$option_array[] = $option_info2[0]." : ".$value_info[$k];
		}		
		
		$return['option']	= join(" / ", $option_array).$add_price;		
		$return['price']	= number_format(getGoodsOptionPrice($data2['price'], $data['uid'], $data['exhibition']), CONF_FLOAT_CNT);
		
		if($data2['qty_type'] == 0 && $data2['qty'] < $row['qty']) {
			if($data2['qty'] > 0) $change_qty = $data2['qty'];
			else $return['disabled'] = 1;
		}
	}
	else {
		$return['option']		= "";	
		$return['option_price'] = 0;
		$return['price']		= getGoodsPrice($data['price'], $data['uid'], $data['exhibition']);
		$return['qty_type']		= $data['qty_type'];

		if($data['qty_type'] == 0 && $data['qty'] < $row['qty']) {
			if($data['qty'] > 0) $change_qty = $data['qty'];
			else $return['disabled'] = 1;
		}		
	}
	if($data['sale_use'] == 0 || $data['price_ment']) $return['disabled'] = 1;
	
	if($change_qty > 0) {
		$sql = "UPDATE mallRN_cart SET qty = '{$change_qty}' WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
		$mysql->query3($sql);

		$GLOBALS['row2']['qty'] = $change_qty;
		$GLOBALS['msg'] = "[{$return['name']} {$return['option']}]<br />재고수량 변경으로 인해 수량이 {$change_qty}로 변경 되었습니다.";
		$tpl->parse("loop_chnage_qty");
	}
	
	if($return['disabled'] == 1) {
		$sql = "UPDATE mallRN_cart SET selects = 0 WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
		$mysql->query3($sql);
	}
	
	$return['event_discount'] = 0;
	if($data['exhibition'] && $data['exhibition'] != ',') {	
		foreach($event_info_array as $k => $v) {
			if(preg_match("/,{$k},/i", $data['exhibition'])) {				
				$return['event_discount'] = $v;
				break;
			}
		}
	}
	
	$return['mileage'] = 0;
	if($my_id) {
		$ck_mileage = 0;
		switch($data['mileage_type']) {
			case "1" : 
				$ck_mileage = $shop_config['member_mileage_order'] + $my_mileage;
			break;
			case "3" :
				$mileage_level	= explode("|*|", $data['mileage_level']);
				foreach($mileage_level as $k => $v) {
					$mileage_level2	= explode("|", $v);
					if($mileage_level2[0] == $my_level) {
						$ck_mileage = $mileage_level2[1];
						break;
					}
				}
			break;
			case "4" :
				$ck_mileage = $data['mileage_common'];
			break;
		}		
		if($ck_mileage > 0) {
			$tmps				= (int) str_replace(",", "", $return['price']);
			$return['mileage']	= ($tmps * $ck_mileage) / 100;
		}
	}

	return $return;		
}

/*
##############################################
    ::: 장바구니 체크 :::          
    사용방법 : checkCartOrder ()
##############################################
*/
function checkCartOrder($direct) {
	global $mysql, $cart_id, $my_id;

	$rtn	= 0;
	$cnt	= 0;
	
	if($direct) {
		$where		= " && direct = 1";
		$setValue	= " direct = 0";
	}
	else {
		$where		= " && selects = 1";
		$setValue	= " selects = 0";
	}

	$sql = "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where}";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		$cnt = 1;

		if($row['vendor_delivery']) {
			$sql	= "SELECT comp_name, sell FROM mallRN_vendor WHERE id = '{$row['vendor_delivery']}'";
			$vinfo	= $mysql->one_row($sql);
			if($vinfo['sell'] != 'A') {
				$sql = "UPDATE mallRN_cart SET {$setValue} WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
				$mysql->query2($sql);
				$rtn = 1;
				continue;
			}
		}

		$sql	= "SELECT sale_use, option_use, qty_type, qty FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
		$data	= $mysql->one_row($sql);

		if($data['sale_use'] == 0 || ($data['option_use'] == 0 && $data['qty_type'] == 0 && $data['qty'] < 1)) {
			$sql = "UPDATE mallRN_cart SET {$setValue} WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
			$mysql->query2($sql);
			$rtn = 1;
			continue;
		}
		
		if($row['option']) {
			$sql	= "SELECT value, qty_type, qty FROM mallRN_goods_option WHERE uid = '{$row['option']}'";
			$odata	= $mysql->one_row($sql);

			if($odata['qty_type'] == 0 && $odata['qty'] < $row['qty']) {
				if($odata['qty'] == 0) {
					$sql = "UPDATE mallRN_cart SET {$setValue} WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
					$mysql->query2($sql);
					$rtn = 1;
					continue;
				}
				else {
					$sql = "UPDATE mallRN_cart SET qty = '{$odata['qty']}' WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
					$mysql->query2($sql);
					$rtn = 1;
					continue;
				}
			}
		}
		else {
			if($data['option_use'] == 1) {
				$sql = "UPDATE mallRN_cart SET {$setValue} WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
				$mysql->query2($sql);
				$rtn = 1;
				continue;									
			}

			if($data['qty_type'] == 0 && $data['qty'] < $row['qty']) {
				$sql = "UPDATE mallRN_cart SET qty = '{$data['qty']}' WHERE cart_id = '{$cart_id}' && uid = '{$row['uid']}'";
				$mysql->query2($sql);
				$rtn = 1;
				continue;
			}
		}	
	}

	if($cnt == 0) $rtn = 2;

	return $rtn;

}

/*
##############################################
    ::: 옵션상품 품절체크 :::          
    사용방법 : goodsOptionSoldOut (상품고유값)
##############################################
*/
function goodsOptionSoldOut($uid) {
	$mysql2 = new mysqlClass(); 
	
	$sql		= "SELECT qty, qty_type FROM mallRN_goods_option WHERE guid = '{$uid}'";
	$mysql2->query($sql);
	
	$cnt		= 0;
	$so_cnt		= 0;
	while($row = $mysql2->fetch_array()){
		if($row['qty_type'] == 0) {
			if($row['qty'] == 0) $so_cnt ++;
		}

		$cnt ++;		
	}
	
	if($so_cnt == 0) $sold_out = 0;
	else if($cnt == $so_cnt) $sold_out = 2;
	else $sold_out = 1;

	$sql = "UPDATE mallRN_goods SET option_soldout = {$sold_out} WHERE uid = '{$uid}'";
	$mysql2->query($sql);
}


/*
##############################################
    ::: 주문상품 재고수량 차감 :::          
    사용방법 : goodsOrderQtyChange ()
##############################################
*/
function goodsOrderQtyChange($order_num) {
	global $mysql;

	if(!$order_num) return;

	$sql = "SELECT g_uid, price, qty, `option` FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){ 

		if($row['option']) {
			$sql	= "SELECT qty_type, qty FROM mallRN_goods_option WHERE uid = '{$row['option']}'";
			$data	= $mysql->one_row($sql);

			if($data['qty_type'] == 0) {
				if($data['qty'] < $row['qty']) $row['qty'] =  $data['qty'];
				$sql = "UPDATE mallRN_goods_option SET qty = qty - {$row['qty']} WHERE uid = '{$row['option']}'";
				$mysql->query2($sql);

				goodsOptionSoldOut($row['g_uid']);
			}
		}
		else {
			$sql	= "SELECT qty_type, qty FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
			$data	= $mysql->one_row($sql);

			if($data['qty_type'] == 0) {
				if($data['qty'] < $row['qty']) $row['qty'] =  $data['qty'];
				$sql = "UPDATE mallRN_goods SET qty = qty - {$row['qty']} WHERE uid = '{$row['g_uid']}'";
				$mysql->query2($sql);
			}
		}

		$sql = "UPDATE mallRN_goods SET order_cnt = order_cnt + {$row['qty']} WHERE uid = '{$row['g_uid']}'";
		$mysql->query2($sql);
	}
}

/*
##############################################
    ::: 주문상품 취소에 따른 재고수량 증가 :::          
    사용방법 : goodsOrderQtyCancel (주문상품고유값)
##############################################
*/
function goodsOrderQtyCancel($uid) {
	global $mysql;

	if(!$uid) return;

	$sql = "SELECT g_uid, price, qty, option FROM mallRN_order_goods WHERE uid = '{$uid}' && reals = 1";
	$mysql->query3($sql);

	while($row = $mysql->fetch_array(3)){ 

		if($row['option']) {
			$sql	= "SELECT qty_type, qty FROM mallRN_goods_option WHERE uid = '{$row['option']}'";
			$data	= $mysql->one_row($sql);

			if($data['qty_type'] == 0) {
				$sql = "UPDATE mallRN_goods_option SET qty = qty + {$row['qty']} WHERE uid = '{$row['option']}'";
				$mysql->query4($sql);

				goodsOptionSoldOut($row['g_uid']);
			}			
		}
		else {
			$sql	= "SELECT qty_type, qty FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
			$data	= $mysql->one_row($sql);

			if($data['qty_type'] == 0) {
				$sql = "UPDATE mallRN_goods SET qty = qty + {$row['qty']} WHERE uid = '{$row['g_uid']}'";
				$mysql->query4($sql);
			}
		}
		
		$sql	= "SELECT order_cnt FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
		$data	= $mysql->one_row($sql);

		if($data['order_cnt']) {
			if($data['order_cnt'] < $row['qty']) $row['qty'] =  $data['order_cnt'];		
			$sql = "UPDATE mallRN_goods SET order_cnt = order_cnt - {$row['qty']} WHERE uid = '{$row['g_uid']}'";
			$mysql->query4($sql);
		}
	}
}

/*
##############################################
    ::: 주문 매출 저장 :::          
    사용방법 : orderSalesInsert ()
##############################################
*/

function orderSalesInsert($values) {
	global $mysql;

	if(!$values) return;

	if(!isset($values['confirmation'])) $values['confirmation'] = 0;
	if(!isset($values['confirm_date'])) $values['confirm_date'] = 0;
	
	$item_array = array('id', 'vendor', 'order_num', 'og_uid', 'g_uid', 'g_cate', 'price', 'qty', 'commission', 'option', 'title', 'type', 'status', 'mobile', 'week', 'new', 'level', 'addr', 'pay_type', 'signdate', 'confirmation', 'confirm_date');
	
	$sql = "INSERT INTO mallRN_order_sales SET";
	foreach ($item_array as $k => $v) {
		if($k == count($item_array) - 1) $sql .= " {$v} = '{$values[$v]}'";
		else $sql .= " {$v} = '{$values[$v]}',";
	}
	$mysql->query3($sql);
}

/*
##############################################
    ::: 결제완료 처리 :::          
    사용방법 : orderStatus1('주문번호', '처리자 아이디');
##############################################
*/
function orderStatus1($order_num, $id) {
	global $mysql;

	if(!$order_num) return;

	$sql	= "SELECT id, name, cell, email, use_mileage, use_coupon, coupon_uid, pay_type, pay_total, pay_status, mobile, sales_issued, cash_receipts, address1, new FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
	if(!$data	= $mysql->one_row($sql)) return;

	$addr_array = array('경기', '서울', '인천', '강원', '충청남', '충청북', '대전', '세종', '경상북', '경상남', '대구', '부산', '울산', '전라북', '전라남', '광주', '제주');

	$values = array();
	$values['id']			= $data['id'];
	$values['order_num']	= $order_num;
	$values['mobile']		= $data['mobile'];
	$values['signdate']		= time();
	$values['week']			= date('w');
	$values['new']			= $data['new'];
	$values['pay_type']		= $data['pay_type'];

	if($data['id']) {
		$sql				= "SELECT level FROM mallRN_member WHERE id = '{$data['id']}'";
		$values['level']	= $mysql->get_one($sql);
		if(!$values['level']) $values['level'] = 1;

		$sql				= "UPDATE mallRN_member SET order_time = '{$values['signdate']}' WHERE id = '{$data['id']}'";
		$mysql->query($sql);
	}
	else $values['level']	= 0;
	
	$values['addr'] = 0;
	foreach($addr_array as $k => $v) {
		if(preg_match("/^{$v}/i", $data['address1'])) {
			$values['addr']	= $k + 1;
			break;
		}
	}

	$sql = "SELECT uid, vendor, vendor_delivery, g_uid, g_cate, g_name, price, orig_price, qty, option, option_name, delivery_type, delivery_type_qty, delivery_price, delivery_add_price, use_coupon, coupon_uid, discount, discount_info, status FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1";
	$mysql->query($sql);
	
	$sum_delivery_option = array();

	while($row = $mysql->fetch_array()){ 

		if($row['status'] != 0) continue;

		$row['g_name']			= addslashes($row['g_name']);
		$row['option_name']		= addslashes($row['option_name']);
	
		$values['vendor']		= $row['vendor'];		
		$values['og_uid']		= $row['uid'];
		$values['g_uid']		= $row['g_uid'];
		$values['g_cate']		= $row['g_cate'];
		$values['option']		= $row['option'];
				
		$values['price']		= ($row['price'] + $row['use_coupon'] + $row['discount']) * $row['qty'];
		$values['qty']			= $row['qty'];
		$values['commission']	= $values['price'] - ($row['orig_price'] * $row['qty']);
		$values['title']		= $row['g_name'];
		if($row['option_name']) $values['title'] .= " [".$row['option_name']."]";
		$values['title']		.= "구매";
		$values['type']			= 0;
		$values['status']		= 0;		

		orderSalesInsert($values);
		
		$values['qty']			= 0;
		$values['commission']	= 0;
		$values['vendor']		= $row['vendor_delivery'];

		$row['delivery_price']	+= $row['delivery_add_price'];
		if($row['delivery_price']) {
			if($row['delivery_type'] == 5) {
				if($row['option']) {				
					if(isset($sum_delivery_option[$row['g_uid']]) && $sum_delivery_option[$row['g_uid']] > 0) {
						$G_DELIVERY_PRICE	= 0;					
					}
					else {
						$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$row['g_uid']}' && !(status = 9 && status2 = 5)";
						$option_qty			= $mysql->get_one($sql);
						$G_DELIVERY_PRICE	= $row['delivery_price'] * ceil($option_qty / $row['delivery_type_qty']);
						$sum_delivery_option[$row['g_uid']] = $option_qty;
					}
				}
				else {
					$G_DELIVERY_PRICE	= $row['delivery_price'] * ceil($row['qty'] / $row['delivery_type_qty']);
				}
				$values['price'] = $G_DELIVERY_PRICE;	
			}
			else $values['price'] = $row['delivery_price'];	
			
			$values['title']		= "{$row['g_name']} 개별배송비";
			$values['type']			= 1;
			
			if($values['price'] > 0) orderSalesInsert($values);
		}
		
		$values['vendor']		= '';
		$values['status']		= 1;

		if($row['use_coupon']) {
			$values['price']		= $row['use_coupon'];			
			$values['title']		= "{$row['g_name']} 상품할인쿠폰 사용(쿠폰UID : {$row['coupon_uid']})";
			$values['type']			= 3;

			orderSalesInsert($values);		
		}

		if($row['discount']) {
			$values['price']		= $row['discount'] * $row['qty'];
			$values['title']		= "{$row['g_name']} 상품할인 ({$row['discount_info']})";
			$values['type']			= 4;			

			orderSalesInsert($values);		
		}

		$sql = "UPDATE mallRN_order_goods SET status = 1, status_date = '{$values['signdate']}' WHERE order_num = '{$order_num}' && uid = '{$row['uid']}'";
		$mysql->query2($sql);
		
		if($id) {
			$sql = "INSERT INTO mallRN_order_log SET
						order_num	= '{$order_num}',
						og_uid		= '{$row['uid']}',
						id			= '{$id}',
						prev_status	= '{$row['status']}',
						status		= '1',
						signdate	= '{$values['signdate']}'
					";
			$mysql->query2($sql);			
		}
	}
	
	$values['qty']			= 0;
	$values['og_uid']		= 0;
	$values['g_uid']		= 0;
	$values['g_cate']		= 0;
	$values['commission']	= 0;
	$values['option']		= '';
	$values['vendor']		= '';

	if($data['sales_issued'] == 0) {

		if($data['use_coupon']) {
			$values['price']		= $data['use_coupon'];		
			$values['title']		= "장바구니쿠폰사용 (쿠폰UID : {$data['coupon_uid']})";
			$values['type']			= 3;	
			$values['status']		= 1;

			orderSalesInsert($values);		
		}

		if($data['use_mileage']) {
			$values['price']		= $data['use_mileage'];
			$values['title']		= "마일리지사용";
			$values['type']			= 2;		
			$values['status']		= 1;

			orderSalesInsert($values);
		}

		$sql = "SELECT * FROM mallRN_order_delivery WHERE order_num = '{$order_num}' && price > 0";
		$mysql->query($sql);
		
		while($row = $mysql->fetch_array()){ 

			$values['vendor']		= $row['vendor'];
			$values['price']		= $row['price'];
			if($row['vendor'])	$values['title'] = "입점사배송비";
			else				$values['title'] = "본사배송비";
			$values['status']		= 0;
			$values['type']			= 1;
			
			orderSalesInsert($values);
		}
	}
	
	$sql			= "SELECT payment_commission_c, payment_commission_r, payment_commission_r2, payment_commission_v, payment_commission_h FROM mallRN_configuration WHERE uid = 1";
	$shop_config	= $mysql->one_row($sql);

	$cp_commission = '';
	switch($data['pay_type']) {
		case "C" : $cp_commission = $shop_config['payment_commission_c']; $cp_title = "카드결제 CP수수료 : {$shop_config['payment_commission_c']}%"; break;
		case "R" : $cp_commission = $shop_config['payment_commission_r']; $cp_title = "실시간계쫘이체 CP수수료 : {$shop_config['payment_commission_r']}%"; break;
		case "V" : $cp_commission = $shop_config['payment_commission_v']; $cp_title = "가상계좌 CP수수료 : {$shop_config['payment_commission_v']}원"; break;
		case "H" : $cp_commission = $shop_config['payment_commission_h']; $cp_title = "핸드폰결제 CP수수료 : {$shop_config['payment_commission_c']}%"; break;	 
	}

	if($cp_commission) {
		$cp_price	= round($data['pay_total'] * ($cp_commission / 100));
		if($data['pay_type'] == 'R') {
			if($shop_config['payment_commission_r2'] && $cp_price < $shop_config['payment_commission_r2']) {
				$cp_title	= "실시간계쫘이체 CP수수료 : {$shop_config['payment_commission_r2']}원";
				$cp_price	= $shop_config['payment_commission_r2'];
			}
		}	

		$values['vendor']		= '';
		$values['price']		= $cp_price;
		$values['title']		= "CP수수료";
		$values['status']		= 1;
		$values['type']			= 5;

		if($cp_price > 0) orderSalesInsert($values);

	}	

	$sql = "UPDATE mallRN_order_info SET sales_issued = 1, pay_status = 'C', status_date = '{$values['signdate']}' WHERE order_num = '{$order_num}'";
	$mysql->query2($sql);

	if(isset($GLOBALS['CK_CP_DB_ERROR'])) {
		if($GLOBALS['bSucc'] == 'false') return;
	}

	if($data['pay_type'] == 'B') {
		$replace_code_array	= array('ORDER_NAME' => stripslashes($data['name']), 'ORDER_NUM' => $order_num);
		mallSmsAuto('bank_ok', $data['cell'], $replace_code_array);

		$sql = "SELECT DISTINCT(vendor) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor ASC";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()){
			if(!$row['vendor']) continue;
			
			$sql	= "SELECT cont_cell FROM mallRN_vendor WHERE id = '{$row['vendor']}'";
			$cell	= $mysql->get_one($sql);

			if($cell) mallSmsAuto('pay_ok2', $cell, $replace_code_array);
		}
	}

	if(($data['pay_type'] == 'B' || $data['pay_type'] == 'V' || $data['pay_type'] == 'R') && $data['cash_receipts']) cashReceiptsApply($order_num);

}


/*
##############################################
    ::: 현금영수증 신청 :::          
    사용방법 : cashReceiptsApply('주문번호');
##############################################
*/
function cashReceiptsApply($order_num) {
	global $mysql;
	
	$sql			= "SELECT payment_cp, cash_receipts_used, cash_receipts_type, cash_receipts_method FROM mallRN_configuration WHERE uid = 1";
	$shop_config	= $mysql->one_row($sql);	
	
	if($shop_config['cash_receipts_used']) {
		
		$sql	= "SELECT id, name, cell, email, pay_type, pay_total, cancel_total, refund_total, cash_receipts, status_date FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		if(!$data	= $mysql->one_row($sql)) return;

		if(($data['pay_type'] == 'B' || $data['pay_type'] == 'V' || $data['pay_type'] == 'R') && $data['cash_receipts'] && $data['status_date']) {

			$sql			= "SELECT count(*) as cnt, g_name FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1";
			$data2			= $mysql->one_row($sql);

			$goods_name		= stripslashes($data2['g_name']);
			if($data2['cnt'] > 1) $goods_name .= "외 ".($data2['cnt'] - 1)."건";
			
			$tax_type		= 0;
			if($shop_config['cash_receipts_type'] == 1) $tax_type	= 1;
			
			$cash_type		= 0;
			$tmps			= explode("|", $data['cash_receipts']);
			if($tmps[0]	== 2) $cash_type	= 1;

			$price			= $data['pay_total'] - $data['refund_total'] - $data['cancel_total'];

			$signdate		= time();
			
			$sql = "INSERT INTO mallRN_order_cash_receipts SET
						order_num		= '{$order_num}',
						id				= '{$data['id']}',
						name			= '{$data['name']}',
						cell			= '{$data['cell']}',
						email			= '{$data['email']}',
						price			= '{$price}',
						goods_name		= '{$goods_name}',
						pay_type		= '{$data['pay_type']}',
						tax_type		= '{$tax_type}',
						cash_type		= '{$cash_type}',
						auth_number		= '{$tmps[1]}',
						signdate		= '{$signdate}'
					";
			$mysql->query3($sql);		

			if($shop_config['cash_receipts_method'] == 1) {
				
				switch($shop_config['payment_cp']) {
					case "KCP" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/kcp/cash_receipts.php?order_num={$order_num}";
					break;
					case "NICEPAY" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/nicepay/cash_receipts.php?order_num={$order_num}";
					break;
					case "INICIS" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/inicis/cash_receipts.php?order_num={$order_num}";
					break;
				}
				
				socketPost($URL, 'POST', 0); //비동기 실행	
			}

		}
	}

}


/*
##############################################
    ::: 배송완료 처리 :::          
    사용방법 : orderStatus4('주문번호', '주문상품고유값', '처리자', '메세지타입');
##############################################
*/
function orderStatus4($order_num, $uid, $id, $msg = '') {
	global $mysql;

	$signdate		= time();

	$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && uid = '{$uid}' && reals = 1";
	if(!$data = $mysql->one_row($sql)) {
		if($msg == 1) logMsg("주문상품이 없거나 삭제 되었습니다.");
		else return;
	}		

	if($data['status'] != 3 && !($data['status'] == 7 && $data['status2'] == 4)) {
		if($msg == 1) logMsg("배송중 상태일때만 수취확인이 가능 합니다.");
		else return;
	}	

	if($data['status'] == 7 && $data['status2'] == 4) {
		$sql	= "UPDATE mallRN_order_status_change SET status2 = 5, status_date = '{$signdate}' WHERE order_num = '{$order_num}' && og_uid = '{$uid}' && status = 7 && status2 = 4";
		$mysql->query2($sql);
	}

	$sql = "UPDATE mallRN_order_goods SET status = '4', status2 = '0', status_date = '{$signdate}' WHERE uid = '{$uid}'";
	$mysql->query2($sql);
	
	$sql = "INSERT INTO mallRN_order_log SET
				order_num		= '{$order_num}',
				og_uid			= '{$uid}',
				id				= '{$id}',
				prev_status		= '{$data['status']}',
				prev_status2	= '{$data['status2']}',
				status			= '4',
				signdate		= '{$signdate}'
			";
	$mysql->query2($sql);	
}


/*
##############################################
    ::: 구매확정 처리 :::          
    사용방법 : orderStatus5('주문번호', '주문상품고유값', '처리자', '메세지타입');
##############################################
*/
function orderStatus5($order_num, $uid, $id, $msg = '') {
	global $mysql;

	$signdate		= time();

	$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && uid = '{$uid}' && reals = 1";
	if(!$data = $mysql->one_row($sql)) {
		if($msg == 1) logMsg("주문상품이 없거나 삭제 되었습니다.");
		else return;
	}		

	if($data['status'] != 4 && $data['status'] != 3 ) {
		if($msg == 1) logMsg("배송중, 배송완료 상태일때만 구매확정이 가능 합니다.");
		else return;
	}	

	$sql = "UPDATE mallRN_order_goods SET status = '5', status2 = '0', status_date = '{$signdate}' WHERE uid = '{$uid}'";
	$mysql->query2($sql);
	
	$sql = "INSERT INTO mallRN_order_log SET
				order_num		= '{$order_num}',
				og_uid			= '{$uid}',
				id				= '{$id}',
				prev_status		= '{$data['status']}',
				prev_status2	= '{$data['status2']}',
				status			= '5',
				signdate		= '{$signdate}'
			";
	$mysql->query2($sql);

	$sql = "UPDATE mallRN_order_sales SET confirmation = '1', confirm_date = '{$signdate}' WHERE order_num = '{$order_num}' && og_uid = '{$uid}' && type = 0 && status = 0";
	$mysql->query2($sql);

	if($data['delivery_type'] == 1) {
		$sql = "SELECT count(*) FROM mallRN_order_sales WHERE confirmation = '0' && order_num = '{$order_num}' && og_uid = 0 && type = 1 && status = 0 && vendor = '{$data['vendor_delivery']}'";
		if($mysql->get_one($sql) > 0) {
			$sql = "UPDATE mallRN_order_sales SET confirmation = '1', confirm_date = '{$signdate}' WHERE confirmation = '0' && order_num = '{$order_num}' && og_uid = 0  && g_uid = 0 && type = 1 && status = 0 && vendor = '{$data['vendor_delivery']}'";
			$mysql->query2($sql);
		}
	}
	else if($data['delivery_type'] == 4 || $data['delivery_type'] == 5) {
		$sql = "UPDATE mallRN_order_sales SET confirmation = '1', confirm_date = '{$signdate}' WHERE confirmation = '0' && order_num = '{$order_num}' && og_uid = '{$uid}' && g_uid = '{$data['g_uid']}' && type = 1 && status = 0 && vendor = '{$data['vendor_delivery']}'";
		$mysql->query2($sql);
	}

	if($data['mileage']) {
		$mileage	= $data['mileage'];
		
		$sql		= "SELECT id FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		$id			= $mysql->get_one($sql);

		$content	= addslashes($data['g_name'])." 상품구매 마일리지 적립";

		$sql		= "SELECT count(*) FROM mallRN_mileage WHERE id = '{$id}' && order_num = '{$order_num}' && goods_uid = '{$uid}'";
		if($mysql->get_one($sql) == 0) {
			saveMileageChange($id, $mileage, $content, $order_num, $uid);
		}
	}
}


/*
##############################################
    ::: 미입금시 취쇠완료 처리 :::          
    사용방법 : orderStatus9('주문번호');
##############################################
*/
function orderStatus9($order_num, $id, $msg = '') {
	global $mysql;

	$table_name		= 'mallRN_order_info';
	$table_name2	= 'mallRN_order_goods';
	$signdate		= time();

	$sql = "SELECT * FROM {$table_name} WHERE order_num = '{$order_num}' && reals = 1";
	$data = $mysql->one_row($sql);

	if($data['pay_status'] == 'C') {
		if($msg == 1) logMsg("입금대기중 상태일 때만 취소가 가능 합니다.");
		else return;
	}

	$sql	= "SELECT * FROM {$table_name2} WHERE order_num = '{$order_num}' && reals = 1";
	$mysql->query2($sql);

	whilE($row = $mysql->fetch_array(2)) {

		if($row['status'] == 0) {
			$sql = "UPDATE {$table_name2} SET status = '9', status2 = 5, status_date = '{$signdate}' WHERE uid = '{$row['uid']}'";
			$mysql->query3($sql);
			
			$sql = "INSERT INTO mallRN_order_log SET
						order_num	= '{$order_num}',
						og_uid		= '{$row['uid']}',
						id			= '{$id}',
						prev_status	= '{$row['status']}',
						status		= '9',
						status2		= '5',
						signdate	= '{$signdate}'
					";
			$mysql->query3($sql);
			
			goodsOrderQtyCancel($row['uid']);

			if($row['use_coupon'] && $row['coupon_uid']) {
				$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$id}' && uid = '{$row['coupon_uid']}'";
				$mysql->query3($sql);
			}
		}				
	}
	
	if($data['use_coupon'] && $data['coupon_uid']) {
		$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$id}' && uid = '{$data['coupon_uid']}'";
		$mysql->query2($sql);
	}

	if($data['use_mileage']) {
		saveMileageChange($data['id'], $data['use_mileage'], "주문취소에 따른 마일리지 사용 환원", $order_num);
	}

	$sql = "UPDATE {$table_name} SET cancel_total = '{$data['pay_total']}', use_mileage = 0, use_coupon = 0 WHERE order_num = '{$order_num}'";
	$mysql->query2($sql);

}

/*
##############################################
    ::: 결제완료시 취소완료 처리 :::          
    사용방법 : orderStatus95('주문번호', '처리자아이디', '에러메세지타입');
##############################################
*/
function orderStatus95($order_num, $id, $msg = '') {
	global $mysql;

	$table_name		= 'mallRN_order_info';
	$table_name2	= 'mallRN_order_goods';
	$signdate		= time();

	$sql = "SELECT * FROM {$table_name} WHERE order_num = '{$order_num}' && reals = 1";
	$data = $mysql->one_row($sql);

	if($data['pay_status'] != 'C') {
		if($msg == 1) logMsg("결제완료 상태일 때만 취소가 가능 합니다.");
		else return;
	}

	$sql	= "SELECT * FROM {$table_name2} WHERE order_num = '{$order_num}' && reals = 1";
	$mysql->query2($sql);

	whilE($row = $mysql->fetch_array(2)) {

		if($row['status'] == 9) {
			$sql	= "UPDATE {$table_name2} SET status2 = 5, status_date = '{$signdate}' WHERE uid = '{$row['uid']}'";
			$mysql->query3($sql);

			$sql	= "SELECT status, status2 FROM mallRN_order_log WHERE order_num = '{$order_num}' && og_uid = '{$row['uid']}' ORDER BY uid DESC LIMIT 1";
			$sinfo	= $mysql->one_row($sql);
			
			$sql	= "INSERT INTO mallRN_order_log SET
						order_num		= '{$order_num}',
						og_uid			= '{$row['uid']}',
						id				= '{$id}',
						prev_status		= '{$sinfo['status']}',
						prev_status2	= '{$sinfo['status2']}',
						status			= '9',
						status2			= '5',
						signdate		= '{$signdate}'
					";
			$mysql->query3($sql);
			
			goodsOrderQtyCancel($row['uid']);

			if($row['use_coupon'] && $row['coupon_uid']) {
				$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$data['id']}' && uid = '{$row['coupon_uid']}'";
				$mysql->query3($sql);
			}
		}				
	}
	
	if($data['use_coupon'] && $data['coupon_uid']) {
		$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$data['id']}' && uid = '{$data['coupon_uid']}'";
		$mysql->query2($sql);
	}

	if($data['use_mileage']) {
		saveMileageChange($data['id'], $data['use_mileage'], "주문취소에 따른 마일리지 사용 환원", $order_num);
	}

	$pay_total	= $data['pay_total'] + $data['use_mileage'] + $data['use_coupon'];			
	
	$sql		= "UPDATE mallRN_order_info SET pay_total = '{$pay_total}', refund_total = '{$pay_total}', use_mileage = 0, use_coupon = 0 WHERE order_num = '{$order_num}'";
	$mysql->query2($sql);

	if($data['cash_issued'] == 1) {		
		$sql		= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
		$payment_cp	= stripslashes($mysql->get_one($sql)); 

		switch($payment_cp) {
			case "KCP" :
				$URL		= ABSOLUTE_PATH_SHOP."plugin/kcp/cash_receipts.php?order_num={$order_num}&mode=cancel";
			break;
			case "NICEPAY" :
				$URL		= ABSOLUTE_PATH_SHOP."plugin/nicepay/cash_receipts.php?order_num={$order_num}&mode=cancel";
			break;
			case "INICIS" :
				$URL		= ABSOLUTE_PATH_SHOP."plugin/inicis/cash_receipts.php?order_num={$order_num}&mode=cancel";
			break;
		}
		socketPost($URL, 'POST', 0); //비동기 실행			
	}	
}

/*
##############################################
    ::: 결제완료시 부분취소/반품완료 처리 :::          
    사용방법 : orderStatus95_partial('주문번호', '처리자아이디', '선택주문상품고유번호');
##############################################
*/
function orderStatus95_partial($order_num, $id, $og_uid) {
	global $mysql, $refund, $mileage, $refund_fee, $coupon, $delivery;

	$signdate	= time();

	$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
	if(!$info = $mysql->one_row($sql)) logMsg("해당주문이 삭제되었거나 존재하지 않습니다.");

	if($info['pay_status'] != 'C') logMsg("결제완료 상태일 때만 취소가 가능 합니다.");

	$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && uid = '{$og_uid}' && reals = 1";
	$data2	= $mysql->one_row($sql);
	
	if($data2['status'] == 9 || $data2['status'] == 8) {
		$sql = "UPDATE mallRN_order_goods SET status2 = 5, status_date = '{$signdate}' WHERE uid = '{$data2['uid']}'";
		$mysql->query($sql);

		$sql	= "SELECT status, status2 FROM mallRN_order_log WHERE og_uid = '{$data2['uid']}' ORDER BY uid DESC LIMIT 1";
		$sinfo	= $mysql->one_row($sql);			
		
		$sql = "INSERT INTO mallRN_order_log SET
					order_num		= '{$order_num}',
					og_uid			= '{$data2['uid']}',
					id				= '{$id}',
					prev_status		= '{$sinfo['status']}',
					prev_status2	= '{$sinfo['status2']}',
					status			= '{$data2['status']}',
					status2			= '5',
					signdate		= '{$signdate}'
				";
		$mysql->query3($sql);
		
		goodsOrderQtyCancel($data2['uid']);

		if($data2['use_coupon'] && $data2['coupon_uid']) {
			$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$info['id']}' && uid = '{$data2['coupon_uid']}'";
			$mysql->query3($sql);
		}
	}

	$sql		= "SELECT * FROM mallRN_order_sales WHERE order_num = '{$order_num}' && og_uid = '{$og_uid}' && type = 0";
	$sinfo		= $mysql->one_row($sql);
	
	$values = array();
	$values['id']			= $sinfo['id'];	
	$values['order_num']	= $order_num;
	$values['og_uid']		= 0;
	$values['g_uid']		= 0;
	$values['g_cate']		= 0;
	$values['qty']			= 0;
	$values['commission']	= 0;
	$values['option']		= '';
	$values['mobile']		= $sinfo['mobile'];
	$values['week']			= date('w');
	$values['new']			= $sinfo['new'];
	$values['level']		= $sinfo['level'];
	$values['addr']			= $sinfo['addr'];
	$values['pay_type']		= $sinfo['pay_type'];
	$values['signdate']		= time();
	
	if($delivery) {
		$values['vendor']	= $data2['vendor_delivery'];
		$values['price']	= (int) str_replace(",", "", $delivery);

		if($data2['vendor_delivery'])	$values['title'] = "[주문취소] 입점사배송비";
		else							$values['title'] = "[주문취소] 본사배송비";
	
		$values['type']		= 1;
		$values['status']	= 1;

		if($sinfo['confirmation'] == 1) {
			$values['confirmation'] = 1;
			$values['confir_date']	= time();
		}
		orderSalesInsert($values);		
	}

	$values['vendor']	= '';

	$sql	= "UPDATE mallRN_order_info SET refund_total = refund_total + '{$refund}' WHERE order_num = '{$order_num}'";
	$mysql->query3($sql);

	if($info['use_mileage']) {
		$mileage2 = $mileage - $refund_fee;
		
		if($mileage2 > 0) {
			$values['title']		= "[주문취소] 마일리지사용";
			$values['type']			= 2;
			$values['status']		= 0;

			if($info['use_mileage'] < $mileage2) {
				saveMileageChange($info['id'], $info['use_mileage'], "주문취소에 따른 마일리지 사용 환원", $order_num);
				
				$values['price']	= $info['use_mileage'];
				orderSalesInsert($values);

				$mileage2 = $mileage2 - $info['use_mileage'];
				saveMileageChange($info['id'], $mileage2, "주문취소에 따른 환불금액 마일리지 적립", $order_num);
				
				$sql	= "UPDATE mallRN_order_info SET pay_total = pay_total + {$info['use_mileage']}, refund_total = refund_total + '{$mileage}', use_mileage = 0 WHERE order_num = '{$order_num}'";
				$mysql->query($sql);
			}
			else {
				saveMileageChange($info['id'], $mileage2, "주문취소에 따른 마일리지 사용 환원", $order_num);

				$values['price']	= $mileage;
				orderSalesInsert($values);

				$sql	= "UPDATE mallRN_order_info SET pay_total = pay_total + {$mileage}, refund_total = refund_total + '{$mileage}', use_mileage = use_mileage - {$mileage} WHERE order_num = '{$order_num}'";
				$mysql->query($sql);
			}
		}
	}

	if($info['use_coupon'] && $coupon) {

		$sql	= "SELECT count(*) FROM mallRN_order_sales WHERE order_num = '{$order_num}' && type = 3 && g_uid = 0 && status = 0";
		if($mysql->get_one($sql) == 0) {
		
			$sql	= "SELECT * FROM mallRN_order_sales WHERE order_num = '{$order_num}' && type = 3 && g_uid = 0 && status = 1";
			if($data3	= $mysql->one_row($sql)) {
				$item_array = array('id', 'vendor', 'order_num', 'g_uid', 'g_cate', 'price', 'commission', 'option', 'title', 'type', 'status', 'mobile', 'week', 'level', 'pay_type');
				$values		= array();
					
				foreach ($item_array as $k => $v) {
					$values[$v]	= $data3[$v];
				}

				$values['title']	= "[주문취소] ".$values['title'];
				$values['signdate'] = time();

				if($values['status'] == 0)	$values['status'] = 1;
				else						$values['status'] = 0;

				orderSalesInsert($values);

				$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$info['id']}' && uid = '{$info['coupon_uid']}'";
				$mysql->query($sql);
			}
		}

		$sql	= "UPDATE mallRN_order_info SET pay_total = pay_total + {$coupon}, refund_total = refund_total + '{$coupon}', use_coupon = use_coupon - {$coupon} WHERE order_num = '{$order_num}'";
		$mysql->query($sql);
	
	}
	
	if($info['cash_issued'] == 1) {		
		$sql		= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
		$payment_cp	= stripslashes($mysql->get_one($sql)); 

		switch($payment_cp) {
			case "KCP" :
				$URL		= ABSOLUTE_PATH_SHOP."plugin/kcp/cash_receipts.php?order_num={$order_num}&mode=partial_cancel";
			break;
			case "NICEPAY" :
				$URL		= ABSOLUTE_PATH_SHOP."plugin/nicepay/cash_receipts.php?order_num={$order_num}&mode=partial_cancel";
			break;
			case "INICIS" :
				$URL		= ABSOLUTE_PATH_SHOP."plugin/inicis/cash_receipts.php?order_num={$order_num}&mode=partial_cancel";
			break;
		}
		socketPost($URL, 'POST', 0); //비동기 실행			
	}

}


/*
##############################################
    ::: 취소/반품시 매출 처리 :::          
    사용방법 : orderStatusX5Sales('주문번호', '처리상품조건');
##############################################
*/

function orderStatusX5Sales($order_num, $where = "") {
	global $mysql;

	$item_array = array('id', 'vendor', 'order_num', 'og_uid', 'g_uid', 'g_cate', 'price', 'qty', 'commission', 'option', 'title', 'type', 'status', 'mobile', 'week', 'new', 'level', 'addr', 'pay_type', 'confirmation', 'confirm_date');

	$sql	= "SELECT * FROM mallRN_order_sales WHERE order_num = '{$order_num}' {$where} ORDER BY uid ASC";
	$mysql->query2($sql);

	while($row = $mysql->fetch_array(2)) {

		if($row['type'] == 5 && $row['pay_type'] == 'R') {
			if(date("Y-m-d", $row['signdate']) != date("Y-m-d")) continue;
		}
		
		$values		= array();

		if($row['confirmation'] == 1) $row['confirm_date'] = time();
		
		foreach ($item_array as $k => $v) {
			$values[$v]	= $row[$v];
		}

		$values['title']	= "[주문취소] ".$values['title'];
		$values['signdate'] = time();

		if($values['status'] == 0)	$values['status'] = 1;
		else						$values['status'] = 0;

		orderSalesInsert($values);
	}
}

/*
##############################################
    ::: 취소/반품시 CP수수료 처리 :::          
    사용방법 : orderStatusX5SalesCP('주문번호', '결제방식', '환불금액', '결제일');
##############################################
*/

function orderStatusX5SalesCP($order_num, $pay_type, $refund, $status_date) {
	global $mysql;

	if($pay_type == 'R' && date("Y-m-d", $status_date) != date("Y-m-d")) return;

	$item_array = array('id', 'vendor', 'order_num', 'og_uid', 'g_uid', 'g_cate', 'price', 'qty', 'commission', 'option', 'title', 'type', 'status', 'mobile', 'week', 'new', 'level', 'addr', 'pay_type', 'confirmation', 'confirm_date');

	$sql	= "SELECT * FROM mallRN_order_sales WHERE order_num = '{$order_num}' && type = 5";
	if($data = $mysql->one_row($sql)) {	

		$sql			= "SELECT payment_commission_c, payment_commission_r, payment_commission_r2, payment_commission_v, payment_commission_h FROM mallRN_configuration WHERE uid = 1";
		$shop_config	= $mysql->one_row($sql);

		$cp_commission = '';
		switch($pay_type) {
			case "C" : $cp_commission = $shop_config['payment_commission_c']; $cp_title = "카드결제 CP수수료 환급 : {$shop_config['payment_commission_c']}%"; break;
			case "R" : $cp_commission = $shop_config['payment_commission_r']; $cp_title = "실시간계쫘이체 CP수수료 환급 : {$shop_config['payment_commission_r']}%"; break;
			case "H" : $cp_commission = $shop_config['payment_commission_h']; $cp_title = "핸드폰결제 CP수수료 환급: {$shop_config['payment_commission_c']}%"; break;	 
		}

		if($cp_commission) {
			$cp_price	= round($refund * ($cp_commission / 100));

			if($pay_type == 'R') {
				$cp_price2	= $data['price'] - $cp_price;
				if($shop_config['payment_commission_r2'] && $cp_price2 < $shop_config['payment_commission_r2']) {
					$cp_price	= $data['price'] - $cp_price2;
					if($cp_price < 1) return;
					$cp_title	= "실시간계쫘이체 CP수수료 환급: {$cp_price}원";					
				}
			}
		}
			
		$values		= array();

		if($data['confirmation'] == 1) $data['confirm_date'] = time();
		
		foreach ($item_array as $k => $v) {
			$values[$v]	= $data[$v];
		}

		$values['title']	= "[주문부분취소] ".$cp_title;
		$values['signdate'] = time();
		$values['price']	= $cp_price;
		$values['status']	= 0;

		orderSalesInsert($values);
	}
}

/*
##############################################
    ::: 주문정보 리셋 처리 :::          
    사용방법 : orderReset('주문번호');
##############################################
*/
function orderReset($order_num) {
	global $mysql, $shop_config;

	if(!$shop_config) {
		$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 1";
		$shop_config	= $mysql->one_row($sql);		
	}

	$table_name		= 'mallRN_order_info';
	$table_name2	= 'mallRN_order_goods';
	$signdate		= time();

	############################### 주문금액 체크 ###################################
	$sql = "SELECT DISTINCT(vendor_delivery) FROM {$table_name2} WHERE order_num = '{$order_num}' && reals = 1 ORDER BY vendor_delivery ASC";
	$mysql->query($sql);

	$SUM_PRICES						= 0;
	$SUM_DISCOUNT					= 0;
	$SUM_DELIVERY					= 0;
	$SUM_TOTAL						= 0;
	$VENDOR_ARRAY					= array();
	$VENDOR_DELIVERY_ARRAY			= array();
	$VENDOR_DELIVERY_INFO_ARRAY		= array();
	$VENDOR_DELIVERY_ADD_ARRAY		= array();
	$VENDOR_COMMISSION_ARRAY		= array();
	$CANCEL_TOTAL					= 0;

	while($row = $mysql->fetch_array()) {
		$VENDOR				= $row['vendor_delivery'];
		$VENDOR_ARRAY[]		= $VENDOR;

		$VENDOR_DELIVERY3	= "1"; //착불배송
		$vendor_sell = 0;
		if(!$VENDOR) {
			$DELIVERY_TYPE		= $shop_config['delivery_type'];
			$DELIVERY_PRICE1	= $shop_config['delivery_p_price1'];
			$DELIVERY_PRICE2	= $shop_config['delivery_p_price2'];
			$DELIVERY_PRICE3	= $shop_config['delivery_d_price'];
			$IM_INFO1			= $shop_config['delivery_im_areas1_used'];
			$IM_INFO2			= $shop_config['delivery_im_areas1_price'];
			$IM_INFO3			= $shop_config['delivery_im_areas2_used'];
			$IM_INFO4			= $shop_config['delivery_im_areas2_price'];
		}
		else {
			$sql	= "SELECT commission FROM mallRN_vendor WHERE id = '{$VENDOR}'";
			$vinfo	= $mysql->one_row($sql);

			$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$VENDOR}'";
			$vshop_config	= $mysql->one_row($sql);

			$DELIVERY_TYPE		= $vshop_config['delivery_type'];		
			$DELIVERY_PRICE1	= $vshop_config['delivery_p_price1'];
			$DELIVERY_PRICE2	= $vshop_config['delivery_p_price2'];		
			$DELIVERY_PRICE3	= $vshop_config['delivery_d_price'];
			$IM_INFO1			= $vshop_config['delivery_im_areas1_used'];
			$IM_INFO2			= $vshop_config['delivery_im_areas1_price'];
			$IM_INFO3			= $vshop_config['delivery_im_areas2_used'];
			$IM_INFO4			= $vshop_config['delivery_im_areas2_price'];

			$VENDOR_COMMISSION_ARRAY[$VENDOR] = $vinfo['commission'];
		}

		$VENDOR_DELIVERY_INFO_ARRAY[$VENDOR] = $DELIVERY_TYPE."|".$DELIVERY_PRICE1."|".$DELIVERY_PRICE2."|".$DELIVERY_PRICE3."|".$IM_INFO1."|".$IM_INFO2."|".$IM_INFO3."|".$IM_INFO4;

		if($DELIVERY_TYPE != 'D') $VENDOR_DELIVERY3 = "0";
		
		$sql	= "SELECT * FROM {$table_name} WHERE order_num = '{$order_num}' && reals = 1";
		$info	= $mysql->one_row($sql);

		$sql = "SELECT * FROM {$table_name2} WHERE order_num = '{$order_num}' && vendor_delivery = '{$VENDOR}' && reals = 1 ORDER BY uid DESC";
		$mysql->query2($sql);

		$VENDOR_PRICE						= 0;
		$VENDOR_DELIVERY					= 0;
		$VENDOR_DELIVERY_CK_PRICE			= 0;
		$VENDOR_DELIVERY_FREE				= 0;
		$VENDOR_DELIVERY_ARRAY[$VENDOR]		= 0;
		$VENDOR_CHECK_ARRAY[$VENDOR]		= 0;
		$sum_delivery_option				= array();
		
		while($row2 = $mysql->fetch_array(2)) {
			$QTY				= $row2['qty'];
			$G_UID				= $row2['g_uid'];
			$PRICE				= $row2['price'];
			$SUM_PRICE			= $PRICE * $QTY;
			
			$row2['delivery_price'] += $row2['delivery_add_price'];
			
			$G_DELIVERY_PRICE	= $row2['delivery_price'];
			
			if($row2['status'] == 9 && $row2['status2'] == 5) {
				if($row2['delivery_type'] == 5) {
					$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($QTY / $row2['delivery_type_qty']);
					if($row2['option']) {
						$sql = "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$G_UID}' && uid != '{$row2['uid']}'";
						if($mysql->get_one($sql) > 0) $G_DELIVERY_PRICE = 0;
					}
				}
				$CANCEL_TOTAL += $SUM_PRICE + $G_DELIVERY_PRICE;
			}
			else {
				if($row2['delivery_type'] == 1) { 
					$VENDOR_DELIVERY_CK_PRICE += $SUM_PRICE;			
				}
				else if($row2['delivery_type'] == 2) $VENDOR_DELIVERY_FREE = 1;
				else if($row2['delivery_type'] == 5) {
					if($row2['option']) {
						if(isset($sum_delivery_option[$G_UID]) && $sum_delivery_option[$G_UID] > 0) {
							$G_DELIVERY_PRICE	= 0;
							$option_qty = 0;
						}
						else {
							$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && g_uid = '{$G_UID}' && !(status = 9 && status2 = 5)";
							$option_qty			= $mysql->get_one($sql);
							$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($option_qty / $row2['delivery_type_qty']);
							$sum_delivery_option[$G_UID] = $option_qty;				
						}
					}
					else {				
						$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($QTY / $row2['delivery_type_qty']);
					}
				}

				if($row2['delivery_type'] != 3) $VENDOR_DELIVERY3 = "0";
				
				if($row2['delivery_type'] == 1) $VENDOR_CHECK_ARRAY[$VENDOR] = 1;
				
				$VENDOR_PRICE		+= $SUM_PRICE;
				$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;	
			}
		}
		
		if($VENDOR_DELIVERY_CK_PRICE && $VENDOR_DELIVERY_FREE == 0 && $DELIVERY_TYPE != 'F') {
			if($VENDOR_DELIVERY_CK_PRICE < $DELIVERY_PRICE1) {
				$VENDOR_DELIVERY				+= $DELIVERY_PRICE2;
				$VENDOR_DELIVERY_ARRAY[$VENDOR] += $DELIVERY_PRICE2;
			}
		}

		$SUM_PRICES			+= $VENDOR_PRICE;
		$SUM_DELIVERY		+= $VENDOR_DELIVERY;
	}

	if($VENDOR_DELIVERY3 == 0) {
		foreach($VENDOR_ARRAY as $k => $v) {
			if($VENDOR_CHECK_ARRAY[$v] == 1) {
				$sql = "SELECT * FROM mallRN_delivery_configuration WHERE vendor = '{$v}' && used = 1 ORDER BY uid ASC";
				$mysql->query($sql);

				$VENDOR_DELIVERY_ADD_ARRAY[$v] = 0;
			
				$shop_config2	= $shop_config;
				if($v) {
					$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$v}'";
					$vshop_config	= $mysql->one_row($sql);

					$shop_config2['delivery_im_areas1_used']		= $vshop_config['delivery_im_areas1_used'];
					$shop_config2['delivery_im_areas1_price']		= $vshop_config['delivery_im_areas1_price'];
					$shop_config2['delivery_im_areas2_used']		= $vshop_config['delivery_im_areas2_used'];
					$shop_config2['delivery_im_areas2_price']		= $vshop_config['delivery_im_areas2_price'];
				}
				
				$return_price	= 0;
				$return_price	+= deliveryImAreasPrice($shop_config2, $v, $info['address1'], $info['postcode']);

				while($row = $mysql->fetch_array()) {	
					$title = stripslashes($row['title']);
					if(preg_match("/{$title}/i", $info['address1'])) {			
						$return_price += $row['price'];
						break;
					}
				}

				$SUM_DELIVERY					+= $return_price;
				$VENDOR_DELIVERY_ARRAY[$v]		+= $return_price;
				$VENDOR_DELIVERY_ADD_ARRAY[$v]	= $return_price;
			}
		}
	}

	$SUM_TOTAL		= $SUM_PRICES + $SUM_DELIVERY;

	if($info['use_coupon'] > 0) {
		$sql		= "SELECT c_uid FROM mallRN_coupon WHERE uid = '{$info['coupon_uid']}' &&  id = '{$info['id']}'";
		$coupon_uid = $mysql->get_one($sql);
		$info['use_coupon'] = getCouponPrice($SUM_PRICES, $coupon_uid);

		$SUM_TOTAL -= $info['use_coupon'];

		if($info['use_coupon'] == 0) {
			$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$info['id']}' && uid = '{$info['coupon_uid']}'";
			$mysql->query($sql);
			$info['coupon_uid'] = 0;
		}
	}

	if($info['use_mileage'] > 0) {
		$mileage_gap = 0;
		if($SUM_TOTAL < $info['use_mileage']) {
			$mileage_gap = $info['use_mileage'] - $SUM_TOTAL;
			$info['use_mileage'] = $SUM_TOTAL;
		}
		$SUM_TOTAL -= $info['use_mileage'];
		
		if($mileage_gap > 0) {
			saveMileageChange($info['id'], $mileage_gap, "주문취소에 따른 마일리지 사용 차액 환원", $order_num);
		}
	}
	############################### 주문금액 체크 ###################################
	
	######################## 주문 정보 업데이트  #########################
	if($CANCEL_TOTAL > 0) $SUM_TOTAL += $CANCEL_TOTAL;

	$sql = "UPDATE {$table_name} SET 
				pay_total		= '{$SUM_TOTAL}',
				delivery_total	= '{$SUM_DELIVERY}',
				cancel_total	= '{$CANCEL_TOTAL}',
				use_mileage		= '{$info['use_mileage']}',
				use_coupon		= '{$info['use_coupon']}',
				coupon_uid		= '{$info['coupon_uid']}'
			WHERE 
				order_num = '{$order_num}'
		";
	$mysql->query($sql);
	######################## 주문 정보 업데이트  #########################

	######################## 배송비 정보 등록  #########################
	$sql = "DELETE FROM mallRN_order_delivery WHERE order_num = '{$order_num}'";
	$mysql->query($sql);

	foreach($VENDOR_DELIVERY_ARRAY as $k => $v) {
		if(!isset($VENDOR_DELIVERY_ADD_ARRAY[$k])) $VENDOR_DELIVERY_ADD_ARRAY[$k] = 0;
		$sql = "INSERT INTO mallRN_order_delivery SET order_num = '{$order_num}', vendor = '{$k}', price = '{$v}', adds = '{$VENDOR_DELIVERY_ADD_ARRAY[$k]}', info = '{$VENDOR_DELIVERY_INFO_ARRAY[$k]}', signdate = '{$signdate}'";
		$mysql->query($sql);
	}
	######################## 배송비 정보 등록  #########################	

}

/*
##############################################
    ::: 주문정보 부분수량 변경 처리 :::          
    사용방법 : orderGoodsQtyChange('주문번호');
##############################################
*/
function orderGoodsQtyChange($order_num, $ginfo, $proc_qty, $def_qty, $og_uid) {
	global $mysql;

	$where		= " && og_uid = '{$og_uid}'";
	$where2		= " && uid = '{$og_uid}'";

	######################## 주문상품 정보 등록 / 매출 등록  #########################
	$item_array		= array('vendor', 'vendor_delivery', 'commission', 'order_num', 'g_uid', 'g_cate', 'g_name', 'g_code', 'price', 'orig_price', 'qty', 'mileage', 'option', 'option_name', 'delivery_type', 'delivery_price', 'use_coupon', 'coupon_uid', 'discount', 'discount_info', 'delivery_info', 'status', 'status_date', 'reals', 'signdate');

	$ginfo['qty']		= $def_qty - $proc_qty;
	$ginfo['mileage']	= ($ginfo['mileage'] / $def_qty) * $ginfo['qty'];
	$ginfo['discount']	= ($ginfo['discount'] / $def_qty) * $ginfo['qty'];

	if($ginfo['delivery_type'] == '4') {
		$ginfo['delivery_price'] = 0;
	}
	
	$sql = "INSERT INTO mallRN_order_goods SET";
	foreach ($item_array as $k => $v) {
		if($k == count($item_array)-1) $sql .= " {$v} = '{$ginfo[$v]}'";
		else $sql .= " {$v} = '{$ginfo[$v]}',";
	}
	$mysql->query2($sql);
	
	$sql		= "SELECT uid FROM mallRN_order_goods WHERE order_num = '{$order_num}' ORDER BY uid DESC LIMIT 1";
	$og_uid2	= $mysql->get_one($sql);

	$sql		= "SELECT * FROM mallRN_order_sales WHERE order_num = '{$order_num}' {$where}";
	$sinfo		= $mysql->one_row($sql);
	
	$item_array	= array('id', 'vendor', 'order_num', 'og_uid', 'g_uid', 'g_cate', 'price', 'qty', 'commission', 'option', 'title', 'type', 'status', 'mobile', 'week', 'new', 'level', 'addr', 'pay_type', 'signdate', 'confirmation', 'confirm_date');

	$sinfo['og_uid']		= $og_uid2;
	$sinfo['price']			= $ginfo['price'] * $ginfo['qty'];
	$sinfo['qty']			= $ginfo['qty'];
	$sinfo['commission']	= ($ginfo['price'] - $ginfo['orig_price']) * $ginfo['qty'];

	$sql = "INSERT INTO mallRN_order_sales SET";
	foreach ($item_array as $k => $v) {
		if($k == count($item_array)-1) $sql .= " {$v} = '{$sinfo[$v]}'";
		else $sql .= " {$v} = '{$sinfo[$v]}',";
	}
	$mysql->query2($sql);
	
	$ginfo['delivery_price'] += $ginfo['delivery_add_price'];
	if($ginfo['delivery_price']) {
		if($ginfo['delivery_type'] == 5)	$ginfo['delivery_price'] = $ginfo['delivery_price'] * $ginfo['qty'];

		$sinfo['price']		= $ginfo['delivery_price'];			
		$sinfo['title']		= addslashes($ginfo['g_name'])." 개별배송비";
		$sinfo['commission'] = 0;
		$sinfo['type']		= 1;

		$sql = "INSERT INTO mallRN_order_sales SET";
		foreach ($item_array as $k => $v) {
			if($k == count($item_array)-1) $sql .= " {$v} = '{$sinfo[$v]}'";
			else $sql .= " {$v} = '{$sinfo[$v]}',";
		}
		$mysql->query2($sql);
	}
	######################## 주문상품 정보 등록 / 매출 등록  #########################

	######################## 주문상품 정보 수정 / 매출 수정  #########################
	$proc_mileage		= ($ginfo['mileage'] / $ginfo['qty']) * $proc_qty;
	$proc_discount		= ($ginfo['discount'] / $ginfo['qty']) * $proc_qty;
		
	$sql				= "UPDATE mallRN_order_goods SET qty = '{$proc_qty}', mileage = '{$proc_mileage}', discount = '{$proc_discount}' WHERE order_num = '{$order_num}' {$where2}";
	$mysql->query2($sql);

	$proc_price			= $ginfo['price'] * $proc_qty;
	$proc_commission	= $ginfo['price'] - ($ginfo['orig_price'] * $proc_qty);

	$sql				= "UPDATE mallRN_order_sales SET price = '{$proc_price}', qty = '{$proc_qty}', commission = '{$proc_commission}' WHERE order_num = '{$order_num}' && type = 0 {$where}";
	$mysql->query2($sql);

	if($ginfo['delivery_price'] && $ginfo['delivery_type'] == 5) {
		$proc_delivery_price = $ginfo['delivery_price'] * $proc_qty;

		$sql				= "UPDATE mallRN_order_sales SET price = '{$proc_delivery_price}' WHERE order_num = '{$order_num}' && type = 1 {$where}";
		$mysql->query2($sql);
	}
	######################## 주문상품 정보 수정 / 매출 수정  #########################
}


/*
##############################################
    ::: 조건부 배송비 취소/반품시으로 배송비 발생시 처리 :::          
    사용방법 : orderGoodsDelivery1Change('주문번호', '주문상품레코드') {
##############################################
*/

function orderGoodsDelivery1Change($delivery, $order_num, $og_uid) {
	global $mysql;

	if(!$delivery || !$order_num || !$og_uid) return;

	$signdate	= time();

	$sql		= "UPDATE mallRN_order_info SET pay_total = pay_total + {$delivery}, delivery_total = delivery_total + {$delivery} WHERE order_num = '{$order_num}'";
	$mysql->query3($sql);

	$sql		= "SELECT vendor_delivery, delivery_type FROM mallRN_order_goods WHERE order_num = '{$order_num}' && uid = '{$og_uid}'"; 
	$ginfo		= $mysql->one_row($sql);

	if($ginfo['delivery_type'] == 3) return;

	$sql		= "UPDATE mallRN_order_delivery SET price = price + {$delivery} WHERE order_num = '{$order_num}' && vendor = '{$ginfo['vendor_delivery']}'";
	$mysql->query3($sql);

	$sql		= "SELECT * FROM mallRN_order_sales WHERE order_num = '{$order_num}' && og_uid = '{$og_uid}' && type = 0";
	$sinfo		= $mysql->one_row($sql);
	
	$values = array();
	$values['id']			= $sinfo['id'];
	$values['vendor']		= $sinfo['vendor'];
	$values['order_num']	= $order_num;
	$values['og_uid']		= 0;
	$values['g_uid']		= 0;
	$values['g_cate']		= 0;
	$values['price']		= $delivery;	
	$values['qty']			= 0;
	$values['commission']	= 0;
	$values['option']		= '';

	if($ginfo['vendor_delivery'])	$values['title'] = "취소/반품으로 인한 입점사배송비 발생";
	else							$values['title'] = "취소/반품으로 인한 본사배송비 발생";

	$values['type']			= 1;
	$values['status']		= 0;	
	$values['mobile']		= $sinfo['mobile'];
	$values['week']			= date('w');
	$values['new']			= $sinfo['new'];
	$values['level']		= $sinfo['level'];
	$values['addr']			= $sinfo['addr'];
	$values['pay_type']		= $sinfo['pay_type'];
	$values['signdate']		= time();

	$values['confirmation'] = 1;
	$values['confirm_date']	= time();
	
	orderSalesInsert($values);

}

/*
##############################################
    ::: 배송비 별도책정 고정상품 취소/반품시 처리 :::          
    사용방법 : orderGoodsDelivery4Change('주문번호', '주문상품레코드') {
##############################################
*/

function orderGoodsDelivery4Change($order_num, $data) {
	global $mysql;

	$sql = "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && status != 8 && status != 9 && g_uid = '{$data['g_uid']}' && delivery_type = 4 && delivery_price = 0";
	if($mysql->get_one($sql) > 0) {
		$sql		= "SELECT uid FROM mallRN_order_goods WHERE order_num = '{$order_num}' && status != 8 && status != 9 && g_uid = '{$data['g_uid']}' && delivery_type = 4 && delivery_price = 0 ORDER BY uid DESC LIMIT 1";
		$mod_uid	= $mysql->get_one($sql);
		
		$sql = "UPDATE mallRN_order_goods SET delivery_price = '{$data['delivery_price']}', delivery_add_price = '{$data['delivery_add_price']}' WHERE uid = '{$mod_uid}'";
		$mysql->query3($sql);

		$sql = "UPDATE mallRN_order_goods SET delivery_price = 0, delivery_add_price = 0 WHERE uid = '{$data['uid']}'";
		$mysql->query3($sql);
	}
}


/*
##############################################
    ::: 쿠폰발급 :::          
    사용방법 : couponIssuance ('쿠폰고유값', '회원아이디')
##############################################
*/
function couponIssuance($c_uid, $id, $g_uid = 0){
	global $mysql;
	
	if(!$c_uid || !$id) return false;

	$sql = "SELECT count(*) FROM mallRN_member WHERE id = '{$id}'";
	if($mysql->get_one($sql) == 0) return false;

	$sql = "SELECT * FROM mallRN_coupon_manager WHERE uid = '{$c_uid}'";
	if(!$data = $mysql->one_row($sql)) return false;

	if($data['use_type'] == 0)	$e_date = $data['use_e_date'];
	else						$e_date = date("Y-m-d 23:59:59", strtotime('+'.$data['use_day'].' DAY', time()));

	if($e_date < date("Y-m-d H:i:s")) return false;

	$signdate	= time();

	$sql = "INSERT INTO mallRN_coupon SET
				c_uid		= '{$c_uid}',
				g_uid		= '{$g_uid}',
				id			= '{$id}',
				e_date		= '{$e_date}',
				signdate	= '{$signdate}'
			";
	$mysql->query3($sql);

	return true;
}


/*
##############################################
    ::: 마일리지갱신 :::          
    사용방법 : mileageChange ('회원아이디')
##############################################
*/
function mileageChange($id){
	global $mysql;
	
	if(!$id) return false;

	$sleep	= "";
	$sql = "SELECT count(*) FROM mallRN_member WHERE id = '{$id}'";
	if($mysql->get_one($sql) == 0) {
		$sql = "SELECT count(*) FROM mallRN_member_sleep WHERE id = '{$id}'";
		if($mysql->get_one($sql) == 0) return false;
		$sleep	= "_sleep";
	}

	$sql			= "SELECT SUM(mileage) as mileage, SUM(use_mileage) as use_mileage FROM mallRN_mileage WHERE id = '{$id}'";
	$data			= $mysql->one_row($sql);

	$mileage		= $data['mileage'] - $data['use_mileage'];

	$sql			= "UPDATE mallRN_member{$sleep} SET mileage = '{$mileage}' WHERE id = '{$id}'";
	$mysql->query3($sql);
	
	return $mileage;
}


/*
##############################################
    ::: 마일리지사용 :::          
    사용방법 : useMileageChange ('회원아이디', '사용금액')
##############################################
*/
function useMileageChange($id, $mileage, $title, $order_num = ""){
	global $mysql;
	
	if(!$id || !$mileage) return false;

	$sql = "SELECT * FROM mallRN_mileage WHERE id = '{$id}' && expired_use = 1 && expired = 0 && mileage > 0 ORDER BY expired_date ASC";
	$mysql->query2($sql);

	$tmp_mileage = $mileage;
	
	while($row = $mysql->fetch_array(2)) { 
		$able_mileage = $row['mileage'] - $row['proc_mileage'];
		if($able_mileage > 0) {
			$use_mileage = $able_mileage - $tmp_mileage;
			if($use_mileage > 0) {
				$sql = "UPDATE mallRN_mileage SET proc_mileage = proc_mileage + {$tmp_mileage} WHERE uid = '{$row['uid']}'";
				$mysql->query3($sql);
				break;
			}
			else {
				$sql = "UPDATE mallRN_mileage SET proc_mileage = proc_mileage + {$able_mileage} WHERE uid = '{$row['uid']}'";
				$mysql->query3($sql);
				$tmp_mileage -= $able_mileage;
			}
		}
	}

	$signdate = time();

	$sql = "INSERT INTO mallRN_mileage SET id = '{$id}', content = '{$title}', use_mileage = '{$mileage}', order_num = '{$order_num}', signdate = '{$signdate}'";
	$mysql->query2($sql);

	mileageChange($id);

	return;
}

/*
##############################################
    ::: 마일리지적립 :::          
    사용방법 : saveMileageChange ('회원아이디', '적립금액', '내역', '주문번호', '주문상품번호')
##############################################
*/
function saveMileageChange($id, $mileage, $content = "", $order_num = "", $goods_uid = ""){
	global $mysql;
	
	if(!$id || !$mileage) return false;

	$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
	$member_config	= $mysql->one_row($sql);

	$validity_yn	= $member_config['member_mileage_validity_yn'];

	if($validity_yn == 'Y') {
		$mileage_validity			= $member_config['member_mileage_validity'];
		$mileage_validity_type		= $member_config['member_mileage_validity_type'];
		$mileage_validity_type_arr	= array("D" => "DAY", "M" => "MONTH", "Y" => "YEAR");
		
		$expired_date	= date("Y-m-d", strtotime("+{$mileage_validity} {$mileage_validity_type_arr[$mileage_validity_type]}", time()));
		$expired_use	= 1;
	}
	else {
		$expired_date	= "1000-01-01";
		$expired_use	= 0;
	}

	$signdate			= time();

	$item_array = array('id', 'content', 'mileage', 'expired_use', 'expired_date', 'order_num', 'goods_uid', 'signdate');

	######################## 마일리지 등록  #########################		
	$sql = "INSERT INTO mallRN_mileage SET";
	foreach ($item_array as $k => $v) {
		if($k == count($item_array)-1) $sql .= " {$v} = '{$$v}'";
		else $sql .= " {$v} = '{$$v}',";
	}
	$mysql->query3($sql);

	mileageChange($id);
	######################## 마일리지 등록  #########################

	return;
}


/*
##############################################
    ::: 택배사명 받기 :::          
    사용방법 : getDeliveryName('택배사고유값')
	return array('택배사명', '배송조회주소');
##############################################
*/
function getDeliveryInfo($num) {
	global $mysql, $shop_config;
	
	if(!$shop_config) {
		$sql			= "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
		$delivery_info	= $mysql->get_one($sql);
	}
	else $delivery_info = $shop_config['delivery_info'];
	
	$delivery_info = explode("|*|", $delivery_info);
	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

			$delivery_info2 = explode("|", $delivery_info[$i]);

			if($delivery_info2[0] == $num) return array($delivery_info2[1], $delivery_info2[2]);
		}
	}

	return array('', '');
}


/*
##############################################
    ::: 마진률 구하기 :::          
    사용방법 : getMarginPer('pc 마진', '모바일 마진', '총마진', '조건절')
	리턴값 : array('pc 마진률', '모바일 마진률', '총마진률');
	$margin_value = getMarginPer($p_margin, $m_margin, $margin, $where); 

##############################################
*/

function getMarginPer($p_margin, $m_margin, $margin, $where) {
	global $mysql;
	
	$sql		= "SELECT SUM(IF(mobile = 'N', IF(status = 0, price, -price), 0)) as p_total, SUM(IF(mobile = 'Y', IF(status = 0, price, -price), 0)) as m_total FROM mallRN_order_sales WHERE {$where}";
	$data		= $mysql->one_row($sql);
	$p_total	= $data['p_total'] ? $data['p_total'] : 0;
	$m_total	= $data['m_total'] ? $data['m_total'] : 0;			
	$total		= $p_total + $m_total;

	if($p_margin != 0 && $p_total != 0) {
		$p_margin_per	= number_format(($p_margin / $p_total) * 100, 2);
		if($p_margin < 0 && $p_total < 0) $p_margin_per = "-".$p_margin_per;
	}
	else $p_margin_per = 0;
	if($m_margin != 0 && $m_total != 0) {
		$m_margin_per	= number_format(($m_margin / $m_total) * 100, 2);
		if($m_margin < 0 && $m_total < 0) $m_margin_per = "-".$m_margin_per;
	}
	else $m_margin_per = 0;
	if($margin != 0 && $total != 0) {
		$margin_per		= number_format(($margin / $total) * 100, 2);
		if($margin < 0 && $total < 0) $margin_per = "-".$margin_per;
	}
	else $margin_per = 0;

	return array($p_margin_per, $m_margin_per, $margin_per);
}


/*
##############################################
    ::: 상품발송 메일 보내기 :::          
    사용방법 : status3Mail('주문번호', '구매상품고유값', '교환')
##############################################
*/
function status3Mail($order_num, $uid, $exchange = ""){
	global $mysql;

	$sql			= "SELECT basic_name, delivery_info FROM mallRN_configuration WHERE uid = 1";
	$shop_config	= $mysql->one_row($sql);

	$sql			= "SELECT content, send FROM mallRN_auto_mail WHERE type = 'delivery'";
	$data			= $mysql->one_row($sql);

	if($data['send'] == 1) {

		$sql				= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && uid = '{$uid}'";
		$info				= $mysql->one_row($sql);

		$name2				= stripslashes($info['name2']);
		$name2				= mb_substr($name2, 0, 1, 'utf-8')." * ".mb_substr($name2, 2, mb_strlen($name2, 'utf-8'), 'utf-8');
		$RECIEVER_INFO		= $name2." / ".substr($info['cell2'], 0, -4)."**** / ".stripslashes($info['postcode'])." ".stripslashes($info['address1'])." ******";

		$sql				= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}'";
		$goods				= $mysql->one_row($sql);

		$goods_delivery		= explode("|", $goods['delivery_info']);

		$delivery_info		= getDeliveryInfo($goods_delivery[0]);
		$DELIVERY_LINK		= $delivery_info[1].$goods_delivery[1];

		$content			= stripslashes($data['content']);
		$content			= str_replace("{ORDER_DATE}",			date("m월 d일", $info['signdate']),			$content);
		$content			= str_replace("{GOODS_NAME}",			stripslashes($goods['g_name']),				$content);
		$content			= str_replace("{DELIVERY_DATE}",		date("m월 d일"),								$content);
		$content			= str_replace("{DELIVERY_NAME}",		$delivery_info[0],							$content);
		$content			= str_replace("{DELIVERY_NUM}",			$goods_delivery[1],							$content);
		$content			= str_replace("{DELIVERY_LINK}",		$DELIVERY_LINK,								$content);
		$content			= str_replace("{RECIEVER_INFO}",		$RECIEVER_INFO,								$content);
		 
		mallMailSend($info['email'], "[".stripslashes($shop_config['basic_name'])."] {$name2}님, 주문하신 상품이 ".date("m월 d일")."에 {$exchange}발송되었습니다.", $content);			
	}
}

/*
##############################################
    ::: 상품발송 문자 보내기 :::          
    사용방법 : status3Sms($sms_send_array, $sms_cnt_array)
##############################################
*/
function status3Sms($sms_send_array, $sms_cnt_array){
	$tmps_order_num		= "";
	foreach($sms_send_array as $k => $v) {
		if($tmps_order_num != $v[0]) {
			
			if($v[5]) {
				if($sms_cnt_array[$v[0]] > 1)	$add_name	= "외 ".($sms_cnt_array[$v[0]] - 1)."건";
				else							$add_name	= "";					

				$replace_code_array	= array('ORDER_NAME' => $v[1], 'GOODS_NAME' => $v[2].$add_name, 'DELIVERY_NAME' => $v[3], 'DELIVERY_NUM' => $v[4]);
				mallSmsAuto('delivery', $v[5], $replace_code_array);
			}
		}

		$tmps_order_num = $v[0];
	}	
}

/*
##############################################
    ::: 메일 발송 :::          
    사용방법 : mallMailSend('이메일', '제목')
##############################################
*/
function mallMailSend($email, $subject, $content){
	global $mysql, $shop_config;
	
	if(!$shop_config) {
		$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 1";
		$shop_config	= $mysql->one_row($sql);		
	}

	if(!$email || !$subject || !$shop_config['basic_email']) return false;
	
	$sql		= "SELECT content FROM mallRN_auto_mail WHERE type = 'common'";
	$mail_form	= stripslashes($mysql->get_one($sql));
	$mail_form	= str_replace("{CONTENT}",	$content,																						$mail_form);
	$mail_form	= str_replace("{CSURL}",	ABSOLUTE_PATH_SHOP.'index.php?channel=cs',														$mail_form);
	$mail_form	= str_replace("{COMPANY}",	stripslashes($shop_config['comp_name']),														$mail_form);
	$mail_form	= str_replace("{OWNER}",	stripslashes($shop_config['comp_owner']),														$mail_form);
	$mail_form	= str_replace("{COMPNUM}",	stripslashes($shop_config['comp_license_no1']),													$mail_form);
	$mail_form	= str_replace("{ADDRESS}",	stripslashes($shop_config['comp_address1'])." ".stripslashes($shop_config['comp_address2']),	$mail_form);
	$mail_form	= str_replace("{TEL}",		stripslashes($shop_config['comp_tel']),															$mail_form);
	$mail_form	= str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),														$mail_form);

	require_once(DEFAULT_PATH.PATH_PHPMAILER.'/PHPMailerAutoload.php');

	$mail = new PHPMailer(); // defaults to using php "mail()"
    $mail->CharSet	= 'UTF-8';
    $mail->From		= $shop_config['basic_email'];
    $mail->FromName = stripslashes($shop_config['basic_name']);
    $mail->Subject	= $subject;
    $mail->AltBody	= ""; // optional, comment out and test
    $mail->msgHTML($mail_form);
    $mail->addAddress($email);
   
    return $mail->send();
	
}

/*
##############################################
    ::: SMS 알림 :::          
    사용방법 : mallSmsAuto('코드명', '수신번호', '치환코드')
##############################################
*/
function mallSmsAuto($code, $cell, $replace_code_array){
	global $mysql, $shop_config;
	
	if(!$code || !$cell) return false;

	if(!$shop_config) {
		$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 1";
		$shop_config	= $mysql->one_row($sql);		
	}

	if($shop_config['sms_yn'] != 'Y') return false;

	$coolsms_key	= $shop_config['sms_key'];
	$coolsms_secret = $shop_config['sms_secret'];
	$coolsms_pfid	= $shop_config['sms_pfid'];
	$calling_number	= str_replace("-", "", $shop_config['sms_calling_number']);
	$cell			= str_replace("-", "", $cell);

	if(!$coolsms_key || !$coolsms_secret || !$calling_number) return false;
	
	$sql = "SELECT * FROM mallRN_sms_auto WHERE code = '{$code}'";
	if(!$data = $mysql->one_row($sql)) return false;
	
	if($data['type'] == 0 || $data['type'] == 1) {
		if($data['ck_message1'] == 1) {
			$data['message1']	= str_replace("{SHOPNAME}",		stripslashes($shop_config['basic_name']),	$data['message1']);
			$variables = array();
			$variables['#{SHOPNAME}'] = stripslashes($shop_config['basic_name']);
			
			foreach($replace_code_array as $k => $v) {
				$data['message1']	= str_replace("{".$k."}", $v, $data['message1']);
				$variables["#{".$k."}"] = $v;
			}

			if($coolsms_pfid && $data['template_id1']) {
				$kakaoOptions = array();
				$kakaoOptions['pfId']		= $coolsms_pfid;
				$kakaoOptions['templateId'] = $data['template_id1'];
				$kakaoOptions['variables']	= $variables;
				$messages = array("to" => $cell, "from" => $calling_number, "text" => $data['message1'], "type" => "ATA", "kakaoOptions" => $kakaoOptions);
				unset($kakaoOptions, $variables);
			}
			else {
				$messages = array("to" => $cell, "from" => $calling_number, "text" => $data['message1']);
			}
		
			mallSmsSend($messages);
		}
	}
	
	if($data['type'] == 0 || $data['type'] == 2) {
		if($data['ck_message2'] == 1) {
			$data['message2']	= str_replace("{SHOPNAME}",		stripslashes($shop_config['basic_name']),	$data['message2']);
			$variables = array();
			$variables['#{SHOPNAME}'] = stripslashes($shop_config['basic_name']);

			foreach($replace_code_array as $k => $v) {
				$data['message2']	= str_replace("{".$k."}", $v, $data['message2']);
				$variables["#{".$k."}"] = $v;
			}

			for($i = 1; $i < 4; $i ++) {
				if($shop_config['sms_admin_number'.$i]) {
					$admin_number	= str_replace("-", "", $shop_config['sms_admin_number'.$i]);
					
					if($coolsms_pfid && $data['template_id2']) {
						$kakaoOptions = array();
						$kakaoOptions['pfId']		= $coolsms_pfid;
						$kakaoOptions['templateId'] = $data['template_id2'];
						$kakaoOptions['variables']	= $variables;
						$messages = array("to" => $admin_number, "from" => $calling_number, "text" => $data['message2'], "type" => "ATA", "kakaoOptions" => $kakaoOptions);
						unset($kakaoOptions);
					}
					else {
						$messages = array("to" => $admin_number, "from" => $calling_number, "text" => $data['message2']);
					}				
					
					mallSmsSend($messages);
				}
			}
		}
	}	
}

function mallSmsSend($messages){
	global $mysql, $shop_config, $config_sms;

	if(!$messages) return;

	if(!$shop_config) {
		$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 1";
		$shop_config	= $mysql->one_row($sql);		
	}

	if($shop_config['sms_yn'] != 'Y') return false;

	$coolsms_key	= $shop_config['sms_key'];
	$coolsms_secret = $shop_config['sms_secret'];
	$calling_number	= str_replace("-", "", $shop_config['sms_calling_number']);
	
	if(!$coolsms_key || !$coolsms_secret || !$calling_number) return false;
	
	require_once(DEFAULT_PATH.PATH_COOLSMS.'/lib/message.php');	

	$rtn = send_messages($messages);

	$signdate	= time();

	if(@$rtn->type == 'LMS')	$type = "1";
	else						$type = "0";

	$sql = "INSERT INTO mallRN_sms_list SET
				cell		= '{$messages['to']}',
				message		= '{$messages['text']}',
				type		= '{$type}',
				groupId		= '".@$rtn->groupId."',
				messageId	= '".@$rtn->messageId."',
				accountId	= '".@$rtn->accountId."',
				result		= '".@$rtn->statusMessage."',
				result_code	= '".@$rtn->statusCode."',
				signdate	= '{$signdate}'
			";
	$mysql->query2($sql);

}


/*
##############################################
    ::: FCM 알림 :::          
    사용방법 : fcmSend('제목', '내용', '판매사아이디|판매사아이디|...')
##############################################
*/

function fcmSend($title, $body, $vendor = "") {
	global $mysql, $shop_config;

	if(!$shop_config) {
		$sql			= "SELECT push_server_key, mobile_icon FROM mallRN_configuration WHERE uid = 1";
		$shop_config	= $mysql->one_row($sql);
	}
	
	$fcm_server_key	= $shop_config['push_server_key'];		

	if(!$fcm_server_key) return;
	
	if($shop_config['mobile_icon']) $basic_image = ABSOLUTE_PATH_SHOP."image/mobile/{$shop_config['mobile_icon']}";		
	else							$basic_image = "";
	
	$url			= 'https://fcm.googleapis.com/fcm/send';
    $headers		= array('Authorization:key='.$fcm_server_key,'Content-Type: application/json');
	
	$sql			= "SELECT token_pc, token_mobile FROM mallRN_admin_configuration WHERE push_yn = 'Y'";
	$mysql->query3($sql);

	while($row = $mysql->fetch_array(3)) {
		if($row['token_pc']) {
			$fields	= array('to' => $row['token_pc'], 'notification' => array("title" => $title, "body" => $body, "icon" => $basic_image, "click_action" => ABSOLUTE_PATH_SHOP));
			fcmSendCurl($url, $headers, $fields);
		}

		if($row['token_mobile']) {
			$fields	= array('to' => $row['token_mobile'], 'notification' => array("title" => $title, "body" => $body, "icon" => $basic_image, "click_action" => ABSOLUTE_PATH_SHOP));
			fcmSendCurl($url, $headers, $fields);
		}		
	}

	if($vendor) {
		$vendor = explode("', '", $vendor);
		$sql		= "SELECT token_pc, token_mobile FROM mallRN_vendor_configuration WHERE push_yn = 'Y' && vendor IN('{$vendor}')";
		$mysql->query3($sql);

		while($row = $mysql->fetch_array(3)) {
			if($row['token_pc']) {
				$fields	= array('to' => $row['token_pc'], 'notification' => array("title" => $title, "body" => $body, "icon" => $basic_image, "click_action" => ABSOLUTE_PATH_SHOP));
				fcmSendCurl($url, $headers, $fields);
			}

			if($row['token_mobile']) {
				$fields	= array('to' => $row['token_mobile'], 'notification' => array("title" => $title, "body" => $body, "icon" => $basic_image, "click_action" => ABSOLUTE_PATH_SHOP));
				fcmSendCurl($url, $headers, $fields);
			}		
		}
	}
}

function fcmSendCurl($url, $headers, $fields) {
	if(!$url || !$headers || !$fields) return;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	if ($result === FALSE) {
		die('Curl failed: ' . curl_error($ch));
	}
	curl_close($ch);
}


function deliveryImAreasPrice($data, $vendor, $address, $postcode) {
	global $mysql;
	
	$return_price = 0;

	if($data['delivery_im_areas1_used'] == 1) {
		if(preg_match("/제주특별자치도/i", $address)) $return_price += $data['delivery_im_areas1_price'];
	}	
	if($data['delivery_im_areas2_used'] == 1) {
		$sql = "SELECT * FROM mallRN_im_areas WHERE (base = 0 || (base = 1 && vendor = '{$vendor}')) && postcode = '{$postcode}'";
		if($data2 = $mysql->one_row($sql)) {
			$sql = "SELECT count(*) FROM mallRN_im_areas_except WHERE vendor = '{$vendor}' && p_uid = '{$data2['uid']}'";
			if($mysql->get_one($sql) == 0) $return_price += $data['delivery_im_areas2_price'];
		}
	}	

	return $return_price;
}

?>