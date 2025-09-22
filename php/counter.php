<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

$referer	= isset($_GET['referer']) ? urldecode($_GET['referer']) : ((isset($_SERVER['HTTP_REFERER'])) ? urldecode($_SERVER['HTTP_REFERER']) : "즐겨찾기, 주소창에 직접입력");
$referer	= add_escape_string($referer);

if(!preg_match('/(http(s)?:\/\/)/i', $referer)) {
	$referer_site	= "즐겨찾기, 직접입력";
}
else {
	$tmps			= preg_replace('/(http(s)?:\/\/)/i', '', $referer);
	$tmps			= explode("/", $tmps);
    $referer_site	= str_replace("www.", "", $tmps[0]);
	unset($tmps);
}

if(isset($_SERVER['HTTP_USER_AGENT'])) {
	$user_agent			= analyse_user_agent($_SERVER["HTTP_USER_AGENT"]);
	$version			= isset($user_agent['os']['version']) ? $user_agent['os']['version'] : "";
	$referer_os			= $user_agent['os']['name']." ".$version;
	$referer_browser	= $user_agent['browser']['name'];
	if($user_agent['is_bot'] == 1) $referer_browser .= "[Bot]";
}
else {
	$referer_os			= "unknown";
	$referer_browser	= "unknown";
}

$YEAR		= date('Y');     
$MONTH		= date('m');
$DAY		= date('d');
$TIME		= date('H');
$WEEK		= date('w');
$check_date	= date('Ymd');
$signdate	= time();
$mobile		= $is_mobile ? "m" : "";

$sql = "SELECT check_date FROM mallRN_count_referer ORDER BY uid DESC LIMIT 1";
$data = $mysql->one_row($sql);

if((!@$data['check_date']) || @$data['check_date'] < $check_date) { 
	socketPost(ABSOLUTE_PATH_SHOP."php/async_day_proc.php?param=".previlEncode($data['check_date']), 'POST', 0); //비동기 실행
	if($shop_config['order_tracker_yn'] == 'Y') {
		socketPost(ABSOLUTE_PATH_SHOP."php/async_tracker.php?param=".previlEncode($data['check_date']), 'POST', 0); //비동기 실행
	}
	socketPost(ABSOLUTE_PATH_SHOP."php/async_patch.php", 'POST', 0); //비동기 실행
}	

