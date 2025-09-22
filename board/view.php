<?php

if(!defined('_B2BMALL_BOARD_')) exit; // 개별 페이지 접근 불가

define('BOARD_DATA', dirname(__FILE__).'/data');

$b_mode		= isset($_GET['b_mode']) ? $_GET['b_mode'] : 'write';
$uid		= checkGetVar('uid');
$focus		= checkGetVar('focus');

if(!$uid || !is_numeric($uid)) alert("필수 정보가 제대로 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid = '{$uid}'";
if(!$data = $mysql->one_row($sql)) alert("등록된 글이 없거나 삭제되었습니다.", "back");

$item_array = array('notice', 'id', 'name', 'subject', 'cate', 'content', 'count', 'count_comment', 'files', 'links', 'add1', 'add2', 'add3', 'add4', 'add5', 'secret', 'ip', 'depth', 'o_id', 'passwd', 'signdate');

foreach($item_array as $k => $v) {
	if($v == 'content') ${$v} = add_escape_re_string(stripslashes($data[$v]));		
	else				${$v} = specialStrReplace2($data[$v]);
}

########################  분류 정보 #############################
$cate_info = explode("|*|", $board_info['cate_info']);
if($cate_info[0] > 100) {
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);

		if($cate == $cate_info2[0])	{
			$cate_name = $cate_info2[1];
			$tplBo->parse("is_cate_name");	
			break;
		}
	}	
}
unset($cate_info, $cate_info2);
########################  분류 정보 #############################

########################  추가항목 #############################
for($i = 1; $i < 6; $i ++) {
	if($board_info['form_add'.$i] == 0) continue;
	$add_title	= stripslashes($board_info['form_add'.$i.'_title']);
	$add_value	= ${"add".$i};

	$tplBo->parse("loop_adds");
}
########################  추가항목 #############################

if($board_info['privacy_type'] == 1 && !defined('__MANAGERS__')) $name = mb_substr($name, 0, 1, 'utf-8')." * ".mb_substr($name, 2, mb_strlen($name, 'utf-8'), 'utf-8');

################## 비밀글 ##################
if($secret == 1) {
	if($my_level < 100) {

		$get_passwd = checkPostVar('passwd');
		$get_passwd = MD5($get_passwd);

		if($depth > 0) {			
			if(!$o_id) {			
				if(!$get_passwd) alert("필수 정보가 제대로 넘어오지 못했습니다.", "back");				
				if($get_passwd != $passwd) alert("비밀번호가 일치하지 않습니다.", "back");
			}
			else if($o_id != $my_id) alert("비밀글 입니다.", "back"); 
		}
		else {		
			if(!$id) {			
				if(!$get_passwd) alert("필수 정보가 제대로 넘어오지 못했습니다.", "back");				
				if($get_passwd != $passwd) alert("비밀번호가 일치하지 않습니다.", "back");
			}
			else if($id != $my_id) alert("비밀글 입니다.", "back"); 
		}
	}
}
################## 비밀글 ##################

if($notice == 0) $tplBo->parse("is_notice");

########################  이미지 캐시 안되게 처리 #############################
$arrimgurl		= array();
$p				= explode("\n", stripslashes($content));
for($u=0; $u<count($p); $u++) {
	if(preg_match("/title=/i", $p[$u])) {
		$pos1 = strpos($p[$u], "title=", 0);	
		$pos2 = strpos($p[$u], "\"", $pos1)+1;
		$pos3 = strpos($p[$u], "\"", $pos2);

		$tmp_name = substr($p[$u], $pos1, ($pos3-$pos1));		
		$p[$u] = str_replace($tmp_name, "", $p[$u]);
	}
	
	while(preg_match("/[^=\"']*\.(gif|jpg|bmp|png)([?|\"|\'| |>]){1}/i", $p[$u], $val)){
		$arrimgurl[substr($val[0],0,-1)]=substr($val[0],0,-1);
		$p[$u]=str_replace(substr($val[0],0,-1),"",$p[$u]);
	}
}

foreach ($arrimgurl as $k => $v) {	
	$v			= trim($v);
	if(preg_match("/{$_SERVER['HTTP_HOST']}/i",$v)) continue;
	$filename	= substr(strrchr($v,"/"),1);
	if(defined('__MANAGERS__'))	$images	= "../../board/data/{$b_id}/{$uid}/".$filename;
	else						$images	= DEFAULT_PATH."board/data/{$b_id}/{$uid}/".$filename;
	$times		= filectime($images);

	$content = str_replace($filename, $filename."?v={$times}" ,  $content);	
}
########################  이미지 캐시 안되게 처리 #############################

$content	= ieHackCheck($content);
$date		= date("Y-m-d H:i:s", $signdate);

