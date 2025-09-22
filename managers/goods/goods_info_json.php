<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('__VENDOR_ABLE__', '1');

include_once('../common/ad_init.php');

define('IMAGE_FOLDER', '../../image/goods/img');

$mysql->msgType(2);

$my_array = array();

$uid = checkPostVar('uid');

if(!$uid) {
	echo json_encode(array('error' => '정보가 제대로 넘어오지 못했습니다.'));
	exit;
}

$sql = "SELECT * FROM mallRN_goods WHERE uid='{$uid}'";
$row = $mysql->one_row($sql);

if(!$row) {
	echo  json_encode(array('error' => '해당상품이 삭제되었거나 존재하지 않습니다.'));
	exit;
}

$uid = "'".str_replace(",","','",$uid)."'";

$sql = "SELECT uid, name, image2, price, cate, qty_type, qty, display_use, sale_use, option_use, option_info, require_info, delivery_info, refund_info, exchange_info, as_info FROM mallRN_goods WHERE uid IN({$uid})";
$mysql->query($sql);

$display_use_array				= array("0"=>"<i class='fas fa-times'></i>","1"=>"<i class='far fa-circle'></i>"); 
$sale_use_array					= array("0"=>"<i class='fas fa-times'></i>","1"=>"<i class='far fa-circle'></i>"); 

while($row=$mysql->fetch_array()){    
	$uid			= $row['uid'];
	$image			= IMAGE_FOLDER.$row['image2'];
	$name			= addslashes($row['name']);	
	$price			= number_format($row['price']);
	$option_info	= addslashes($row['option_info']);	
	$require_info	= addslashes($row['require_info']);
	$delivery_info	= addslashes($row['delivery_info']);
	$refund_info	= addslashes($row['refund_info']);
	$exchange_info	= addslashes($row['exchange_info']);
	$as_info		= addslashes($row['as_info']);
	$cate			= getCateAllName($row['cate'], 0);
	$display_use	= $display_use_array[$row['display_use']];
	$sale_use		= $sale_use_array[$row['sale_use']];

	if($row['option_use']==1) {
		$sql = "SELECT count(*) as cnt, sum(qty) as sum FROM mallRN_goods_option WHERE guid = '{$uid}' && qty_type = 0";
		$data = $mysql->one_row($sql);
		$return = "<span class='size09'>옵션</span><br />";
		if($data['cnt']>0) $qty_type = $return.number_format($data['sum']);
		else $qty_type = $return."무제한";
	}
	else {
		if($row['qty_type'] == 1) $qty_type = "무제한";
		else $qty_type = number_format($row['qty']);
	}
	
	$my_array[] = ["uid"=>$uid, "image"=>$image, "name"=>$name, "price"=>$price, "qty_type"=>$qty_type, "cate"=>$cate, "display_use"=>$display_use, "sale_use"=>$sale_use, "option_info"=>$option_info, "require_info"=>$require_info, "delivery_info"=>$delivery_info, "refund_info"=>$refund_info, "exchange_info"=>$exchange_info, "as_info"=>$as_info];
}
	
echo json_encode($my_array);

?>