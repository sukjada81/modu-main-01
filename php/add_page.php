<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

include_once(PATH_LIB.'/class.ListPaging.php');

if(!$uid = checkGetVar('uid')) Error('필수 정보가 제대로 넘어오지 못했습니다.');	

$sql	= "SELECT * FROM mallRN_add_page WHERE uid = '{$uid}'";
$data	= $mysql->one_row($sql);

$TITLE	= stripslashes($data['title']);
if($data['status'] == 1) alert("{$TITLE} 페이지가 존재하지 않거나 삭제 되었습니다.", "back");

if($data['detail_image_only'] == 0) {
	$EXPLAINS = add_escape_re_string($data['explains']);
}
else {
	$EXPLAINS = detailImageToTag2($data['uid'], $data['detail_image_type'], $data['detail_image'], 'add_page');
}

?>