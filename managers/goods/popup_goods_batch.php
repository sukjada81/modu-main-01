<?php 

include_once("../common/popup_top.php");

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

$search_variable	= array('field','keyword','cate','date_type','s_date','e_date','field2','keyword2','field3','keyword3','field4','keyword4','display_use','sell_use','option_use','mileage_type','delivery_type','engine_use','order_priority','qty_type','vendor');	

$addstring	= "";
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if(strlen($value)>0) $addstring .= "&{$v}={$value}";
}		

$where = goodsTypeWhere(2, '');

$sql = "SELECT count(*) FROM mallRN_goods a WHERE a.uid > 0";
$TOTALS2 = $mysql->get_one($sql);	

if($where) {
	if(!preg_match("/b./i",$where)) {
		$sql = "SELECT count(*) FROM mallRN_goods a WHERE a.uid > 0 {$where}";
	}
	else {
		$sql = "SELECT count(*) FROM  mallRN_goods a, mallRN_goods_cate b WHERE a.uid != 0 && a.uid = b.guid {$where}";
	}
	$TOTALS1 = $mysql->get_one($sql);
}
else {
	$TOTALS1 = $TOTALS2;
}
$TOTALS1 = number_format($TOTALS1);
$TOTALS2 = number_format($TOTALS2);

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_goods_batch.html");
$tpl->scan_area("main");

$sql = "SELECT goods_price_limit1, goods_price_limit2, member_mileage_order, delivery_type, delivery_d_price, delivery_p_type, delivery_p_price1, delivery_p_price2 FROM mallRN_configuration WHERE uid=1";
$multi_data = $mysql->one_row($sql);

$goods_price_limit1 = $goods_price_limit2 = '';

switch($mode) {
	case "mileage" :
		######################## 마일리지 설정 #############################
		$conf_mileage = $multi_data['member_mileage_order'];

		$sql = "SELECT * FROM mallRN_member_level WHERE level < 100 ORDER BY level ASC";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()) {

			$level			= $row['level'];
			$level_name		= stripslashes($row['name']);	

			$tpl->parse("loop_level");	
		}
		unset($level, $level_name);
		######################## 마일리지 설정 #############################
	break;

	case "delivery" :
		######################## 배송비 설정 #############################
		$delivery_type_disable1 = "delivery_type_disable";
		$delivery_type_disable2 = "disabled";
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

		if($multi_data['delivery_im_areas1_used']=='1') {
			$conf_delivery .= " / 제주도 - ".number_format($multi_data['delivery_im_areas1_price'])."원";
		}
		if($multi_data['delivery_im_areas2_used']=='1') {
			$conf_delivery .= " / 도서산간 - ".number_format($multi_data['delivery_im_areas2_price'])."원";
		}
		unset($delivery_p_type_arr);
		######################## 배송비 설정 #############################
	break;

	case "name" :
		$tpl->parse("is_{$mode}1");
		$tpl->parse("is_{$mode}2");
	break;

	case "image" : 
	break;
		
	case "price" :
		$goods_price_limit1 = $multi_data['goods_price_limit1'];
		$goods_price_limit2 = $multi_data['goods_price_limit2'];
	break;

	default : iframeViewError("정보가 제대로 넘어오지 못했습니다.");
}

$tpl->parse("is_{$mode}");

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>