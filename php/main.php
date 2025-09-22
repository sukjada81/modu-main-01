<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$main_display		= $shop_config['design_main_display_order'] ? $shop_config['design_main_display_order'] : 'reco, code, best, cate, new';
$main_display_arr	= explode(",", $main_display);
$display_check_arr  = array('reco' => 2, 'best' => 1, 'new' => 3, 'code' => 0, 'cate' => 0); 

$goods_field = array();
foreach($default_goods_field as $k => $v) {
	$goods_field[] = "{$v}";
}
if(count($goods_field) > 0) $goods_field = join(", ", $goods_field);
else $goods_field = "*";

foreach($main_display_arr as $k => $v) {
	$v = trim($v);

	if($display_check_arr[$v] > 0) {
		$i = $display_check_arr[$v];
		if($shop_config['design_main_display'.$i] == 0) continue;
		
		$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && main1_display{$i} = 1 ORDER BY main1_display{$i}_sequence ASC";
		$mysql->query($sql);

		$ck = 0;
		while($row = $mysql->fetch_array()){
			getGoodsInfo($row, "goods_item");
			$ck++;
		}	
		
		if($shop_config['design_main_display'.$i] == 3)	$tpl->parse("is_display_goods_type1_group");
		if($shop_config['design_main_display'.$i] > 1)	$tpl->parse("is_display_goods_type1");
		else											$tpl->parse("is_display_goods_type0");

		if($ck > 0) $tpl->parse("is_display_goods_item");	
		
		$tpl->parse("loop_display_goods");
	}
	else if($v == 'cate') {
		if($shop_config['design_main_category'] == 0) continue;
		
		if($shop_config['design_main_category_info'] && $shop_config['design_main_category_info'] != '||') {
			$main_category_info = explode("|*|", $shop_config['design_main_category_info']);
			foreach($main_category_info as $k => $v2) {
				$main_category_info2	= explode("|", $v2);
				if($main_category_info2[2] == 0) continue;
				$goods_cate				= $main_category_info2[0];
				$goods_display			= $main_category_info2[1];
				$CATE_NAME				= getCateAllName($goods_cate, '', 1);
				$v						= "cate".$goods_cate;

				$sql = "SELECT {$goods_field} FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 && SUBSTRING(cate, 1, 3) = '{$goods_cate}' && main2_display{$goods_display} = 1 ORDER BY main2_display{$goods_display}_sequence ASC";
				$mysql->query($sql);
				
				$ck = 0;
				while($row = $mysql->fetch_array()){
					getGoodsInfo($row, "cate_goods_item", 1);			
					$ck++;
				}
			
				if($ck > 0) {
					if($shop_config['design_main_category'] == 3)	$tpl->parse("is_cate_goods_type1_group");
					if($shop_config['design_main_category'] > 1)	$tpl->parse("is_cate_goods_type1");
					else											$tpl->parse("is_cate_goods_type0");
				}

				$tpl->parse("loop_cate_menu");
				$tpl->parse("loop_cate_goods");
			}

			$tpl->parse("is_cate_goods_item");
			unset($goods_cate, $goods_display, $CATE_NAME, $main_category_info);

			$tpl->parse("loop_display_goods");
		}
	}
	else if($v == 'code') {
		if($shop_config['design_main_custom_code'] == 0) continue;

		$CUSTOM_CODE = stripslashes($shop_config['design_main_custom_code_info']);
		$tpl->parse("is_custom_code");
		$tpl->parse("loop_display_goods");
	}
}


?>