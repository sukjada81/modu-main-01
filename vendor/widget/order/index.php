<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$date5		= date("m-d");
$sql		= "SELECT SUM(IF(status = '0' , 1 , 0)) as cnt FROM mallRN_order_goods WHERE vendor = '{$v_my_id}' && reals = 1 && INSTR(from_unixtime(signdate), '{$today}') && status != 9 GROUP BY order_num";
$mysql->query($sql);

$total	= 0;
$cnt1	= 0;
while($row = $mysql->fetch_array()) {
	$total ++;
	if($row['cnt'] > 0) $cnt1 ++;
}

$value5		= $total;
$TOTAL		= number_format($total);
$CNT1		= number_format($cnt1);
$CNT2		= number_format($total - $cnt1);

for($i = 4, $i2 = 1; $i > 0; $i --) {
	$date			= date("Y-m-d", strtotime('-'.$i.' DAY', time()));
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT count(*) FROM mallRN_order_goods WHERE vendor = '{$v_my_id}' && reals = 1 && INSTR(from_unixtime(signdate), '{$date}') && status != 9";
	${"value".$i2}	= $mysql->get_one($sql);
	$i2 ++;
}

include_once("../widget_bottom.php");

?>