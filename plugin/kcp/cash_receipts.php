<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
header("Content-Type: text/html; charset=euc-kr");

define('DEFAULT_PATH',	'../../');
define('PATH_LIB',			'lib');
define('PATH_INCLUDE',		'include');

setlocale(LC_CTYPE, 'ko_KR.euc-kr');

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

	$_POST['req_tx']	= "mod";
	$_POST['mod_type']	= "STSC";
	$_POST['mod_gubn']	= "MG01";
	$_POST['mod_value'] = $data['receipt_no'];

}
else if($mode == 'partial_cancel') {
	
	if($data['status'] != '3') {
		echo "False";
		exit;
	}

	$_POST['req_tx']	= "mod";
	$_POST['mod_type']	= "STPC";
	$_POST['mod_gubn']	= "MG01";
	$_POST['mod_value'] = $data['receipt_no'];
		
	$sql				= "SELECT pay_total, cancel_total, refund_total FROM mallRN_order_info WHERE order_num = '{$order_num}'";
	$info				= $mysql->one_row($sql);
	$oprice				= $data['price'] - $data['partial_price'];
	$price				= $data['price'] - ($info['pay_total'] - $info['refund_total'] - $info['cancel_total']);

	if($price < 1) {
		$_POST['mod_type']	= "STSC";
		$mode				= "cancel";
	}
	else {
		$_POST['mod_mny']	= $price;
		$_POST['rem_mny']	= $oprice;
	}
}
else {	
	
	$COMP_NUM				= stripslashes($shop_config['comp_license_no1']);
	$COMP_NAME				= stripslashes($shop_config['comp_name']);
	$COMP_OWNER				= stripslashes($shop_config['comp_owner']);
	$COMP_ADDR				= stripslashes($shop_config['comp_address1'])." ".stripslashes($shop_config['comp_address2']);
	$COMP_ADDR				= str_replace(array("'","\"","|",",",":","&","\n","\\"),"",$COMP_ADDR);
	$COMP_TEL				= stripslashes($shop_config['comp_tel']);
	
	$data['goods_name']		= str_replace(array("'","\"","|",",",":","&","\n","\\"),"", $data['goods_name']);
	$data['name']			= str_replace(array("'","\"","|",",",":","&","\n","\\"),"", $data['name']);
	$data['cell']			= $data['cell'];

	$TTIME					= date("YmdHis",time());
	
	if($data['tax_type'] == 0) {
		$PRICE				= $data['price'];
		$PRICE1				= round(($data['price'] / 1.1), 0);
		$PRICE2				= $PRICE - $PRICE1;
		$TAX_TYPE			= "TG01";
	}
	else {
		$PRICE = $PRICE1	= $data['price'];
		$PRICE2				= 0;
		$TAX_TYPE			= "TG02";
	}

	$_POST['req_tx']		= "pay";
	$_POST['ordr_idxx']		= $order_num;
	$_POST['good_name']		= iconv("utf-8", "euc-kr",$data['goods_name']);
	$_POST['buyr_name']		= iconv("utf-8", "euc-kr",$data['name']);
	$_POST['buyr_mail']		= $data['email'];
	$_POST['buyr_tel1']		= $data['cell'];
	$_POST['comment']		= '';
	$_POST['corp_type']		= "0";
	$_POST['corp_tax_type'] = $TAX_TYPE;
	$_POST['corp_tax_no']	= $COMP_NUM;
	$_POST['corp_nm']		= iconv("utf-8","euc-kr", $COMP_NAME);
	$_POST['corp_owner_nm'] = iconv("utf-8","euc-kr", $COMP_OWNER);
	$_POST['corp_addr']		= iconv("utf-8","euc-kr", $COMP_ADDR);	
	$_POST['corp_telno']	= $COMP_TEL;
	$_POST['trad_time']		= $TTIME;
	$_POST['tr_code']		= $data['cash_type'];
	$_POST['id_info']		= $data['auth_number'];
	$_POST['amt_tot']		= $PRICE;
	$_POST['amt_sup']		= $PRICE1;
	$_POST['amt_svc']		= "0";
	$_POST['amt_tax']		= $PRICE2;
}


include_once("pp_cli_hub_cash.php");

?>