<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_info.html");
$tpl->scan_area("main");

$item_array = array('goods_option_info','goods_brand_info','goods_make_info','goods_origin_info','goods_delivery_info','goods_refund_info','goods_exchange_info','goods_as_info');

$sql = "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$v_my_id}'";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

$multi_arr = array('option','brand','make','origin');
foreach($multi_arr as $k => $v) {
	if(${"goods_".$v."_info"}) {
		$ttl = $v;
		$vls = explode("|*|",${"goods_".$v."_info"});		
		foreach($vls as $k2 => $v2) {		
			$vls2	= explode("|", $v2);
			$title	= $vls2[0];
			$used	= $vls2[1];
			if(isset($vls2[2])) $info	= str_replace("\r\n","\\r\\n",$vls2[2]);
			$tpl->parse("loop_multi");
		}
	}
}
unset($title, $info, $used);
foreach($multi_arr as $k => $v) {
	$ttl = $v;
	$tpl->parse("loop_multi");
}

$goods_delivery_info	= add_escape_re_string($goods_delivery_info);
$goods_refund_info		= add_escape_re_string($goods_refund_info);
$goods_exchange_info	= add_escape_re_string($goods_exchange_info);
$goods_as_info			= add_escape_re_string($goods_as_info);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>