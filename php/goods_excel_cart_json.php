<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();
$mode		= add_escape_re_string($_POST['mode']);

if($mode == 'delete') {
	
	$uid		= checkPostVar('uid');
	
	if(!$uid) json_error_msg('필수 정보가 넘어오지 못했습니다.');

	$sql = "DELETE FROM mallRN_excel_cart WHERE cart_id = '{$cart_id}' && uid = '{$uid}'";
	$mysql->query($sql);
	
}
$my_array[] = ["ok" => 1];	
echo json_encode($my_array);

?>