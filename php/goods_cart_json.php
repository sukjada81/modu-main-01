<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array = array();

$uid			= checkPostVar('uid');

if(!$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql	= "SELECT cate, vendor, sale_use, option_use, qty_type, qty, limit_qty FROM mallRN_goods WHERE uid = '{$uid}'";
$data	= $mysql->one_row($sql);

if(!$data) json_error_msg('해당 상품이 삭제되었거나 존재하지 않습니다.');

if($data['sale_use'] == 0 || ($data['option_use'] == 0 && $data['qty_type'] == 0 && $data['qty'] < 1)) json_error_msg('해당 상품이 품절되었습니다.');

checkCateAccess($data['cate'], 1);

$_POST['vendor_delivery'] = "";
if($data['vendor']) {
	$sql	= "SELECT delivery_type, sell FROM mallRN_vendor WHERE id = '{$data['vendor']}'";
	$vdata	= $mysql->one_row($sql);

	if($vdata['sell'] != 'A') json_error_msg('해당 상품이 품절되었습니다.');

	if($vdata['delivery_type'] == 0) $_POST['vendor_delivery'] = $data['vendor'];
}

$option				= checkPostVar('option');
$direct				= checkPostVar('direct');
$start				= checkPostVar('start');
$_POST['qty']		= checkPostVar('qty');
if(!$_POST['qty'])	$_POST['qty'] = 1;
$_POST['vendor']	= $data['vendor'];
$_POST['g_uid']		= $uid;
$_POST['g_cate']	= $data['cate'];
$_POST['cart_id']	= $cart_id;
$_POST['selects']	= 1;
$_POST['signdate']	= time();

$item_array		= array('vendor', 'vendor_delivery', 'cart_id', 'g_uid', 'g_cate', 'qty', 'option', 'direct', 'selects', 'signdate');
$item_default	= array('direct');

if($direct == 1 && $start == 1) {
	$sql = "DELETE FROM mallRN_cart WHERE direct = '1' AND cart_id = '{$cart_id}'";
	$mysql->query($sql);
}

$sql		= "SELECT qty FROM mallRN_cart WHERE cart_id = '{$cart_id}' AND g_uid = '{$uid}' AND `option` = '{$_POST['option']}'";
$prev_qty	= $mysql->get_one($sql);
$prev_msg	= "";

if(!$prev_qty) $prev_qty = 0;	
else $prev_msg = "기장바구니 상품 포함";

$check_qty	= $_POST['qty'] + $prev_qty;

if($data['limit_qty'] > 0) {
	if(!$my_id) json_error_msg('회원만 구매 가능 한 상품 입니다.');

	$able_qty = $data['limit_qty'] - getOrderQty($uid) + $prev_qty;
	if($check_qty > $able_qty) json_error_msg('구매제한 수량을 초과 했습니다.');
}

if($option) {
	$sql	= "SELECT value, qty_type, qty FROM mallRN_goods_option WHERE uid = '{$option}'";
	$odata	= $mysql->one_row($sql);

	if($odata['qty_type'] == 0 && $odata['qty'] < $check_qty) json_error_msg("선택옵션({$odata['value']})이 {$prev_msg} 재고량 {$odata['qty']}개를 초과 했습니다.");
}
else {
	if($data['qty_type'] == 0 && $data['qty'] < $check_qty) json_error_msg("해당 상품이 {$prev_msg} 재고량 {$data['qty']}개를 초과 했습니다.");
}

if($prev_qty == 0) {
	$sql = "INSERT INTO mallRN_cart SET";
	// foreach ($item_array as $k => $v) {
	// 	if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
	// 	else $_POST[$v] = checkPostVar($v);

	// 	if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
	// 	else $sql .= " {$v} = '{$_POST[$v]}',";
	// }
	foreach ($item_array as $k => $v) {
		if (in_array($v, $item_default)) {
			$_POST[$v] = checkPostVar($v, 0);
		} else {
			$_POST[$v] = checkPostVar($v);
		}

		// 컬럼명을 항상 백틱(`)으로 감싼다
		if ($k == count($item_array) - 1) {
			$sql .= " `{$v}` = '{$_POST[$v]}'";
		} else {
			$sql .= " `{$v}` = '{$_POST[$v]}',";
		}
	}
	$mysql->query($sql);
	$my_array[] = ["ok" => 1, "option" => $option, "opt_name" => checkPostVar('opt_name')];
}
else {
	if($direct == 1)	$add_query = ", direct = 1";
	else				$add_query = "";

	$sql = "UPDATE mallRN_cart SET qty = qty + {$_POST['qty']}, selects = 1 {$add_query} WHERE cart_id = '{$cart_id}' AND g_uid = '{$uid}' AND `option` = '{$_POST['option']}'";		
	$mysql->query($sql);
	$my_array[] = ["ok" => 2, "qty" => $check_qty, "opt_name" => checkPostVar('opt_name')];
}

$sql = "UPDATE mallRN_cart SET contact = 0 WHERE cart_id = '{$cart_id}'";
$mysql->query($sql);

$sql = "UPDATE mallRN_cart SET contact = 1 WHERE cart_id = '{$cart_id}' AND vendor_delivery = '{$_POST['vendor_delivery']}'";
$mysql->query($sql);
	
echo json_encode($my_array);

?>