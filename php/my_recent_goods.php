<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$sql = "SELECT count(*) FROM mallRN_goods_recent_view WHERE check_id = '{$cart_id}'";
$RECENT_VIEW = $mysql->get_one($sql);

if($RECENT_VIEW > 0) {
	$week_arr	= array('일', '월', '화', '수', '목', '금', '토');

	$sql	= "SELECT b.signdate, b.uid, b.g_uid, a.name, a.image3, a.moddate, a.price, a.price_ment FROM mallRN_goods_recent_view b, mallRN_goods a WHERE b.g_uid = a.uid && b.check_id = '{$cart_id}' && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 ORDER BY b.signdate DESC LIMIT 30";
	$mysql->query($sql);
	
	$tmp_dates	= "";
	$ck_cnt		= 0;
	while($row = $mysql->fetch_array()){

		if($tmp_dates && $tmp_dates != date("m.d", $row['signdate'])) {
			$ck_cnt = 0;			
			$tpl->parse("loop_list"); 
		}
		
		$DATES	= date("m.d", $row['signdate']);
		$WEEKS	= $week_arr[date('w', $row['signdate'])];		

		$UID		= $row['g_uid'];
		$RECENT_UID	= $row['uid'];
		
		$NAME	= stripslashes($row['name']);
		$IMAGE	= $row['image3']."?t={$row['moddate']}";
		if($row['price'] == 0 && $row['price_ment']) $PRICE = stripslashes($row['price_ment']);
		else {
			$PRICE = number_format($row['price'], CONF_FLOAT_CNT);
		}
		
		$tpl->parse("loop_goods");

		$tmp_dates = $DATES;

		$ck_cnt++;
	}

	if($ck_cnt > 0) $tpl->parse("loop_list"); 
}
else $tpl->parse("loop_empty");


?>