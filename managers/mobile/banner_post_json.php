<?php

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array	= array();
$code		= checkPostVar('code');
$order		= checkPostVar('order');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$code || !$order)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');

$order = explode(",", $order);
for($i = 0, $cnt = count($order); $i < $cnt; $i ++) {
	$uid = trim($order[$i]);
	
	$i2 = $i + 1;
	$sql = "UPDATE mallRN_mobile_banner SET sequence = '{$i2}' WHERE uid = '{$uid}' && code = '{$code}'";
	$mysql->query($sql);
}

$my_array[] = ["label"=>"Success"];


echo json_encode($my_array);

?>
