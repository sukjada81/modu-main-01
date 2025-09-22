<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","payment_info.html");
$tpl->scan_area("main");

$item_array = array('payment_type_b', 'payment_type_c', 'payment_type_r', 'payment_type_v', 'payment_type_h', 'payment_cp', 'payment_shop_id', 'payment_shop_key', 'payment_install_range', 'payment_escrow_v', 'payment_complex_tax', 'payment_bank_info', 'payment_commission_c', 'payment_commission_r', 'payment_commission_r2', 'payment_commission_v', 'payment_commission_h', 'cash_receipts_used', 'cash_receipts_require', 'cash_receipts_method', 'cash_receipts_type', 'naverpay_used', 'naverpay_mode', 'naverpay_test_id', 'naverpay_shop_id', 'naverpay_key1', 'naverpay_key2', 'naverpay_key3');

$sql = "SELECT * FROM mallRN_configuration WHERE uid=1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

if($payment_type_b==1) $checked_type_b = "checked='chedked'";
if($payment_type_c==1) $checked_type_c = "checked='chedked'";
if($payment_type_r==1) $checked_type_r = "checked='chedked'";
if($payment_type_v==1) $checked_type_v = "checked='chedked'";
if($payment_type_h==1) $checked_type_h = "checked='chedked'";
if($payment_escrow_v==1) $checked_escrow_v = "checked='chedked'";

${"checked_cp_".$payment_cp} = "checked='chedked'";
${"checked_install_range_".$payment_install_range} = "checked='chedked'";
${"checked_complex_tax_".$payment_complex_tax} = "checked='chedked'";
${"checked_receipts_used_".$cash_receipts_used} = "checked='chedked'";
${"checked_receipts_require_".$cash_receipts_require} = "checked='chedked'";
${"checked_receipts_method_".$cash_receipts_method} = "checked='chedked'";
${"checked_receipts_type_".$cash_receipts_type} = "checked='chedked'";

${"checked_naverpay_used_".$naverpay_used} = "checked='chedked'";
${"checked_naverpay_mode_".$naverpay_mode} = "checked='chedked'";

if($payment_bank_info) {
	$bank_info = explode("|*|",$payment_bank_info);
	foreach($bank_info as $k => $v) {
		$bank_info2 = explode("|",$v);
		if($bank_info2[0]) {
			$bank_name = $bank_info2[0];
			$bank_num = $bank_info2[1];
			$bank_owner = $bank_info2[2];
			$bank_used = $bank_info2[3];
			if($bank_used!='1') $bank_used = "0";
			$tpl->parse("loop_bank");
		}
	}	
}
$bank_name = $bank_num = $bank_owner = $bank_used = "";
$tpl->parse("loop_bank");

$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$SHOP_URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;

$payment_shop_key	= add_escape_re_string($data['payment_shop_key']);

$sql	= "SELECT payment_shop_id, payment_shop_key FROM mallRN_configuration WHERE uid = 2";
$data2	= $mysql->one_row($sql);
$payment_shop_id2	= add_escape_re_string($data2['payment_shop_id']);
$payment_shop_key2	= $data2['payment_shop_key'];

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>