$sql = "SELECT count(*) FROM mallRN_count_referer WHERE check_ip = '{$access_ip}' && check_date = '{$check_date}'";
if($mysql->get_one($sql) == 0) {  
   
    ################ 접속자수 카운터 ################
    $sql = "SELECT count(*) FROM mallRN_count_list WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '0'";
    if($mysql->get_one($sql) == 0) {	   
	    $sql = "INSERT INTO mallRN_count_list (type, year, month, day, week, {$mobile}h_{$TIME}, {$mobile}total) VALUES('0', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";		
    } 
	else {
		$sql = "UPDATE mallRN_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '0'";	    
    }
    $mysql->query($sql);

	$sql = "SELECT count(*) FROM mallRN_count_referer WHERE check_ip = '{$access_ip}' && check_date >= '".date("Ymd", strtotime('-1 MONTH', time()))."'";
	if($mysql->get_one($sql) == 0) {  //신규방문자
		$sql = "SELECT count(*) FROM mallRN_count_list WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '3'";
		if($mysql->get_one($sql) == 0) {	   
			// [권장 수정] 신규(type=3) INSERT 시도 모바일/PC 컬럼을 일관 적용하려면
			//   현재: h_{$TIME}, total
			//   권장: {$mobile}h_{$TIME}, {$mobile}total
			$sql = "INSERT INTO mallRN_count_list (type, year, month, day, week, h_{$TIME}, total) VALUES('3', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";			
		} 
		else {
			$sql = "UPDATE mallRN_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '3'";			
		}
		$mysql->query($sql);		
	}
	else { // 재방문자
		$sql = "SELECT count(*) FROM mallRN_count_list WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '2'";
		if($mysql->get_one($sql) == 0) {	   
			// [권장 수정] 재방문(type=2) INSERT도 모바일/PC 컬럼 일관 적용 권장
			//   현재: h_{$TIME}, total
			//   권장: {$mobile}h_{$TIME}, {$mobile}total
			$sql = "INSERT INTO mallRN_count_list (type, year, month, day, week, h_{$TIME}, total) VALUES('2', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";			
		} 
		else {
			$sql = "UPDATE mallRN_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '2'";			
		}
		$mysql->query($sql);
	}
	################ 접속자수 카운터 ################

	################ 브라우저 카운터 ################
    $sql = "SELECT count(*) FROM mallRN_count_browser WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_browser}'";  
    if($mysql->get_one($sql) == 0) {	   
	    $sql = "INSERT INTO mallRN_count_browser (year, month, day, content, count) VALUES('{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_browser}', '1')";
    } 
	else {
	    $sql = "UPDATE mallRN_count_browser SET count = count + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_browser}'";	   	   
    }
    $mysql->query($sql);
	################ 브라우저 카운터 ################

	################ OS 카운터 ################
    $sql = "SELECT count(*) FROM mallRN_count_os WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_os}'";  
    if($mysql->get_one($sql) == 0) {	   
	    $sql = "INSERT INTO mallRN_count_os (year, month, day, content, count) VALUES('{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_os}', '1')";
    } 
	else {
	    $sql = "UPDATE mallRN_count_os SET count = count + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_os}'";	   	   
    }
    $mysql->query($sql);
	################ OS 카운터 ################

	################ 사이트 카운터 ################
    $sql = "SELECT count(*) FROM mallRN_count_site WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_site}'";  
    if($mysql->get_one($sql) == 0) {	   
	    $sql = "INSERT INTO mallRN_count_site (year, month, day, content, count) VALUES('{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_site}', '1')";
    } 
	else {
	    $sql = "UPDATE mallRN_count_site SET count = count + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_site}'";	   	   
    }
    $mysql->query($sql);
	################ 사이트 카운터 ################

	################ 검색어 카운터 ################
	$tmps			= preg_replace('/oquery=/i', '', $referer);
	$check_keword	= array('/query=/i', '/q=/i', '/p=/i');
	$tmps			= preg_replace($check_keword, '||', $tmps);
	$tmps			= explode("||", $tmps);
	$referer_keyword = "";
	if(isset($tmps[1])) {
		$tmps2 = explode("&", $tmps[1]);
		$referer_keyword = (mb_check_encoding($tmps2[0], "utf-8")==true) ? $tmps2[0] : iconv('euc-kr', 'utf-8', $tmps2[0]);
		
		if(!$referer_keyword) {		
			if(isset($tmps[2])) {
				$tmps2 = explode("&", $tmps2);
				$referer_keyword = (mb_check_encoding($tmps2[0], "utf-8")==true) ? $tmps2[0] : iconv('euc-kr', 'utf-8', $tmps2[0]);			
			}
		}
	}
	
	if($referer_keyword) {
		$sql = "SELECT count(*) FROM mallRN_count_keyword WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_keyword}'";  
		if($mysql->get_one($sql) == 0) {	   
			$sql = "INSERT INTO mallRN_count_keyword (year, month, day, content, count) VALUES('{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_keyword}', '1')";
		} 
		else {
			$sql = "UPDATE mallRN_count_keyword SET count = count + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_keyword}'";	   	   
		}
		$mysql->query($sql);
	}
	################ 검색어 카운터 ################
	
	################ 접속경로 저장 ################
	$sql = "INSERT INTO mallRN_count_referer (referer, os, browser, check_ip, check_date, signdate)  VALUES('{$referer}', '{$referer_os}', '{$referer_browser}', '{$access_ip}', '{$check_date}', '{$signdate}')";
	$mysql->query($sql);  
	################ 접속경로 저장 ################
}

################ 페이지뷰 카운터 ################
$sql = "SELECT count(*) FROM mallRN_count_list WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '1'";
if($mysql->get_one($sql) == 0) {	   
	// [권장 수정] 페이지뷰(type=1) INSERT 시도 모바일/PC 컬럼을 일관 적용하려면
	//   현재: h_{$TIME}, total
	//   권장: {$mobile}h_{$TIME}, {$mobile}total
	$sql = "INSERT INTO mallRN_count_list (type, year, month, day, week, h_{$TIME}, total) VALUES('1', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";	
} 
else {
	$sql = "UPDATE mallRN_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '1'";	
}
$mysql->query($sql);
################ 페이지뷰 카운터 ################

unset($YEAR, $MONTH, $DAY, $TIME, $check_date, $referer_site, $referer_browser, $referer_os, $referer_keyword, $mobile);

?>
