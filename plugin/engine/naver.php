<?php

define('DEFAULT_PATH',	'../../');
include_once('../../php/init.php');

$mode	= checkGetVar("mode");

if($shop_config['goods_engine_naver'] == 0) exit;
if($mode && ($mode != 'summ')) exit;

$filename	= "data/naver{$mode}.txt";
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
if($mode=="summ") {
	$where = " && (signdate > '".(time() - 86400)."' || moddate > '".(time() - 86400)."')";
}

echo "id	title	price_pc	price_mobile	normal_price	link	mobile_link	image_link	add_image_link	category_name1	category_name2	category_name3	category_name4	naver_category	naver_product_id	condition	import_flag	parallel_import	order_made	product_flag	adult	goods_type	barcode	manufacture_define_number	model_number	brand	maker	origin	card_event	event_words	coupon	partner_coupon_download	interest_free_event	point	installation_costs	pre_match_code	search_tag	group_id	vendor_id	coordi_id	minimum_purchase_quantity	review_count	shipping	delivery_grade	delivery_detail	attribute	option_detail	seller_id	age_group	gender	class	update_time\r\n";

$utime			= date("Y-m-d H:i:s");

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
	
	$EVENT	= "";
	if($row['exhibition'] && $row['exhibition'] != ',') {	
		foreach($event_info_array as $k => $v) {
			if(preg_match("/,{$k},/i", $row['exhibition'])) {				
				$EVENT = "이벤트할인 {$v}%";
				break;
			}
		}
	}
	
	$COUPON	= "";
	if($goods_info['coupon_price']) {
		$tmp_price1		= (int) $PRICE;
		$tmp_price2		= (int) str_replace(",", "", $goods_info['coupon_price']);
		$PRICE			= $tmp_price1 + $tmp_price2;
		$tmp_per		= round(($tmp_price2 * 100) / $PRICE);
		$COUPON			= $tmp_price2."원, ".$tmp_per."%";
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
		$MILEAGE	= "마일리지^{$mileage}";
	}	

	$sql		= "SELECT count(*) FROM mallRN_review WHERE g_uid = '{$row['uid']}'";
	$REVIEW		= $mysql->get_one($sql);

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

	switch($row['delivery_type']) {
		case "1" : 
			if($delivery_type == 'F') $CARR = 0;
			else if($delivery_type=='D') $CARR = "-1";
			else {
				if($PRICE < $delivery_p_price1) $CARR = $delivery_p_price2;
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
			$CARR = $row['delivery_price'];
		break;
		case "5" :
			$CARR = $row['delivery_price'];
		break;
	}
	######################## 배송비 #############################

	######################## 옵션 #############################	
	$OTP	= "";
	if($row['option_use'] == 1) {		

		$option_info = explode("|*|",$row['option_info']);						
		$option_name = array();
		for($ii=0,$cnt=count($option_info); $ii<$cnt; $ii++) {
			$option_info2	= explode("|",$option_info[$ii]);
			$option_name[]	= $option_info2[0];								
		}	
		unset($option_info, $option_info2);

		$sql = "SELECT * FROM mallRN_goods_option WHERE guid='{$row['uid']}' ORDER BY sequence ASC";
		$mysql->query2($sql);
		
		$options = array();
		while($row_op = $mysql->fetch_array(2)){
			$option_value	= explode("|",stripslashes($row_op['value']));
			$option_value2	= array();
			for($j=0, $cnt=count($option_value); $j<$cnt; $j++) {
				$option_value2[] = $option_name[$j].":".$option_value[$j];																		
			}
			$options[] = join(", ",$option_value2)."^".$row_op['price'];
		}

		$OTP	= join("|", $options);				
		unset($option_value, $option_value2, $options);
		
	}
	######################## 옵션 #############################
	
	$class = $up_time = "";
	
	if(date("Y-m-d", $row['signdate']) == date("Y-m-d")) {
		if($row['qty_type'] == 0 && $row['qty'] < 1) $class = "D";
		else $class = "I";		

		if($mode=='summ') $up_time = date("Y-m-d H:i:s", $row['signdate']);
	}
	else {
		if($PRICE == 0) $class = "D";
		else if($row['qty_type'] == 0 && $row['qty'] < 1) $class = "D";
		else $class = "U";		

		if($mode=='summ') $up_time = date("Y-m-d H:i:s",$row['moddate']);
	}	

	if($mode!='summ') $class = $up_time = "";

	echo "{$UID}	{$NAME}	{$PRICE}	{$PRICE}	{$CPRICE}	{$LINK}	{$MLINK}	{$IMAGE}		{$cate1}	{$cate2}	{$cate3}	{$cate4}			신상품									{$MODEL}	{$BRAND}	{$MAKE}	{$ORIGIN}		{$EVENT}	{$COUPON}			{$MILEAGE}			{$TAG}					{$REVIEW}	{$CARR}				{$OTP}				{$class}	{$up_time}\r\n";
}

$tmps = ob_get_contents();
ob_end_flush(); 
ob_end_clean(); 
file_put_contents($filename, $tmps);

?>