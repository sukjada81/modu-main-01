<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/exhibition');
define('TEMP_UPLOAD_FOLDER', '../../image/temp_upload');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_exhibition';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'discount_yn', 'status', 'cate_info', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "exhibition_list.php?{$addstring}";


if($mode=='write' || $mode=='modify2') {

	$_POST['detail_image']		= checkPostVar('detail_image_order');
	$_POST['discount_yn']		= checkPostVar('discount_yn');
	$_POST['name']				= checkPostVar('name');
	$_POST['s_date']			= checkPostVar('s_date');
	$_POST['e_date']			= checkPostVar('e_date');
	$_POST['status']			= 0;

	if(isset($_POST['cate_order'])) {
		$cate_order			= explode(",", $_POST['cate_order']);
		$cate_max_num		= $_POST['cate_max_num'];		
		$_POST['cate_info']	= $cate_max_num."|*|".multiPostVar($cate_order, array('cate_num','cate_name'));		
	}
	else $_POST['cate_info'] = "";
	
	if($_POST['discount_yn'] == 'Y') {
		if(!$_POST['s_date'] || !$_POST['e_date']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		$today	= date("Y-m-d");				
		
		$_POST['status'] = 1;
		if($_POST['s_date'] <= $today && $_POST['e_date'] >= $today) $_POST['status'] = 2;
		else if($_POST['e_date'] < $today) $_POST['status'] = 3;

		$_POST['e_date'] = $_POST['e_date']." 23:59:59";
	}
}

$item_array			= array('name', 'discount_yn', 'discount', 's_date', 'e_date', 'cate_info', 'detail_image', 'detail_image_only', 'detail_image_type', 'explains', 'status');
$item_default		= array('detail_image_only');	
$item_able_value	= array('detail_image_type' => ['1', '2']);	


switch($mode) {
    case "write" :  

		if(!$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$_POST['signdate']	= time();
				
		array_push($item_array, 'signdate');

		######################## 모음전 등록  #########################		
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
		######################## 모음전 등록  #########################

		######################## 이미지 등록  #########################
		if(!preg_match("/none/i",$_FILES['image1']['tmp_name']) && $_FILES['image1']['tmp_name']) {									
			if(!is_dir(UPLOAD_FOLDER.'/'.$uid)) mkdir(UPLOAD_FOLDER.'/'.$uid, 0707);
			$image1 = upFile($_FILES['image1']['tmp_name'], $_FILES['image1']['name'], UPLOAD_FOLDER.'/'.$uid, 1, "image1", 1);

			$sql = "UPDATE {$table_name} SET image1 = '{$image1}' WHERE uid = '{$uid}'";
			$mysql->query($sql);
		}		
		######################## 이미지 등록  #########################

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
			SetCookie("temp_upload", "", -999, "/");
			######################## 상품상세 이미지  #########################
		}

		alertMsg("모음전이 등록 되었습니다.<br />모음전 상품관리로 이동 합니다.", "exhibition_goods.php?exhibition={$uid}");		

    break;	

	case "modify2" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid || !$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 모음전이 존재하지 않거나 삭제 되었습니다.");
		
		######################## 모음전 수정  #########################
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}

		if(!preg_match("/none/i",$_FILES["image1"]['tmp_name']) && $_FILES["image1"]['tmp_name']) {
			$image1 = upFile($_FILES["image1"]['tmp_name'],$_FILES["image1"]['name'], UPLOAD_FOLDER.'/'.$uid, 1, "image1", 1);
			$sql .= ", image1 = '{$image1}'";
		}			
		else {
			$image1_del = checkPostVar('image1_del');
			if($image1_del == '1') {
				@unlink(UPLOAD_FOLDER.'/'.$uid.'/'.$row['image1']);
				$sql .= ", image1 = ''";
			}
			else $image1 = 	$row['image1'];
		}

		$sql .= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);
		######################## 모음전 수정  #########################

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
	
		######################## 분류 변경 체크 (없어진 분류상품 해제 처리)  #########################
		if($_POST['cate_info']) {
			$cate_info = explode("|*|", $_POST['cate_info']);
			$cate_arr = array();
			for($i = 1, $cnt = count($cate_info); $i < $cnt; $i++){
				$cate_info2 = explode("|", $cate_info[$i]);
				$cate_num	= specialStrReplace($cate_info2[0]);
				$cate_arr[] = $cate_num;
			}
			
			$sql = "SELECT * FROM mallRN_exhibition_goods WHERE euid = '{$uid}'";
			$mysql->query($sql);

			while($row = $mysql->fetch_array()) {
				if($row['ecate']) {
					if(!in_array($row['ecate'], $cate_arr)) {					
						exhibitionGoodsDel($row['guid'], $uid, '');					
					}
				}
			}
		}
		######################## 분류 변경 체크  (없어진 분류상품 해제 처리) #########################

		alertMsg("모음전이 수정 되었습니다.", $link_page);			
		
	break;

	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);

			$sql = "SELECT * FROM mallRN_exhibition_goods WHERE euid = '{$uid}'";
			$mysql->query($sql);

			while($row = $mysql->fetch_array()) {
				exhibitionGoodsDel($row['guid'], $uid, '');
			}

			deltree(UPLOAD_FOLDER.'/'.$uid);
		}
		alertMsg("{$i}건의 모음전이 삭제 되었습니다!", $link_page);		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
