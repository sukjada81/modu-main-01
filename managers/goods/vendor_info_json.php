<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$id = checkPostVar('id');

if(!$id) {
	echo json_encode(array('error' => '정보가 제대로 넘어오지 못했습니다.'));
	exit;
}

$sql = "SELECT commission FROM mallRN_vendor WHERE id = '{$id}'";
$row = $mysql->one_row($sql);

if(!$row) {
	echo  json_encode(array('error' => '해당 판매사가 삭제되었거나 존재하지 않습니다.'));
	exit;
}

$delivery_type_disable1 = "delivery_type_disable";
$delivery_type_disable2 = "disabled";

$sql = "SELECT delivery_type, delivery_d_price, delivery_p_type, delivery_p_price1, delivery_p_price2 FROM mallRN_vendor_configuration WHERE vendor = '{$id}'";
$multi_data = $mysql->one_row($sql);

if($multi_data['delivery_type']=='F') {
	$conf_delivery = "무료배송";
}
else if($multi_data['delivery_type']=='D') {
	$conf_delivery = "착불 - ".number_format($multi_data['delivery_d_price']);
}
else {
	$delivery_p_type_arr = array("order"=>"주문금액","pay"=>"결제금액");
	$conf_delivery = "조건부 - ".$delivery_p_type_arr[$multi_data['delivery_p_type']]." ".number_format($multi_data['delivery_p_price1'])."원 미만 ".number_format($multi_data['delivery_p_price2'])." 원";
	$delivery_type_disable1 = $delivery_type_disable2 = "";
}
	
echo json_encode(array("commission" => $row['commission'], "delivery" => $conf_delivery, "disable1" => $delivery_type_disable1, "disable2" => $delivery_type_disable2));

?>