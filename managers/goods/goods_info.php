<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

define('IMAGE_FOLDER', '../../image/goods/img');
define('SN_INFO_FOLDER', '../../image/sn_upload/information_use/goods');
define('ICON_FOLDER', '../../image/icon');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_info.html");
$tpl->scan_area("main");

$cookie_temp_upload = isset($_COOKIE['temp_upload']) ? previlDecode($_COOKIE['temp_upload']) : '';

######################## 분류 생성 ##############################
$tmps1	= "CATEname = [";
$tmps2	= "CATEnum	= [";
$cnts	= 0;
$sql = "SELECT cate, cate_name, cate_sub FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
$mysql->query($sql);

while($row=$mysql->fetch_array()){    
	$row['cate_name'] = addslashes($row['cate_name']);
	if($row['cate_sub']==1) {
	    if($cnts==1) { 
			$tmps1.= ",['".$row['cate_name']."→'";		
			$tmps2.= ",['".$row['cate']."'";		
		}
		else { 
			$tmps1.= "['".$row['cate_name']."→'";		
			$tmps2.= "['".$row['cate']."'";		
        }		
    } 
	else {
		if($cnts==1) {
			$tmps1.= ",['".$row['cate_name']."'";		
			$tmps2.= ",['".$row['cate']."'";		
		} 
		else {
			$tmps1.= "['".$row['cate_name']."'";		
			$tmps2.= "['".$row['cate']."'";		
		}	
	}
	$tmps1.= "]";
	$tmps2.= "]";	
	$cnts = 1;	
}

$tmps1.= "];";
$tmps2.= "];";
######################## 분류 생성 ##############################

######################## 변수 및 파라미터 정의 ##############################
$mode		= isset($_GET['mode']) ? $_GET['mode'] : 'write';
$price		= $orig_price = $consumer_price = $commission_won = $commission_type = 0;
$commission = "0.00";

$addstring	= "";
$search_variable	= array('field','keyword','cate','date_type','s_date','e_date','field2','keyword2','field3','keyword3','field4','keyword4','display_use','sell_use','option_use','mileage_type','delivery_type','engine_use','order_priority','qty_type','vendor','limit_qty','cate_hide','soldout','option_soldout','commission_type','information_use','back');

foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if(strlen($value)>0) $addstring .= "&{$v}={$value}";
}
######################## 변수 및 파라미터 정의 ##############################

