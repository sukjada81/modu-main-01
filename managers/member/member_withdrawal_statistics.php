<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","member_withdrawal_statistics.html");
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
$mode				= isset($_GET['mode'])		? $_GET['mode']		: 'month';
$s_date				= isset($_GET['s_date'])	? $_GET['s_date']	: '';
$e_date				= isset($_GET['e_date'])	? $_GET['e_date']	: '';
$last_day			= date('t', strtotime("{$year}-{$month}"));
${"select_".$mode}	= "selected";

$TTL = "탈퇴사유"; 

$sql = "SELECT signdate FROM mallRN_member_withdrawal ORDER BY uid ASC LIMIT 1";
if(!$start_year = date("Y", $mysql->get_one($sql))) $start_year = $this_year;
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
		$where		= "INSTR(from_unixtime(signdate),'{$year}')";
	break;
	case "month" :
		$where		= "INSTR(from_unixtime(signdate),'{$year}-{$month}')";
	break;
	case "week" :
		$week_arr	= explode(" ~ ", $week);
		$where		= " ( from_unixtime(signdate) BETWEEN '{$week_arr[0]}' AND '{$week_arr[1]} 23:59:59')";
	break;
	case "day" :
		$where		= " INSTR(from_unixtime(signdate),'{$year}-{$month}-{$day}')";
	break;	
	case "detail" :
		if(!$s_date) $s_date = $DATE1;	
		if(!$e_date) $e_date = $DATE1;	

		$where		= "from_unixtime(signdate) BETWEEN '{$s_date}' AND '{$e_date} 23:59:59' ";

		$tpl->parse("is_detail1");
		$tpl->parse("is_detail2");
	break;
}

$sql	= "SELECT count(*) as total FROM mallRN_member_withdrawal WHERE {$where}";
$data	= $mysql->one_row($sql);
$TOTAL	= $data['total'] ? $data['total'] : 0;
$NUM	= 0;

$sql	= "SELECT MAX(b.count2) FROM ( SELECT uid, count(*) as count2 FROM mallRN_member_withdrawal WHERE {$where} GROUP BY reason ) b JOIN mallRN_member_withdrawal c ON b.uid = c.uid";
if(!$MAX_COUNT = $mysql->get_one($sql)) $MAX_COUNT = 0;

$sql = "SELECT c.reason, b.count2 as count FROM ( SELECT uid, count(*) as count2 FROM mallRN_member_withdrawal WHERE {$where} GROUP BY reason ) b JOIN mallRN_member_withdrawal c ON b.uid = c.uid";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$NUM ++;
	$CONTENT	= stripslashes($row['reason']);
	$COUNT		= number_format($row['count']);

	if($TOTAL == 0) $PER	= $WIDTH = 0;
	else {
		$PER	= number_format((100 * $row['count']) / $TOTAL, 2);
		$WIDTH	= number_format((100 * $row['count']) / $MAX_COUNT, 2);
	}

	$tpl->parse("loop_list");
	$tpl->parse("loop_content");
}

if($NUM == 0) $tpl->parse("empty_list");

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>