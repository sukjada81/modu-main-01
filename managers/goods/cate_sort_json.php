<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$cate_dep	= checkPostVar('dep');
$order		= checkPostVar('order');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$cate_dep || !$order) {
	echo json_encode(array('error' => '정보가 제대로 넘어오지 못했습니다.'));
	exit;
}

$order = explode(",",$order);

for($i=0, $cnt=count($order); $i<$cnt; $i++) {
	$i2 = $i+1;
	$sql = "UPDATE mallRN_cate SET sequence = '{$i2}' WHERE cate_dep='{$cate_dep}' && cate='{$order[$i]}'";
	$mysql->query($sql);
}

echo json_encode(array('success' => $cate_dep.'분류 순서가 변경 되었습니다.'));

?>