################## 첨부 파일 ##################
if($files) {
	$attach_array = explode("|", $files);
	foreach($attach_array as $k => $v) {
		if($board_info['types'] =='3')	{			
			if(defined('__MANAGERS__'))	$images	= "../../board/data/{$b_id}/{$uid}/".$v;
			else						$images	= DEFAULT_PATH."board/data/{$b_id}/{$uid}/".$v;
			$times			= @filectime($images);
			$images			.= "?v=".$times;
			$tplBo->parse("loop_image");			
		}
		else {
			$attach_name	= $v;
			$attach_size	= getFilesize(BOARD_DATA."/{$b_id}/{$uid}/".$v);
			$tplBo->parse("loop_attach");
		}
	}
	unset($attach_name, $attach_size, $attach_array);

	if($board_info['types'] =='3')	$tplBo->parse("is_image");
	else							$tplBo->parse("is_attach");	
}
################## 첨부 파일 ##################

################## 관련링크 ##################
if($links) {
	$links_array = explode("|", $links);
	foreach($links_array as $k => $v) {
		if(!$v) continue;
		$link_url = $v;		
		$tplBo->parse("loop_links");
	}
	unset($links_url, $links_array);
	$tplBo->parse("is_links");
}
################## 관련링크 ##################

################## 댓글 ##################
$comment_access = 1;
if($board_info['access_comment'] > 0) {
	if($board_info['access_comment'] == 1) {
		if($my_level < 99) $comment_access = 0;
	}
	else if($board_info['access_comment'] == 2) {
		if(!$my_id) $comment_access = 0;
	}
	else if($board_info['access_comment'] == 3) {
		$access_level	= explode(",", $board_info['access_comment_level']);
		array_push($access_level, '99', '100');
		if(!$my_id || !in_array($my_level, $access_level)) $comment_access = 0;
	}
	else if($board_info['access_comment'] == 4) {
		if($my_level < 99 && !$v_my_id) $comment_access = 0;
	}
}

if($comment_access == 1) @$tplBo->parse("is_comment");

$sql	= "SELECT * FROM  mallRN_board_{$b_id}_comment WHERE b_uid = '{$uid}' ORDER BY main DESC, sub ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){ 
	$CO_UID		= $row['uid'];
	$CO_DEPTH	= $row['depth'];
		
	if($row['dels'] == 1) {
		$CO_NAME = $CO_DATE = "";
		$tplBo->parse("is_delete_comment");
	}
	else {
		$CO_NAME	= specialStrReplace2($row['name']);
		if($board_info['privacy_type'] == 1) $CO_NAME =  mb_substr($CO_NAME, 0, 1, 'utf-8')." * ".mb_substr($CO_NAME, 2, mb_strlen($CO_NAME, 'utf-8'), 'utf-8');

		$CO_CONTENT = ieHackCheck($row['content']);
		if($row['signdate'] > strtotime(date("Y-m-d"))) $CO_DATE = date("H:i:s", $row['signdate']);
		else											$CO_DATE = date("Y-m-d", $row['signdate']);

		if($CO_DEPTH > 0) {
			for($i = 1; $i < $CO_DEPTH; $i ++) {
				$tplBo->parse("loop_empty");
			}
			$tplBo->parse("is_co_reply");
		}

		if(!$row['id']) {
			$tplBo->parse("is_comment_delete");
		}
		else if($my_level == 100 || $row['id'] == $my_id) {
			$tplBo->parse("is_comment_delete");
		}

		if($comment_access == 1) $tplBo->parse("is_comment_reply");
	}

	$tplBo->parse("loop_comment");
}
################## 댓글 ##################

################## 조회수 증가 ##################
@session_start();
$board_view = isset($_SESSION['board_view']) ? $_SESSION['board_view'] : '';
if($board_view) {
	$board_view = explode(",", $board_view);
	if(!in_array("{$b_id}:{$uid}", $board_view)){      
		$sql = "UPDATE mallRN_board_{$b_id} SET count = count + 1 WHERE uid = '{$uid}'";
		$mysql->query($sql);
		array_push($board_view, $b_id.':'.$uid);
		$board_view = implode(',', $board_view);	  
		$_SESSION['board_view'] = $board_view;
		$count++;		
	} 
	unset($board_view);
}
else {
	$sql = "UPDATE mallRN_board_{$b_id} SET count = count + 1 WHERE uid = '{$uid}'";
	$mysql->query($sql);
	$_SESSION['board_view'] = $b_id.':'.$uid;
	$count++;		
}
################## 조회수 증가 ##################

