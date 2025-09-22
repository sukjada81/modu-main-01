<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../../');
define('PATH_LIB',			'lib');
define('PATH_INCLUDE',		'include');

include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/lib.Shop.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');

$mysql = new mysqlClass();

$referer	= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$access_ip	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$order_num	= checkPostVar('order_num');
$mode		= checkPostVar('mode');

if(!$referer || !$access_ip || $access_ip != $_SERVER['SERVER_ADDR'] || !$order_num)	{
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 1";
$shop_config	= $mysql->one_row($sql);

$sql			= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1 && (pay_type = 'C' || pay_type = 'R') && pay_status = 'C'";
if(!$data = $mysql->one_row($sql)) {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

$ordr_idxx			= $order_num;
$signdate			= time();
$cancel_escrow		= $data['escrow'];
$cancel_tno			= $data['pay_number'];
$cancel_pay_type	= $data['pay_type'];

if($mode == 'cancel') {	

	$sql				= "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && status != 9";
	if($mysql->get_one($sql) > 0) {
		header("HTTP/1.1 404 Internal Server Error");
		exit(0);
	}

	$cancel_mode_type	= "0";	
	$og_uid				= '';
	$cancel_rem_mny		= $data['pay_total'];
	$cancel_mod_mny		= $data['pay_total'];
	$cancel_msg			= "주문취소요청";
	$order_num2			= $order_num;
}
else if($mode == 'partial_cancel') {

	$og_uid				= checkPostVar('og_uid');
	$refund				= checkPostVar('refund');

	if(!$og_uid || !$refund) {
		header("HTTP/1.1 404 Internal Server Error");
		exit(0);
	}

	$sql				= "SELECT rem_price FROM mallRN_order_cancel_cp_log WHERE order_num = '{$order_num}' && status = 0 ORDER BY uid DESC LIMIT 1";
	$rem_price			= $mysql->get_one($sql);
	if(!$rem_price)	$rem_price = $data['pay_total'];

	$cancel_mode_type	= "1";
	$cancel_rem_mny		= $rem_price;
	$cancel_mod_mny		= $refund;
	$cancel_msg			= "주문부분취소요청";
	$cancel_rem_mny2	= $rem_price - $refund;
	$order_num2			= $order_num."_".$og_uid;
	
}

include_once("cancelResult.php");

?>