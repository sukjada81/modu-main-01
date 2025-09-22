<?php

if(!defined('_B2BMALL_BOARD_')) exit; // 개별 페이지 접근 불가

$b_mode				= isset($_GET['b_mode']) ? $_GET['b_mode'] : 'write';
$uid				= checkGetVar('uid');
$cookie_temp_upload = isset($_COOKIE['temp_upload']) ? previlDecode($_COOKIE['temp_upload']) : '';

if($b_mode == 'modify') {
	
	if(!$uid || !is_numeric($uid)) alert("필수정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid = '{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 글이 없거나 삭제되었습니다.", "back");

	if($my_level < 100) {
		if(!$data['id']) {
			$passwd	= checkPostVar('passwd');
			if(!$passwd) alert("필수정보가 제대로 넘어오지 못했습니다.", "back");
			$passwd = md5($passwd);
			if($passwd != $data['passwd']) alert("비밀번호가 일치하지 않습니다.", "back");
		}
		else if($data['id'] != $my_id) alert("회원정보가 일치하지 않습니다.", "back");
	}

	$item_array = array('notice', 'id', 'name', 'subject', 'cate', 'content', 'files', 'links', 'add1', 'add2', 'add3', 'add4', 'add5', 'secret');
	
	foreach($item_array as $k => $v) {
		if($v != 'content') ${$v} = specialStrReplace3($data[$v]);
		else				${$v} = stripslashes($data[$v]);
	}
	
	${"checked_notice_".$notice}	= "checked='checked'";
	${"checked_secret_".$secret}	= "checked='checked'";

	define('UPLOAD_FOLDER', dirname(__FILE__)."/data/{$b_id}");

	if($files)	$attach_array = explode("|", $files);
	else		$attach_array = "";

	if($links)	$links_array = explode("|", $links);
	else		$links_array = "";

	$temp_upload = $uid;

	$TTL = "글 수정";	
	$tplBo->parse("is_write");

}
else {
	$ck_w1		= checkGetVar('ck_w1');
	$ck_w2		= checkGetVar('ck_w2');
	$cate		= checkGetVar('cate');
	$file_array = "";

	define('UPLOAD_FOLDER', dirname(__FILE__).'/../image/temp_upload');
	
	if($cookie_temp_upload) {
		delTree(UPLOAD_FOLDER.'/'.$cookie_temp_upload);
	}
	$temp_upload = date("Ymdhis_").getCode(4);
	
	if($b_mode == 'reply') {
		if(!$uid || !is_numeric($uid)) alert("필수정보가 제대로 넘어오지 못했습니다.", "back");
		
		$sql = "SELECT * FROM mallRN_board_{$b_id} WHERE uid = '{$uid}'";
		if(!$data = $mysql->one_row($sql)) alert("원본글이 없거나 삭제되었습니다.", "back");

		$subject = "RE: ".stripslashes($data['subject']);
		$content = stripslashes($data['content']);
		$content = add_escape_re_string($content);

		$TTL	= "답글 등록";
		$tplBo->parse("is_reply");
	}
	else {
		$b_mode	= "write";
		$TTL	= "글 등록";
		$tplBo->parse("is_write");
	}	

	$add1 = $add2 = $add3 = $add4 = $add5 = "";
}

######################## 옵션 설정 #############################
$ck_option	= 0;
if($my_level == 100 && $b_mode != 'reply') {
	@$tplBo->parse("is_notice");
	$ck_option	= 1;
}

if($board_info['secret_type'] > 0 && $b_mode != 'reply') {
	$secret_disable = "";
	if($board_info['secret_type'] == 1) {
		$checked_secret_1	= "checked='checked'";
		$secret_disable		= "disabled";
	}
	@$tplBo->parse("is_secret");
	$ck_option	= 1;
}

if($ck_option == 1) @$tplBo->parse("is_options");
######################## 옵션 설정 #############################

######################## 분류 설정 #############################
$cate_info = explode("|*|", $board_info['cate_info']);
if($cate_info[0] > 100) {
	for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
		$cate_info2 = explode("|", $cate_info[$i]);

		$cate_num = $cate_info2[0];
		$cate_name = $cate_info2[1];
		
		$tplBo->parse("loop_cate");
	}
	$tplBo->parse("is_cate");	
}
unset($cate_info, $cate_info2, $cate_name, $cate_num);
######################## 분류 설정 #############################

######################## 첨부파일 및 관련링크 #############################
for($i = 1; $i < 6; $i ++) {
	if($i > 1)	$display = "displayNone";
	else		$display = "";
	
	$i2 = $i - 1;
	$attach_name	= isset($attach_array[$i2]) ? $attach_array[$i2] : '';
	$links_name		= isset($links_array[$i2]) ? $links_array[$i2] : '';

	if($links_name) $display = "";

	if($board_info['upload_file'] == 1) $tplBo->parse("loop_attach");
	if($board_info['links'] == 1) $tplBo->parse("loop_links");
}
######################## 첨부파일 및 관련링크 #############################

######################## 업로드 폴더 생성 #############################
if(!is_dir(UPLOAD_FOLDER.'/'.$temp_upload)) mkdir(UPLOAD_FOLDER.'/'.$temp_upload,0744);	
$temp_upload = previlEncode($temp_upload);
SetCookie("temp_upload", $temp_upload, 0, "/");
######################## 업로드 폴더 생성 #############################

########################  추가항목 #############################
for($i = 1; $i < 6; $i ++) {
	if($board_info['form_add'.$i] == 0) continue;
	if($board_info['form_add'.$i] == 2) $add_required = 'required="required"';
	else								$add_required = '';

	if($my_level == 100 && $b_mode == 'reply') $add_required = '';

	$ADD_TITLE	= stripslashes($board_info['form_add'.$i.'_title']);
	$ADD_VALUE	= ${"add".$i};

	$tplBo->parse("loop_add");
}
########################  추가항목 #############################


if(!$my_id) {
	
	if($b_mode != 'modify') {
		$sql			= "SELECT agreement_info5 FROM mallRN_configuration WHERE uid = 2";
		$agreement_info	= stripslashes($mysql->get_one($sql));
		$tplBo->parse("is_agreement");
		$tplBo->parse("is_default");
	}
	else $tplBo->parse("is_modify");

	$tplBo->parse("is_guest");
}


?>