<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$CS_TIME1		= ($shop_config['basic_cs_time1']) ? stripslashes($shop_config['basic_cs_time1']) : "09:00 ~ 18:00";
$CS_TIME2		= ($shop_config['basic_cs_time2']) ? stripslashes($shop_config['basic_cs_time2']) : "휴무";
$CS_TIME3		= ($shop_config['basic_cs_time3']) ? stripslashes($shop_config['basic_cs_time3']) : "휴무";
$CS_TIME4		= ($shop_config['basic_cs_time4']) ? stripslashes($shop_config['basic_cs_time4']) : "12:00 ~ 13:00";
$CO_TEL			= stripslashes($shop_config['comp_tel']);

$sql			= "SELECT cate_info FROM mallRN_board_manager WHERE id = 'faq'";
$faq_cate_info	= $mysql->get_one($sql);

$cate_info = explode("|*|", $faq_cate_info);
if($cate_info[0] > 100) {
	$cate_array = array();
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);

		$cate_num = $cate_info2[0];
		$cate_name = $cate_info2[1];

		$cate_array[$cate_num] = $cate_name;

		$tpl->parse("loop_faq_cate");
	}
}
else $cate_array = array('');


$sql = "SELECT uid, subject, signdate FROM mallRN_board_notice WHERE dels = 0 ORDER BY notice ASC, idx ASC, main ASC, sub ASC LIMIT 3";
$mysql->query($sql);	
while($row = $mysql->fetch_array()){
	$UID		= $row['uid'];
	$SUBJECT	= specialStrReplace2($row['subject']);
	
	if($row['signdate'] > strtotime(date("Y-m-d")))	$DATE = date("H:i:s", $row['signdate']);
	else											$DATE = date("Y-m-d", $row['signdate']);
	
	$tpl->parse("loop_notice");
}	


?>