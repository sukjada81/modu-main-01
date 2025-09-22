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
	case "conf" :  

		$item_array			= array('inquiry_cate_info', 'inquiry_secret_type', 'inquiry_privacy_type', 'inquiry_access_write');
		$item_default		= array('inquiry_privacy_type');
		$item_able_value	= array('inquiry_secret_type' => ['0', '1', '2'], 'inquiry_access_write' => ['0', '1', '2', '3']);

		if(isset($_POST['cate_order'])) {
			$cate_order			= explode(",", $_POST['cate_order']);
			$cate_max_num		= $_POST['cate_max_num'];		
			$_POST['inquiry_cate_info']	= $cate_max_num."|*|".multiPostVar($cate_order, array('cate_num','cate_name'));		
		}
		else $_POST['inquiry_cate_info'] = "";

		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='1'";
		$mysql->query($sql);

		logMsg("상품문의가 설정 되었습니다.","success");
		
	break;

    case "answer" :  

		$uid	= checkPostVar('uid');
		$answer	= checkPostVar('answer');
		
		if(!$uid || strlen($answer) == 0) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 상품문의가 존재하지 않거나 삭제 되었습니다.");

		$sql = "UPDATE {$table_name} SET answer = '{$answer}' WHERE uid = '{$uid}'";
		$mysql->query($sql);
		
		alertMsg("답변이 등록 되었습니다.", $link_page);			
		
	break;

	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);
			
			deltree(UPLOAD_FOLDER.'/'.$uid);
		}
		alertMsg("{$i}건의 상품문의가 삭제 되었습니다!", $link_page);		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
