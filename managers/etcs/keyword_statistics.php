<?php 

$reset = isset($_POST['reset']) ? $_POST['reset'] : '';

if($reset==1) {
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");

	include_once('../common/ad_init.php');
	include_once(PATH_LIB.'/class.Template.php');  

	$mysql->msgType(2);

	$my_array = array();
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
}
else include_once("../common/top.php");
include_once(PATH_LIB.'/class.ListPaging.php');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","keyword_statistics.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
if(!isset($_GET['sort'])) $_GET['sort'] = "count DESC";
$sort		= "";
$lastdate	= 1;
$this_year	= date("Y");
$this_month = date("m");
$this_day	= date("d");
$DATE1		= date("Y-m-d");
$DATE2		= date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3		= date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4		= date('Y-m-d', strtotime('-1 MONTH', time()));

$sql = "SELECT date FROM mallRN_keyword_search ORDER BY uid ASC LIMIT 1";
if($tmp = $mysql->get_one($sql)){
	$start_year = substr($tmp, 0, 4);
}
else $start_year = $this_year;
######################## 변수 정의 #############################

######################## listPaging 정의 #############################
$listPaging =					new listPaging('mallRN_keyword_search'); 
$listPaging->search_variable	= array('mode' => 5, 'year' => 5, 'month' => 5, 'week' => 5, 'day' => 5, 's_date' => 5, 'e_date' => 5, 'sort' => 0, 'keyword_total' => 0, 'keyword_max' => 0, 'limit' => 0, 'page' => 0, 'atotal_record' => 0, 'total_record' => 0);
$listPaging->list_variable		= array('uid' => '', 'keyword' => '', 'count' => 'number', 'per' => 'function|count', 'width' => 'function|count');
######################## listPaging 정의 #############################

######################## list_variable : funtion일 경우 정의 #############################
$per_function = function ($vls, $count) {	
	if($GLOBALS['keyword_total'] == 0) return 0;
	return number_format((100 * $count) / $GLOBALS['keyword_total'], 2);
};

$width_function = function ($vls, $count) {	
	if($GLOBALS['keyword_max'] == 0) return 0;
	return number_format((100 * $count) / $GLOBALS['keyword_max'], 2);
};
######################## list_variable : funtion일 경우 정의 #############################

######################## listPaging 파라미터 및 검색조건 처리 #############################
$listPaging->make_string();
if($atotal_record)	$listPaging->atotal_record = $atotal_record;
if($total_record)	$listPaging->total_record = $total_record;

if(!$year)	$year	= $this_year;
if(!$month) $month	= $this_month;
if(!$day)	$day	= $this_day;
if(!$mode)	$mode	= "day";
$last_day			= date('t', strtotime("{$year}-{$month}"));

${"select_".$mode} = "selected";

if($mode == 'year' || $mode == 'month' || $mode == 'week' || $mode == 'day') {
	for($i = $start_year; $i <= $this_year; $i ++) {
		$tpl->parse("loop_year1");
		$tpl->parse("loop_year2");
	}
	$tpl->parse("is_year1");
	$tpl->parse("is_year2");
}

if($mode == 'month' || $mode == 'week' || $mode == 'day') {
	for($i = 1; $i < 13; $i ++) {
		$i2 = sprintf("%02d", $i);
		$tpl->parse("loop_month1");
		$tpl->parse("loop_month2");
	}
	$tpl->parse("is_month1");
	$tpl->parse("is_month2");
}

if($mode == 'day') {
	for($i = 1; $i <= $last_day; $i ++) {
		$i2 = sprintf("%02d", $i);
		$tpl->parse("loop_day1");
		$tpl->parse("loop_day2");
	}
	$tpl->parse("is_day1");
	$tpl->parse("is_day2");
}

