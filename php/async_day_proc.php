<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');

include_once('init.php');

$referer	= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$access_ip	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$param		= checkPostVar('param');

if(!$referer || !$access_ip || $access_ip != $_SERVER['SERVER_ADDR'] || !$param)	{
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

$param		= previlDecode($param);
if($param != date('Ymd', time() - 86400)) {	
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

$sql		= "SELECT check_proc FROM mallRN_count_referer WHERE check_date = '{$param}'";
$check_proc	= $mysql->get_one($sql);
if(strlen($check_proc) == 0 || $check_proc == 1) {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

############ 생일 축하쿠폰 발급 ############
$sql = "SELECT count(*) FROM mallRN_coupon_manager WHERE type = 3";
if($mysql->get_one($sql) > 0) {

	$lunarFile = file(DEFAULT_PATH.PATH_LIB.'/lunar.txt');
	foreach ($lunarFile as $lunar_num => $lunar) {
		if(strstr($lunar, "'".date("Y-n-j")."',")) {
			$lunars = str_replace("'".date("Y-n-j")."',", "", $lunar);
			$lunars = str_replace("'","",$lunars);
			$LUNARS = date("md", strtotime($lunars));			
			break;
		}    
	}
	unset($lunarFile, $lunar_num, $lunar, $lunars);

	$BIRTH		= date("md");
	$CK_DATE	= strtotime(date("Y")."-01-01 00:00:00");

	$sql = "SELECT id FROM mallRN_member WHERE (SUBSTRING(birth, 5, 4) = '{$BIRTH}' && birth_sl = 'S') || (SUBSTRING(birth, 5, 4) = '{$LUNARS}' && birth_sl = 'L')";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()) {
		
		$sql = "SELECT * FROM mallRN_coupon_manager WHERE type = 3";
		$mysql->query($sql);

		while($row2 = $mysql->fetch_array()) {
			
			$sql = "SELECT count(*) FROM mallRN_coupon WHERE id = '{$row['id']}' && c_uid = '{$row2['uid']}' && signdate > {$CK_DATE}";
			if($mysql->get_one($sql) == 0) {
				couponIssuance($row2['uid'], $row['id']);
			}
		}
	}
	unset($CK_DATE, $BIRTH);
}							
############ 생일 축하쿠폰 발급 ############

################ 쿠폰 만료 처리 ################
$sql = "SELECT uid, id FROM mallRN_coupon WHERE status = 0 && e_date < '".date("Y-m-d 23:59:59")."'";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$sql = "UPDATE mallRN_coupon SET status = 2 WHERE uid = '{$row['uid']}'";
	$mysql->query2($sql);
}
################ 쿠폰 만료 처리 ################

################ 마일리지 유효기간 만료 처리 ################
$sql = "SELECT uid, id, mileage, proc_mileage, expired_date FROM mallRN_mileage WHERE expired_use = 1 && expired = 0 && expired_date < '".date("Y-m-d")."'";
$mysql->query($sql);

$signdate	= time();
while($row = $mysql->fetch_array()) {
	$sql = "UPDATE mallRN_mileage SET expired = 1 WHERE uid = '{$row['uid']}'";
	$mysql->query2($sql);
	
	$check_mileage = $row['mileage'] - $row['proc_mileage'];
	if($check_mileage > 0) {
		$sql = "INSERT INTO mallRN_mileage SET id = '{$row['id']}', content = '유효기간경과 마일리지 소멸 ({$row['expired_date']})', use_mileage = '{$check_mileage}', expired = 1, signdate = '{$signdate}'";
		$mysql->query2($sql);
	}

	mileageChange($row['id']);
}
################ 마일리지 유효기간 만료 처리 ################

################ 모음전 기간체크(진행중, 종료) ################
$sql = "UPDATE mallRN_exhibition SET status = 2 WHERE discount_yn = 'Y' && status = 1 && s_date > '".date("Y-m-d")."'";
$mysql->query($sql);

$sql = "UPDATE mallRN_exhibition SET status = 3 WHERE discount_yn = 'Y' && status = 2 && e_date  < '".date("Y-m-d 23:59:59")."'";
$mysql->query($sql);	
################ 모음전 기간체크(진행중, 종료) ################

################ 1달지난 최근 검색어 삭제(고객용) ################
$sql = "DELETE FROM mallRN_keyword_recent WHERE signdate < ".strtotime('-1 MONTH');
$mysql->query($sql);
################ 1달지난 최근 검색어 삭제 ################

################ 6달지난 최근 검색어 삭제(관리자용) ################
$sql = "DELETE FROM mallRN_keyword_recent WHERE signdate < ".strtotime('-6 MONTH');
$mysql->query($sql);
################ 6달지난 최근 검색어 삭제 ################

################ 1년지난 상품 카운트 삭제 ################
$sql = "DELETE FROM mallRN_goods_view WHERE signdate < ".strtotime('-1 YEAR');
$mysql->query($sql);

$dates = date("Y-m-d", strtotime('-1 DAY'));
$sql = "SELECT COUNT(uid) as sum, g_uid FROM mallRN_goods_view WHERE from_unixtime(signdate) BETWEEN '{$dates}' AND '{$dates} 23:59:59' GROUP BY g_uid ORDER BY sum DESC, g_uid ASC LIMIT 0, 100";
$mysql->query($sql);
$uid_array = array();
while($row = $mysql->fetch_array()) {
	$uid_array[] = $row['g_uid'];
}
$where = " && g_uid NOT IN (".join(", ", $uid_array).")";
$sql = "DELETE FROM mallRN_goods_view WHERE from_unixtime(signdate) BETWEEN '{$dates}' AND '{$dates} 23:59:59' {$where}";
$mysql->query($sql);
################ 1년지난 상품 카운트 삭제 ################

################ 1달지난 최근본상품 삭제 ################
$sql = "DELETE FROM mallRN_goods_recent_view WHERE signdate < ".strtotime('-1 MONTH');
$mysql->query($sql);
################ 1달지난 최근본상품 삭제 ################

################ 1달지난 디비오류로그 삭제 ################
$sql = "DELETE FROM mallRN_db_error_log WHERE signdate < ".strtotime('-1 MONTH');
$mysql->query($sql);
################ 1달지난 디비오류로그 삭제 ################

################ 1년지난 관심상품 삭제 ################
$sql = "DELETE FROM mallRN_favorite_goods WHERE signdate < ".strtotime('-1 YEAR');
$mysql->query($sql);
################ 1년지지난 관심상품 삭제 ################

################ 1년지난 동시구매상품정보 삭제 ################
$sql = "DELETE FROM mallRN_order_related_goods WHERE signdate < ".strtotime('-1 YEAR');
$mysql->query($sql);
################ 1년지난 동시구매상품정보 삭제 ################

################ 6달지난 접속경로 삭제 ################
$sql = "DELETE FROM mallRN_count_referer WHERE check_date < ".date("Ymd", strtotime('-6 MONTH'));
$mysql->query($sql);

$sql = "DELETE FROM mallRN_store_count_referer WHERE check_date < ".date("Ymd", strtotime('-6 MONTH'));
$mysql->query($sql);
################ 6달지난 접속경로 삭제 ################

################ 6달지난 SMS발송내역 삭제 ################
$sql = "DELETE FROM mallRN_sms_list WHERE signdate < ".strtotime('-6 MONTH');
$mysql->query($sql);
################ 6달지난 SMS발송내역 삭제 ################

################ 2년지난 개인정보 접속로그 삭제 ################
$sql = "DELETE FROM mallRN_admin_log WHERE signdate < ".strtotime('-2 YEAR');
$mysql->query($sql);

$sql = "DELETE FROM mallRN_vendor_log WHERE signdate < ".strtotime('-2 YEAR');
$mysql->query($sql);
################ 2년지난 개인정보 접속로그 삭제 ################

################ 자동배송완료 처리 ################
if($shop_config['order_tracker_yn'] == 'N' && $shop_config['order_auto_completed1'] > 0) {
	$sql = "SELECT uid, order_num FROM mallRN_order_goods WHERE reals = 1 && (status = 3 || (status = 7 && status2 = 4)) && status_date < ".strtotime('-'.$shop_config['order_auto_completed1'].' DAY');
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		orderStatus4($row['order_num'], $row['uid'], 'auto');
	}
}
################ 자동배송완료 처리 ################

