<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$item_array		= array('message1', 'message2', 'template_id1', 'template_id2', 'ck_message1', 'ck_message2');
$item_default	= array('ck_message1', 'ck_message2');

$sql = "SELECT * FROM mallRN_sms_auto ORDER BY uid ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$_POST['message1']		= checkPostVar('message1_'.$row['uid']);
	$_POST['message2']		= checkPostVar('message2_'.$row['uid']);
	$_POST['template_id1']	= checkPostVar('template_id1_'.$row['uid']);
	$_POST['template_id2']	= checkPostVar('template_id2_'.$row['uid']);	
	$_POST['ck_message1']	= checkPostVar('ck_message1_'.$row['uid']);
	$_POST['ck_message2']	= checkPostVar('ck_message2_'.$row['uid']);
	
	$sql = "UPDATE mallRN_sms_auto SET";
	foreach ($item_array as $k => $v) {
		if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
		else $_POST[$v] = checkPostVar($v);
		if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
		else $sql .= " {$v} = '{$_POST[$v]}',";
	}
	$sql .= " WHERE uid = '{$row['uid']}'";
	
	$mysql->query2($sql);
}	

logMsg("문자 알림설정이 저장되었습니다.","success");

?>