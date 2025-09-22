<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$sql = "SELECT count(*) FROM mallRN_favorite_goods WHERE id = '{$my_id}'";
$FAVORITE_VIEW = $mysql->get_one($sql);

if($FAVORITE_VIEW > 0) {
	$week_arr	= array('일', '월', '화', '수', '목', '금', '토');

	$sql = "SELECT * FROM mallRN_favorite_goods WHERE id = '{$my_id}' ORDER BY uid DESC";
	$mysql->query($sql);
	
	$tmp_dates	= "";
	$ck_cnt		= 0;
	while($row = $mysql->fetch_array()){
		$DATES	= date("m.d", $row['signdate']);
		$WEEKS	= $week_arr[date('w', $row['signdate'])];
		
		$UID		= $row['g_uid'];
		$RECENT_UID	= $row['uid'];
		
		$sql	= "SELECT uid, name, image2, price, price_ment, moddate, exhibition FROM mallRN_goods WHERE uid = '{$UID}'";
		$data	= $mysql->one_row($sql);

		$NAME	= stripslashes($data['name']);
		$IMAGE	= $data['image2']."?t={$data['moddate']}";
		if($data['price'] == 0 && $data['price_ment']) $PRICE = stripslashes($data['price_ment']);
		else {
			$PRICE = getGoodsPrice($data['price'], $data['uid'], $data['exhibition']);
		}
		
		$tpl->parse("loop_goods");

		$ck_cnt++;
		
		if($tmp_dates && $tmp_dates != $DATES) {
			$ck_cnt = 0;
			$tpl->parse("loop_list"); 
		}
		
		$tmp_dates = $DATES;
	}
	
	if($ck_cnt > 0) $tpl->parse("loop_list"); 
}
else $tpl->parse("loop_empty");


?>