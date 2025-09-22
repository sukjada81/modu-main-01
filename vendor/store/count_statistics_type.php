<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","count_statistics_type.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$lastdate			= 1;
$this_year			= date("Y");
$this_month			= date("m");
$this_day			= date("d");
$DATE1				= date("Y-m-d");
$DATE2				= date("Y-m-d", strtotime('-3 DAY', time()));
$DATE3				= date('Y-m-d', strtotime('-1 WEEK', time()));
$DATE4				= date('Y-m-d', strtotime('-1 MONTH', time()));
$year				= isset($_GET['year'])		? $_GET['year']		: $this_year;
$month				= isset($_GET['month'])		? $_GET['month']	: $this_month;
$day				= isset($_GET['day'])		? $_GET['day']		: $this_day;
$week				= isset($_GET['week'])		? $_GET['week']		: '';
$mode				= isset($_GET['mode'])		? $_GET['mode']		: 'day';
$type				= isset($_GET['type'])		? $_GET['type']		: 'os';
$s_date				= isset($_GET['s_date'])	? $_GET['s_date']	: '';
$e_date				= isset($_GET['e_date'])	? $_GET['e_date']	: '';
$last_day			= date('t', strtotime("{$year}-{$month}"));
${"select_".$mode}	= "selected";

if($type != 'os' && $type != 'browser' && $type != 'site' && $type != 'keyword') $type = 'os'; 

switch($type) {
	case "os" :			$TTL = "운영체제"; break;
	case "browser" :	$TTL = "브라우저"; break;
	case "site" :		$TTL = "사이트"; break;
	case "keyword" :	$TTL = "키워드"; break;
}

$sql = "SELECT year FROM mallRN_store_count_{$type} WHERE vendor = '{$v_my_id}' ORDER BY uid ASC LIMIT 1";
if(!$start_year = $mysql->get_one($sql)) $start_year = $this_year;
######################## 변수 정의 #############################

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
		$start_wday = date("Y-m-d", strtotime("-1 DAY", strtotime("{$year}-{$month}-{$i2}")));
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

$ck_concat	= 0;

switch($mode) {
	case "year" :
		$where		= "year = '{$year}'";
	break;
	case "month" :
		$where		= "year = '{$year}' && month = '{$month}'";
	break;
	case "week" :
		$week		= str_replace("-", "", $week);
		$week_arr	= explode(" ~ ", $week);
		$where		= " trdate BETWEEN  {$week_arr[0]} AND {$week_arr[1]}";		
		$ck_concat	= 1;
	break;
	case "day" :
		$where		= "year = '{$year}' && month = '{$month}' && day = '{$day}'";
	break;
	case "detail" :
		if(!$e_date) {
			$e_date = $DATE1;	
			if(!$s_date) $s_date = $DATE1;	
		}
		if(!$s_date)	$where = "&& year <= '".substr($e_date, 0, 4)."' && month <= '".substr($e_date, 5, 2)."' && day <= '".substr($e_date, 8, 2)."'";
		else {
						$where		= " trdate BETWEEN ".str_replace("-", "", $s_date)." AND ".str_replace("-", "", $e_date);		
						$ck_concat	= 1;
		}
		$tpl->parse("is_detail1");
		$tpl->parse("is_detail2");
	break;
}

if($ck_concat == 1) {
	$sql	= "SELECT sum(count) as total FROM ( SELECT  uid, concat(year, lpad(month, 2, 0), lpad(day, 2, 0)) as trdate FROM mallRN_store_count_{$type} ) b JOIN mallRN_store_count_{$type} c ON b.uid = c.uid WHERE {$where}";
}
else {
	$sql	= "SELECT SUM(count) as total FROM mallRN_store_count_{$type} WHERE {$where}";
}

$data	= $mysql->one_row($sql);
$TOTAL	= $data['total'] ? $data['total'] : 0;
$NUM	= 0;
$DATAS	= array();

if($ck_concat == 1) {
	$sql	= "SELECT c.content, b.count2 as count FROM ( SELECT c1.uid, SUM(count) as count2 FROM ( SELECT uid, concat(year, lpad(month, 2, 0), lpad(day, 2, 0)) as trdate FROM mallRN_store_count_{$type} ) b1 JOIN mallRN_store_count_{$type} c1 ON b1.uid = c1.uid WHERE {$where} GROUP BY content ) b JOIN mallRN_store_count_{$type} c ON b.uid = c.uid";
}
else {
	$sql = "SELECT c.content, b.count2 as count FROM ( SELECT uid, SUM(count) as count2 FROM mallRN_store_count_{$type} WHERE {$where} GROUP BY content ) b JOIN mallRN_store_count_{$type} c ON b.uid = c.uid";
}
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$DATAS[] = array("sum" => $row['count'], "title" => stripslashes($row['content']));
}

if(count($DATAS) > 0) {

	foreach ($DATAS as $key => $row) {
		$rank[$key]  = $row['sum'];   
	}
	@array_multisort($rank, SORT_DESC, $DATAS);

	$MAX_COUNT	= $DATAS[0]['sum'];
	foreach ($DATAS as $key => $row) {
		$NUM		++;
		$CONTENT	= $row['title'];
		$COUNT		= number_format($row['sum']);
		$COUNT2		= $row['sum'];

		if($TOTAL == 0) $PER	= $WIDTH = 0;
		else {
			$PER	= number_format((100 * $row['sum']) / $TOTAL, 2);
			$WIDTH	= number_format((100 * $row['sum']) / $MAX_COUNT, 2);
			$tpl->parse("loop_content");
		}

		$tpl->parse("loop_list");
	}

}

if($NUM == 0) $tpl->parse("empty_list");

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>