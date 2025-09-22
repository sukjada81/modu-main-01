<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","exhibition_info.html");
$tpl->scan_area("main");

$mode				= isset($_GET['mode']) ? $_GET['mode'] : 'write';
$cookie_temp_upload = isset($_COOKIE['temp_upload']) ? previlDecode($_COOKIE['temp_upload']) : '';
$cate_max_num		= 100;
$oimage_size		= $SKIN_DEFINE['exhibition_image'];
	
$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'discount_yn', 'status', 'cate_info', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify2') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_exhibition WHERE uid='{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('name', 'discount_yn', 'discount', 's_date', 'e_date', 'cate_info', 'image1', 'detail_image', 'detail_image_only', 'detail_image_type', 'explains');
	
	foreach($item_array as $k => $v) {
		${$v} = stripslashes($data[$v]);
	}

	$explains = add_escape_re_string($explains);
	
	${"checked_discount_yn_".$discount_yn}	= "checked='checked'";

	$s_date = substr($s_date, 0, 10);
	$e_date = substr($e_date, 0, 10);

	$cate_info = explode("|*|", $cate_info);
	$cate_max_num = $cate_info[0];
	if($cate_max_num > 100) {
		for($i = 1, $cnt = count($cate_info); $i < $cnt; $i ++) {
			$cate_info2 = explode("|", $cate_info[$i]);

			$cate_num = $cate_info2[0];
			$cate_name = $cate_info2[1];
			
			$tpl->parse("loop_cate");
		}
	}
	unset($cate_info, $cate_info2, $cate_name, $cate_num);

	define('UPLOAD_FOLDER', '../../image/exhibition');
	$temp_upload	= $uid;		

	$image_upload = UPLOAD_FOLDER.'/'.$temp_upload;

	for($i=1; $i<2; $i++) {
		if(${"image".$i}) {	
			$image	= $image_upload.'/'.${"image".$i}."?t={$t}";
			$tpl->parse("loop_image");
		}
	}

	if($detail_image) {
		$detail_image = explode(",", $detail_image);
		for($i=0,$cnt=count($detail_image); $i<$cnt; $i++) {
			if(!$detail_image[$i]) continue;
			$image		= $image_upload.'/'.$detail_image[$i];
			$image_name = $detail_image[$i];			
			$tpl->parse("loop_detail_image");
		}	
		unset($image, $image_name, $detail_image);		
	}

	$TTL = "수정";	

}
else {

	$checked_discount_yn_Y = "checked='checked'";
	$discount				= 0;
	$detail_image_type		= "1";
	$detail_image_only		= "1";	

	define('UPLOAD_FOLDER', '../../image/temp_upload');
	if($cookie_temp_upload) {
		delTree(UPLOAD_FOLDER.'/'.$cookie_temp_upload);
	}
	$temp_upload = date("Ymdhis_").getCode(4);

	$TTL	= "등록";
	
}

if(!is_dir(UPLOAD_FOLDER.'/'.$temp_upload)) mkdir(UPLOAD_FOLDER.'/'.$temp_upload, 0707);	
$temp_upload = previlEncode($temp_upload);
SetCookie("temp_upload", $temp_upload, 0, "/");

$cate_num = $cate_name = "";
$tpl->parse("loop_cate");

$SHOP_WIDTH	= $SKIN_DEFINE['width'];

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>