<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

define('ICON_FOLDER', '../../image/icon');

$mysql->msgType(2);

$my_array	= array();
$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : '';
$icon_order = str_replace(",", "|", checkPostVar('icon_order'));
$name		= checkGetVar('name');

if(isset($_GET['files'])) {  

	foreach($_FILES as $file) {

		if($name) {
			$tmps = explode(".", $name);
			$idx = str_replace("icons_","",$tmps[0]);			
		}
		else {
			if(is_dir(ICON_FOLDER)) { 	
				$handle	= @opendir(ICON_FOLDER);
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
			else json_error_msg('파일 업로드가 실패 되었습니다.');
		}

		if($up_file =  upFile($file['tmp_name'], $file['name'], ICON_FOLDER, 1, 'icons_'.$idx, 2)) {
			$my_array[] = ["img" => ICON_FOLDER."/{$up_file}", "img_name"=>$up_file];
			$idx++;

			if(!$name) {
				if($icon_order) $icon_order .= "|{$up_file}";
				else $icon_order = $up_file;

				$sql = "UPDATE mallRN_configuration SET goods_icon_info = '{$icon_order}' WHERE uid = 1";
				$mysql->query($sql);
			}
		}
		else json_error_msg('파일 업로드가 실패 되었습니다.');
	}
}
else if($mode=='delete') {		
	if($name) {
		@unlink(ICON_FOLDER."/{$name}");
		$my_array[] = ["label"=>"Success"];

		$icon_order = explode('|', $icon_order);
		for($i=0, $cnt=count($icon_order); $i<=$cnt; $i++){		
			if($icon_order[$i]==$name) {
				array_splice($icon_order,$i,1);							
				break;		
			}	
		}
		$icon_order = implode('|', $icon_order);

		$sql = "UPDATE mallRN_configuration SET goods_icon_info = '{$icon_order}' WHERE uid = 1";
		$mysql->query($sql);
	}
	else json_error_msg('파일 삭제가 실패 되었습니다.');
}
	
echo json_encode($my_array);

?>