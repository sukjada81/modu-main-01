<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$date5		= date("m-d");
$TOTAL		= 0;

for($i = 0; $i < 3; $i ++) {
	if($i < 2) {
		$sql		= "SELECT SUM(IF(status = 0, price , -price)) as total FROM mallRN_order_sales WHERE vendor = '{$v_my_id}' && INSTR(from_unixtime(signdate), '{$today}') && type = '{$i}'";
	}
	else {
		$sql		= "SELECT SUM(IF(status = 0, commission , -commission)) as total FROM mallRN_order_sales WHERE vendor = '{$v_my_id}' && INSTR(from_unixtime(signdate), '{$today}') && type = '0'";
	}

	$data			= $mysql->one_row($sql);
	$total			= $data['total'] ? $data['total'] : 0;

	${"CNT".($i + 1)}		= number_format($total);
	
	if($i < 2) 	$TOTAL		+= ($total);
	else		$TOTAL		-= ($total);
}

$value5						= $TOTAL;
$TOTAL						= number_format($TOTAL);

for($i = 4, $i2 = 1; $i > 0; $i --) {
	$date			= date("Y-m-d", strtotime('-'.$i.' DAY', time()));
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT SUM(IF(status = 0, price , -price)) as total1 FROM mallRN_order_sales WHERE vendor = '{$v_my_id}' && INSTR(from_unixtime(signdate), '{$date}')";
	$data			= $mysql->one_row($sql);
	$total1			= $data['total1'] ? $data['total1'] : 0;

	$sql			= "SELECT SUM(IF(status = 0, commission , -commission)) as total2 FROM mallRN_order_sales WHERE vendor = '{$v_my_id}' && INSTR(from_unixtime(signdate), '{$date}') && type = '0'";
	$data2			= $mysql->one_row($sql);
	$total2			= $data2['total2'] ? $data2['total2'] : 0;

	${"value".$i2}	= $total1 - $total2;
	$i2 ++;
}

include_once("../widget_bottom.php");

?>