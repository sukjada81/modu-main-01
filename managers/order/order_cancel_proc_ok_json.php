<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array = array();

$uid = checkPostVar('uid');

if(!$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql = "UPDATE mallRN_order_cancel_cp_log SET proc = 1 WHERE uid = '{$uid}'";
$mysql->query($sql);

echo json_encode(array('success' => '정상처리 되었습니다.'));

?>