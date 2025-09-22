<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();

$uid		= checkPostVar('uid');
$g_uid		= checkPostVar('g_uid');
$option		= checkPostVar('option');
$qty		= checkPostVar('qty');

if(!$uid || !$g_uid || !$option || !$qty) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql	= "SELECT cate, vendor, sale_use, option_use, qty_type, qty, limit_qty FROM mallRN_goods WHERE uid = '{$g_uid}'";
$data	= $mysql->one_row($sql);

if(!$data) json_error_msg('해당 상품이 삭제되었거나 존재하지 않습니다.');

if($data['sale_use'] == 0 || ($data['option_use'] == 0 && $data['qty_type'] == 0 && $data['qty'] < 1)) json_error_msg('해당 상품이 품절되었습니다.');

$sql		= "SELECT uid, qty FROM mallRN_cart WHERE cart_id = '{$cart_id}' && g_uid = '{$g_uid}' && option = '{$option}'";
$data2		= $mysql->one_row($sql);

if($data['limit_qty'] > 0) {
	if(!$my_id) json_error_msg('회원만 구매 가능 한 상품 입니다.');

	$able_qty = $data['limit_qty'] - getOrderQty($g_uid) + $data2['qty'];
	if($qty > $able_qty) json_error_msg('구매제한 수량을 초과 했습니다.');
}

if($data2['uid'] == $uid)	$prev_qty = 0;
else						$prev_qty	= $data2['qty'];

$sql	= "SELECT value, qty_type, qty FROM mallRN_goods_option WHERE uid = '{$option}'";
$odata	= $mysql->one_row($sql);

if($prev_qty > 0) {
	$qty	= $qty + $prev_qty;

	if($odata['qty_type'] == 0 && $odata['qty'] < $qty) {
		echo json_encode(array('error' => "선택옵션({$odata['value']})이 재고량 {$odata['qty']}개를 초과 했습니다."));
		exit;
	}
	
	$sql = "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";		
	$mysql->query($sql);

	$sql = "UPDATE mallRN_cart SET qty = '{$qty}' WHERE cart_id = '{$cart_id}' && g_uid = '{$g_uid}' && option = '{$option}'";
	$mysql->query($sql);

	$my_array[] = ["ok" => 1, "uid" => $data2['uid'], "qty" => $qty];
}
else {
	
	if($odata['qty_type'] == 0 && $odata['qty'] < $qty) {
		echo json_encode(array('error' => "선택옵션({$odata['value']})이 재고량 {$odata['qty']}개를 초과 했습니다."));
		exit;
	}

	$sql = "UPDATE mallRN_cart SET option = '{$option}', qty = '{$qty}' WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";		
	$mysql->query($sql);

	if($data2['uid'] == $uid)	$my_array[] = ["ok" => 3, "qty" => $qty];
	else						$my_array[] = ["ok" => 2, "qty" => $qty];
}
	
echo json_encode($my_array);

?>