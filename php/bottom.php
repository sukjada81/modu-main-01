<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}bottom.html");
$tpl->scan_area("main");

$CS_TIME1		= ($shop_config['basic_cs_time1']) ? stripslashes($shop_config['basic_cs_time1']) : "09:00 ~ 18:00";
$CS_TIME2		= ($shop_config['basic_cs_time2']) ? stripslashes($shop_config['basic_cs_time2']) : "휴무";
$CS_TIME3		= ($shop_config['basic_cs_time3']) ? stripslashes($shop_config['basic_cs_time3']) : "휴무";
$CS_TIME4		= ($shop_config['basic_cs_time4']) ? stripslashes($shop_config['basic_cs_time4']) : "12:00 ~ 13:00";
$CO_SHOP_NAME	= stripslashes($shop_config['basic_name']);
$CO_NAME		= stripslashes($shop_config['comp_name']);
$CO_OWNER		= stripslashes($shop_config['comp_owner']);
$CO_NUM1		= stripslashes($shop_config['comp_license_no1']);
$CO_NUM2		= stripslashes($shop_config['comp_license_no2']);
$CO_NUM3		= stripslashes(str_replace("-", "", $shop_config['comp_license_no1']));
$CO_ADDR		= stripslashes($shop_config['comp_address1'])." ".stripslashes($shop_config['comp_address2']);
if($shop_config['comp_rtn_address1']) {
	$CO_RTN_ADDR	= stripslashes($shop_config['comp_rtn_address1'])." ".stripslashes($shop_config['comp_rtn_address2']);
	$tpl->parse("is_return_address");
}
$CO_TEL			= stripslashes($shop_config['comp_tel']);
$CO_FAX			= stripslashes($shop_config['comp_fax']);
$CO_ADMIN		= stripslashes($shop_config['basic_admin']);
$CO_EMAIL		= stripslashes($shop_config['basic_email']);

if($shop_config['payment_bank_info']) {
	$bank_info = explode("|*|", $shop_config['payment_bank_info']);
	foreach($bank_info as $k => $v) {
		$bank_info2 = explode("|", $v);

		if($bank_info2[3] == 0) continue;

		$BANK_NAME	= $bank_info2[0];
		$BANK_NUM	= $bank_info2[1];
		$BANK_OWNER	= $bank_info2[2];
		
		$tpl->parse("loop_bank");
	}
}

$START_YEAR = date("Y", $shop_config['signdate']);

if($mobile_header != "mobile_") {

	if($shop_config['design_icon_display']) {
		$basic_icon_display = explode("|", $shop_config['design_icon_display']);
		if($basic_icon_display[0] == 1) $tpl->parse("is_icon1");
		if($basic_icon_display[1] == 1) $tpl->parse("is_icon2");
		if($basic_icon_display[2] == 1) $tpl->parse("is_icon3");

		unset($basic_icon_display);
	}

	if($shop_config['design_vendor_link']) {
		$tpl->parse("is_vendor_link");		
	}
}

################ 최근본 상품 ################
$sql				= "SELECT count(*) FROM mallRN_goods_recent_view WHERE check_id = '{$cart_id}'";
$RECENT_VIEW_CNT	= $mysql->get_one($sql);

if($RECENT_VIEW_CNT > 0) {
	$week_arr	= array('일', '월', '화', '수', '목', '금', '토');

	$sql	= "SELECT b.signdate, b.uid, b.g_uid, a.name, a.image3, a.moddate FROM mallRN_goods_recent_view b, mallRN_goods a WHERE b.g_uid = a.uid && b.check_id = '{$cart_id}' && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 ORDER BY b.signdate DESC LIMIT 30";
	$mysql->query($sql);
	
	$tmp_dates	= "";
	$i			= 0;
	while($row = $mysql->fetch_array()){
		
		if($tmp_dates && $tmp_dates != date("m.d", $row['signdate'])) $tpl->parse("loop_recent_view"); 
		
		$DATES	= date("m.d", $row['signdate']);
		$WEEKS	= $week_arr[date('w', $row['signdate'])];
		
		$UID		= $row['g_uid'];
		$RECENT_UID	= $row['uid'];
		
		$NAME	= stripslashes($row['name']);
		$IMAGE	= $row['image3']."?t={$row['moddate']}";
		
		$tpl->parse("loop_recent_view_goods");

		if($i == 0) {
			@$tpl->parse("loop_recent_view_goods0");
			$i = 1;
		}
		
		$tmp_dates = $DATES;
	}
	$tpl->parse("loop_recent_view"); 
}
else $tpl->parse("loop_empty_recent_view");
################ 최근본 상품 ################

