<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array = array();
$id = $_GET['id'];

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$id) {
	$my_array[] = ['status' => 1, 'error' => '정보가 제대로 넘어오지 못했습니다.'];
	echo json_encode($my_array);
	exit;
}

if($id == 'manager') $my_array[] = ['status' => 1, 'error' => '사용할 수 없는 아이디 입니다.'];

$sql = "SELECT count(*) FROM mallRN_board_manager WHERE id = '{$id}'";
if($mysql->get_one($sql) > 0) {
	$my_array[] = ['status' => 1, 'error' => '이미 등록된 아이디 입니다.'];
	echo  json_encode($my_array);
}

echo json_encode(array('satatus' => 2));

?>