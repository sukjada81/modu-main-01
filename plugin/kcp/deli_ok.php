<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
header("Content-Type: text/html; charset=euc-kr");

define('DEFAULT_PATH',	'../../');
define('PATH_LIB',			'lib');
define('PATH_INCLUDE',		'include');

include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/lib.Shop.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');  

$mysql = new mysqlClass();

if($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) {
	echo "False";
	exit;
}

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
$shop_config = $mysql->one_row($sql);

$SHOP_ID			= trim($shop_config['payment_shop_id']);
$SHOP_KEY			= trim($shop_config['payment_shop_key']);

$order_num = checkGetVar('order_num');
if(!$order_num) {
	echo "False";
	exit;
}

$sql	= "SELECT pay_number FROM mallRN_order_info WHERE escrow = '1' && order_num='{$order_num}'";
$data	= $mysql->one_row($sql);

if(!$data['pay_number']) {
	echo "False";
	exit;
}

$sql	= "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && (status = 1 || status = 2)";
if($mysql->get_one($sql) > 0) {
	echo "False";
	exit;
}

$sql	= "SELECT delivery_info FROM mallRN_order_goods WHERE order_num = '{$order_num}' ORDER BY status_date DESC LIMIT 1";
$data2	= $mysql->one_row($sql);

if($data2['delivedry_info']) {
	$goods_delivery		= explode("|", $data2['delivery_info']);

	$delivery_info		= getDeliveryInfo($goods_delivery[0]);
	$DELI_NAME			= iconv("utf-8", "euc-kr", $delivery_info[0]);
	$DELI_NUM			= $goods_delivery[1];
}
else {
	echo "False";
	exit;
}
?>

<form name="mod_escrow_form" action="pp_cli_hub_escw.php" method="post">
	<input type='hidden' name='site_cd'		value='<?=$SHOP_ID?>' />
	<input type='hidden' name='site_key'    value='<?=$SHOP_KEY?>' />
	<input type='hidden' name='req_tx'		value='mod_escrow' />
	<input type="hidden" name="vcnt_yn"		value="N" />
	<input type='hidden' name='mod_type'	value='STE1' />
	<input type='hidden' name='tno'			value='<?=$data['pay_number']?>' />
	<input type='hidden' name='deli_numb'	value='<?=$DELI_NAME?>' />
	<input type='hidden' name='deli_corp'	value='<?=$CNUM?>' />
</form>

<script>document.mod_escrow_form.submit();</script>