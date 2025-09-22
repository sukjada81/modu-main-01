<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

define('UPLOAD_FOLDER', '../../image/banner');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","banner_info.html");
$tpl->scan_area("main");

$mode				= isset($_GET['mode']) ? $_GET['mode'] : 'write';
		
$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'code', 'status', 'target', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_banner WHERE uid = '{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('name', 'code', 'status', 'target', 's_date', 'e_date', 'image1', 'link1');

	for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
		${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
	}
	
	${"checked_status_".$status}	= "checked='checked'";
	${"checked_target_".$target}	= "checked='checked'";

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

	$write_require		= '';

	$TTL = "수정";	

}
else {
	
	$code				= "";
	$checked_status_0	= "checked='checked'";
	$checked_target_0	= "checked='checked'";
	$write_require		= 'required="required"';
	
	$mode	= "write";
	$TTL	= "등록";
	
}

$image_size = array();
$image_help = array();
foreach($BANNER_DEFINE as $k => $v) {
	if(!$code) $code = $k;
	$TITLE			= $v[0];
	$CODE			= $k;
	$image_size[]	= "'{$k}' : '{$v[1]}'";
	$image_help[]	= "'{$k}' : '{$v[2]}'";
	$tpl->parse("loop_code");
}
$image_size = join(", ", $image_size);
$image_help = join(", ", $image_help);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>