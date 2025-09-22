<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","count_statistics.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$lastdate	= 1;
$this_year	= date("Y");
$this_month = date("m");
$this_day	= date("d");

$sql = "SELECT year FROM mallRN_store_count_list WHERE vendor = '{$v_my_id}' ORDER BY uid ASC LIMIT 1";
if(!$start_year = $mysql->get_one($sql)) $start_year = $this_year;
if($start_year == $this_year) $start_year --;

$year				= isset($_GET['year'])	? $_GET['year']		: $this_year;
$month				= isset($_GET['month']) ? $_GET['month']	: $this_month;
$day				= isset($_GET['day'])	? $_GET['day']		: $this_day;
$week				= isset($_GET['week'])	? $_GET['week']		: '';
$mode				= isset($_GET['mode'])	? $_GET['mode']		: 'time';
$last_day			= date('t', strtotime("{$year}-{$month}"));
${"select_".$mode}	= "selected";

if($year == 'all')			$month = $week = 'all';
else if($month == 'all')	$week = 'all';
######################## 변수 정의 #############################

if($mode == 'week') {
	$tpl->parse("is_year_all");
	$tpl->parse("is_month_all");
	$tpl->parse("is_week_all");
}

if($mode == 'month' || $mode == 'week' || $mode == 'day' || $mode == 'time') {
	for($i = $start_year; $i <= $this_year; $i++) {
		$tpl->parse("loop_year1");
		$tpl->parse("loop_year2");
	}
	$tpl->parse("is_year1");
	$tpl->parse("is_year2");
}

if($mode == 'week' || $mode == 'day' || $mode == 'time') {
	for($i = 1; $i < 13; $i++) {
		$i2 = sprintf("%02d", $i);
		$tpl->parse("loop_month1");
		$tpl->parse("loop_month2");
	}
	$tpl->parse("is_month1");
	$tpl->parse("is_month2");
}

if($mode == 'time') {
	for($i = 1; $i <= $last_day; $i++) {
		$i2 = sprintf("%02d", $i);
		$tpl->parse("loop_day1");
		$tpl->parse("loop_day2");
	}
	$tpl->parse("is_day1");
	$tpl->parse("is_day2");	
}

