<?php 

include_once("../widget_top.php");

$year		= date("Y");
$month		= date("m");
$day		= date("d");
$date5		= date("m-d");
$sql		= "SELECT SUM(total) as p_total, SUM(mtotal) as m_total FROM mallRN_count_list WHERE year = '{$year}' && month = '{$month}' && day = '{$day}' && type = '0'";
$data		= $mysql->one_row($sql);
$PTOTAL		= number_format($data['p_total']);
$MTOTAL		= number_format($data['m_total']);
$value5		= $data['p_total'] + $data['m_total'];
$TOTAL		= number_format($data['p_total'] + $data['m_total']);

$sql		= "SELECT SUM(total + mtotal) FROM mallRN_count_list WHERE year = '{$year}' && month = '{$month}' && day = '{$day}' && type = '1'";
$CNT1		= number_format($mysql->get_one($sql));

$sql		= "SELECT SUM(total + mtotal) FROM mallRN_count_list WHERE year = '{$year}' && month = '{$month}' && day = '{$day}' && type = '2'";
$CNT2		= number_format($mysql->get_one($sql));

$sql		= "SELECT SUM(total + mtotal) FROM mallRN_count_list WHERE year = '{$year}' && month = '{$month}' && day = '{$day}' && type = '3'";
$CNT3		= number_format($mysql->get_one($sql));

for($i = 4, $i2 = 1; $i > 0; $i --) {
	${"date".$i2}	= date("m-d", strtotime('-'.$i.' DAY', time()));
	$year			= date("Y", strtotime('-'.$i.' DAY', time()));
	$month			= date("m", strtotime('-'.$i.' DAY', time()));
	$day			= date("d", strtotime('-'.$i.' DAY', time()));
	$sql			= "SELECT SUM(total + mtotal)  FROM mallRN_count_list WHERE year = '{$year}' && month = '{$month}' && day = '{$day}' && type = '0'";
	$value			= $mysql->get_one($sql);
	if(!$value)		$value = 0;
	${"value".$i2}	= $value;
	$i2 ++;
}

include_once("../widget_bottom.php");

?>