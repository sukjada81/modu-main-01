<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$sub_page_arr = array();
$sub_page_arr['mypage'] = [['order_list', '주문내역'], ['my_mileage', '마일리지'], ['my_coupon', '쿠폰'], ['my_favorite_goods', '나의관심', [['my_favorite_goods', '관심상품'], ['my_favorite_store', '관심스토어'], ['my_recent_goods', '최근본상품']]], ['my_counsel', '내가쓴글', [['my_counsel', '1:1 문의'], ['my_review', '구매후기'], ['my_inquiry', '상품문의']]], ['member_modify', '회원정보 관리', [['member_modify', '회원정보 수정'], ['member_passwd', '비밀번호 변경'], ['member_withdrawal', '회원탈퇴']]]];

$sub_page_arr['cs_center'] = [['cs_board|faq', '자주찾는질문'], ['cs_board|counsel', '1:1문의'], ['cs_board|notice', '공지사항'], ['cs_board|review', '구매후기'], ['cs_board|inquiry', '상품문의']];

$tpl = new classTemplate;
$tpl->define("main","{$skin}/sub_menu.html");
$tpl->scan_area("main");

$tmps_b_id	= checkGetVar('b_id');
foreach ($sub_page_arr[$channel_group[0]] as $k => $v) {	
	$tmps			= explode("|", $v[0]);
	if(count($tmps) == 1)		$MENU_CHANNEL	= $tmps[0];
	else if(count($tmps) == 2)	$MENU_CHANNEL	= $tmps[0]."&b_id=".$tmps[1];			
	$MENU_NAME		= $v[1];

	if(isset($v[2])) {
		foreach ($v[2] as $k2 => $v2) {			
			if($my_sns_type && $v2[0] == 'member_passwd') continue;

			$tmps			= explode("|", $v2[0]);
			if(count($tmps) == 1)		$SMENU_CHANNEL	= $tmps[0];
			else if(count($tmps) == 2)	$SMENU_CHANNEL	= $tmps[0]."&b_id=".$tmps[1];			
			$SMENU_NAME		= $v2[1];
			
			$ck_sec = 0;
			if($channel == 'cs_board') {				
				if($channel == $tmps[0] && $tmps_b_id == $tmps[1]) $ck_sec = 1;					
			}
			else if($channel == $tmps[0]) $ck_sec = 1;

			if($ck_sec == 1) {
				$SELECT_MENU_NAME1	= $v[1];
				$SELECT_MENU_NAME	= $v2[1];
				$tpl->parse("is_sub_menu_name1");
			}
			else $SMENU_SELECT = "";		
			
			$tpl->parse("loop_sub_menu_sub");
		}
	}
	else {
		if($channel == 'cs_board') {
			if($channel == $tmps[0] && $tmps_b_id == $tmps[1]) $SELECT_MENU_NAME	= $v[1];
		}
		else if($channel == $v[0]) $SELECT_MENU_NAME	= $v[1];		
	}

	$tpl->parse("loop_sub_menu");
}

$MASTER_MENU_CHANNEL	= $channel_group[0];
$MASTER_MENU_NAME		= $channel_group[1];

if($channel == 'cs_board' && !isset($SELECT_MENU_NAME)) {
	$b_id				= checkGetVar('b_id');
	$sql				= "SELECT name FROM mallRN_board_manager WHERE id = '{$b_id}'";
	$SELECT_MENU_NAME	= stripslashes($mysql->get_one($sql));
}

if($channel != 'cs_center' && $channel != 'mypage') $tpl->parse("is_sub_menu_name0");



unset($tmps, $tmps_b_id);

$tpl->parse("main");
$SUB_MENUS = $tpl->tprint("main", 1);
$tpl->close();

?>