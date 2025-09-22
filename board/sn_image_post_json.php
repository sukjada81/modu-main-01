<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('../php/init.php');

$mysql->msgType(2);

$my_array	= array();
$type		= isset($_POST['type']) ? previlDecode(urldecode($_POST['type'])) : '';
$b_id		= checkPostVar('b_id');	

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$type || !$b_id) json_error_msg('파일 업로드가 실패 되었습니다.');

$sql		= "SELECT upload_size FROM mallRN_board_manager WHERE id = '{$b_id}'";
$board_info = $mysql->one_row($sql);
if(!$board_info) json_error_msg('파일 업로드가 실패 되었습니다.');

$sql = "SELECT count(*) FROM mallRN_board_{$b_id} WHERE uid = '{$type}'";
if($mysql->get_one($sql) == 0) {
	define('UPLOAD_FOLDER', '../image/temp_upload');
}
else {
	define('UPLOAD_FOLDER', "../board/data/{$b_id}");
}

$upload_dir = UPLOAD_FOLDER."/".$type;

if(isset($_GET['files'])) { 

	foreach($_FILES as $file) {
	
		if(is_dir($upload_dir)) { 	
			$handle	= @opendir($upload_dir);
			$img = array();
			while ($file2 = @readdir($handle)) {
				if($file2 != '.' && $file2 != '..' && preg_match("/image_/i",$file2)) {
					$img[] = $file2;
				}		
			}
			@closedir($handle);		
			$cnt = count($img);
			
			if($cnt > 19) json_error_msg('이미지는 최대 20개 까지만 등록 가능 합니다.');

			if($cnt > 0) {
				sort($img);
				$tmps = explode(".",$img[$cnt-1]);
				$idx = sprintf('%03d', substr($tmps[0], -3) + 1);
			}
			else $idx = '001';
		}
		else json_error_msg($upload_dir.'파일 업로드가 실패 되었습니다.');	

		if($up_file =  upFile($file['tmp_name'], $file['name'], $upload_dir, 1, "image_".$idx, 2, $board_info['upload_size'])) {
			$upload_dir2	= str_replace("../", '/'.CONF_ROOT, $upload_dir);	
			$my_array[] = ["img"=>"{$upload_dir2}/{$up_file}","img_name"=>$up_file];
			$idx++;			
		}
		else json_error_msg('파일 업로드가 실패 되었습니다.');
	}
}
	
echo json_encode($my_array);

?>