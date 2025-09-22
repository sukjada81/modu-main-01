<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

if($vendor) {

	$referer = isset($_GET['referer']) ? urldecode($_GET['referer']) : ((isset($_SERVER['HTTP_REFERER'])) ? urldecode($_SERVER['HTTP_REFERER']) : "즐겨찾기, 주소창에 직접입력");

	if(!preg_match('/^((http(s?))\:\/\/)$/', $referer)) {
		$referer_site	= "즐겨찾기, 직접입력";
	}
	else {
		$tmps			= preg_replace('/^((http(s?))\:\/\/)$/', '', $referer);
		$tmps			= explode("/", $tmps);
		$referer_site	= str_replace("www.", "", $tmp[0]);
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

	$sql = "SELECT check_date FROM mallRN_store_count_referer WHERE vendor = '{$vendor}' ORDER BY uid DESC LIMIT 1";
	$data = $mysql->one_row($sql);

	$sql = "SELECT count(*) FROM mallRN_store_count_referer WHERE vendor = '{$vendor}' && check_ip = '{$access_ip}' && check_date = '{$check_date}'";
	if($mysql->get_one($sql) == 0) {  
	   
		################ 접속자수 카운터 ################
		$sql = "SELECT count(*) FROM mallRN_store_count_list WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '0'";
		if($mysql->get_one($sql) == 1) {	   
			$sql = "UPDATE mallRN_store_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '0'";
		} 
		else {
			$sql = "INSERT INTO mallRN_store_count_list (vendor, type, year, month, day, week, {$mobile}h_{$TIME}, {$mobile}total) VALUES('{$vendor}', '0', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";
		}
		$mysql->query($sql);

		$sql = "SELECT count(*) FROM mallRN_store_count_referer WHERE vendor = '{$vendor}' && check_ip = '{$access_ip}' && check_date >= '".date("Ymd", strtotime('-1 MONTH', time()))."'";
		if($mysql->get_one($sql) == 0) {  //신규방문자
			$sql = "SELECT count(*) FROM mallRN_store_count_list WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '3'";
			if($mysql->get_one($sql) == 1) {	   
				$sql = "UPDATE mallRN_store_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '3'";
			} 
			else {
				$sql = "INSERT INTO mallRN_store_count_list (vendor, type, year, month, day, week, h_{$TIME}, total) VALUES('{$vendor}', '3', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";
			}
			$mysql->query($sql);		
		}
		else { // 재방문자
			$sql = "SELECT count(*) FROM mallRN_store_count_list WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '2'";
			if($mysql->get_one($sql) == 1) {	   
				$sql = "UPDATE mallRN_store_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '2'";
			} 
			else {
				$sql = "INSERT INTO mallRN_store_count_list (vendor, type, year, month, day, week, h_{$TIME}, total) VALUES('{$vendor}', '2', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";
			}
			$mysql->query($sql);
		}
		################ 접속자수 카운터 ################

		################ 브라우저 카운터 ################
		$sql = "SELECT count(*) FROM mallRN_store_count_browser WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_browser}'";  
		if($mysql->get_one($sql) == 0) {	   
			$sql = "INSERT INTO mallRN_store_count_browser (vendor, year, month, day, content, count) VALUES('{$vendor}', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_browser}', '1')";
		} 
		else {
			$sql = "UPDATE mallRN_store_count_browser SET count = count + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_browser}'";	   	   
		}
		$mysql->query($sql);
		################ 브라우저 카운터 ################

		################ OS 카운터 ################
		$sql = "SELECT count(*) FROM mallRN_store_count_os WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_os}'";  
		if($mysql->get_one($sql) == 0) {	   
			$sql = "INSERT INTO mallRN_store_count_os (vendor, year, month, day, content, count) VALUES('{$vendor}', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_os}', '1')";
		} 
		else {
			$sql = "UPDATE mallRN_store_count_os SET count = count + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_os}'";	   	   
		}
		$mysql->query($sql);
		################ OS 카운터 ################

		################ 사이트 카운터 ################
		$sql = "SELECT count(*) FROM mallRN_store_count_site WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_site}'";  
		if($mysql->get_one($sql) == 0) {	   
			$sql = "INSERT INTO mallRN_store_count_site (vendor, year, month, day, content, count) VALUES('{$vendor}', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_site}', '1')";
		} 
		else {
			$sql = "UPDATE mallRN_store_count_site SET count = count + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_site}'";	   	   
		}
		$mysql->query($sql);
		################ 사이트 카운터 ################

		################ 검색어 카운터 ################
		$check_keword	= array('/query=/i', '/q=/i', '/p=/i');
		$tmps			= preg_replace($check_keword, '||', $referer);
		$tmps			= explode("||", $tmps);
		if(isset($tmps[1])) {
			$tmps = explode("&", $tmps[1]);
			$referer_keyword = (mb_check_encoding($tmps[0], "utf-8")==true) ? $tmps[0] : iconv('euc-kr', 'utf-8', $tmps[0]);
		}
		else $referer_keyword = "";
		
		if($referer_keyword) {
			$sql = "SELECT count(*) FROM mallRN_store_count_keyword WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_keyword}'";  
			if($mysql->get_one($sql) == 0) {	   
				$sql = "INSERT INTO mallRN_store_count_keyword (vendor, year, month, day, content, count) VALUES('{$vendor}', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$referer_keyword}', '1')";
			} 
			else {
				$sql = "UPDATE mallRN_store_count_keyword SET count = count + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && content = '{$referer_keyword}'";	   	   
			}
			$mysql->query($sql);
		}
		################ 검색어 카운터 ################
		
		################ 접속경로 저장 ################
		$sql = "INSERT INTO mallRN_store_count_referer (vendor, referer, os, browser, check_ip, check_date, signdate)  VALUES('{$vendor}', '{$referer}', '{$referer_os}', '{$referer_browser}', '{$access_ip}', '{$check_date}', '{$signdate}')";
		$mysql->query($sql);  
		################ 접속경로 저장 ################
	}

	################ 페이지뷰 카운터 ################
	$sql = "SELECT count(*) FROM mallRN_store_count_list WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '1'";
	if($mysql->get_one($sql) == 1) {	   
		$sql = "UPDATE mallRN_store_count_list SET {$mobile}h_{$TIME} = {$mobile}h_{$TIME} + 1, {$mobile}total = {$mobile}total + 1 WHERE vendor = '{$vendor}' && year = '{$YEAR}' && month = '{$MONTH}' && day = '{$DAY}' && type = '1'";
	} 
	else {
		$sql = "INSERT INTO mallRN_store_count_list (vendor, type, year, month, day, week, h_{$TIME}, total) VALUES('{$vendor}', '1', '{$YEAR}', '{$MONTH}', '{$DAY}', '{$WEEK}', '1', '1')";
	}
	$mysql->query($sql);
	################ 페이지뷰 카운터 ################

	unset($YEAR, $MONTH, $DAY, $TIME, $check_date, $referer_site, $referer_browser, $referer_os, $referer_keyword, $mobile);
}

?>