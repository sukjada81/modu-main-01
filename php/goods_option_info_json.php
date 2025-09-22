<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();
$uid		= checkPostVar('uid');
$value		= checkPostVar('value');
$value2		= explode("|", $value);
$value3		= count($value2);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$uid || !$value) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql			= "SELECT option_info, sale_use FROM mallRN_goods WHERE uid = '{$uid}'";
$data			= $mysql->one_row($sql);
$option_info	= $data['option_info'];

if(!$option_info) json_error_msg('옵션이 없는 상품 입니다.');

$option_info	= explode("|*|", $option_info);
$option_cnt		= count($option_info);

if($option_cnt == ($value3 + 1)) {
	
	$sql	= "SELECT * FROM mallRN_goods_option WHERE guid='{$uid}' && used = 1 && value like '{$value}|%' ORDER BY sequence ASC";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		$value2 = explode("|", $row['value']);
		$value2 = array_slice($value2, $value3);
		$value2 = join("|", $value2);
		
		if(($row['qty_type'] == 0 && $row['qty'] < 1) || $data['sale_use'] == 0)	$soldout = 1;
		else																		$soldout = 0;

		$goods_price = getGoodsOptionPrice($row['price'], $uid);		

		$my_array[] = ["option_value" => $value2, "option_info" => $row['uid']."|".$row['price']."|".$row['qty_type']."|".$row['qty']."|".$goods_price, "option_price" => $row['price'], "option_soldout" => $soldout];	
	}
}
else {

	$sql = "SELECT value FROM mallRN_goods_option WHERE guid='{$uid}' && used = 1 && value like '{$value}|%' ORDER BY sequence ASC";
	$option_value_info	= $mysql->get_one_jum($sql, "|*|");
	$option_value_info2 = explode("|*|", $option_value_info);

	$option_value_array = array();
	foreach($option_value_info2 as $k2 => $v2) {
		$option_value_info3 = explode("|", $v2);
		if(!in_array($option_value_info3[$value3], $option_value_array)) $option_value_array[] = $option_value_info3[$value3];
	}

	foreach($option_value_array as $k2 => $v2) {
		$my_array[] = ["option_value" => $v2, "option_info" => ""];	
	}

}
echo json_encode($my_array);

?>