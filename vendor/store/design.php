<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

define('UPLOAD_FOLDER', '../../image/store');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","design.html");
$tpl->scan_area("main");

$main_display		= 'reco, code, best, new';
$display_name_arr	= array('reco' => '추천상품', 'code' => '커스텀코드', 'best' => '인기상품', 'new' => '신상품');

$item_array			= array('design_main_display1', 'design_main_display2', 'design_main_display3', 'design_main_custom_code', 'design_main_custom_code_image', 'design_main_custom_code_info', 'design_main_display_order');

$sql				= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$v_my_id}'";
$data				= $mysql->one_row($sql);

for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
	${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
}

$design_main_custom_code_info = add_escape_re_string($design_main_custom_code_info);

${"checked_design_main_display1_".$design_main_display1} = "checked";
${"checked_design_main_display2_".$design_main_display2} = "checked";
${"checked_design_main_display3_".$design_main_display3} = "checked";
${"checked_design_main_custom_code_".$design_main_custom_code} = "checked";

$image_upload = UPLOAD_FOLDER."/".$v_my_id;
if(!is_dir($image_upload)) mkdir($image_upload,0707);	

if($design_main_custom_code_image) {
	$detail_image = explode(",", $design_main_custom_code_image);
	foreach($detail_image as $k => $v) {
		$image		= $image_upload.'/'.$v;
		$image_name = $v;			
		$tpl->parse("loop_detail_image");
	}	
	unset($image, $image_name, $detail_image);		
}
$temp_upload = previlEncode($image_upload);

$main_display = $design_main_display_order ? $design_main_display_order : $main_display;
$main_display = explode(",", $main_display);
foreach($main_display as $k => $v) {
	$display_code = trim($v);
	$display_name = $display_name_arr[trim($v)];
	$tpl->parse("loop_main_display");
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>