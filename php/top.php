<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}top.html");
$tpl->scan_area("main");

$access_ip	= $_SERVER['REMOTE_ADDR'];

######################## 최근 검색어 저장 & 출력 #############################
if($my_id) $where = "&& (a.id='{$my_id}' || a.ip='{$access_ip}')";
else $where = "&& a.ip='{$access_ip}'";

if($channel == 'search' && !isset($_GET['page'])) {

	$keyword		= isset($_POST['keyword']) ?  urldecode(trim($_POST['keyword'])) :  ((isset($_GET['keyword'])) ?  urldecode(trim($_GET['keyword'])) : '');
	if(strlen($keyword) == 0) alert("검색어를 입력 하시기 바랍니다.", "back");
	
	$sql = "SELECT count(*) FROM mallRN_keyword_recent a WHERE a.keyword = '{$keyword}' {$where}";	
	if($mysql->get_one($sql) == 0) {		
		$sql = "INSERT INTO mallRN_keyword_recent (keyword, id, ip, signdate) VALUES('{$keyword}', '{$my_id}', '{$access_ip}', ".time().")";	
		$mysql->query($sql);

		$sql = "INSERT INTO mallRN_keyword_recent2 (keyword, id, ip, signdate) VALUES('{$keyword}', '{$my_id}', '{$access_ip}', ".time().")";	
		$mysql->query($sql);
	}	
}

$check_time	= time() - (86400 * 3);

$sql = "SELECT c.uid, c.keyword FROM ( SELECT a.uid FROM mallRN_keyword_recent a WHERE a.signdate > {$check_time} {$where} ) b JOIN mallRN_keyword_recent c ON b.uid = c.uid ORDER BY c.uid DESC LIMIT 10";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$recent_uid		= $row['uid'];
	$recent_keyword = specialStrReplace2($row['keyword']);

	$tpl->parse("loop_recent_keyword");
}

unset($where, $check_time);
######################## 최근 검색어 저장 & 출력 #############################

######################## 추천 검색어 출력 #############################
$check_date		= date("Y-m-d", time() - (86400 * 3));
$sql = "SELECT keyword FROM mallRN_keyword_search WHERE date >= '{$check_date}' ORDER BY count DESC LIMIT 10";
$mysql->query($sql);

$ck_cnt = 0;
while($row = $mysql->fetch_array()) {
	$best_keyword = specialStrReplace2($row['keyword']);

	$tpl->parse("loop_best_keyword");
	$ck_cnt++;
}

if($ck_cnt < 10) {	
	$basic_keyword = explode(",", $shop_config['basic_real_keyword']);

	foreach($basic_keyword as $k => $v) {
		if($v) {
			$best_keyword = specialStrReplace2($v);	

			$tpl->parse("loop_best_keyword");
			$ck_cnt++;
		}

		if($ck_cnt == 9) break;
	}
}
unset($basic_keyword, $check_time, $k, $v);
######################## 추천 검색어 출력 #############################

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

if($my_id) {
	$tpl->parse("is_login");
	@$tpl->parse("is_login2");
}
else {
	$my_reserve = 0;

	if(!isset($member_config)) {
		$sql = "SELECT member_mileage_yn, member_mileage_join FROM mallRN_configuration WHERE uid = 2";
		$member_config = $mysql->one_row($sql);		
	}
	
	if($member_config['member_mileage_yn'] == 'Y' && $member_config['member_mileage_join'] > 0) {   // 회원가입시 지급 마일리지
		$JPOINT = number_format($member_config['member_mileage_join'], CONF_FLOAT_CNT);
		@$tpl->parse("is_join_point");
	}

	$tpl->parse("is_logout");
	@$tpl->parse("is_logout2");
}

if($channel == "main") @$tpl->parse("is_main");

commonBannerCheck('top');

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>