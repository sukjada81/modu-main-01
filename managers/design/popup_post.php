<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/popup');
define('TEMP_UPLOAD_FOLDER',	'../../image/temp_upload');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_popup';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'type', 'status', 'position', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page			= "popup_list.php?{$addstring}";

$item_array			= array('name', 'status', 'type', 'period', 'position', 'input_position', 'input_size', 'image_only', 'link1', 'content');
$item_default		= array('type', 'period');	
$item_able_value	= array('status' => ['0', '1', '2'], 'position' => ['5', '0', '1', '2', '3', '4', '6', '7', '8', '9']);

if($mode=='write' || $mode=='modify') {
	
	$_POST['name']		= checkPostVar('name');
	$_POST['s_date']	= checkPostVar('s_date');
	$_POST['e_date']	= checkPostVar('e_date');

	if($_POST['e_date']) $_POST['e_date'] = $_POST['e_date']." 23:59:59";

	if($_POST['s_date']) array_push($item_array, 's_date');
	if($_POST['e_date']) array_push($item_array, 'e_date');

	$_POST['input_position']	= checkPostVar('position_x')."|".checkPostVar('position_y');
	$_POST['input_size']		= checkPostVar('size_x')."|".checkPostVar('size_y');
	
}

switch($mode) {
    case "write" :  

		if(!$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$_POST['signdate']	= time();

		array_push($item_array, 'signdate');

		######################## 배너 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_able_value)) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		$uid = $mysql->InsertNo();
		$upload_dir	=  UPLOAD_FOLDER.'/'.$uid;
		if(!is_dir($upload_dir)) mkdir($upload_dir, 0707);	
		######################## 배너  등록  #########################

		######################## 이미지 등록  #########################
		if(!preg_match("/none/i",$_FILES['image1']['tmp_name']) && $_FILES['image1']['tmp_name']) {									
			$image1 = upFile($_FILES['image1']['tmp_name'], $_FILES['image1']['name'], UPLOAD_FOLDER.'/'.$uid, 1, "popup_image", 1);

			$sql = "UPDATE {$table_name} SET image1 = '{$image1}' WHERE uid = '{$uid}'";
			$mysql->query($sql);
		}		
		######################## 이미지 등록  #########################

		if(isset($_POST['temp_upload'])) {
			######################## 내용 이미지  #########################
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
				copyTree($temp_upload, $upload_dir);	
				if($_POST['content']) {
					$content = str_replace($temp_upload, $upload_dir, $_POST['content']);
					$sql = "UPDATE {$table_name} SET content = '{$content}' WHERE uid = '{$uid}'";
					$mysql->query($sql);
					unset($content);
				}
			}		
			delTree($temp_upload);
			SetCookie("temp_upload", "", -999, "/");
			######################## 내용 이미지  #########################
		}

		alertMsg("팝업이 등록 되었습니다.", $link_page);

    break;	

	case "modify" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid || !$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 팝업이 존재하지 않거나 삭제 되었습니다.");

		######################## 배너 수정  #########################
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_able_value)) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}

		if(!preg_match("/none/i",$_FILES["image1"]['tmp_name']) && $_FILES["image1"]['tmp_name']) {
			alertMsg("11");
			$image1 = upFile($_FILES["image1"]['tmp_name'],$_FILES["image1"]['name'], UPLOAD_FOLDER.'/'.$uid, 1, "popup_image", 1);
			$sql .= ", image1 = '{$image1}'";
		}			
		else {
			$image1_del = checkPostVar('image1_del');
						
			if($image1_del == '1') {
				@unlink(UPLOAD_FOLDER.'/'.$uid.'/'. $row['image1']);
				$sql .= ", image1 = ''";
			}
		}

		$sql .= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);
		######################## 배너 수정  #########################

		alertMsg("팝업이 수정 되었습니다.", $link_page);			
		
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
		alertMsg("{$i}건의 팝업이 삭제 되었습니다!", $link_page);		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
