<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/sn_upload');

$mysql->msgType(2);

$my_array	= array();
$type		= isset($_POST['type']) ? urldecode($_POST['type']) : '';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

$type_tmp   = explode("|",$type);
$ck_type	= 0;

if(count($type_tmp) > 1) {	
	$type_tmp[1]	= previlDecode($type_tmp[1]);
	if($type_tmp[0] == 'temp') {
		$ck_type	= 1;
		$type		= $type_tmp[1];	
	}
	else $type		= join("", $type_tmp);
}

if(!$type) json_error_msg('파일 업로드가 실패 되었습니다.');

if($ck_type == 1) $upload_dir = '../../image/temp_upload/'.$type;
else $upload_dir = UPLOAD_FOLDER."/".$type;

if(isset($_GET['files'])) {  

	foreach($_FILES as $file) {
	
		if(is_dir($upload_dir)) { 	
			$handle	= @opendir($upload_dir);
			$img = array();
			while ($file2 = @readdir($handle)) {
				if($file2 != '.' && $file2 != '..') {
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
		else json_error_msg($upload_dir.' 파일 업로드가 실패 되었습니다.');	

		if($up_file =  upFile($file['tmp_name'], $file['name'], $upload_dir, 1, "image_".$idx, 2)) {
			$my_array[] = ["img"=>"{$upload_dir}/{$up_file}","img_name"=>$up_file];
			$idx++;			
		}
		else json_error_msg('파일 업로드가 실패 되었습니다.');
	}
}
	
echo json_encode($my_array);

?>