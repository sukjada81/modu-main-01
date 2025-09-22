<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$direct		= checkPostVar('direct');
$vendor		= checkPostVar('vendor');
$address	= checkPostVar('address');
$postcode	= checkPostVar('postcode');
$signdate	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$address || !$postcode) json_error_msg('필수 정보가 넘어오지 못했습니다.');

if($direct) $where		= " && direct = 1";
else 		$where		= " && selects = 1";

$return_price	= 0;

$sql = "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' && vendor_delivery = '{$vendor}' {$where}";
$mysql->query($sql);

$sum_delivery_option		= array();
$ck = 0;
while($row = $mysql->fetch_array()){
	$sql	= "SELECT delivery_type, delivery_type_qty, delivery_im_areas1_used,delivery_im_areas1_price,delivery_im_areas2_used,delivery_im_areas2_price, option_use FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
	$data	= $mysql->one_row($sql);

	if($data['delivery_type'] != 1) {
		$return_price2 = deliveryImAreasPrice($data, $vendor, $address, $postcode);		
		if($data['delivery_type'] == 5) {
			 if($data['option_use'] == 1) {
				if(isset($sum_delivery_option[$row['g_uid']]) && $sum_delivery_option[$row['g_uid']] > 0) {
					$return_price2	= 0;					
				}
				else {
					$sql				= "SELECT SUM(qty) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && vendor_delivery = '{$vendor}' && g_uid = '{$row['g_uid']}' {$where}";
					$option_qty			= $mysql->get_one($sql);
					$return_price2		= $return_price2 * ceil($option_qty / $data['delivery_type_qty']);
					$sum_delivery_option[$row['g_uid']] = $option_qty;
				}
			}
			else {
				$return_price2 = $return_price2 * ceil($row['qty'] / $data['delivery_type_qty']);				
			}			
		}
		$return_price += $return_price2;
	}
	else $ck = 1;
}

if($ck == 1) {
	if($vendor) {
		$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$vendor}'";
		$vshop_config	= $mysql->one_row($sql);

		$shop_config['delivery_im_areas1_used']		= $vshop_config['delivery_im_areas1_used'];
		$shop_config['delivery_im_areas1_price']	= $vshop_config['delivery_im_areas1_price'];
		$shop_config['delivery_im_areas2_used']		= $vshop_config['delivery_im_areas2_used'];
		$shop_config['delivery_im_areas2_price']	= $vshop_config['delivery_im_areas2_price'];
	}

	$return_price += deliveryImAreasPrice($shop_config, $vendor, $address, $postcode);		
		
	$sql = "SELECT * FROM mallRN_delivery_configuration WHERE vendor = '{$vendor}' && used = 1 ORDER BY uid ASC";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()) {	
		$title = stripslashes($row['title']);
		if(preg_match("/{$title}/i", $address)) {
			$return_price += $row['price'];
			break;
		}
	}
}

echo json_encode(array('price' => $return_price));
exit;

?>