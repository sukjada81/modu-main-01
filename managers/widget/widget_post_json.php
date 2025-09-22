<?php

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array		= array();
$order			= checkPostVar('order');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$order)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');

$sql = "UPDATE mallRN_admin_configuration SET widget_info = '{$order}' WHERE id = '{$my_id}'";
$mysql->query($sql);
		
$my_array[] = ["label"=>"Success"];

echo json_encode($my_array);

?>
