<?php 

include_once("../common/top.php");

define('UPLOAD_FOLDER', '../../image/popup');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_info.html");
$tpl->scan_area("main");

$mode				= isset($_GET['mode']) ? $_GET['mode'] : 'write';
$cookie_temp_upload = isset($_COOKIE['temp_upload']) ? previlDecode($_COOKIE['temp_upload']) : '';
	
$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'type', 'status', 'position', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_popup WHERE uid='{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('name', 'status', 'type', 'period', 's_date', 'e_date', 'position', 'input_position', 'input_size', 'image1', 'link1', 'image_only', 'content');
	
	foreach($item_array as $k => $v) {
		${$v} = stripslashes($data[$v]);
	}

	$content = add_escape_re_string($content);
	
	${"checked_status_".$status}	= "checked='checked'";
	${"checked_type_".$type}		= "checked='checked'";
	${"checked_period_".$period}	= "checked='checked'";


	$s_date = substr($s_date, 0, 10);
	if($s_date == '1000-01-01') $s_date = "";
	$e_date = substr($e_date, 0, 10);
	if($e_date == '1000-01-01') $e_date = "";
	
	$image_upload	= UPLOAD_FOLDER.'/'.$uid;
	for($i=1; $i<2; $i++) {
		if(${"image".$i}) {	
			$image	= $image_upload.'/'.${"image".$i}."?t={$t}";
			$tpl->parse("loop_image");
		}
	}
	
	$in_position		= explode("|", $input_position);
	$position_x			= $in_position[0];
	$position_y			= $in_position[1];

	$in_size		= explode("|", $input_size);
	$size_x				= $in_size[0];
	$size_y				= $in_size[1];

	$write_require		= '';

	$TTL = "수정";	

	define('UPLOAD_FOLDER2', "../../image/popup/{$uid}");
	$temp_upload		= $uid;
	$temp_upload_mode	= "popup|";

}
else {

	$checked_status_0	= "checked='checked'";
	$checked_type_1		= "checked='checked'";
	$checked_period_0	= "checked='checked'";

	$position			= "5";
	$image_only			= "1";	
	$size_x				= "360";
	$size_y				= "480";
	$position_x			= "";
	$position_y			= "";

	$write_require		= 'required="required"';

	$mode	= "write";
	$TTL	= "등록";	

	define('UPLOAD_FOLDER2', '../../image/temp_upload');
	
	if($cookie_temp_upload) {
		delTree(UPLOAD_FOLDER2.'/'.$cookie_temp_upload);
	}
	$temp_upload = date("Ymdhis_").getCode(4);
	$temp_upload_mode	= "temp|";
}

######################## 업로드 폴더 생성 #############################
if(!is_dir(UPLOAD_FOLDER2.'/'.$temp_upload)) mkdir(UPLOAD_FOLDER2.'/'.$temp_upload,0744);	
$temp_upload = previlEncode($temp_upload);
SetCookie("temp_upload", $temp_upload, 0, "/");
######################## 업로드 폴더 생성 #############################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>