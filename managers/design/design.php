<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

define('UPLOAD_FOLDER', '../../image/main');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","design.html");
$tpl->scan_area("main");

$main_display		= 'reco, code, best, cate, new';
$main2_display		= 'reco, best, new';
$display_name_arr	= array('reco' => '추천상품', 'code' => '커스텀코드', 'best' => '인기상품', 'cate' => '분류상품', 'new' => '신상품');

$item_array			= array('design_main_display1', 'design_main_display2', 'design_main_display3', 'design_main_category', 'design_main_category_info', 'design_main_custom_code', 'design_main_custom_code_image', 'design_main_custom_code_info', 'design_main_display_order', 'design_main2_display1', 'design_main2_display2', 'design_main2_display3', 'design_main2_display_order', 'design_icon_display', 'design_vendor_link');

$sql				= "SELECT * FROM mallRN_configuration WHERE uid = 1";
$data				= $mysql->one_row($sql);

for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
	${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
}

$design_main_custom_code_info = add_escape_re_string($design_main_custom_code_info);

${"checked_design_main_display1_".$design_main_display1}		= "checked";
${"checked_design_main_display2_".$design_main_display2}		= "checked";
${"checked_design_main_display3_".$design_main_display3}		= "checked";
${"checked_design_main2_display1_".$design_main2_display1}		= "checked";
${"checked_design_main2_display2_".$design_main2_display2}		= "checked";
${"checked_design_main2_display3_".$design_main2_display3}		= "checked";
${"checked_design_main_category_".$design_main_category}		= "checked";
${"checked_design_main_custom_code_".$design_main_custom_code}	= "checked";
${"checked_design_vendor_link_".$design_vendor_link}			= "checked";

if($design_icon_display) {
	$design_icon_display = explode("|", $design_icon_display);
	${"checked_design_icon1_".$design_icon_display[0]} = "checked";
	${"checked_design_icon2_".$design_icon_display[1]} = "checked";
	${"checked_design_icon3_".$design_icon_display[2]} = "checked";
}

######################## 분류 정보 #############################
$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
$mysql->query($sql);

$goods_cate_select_option = array();
while($row=$mysql->fetch_array()){    
	$cate		= substr(specialStrReplace($row['cate']), 0, 3);
	$cate_name	= specialStrReplace($row['cate_name']);
	$goods_cate_select_option[] = "['{$cate_name}', '{$cate}']";	
}
if(count($goods_cate_select_option) > 0) $goods_cate_select_option = join(", ", $goods_cate_select_option);
else $goods_cate_select_option = "";
unset($cate, $cate_name);
######################## 분류 정보 #############################

if($design_main_category_info && $design_main_category_info != '||') {
	$design_main_category_info = explode("|*|",$design_main_category_info);
	foreach($design_main_category_info as $k => $v) {
		$design_main_category_info2 = explode("|", $v);
		$goods_cate					= $design_main_category_info2[0];
		$goods_display				= $design_main_category_info2[1];
		$goods_used					= $design_main_category_info2[2];
		if($goods_used != '1') $goods_used = "0";

		$tpl->parse("loop_cate_goods");
	}	
}
unset($design_main_category_info, $design_main_category_info2, $goods_cate, $goods_display, $goods_used);;
$tpl->parse("loop_cate_goods");

$image_upload = UPLOAD_FOLDER;
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

$main2_display = $design_main2_display_order ? $design_main2_display_order : $main2_display;
$main2_display = explode(",", $main2_display);
foreach($main2_display as $k => $v) {
	$display_code = trim($v);
	$display_name = $display_name_arr[trim($v)];
	$tpl->parse("loop_main2_display");
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>