################ 스마트택배 API연동시 3달이 지난 로그기록 삭제 처리 ################
if($shop_config['order_tracker_yn'] == 'Y') {
	$sql = "DELETE FROM mallRN_delivery_api_log WHERE signdate < ".strtotime('-3 MONTH');
	$mysql->query($sql);
}
################ 스마트택배 API연동시 3달이 지난 로그기록 삭제 처리 ################

################ 자동구매확정 처리 ################
if($shop_config['order_auto_completed2'] > 0) {
	$sql = "SELECT uid, order_num FROM mallRN_order_goods WHERE reals = 1 && status = 4 && status_date < ".strtotime('-'.$shop_config['order_auto_completed2'].' DAY');
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		orderStatus5($row['order_num'], $row['uid'], 'auto');
	}
}
################ 자동배송완료 처리 ################

################ 자동주문취소 처리 ################
if($shop_config['order_auto_completed3'] > 0) {
	$sql = "SELECT order_num FROM mallRN_order_info WHERE pay_status = 'C' && signdate < ".strtotime('-'.$shop_config['order_auto_completed3'].' DAY');
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		$sql = "SELECT count(*) FROM mallRN_order_goods WHERE reals = 1 &&  order_num = '{$row['order_num']}' && status > 0 && status < 9";
		if($mysql->get_one($sql) == 0) {
			orderStatus9($row['order_num'], 'auto');
		}			
	}
}
################ 자동주문취소 처리 ################

