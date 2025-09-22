<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$date5		= date("m-d");
$PTOTAL		= 0;
$MTOTAL		= 0;
$TOTAL		= 0;

for($i = 0; $i < 6; $i ++) {
	$sql					= "SELECT SUM(IF(mobile = 'N', IF(status = 0, price, -price), 0)) as p_total, SUM(IF(mobile = 'Y', IF(status = 0, price, -price), 0)) as m_total FROM mallRN_order_sales WHERE INSTR(from_unixtime(signdate), '{$today}') && type = '{$i}'";

	$data					= $mysql->one_row($sql);
	$p_total		= $data['p_total'] ? $data['p_total'] : 0;
	$m_total		= $data['m_total'] ? $data['m_total'] : 0;

	$PTOTALS				= $p_total;
	$MTOTALS				= $m_total;
	${"CNT".($i + 1)}		= number_format($PTOTALS + $MTOTALS);

	$PTOTAL					+= $PTOTALS;
	$MTOTAL					+= $MTOTALS;
	$TOTAL					+= ($PTOTALS + $MTOTALS);
}

$PTOTAL						= number_format($PTOTAL);
$MTOTAL						= number_format($MTOTAL);
$value5						= $TOTAL;
$TOTAL						= number_format($TOTAL);

for($i = 4, $i2 = 1; $i > 0; $i --) {
	$date			= date("Y-m-d", strtotime('-'.$i.' DAY', time()));
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT SUM(IF(status = 0, price , -price)) as total FROM mallRN_order_sales WHERE INSTR(from_unixtime(signdate), '{$date}')";
	$data			= $mysql->one_row($sql);
	$total			= $data['total'] ? $data['total'] : 0;
	${"value".$i2}	= $total;
	$i2 ++;
}

include_once("../widget_bottom.php");

?>