if($mode=='modify') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.","back");

	$sql = "SELECT * FROM mallRN_goods_cate WHERE guid='{$uid}' ORDER BY cate_rep DESC, cate ASC";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		$cate_no	= stripslashes($row['cate']);
		$cate_name	= addslashes(getCateAllName($cate_no));
		$cate_rep	= stripslashes($row['cate_rep']);
	
		$tpl->parse("loop_cate");
	}

	$sql = "SELECT * FROM mallRN_goods WHERE uid='{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('vendor','name','name_code_able','price','orig_price','consumer_price','price_ment','commission_type','commission','image1','image2','image3','other_image','detail_image','detail_image_only','detail_image_type','explains','option_use','option_info','qty_type','qty','limit_qty','require_info','display_use','sale_use','order_priority','main1_display1','main1_display2','main1_display3','main2_display1','main2_display2','main2_display3','icon','goods_code','detail','brand','make','origin','model','making_info','mileage_type','mileage_common','mileage_level','delivery_type','delivery_type_qty','delivery_price','keyword','related_goods_type','related_goods','information_use','delivery_info','refund_info','exchange_info','as_info','engine_use', 'moddate','delivery_im_areas1_used','delivery_im_areas1_price','delivery_im_areas2_used','delivery_im_areas2_price');

	foreach($item_array as $k => $v) {
		${$v} = stripslashes($data[$v]);
	}
	
	$explains = add_escape_re_string($explains);

	${"checked_option_use_".$option_use}			= "checked='checked'";
	${"checked_display_use_".$display_use}			= "checked='checked'";
	${"checked_sale_use_".$sale_use}				= "checked='checked'";
	${"checked_mileage_type_".$mileage_type}		= "checked='checked'";
	${"checked_delivery_type_".$delivery_type}		= "checked='checked'";
	${"checked_engine_use_".$engine_use}			= "checked='checked'";
	${"checked_main1_display1_".$main1_display1}	= "checked='checked'";
	${"checked_main1_display2_".$main1_display2}	= "checked='checked'";
	${"checked_main1_display3_".$main1_display3}	= "checked='checked'";
	${"checked_main2_display1_".$main2_display1}	= "checked='checked'";
	${"checked_main2_display2_".$main2_display2}	= "checked='checked'";
	${"checked_main2_display3_".$main2_display3}	= "checked='checked'";

	$icon_arr = isset($icon) ? explode("|",$icon) : '';

	for($i=1; $i<4; $i++) {
		if(${"image".$i}) {
			$image = IMAGE_FOLDER.${"image".$i};
			$size  = @GetImageSize($image);
			${"image_size".$i} = "{$size[0]}px X {$size[1]}px";
			$image .= "?t=".$moddate;
			$tpl->parse("loop_image");
		}
	}
	unset($size, $image);

	define('UPLOAD_FOLDER', '../../image/goods/upload');
	$img_block		= floor($uid/10000);
	$temp_upload	= $img_block.'/'.$uid;		

	$other_upload = UPLOAD_FOLDER.'/'.$temp_upload;
	$other_image2	= array();

	if($other_image) {
		$other_image = explode(",",$other_image);
		for($i=0,$cnt=count($other_image); $i<$cnt; $i++) {
			if(!$other_image[$i]) continue;
			$image		= $other_upload.'/'.$other_image[$i]."?t=".$moddate;
			$image_name = $other_image[$i];
			$other_image2[] = $image_name;
			$tpl->parse("loop_other_image");
		}	
		unset($image, $image_name, $other_image);		
	}

	if($detail_image) {
		$detail_image = explode(",",$detail_image);
		for($i=0,$cnt=count($detail_image); $i<$cnt; $i++) {
			if(!$detail_image[$i]) continue;
			$image		= $other_upload.'/'.$detail_image[$i]."?t=".$moddate;
			$image_name = $detail_image[$i];
			$other_image2[] = $image_name;
			$tpl->parse("loop_detail_image");
		}	
		unset($image, $image_name, $detail_image);		
	}

	if(count($other_image2)>0) {
		$handle	= @opendir($other_upload);	
		while ($file = @readdir($handle)) {
			if($file != '.' && $file != '..') {
				if(!in_array($file, $other_image2)) unlink("{$other_upload}/{$file}");
			}
		}
		@closedir($handle);			
	}
	unset($other_upload, $other_image2);
	
	if($option_info) {
		$option_info = explode("|*|", $option_info);
		foreach($option_info as $k => $v) {
			$option_info2	= explode("|",$v);
			if(!$option_info2[0]) continue;
			$option_name	= str_replace(array("&lt;s&#67;ript&gt;", "&lt;/s&#67;ript&gt;"), "", specialStrReplace2($option_info2[0]));
			$option_value	= str_replace(array("&lt;s&#67;ript&gt;", "&lt;/s&#67;ript&gt;"), "", specialStrReplace2($option_info2[1]));
			$tpl->parse("loop_option_info");
		}	
		unset($option_name, $option_value, $option_info, $option_info2);

		$sql = "SELECT * FROM mallRN_goods_option WHERE guid='{$uid}' ORDER BY sequence ASC";
		$mysql->query($sql);
		
		while($row = $mysql->fetch_array()){
			$option_value		= explode("|",stripslashes($row['value']));
			$i = 1;
			foreach($option_value as $k => $v) {
				$option_value2 = specialStrReplace($v);
				$tpl->parse("loop_option_table_value");					
				$i ++;
			}

			$option_price		= number_format(stripslashes($row['price']));
			$option_qty_type	= stripslashes($row['qty_type']);
			$option_qty			= number_format(stripslashes($row['qty']));
			$option_used		= stripslashes($row['used']);
			$option_code		= specialStrReplace($row['code']);
			$option_uid			= stripslashes($row['uid']);		
			$tpl->parse("loop_option_table");			
		}

		$tpl->parse("is_option_table");
		unset($option_value, $option_price, $option_qty_type, $option_qty, $option_used, $option_code);
	}

	if($require_info) {
		$require_info = explode("|*|", $require_info);
		foreach($require_info as $k => $v) {
			$require_info2	= explode("|", $v);
			$require_name	= specialStrReplace($require_info2[0]);
			$require_value	= specialStrReplace($require_info2[1]);
			$require_help	= isset($require_info2[2]) ? $require_info2[2] : '';
			$tpl->parse("loop_require_info");
		}	
		unset($require_name, $require_value, $require_help, $require_info, $require_info2);
	}

	if($making_info) {	
		$making_info = explode("|*|", $making_info);
		foreach($making_info as $k => $v) {
			$making_info2	= explode("|", $v);
			if($making_info2[0]) {
				$making_name	= specialStrReplace($making_info2[0]);
				$making_value	= specialStrReplace($making_info2[1]);
				$tpl->parse("loop_making_info");
			}
		}	
		unset($making_name, $making_value, $making_info, $making_info2);
	}

	if($mileage_level) {
		$mileage_level		= explode("|*|", $mileage_level);
		$mileage_level_arr	= array();
		foreach($mileage_level as $k => $v) {
			$mileage_level2							= explode("|", $v);
			$mileage_level_arr[$mileage_level2[0]]	= $mileage_level2[1];
		}	
		unset($mileage_level, $mileage_level2);
	}

	if($related_goods) $tpl->parse("is_related_goods");

	$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
	$SHOP_URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;

	$tpl->parse("is_goods_link");
	
	${"checked_delivery_im_areas1_used_".$delivery_im_areas1_used} = "checked='checked'";
	${"checked_delivery_im_areas2_used_".$delivery_im_areas2_used} = "checked='checked'";
		
	$TTL = "수정";
}
else {	
	$order_priority		= "5";
	$detail_image_type	= "1";
	$detail_image_only	= "1";
	$qty_type			= "1";
	$qty				= "0";
	$icon_arr			= "";
	$related_goods_type	= "0";
	$information_use	= "1";
	$limit_qty			= "0";
	$delivery_type_qty	= "1";

	$checked_option_use_0		= "checked='checked'";
	$checked_display_use_1		= "checked='checked'";
	$checked_sale_use_1			= "checked='checked'";
	$checked_mileage_type_1		= "checked='checked'";
	$checked_delivery_type_1	= "checked='checked'";
	$checked_engine_use_0		= "checked='checked'";
	$write_require				= "required='required'";
	$mileage_level_arr			= array();
	
	$delivery_im_areas1_price	= "0";
	$delivery_im_areas2_price	= "0";
	$checked_delivery_im_areas1_used_1 = "";
	$checked_delivery_im_areas1_used_2 = "";

	$image_size1	= $SKIN_DEFINE['image1'];
	$image_size2	= $SKIN_DEFINE['image2'];
	$image_size3	= $SKIN_DEFINE['image3'];
	
	define('UPLOAD_FOLDER', '../../image/temp_upload');
	if($cookie_temp_upload) {
		delTree(UPLOAD_FOLDER.'/'.$cookie_temp_upload);
		delTree(SN_INFO_FOLDER.'/'.$cookie_temp_upload);
	}
	$temp_upload = date("Ymdhis_").getCode(4);

	$TTL = "등록";	
}

