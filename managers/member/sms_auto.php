<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","sms_auto.html");
$tpl->scan_area("main");

$sql	= "SELECT sms_pfid FROM mallRN_configuration WHERE uid=1";
$pfid	= $mysql->get_one($sql);

$sql = "SELECT * FROM mallRN_sms_auto ORDER BY uid ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {	
	$UID		= $row['uid'];
	$CODE		= $row['code'];
	$TITLE		= stripslashes($row['title']);
	$MESSAGE1	= stripslashes($row['message1']);
	$MESSAGE2	= stripslashes($row['message2']);
	if($row['ck_message1'] == 1)	$CK_MESSAGE1 = "checked='checked'";
	else							$CK_MESSAGE1 = "";	
	if($row['ck_message2'] == 1)	$CK_MESSAGE2 = "checked='checked'";
	else							$CK_MESSAGE2 = "";	
	
	$DISABLE1 = $DISABLE2 = "";
	if($row['type'] == 1) $DISABLE2 = "disabled='disabled'";
	else if($row['type'] == 2) $DISABLE1 = "disabled='disabled'";

	$TTL	= "고객";
	if($CODE == 'pay_ok2') $TTL	= "판매사";

	if($pfid) {
		$TEMPLATE_ID1	= stripslashes($row['template_id1']);
		$TEMPLATE_ID2	= stripslashes($row['template_id2']);

		$tpl->parse("is_kakao1");
		$tpl->parse("is_kakao2");
	}

	$tpl->parse("loop_list");
}

if($pfid) {
	$tpl->parse("is_kakao3");
}

/*
$sql = "INSERT INTO mallRN_sms_auto SET code = 'order', title = '무통장 주문접수', type = 0";
$mysql->query($sql);

$sql = "INSERT INTO mallRN_sms_auto SET code = 'bank_ok', title = '무통장 입금확인', type = 1";
$mysql->query($sql);

$sql = "INSERT INTO mallRN_sms_auto SET code = 'pay_ok', title = '결제완료 (무통장제외)', type = 0";
$mysql->query($sql);

$sql = "INSERT INTO mallRN_sms_auto SET code = 'pay_ok2', title = '결제완료시 판매사통보', type = 1";
$mysql->query($sql);

$sql = "INSERT INTO mallRN_sms_auto SET code = 'delivery', title = '상품발송', type = 1";
$mysql->query($sql);
*/

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>