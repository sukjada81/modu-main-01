<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");

for($i = 0; $i < 2; $i ++) {
	if($i == 0) {
		$i2		= "";
		$where = "&& INSTR(from_unixtime(signdate), '{$today}')";
	}
	else {
		$i2		= "_".$i;
		$where	= "";		
	}

	$sql		= "SELECT SUM(IF(status = '0' , 1 , 0)) as cnt0, SUM(IF(status = '1' , 1 , 0)) as cnt1, SUM(IF(status = '2' , 1 , 0)) as cnt2, SUM(IF(status = '3' , 1 , 0)) as cnt3, SUM(IF(status = '4' , 1 , 0)) as cnt4, SUM(IF(status = '5' , 1 , 0)) as cnt5, SUM(IF(status = '7' , 1 , 0)) as cnt7, SUM(IF(status = '8' , 1 , 0)) as cnt8, SUM(IF(status = '9' , 1 , 0)) as cnt9 FROM mallRN_order_goods WHERE reals = 1 {$where}";
	$ocnt	= $mysql->one_row($sql);
	
	for($j = 0; $j < 10; $j ++) {
		if($j == 6) continue;
		${"CNT".$j.$i2} = number_format($ocnt['cnt'.$j]);
	}
}

include_once("../widget_bottom.php");

?>