######################## 업로드 폴더 생성 #############################
if(!is_dir(UPLOAD_FOLDER.'/'.$temp_upload)) mkdir(UPLOAD_FOLDER.'/'.$temp_upload,0707);	
if(!is_dir(SN_INFO_FOLDER.'/'.$temp_upload)) {
	mkdir(SN_INFO_FOLDER.'/'.$temp_upload,0707);	
	mkdir(SN_INFO_FOLDER.'/'.$temp_upload.'/1',0707);	
	mkdir(SN_INFO_FOLDER.'/'.$temp_upload.'/2',0707);	
	mkdir(SN_INFO_FOLDER.'/'.$temp_upload.'/3',0707);	
	mkdir(SN_INFO_FOLDER.'/'.$temp_upload.'/4',0707);	
}
$temp_upload = previlEncode($temp_upload);
SetCookie("temp_upload", $temp_upload, 0, "/");
######################## 업로드 폴더 생성 #############################

######################## 판매사 정보 #############################
$sql = "SELECT * FROM mallRN_vendor WHERE auth='Y' && sell != 'N' ORDER BY comp_name ASC";
$mysql->query($sql);

$vendor_commission = array();
while($row = $mysql->fetch_array()){
	$vendor_id				= specialStrReplace($row['id']);
	$vendor_name			= specialStrReplace($row['comp_name']);
	$tpl->parse("loop_vendor");
}
unset($vendor_id, $vendor_name);
######################## 판매사 정보 #############################

