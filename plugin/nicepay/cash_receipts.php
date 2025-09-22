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

$sql			= "SELECT * FROM mallRN_order_cash_receipts WHERE order_num = '{$order_num}'";
$data			= $mysql->one_row($sql);

if($mode == 'cancel') {
	
	if($data['status'] != '3') {
		echo "False";
		exit;
	}

	$_POST['TID']				= $data['receipt_no'];
	$_POST['CancelAmt']			= $data['price'];
	$_POST['PartialCancelCode']	= 0;
	$order_num2					= $order_num;

}
else if($mode == 'partial_cancel') {
	
	if($data['status'] != '3') {
		echo "False";
		exit;
	}

	$_POST['TID']		= $data['receipt_no'];
		
	$sql				= "SELECT pay_total, cancel_total, refund_total FROM mallRN_order_info WHERE order_num = '{$order_num}'";
	$info				= $mysql->one_row($sql);
	$oprice				= $data['price'] - $data['partial_price'];
	$price				= $data['price'] - ($info['pay_total'] - $info['refund_total'] - $info['cancel_total']);

	if($price < 1) {
		$_POST['PartialCancelCode']	= 0;
		$mode				= "cancel";
		$_POST['CancelAmt']	= $oprice;
	}
	else {
		$_POST['PartialCancelCode']	= 1;		
		$_POST['rem_mny']	= $oprice;
		$_POST['CancelAmt']	= $oprice - $price;
	}
	
	$order_num2			= $order_num."_".mt_rand(10,99);
}
else {	
	
	$data['goods_name']		= str_replace(array("'","\"","|",",",":","&","\n","\\"),"", $data['goods_name']);
	
	if($data['tax_type'] == 0) {
		$PRICE				= $data['price'];
		$PRICE1				= round(($data['price'] / 1.1), 0);
		$PRICE2				= $PRICE - $PRICE1;
		$PRICE3				= 0;
	}
	else {
		$PRICE = $PRICE3	= $data['price'];
		$PRICE2				= 0;
		$PRICE1				= 0;
	}

	$order_num2				= $order_num;
	$_POST['GoodsName']		= $data['goods_name'];
	$_POST['name']			= $data['name'];
	$_POST['cell']			= $data['cell'];
	$_POST['email']			= $data['email'];
	
	if($data['cash_type'] == 0) $_POST['ReceiptType']	= "1";
	else						$_POST['ReceiptType']	= "2";

	$_POST['ReceiptTypeNo']	= $data['auth_number'];

	$_POST['ReceiptAmt']		= $PRICE;
	$_POST['ReceiptSupplyAmt']	= $PRICE1;
	$_POST['ReceiptServiceAmt']	= "0";
	$_POST['ReceiptVAT']		= $PRICE2;
	$_POST['ReceiptTaxFreeAmt']	= $PRICE3;
}

if(!$mode)	include_once("receiptResult.php");
else		include_once("receiptCancel.php");

?>