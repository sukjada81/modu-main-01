<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');
include_once("../../{$use_skin}/info/skin_define.php");

define('CATEGORY_FOLDER', '../../image/category');

$mysql->msgType(2);

$my_array	= array();
$cate		= checkPostVar('cate');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$cate) {
	echo json_encode(array('error' => '정보가 제대로 넘어오지 못했습니다.'));
	exit;
}

$sql	= "SELECT * FROM mallRN_cate WHERE cate = '{$cate}'";
$data	= $mysql->one_row($sql);

if(!$data) {
	echo  json_encode(array('error' => '해당분류가 삭제되었거나 존재하지 않습니다.'));
	exit;
}

for($i = 1; $i < 4; $i ++) {
	if($data['image'.$i]) {
		$imgSize = @GetImageSize(CATEGORY_FOLDER.'/'.$data['image'.$i]);
		${"imgSize".$i} = $imgSize[0];
	}
	else ${"imgSize".$i} = 0;
}

if($data['cate_dep'] > 1) {
	$sql = "SELECT used FROM mallRN_cate WHERE cate = '{$data['cate_parent']}'";
	$parent_used = $mysql->get_one($sql);
}
else $parent_used = 1;

$CATE_IMAGE1_HELP = isset($SKIN_DEFINE['cate'.$data['cate_dep'].'_image1']) ? $SKIN_DEFINE['cate'.$data['cate_dep'].'_image1'] : "사용안함";
$CATE_IMAGE2_HELP = isset($SKIN_DEFINE['cate'.$data['cate_dep'].'_image2']) ? $SKIN_DEFINE['cate'.$data['cate_dep'].'_image2'] : "사용안함";
$CATE_IMAGE3_HELP = isset($SKIN_DEFINE['cate'.$data['cate_dep'].'_image3']) ? $SKIN_DEFINE['cate'.$data['cate_dep'].'_image3'] : "사용안함";

$my_array[] = ["name" => $data['cate_name'], "used" => $data['used'], "access_type" => $data['access_type'], "access_level" => $data['access_level'], "image1" => $data['image1'], "image2" => $data['image2'], "image3" => $data['image3'], "img_size1" => $imgSize1, "img_size2" => $imgSize2, "img_size3" => $imgSize3, "parent_used" => $parent_used, "cate_image1_help" => $CATE_IMAGE1_HELP, "cate_image2_help" => $CATE_IMAGE2_HELP, "cate_image3_help" => $CATE_IMAGE3_HELP];
	
echo json_encode($my_array);

?>