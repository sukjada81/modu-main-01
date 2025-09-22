<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();
$mode		= add_escape_re_string($_POST['mode']);

if($mode == 'selects') {
	$item		= checkPostVar('item');

	if($item)	$item_array	= explode(",", $item);
	else		$item_array	= array();

	$sql = "SELECT uid FROM mallRN_cart WHERE cart_id = '{$cart_id}'";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){ 
		if(in_array($row['uid'], $item_array))	$selected = 1;
		else								$selected = 0;	

		$sql = "UPDATE mallRN_cart SET selects = '{$selected}' WHERE uid = '{$row['uid']}'";	
		$mysql->query2($sql);
	}
}
else if($mode == 'direct') {
	
	$uid		= checkPostVar('uid');
	
	if(!$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

	$sql = "UPDATE mallRN_cart SET direct = '0' WHERE cart_id = '{$cart_id}'";
	$mysql->query($sql);

	$sql		= "UPDATE mallRN_cart SET direct = 1 WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";
	$mysql->query($sql);	
}
else if($mode == 'delete') {
	
	$uid		= checkPostVar('uid');
	
	if(!$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

	if($uid == 'all')	$sql = "DELETE FROM mallRN_cart WHERE  cart_id = '{$cart_id}'";
	else				$sql = "DELETE FROM mallRN_cart WHERE  cart_id = '{$cart_id}' && uid IN ({$uid})";
	$mysql->query($sql);
	
}
else if($mode == 'qtys') {
	$uid		= checkPostVar('uid');
	$qty		= checkPostVar('qty');

	if(!$uid || !$qty) json_error_msg('필수 정보가 넘어오지 못했습니다.');

	$sql	= "SELECT g_uid, option, qty FROM mallRN_cart WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";
	$data	= $mysql->one_row($sql);

	if(!$data)	json_error_msg('해당 장바구니 상품이 없거나 삭제 되었습니다.');

	$sql	= "SELECT qty_type, qty, limit_qty FROM mallRN_goods WHERE uid = '{$data['g_uid']}'";
	$gdata	= $mysql->one_row($sql);

	if($gdata['limit_qty'] > 0) {
		if(!$my_id) json_error_msg('회원만 구매 가능 한 상품 입니다.');

		$able_qty = $gdata['limit_qty'] - getOrderQty($data['g_uid']) + $data['qty'];
		if($qty > $able_qty) {
			echo json_encode(array('error' => "구매제한 수량을 초과 했습니다.", "qty" => $data['qty']));
			exit;
		}
	}

	if($data['option']) {
		$sql	= "SELECT value, qty_type, qty FROM mallRN_goods_option WHERE uid = '{$data['option']}'";
		$odata	= $mysql->one_row($sql);

		if($odata['qty_type'] == 0 && $odata['qty'] < $qty) {
			if($odata['qty'] > 0) {
				$sql = "UPDATE mallRN_cart SET qty = '{$odata['qty']}' WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";
				$mysql->query($sql);
			}
			
			echo json_encode(array('error' => "해당 상품은 최대 {$odata['qty']}개까지 구매 가능 합니다.", "qty" => $odata['qty']));
			exit;
		}
	}
	else {		
		if($gdata['qty_type'] == 0 && $gdata['qty'] < $qty) {
			if($gdata['qty'] > 0) {
				$sql = "UPDATE mallRN_cart SET qty = '{$gdata['qty']}' WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";
				$mysql->query($sql);
			}

			echo json_encode(array('error' => "해당 상품은 최대 {$gdata['qty']}개까지 구매 가능 합니다.", "qty" => $gdata['qty']));
			exit;
		}
	}

	$sql = "UPDATE mallRN_cart SET qty = '{$qty}' WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";	
	$mysql->query($sql);
}

$my_array[] = ["ok" => 1];	
echo json_encode($my_array);

?>