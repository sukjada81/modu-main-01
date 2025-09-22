<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/inquiry');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_inquiry';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'vendor', 'cate', 'answer', 'id', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "inquiry_list.php?{$addstring}";

switch($mode) {
	case "answer" :  

		$uid	= checkPostVar('uid');
		$answer	= checkPostVar('answer');
		
		if(!$uid || strlen($answer) == 0) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE vendor = '{$v_my_id}' && uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 상품문의가 존재하지 않거나 삭제 되었습니다.");

		$sql = "UPDATE {$table_name} SET answer = '{$answer}' WHERE uid = '{$uid}'";
		$mysql->query($sql);
		
		alertMsg("답변이 등록 되었습니다.", $link_page);			
		
	break;	

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
