<?php

include_once('../php/popup_init.php'); 

$b_id		= checkGetVar('b_id');
$uid		= checkGetVar('uid');
$c_uid		= checkGetVar('c_uid');
$ck_w1		= checkGetVar('ck_w1');
$ck_w2		= checkGetVar('ck_w2');
$managers	= checkGetVar('managers');
$mypages	= checkGetVar('mypages');

if($c_uid)	$mode = "comment_reply";
else		$mode = "comment_write";

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if(!$b_id || !$uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

$sql		= "SELECT * FROM mallRN_board_manager WHERE id = '{$b_id}'";
$board_info = $mysql->one_row($sql);

if(!$board_info) iframeViewError("해당 게시판이 없거나 삭제된 게시판 입니다.");

if($managers == 1)	$bo_skin	= "../managers/board/skin/".$board_info['skin'];
else				$bo_skin	= $skin."/board/".$board_info['skin'];

if(!is_dir($bo_skin)) iframeViewError("게시판 스킨이 삭제 되어 사용 하실 수 없습니다.");

$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) iframeViewError("등록된 글이 없거나 삭제되었습니다.");

$tplBo = new classTemplate;
$tplBo->define("main","{$bo_skin}/{$mobile_header}popup_comment.html");
$tplBo->scan_area("main");

if(!$my_id) $tplBo->parse("is_guest");

$tplBo->parse("main");
$tplBo->tprint("main");
$tplBo->close();

?>