<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

define('DEFAULT_PATH',			'../');
define('BOARD_DATA',			'data');

include_once('../php/init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$b_id		= checkPostVar('b_id');
$uid		= checkPostVar('uid');
$name		= urldecode(checkPostVar('name'));

if(!$b_id || !$uid || !is_numeric($uid) || !$name) logMsg("필수 정보가 넘어오지 못했습니다.");

$sql		= "SELECT * FROM mallRN_board_manager WHERE id = '{$b_id}'";
$board_info = $mysql->one_row($sql);

if(!$board_info) logMsg("해당 파일이 없거나 삭제되었습니다.");

if($board_info['access_view'] > 0) {
	if($board_info['access_view'] == 1) {
		if($my_level < 99) logMsg("파일다운 권한이 없습니다.");
	}
	else if($board_info['access_view'] == 2) {
		if(!$my_id) logMsg("파일다운 권한이 없습니다.");
	}
	else if($board_info['access_view'] == 3) {
		$access_level	= explode(",", $board_info['access_view_level']);
		array_push($access_level, '99', '100');
		if(!$my_id || !in_array($my_level, $access_level)) logMsg("파일다운 권한이 없습니다.");
	}
}

$sql	= "SELECT files FROM mallRN_board_{$b_id} WHERE uid = '{$uid}'";
$attach = $mysql->get_one($sql);

if(!$attach || !file_exists(BOARD_DATA."/{$b_id}/{$uid}/{$name}")) logMsg("해당 파일이 없거나 삭제되었습니다.");

fileDown(BOARD_DATA."/{$b_id}/{$uid}/{$name}", $name);

?>
