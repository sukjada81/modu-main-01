<?php

define('DEFAULT_PATH',	'../../');
include_once('../../php/init.php');

$mode	= checkGetVar("mode");

if($shop_config['goods_engine_daum'] == 0) exit;
if($mode && ($mode != 'summ')) exit;

$filename	= "data/daum{$mode}.txt";
if(file_exists($filename)) {
	$times	= filectime($filename);
	if($times > time() - 600) {
		echo file_get_contents($filename);
		exit;
	}
}

ob_start();

$cate_array		= array();
$brand_array	= array();

$sql = "SELECT cate_name, cate FROM mallRN_cate WHERE used = 1 ORDER BY cate ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	$cate_array[$row['cate']] = stripslashes($row['cate_name']);	
}

$vshop_config	= array();
$sql			= "SELECT vendor, delivery_type, delivery_p_price1, delivery_p_price2 FROM mallRN_vendor_configuration";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	$vshop_config[$row['vendor']] = array($row['delivery_type'], $row['delivery_p_price1'], $row['delivery_p_price2']);
}

$sql					= "SELECT member_mileage_order FROM mallRN_configuration WHERE uid = 2";
$member_mileage_order	= $mysql->get_one($sql);

$where		= "";

$sql	= "SELECT * FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && engine_use = 1 {$where} ORDER BY cate ASC, uid ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	if($mode != 'summ') {
		if($row['qty_type'] == 0 && $row['qty'] < 1) continue;
	}

	$cate1 = $caid1 = $cate2 = $caid2 = $cate3 = $caid3 = $cate4 = $caid4 = '';
	
	$goods_info		= getGoodsInfo($row);	

	$LINK		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".$goods_info['link'];
	$MLINK		= $LINK;
	$IMAGE		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".str_replace("../../", "", $goods_info['image']);
	$MODEL		= stripslashes($row['model']);
	$MAKE		= stripslashes($row['make']);
	$ORIGIN		= stripslashes($row['origin']);
	$NAME		= $goods_info['name'];
	$PRICE		= $goods_info['price'];	
	$PRICE		= str_replace(",", "",$PRICE);
	$MILEAGE	= 0;
	$UID		= "SHOP_".$row['uid'];
	$BRAND		= $row['brand'];
	if($row['consumer_price']>0)	$CPRICE	= $row['consumer_price'];
	else							$CPRICE	= "";
	$TAG		= (substr($row['keyword'], 1, -1));
	$TAG		= str_replace("," ,"|",$TAG);
		
	if(substr($row['cate'], 9, 3) !='000') {
		$cate4 = $cate_array[$row['cate']];
		$caid4 = $row['cate'];
	} 
	else $cate4 = $caid4 = '';
	if(substr($row['cate'],6 , 3) !='000') {
		$cate3 = $cate_array[substr($row['cate'],0,9)."000"];
		$caid3 = substr($row['cate'],0,9);
	} 
	else $cate3 = $caid3 = '';
	if(substr($row['cate'], 3, 3) != '000') {
		$cate2 = $cate_array[substr($row['cate'],0,6)."000000"];
		$caid2 = substr($row['cate'],0,6);
	}
	else $cate2 = $caid2 = '';
	$cate1	= $cate_array[substr($row['cate'],0,3)."000000000"];
	$caid1	= substr($row['cate'],0,3);
	
	$COUPON	= "";
	if($goods_info['coupon_price']) {
		$tmp_price1		= (int) $PRICE;
		$tmp_price2		= (int) str_replace(",", "", $goods_info['coupon_price']);
		$PRICE			= $tmp_price1 + $tmp_price2;
		//$tmp_per		= round(($tmp_price2 * 100) / $PRICE);
		$COUPON			= $tmp_price2."원";
	}

	$MILEAGE	= $mileage_per = "";
	switch($row['mileage_type']) {
		case "1" : 
			$mileage_per = $member_mileage_order; 
		break;
		case "3" : 			
			$mileage_level = explode("|*|", $row['mileage_level']);
			foreach($mileage_level as $k => $v) {
				$mileage_level2 = explode("|", $v);
				if($mileage_level2[0] == 1) {
					$mileage_per = $mileage_level2[1];
					break;
				}
			}
			unset($mileage_level, $mileage_level2);
		break;
		case "4" :
			$mileage_per = $row['mileage_common'];
		break;
	}
	if($mileage_per) {
		$mileage	= floor(($PRICE * $mileage_per) / 100);
		$MILEAGE	= $mileage;
	}	

	######################## 배송비 #############################
	if($row['vendor']) {
		$delivery_type		= $vshop_config[$row['vendor']][0];
		$delivery_p_price1	= $vshop_config[$row['vendor']][1];
		$delivery_p_price2	= $vshop_config[$row['vendor']][2];
	}
	else {
		$delivery_type		= $shop_config['delivery_type'];
		$delivery_p_price1	= $shop_config['delivery_p_price1'];
		$delivery_p_price2	= $shop_config['delivery_p_price2'];
	}

	$CARR2 = '';

	switch($row['delivery_type']) {
		case "1" : 
			if($delivery_type == 'F') $CARR = 0;
			else if($delivery_type=='D') $CARR = "-1";
			else {
				if($PRICE < $delivery_p_price1) {
					$CARR = 2;
					$CARR2 = "{$delivery_p_price1}원미만 {$delivery_p_price2}원";
				}					
				else $CARR = 0;
			}
		break;
		case "2" :
			$CARR = 0;
		break;
		case "3" : 
			$CARR = "-1";
		break;
		case "4" :
			$CARR = 1;
			$CARR2 = $row['delivery_price'];
		break;
		case "5" :
			$CARR = 1;
			$CARR2 = $row['delivery_price'];
		break;
	}
	######################## 배송비 #############################
	
	$tmps	= explode("?", $goods_info['image']);
	$times = filectime($tmps[0]);
	$times = date("Ymdhis", $times);

	switch($mode) {
		case "summ" :
			echo "<<<begin>>>\r\n<<<pid>>>{$UID}\r\n<<<price>>>{$PRICE}\r\n<<<pname>>>{$NAME}\r\n<<<end>>>\r\n";
		break;
		
		default :
			echo "<<<begin>>>\r\n<<<pid>>>{$UID}\r\n<<<price>>>{$PRICE}\r\n<<<pname>>>{$NAME}\r\n<<<pgurl>>>{$LINK}\r\n<<<igurl>>>{$IMAGE}\r\n<<<updateimg>>>{$times}\r\n<<<cate1>>>{$caid1}\r\n<<<cate2>>>{$caid2}\r\n<<<cate3>>>{$caid3}\r\n<<<cate4>>>{$caid4}\r\n<<<catename1>>>{$cate1}\r\n<<<catename2>>>{$cate2}\r\n<<<catename3>>>{$cate3}\r\n<<<catename4>>>{$cate4}\r\n<<<model>>>{$MODEL}\r\n<<<brand>>>{$BRAND}\r\n<<<maker>>>{$MAKE}\r\n<<<pdate>>>\r\n<<<coupon>>>{$COUPON}\r\n<<<pcard>>>\r\n<<<point>>>{$MILEAGE}\r\n<<<deliv>>>{$CARR}\r\n<<<deliv2>>>{$CARR2}\r\n<<<end>>>\r\n";
		break;
	}
}

$tmps = ob_get_contents();
ob_end_flush(); 
ob_end_clean(); 
file_put_contents("data/{$fname}.txt", $tmps);

?>