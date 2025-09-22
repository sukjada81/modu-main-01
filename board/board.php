<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('_B2BMALL_BOARD_', '1');

$b_id			= checkGetVar('b_id');
$b_mode			= checkGetVar('b_mode');
$BOARD_CONTENT	= "";

if(!$b_id) Error("게시판 아이디가 없습니다.<br />board.php?b_id=xxxx 형태로 게시판아이디를 넣으셔야 사용 가능 합니다.");

$sql		= "SELECT * FROM mallRN_board_manager WHERE id = '{$b_id}'";
$board_info = $mysql->one_row($sql);

if(!$board_info) Error("해당 게시판이 없거나 삭제된 게시판 입니다.");

if(!$b_mode) {
	switch($board_info['start_page']) {
		case "1"	: $b_mode = "view"; break;
		case "2"	: $b_mode = "write"; break;
		default		: $b_mode = "list";
	}
}

$my_where = "";

if(!defined('__MANAGERS__')) {
	if($b_id == 'vnotice' || $b_id == 'vcounsel') Error("게시판 사용 권한이 없습니다.");
	
	$bo_skin		= $skin."/board/".$board_info['skin'];
	$DEFAULT_LINK	= "{$Main}?channel=cs_board&b_id={$b_id}";
}
else {
	$b_mode			= isset($_GET['b_mode']) ? $_GET['b_mode'] : "list";
	$bo_skin		= "skin/".$board_info['skin'];	
	$DEFAULT_LINK	= "board.php?b_id={$b_id}";

	if($b_id == 'vcounsel' && @$v_my_id) {
		$my_id		= $v_my_id;
		$my_name	= $v_my_name;
		$_GET['id']		= $my_id;
		$my_where		= "&& (id = '{$my_id}' || o_id = '{$my_id}' )";
		define('__MYPAGE2__',		'1');
	}
}

if(defined('__MYPAGE__')) {
	$b_mode			= isset($_GET['b_mode']) ? $_GET['b_mode'] : "list";
	$DEFAULT_LINK	= "{$Main}?channel={$channel}&b_id={$b_id}";
	$_GET['id']		= $my_id;
	$my_where		= "&& (id = '{$my_id}' || o_id = '{$my_id}' )";
}

if(!is_dir($bo_skin)) Error("게시판 스킨이 삭제 되어 사용 하실 수 없습니다.");

$BOARD_TITLE		= stripslashes($board_info['name']);
$error_msg_array	= array("list" => "글목록", "write" => "글쓰기", "view" => "글보기", "reply" => "답글쓰기", "modify" => "글수정");
$b_mode_array		= array("list" => "list", "view" => "view", "write" => "write", "modify" => "write", "reply" => "reply", "delete" => "write");
$b_mode2			= $b_mode_array[$b_mode];

if($board_info['access_'.$b_mode2] > 0) {
	if($board_info['access_'.$b_mode2] == 1) {
		if($my_level < 99) alert($error_msg_array[$b_mode]." 권한이 없습니다.", "back");
	}
	else if($board_info['access_'.$b_mode2] == 2) {
		if(!$my_id) alert($error_msg_array[$b_mode]." 권한이 없습니다.", "back");
	}
	else if($board_info['access_'.$b_mode2] == 3) {
		$access_level	= explode(",", $board_info['access_'.$b_mode2.'_level']);
		array_push($access_level, '99', '100');
		if(!$my_id || !in_array($my_level, $access_level)) alert($error_msg_array[$b_mode]." 권한이 없습니다.", "back");
	}
	else if($board_info['access_'.$b_mode2] == 4) {
		if($my_level < 99 && !$v_my_id) alert($error_msg_array[$b_mode]." 권한이 없습니다.", "back");
	}
}

if($board_info['header'] == 1) $BOARD_CONTENT = stripslashes($board_info['header_content']);

switch($b_mode) {
	case 'write': case 'modify':  case 'reply':  
		$b_page	= "write";
	break;
	case 'view' : 
		$b_page	= "view";
	break;
	case 'delete' : 
		$b_page	= "delete";
	break;
	default :
		$b_page	= "list";
}

if($b_mode == 'list' || $b_mode == 'view') {
	$ck_w1	= base64_encode(time());	
	$ck_w2	= md5($ck_w1.CONF_KEY);
}

if($board_info['start_page'] == 2) {
	$_GET['ck_w1']	= base64_encode(time());	
	$_GET['ck_w2']	= md5($_GET['ck_w1'].CONF_KEY);	
}

if($b_page == 'view' || $b_page == 'write') {
	$addstring			= "";
	$search_variable	= array('field' ,'keyword', 'limit', 'page', 'cate');
	foreach ($search_variable as $k => $v) {
		if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
		else $value = isset($_GET[$v]) ? $_GET[$v] : '';

		if($value) $addstring .= "&{$v}={$value}";
	}
}

if($b_page == 'view' && !$_GET['uid']) {
	$sql = "SELECT uid FROM mallRN_board_{$b_id} ORDER BY uid DESC LIMIT 1";
	if(!$_GET['uid'] = $mysql->get_one($sql)) {
		$b_page = "list";	
	}
}

$tplBo = new classTemplate;
$tplBo->define("main","{$bo_skin}/{$mobile_header}{$b_page}.html");
$tplBo->scan_area("main");

include_once dirname(__FILE__)."/{$b_page}.php";

$tplBo->parse("main");
$BOARD_CONTENT .= $tplBo->tprint("main", 1);
$tplBo->close();

if($b_mode == 'view' && $board_info['view_type'] == 1) {
	$tplBo = new classTemplate;
	$tplBo->define("main","{$bo_skin}/list.html");
	$tplBo->scan_area("main");

	include_once dirname(__FILE__)."/list.php";

	$tplBo->parse("main");
	$BOARD_CONTENT .= $tplBo->tprint("main", 1);
	$tplBo->close();	
}

if($board_info['footer'] == 1) $BOARD_CONTENT .= stripslashes($board_info['footer_content']);

unset($board_info);
?>