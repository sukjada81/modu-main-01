<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","calculate_amount.html");
$tpl->scan_area("main");

######################## 변수 정의 #############################
$DATE1			= date('Y-m-01', strtotime('-1 MONTH', time()));
$DATE2			= date('Y-m-15', strtotime('-1 MONTH', time()));
$DATE3			= date('Y-m-16', strtotime('-1 MONTH', time()));
$DATE4			= date('Y-m-t', strtotime('-1 MONTH', time()));
$DATE5			= date('Y-m-01');
$DATE6			= date('Y-m-15');
$DATE7			= date('Y-m-16');
$DATE8			= date('Y-m-t');
$w				= date("w");	
$DATE9			= date("Y-m-d", strtotime("-".$w." DAY"));
$DATE10			= date("Y-m-d", strtotime("+6 DAY", strtotime($DATE9)));
$DATE11			= date("Y-m-d", strtotime("-1 WEEK", strtotime($DATE9)));
$DATE12			= date("Y-m-d", strtotime("-1 WEEK", strtotime($DATE10)));
$DATE13			= date("Y-m-d", strtotime("-2 WEEK", strtotime($DATE9)));
$DATE14			= date("Y-m-d", strtotime("-2 WEEK", strtotime($DATE10)));

$s_date			= checkGetVar('s_date');
$e_date			= checkGetVar('e_date');
$account_cycle	= checkGetVar('account_cycle');
$vendor			= checkGetVar('vendor');
######################## 변수 정의 #############################

if($s_date && $e_date) {

	$where		= "from_unixtime(confirm_date) BETWEEN '{$s_date}' AND '{$e_date} 23:59:59' ";
	$where2		= "";
	if($account_cycle) $where2 .= "&& account_cycle = '{$account_cycle}'";
	if($vendor) $where2 .= "&& id = '{$vendor}'";

	$NUM					= 0;
	$DATAS					= array();

	######################## 판매사 #############################
	$sql = "SELECT id, comp_name FROM mallRN_vendor WHERE uid > 0 {$where2} ORDER BY comp_name ASC";
	$mysql->query($sql);
	
	$vendor_array = array();
	while($row = $mysql->fetch_array()) {
		$vendor_array[$row['id']] = $row['comp_name'];
	}
	
	$sql = "SELECT SUM(IF(type = 0, IF(status = 0, price, -price), 0)) as total0, SUM(IF(type = 1, IF(status = 0, price, -price), 0)) as total1,  SUM(IF(type = 0, IF(status = 0, commission, -commission), 0)) as total2, vendor FROM mallRN_order_sales WHERE {$where} && adjustment = 0 && vendor !='' GROUP BY vendor";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()) {
	
		$TOTAL		= 0;
		$SUM[]		= array();
	
		for($j = 0; $j < 3; $j ++) {			
			$total		= $row['total'.$j] ? $row['total'.$j] : 0;

			if($j < 2)	$TOTAL	+= $total;
			else		$TOTAL	-= $total;
			
			$SUM[$j]			= $total;			
		}		
		
		if(isset($vendor_array[$row['vendor']])) {
			$DATAS[] = array("sum" => $TOTAL, "title" => stripslashes($vendor_array[$row['vendor']])."(".stripslashes($row['vendor']).")", "id" => stripslashes($row['vendor']), "sum1" => $SUM[0], "sum2" => $SUM[1], "sum3" => $SUM[2] );		
		}

	}
	######################## 판매사 #############################

	if(count($DATAS) > 0) {

		foreach ($DATAS as $key => $row) {
			$rank[$key]  = $row['sum'];   
		}
		@array_multisort($rank, SORT_DESC, $DATAS);

		foreach ($DATAS as $key => $row) {
			$NUM		++;
			$VENDOR		= $row['title'];
			$VENDOR_ID	= $row['id'];
			$SUM		= number_format($row['sum']);
			$SUM1		= number_format($row['sum1']);
			$SUM2		= number_format($row['sum2']);
			$SUM3		= number_format($row['sum3']);
			
			$tpl->parse("loop_list");
		}
	}

	if($NUM == 0) {
		$tpl->parse("empty_list");
		$tpl->parse("empty_list2");
	}

	$tpl->parse("is_search");

}

if(!$s_date) $s_date = $DATE1;	
if(!$e_date) $e_date = $DATE4;


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/bottom.php");

?>