if($mode == 'week') {
	for($i = 1, $cnt = 1; $i <= $last_day; $i += 7) {
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
unset($cnt, $w, $last_dat, $start_wday, $end_wday,  $DATES);

switch($mode) {
	case "year" :
		$where		= "1 = 1";
		$rwhere		= "&& year = '{{RCODE}}'";
		$start		= $start_year;
		$end		= $this_year;
		$addstr1	= "";
		$addstr2	= "";
		$TTL		= "날자";
	break;
	case "month" :
		$where		= "year = '{$year}'";
		$rwhere		= "&& month = '{{RCODE}}'";
		$start		= 1;
		$end		= 12;
		$addstr1	= "{$year}-";
		$addstr2	= "";
		$TTL		= "날자";
	break;
	case "week" :
		if($week != 'all') {
			$week_arr	= explode(" ~ ", $week);
			$week_arr2 = array();
			for($i = 0; $i < 7; $i ++) {
				$s_date = date("Y-m-d", strtotime("+{$i} DAY", strtotime($week_arr[0])));
				$week_arr2[] = "year = '".substr($s_date, 0, 4)."' && month = '".substr($s_date, 5, 2)."' && day = '".substr($s_date, 8, 2)."'";
			}
			$week_arr2 = join(") || (", $week_arr2);

			$where		= " (({$week_arr2}))";
			unset($week_arr2, $s_date);
		}
		else if($year == 'all')		$where		= "1 = 1";
		else if($month == 'all')	$where		= "year = '{$year}'";
		else if($week == 'all')		$where		= "year = '{$year}' && month = '{$month}'";

		$rwhere		= "&& week = '{{RCODE}}'";
		$start		= 0;
		$end		= 6;
		$addstr1	= "";
		$addstr2	= "요일";
		$week_arr	= array('일', '월', '화', '수', '목', '금', '토');
		$TTL		= "요일";
	break;
	case "day" :
		$where		= "year = '{$year}' && month = '{$month}'";
		$rwhere		= "&& day = '{{RCODE}}'";
		$start		= 1;
		$end		= $last_day;
		$addstr1	= "{$year}-{$month}-";
		$addstr2	= "";
		$TTL		= "날자";
	break;
	case "time" :
		$where		= "year = '{$year}' && month = '{$month}' && day = '{$day}'";
		$rwhere		= '';
		$start		= 0;
		$end		= 23;
		$addstr1	= "";
		$addstr2	= ":00";
		$TTL		= "시간대";
	break;	
}

$total_per			= array();
$total_per_pc		= array();
$total_per_mobile	= array();
for($i = 0; $i < 4; $i ++) {
	$sql					= "SELECT SUM(total) as p_total, SUM(mtotal) as m_total FROM mallRN_store_count_list WHERE {$where} && type = '{$i}' && vendor = '{$v_my_id}'";
	$data					= $mysql->one_row($sql);
	$PTOTAL					= $data['p_total'] ? $data['p_total'] : 0;
	$MTOTAL					= $data['m_total'] ? $data['m_total'] : 0;
	$TOTAL					= $PTOTAL + $MTOTAL;
	$total_per[$i]			= $TOTAL;
	$total_per_pc[$i]		= $PTOTAL;
	$total_per_mobile[$i]	= $MTOTAL;
	$PTOTAL					= number_format($PTOTAL);
	$MTOTAL					= number_format($MTOTAL);
	$tpl->parse("loop_type_total");
}

$DATESTR	= array();
$COUNTSTR	= array();
for($i = $start; $i <= $end; $i++) {
	if($mode == 'week') {
		$i2			= $week_arr[$i];
		$rwhere2	= str_replace("{{RCODE}}", $i, $rwhere);
	}	
	else {
		$i2			= sprintf("%02d", $i);	
		$rwhere2	= str_replace("{{RCODE}}", $i2, $rwhere);
	}
	$DATES				= $addstr1.$i2.$addstr2;
	$DATESTR[]			= $DATES;
	$COUNTSTR[]			= array();
	$COUNTSTRPC[]		= array();
	$COUNTSTRMOBILE[]	= array();		
	
	for($j = 0; $j < 4; $j ++) {
		if($mode == 'year' || $mode == 'month') {
			$sql = "SELECT SUM(total) as total, SUM(mtotal) as mtotal FROM mallRN_store_count_list WHERE {$where} {$rwhere2} && type = '{$j}' && vendor = '{$v_my_id}'";
		}
		else $sql = "SELECT * FROM mallRN_store_count_list WHERE {$where} {$rwhere2} && type = '{$j}'";		
		
		if($data = $mysql->one_row($sql)) {
			if($mode == 'time') {
				$pc_value		= $data['h_'.$i2];
				$mobile_value	= $data['mh_'.$i2];
			}
			else {
				$pc_value		= $data['total'];
				$mobile_value	= $data['mtotal'];
			}
			$value	= $pc_value + $mobile_value;
		}
		else $value = $pc_value = $mobile_value = 0;

		${"COUNT".($j + 1)}			= number_format($value);
		${"COUNT_PC".($j + 1)}		= number_format($pc_value);
		${"COUNT_MOBILE".($j + 1)}	= number_format($mobile_value);

		if($total_per[$j] > 0)			${"PER".($j + 1)}	= number_format((100 * $value) / $total_per[$j], 2);
		else							${"PER".($j + 1)} = 0;

		if($total_per_pc[$j] > 0)		${"PER_PC".($j + 1)}	= number_format((100 * $pc_value) / $total_per_pc[$j], 2);
		else							${"PER_PC".($j + 1)} = 0;

		if($total_per_mobile[$j] > 0)	${"PER_MOBILE".($j + 1)}	= number_format((100 * $mobile_value) / $total_per_mobile[$j], 2);
		else							${"PER_MOBILE".($j + 1)} = 0;

		$COUNTSTR[$j][]			= $value;
		$COUNTSTRPC[$j][]		= $pc_value;
		$COUNTSTRMOBILE[$j][]	= $mobile_value;
	}
	$tpl->parse("loop_count");
	$tpl->parse("loop_count_pc");
	$tpl->parse("loop_count_mobile");
}
$DATESTR = join("', '", $DATESTR);

foreach($COUNTSTR as $k => $v) {
	${"COUNTSTR".($k + 1)} = join(",", $v);	
}
foreach($COUNTSTRPC as $k => $v) {
	${"COUNTSTRPC".($k + 1)} = join(",", $v);	
}
foreach($COUNTSTRMOBILE as $k => $v) {
	${"COUNTSTRMOBILE".($k + 1)} = join(",", $v);	
}

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>