$sql = "SELECT goods_price_limit1, goods_price_limit2, goods_option_info, goods_icon_info, goods_brand_info, goods_make_info, goods_origin_info, goods_require_info, member_mileage_order, delivery_type, delivery_d_price, delivery_p_type, delivery_p_price1, delivery_p_price2, delivery_im_areas1_used, delivery_im_areas1_price, delivery_im_areas2_used, delivery_im_areas2_price FROM mallRN_configuration WHERE uid=1";
$multi_data = $mysql->one_row($sql);

$goods_price_limit1 = $multi_data['goods_price_limit1'];
$goods_price_limit2 = $multi_data['goods_price_limit2'];

######################## 등록된 옵션 정보 #########################
$option_data = $multi_data['goods_option_info'];
if($option_data) {
	$option_data = explode("|*|", $option_data);
	foreach($option_data as $k => $v) {
		$option_data2	= explode("|", $v);
		if($option_data2[1]!=1) continue;
		$option_name	= specialStrReplace($option_data2[0]);
		$option_info	= specialStrReplace($option_data2[2]);
		$tpl->parse("loop_option_select");
	}
	unset($option_data, $option_data2, $option_name, $option_info);
}
######################## 등록된 옵션 정보 #########################

######################## 등록된 브랜드 정보 #########################
$brand_data = $multi_data['goods_brand_info'];
if($brand_data) {
	$brand_data = explode("|*|", $brand_data);
	foreach($brand_data as $k => $v) {
		$brand_data2	= explode("|", $v);
		if($brand_data2[1]!=1) continue;
		$brand_name		= specialStrReplace($brand_data2[0]);
		$tpl->parse("loop_brand");
	}
	unset($brand_data, $brand_data2, $brand_name);
}
######################## 등록된 브랜드 정보 #########################

######################## 등록된 제조사 정보 #########################
$make_data = $multi_data['goods_make_info'];
if($make_data) {
	$make_data = explode("|*|", $make_data);
	foreach($make_data as $k => $v) {
		$make_data2 = explode("|", $v);
		if($make_data2[1]!=1) continue;
		$make_name	= specialStrReplace($make_data2[0]);
		$tpl->parse("loop_make");
	}
	unset($make_data, $make_data2, $make_name);
}
######################## 등록된 브랜드 정보 #########################

