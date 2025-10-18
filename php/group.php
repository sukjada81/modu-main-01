<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('ICON_FOLDER', '../../image/icon');

######################## 변수 정의 #############################
$goods_field = array();
foreach($default_goods_field as $k => $v) {
	$goods_field[] = "a.{$v}";
}
$goods_field = join(", ", $goods_field);
######################## 변수 정의 #############################

$goods_array	= array();
$day			= strtotime('-1 WEEK', time());
$sql = "SELECT COUNT(b.g_uid) as o_cnt, {$goods_field} FROM mallRN_order_goods b, mallRN_goods a WHERE b.g_uid = a.uid && b.signdate > '{$day}' && b.status < 8 && a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 && b.reals = 1 GROUP BY b.g_uid ORDER BY o_cnt DESC LIMIT 50";
$mysql->query($sql);

$ck = 0;
while($row = $mysql->fetch_array()){
	
	$ck++;	
	getGoodsInfo($row, "list");	
	$goods_array[] = $row['uid'];
	
}	

if($ck < 51) {
	$limit	= 50 - $ck;
	$where	= "";
	if(count($goods_array) > 0) {
		$goods_array = join(",", $goods_array);
		$where = " && a.uid NOT IN({$goods_array}) ";
	}
	
	$sql = "SELECT {$goods_field} FROM mallRN_goods a WHERE a.display_use = 1 && a.auth_ck = 'Y' && a.cate_hide = 0 && a.vendor_hide = 0 {$where} ORDER BY a.order_priority ASC, a.order_cnt DESC, a.view_cnt DESC LIMIT {$limit}";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		
		$ck++;
		getGoodsInfo($row, "list");		
		
	}	
}

if($ck == 0) $tpl->parse("empty_list");

?>