################## 버튼 권한 ##################
if($my_level == 100) {
	$tplBo->parse("is_reply");
	$tplBo->parse("is_write");	
	$tplBo->parse("is_modify");
	$tplBo->parse("is_modify_member");
}
else {
	if(!$id) {
		$tplBo->parse("is_modify");
		$tplBo->parse("is_modify_guest");
	}
	else if($id == $my_id) {
		$tplBo->parse("is_modify");
		$tplBo->parse("is_modify_member");
	}
	
	$ables = "0";
	if($board_info['access_write'] > 0) {
		if($board_info['access_write'] == 2) {
			if($my_id) $ables = "1";
		}
		else if($board_info['access_write'] == 3) {
			$access_level	= explode(",", $board_info['access_write_level']);
			if($my_id && in_array($my_level, $access_level)) $ables = "1";
		}
		if($board_info['access_write'] == 4) {
			if($v_my_id) $ables = "1";
		}
	}
	else $ables = "1";
		
	if($ables == 1) {
		if($secret != 1) $tplBo->parse("is_reply");
		$tplBo->parse("is_write");
	}
	unset($ables);
}
################## 버튼 권한 ##################

if($focus == 1) $tplBo->parse("is_focus");

if($board_info['view_type'] == 0) {

	$field		= isset($_GET['field']) ?  $_GET['field'] : '';
	$keyword	= isset($_GET['keyword']) ?  urldecode($_GET['keyword']) : '';
	$cate		= isset($_GET['cate']) ?  $_GET['cate'] : '';
	
	$bo_where = "";
	if($field && $keyword) {
		if($field == 'S') $bo_where = "&& INSTR(subject, '{$keyword}')";
		else if($field == 'C') $bo_where = "&& INSTR(content, '{$keyword}')";
		else if($field == 'N') $bo_where = "&& INSTR(name, '{$keyword}')";
		else if($field == 'I') $bo_where = "&& INSTR(id, '{$keyword}')";
		else if($field == 'SC') $bo_where = "&& (INSTR(subject, '{$keyword}') || INSTR(content, '{$keyword}'))";
	}

	################## 이전글 다음글 ##################
	$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid != {$uid} && dels = 0 && notice >= {$data['notice']} && idx >= {$data['idx']} && main >= {$data['main']} && sub >= {$data['sub']} {$my_where} {$bo_where} ORDER BY notice ASC, idx ASC, main ASC, sub ASC LIMIT 1";

	$p_data = $mysql->one_row($sql);	
	if($p_data) {		
		$p_subject	= specialStrReplace2($p_data['subject']);
		$p_uid		= $p_data['uid'];

		$p_secret	= "";
		if($p_data['secret'] == 1) {
			if($my_level < 100) {
				if(!$p_data['id']) $p_secret = 1;
				else if($p_data['id'] != $my_id && $p_data['o_id'] != $my_id) $p_secret = 2;
			}
		}

		if($p_data['notice'] == 0) $tplBo->parse("is_pnotice");
		if($p_data['secret'] == 1) $tplBo->parse("is_psecret");
		if($p_data['depth'] > 0) {
			$EMPTY = "";
			for($i = 1; $i < $p_data['depth']; $i ++) $EMPTY .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$tplBo->parse("is_pdepth");
		}

		$tplBo->parse("is_prev_article");
		unset($p_data, $p_subject, $p_uid, $p_secret);
	}	

	$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid != {$uid} && dels = 0 && notice <= {$data['notice']} && idx <= {$data['idx']} && main <= {$data['main']} && sub <= {$data['sub']} {$my_where} {$bo_where} ORDER BY notice DESC, idx DESC, main DESC, sub DESC LIMIT 1";

	$n_data = $mysql->one_row($sql);	
	if($n_data) {		
		$n_subject	= specialStrReplace2($n_data['subject']);
		$n_uid		= $n_data['uid'];
		
		$n_secret	= "";
		if($n_data['secret'] == 1) {
			if($my_level < 100) {
				if(!$n_data['id']) $n_secret = 1;
				else if($n_data['id'] != $my_id && $n_data['o_id'] != $my_id) $n_secret = 2;
			}
		}

		if($n_data['notice'] == 0) $tplBo->parse("is_nnotice");
		if($n_data['secret'] == 1) $tplBo->parse("is_nsecret");
		if($n_data['depth'] > 0) {
			$EMPTY = "";
			for($i = 1; $i < $n_data['depth']; $i ++) $EMPTY .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$tplBo->parse("is_ndepth");
		}

		$tplBo->parse("is_next_article");
		unset($n_data, $n_subject, $n_uid, $n_secret);
	}
	$tplBo->parse("is_pn_article");
	################## 이전글 다음글 ##################
}
else {
	$tplBo->parse("is_empty");	
}

?>