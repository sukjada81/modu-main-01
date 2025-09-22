<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function		
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/		
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

$s_date				= checkPostVar('s_date');
$e_date				= checkPostVar('e_date');
$signdate			= time();
$items				= stripslashes(checkPostVar('items'));
$items				= json_decode($items);
$items				= objectToArray($items);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$s_date || !$e_date || count($items) == 0) json_error_msg('필수 정보가 넘어오지 못했습니다.');

for($i = 0, $cnt = count($items); $i < $cnt; $i ++) {
	$id		= $items[$i]['id'];
	$sum	= str_replace(",", "", $items[$i]['sum']);
	$price1	= str_replace(",", "", $items[$i]['price1']);
	$price2	= str_replace(",", "", $items[$i]['price2']);
	$price3	= str_replace(",", "", $items[$i]['price3']);

	$sql		= "SELECT id, comp_name, bank_name, bank_num, bank_owner FROM mallRN_vendor WHERE id = '{$id}'";
	if(!$data = $mysql->one_row($sql)) json_error_msg("{$id} 판매사가 존재하지 않거나 삭제 되었습니다.");

	$vendor			= $id;
	$vendor_name	= $data['comp_name'];
	$bank_name		= $data['bank_name'];
	$bank_num		= $data['bank_num'];
	$bank_owner		= $data['bank_owner'];
	
	$item_array	= array('s_date', 'e_date', 'sum', 'price1', 'price2', 'price3', 'bank_name', 'bank_num', 'bank_owner');
	
	$sql = "SELECT count(*) FROM mallRN_sales_calculate WHERE vendor = '{$id}' && s_date = '{$s_date}' && e_date = '{$e_date}' && status = 0";
	if($mysql->get_one($sql) == 0) {
		array_push($item_array, 'vendor', 'vendor_name', 'signdate');
		
		$sql = "INSERT INTO mallRN_sales_calculate SET";
		foreach ($item_array as $k => $v) {			
			if($k==count($item_array)-1) $sql .= " {$v} = '{$$v}'";
			else $sql .= " {$v} = '{$$v}',";
		}
	}
	else {
		$sql = "UPDATE mallRN_sales_calculate SET";
		foreach ($item_array as $k => $v) {
			if($k==count($item_array)-1) $sql .= " {$v} = '{$$v}'";
			else $sql .= " {$v} = '{$$v}',";
		}
		$sql .= "WHERE vendor = '{$id}' && s_date = '{$s_date}' && e_date = '{$e_date}' && status = 0";
	}
	$mysql->query($sql);

	$sql = "UPDATE mallRN_order_sales SET adjustment = 1 WHERE from_unixtime(confirm_date) BETWEEN '{$s_date}' AND '{$e_date} 23:59:59' && vendor = '{$id}' && type < 2";
	$mysql->query($sql);
}

echo json_encode(array('success' => '1'));

?>