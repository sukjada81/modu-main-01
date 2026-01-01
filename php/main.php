<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

if($mobile_header != "mobile_") {

    ######################## 쇼핑카테고리 전체보기 #############################
    $sql = "SELECT * FROM mallRN_cate WHERE cate_dep = '1' && used = '1' ORDER BY sequence ASC";
    $mysql->query($sql);

    while($row = $mysql->fetch_array()) {
        if($my_level < 99) {
            if($row['access_type'] == '1' && $my_level == '0') continue;
            if($row['access_type'] == '2') {
                $acc_level = explode(",", $row['access_level']);
                if(!in_array($my_level, $acc_level)) continue;
            }
        }

        if($row['cate_sub'] == 1) {
            $sql = "SELECT * FROM mallRN_cate WHERE cate_parent = '{$row['cate']}' && used = '1' ORDER BY sequence ASC";
            $mysql->query2($sql);

            while($row2 = $mysql->fetch_array('2')) {
                $CATE		= $row2['cate'];
                $CATE_NAME	= stripslashes($row2['cate_name']);
                $tpl->parse("loop_scate");
            }
        }

        $CATE		= $row['cate'];
        $CATE_NAME	= stripslashes($row['cate_name']);
        $tpl->parse("loop_cate");
    }
    unset($row2, $CATE, $CATE_NAME);
    ######################## 쇼핑카테고리 전체보기 #############################

    ######################## 상단메뉴 #############################
    if($shop_config['design_top_menu']) {
        $top_menu_info = explode("|*|", $shop_config['design_top_menu']);
        foreach($top_menu_info as $k => $v) {
            $top_menu_info2 = explode("|", $v);
            if($top_menu_info2[2] == 0) continue;
            $MENU	= $top_menu_info2[0];
            $URL	= $top_menu_info2[1];

            preg_match("/(cate=)([0-9]*)/",$URL, $matchs);

            if(@$matchs[1] && @$matchs[2]) {
                $sql = "SELECT * FROM mallRN_cate WHERE cate_parent='{$matchs[2]}' && used ='1' ORDER BY  sequence ASC";
                $mysql->query($sql);

                $i2 = 0;
                while($row = $mysql->fetch_array()){
                    if($my_level < 100 && checkCateAccessThis($row['access_type'], $row['access_level'])) continue;

                    $CATE		= $row['cate'];
                    $CATE_NAME = stripslashes($row['cate_name']);
                    $tpl->parse("loop_menu_sub");
                    $i2 ++;
                }

                if($i2 > 0) $tpl->parse("is_menu_sub");
            }
            $tpl->parse("loop_menu");
        }
        unset($top_menu_info2, $MENU, $URL, $CATE, $CATE_NAME);
    }
    ######################## 상단메뉴 #############################
}
else {
    ######################## 상단메뉴 #############################
    if($shop_config['mobile_top_menu']) {
        $top_menu_info = explode("|*|", $shop_config['mobile_top_menu']);
        foreach($top_menu_info as $k => $v) {
            $top_menu_info2 = explode("|", $v);
            if($top_menu_info2[2] == 0) continue;
            $MENU	= $top_menu_info2[0];
            $URL	= $top_menu_info2[1];

            $tpl->parse("loop_menu");
        }
        unset($top_menu_info2, $MENU, $URL);
    }
    ######################## 상단메뉴 #############################

    ######################## 쇼핑카테고리 #############################
    $sql = "SELECT * FROM mallRN_cate WHERE cate_dep = '1' && used = '1' ORDER BY sequence ASC";
    $mysql->query($sql);

    while($row = $mysql->fetch_array()) {
        if($my_level < 99) {
            if($row['access_type'] == '1' && $my_level == '0') continue;
            if($row['access_type'] == '2') {
                $acc_level = explode(",", $row['access_level']);
                if(!in_array($my_level, $acc_level)) continue;
            }
        }

        $CATE		= $row['cate'];
        $CATE_NAME	= stripslashes($row['cate_name']);
        $tpl->parse("loop_cate");
    }
    unset($row2, $CATE, $CATE_NAME);
    ######################## 쇼핑카테고리 전체보기 #############################

    $CO_TEL			= stripslashes($shop_config['comp_tel']);
}

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