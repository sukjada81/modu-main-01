<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('__BASIC__',		'1');
define('__VENDOR_ABLE__', '1');

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array		= array();
$mode			= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : '';
$temp_upload	= checkPostVar('temp_upload');
$type			= checkPostVar('type');
$mode2			= isset($_POST['mode2']) ? add_escape_re_string($_POST['mode2']) : '';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if($mode2 == 'modify') define('UPLOAD_FOLDER', '../../image/goods/upload');
else if($mode2 == 'modify2') define('UPLOAD_FOLDER', '../../image/exhibition');
else if($mode2 == 'modify3') define('UPLOAD_FOLDER', '../../image/add_page');
else define('UPLOAD_FOLDER', '../../image/temp_upload');

if(!$temp_upload) json_error_msg('파일 업로드가 실패 되었습니다.');
if($mode2 == 'common') $upload_dir = previlDecode($temp_upload);
else $upload_dir = UPLOAD_FOLDER."/".previlDecode($temp_upload);

if(isset($_GET['files'])) {  
	
	if($type!='other') $type = "detail";

	foreach($_FILES as $file) {
	
		if(is_dir($upload_dir)) { 	
			$handle	= @opendir($upload_dir);
			$img = array();
			while ($file2 = @readdir($handle)) {
				if($file2 != '.' && $file2 != '..' && preg_match("/{$type}_image/i",$file2)) {
					$img[] = $file2;
				}		
			}
			@closedir($handle);		

			$cnt = count($img);
			
			if($cnt>0) {
				sort($img);
				$tmps = explode(".",$img[$cnt-1]);
				$idx = sprintf('%03d', substr($tmps[0], -3) + 1);
			}
			else $idx = '001';
		}
		else json_error_msg('파일 업로드가 실패 되었습니다.');	

		if($up_file =  upFile($file['tmp_name'], $file['name'], $upload_dir, 1, "{$type}_image_".$idx, 2)) {
			$my_array[] = ["img"=>"{$upload_dir}/{$up_file}?t={$t}","img_name"=>$up_file];
			$idx++;			
		}
		else json_error_msg('파일 업로드가 실패 되었습니다.');
	}
}

if($mode=='delete') {
	$name = isset($_GET['name']) ? $_GET['name'] : '';
	if($_GET['name']) {
		unlink("{$upload_dir}/{$name}");
		$my_array[] = ["label"=>"Success"];
	}
	else json_error_msg('파일 삭제가 실패 되었습니다.');
}

echo json_encode($my_array);

?>