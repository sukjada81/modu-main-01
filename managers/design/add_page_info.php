<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","add_page_info.html");
$tpl->scan_area("main");

$mode				= isset($_GET['mode']) ? $_GET['mode'] : 'write';
$cookie_temp_upload = isset($_COOKIE['temp_upload']) ? previlDecode($_COOKIE['temp_upload']) : '';
	
$addstring			= "";
$search_variable	= array('field' ,'keyword', 'status', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify3') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_add_page WHERE uid = '{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('title', 'status', 'detail_image', 'detail_image_only', 'detail_image_type', 'explains');
	
	foreach($item_array as $k => $v) {
		${$v} = stripslashes($data[$v]);
	}

	$explains = add_escape_re_string($explains);
	
	${"checked_status_".$status}	= "checked='checked'";

	define('UPLOAD_FOLDER', '../../image/add_page');
	$temp_upload	= $uid;		

	$image_upload = UPLOAD_FOLDER.'/'.$temp_upload;

	if($detail_image) {
		$detail_image = explode(",", $detail_image);
		for($i=0,$cnt=count($detail_image); $i<$cnt; $i++) {
			if(trim($detail_image[$i])) {
				$image		= $image_upload.'/'.$detail_image[$i];
				$image_name = $detail_image[$i];			
				$tpl->parse("loop_detail_image");
			}
		}	
		unset($image, $image_name, $detail_image);		
	}

	$TTL = "수정";	

}
else {

	$checked_status_0		= "checked='checked'";
	$detail_image_type		= "1";
	$detail_image_only		= "1";	

	define('UPLOAD_FOLDER', '../../image/temp_upload');
	if($cookie_temp_upload) {
		delTree(UPLOAD_FOLDER.'/'.$cookie_temp_upload);
	}
	$temp_upload = date("Ymdhis_").getCode(4);

	$TTL	= "등록";
	
}

$SHOP_WIDTH	= $SKIN_DEFINE['width'];

if(!is_dir(UPLOAD_FOLDER.'/'.$temp_upload)) mkdir(UPLOAD_FOLDER.'/'.$temp_upload,0744);	
$temp_upload = previlEncode($temp_upload);
SetCookie("temp_upload", $temp_upload, 0, "/");

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>