######################## 등록된 원산지 정보 #########################
$origin_data = $multi_data['goods_origin_info'];
if($origin_data) {
	$origin_data = explode("|*|", $origin_data);
	foreach($origin_data as $k => $v) {
		$origin_data2	= explode("|", $v);
		if($origin_data2[1]!=1) continue;
		$origin_name	= specialStrReplace($origin_data2[0]);
		$tpl->parse("loop_origin");
	}
	unset($origin_data, $origin_data2, $origin_name);
}
######################## 등록된 브랜드 정보 #########################

######################## 등록된 아이콘 #########################
$icon_data = $multi_data['goods_icon_info'];

if($icon_data) {
	$icon_data = explode("|", $icon_data);
	foreach($icon_data as $k => $v) {
		$icon			= ICON_FOLDER."/{$v}?t={$t}";
		$icon_name		= $v;
		$checked_icon	= $class_icon = "";
		if($icon_arr) {
			if(in_array($icon_name, $icon_arr)) {
				$checked_icon	= "checked='checked'";
				$class_icon		= "selected";
			}
		}
		$tpl->parse("loop_icon");
	}
	unset($icon_data, $icon_data2, $icon, $icon_name, $checked_icon, $icon_arr);
}
######################## 등록된 아이콘 #########################

######################## 등록된 상품필수정보 #########################
$require_data = $multi_data['goods_require_info'];

if($require_data) {
	$require_data = explode("|*|", $require_data);
	foreach($require_data as $k => $v) {
		$require_data2		= explode("|",$v);
		if($require_data2[1]==0) continue;
		$require_title		= specialStrReplace($require_data2[0]);
		$require_value		= str_replace("\r\n","[BR]",(specialStrReplace($require_data2[2])));
		$tpl->parse("loop_require");
	}
	unset($require_data, $require_data2, $require_title, $require_value);
}
######################## 등록된 상품필수정보 #########################

######################## 마일리지 설정 #############################
$sql			= "SELECT member_mileage_order FROM mallRN_configuration WHERE uid = 2";
$conf_mileage	= $mysql->get_one($sql);

$sql = "SELECT * FROM mallRN_member_level WHERE level < 100 ORDER BY level ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {
	$level			= $row['level'];
	$level_name		= stripslashes($row['name']);
	$level_mileage	= isset($mileage_level_arr[$level]) ? $mileage_level_arr[$level] : '';
	$tpl->parse("loop_level");	
}
unset($level, $level_name, $level_mileage, $mileage_level_arr);
######################## 마일리지 설정 #############################

######################## 배송비 설정 #############################
$delivery_type_disable1 = "delivery_type_disable";
$delivery_type_disable2 = "disabled";

if($multi_data['delivery_type']=='F') {
	$conf_delivery = "무료배송";
}
else if($multi_data['delivery_type']=='D') {
	$conf_delivery = "착불 - ".number_format($multi_data['delivery_d_price']);
}
else {
	$delivery_p_type_arr = array("order"=>"주문금액","pay"=>"결제금액");
	$conf_delivery = "조건부 - ".$delivery_p_type_arr[$multi_data['delivery_p_type']]." ".number_format($multi_data['delivery_p_price1'])."원 미만 ".number_format($multi_data['delivery_p_price2'])." 원";
	$delivery_type_disable1 = $delivery_type_disable2 = "";
}

if($multi_data['delivery_im_areas1_used']=='1') {
	$conf_delivery .= " / 제주도 - ".number_format($multi_data['delivery_im_areas1_price'])."원";
}
if($multi_data['delivery_im_areas2_used']=='1') {
	$conf_delivery .= " / 도서산간 - ".number_format($multi_data['delivery_im_areas2_price'])."원";
}

unset($multi_data, $delivery_p_type_arr);
######################## 배송비 설정 #############################


$option_name = $option_info = "";
$tpl->parse("loop_option");

$making_name = $making_info = "";
$tpl->parse("loop_making");

$SHOP_WIDTH	= $SKIN_DEFINE['view_width'];

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>