if($mode == 'week') {
	for($i = 1, $cnt = 1; $i <= $last_day; $i+=7) {
		$i2			= sprintf("%02d", $i);
		$w			= date("w", strtotime("{$year}-{$month}-{$i2}"));
		$start_wday = date("Y-m-d", strtotime("-".$w." DAY", strtotime("{$year}-{$month}-{$i2}")));
		$end_wday	= date("Y-m-d", strtotime("+6 DAY", strtotime($start_wday)));
		$DATES		= "{$start_wday} ~ {$end_wday}";
		if(!$week) {	
			if($year == $this_year && $month == $this_month) {
				if($start_wday <= "{$year}-{$month}-{$this_day}" && $end_wday >= "{$year}-{$month}-{$this_day}") $week = $DATES;
			}
			else $week = $DATES;
		}
		$tpl->parse("loop_week1");
		$tpl->parse("loop_week2");
		$cnt++;
	}
	$tpl->parse("is_week1");
	$tpl->parse("is_week2");
}
unset($cnt, $w, $last_dat, $start_wday, $end_wday);

switch($mode) {
	case "year" :
		$where = "INSTR(date, '{$year}')";		
	break;
	case "month" :
		$where = "INSTR(date, '{$year}-{$month}')";
	break;
	case "week" :
		$week_arr = explode(" ~ ", $week);
		$where = "date BETWEEN '{$week_arr[0]}' AND '{$week_arr[1]} 23:59:59'";
	break;
	case "day" :
		$where = "INSTR(date, '{$year}-{$month}-{$day}')";
	break;
	case "detail" :
		if(!$e_date) {
			$e_date = $DATE1;	
			if(!$s_date) $s_date = $DATE1;	
		}
		if(!$s_date) $where = "date < '{$e_date} 23:59:59'";		
		$where = "date BETWEEN '{$s_date}' AND '{$e_date} 23:59:59'";

		$tpl->parse("is_detail1");
		$tpl->parse("is_detail2");
	break;
}

if(!$keyword_total) {
	$sql = "SELECT SUM(count) FROM mallRN_keyword_search WHERE {$where}";
	if(!$keyword_total = $mysql->get_one($sql)) $keyword_total = 0;
}

if(!$keyword_max) {
	$sql = "SELECT MAX(b.count2) FROM ( SELECT a.uid, SUM(count) as count2 FROM mallRN_keyword_search a WHERE {$GLOBALS['where']} GROUP BY a.keyword ) b JOIN mallRN_keyword_search c ON b.uid = c.uid";
	if(!$keyword_max = $mysql->get_one($sql)) $keyword_max = 0;
}

$listPaging->cquery = "cquery";
$cqueryTotal = function ($where) {	
	return "SELECT count(DISTINCT(keyword)) FROM mallRN_keyword_search WHERE {$GLOBALS['where']}";
};

$cqueryPrint = function ($where, $start_record, $page_record_num) {
	return "SELECT c.uid, c.keyword, b.count2 as count FROM ( SELECT DISTINCT(a.uid), SUM(a.count) as count2 FROM mallRN_keyword_search a WHERE {$GLOBALS['where']} GROUP BY a.keyword ) b JOIN mallRN_keyword_search c ON b.uid = c.uid ORDER BY {$GLOBALS['sort']} LIMIT {$start_record}, {$page_record_num}";	
};

$listPaging->where		 = "&& ".$where;
$listPaging->pagestring .= "&keyword_total={$keyword_total}&keyword_max={$keyword_max}";
$pagestring				 = $listPaging->pagestring;
$listPaging->total_record();
######################## listPaging 파라미터 및 검색조건 처리 #############################

######################## 리스트 출력 및 페이징 처리 #############################
$PAGING_TYPE	= PAGING_TYPE;
if($listPaging->total_record > 0) {
	$listPaging->print_record();
	if(PAGING_TYPE==1)	$PAGING = "";
	else				$PAGING = $listPaging->print_page();	
	$tpl->parse("is_paging_type_".PAGING_TYPE);
}
else $tpl->parse("empty_list");
######################## 리스트 출력 및 페이징 처리 #############################

$tpl->parse("is_list_area");

if($reset==1) {
	######################## 리스트 항목만 출력 (JOSON)  #############################
	$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1), "listPaging" => $PAGING, "listPage" => $page, "total" => number_format($TOTAL)];
	echo json_encode($my_array);
	exit;
	######################## 리스트 항목만 출력 (JOSON)  #############################
}

$TOTAL	= number_format($TOTAL);

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>