<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/banner');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_banner';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'code', 'status', 'target', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "banner_list.php?{$addstring}";

$item_array = array('name', 'code', 'link1', 'status', 'target', 's_date', 'e_date');
$item_default = array('status', 'target');	

if($mode=='write' || $mode=='modify') {
	
	$_POST['name']		= checkPostVar('name');
	$_POST['s_date']	= checkPostVar('s_date');
	$_POST['e_date']	= checkPostVar('e_date');

	if($_POST['e_date']) $_POST['e_date'] = $_POST['e_date']." 23:59:59";

	if(!$_POST['s_date']) $_POST['s_date'] = "1000-01-01 00:00:00";
	if(!$_POST['e_date']) $_POST['e_date'] = "1000-01-01 23:59:59";
	
}

switch($mode) {
    case "write" :  

		if(!$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$_POST['signdate']	= time();

		$sql = "SELECT MAX(sequence) FROM {$table_name} WHERE code = '{$_POST['code']}'";
		if($_POST['sequence'] = $mysql->get_one($sql)) $_POST['sequence'] ++;
		else $_POST['sequence'] = 1;
				
		array_push($item_array, 'sequence', 'signdate');
		
		######################## 배너 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		$uid = $mysql->InsertNo();
		######################## 배너  등록  #########################

		######################## 이미지 등록  #########################
		if(!preg_match("/none/i",$_FILES['image1']['tmp_name']) && $_FILES['image1']['tmp_name']) {									
			if(!is_dir(UPLOAD_FOLDER.'/'.$uid)) mkdir(UPLOAD_FOLDER.'/'.$uid, 0707);
			$image1 = upFile($_FILES['image1']['tmp_name'], $_FILES['image1']['name'], UPLOAD_FOLDER.'/'.$uid, 1, $_POST['code']."_image", 1);

			$sql = "UPDATE {$table_name} SET image1 = '{$image1}' WHERE uid = '{$uid}'";
			$mysql->query($sql);
		}		
		######################## 이미지 등록  #########################

		alertMsg("배너가 등록 되었습니다.", $link_page);

    break;	

	case "modify" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid || !$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$_POST['moddate']	= time();
	
		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 배너가 존재하지 않거나 삭제 되었습니다.");

		array_push($item_array, 'moddate');

		######################## 배너 수정  #########################
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}

		if(!preg_match("/none/i",$_FILES["image1"]['tmp_name']) && $_FILES["image1"]['tmp_name']) {			
			$image1 = upFile($_FILES["image1"]['tmp_name'],$_FILES["image1"]['name'], UPLOAD_FOLDER.'/'.$uid, 1, $_POST['code']."_image", 1);
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

		alertMsg("배너가 수정 되었습니다.", $link_page);			
		
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
		alertMsg("{$i}건의 배너가 삭제 되었습니다!", $link_page);		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
