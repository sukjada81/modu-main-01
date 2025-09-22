<?php
   
if(!isset($shop_config)) {

	include_once('../../include/config.php');

	$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
	$shop_config = $mysql->one_row($sql);
}

$SHOP_ID			= trim($shop_config['payment_shop_id']);
$SHOP_KEY			= add_escape_re_string(trim($shop_config['payment_shop_key']));

$sql				= "SELECT payment_shop_id, payment_shop_key FROM mallRN_configuration WHERE uid = 2";
$tmp_data			= $mysql->one_row($sql);
$SHOP_ID2			= add_escape_re_string(trim($tmp_data['payment_shop_id']));
$SHOP_KEY2			= add_escape_re_string(trim($tmp_data['payment_shop_key']));
unset($tmp_data);

?>
