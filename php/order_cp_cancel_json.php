<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$order_num	= checkPostVar('order_num');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$order_num) exit;

$sql = "DELETE FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 0";
$mysql->query($sql);

$sql = "DELETE FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 0";
$mysql->query($sql);

echo json_encode(array('success' => 1));

?>