################ 임시주문 삭제 ################
$sql = "DELETE FROM  mallRN_order_info WHERE reals = 0 && signdate < ".strtotime('-3 DAY');
$mysql->query($sql);

$sql = "DELETE FROM  mallRN_order_goods WHERE reals = 0 && signdate < ".strtotime('-3 DAY');
$mysql->query($sql);
################ 임시주문 삭제 ################

################ 상품등록 임시폴더 삭제 ################
define('UPLOAD_FOLDER', '../image/temp_upload');

$check_dir	= UPLOAD_FOLDER."/";

$handle = @opendir($check_dir);
while ($tmps = readdir($handle)) {	
	if(!preg_match("/\./i",$tmps)) {
		if(strlen(substr($tmps, 0, 8)) == 8 && substr($tmps, 0, 8) < date("Ymd", time() - (3600 * 24))) {
			delTree($check_dir.$tmps);
		}
	}
}
@closedir($handle);
unset($check_dir, $tmps);

define('SN_INFO_FOLDER', '../image/sn_upload/information_use/goods');

$check_dir	= SN_INFO_FOLDER."/";

$handle = @opendir($check_dir);
while ($tmps = readdir($handle)) {	
	if(!preg_match("/\./i",$tmps)) {
		if(strlen(substr($tmps, 0, 8)) == 8 && substr($tmps, 0, 8) < date("Ymd", time() - (3600 * 24))) {
			delTree($check_dir.$tmps);
		}
	}
}
@closedir($handle);
unset($check_dir, $tmps);
################ 상품등록 임시폴더 삭제 ################

################ 휴먼회원 1달전 메일발송 ################
$sql			= "SELECT id, email FROM mallRN_member WHERE (login_time > 0 && INSTR(from_unixtime(login_time), '".date("Y-m-d", strtotime('-335 DAY'))."')) || (login_time = 0 && INSTR(from_unixtime(signdate), '".date("Y-m-d", strtotime('-335 DAY'))."'))";
$mysql->query($sql);

$sql			= "SELECT content FROM mallRN_auto_mail WHERE type = 'sleep'";
$content_orig	= stripslashes($mysql->get_one($sql));
$content_orig	= str_replace("{SHOPNAME}",	stripslashes($shop_config['basic_name']),	$content_orig);
$content_orig	= str_replace("{SLP_DATE}",	date("Y년 m월 d일", strtotime('+30 DAY')),	$content_orig);

while($row = $mysql->fetch_array()) {		
	if(!$row['email']) continue;
	$content	= str_replace("{ID}", $row['id'], $content_orig);	
	mallMailSend($row['email'], stripslashes($shop_config['basic_name'])." 휴면회원전환 사전안내를 드립니다.", $content);
}
################ 휴먼회원 1달전 메일발송 ################

################ 휴먼회원 전환 ################
$signdate		= time();
$sql			= "SELECT uid FROM mallRN_member WHERE (login_time > 0 && INSTR(from_unixtime(login_time), '".date("Y-m-d", strtotime('-365 DAY'))."')) || (login_time = 0 && INSTR(from_unixtime(signdate), '".date("Y-m-d", strtotime('-365 DAY'))."'))";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {	
	$sql	= "INSERT INTO mallRN_member_sleep (SELECT * FROM mallRN_member WHERE uid = '{$row['uid']}')";
	$mysql->query2($sql);

	$sql	= "UPDATE mallRN_member_sleep SET sleep_time = '{$signdate}' WHERE uid = '{$row['uid']}'";
	$mysql->query2($sql);

	$sql	= "DELETE FROM mallRN_member WHERE uid = '{$row['uid']}'";
	$mysql->query2($sql);
}
################ 휴먼회원 전환 ################

$sql = "UPDATE mallRN_count_referer SET check_proc = 1 WHERE check_date = '{$param}'";
$mysql->query($sql);

?>
