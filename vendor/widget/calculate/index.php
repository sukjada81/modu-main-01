<?php 

include_once("../widget_top.php");

$status_array	= array("대기중", "정산완료");

$sql = "SELECT * FROM mallRN_sales_calculate WHERE vendor = '{$v_my_id}' ORDER BY uid DESC LIMIT 4";
$mysql->query($sql);

$i = 0;
while($row = $mysql->fetch_array()) {
	$DATES	= "{$row['s_date']} ~ {$row['e_date']}";
	$PRICE	= number_format($row['sum']);
	$STATUS	= $status_array[$row['status']];

	$tpl->parse("loop_list");
	$i ++;
}

if($i == 0) $tpl->parse("empty_list");

include_once("../widget_bottom.php");

?>