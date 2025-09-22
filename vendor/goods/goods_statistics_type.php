<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_statistics_type.html");
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
$type				= isset($_GET['type'])		? $_GET['type']		: 'pay';
$s_date				= isset($_GET['s_date'])	? $_GET['s_date']	: '';
$e_date				= isset($_GET['e_date'])	? $_GET['e_date']	: '';
$last_day			= date('t', strtotime("{$year}-{$month}"));
${"select_".$mode}	= "selected";

if($type != 'qtys' && $type != 'click' && $type != 'favorite') $type = 'sales'; 

switch($type) {
	case "sales" : 		$TTL = "판매금액"; break;		
	case "qtys" :		$TTL = "판매수량"; break;	
	case "click" :		$TTL = "클릭수"; break;	
	case "favorite" :	$TTL = "관심상품저장수"; break;	
}

$sql = "SELECT signdate FROM mallRN_order_sales ORDER BY uid ASC LIMIT 1";
if($tmps = $mysql->get_one($sql))  $start_year = date("Y", $tmps);
else $start_year = $this_year;
if($start_year == $this_year) $start_year --;
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
	for($i = 1, $cnt = 1; $i <= $last_day; $i += 7) {
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
		$where		= "INSTR(from_unixtime(signdate), '{$year}')";
	break;
	case "month" :
		$where		= "INSTR(from_unixtime(signdate), '{$year}-{$month}')";
	break;
	case "week" :
		$week_arr	= explode(" ~ ", $week);
		$week_arr2 = array();
		for($i = 0; $i < 7; $i ++) {
			$s_date = date("Y-m-d", strtotime("+{$i} DAY", strtotime($week_arr[0])));
			$week_arr2[] = "INSTR(from_unixtime(signdate), '{$s_date}')";
		}
		$week_arr2 = join(") || (", $week_arr2);

		$where		= " (({$week_arr2}))";
		unset($week_arr2, $s_date);
	break;
	case "day" :
		$where		= "INSTR(from_unixtime(signdate), '{$year}-{$month}-{$day}')";
	break;
	case "detail" :
		if(!$s_date) $s_date = $DATE1;	
		if(!$e_date) $e_date = $DATE1;	

		$where		= "from_unixtime(signdate) BETWEEN '{$s_date}' AND '{$e_date} 23:59:59' ";

		$tpl->parse("is_detail1");
		$tpl->parse("is_detail2");
	break;
}

$where	.= " && vendor = '{$v_my_id}'";

$TOTAL			= 0;
$TOTAL_PC		= 0;
$TOTAL_MOBILE	= 0;
$DATAS			= array();
$DATAS_PC		= array();
$DATAS_MOBILE	= array();

if($type == 'sales') {
	######################## 판매금액 #############################
	$sql = "SELECT SUM(IF(mobile = 'N', IF(status = 0, commission, -commission), 0)) as p_total, SUM(IF(mobile = 'Y', IF(status = 0, commission, -commission), 0)) as m_total, SUM(IF(status = 0, commission, -commission)) as sum, g_uid FROM mallRN_order_sales WHERE {$where} && type = 0 GROUP BY g_uid ORDER BY sum DESC LIMIT 30";
	######################## 판매금액 #############################
}
else if($type == 'qtys') {
	######################## 판매수량 #############################
	$sql = "SELECT SUM(IF(mobile = 'N', IF(status = 0, qty, -qty), 0)) as p_total, SUM(IF(mobile = 'Y', IF(status = 0, qty, -qty), 0)) as m_total, SUM(IF(status = 0, qty, -qty)) as sum, g_uid FROM mallRN_order_sales WHERE {$where} && type = 0 GROUP BY g_uid ORDER BY sum DESC LIMIT 30";
	######################## 판매수량 #############################
}
else if($type == 'click') {
	######################## 상품클릭 #############################
	$sql = "SELECT SUM(IF(mobile = 'N', 1, 0)) as p_total, SUM(IF(mobile = 'Y', 1, 0)) as m_total, COUNT(uid) as sum, g_uid FROM mallRN_goods_view WHERE {$where} GROUP BY g_uid ORDER BY sum DESC LIMIT 30";
	######################## 상품클릭 #############################
}
else if($type == 'favorite') {
	######################## 관심상품 #############################
	$sql = "SELECT SUM(IF(mobile = 'N', 1, 0)) as p_total, SUM(IF(mobile = 'Y', 1, 0)) as m_total, COUNT(uid) as sum, g_uid FROM mallRN_favorite_goods WHERE {$where} GROUP BY g_uid ORDER BY sum DESC LIMIT 30";	
}
$mysql->query($sql);

while($row = $mysql->fetch_array()){  
	
	$p_total		= $row['p_total'] ? $row['p_total'] : 0;
	$m_total		= $row['m_total'] ? $row['m_total'] : 0;

	$value			= $p_total + $m_total;
	if($value < 0) continue;
	$TOTAL			+= $value;
	$TOTAL_PC		+= $p_total;
	$TOTAL_MOBILE	+= $m_total;

	$sql			= "SELECT name, image3 FROM mallRN_goods WHERE uid = '{$row['g_uid']}'";
	$ginfo			= $mysql->one_row($sql);

	$DATAS[]		= array("sum" => $value, "title" => stripslashes($ginfo['name']), "image" => stripslashes($ginfo['image3']), "g_uid" => $row['g_uid']);
	$DATAS_PC[]		= array("sum" => $p_total, "title" => stripslashes($ginfo['name']), "image" => stripslashes($ginfo['image3']), "g_uid" => $row['g_uid']);
	$DATAS_MOBILE[] = array("sum" => $m_total, "title" => stripslashes($ginfo['name']), "image" => stripslashes($ginfo['image3']), "g_uid" => $row['g_uid']);
}

$data_array	= array("", "_PC", "_MOBILE");
foreach($data_array as $k => $v) {
	$datas	= ${"DATAS".$v};
	$totals	= ${"TOTAL".$v};
	$NUM	= 0;

	if(count($datas) > 0) {
		foreach ($datas as $key => $row) {
			$rank[$key]  = $row['sum'];   
		}
		@array_multisort($rank, SORT_DESC, $datas);

		$MAX_COUNT	= $datas[0]['sum'];
		foreach ($datas as $key => $row) {
			if($totals == 0) $PER = $WIDTH = 0;
			else {
				$NUM		++;
				$CONTENT	= $row['title'];
				$COUNT		= number_format($row['sum']);
				$COUNT2		= $row['sum'];
				$IMAGE		= $row['image'];
				$G_UID		= $row['g_uid'];

				$PER	= number_format((100 * $row['sum']) / $totals, 2);
				if($MAX_COUNT > 0)	$WIDTH	= number_format((100 * $row['sum']) / $MAX_COUNT, 2);
				else				$WIDTH	= 0;				
				$tpl->parse("loop_content".$v);
				$tpl->parse("loop_list".$v);
			}			
		}
	}

	if($NUM == 0) $tpl->parse("empty_list".$v);
}


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>