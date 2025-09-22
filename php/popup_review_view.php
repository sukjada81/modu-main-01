<?php

$pop_title	= "구매후기보기";

include_once('../php/popup_init.php'); 

$tpl = new classTemplate;
$tpl->define("main","{$skin}/{$mobile_header}popup_review_view.html");
$tpl->scan_area("main");

$uid = checkGetVar('uid');
if(!$uid) Error('필수 정보가 제대로 넘어오지 못했습니다.');

$sql	= "SELECT * FROM mallRN_review WHERE uid = '{$uid}'";
$data	= $mysql->one_row($sql);

$ck		= 0;
$attach_array = explode("|", $data['files']);
foreach($attach_array as $k => $v) {
	if($v) {
		$IMAGE	= DEFAULT_PATH."image/review/{$uid}/{$v}";
		$tpl->parse("loop_image");
		$ck		= 1;
	}
}

if($ck == 0) {
	$sql		= "SELECT image2, moddate FROM mallRN_goods WHERE uid = '{$data['g_uid']}'";
	$data2		= $mysql->one_row($sql);

	if($data2['image2'])	$IMAGE = DEFAULT_PATH."image/goods/img{$data2['image2']}?t={$data2['moddate']}";
	else					$IMAGE = DEFAULT_PATH."image/no_image.png";

	$tpl->parse("loop_image");
}
			
$G_NAME			= stripslashes($data['g_name']);
$OP_NAME		= stripslashes($data['op_name']);
if($OP_NAME) $tpl->parse("is_op_name");

$data['name']	= specialStrReplace2($data['name']);
$NAME			= mb_substr($data['name'], 0, 1, 'utf-8')." * ".mb_substr($data['name'], 2, mb_strlen($data['name'], 'utf-8'), 'utf-8');
$DATE			= date("Y-m-d", $data['signdate']);
$CONTENT		= ieHackCheck($data['content']);

for($i = 0; $i < $data['stars']; $i ++) $tpl->parse("loop_stars");

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

?>