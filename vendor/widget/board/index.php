<?php 

include_once("../widget_top.php");

$today		= date("Y-m-d");
$code_arr	= array('', '', '', 'vcounsel', 'vnotice');


for($i = 0; $i < 2; $i ++) {
	if($i == 0) {
		$i2		= "";
		$where = "&& INSTR(from_unixtime(signdate), '{$today}')";
	}
	else {
		$i2		= "_".$i;
		$where	= "";		
	}
	
	$sql			= "SELECT count(*) FROM mallRN_review WHERE vendor = '{$v_my_id}' {$where}";
	${"CNT1".$i2}	= number_format($mysql->get_one($sql));

	$sql			= "SELECT count(*) FROM mallRN_inquiry WHERE vendor = '{$v_my_id}' {$where}";
	${"CNT2".$i2}	= number_format($mysql->get_one($sql));

	for($j = 3; $j < 5; $j ++) {
		if($j == 3) $sql = "SELECT count(*) FROM mallRN_board_{$code_arr[$j]} WHERE id = '{$v_my_id}' {$where}";
		else		$sql = "SELECT count(*) FROM mallRN_board_{$code_arr[$j]} WHERE uid > 0 {$where}";
		${"CNT".$j.$i2}	= number_format($mysql->get_one($sql));
	}
}
	
include_once("../widget_bottom.php");

?>