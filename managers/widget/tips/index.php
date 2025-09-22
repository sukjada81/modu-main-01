<?php 

include_once("../widget_top.php");

$num	= checkPostVar('num');
$min	= 100;
$max	= 121;

$sql	= "SELECT basic_name, payment_type_b, payment_type_c, payment_type_r, payment_type_v, payment_type_h, payment_bank_info FROM mallRN_configuration WHERE uid = 1";
$data	= $mysql->one_row($sql);

if(!$num) {
	if(!$data['basic_name']) $num = 10;
}

if(!$num) {
	$sql	= "SELECT count(*) FROM mallRN_banner WHERE moddate != 0";
	if($mysql->get_one($sql) == 0) $num = 15;
}

if(!$num) {
	$sql	= "SELECT count(*) FROM mallRN_cate";
	if($mysql->get_one($sql) == 0) $num = 11;
}

if(!$num) {
	$sql	= "SELECT count(*) FROM mallRN_goods";
	if($mysql->get_one($sql) == 0) $num = 12;
}

if(!$num) {
	if($data['payment_type_b'] == 1 && (!$data['payment_bank_info'] || $data['payment_bank_info'] == '|||')) {
		$num = 14;
	}	
}


if(!$num) {
	if($data['payment_type_b'] == 0 && $data['payment_type_c'] == 0  && $data['payment_type_r'] == 0 && $data['payment_type_v'] == 0 && $data['payment_type_h'] == 0) {
		$num = 13;
	}	
}

if($num && strlen($num) == 2) {
	$tpl->parse("is_tips_{$num}");
	$rand	= mt_rand($min, $max);
}
else {
	if($num)	{
		if($num == $max)	$rand	= $min;
		else				$rand	= $num + 1;
	}
	else					$rand	= mt_rand($min, $max);

	$tpl->parse("is_tips_{$rand}");
}

include_once("../widget_bottom.php");

?>