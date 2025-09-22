<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/add_page');
define('TEMP_UPLOAD_FOLDER', '../../image/temp_upload');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_add_page';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'status', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "add_page_list.php?{$addstring}";


if($mode=='write' || $mode=='modify3') {
	$_POST['detail_image']		= checkPostVar('detail_image_order');
	$_POST['title']				= checkPostVar('title');
}

$item_array			= array('title', 'detail_image', 'detail_image_only', 'detail_image_type', 'explains', 'status');
$item_default		= array('detail_image_only', 'status');	
$item_able_value	= array('detail_image_type' => ['1', '2']);	

switch($mode) {
    case "write" :  

		if(!$_POST['title']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$_POST['signdate']	= time();
				
		array_push($item_array, 'signdate');

		######################## 추가페이지 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		$uid = $mysql->InsertNo();
		######################## 추가페이지 등록  #########################

		if(isset($_POST['temp_upload'])) {
			######################## 상품상세 이미지  #########################
			$ck_files = 0;
			$temp_upload = TEMP_UPLOAD_FOLDER.'/'.previlDecode($_POST['temp_upload']);
			$handle	= @opendir($temp_upload);	
			while ($file = @readdir($handle)) {
				if($file != '.' && $file != '..') {
					$ck_files = 1;
					break;
				}
			}
			@closedir($handle);	

			if($ck_files==1) {			
				if(!is_dir(UPLOAD_FOLDER.'/'.$uid)) mkdir(UPLOAD_FOLDER.'/'.$uid, 0707);
				copyTree($temp_upload, UPLOAD_FOLDER.'/'.$uid);	
				if($_POST['explains']) {
					$explains = str_replace($temp_upload, UPLOAD_FOLDER.'/'.$uid, $_POST['explains']);
					$sql = "UPDATE {$table_name} SET explains = '{$explains}' WHERE uid='{$uid}'";
					$mysql->query($sql);
					unset($explains);
				}
			}		
			delTree($temp_upload);
			######################## 상품상세 이미지  #########################
		}

		alertMsg("추가페이지가 등록 되었습니다.", $link_page);

    break;	

	case "modify3" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid || !$_POST['title']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 추가페이지가 존재하지 않거나 삭제 되었습니다.");
		
		########################추가페이지 수정  #########################
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);
		######################## 추가페이지 수정  #########################

		######################## 업로드 이미지 체크  #########################
		$upload_folder	= UPLOAD_FOLDER.'/'.$uid;
		$upload_image	= array();
		if($_POST['detail_image']) $upload_image = explode(",", $_POST['detail_image']);
		if($image1) $upload_image[] = $image1;
		
		if(count($upload_image) > 0) {
			$handle	= @opendir($upload_folder);
			while ($file = @readdir($handle)) {
				if($file != '.' && $file != '..') {
					if(!in_array($file, $upload_image)) unlink("{$upload_folder}/{$file}");
				}
			}
			@closedir($handle);			
		}
		unset($upload_folder, $upload_image);
		######################## 업로드 이미지 체크  #########################
	
		alertMsg("추가페이지가 수정 되었습니다.", $link_page);			
		
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
		alertMsg("{$i}건의 추가페이지가 삭제 되었습니다!", $link_page);		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
