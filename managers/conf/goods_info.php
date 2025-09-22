<?php 

include_once("../common/top.php");

define('ICON_FOLDER', '../../image/icon');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_info.html");
$tpl->scan_area("main");

$item_array = array('goods_price_limit1','goods_price_limit2','goods_soldout','goods_engine_naver','goods_engine_daum','goods_option_info','goods_brand_info','goods_make_info','goods_origin_info','goods_require_info','goods_icon_info','goods_delivery_info','goods_refund_info','goods_exchange_info','goods_as_info');

$sql = "SELECT * FROM mallRN_configuration WHERE uid=1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

${"checked_soldout_".$goods_soldout} = "checked='chedked'";	
${"checked_engine_naver_".$goods_engine_naver} = "checked='chedked'";	
${"checked_engine_daum_".$goods_engine_daum} = "checked='chedked'";	

$multi_arr = array('option','brand','make','origin','require');
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

if($goods_icon_info) {
	$goods_icon_info = explode("|", $goods_icon_info);
	foreach($goods_icon_info as $k => $v) {
		$img = ICON_FOLDER."/{$v}?t={$t}";
		$img_name = $v;
		$tpl->parse("loop_icon");
	}
}

$goods_delivery_info	= add_escape_re_string($goods_delivery_info);
$goods_refund_info		= add_escape_re_string($goods_refund_info);
$goods_exchange_info	= add_escape_re_string($goods_exchange_info);
$goods_as_info			= add_escape_re_string($goods_as_info);

$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$SHOP_URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>