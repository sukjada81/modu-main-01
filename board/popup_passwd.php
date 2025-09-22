<?php

include_once('../php/popup_init.php'); 

$b_id		= checkGetVar('b_id');
$uid		= checkGetVar('uid');
$c_uid		= checkGetVar('c_uid');
$managers	= checkGetVar('managers');
$vendor		= checkGetVar('vendor');
$mypages	= checkGetVar('mypages');
$type		= isset($_GET['type']) ? add_escape_re_string($_GET['type']) : add_escape_re_string($_POST['type']);

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if(!$b_id || !$type || !$uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

$sql		= "SELECT * FROM mallRN_board_manager WHERE id = '{$b_id}'";
$board_info = $mysql->one_row($sql);

if(!$board_info) iframeViewError("해당 게시판이 없거나 삭제된 게시판 입니다.");

if($vendor == 1 && $_COOKIE['v_my_id'] && $b_id == 'vcounsel') {
	include_once(DEFAULT_PATH.PATH_LIB.'/checkVLogin.php');
	$my_id		= $v_my_id;
	$my_name	= $v_my_name;
}

if($managers == 1)		$bo_skin	= "../managers/board/skin/".$board_info['skin'];
else if($vendor == 1)	$bo_skin	= "../vendor/board/skin/".$board_info['skin'];
else					$bo_skin	= $skin."/board/".$board_info['skin'];

if(!is_dir($bo_skin)) iframeViewError("게시판 스킨이 삭제 되어 사용 하실 수 없습니다.");

$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) iframeViewError("등록된 글이 없거나 삭제되었습니다.");

$SUBJECT = specialStrReplace3($data['subject']);

$tplBo = new classTemplate;
$tplBo->define("main","{$bo_skin}/{$mobile_header}popup_passwd.html");
$tplBo->scan_area("main");

switch($type) {
	case "modify" : 
		$ACTION = "../{$Main}?channel=cs_board&b_id={$b_id}&b_mode=modify&uid={$uid}{$addstring}"; 
		$target = "_parent";
		$TTL	= "수정할 글";

		if($my_level < 100) { 
			if(!$data['id']) $tplBo->parse("is_guest");
			else if($data['id'] != $my_id) iframeViewError("회원정보가 일치하지 않습니다."); 
		}
	break;
	case "view" : 
		$ACTION = "../{$Main}?channel=cs_board&b_id={$b_id}&b_mode=view&uid={$uid}{$addstring}"; 
		$target = "_parent";
		$TTL	= "비밀글";

		if($my_level < 100) { 
			if($data['depth'] > 0) {
				if(!$data['o_id']) $tplBo->parse("is_guest");
				else if($data['o_id'] != $my_id) iframeViewError("회원정보가 일치하지 않습니다."); 
			}
			else {
				if(!$data['id']) $tplBo->parse("is_guest");
				else if($data['id'] != $my_id) iframeViewError("회원정보가 일치하지 않습니다."); 
			}
		}		
	break;
	case "delete" : 
		$ACTION = "board_post.php?{$addstring}"; 
		$target	= "HFrm";
		$TTL	= "삭제할 글";

		if($my_level < 100) { 
			if(!$data['id']) $tplBo->parse("is_guest");
			else if($data['id'] != $my_id) iframeViewError("회원정보가 일치하지 않습니다."); 
		}

		$tplBo->parse("is_post");
	break;
	case "comment_delete" : 
		if(!$c_uid) iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");

		$ACTION = "board_post.php?{$addstring}"; 
		$target	= "HFrm";
		$TTL	= "삭제할 댓글 작성자";

		$sql = "SELECT * FROM mallRN_board_{$b_id}_comment WHERE uid = '{$c_uid}'";
		if(!$data = $mysql->one_row($sql)) iframeViewError("등록된 글이 없거나 삭제되었습니다.");

		$SUBJECT = stripslashes($data['name']);
		if($board_info['privacy_type'] == 1) $SUBJECT =  mb_substr($SUBJECT, 0, 1, 'utf-8')." * ".mb_substr($SUBJECT, 2, mb_strlen($SUBJECT, 'utf-8'), 'utf-8');
		
		if($my_level < 100) { 
			if(!$data['id']) $tplBo->parse("is_guest");
			else if($data['id'] != $my_id) iframeViewError("회원정보가 일치하지 않습니다."); 
		}

		$tplBo->parse("is_post");
	break;
}

$tplBo->parse("main");
$tplBo->tprint("main");
$tplBo->close();

?>