$sql		= "SELECT count(*) FROM mallRN_cart WHERE cart_id = '{$cart_id}'";
$CART_CNT	= $mysql->get_one($sql);

commonBannerCheck('bottom');

################ 팝업 ################
if($channel == "main") {

	$tpl->parse("is_popup_init");
	
	define('POPUP_FOLDER', "image/{$mobile_header}popup");

	$sql = "SELECT * FROM mallRN_{$mobile_header}popup WHERE status = '0' ORDER BY position ASC, uid ASC";
	$mysql->query($sql);

	$today			= date("Y-m-d H:i:s");
	$position_ck	= array();

	while($row = $mysql->fetch_array()){
		if($row['period'] == 1) {
			if(substr($row['s_date'], 0, 10) > $today) continue;
			if(substr($row['e_date'], 0, 10) < $today) {
				$sql = "UPDATE mallRN_{$mobile_header}popup SET status = '2' WHERE uid = '{$row['uid']}'";
				$mysql->query2($sql);
				continue;
			}
		}
		
		$popup_name = "popup_".$row['uid'];		
		$POP_POSX = $POP_POSY = "";

		$pop_cookie = isset($_COOKIE[$popup_name]) ? $_COOKIE[$popup_name] : "";
		if($pop_cookie &&  $row['type'] == '1') continue;

		if(!in_array($row['position'], $position_ck)) {
			
			$POP_POSITION	= $row['position'];
			if($row['position'] == 0) {
				$in_position	= explode("|", $row['input_position']);
				$POP_POSX		= $in_position[0];
				$POP_POSY		= $in_position[1];
			}
			if($row['type'] == 1) $POP_COOKIE = $row['uid'];

			if($row['image_only'] == 1) {					

				$pop_image		= POPUP_FOLDER."/".$row['uid']."/".$row['image1'];
				$POP_CONTENT	= "<img src='{$pop_image}' alt='' />";
			
				if($pop_size		= @GetImageSize($pop_image)) {
					$POP_LINK		= stripslashes($row['link1']);
					$POP_WIDTH		= $pop_size[0];
					$POP_HEIGHT		= $pop_size[1];
					$POP_IMG		= 1;						
					
					$sql = "SELECT count(*) FROM mallRN_{$mobile_header}popup WHERE status = '0' && position = '{$row['position']}' && image_only = 1 && uid != '{$row['uid']}'";
					if($mysql->get_one($sql) > 0) {

						$sql = "SELECT * FROM mallRN_{$mobile_header}popup WHERE status = '0' && position = '{$row['position']}' && image_only = 1 && uid != '{$row['uid']}'";
						$mysql->query2($sql);

						$pop_image_arr	= array();
						$pop_name_arr	= array();
						$pop_link_arr	= array();
						$pop_cookie_arr	= array();

						$pop_image_arr[]	= $pop_image;
						$pop_name_arr[]		= stripslashes($row['name']);
						$pop_link_arr[]		= stripslashes($row['link1']);
						if($row['type'] == 1) $pop_cookie_arr[]	= $row['uid'];
						
						while($row2 = $mysql->fetch_array(2)) {
							$popup_name = "popup_".$row2['uid'];	
							$pop_cookie = isset($_COOKIE[$popup_name]) ? $_COOKIE[$popup_name] : "";
							if($pop_cookie &&  $row2['type'] == '1') continue;

							$pop_image_arr[]	= POPUP_FOLDER."/".$row2['uid']."/".$row2['image1'];
							$pop_name_arr[]		= stripslashes($row2['name']);
							$pop_link_arr[]		= stripslashes($row2['link1']);
							if($row2['type'] == 1) $pop_cookie_arr[]	= $row2['uid'];
							$POP_IMG		= 2;
						}	
						
						if($POP_IMG == 2) {
							$POP_CONTENT	= join("|", $pop_image_arr);
							$POP_NAME		= join("|", $pop_name_arr);
							$POP_LINK		= join("|", $pop_link_arr);
							$POP_COOKIE		= join("|", $pop_cookie_arr);
						}

						$position_ck[] = $row['position'];							
					}
					
					$tpl->parse("loop_popup");
				}
			}
			else {
				$POP_CONTENT	= add_escape_re_string($row['content']);
				$in_size		= explode("|", $row['input_size']);
				$POP_WIDTH		= $in_size[0];
				$POP_HEIGHT		= $in_size[1];

				$tpl->parse("loop_popup");
			}
		}
	}
}
################ 팝업 ################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>