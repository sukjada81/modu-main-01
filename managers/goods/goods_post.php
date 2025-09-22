<?php

ini_set('memory_limit', -1); // 메모리 제한을 해제해준다. 
header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');
include_once("../../{$use_skin}/info/skin_define.php");

define('IMAGE_FOLDER', '../../image/goods/img');
define('UPLOAD_FOLDER', '../../image/goods/upload');
define('TEMP_UPLOAD_FOLDER', '../../image/temp_upload');
define('SN_INFO_FOLDER', '../../image/sn_upload/information_use/goods');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

function goodsDel($uid) {
	global $mysql;

	$img_block = floor($uid/10000);

	$sql = "SELECT * FROM mallRN_goods WHERE uid = '{$uid}'";
	if(!$data = $mysql->one_row($sql)) logMsg("해당상품은 삭제 되었거나 존재하지 않습니다.");

	for($i=1; $i<4; $i++) {		   				
		if($data["image{$i}"]) delFile(IMAGE_FOLDER.$data["image{$i}"]);
	}
	
	delTree(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid);
	delTree(SN_INFO_FOLDER.'/'.$img_block.'/'.$uid);
		
	$sql = "DELETE FROM mallRN_goods WHERE uid = '{$uid}'";
	$mysql->query($sql);	

	$sql = "DELETE FROM mallRN_goods_option WHERE guid = '{$uid}'";
	$mysql->query($sql);	

	$sql = "DELETE FROM mallRN_goods_cate WHERE guid = '{$uid}'";
	$mysql->query($sql);	

	$sql = "DELETE FROM mallRN_favorite_goods WHERE g_uid = '{$uid}'";
	$mysql->query($sql);
	
	$sql = "DELETE FROM mallRN_goods_recent_view WHERE g_uid = '{$uid}'";
	$mysql->query($sql);

	$sql = "DELETE FROM mallRN_goods_view WHERE g_uid = '{$uid}'";
	$mysql->query($sql);

	$sql = "DELETE FROM mallRN_review WHERE g_uid = '{$uid}'";
	$mysql->query($sql);

	$sql = "DELETE FROM mallRN_inquiry WHERE g_uid = '{$uid}'";
	$mysql->query($sql);

	$sql = "DELETE FROM mallRN_coupon WHERE g_uid = '{$uid}'";
	$mysql->query($sql);

	$sql = "DELETE FROM mallRN_cart WHERE g_uid = '{$uid}'";
	$mysql->query($sql);	
}

function goodsCopy($orig_uid) {
	global $mysql, $item_array;
	
	$sql = "SELECT MAX(uid) FROM mallRN_goods";
	$maxUid = $mysql->get_one($sql);
	$maxUid++;
	
	$signdate		= time();
	$img_block		= floor($maxUid/10000);
	$orig_img_block	= floor($orig_uid/10000);
	$tmp_uid		= $maxUid.getCode(4);

	for($i=1;$i<4;$i++) {
		if(!is_dir(IMAGE_FOLDER.$i.'/'.$img_block)) mkdir(IMAGE_FOLDER.$i.'/'.$img_block,0707);	
	}
	if(!is_dir(UPLOAD_FOLDER.'/'.$img_block)) mkdir(UPLOAD_FOLDER.'/'.$img_block,0707);	
	if(!is_dir(SN_INFO_FOLDER.'/'.$img_block)) mkdir(SN_INFO_FOLDER.'/'.$img_block,0707);	

	$sql = "SELECT * FROM mallRN_goods WHERE uid = '{$orig_uid}'";
	if(!$data = $mysql->one_row($sql)) logMsg("해당상품은 삭제 되었거나 존재하지 않습니다.");

	$sql = "INSERT INTO mallRN_goods SET";
	foreach ($item_array as $k => $v) {
		$data[$v] = addslashes($data[$v]);
		if($k==count($item_array)-1) $sql .= " {$v} = '{$data[$v]}'";
		else $sql .= " {$v} = '{$data[$v]}',";
	}		
	$mysql->query($sql);
	$uid	= $mysql->InsertNo();	
	$re_uid = (100009999 - $uid);

	######################## 판매사 판매상태 체크  #########################
	if($data['vendor']) {
		$sql = "SELECT sell FROM mallRN_vendor WHERE id = '{$data['vendor']}'";
		if($mysql->get_one($sql) != 'A') {
			$sql = "UPDATE mallRN_goods SET vendor_hide = 1 WHERE uid = '{$uid}'";
			$mysql->query($sql);
		}
	}
	######################## 판매사 판매상태 체크  #########################

	######################## 이미지 이름변경  #########################
	for($i=1;$i<4;$i++) {
		${"image".$i} = "{$i}/{$img_block}/{$uid}.".getExtension($data['image'.$i]);
		$save_name = $uid.".".getExtension($data['image'.$i]);
		@copy(IMAGE_FOLDER.$data['image'.$i], IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name);
	}
	
	$sql = "UPDATE mallRN_goods SET image1 = '{$image1}', image2 = '{$image2}', image3 = '{$image3}', re_uid = '{$re_uid}', signdate='{$signdate}' WHERE uid='{$uid}'";
	$mysql->query($sql);
	######################## 이미지 이름변경  #########################	

	######################## 상품상세 이미지변경  #########################
	if(is_dir(UPLOAD_FOLDER.'/'.$orig_img_block.'/'.$orig_uid)) {
		copyTree(UPLOAD_FOLDER.'/'.$orig_img_block.'/'.$orig_uid, UPLOAD_FOLDER.'/'.$img_block.'/'.$uid);	
		
		$explains = str_replace(UPLOAD_FOLDER.'/'.$orig_img_block.'/'.$orig_uid, UPLOAD_FOLDER.'/'.$img_block.'/'.$uid, $data['explains']);
		$sql = "UPDATE mallRN_goods SET explains = '{$explains}' WHERE uid='{$uid}'";
		$mysql->query($sql);
		unset($explains);
	}
	######################## 상품상세 이미지변경  #########################
		
	######################## 이용안내 이미지  #########################	
	if(is_dir(SN_INFO_FOLDER.'/'.$orig_img_block.'/'.$orig_uid)) {
		$sn_dir = SN_INFO_FOLDER.'/'.$orig_img_block.'/'.$orig_uid;
		copyTree($sn_dir, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid);	
		
		$delivery_info	= str_replace($sn_dir, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $data['delivery_info']);
		$refund_info	= str_replace($sn_dir, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $data['refund_info']);
		$exchange_info	= str_replace($sn_dir, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $data['exchange_info']);
		$as_info		= str_replace($sn_dir, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $data['as_info']);
		$sql = "UPDATE mallRN_goods SET delivery_info = '{$delivery_info}', refund_info = '{$refund_info}', exchange_info = '{$exchange_info}', as_info = '{$as_info}' WHERE uid='{$uid}'";
		$mysql->query($sql);
		unset($sn_dir, $delivery_info, $refund_info, $exchange_info, $as_info);
	}	
	######################## 이용안내 이미지  #########################
		
	######################## 상품 분류 정보 #########################
	$sql = "SELECT * FROM mallRN_goods_cate WHERE guid='{$orig_uid}' ORDER BY sequence ASC";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()) {
		$sql = "INSERT INTO mallRN_goods_cate SET 
					guid		= '{$uid}', 
					cate		= '{$row['cate']}',
					cate_rep	= '{$row['cate_rep']}'
				";
		$mysql->query2($sql);			
	}
	######################## 상품 분류 정보 #########################

	######################## 상품 옵션 정보  #########################		
	$sql = "SELECT * FROM mallRN_goods_option WHERE guid='{$orig_uid}' ORDER BY sequence ASC";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()) {	
		$sql = "INSERT INTO mallRN_goods_option SET 
					guid		= '{$uid}', 
					value		= '{$row['value']}',
					price		= '{$row['price']}',
					qty_type	= '{$row['qty_type']}',
					qty			= '{$row['qty']}',
					used		= '{$row['used']}',
					code		= '{$row['code']}',
					sequence	= '{$row['sequence']}'
				";
		$mysql->query2($sql);
	}
	######################## 상품 옵션 정보  #########################	
}

######################## 파라미터 링크 추가  #########################
$addstring			= "";
$search_variable	= array('field','keyword','cate','date_type','s_date','e_date','field2','keyword2','field3','keyword3','field4','keyword4','display_use','sell_use','option_use','mileage_type','delivery_type','engine_use','order_priority','qty_type','vendor','limit_qty','cate_hide','soldout','option_soldout','commission_type','information_use');

foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if(strlen($value)>0) $addstring .= "&{$v}={$value}";
}
######################## 파라미터 링크 추가  #########################

$moddate	= time();
$back		= isset($_GET['back']) ? $_GET['back'] : '';
$link_page	= "goods{$back}_list.php?{$addstring}";

if($mode=='modify') {	
	$img_block	= floor($_POST['uid']/10000);
	$tmp_uid	= $_POST['uid'];
}		
else if($mode=='write') {
	$sql = "SELECT MAX(uid) FROM mallRN_goods";
	$maxUid = $mysql->get_one($sql);
	
	if(!$maxUid) $maxUid = '10000';
	else $maxUid++;

	$img_block	= floor($maxUid/10000);	
	$tmp_uid	= $maxUid.getCode(4);
	
	for($i=1;$i<4;$i++) {
		if(!is_dir(IMAGE_FOLDER.$i.'/'.$img_block)) mkdir(IMAGE_FOLDER.$i.'/'.$img_block,0707);	
	}
	if(!is_dir(UPLOAD_FOLDER.'/'.$img_block)) mkdir(UPLOAD_FOLDER.'/'.$img_block,0707);	
	if(!is_dir(SN_INFO_FOLDER.'/'.$img_block)) mkdir(SN_INFO_FOLDER.'/'.$img_block,0707);	
}

$sql = "SELECT goods_price_limit1, goods_price_limit2 FROM mallRN_configuration WHERE uid=1";
$multi_data = $mysql->one_row($sql);

$goods_price_limit1 = $multi_data['goods_price_limit1'];
$goods_price_limit2 = $multi_data['goods_price_limit2'];
unset($multi_data);

if($mode=='write' || $mode=='modify') {
	
	for($i=1;$i<4;$i++) {
		if(!preg_match("/none/i",$_FILES['image'.$i]['tmp_name']) && $_FILES['image'.$i]['tmp_name']) {									
			$_POST['image'.$i] = $i.'/'.$img_block.'/'.upFile($_FILES['image'.$i]['tmp_name'], $_FILES['image'.$i]['name'], IMAGE_FOLDER.$i.'/'.$img_block, 1, $tmp_uid, 1);
		}
		else $_POST['image'.$i] = '';	
		
		if($i > 1) {
			if(($mode=='write' || ($mode=='modify' && $_POST['del_image'.$i] == '1')) && !$_POST['image'.$i] && $base_image) {
				$img_size = $IMG_DEFINE['image'.$i];
				$save_name = $tmp_uid.".".getExtension($base_image);
				$thumb = createThumbnail(IMAGE_FOLDER.$base_image, $img_size, 'w');
				if($thumb) @rename($thumb, IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name);
				$_POST['image'.$i] = $i.'/'.$img_block.'/'.$save_name;
			}
		}
		else {			
			if($_POST['image'.$i]) $base_image = $_POST['image'.$i];
			else if($mode == 'modify' && checkPostVar('uid')) {
				$sql = "SELECT image1 FROM mallRN_goods WHERE uid = '".checkPostVar('uid')."'";
				$base_image = IMAGE_FOLDER.$mysql->get_one($sql);	
			}
		}
	}
	unset($save_name, $thumb);
	
	$_POST['cate']				= checkPostVar('cate_rep');
	$_POST['name_code_able']	= str_replace(array("<p>","</p>"), '', checkPostVar('name'));
	$_POST['name']				= str_replace("&nbsp;", " ", html2txt($_POST['name_code_able']));
	$_POST['price']				= priceLimit(checkPostVar('price'));
	$_POST['orig_price']		= priceLimit(checkPostVar('orig_price'));
	$_POST['other_image']		= checkPostVar('other_image_order');
	$_POST['detail_image']		= checkPostVar('detail_image_order');
	$_POST['mileage_type']		= checkPostVar('mileage_type');
	$_POST['option_order']		= checkPostVar('option_order');	
	if($_POST['option_order'] == '|') $_POST['option_order'] = '';
	$_POST['delivery_type_qty']	= checkPostVar('delivery_type_qty', 1);
	
	if(isset($_POST['option_order'])) {
		$option_order			= explode(",", $_POST['option_order']);
		$option_order_cnt		= count($option_order);
		$_POST['option_info']	= multiPostVar($option_order, array('option_name' ,'option_info'));
	}
	else $_POST['option_info'] = "";
	
	if(isset($_POST['icon_arr']))	$_POST['icon'] = join('|', $_POST['icon_arr']);
	else							$_POST['icon'] = "";
	
	if(isset($_POST['require_order'])) {
		$require_order			= explode(",",$_POST['require_order']);
		$_POST['require_info']	= multiPostVar($require_order, array('require_name','require_info','require_info_help'));
	}
	else $_POST['require_info'] = "";

	if(isset($_POST['making_order'])) {
		$making_order = explode(",",$_POST['making_order']);
		$_POST['making_info']	= multiPostVar($making_order, array('making_name','making_info'));
	}
	else $_POST['making_info'] = "";
	
	$_POST['mileage_level'] = "";
	if($_POST['mileage_type'] == '3') {
		$sql = "SELECT * FROM mallRN_member_level WHERE level < 100 ORDER BY level ASC";
		$mysql->query($sql);

		$mileage_level_arr = array();
		while($row = $mysql->fetch_array()) {
			$mileage_level_arr[] = $row['level'].'|'.$_POST['mileage_level_'.$row['level']];				
		}

		if(count($mileage_level_arr)>0) $_POST['mileage_level'] = join("|*|",$mileage_level_arr);

		unset($mileage_level_arr);
	}

	$_POST['keyword'] = isset($_POST['keyword']) ? ','.$_POST['keyword'].',' : '';

	if(isset($_POST['related_goods_order'])) {
		$_POST['related_goods']	= $_POST['related_goods_order'];
	}
	else $_POST['related_goods'] = "";
	
	if(isset($_POST['information_use'])) {
		if($_POST['information_use']=='1') $_POST['delivery_info'] = $_POST['refund_info'] = $_POST['exchange_info'] = $_POST['as_info'] = "";
	}
}

$item_array = array('cate', 'vendor', 'name', 'name_code_able', 'price', 'orig_price', 'consumer_price', 'price_ment', 'commission_type', 'commission', 'other_image', 'detail_image', 'detail_image_only', 'detail_image_type', 'explains', 'option_use', 'option_info', 'qty_type', 'qty', 'limit_qty', 'require_info', 'display_use', 'sale_use', 'order_priority', 'main1_display1', 'main1_display2', 'main1_display3', 'main2_display1', 'main2_display2', 'main2_display3', 'icon', 'goods_code', 'detail', 'brand', 'make', 'origin', 'model', 'making_info', 'mileage_type', 'mileage_common', 'mileage_level', 'delivery_type', 'delivery_type_qty', 'delivery_price', 'keyword', 'related_goods_type', 'related_goods', 'information_use', 'delivery_info', 'refund_info', 'exchange_info', 'as_info', 'engine_use','delivery_im_areas1_used','delivery_im_areas1_price','delivery_im_areas2_used','delivery_im_areas2_price');

$item_default = array('commission_type', 'detail_image_only', 'option_use', 'qty_type', 'display_use', 'sale_use', 'main1_display1', 'main1_display2', 'main1_display3', 'main2_display1', 'main2_display2', 'main2_display3', 'engine_use','delivery_im_areas1_used','delivery_im_areas1_price','delivery_im_areas2_used','delivery_im_areas2_price');

$item_able_value = array('detail_image_type' => ['1', '2'], 'order_priority' => ['5', '0', '1', '2', '3', '4', '6', '7', '8', '9'], 'related_goods_type' => ['0', '1', '2', '3', '4'], 'mileage_type' => ['1', '2', '3', '4'], 'delivery_type' => ['1', '2', '3', '4', '5'], 'information_use' => ['1', '2', '3']);

switch($mode) {
	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			goodsDel($item[$i]);
		}
		alertMsg("{$i}개의 상품이 삭제 되었습니다!", $link_page, 1);

	break;

	case "copy" :

		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			goodsCopy($item[$i]);
		}
		alertMsg("{$i}개의 상품이 복사 되었습니다!", $link_page);

	break;

	case "display" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		if($value == 2) $value = 0;
		else			$value = 1;		
		if(!$item || strlen($value) == 0)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$sql = "UPDATE mallRN_goods SET display_use = '{$value}', moddate='{$moddate}' WHERE uid IN (".join(",",$item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}개의 상품의 진열상태가 변경 되었습니다!", $link_page);

	break;

	case "sale" :
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		if($value == 2) $value = 0;
		else			$value = 1;		
		if(!$item || strlen($value) == 0)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
				
		$sql = "UPDATE mallRN_goods SET sale_use = '{$value}', moddate='{$moddate}' WHERE uid IN (".join(",", $item).")";
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());
		alertMsg("{$cnt}개의 상품의 판매상태가 변경 되었습니다!", $link_page);

	break;

	case "auth_ck" :
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
				
		$sql = "UPDATE mallRN_goods SET auth_ck = '{$value}', moddate='{$moddate}' WHERE uid IN (".join(",", $item).")";
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());
		alertMsg("{$cnt}개의 상품의 승인상태가 변경 되었습니다!", "../vendor/vendor_goods_list.php?{$addstring}");

	break;

	case "order_priority" :
		
		$item		= checkPostVar('item');
		$value		= returnNumeric(checkPostVar('value'));
		$cnt		= count($item);
		if(!$item || strlen($value) == 0)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$sql = "UPDATE mallRN_goods SET order_priority = '{$value}', moddate='{$moddate}' WHERE uid IN (".join(",", $item).")";
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());
		alertMsg("{$cnt}개의 상품의 진열 우선순위가 변경 되었습니다!", "goods_modify_list.php?{$addstring}");

	break;

	case "modOne" :
	
		$uid				= checkPostVar('uid');
		$_POST['moddate']	= time();

		if(!$uid) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

		$item_array = array('price','orig_price','commission_type','commission','consumer_price','qty_type','qty','moddate');
		
		$sql = "UPDATE mallRN_goods SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = str_replace(",", "", checkPostVar($v, $item_default));
			if($k<2) $_POST[$v] = priceLimit($_POST[$v]);
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='{$uid}'";
		$mysql->query($sql);

		logMsg("저장 되었습니다.","success");

	break;

	case "modAll" : 
	
		$item		= checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');				

		$item_array = array('price','orig_price','commission_type','commission','consumer_price','qty_type','qty');

		for($i=0, $cnt=count($item); $i<$cnt; $i++) {
			$uid = $item[$i];
			$sql = "UPDATE mallRN_goods SET";
			foreach ($item_array as $k => $v) {
				$_POST[$v] = str_replace(",", "", checkPostVar($v."_".$uid,''));
				if($k<2) $_POST[$v] = priceLimit($_POST[$v]);
				$sql .= " {$v} = '{$_POST[$v]}',";
			}
			$sql .= " moddate='{$moddate}' WHERE uid='{$uid}'";			
			$mysql->query($sql);	
		}
		
		$cnt = number_format($cnt);		
		logMsg("{$cnt}개의 상품이 저장 되었습니다.","success");

	break;

	case "mileage" :
		
		$item			= checkPostVar('item');
		$mileage_type	= checkPostVar('mileage_type', 1, ['1', '2', '3']);
		$mileage_common	= checkPostVar('mileage_common', 0);
		
		if(!isset($_POST['proc_type1'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$_POST['mileage_level'] = "";
		if($_POST['mileage_type'] == '3') {
			$sql = "SELECT * FROM mallRN_member_level WHERE level < 100 ORDER BY level ASC";
			$mysql->query($sql);

			$mileage_level_arr = array();
			while($row = $mysql->fetch_array()) {
				$mileage_level_arr[] = $row['level'].'|'.checkPostVar('mileage_level_'.$row['level']);				
			}

			if(count($mileage_level_arr)>0) $_POST['mileage_level'] = join("|*|",$mileage_level_arr);

			unset($mileage_level_arr);
		}

		$where = goodsTypeWhere($_POST['proc_type1'], $item);

		if(!preg_match("/b./i",$where)) {
			$sql = "UPDATE mallRN_goods a SET
					a.mileage_type	= '{$_POST['mileage_type']}',
					a.mileage_common	= '{$_POST['mileage_common']}',
					a.mileage_level	= '{$_POST['mileage_level']}',
					a.moddate		= '{$moddate}'
				WHERE a.uid != 0 {$where}
				";			
		}
		else {
			$sql = "UPDATE mallRN_goods a, mallRN_goods_cate b SET
					a.mileage_type		= '{$_POST['mileage_type']}',
					a.mileage_common		= '{$_POST['mileage_common']}',
					a.mileage_level		= '{$_POST['mileage_level']}',
					a.moddate			= '{$moddate}'
				WHERE a.uid != 0 && a.uid = b.guid {$where}
				";
		}
		$mysql->query($sql);
		$cnt =  $mysql->affected_rows();

		$cnt = number_format($cnt);
		alertMsg("{$cnt}개의 상품의 판매상태가 변경 되었습니다!",$link_page);
		
	break;

	case "delivery" :
		
		$item						= checkPostVar('item');
		$delivery_type				= checkPostVar('delivery_type', 1, ['1', '2', '3', '4', '5']);
		$delivery_price				= checkPostVar('delivery_price', 0);
		$delivery_type_qty			= checkPostVar('delivery_type_qty', 1);
		$delivery_im_areas1_used	= checkPostVar('delivery_im_areas1_used', 0);
		$delivery_im_areas1_price	= checkPostVar('delivery_im_areas1_price', 0);
		$delivery_im_areas2_used	= checkPostVar('delivery_im_areas2_used', 0);
		$delivery_im_areas2_price	= checkPostVar('delivery_im_areas2_price', 0);

		if(!isset($_POST['proc_type1'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$where = goodsTypeWhere($_POST['proc_type1'], $item);

		if(!preg_match("/b./i",$where)) {
			$sql = "UPDATE mallRN_goods a SET
					a.delivery_type				= '{$delivery_type}',
					a.delivery_type_qty			= '{$delivery_type_qty}',
					a.delivery_price			= '{$delivery_price}',
					a.delivery_im_areas1_used	= '{$delivery_im_areas1_used}',
					a.delivery_im_areas1_price	= '{$delivery_im_areas1_price}',
					a.delivery_im_areas2_used	= '{$delivery_im_areas2_used}',
					a.delivery_im_areas2_price	= '{$delivery_im_areas2_price}',
					a.moddate					= '{$moddate}'
				WHERE a.uid != 0 {$where}
				";			
		}
		else {
			$sql = "UPDATE mallRN_goods a, mallRN_goods_cate b SET
					a.delivery_type				= '{$delivery_type}',
					a.delivery_type_qty			= '{$delivery_type_qty}',
					a.delivery_price			= '{$delivery_price}',
					a.delivery_im_areas1_used	= '{$delivery_im_areas1_used}',
					a.delivery_im_areas1_price	= '{$delivery_im_areas1_price}',
					a.delivery_im_areas2_used	= '{$delivery_im_areas2_used}',
					a.delivery_im_areas2_price	= '{$delivery_im_areas2_price}',
					a.moddate					= '{$moddate}'
				WHERE a.uid != 0 && a.uid = b.guid {$where}
				";
		}		
		$mysql->query($sql);
		$cnt =  $mysql->affected_rows();

		$cnt = number_format($cnt);
		alertMsg("{$cnt}개의 상품의 배송비가 변경 되었습니다!",$link_page);
		
	break;

	case "name" :
		
		$item		= checkPostVar('item');
		if(!isset($_POST['proc_type1'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			
		$add_name1 = "";
		$name	= checkPostVar('prev_name');		
		
		if($name) {
			$name = str_replace(array("<p>","</p>"), '', $name);
			$name2 = html2txt($name);
			$name2 = str_replace('&nbsp;', ' ', $name2);
			$add_name1 = ", a.name = concat('{$name2}',a.name), a.name_code_able = concat('{$name}',a.name_code_able)";
		}

		$add_name2 = "";
		$name	= checkPostVar('next_name');		
		if($name) {
			$name = str_replace(array("<p>","</p>"), '', $name);
			$name2 = html2txt($name);
			$name2 = str_replace('&nbsp;', ' ', $name2);
			$add_name2 = ", a.name = concat(a.name,'{$name2}'), a.name_code_able = concat(a.name_code_able,'{$name}')";
		}

		$add_name3 = "";
		$change_name	= checkPostVar('change_name');
		$changed_name	= checkPostVar('changed_name');
		if($change_name) {
			$add_name3 = ", a.name = replace(a.name,'{$change_name}','{$changed_name}'), a.name_code_able = replace(a.name_code_able,'{$change_name}','{$changed_name}')";
		}
		
		if(!$add_name1 && !$add_name2 && !$add_name3) logMsg('변경 사항이 없습니다.');

		$where = goodsTypeWhere($_POST['proc_type1'], $item);
		
		if(!preg_match("/b./i",$where)) {
			$sql = "UPDATE mallRN_goods a SET
					a.moddate		= '{$moddate}'
					{$add_name1}
					{$add_name2}
					{$add_name3}
				WHERE a.uid != 0 {$where}
				";			
		}
		else {
			$sql = "UPDATE mallRN_goods a, mallRN_goods_cate b SET
					a.moddate		= '{$moddate}'
					{$add_name1}
					{$add_name2}
					{$add_name3}
				WHERE a.uid != 0 && a.uid = b.guid {$where}
				";
		}		

		$mysql->query($sql);
		$cnt =  $mysql->affected_rows();

		$cnt = number_format($cnt);
		alertMsg("{$cnt}개의 상품명이 변경 되었습니다!",$link_page);
		
	break;

	case "price" :
		
		$item		= checkPostVar('item');
		if(!isset($_POST['proc_type1'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$where = goodsTypeWhere($_POST['proc_type1'], $item);
		
		$msg1	= "";
		$price	= checkPostVar('consumer_price');		
		if($price) {
			$price_type = checkPostVar('price_type3');
			if($price_type!='-') $price_type = '+';

			$add_price = ", a.consumer_price = a.consumer_price {$price_type} {$price}";

			if(!preg_match("/b./i",$where)) {
				$sql = "UPDATE mallRN_goods a SET
						a.moddate		= '{$moddate}'
						{$add_price}
					WHERE a.uid != 0 {$where}
					";			
			}
			else {
				$sql = "UPDATE mallRN_goods a, mallRN_goods_cate b SET
						a.moddate		= '{$moddate}'
						{$add_price}
					WHERE a.uid != 0 && a.uid = b.guid {$where}
					";
			}	
			$mysql->query($sql);
			$cnt =  $mysql->affected_rows();
			$msg1 = "[{$cnt}개의 상품의 소비자] ";
		}

		######################## 판메사 정보 #############################
		$sql = "SELECT * FROM mallRN_vendor WHERE uid>0 ORDER BY comp_name ASC";
		$mysql->query2($sql);

		$vendor_commission	= array();
		while($row = $mysql->fetch_array(2)){
			$vendor_commission[$row['id']] = $row['commission'];	
		}				
		######################## 판메사 정보 #############################
		
		$msg2	= "";
		$price	= checkPostVar('orig_price');
		if($price) {
			$price_type		= checkPostVar('price_type2');
			$price_type2	= checkPostVar('price_type22');
			
			if(!preg_match("/b./i",$where)) {
				$sql = "SELECT a.uid, a.orig_price, a.commission, a.commission_type, a.vendor FROM mallRN_goods a WHERE a.uid != 0 {$where}";
			}
			else {
				$sql = "SELECT a.uid, a.orig_price, a.commission, a.commission_type, a.vendor FROM mallRN_goods a, mallRN_goods_cate b WHERE a.uid != 0 && a.uid = b.guid {$where} GROUP BY a.uid";
			}	
			$mysql->query($sql);

			$cnt = 0;
			while($row = $mysql->fetch_array()) {
				if($price_type2 == 'p') $change_price = ($row['orig_price'] * $price) / 100;
				else					$change_price = $price;

				if($price_type == '+')	$price2 = $row['orig_price'] + $change_price;
				else					$price2 = $row['orig_price'] - $change_price;

				$price2 = priceLimit($price2);

				if($row['vendor']) {
					if($row['commission_type']==1) $commission = $row['commission'];
					else $commission = $vendor_commission[$row['vendor']];
					$def_price = priceLimit($price2 * 100 / (100 - $commission));

					$sql = "UPDATE mallRN_goods SET price = '{$def_price}', orig_price = '{$price2}', moddate = '{$moddate}' WHERE uid = '{$row['uid']}'";
				}
				else {
					$sql = "UPDATE mallRN_goods SET orig_price = '{$price2}', moddate = '{$moddate}' WHERE uid = '{$row['uid']}'";
				}
				$mysql->query2($sql);

				$cnt ++;
			}
			$msg2 = "[{$cnt}개의 상품의 매입가] ";
		}
		
		$msg3	= "";
		$price	= checkPostVar('price');
		if($price) {
			$price_type		= checkPostVar('price_type1');
			$price_type2	= checkPostVar('price_type12');

			if(!preg_match("/b./i",$where)) {
				$sql = "SELECT a.uid, a.price, a.commission, a.commission_type, a.vendor FROM mallRN_goods a WHERE a.uid != 0 {$where}";
			}
			else {
				$sql = "SELECT a.uid, a.price, a.commission, a.commission_type, a.vendor FROM mallRN_goods a, mallRN_goods_cate b WHERE a.uid != 0 && a.uid = b.guid {$where} GROUP BY a.uid";
			}	

			$mysql->query($sql);
			
			$cnt = 0;
			while($row = $mysql->fetch_array()) {
				if($price_type2 == 'p') $change_price = ($row['price'] * $price) / 100;
				else					$change_price = $price;

				if($price_type == '+')	$price2 = $row['price'] + $change_price;
				else					$price2 = $row['price'] - $change_price;

				$price2 = priceLimit($price2);
				
				if($row['vendor']) {
					if($row['commission_type']==1) $commission = $row['commission'];
					else $commission = $vendor_commission[$row['vendor']];
					$orig_price = priceLimit($price2 * ((100 - $commission) / 100));

					$sql = "UPDATE mallRN_goods SET  price = '{$price2}', orig_price = '{$orig_price}', moddate = '{$moddate}' WHERE uid = '{$row['uid']}'";
				}
				else {
					$sql = "UPDATE mallRN_goods SET  price = '{$price2}', moddate = '{$moddate}' WHERE uid = '{$row['uid']}'";
				}
				$mysql->query2($sql);

				$cnt ++;
			}
			$msg3 = "[{$cnt}개의 상품의 판매가] ";
		}

		if(!$msg1 && !$msg2 && !$msg3) logMsg('변경 사항이 없습니다.');

		alertMsg("{$msg1}{$msg2}{$msg3} 변경 되었습니다!",$link_page);
		
	break;

	case "image" :
		
		$item			= checkPostVar('item');
		$image1			= checkPostVar('image1');
		$image2			= checkPostVar('image2');
		$image3			= checkPostVar('image3');
		$other_image	= checkPostVar('other_image');

		if(!isset($_POST['proc_type1'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		if(!$image1 && !$image2 && !$image3 && !$other_image) logMsg('변경 사항이 없습니다.');

		$where = goodsTypeWhere($_POST['proc_type1'], $item);

		if(!preg_match("/b./i",$where)) {
			$sql = "SELECT a.uid, a.image1, a.image2, a.image3, a.other_image FROM mallRN_goods a WHERE a.uid != 0 {$where}";
		}
		else {
			$sql = "SELECT a.uid, a.image1, a.image2, a.image3, a.other_image FROM mallRN_goods a, mallRN_goods_cate b WHERE a.uid != 0 && a.uid = b.guid {$where} GROUP BY a.uid";
		}		
		$mysql->query($sql);
		$cnt =  $mysql->affected_rows();
		
		$image_arr = array('image1','image2','image3','other_image');
		while($row = $mysql->fetch_array()) {
			foreach ($image_arr as $k => $v) {
				if($$v) {
					if($k==3 && $row[$v]) {
						$img_block		= floor($row['uid']/10000);
						$temp_upload	= $img_block.'/'.$row['uid'];		

						$other_upload = UPLOAD_FOLDER.'/'.$temp_upload;

						$other_image2 = explode(",", $row[$v]);
						for($i=0, $cnt2=count($other_image2); $i<$cnt2; $i++) {
							$image	= $other_upload.'/'.$other_image2[$i];
							$thumb	= createThumbnail($image, $$v, 'w');
							if($thumb) @rename($thumb, $image);							
						}	

					}
					else {					
						$base_image = $row[$v];
						if($k==1 || $k==2) {
							if(isset($_POST['image1_use'.$k])) {
								if($_POST['image1_use'.$k]==1) $base_image = $row['image1'];
							}
						}

						$thumb = createThumbnail(IMAGE_FOLDER.$base_image, $$v, 'w');
						if($thumb) @rename($thumb, IMAGE_FOLDER.$row[$v]);
					}
				}
			}
		}		

		$cnt = number_format($cnt);
		alertMsg("{$cnt}개의 상품 이미지 사이즈가 변경 되었습니다!",$link_page);
		
	break;

	case "option" :

		$uid		= checkPostVar('uid');
		
		if(isset($_POST['option_order'])) {
			$option_order			= explode(",",$_POST['option_order']);
			$option_order_cnt		= count($option_order);
			$_POST['option_info']	= multiPostVar($option_order, array('option_name','option_info'));
		}
		else $_POST['option_info'] = "";

		if(!$uid || !isset($_POST['option_order']) || !isset($_POST['optionList_order'])) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM mallRN_goods WHERE uid='{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 상품이 존재하지 않거나 삭제 되었습니다.");

		$option_soldout = 0;
		$so_cnt			= 0;

		######################## 상품 옵션 정보  #########################				
		if(isset($_POST['optionList_order'])) {			
			$optionList_order	= explode(",",$_POST['optionList_order']);
			$option_arr			= array();			

			for($i = 0, $cnt = count($optionList_order); $i < $cnt; $i ++) {
				$op_value = array();
				for($j=1; $j<=$option_order_cnt; $j++) {
					$op_value[] = trim(checkPostVar('option_value'.$j.$optionList_order[$i]));
				}
				$op_value = join("|", $op_value);
				
				$op_price		= priceLimit(checkPostVar('option_price'.$optionList_order[$i]));
				$op_qty_type	= checkPostVar('option_qty_type'.$optionList_order[$i]);
				if($op_qty_type != '1') $op_qty_type = '0';
				$op_qty			= checkPostVar('option_qty'.$optionList_order[$i]);			
				if($op_qty_type == 0 && $op_qty == 0) $so_cnt ++;
				$op_used		= checkPostVar('option_used'.$optionList_order[$i]);
				if($op_used!='1') $op_used = '0';
				$op_code		= checkPostVar('option_code'.$optionList_order[$i]);
				$op_uid			= checkPostVar('option_uid'.$optionList_order[$i]);

				if($op_uid)	{
					$sql = "UPDATE mallRN_goods_option SET 
								guid		= '{$uid}', 
								value		= '{$op_value}',
								price		= '{$op_price}',
								qty_type	= '{$op_qty_type}',
								qty			= '{$op_qty}',
								used		= '{$op_used}',
								code		= '{$op_code}',
								sequence	= '{$i}'
							WHERE  guid='{$uid}' && uid='{$op_uid}'
							";
					$mysql->query($sql);
					$option_arr[] = $op_uid;
				}
				else {
					$sql = "INSERT INTO mallRN_goods_option SET 
								guid		= '{$uid}', 
								value		= '{$op_value}',
								price		= '{$op_price}',
								qty_type	= '{$op_qty_type}',
								qty			= '{$op_qty}',
								used		= '{$op_used}',
								code		= '{$op_code}',
								sequence	= '{$i}'
							";
					$mysql->query($sql);
					$option_arr[] = $mysql->InsertNo();
				}				
			}

			if($so_cnt == 0)			$option_soldout = 0;
			else if($cnt == $so_cnt)	$option_soldout = 2;
			else						$option_soldout	= 1;

			$sql = "SELECT uid FROM mallRN_goods_option WHERE guid='{$uid}'";
			$mysql->query($sql);
			while($row = $mysql->fetch_array()){
				if(!in_array($row['uid'], $option_arr)) {
					$sql = "DELETE FROM mallRN_goods_option WHERE uid='{$row['uid']}'";
					$mysql->query2($sql);
				}
			}
			unset($option_arr);
		}
		######################## 상품 옵션 정보  #########################	
		$sql = "UPDATE mallRN_goods SET moddate='{$moddate}', option_soldout = '{$option_soldout}' WHERE uid='{$uid}'";		
		$mysql->query($sql);

		iframeViewMsg("옵션이 저장 되었습니다.");

	break;

	case "cate" :

		$cate		= checkPostVar('cate_rep');
		$item		= checkPostVar('item');
				
		if(!isset($_POST['proc_type1']) || !isset($_POST['proc_type2']) || !isset($_POST['cate_max_cnt'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$where = goodsTypeWhere($_POST['proc_type1'], $item);
		
		if(!preg_match("/b./i", $where)) {
			$sql = "SELECT a.uid FROM mallRN_goods a WHERE a.uid>0 {$where} ORDER BY a.uid ASC";
		}
		else {
			$sql = "SELECT DISTINCT(a.uid) FROM mallRN_goods a, mallRN_goods_cate b WHERE a.uid>0  && a.uid = b.guid {$where} ORDER BY a.uid ASC";
		}
		$mysql->query($sql);

		$mode_cnt = 0;
		while($row = $mysql->fetch_array()){
			
			$uid		= $row['uid'];
			$ck_mode	= 0;
			
			switch($_POST['proc_type2']) {
				case "1" : //대표분류 변경
					if(!$cate) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
					$sql = "DELETE FROM mallRN_goods_cate WHERE guid='{$uid}' && cate_rep='1'";
					$mysql->query2($sql);
					
					######################## 상품 분류 정보 #########################
					$cate_no = $_POST['cate_no'.$_POST['cate_max_cnt']];
					if($cate_no) {
						$sql = "SELECT uid FROM mallRN_goods_cate WHERE guid='{$uid}' && cate='{$cate_no}'";
						if($cate_uid = $mysql->get_one($sql)) {
							$sql = "UPDATE mallRN_goods_cate SET cate_rep = '1' WHERE uid='{$cate_uid}'";
						}
						else {
							$sql = "INSERT INTO mallRN_goods_cate SET guid = '{$uid}', cate = '{$cate_no}', cate_rep = '1'";
						}						
						$mysql->query2($sql);

						$sql = "SELECT used FROM mallRN_cate WHERE cate = '{$cate_no}'";
						if($mysql->get_one($sql)=='0') $cate_hide = 1;
						else $cate_hide = 0;
						
						$sql = "UPDATE mallRN_goods SET cate='{$cate_no}', cate_hide='{$cate_hide}' WHERE uid='{$uid}'";
						$mysql->query2($sql);

						$ck_mode = 1;
					}
					######################## 상품 분류 정보 #########################					
				break;

				case "2" :  //분류 변경
					if(!$cate) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

					$sql = "DELETE FROM mallRN_goods_cate WHERE guid='{$uid}'";
					$mysql->query2($sql);

					######################## 상품 분류 정보 #########################
					for($i=1; $i<=$_POST['cate_max_cnt']; $i++) {
						$cate_no = $_POST['cate_no'.$i];
						if($cate_no) {
							if($cate==$cate_no) $cate_rep = 1;
							else $cate_rep = 0;
											
							$sql = "INSERT INTO mallRN_goods_cate SET 
										guid		= '{$uid}', 
										cate		= '{$cate_no}',
										cate_rep	= '{$cate_rep}'
									";
							$mysql->query2($sql);
							$ck_mode = 1;
						}
					}

					if($ck_mode==1) {
						$sql = "UPDATE mallRN_goods SET cate='{$cate}' WHERE uid='{$uid}'";
						$mysql->query2($sql);					
					}				
					######################## 상품 분류 정보 #########################
				break;

				case "3" :  //분류 추가
					######################## 상품 분류 정보 #########################
					for($i=1; $i<=$_POST['cate_max_cnt']; $i++) {
						$cate_no = $_POST['cate_no'.$i];
						if($cate_no) {
							$sql = "SELECT uid FROM mallRN_goods_cate WHERE guid='{$uid}' && cate='{$cate_no}'";
							if(!$mysql->get_one($sql)) {
								$sql = "INSERT INTO mallRN_goods_cate SET guid = '{$uid}', cate = '{$cate_no}', cate_rep = '0'";
								$mysql->query2($sql);	
								$ck_mode = 1;
							}							
						}
					}
					######################## 상품 분류 정보 #########################
				break;

				case "4" :  //분류 삭제
					######################## 상품 분류 정보 #########################					
					for($i=1; $i<=$_POST['cate_max_cnt']; $i++) {
						$cate_no = $_POST['cate_no'.$i];
						if($cate_no) {
							$sql = "SELECT uid FROM mallRN_goods_cate WHERE guid='{$uid}' && cate='{$cate_no}' && cate_rep='0'";
							if($mysql->get_one($sql)) {
								$sql = "DELETE FROM mallRN_goods_cate WHERE guid='{$uid}' && cate='{$cate_no}' && cate_rep='0'";
								$mysql->query2($sql);	
								$ck_mode = 1;
							}
						}
					}
					######################## 상품 분류 정보 #########################
				break;

				default :
					logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
				break;
			}
			
			if($ck_mode==1) {
				$sql = "UPDATE mallRN_goods SET moddate='{$moddate}' WHERE uid='{$uid}'";		
				$mysql->query2($sql);
				$mode_cnt++;
			}
		}

		iframeViewMsg("{$mode_cnt}개의 상품분류가 변경 되었습니다!");

	break;

	case "excel" :

		if(preg_match("/none/i",$_FILES["excel"]['tmp_name']) && !$_FILES["excel"]['tmp_name']) {
			logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		}

		$ext = getExtension($_FILES["excel"]['name']);
		if($ext!='xls' && $ext!='xlsx') {			
			logMsg('엑셀파일(xls, xlsx) 파일만 가능 합니다.');
		}

		$fileType = 'Excel2007';
		if($ext == "xls") $fileType = 'Excel5';	
		
		$matching = checkPostVar('matching');
		$img_copy = checkPostVar('img_copy');

		if($matching==1) {
			$sql = "SELECT matching, cate, used FROM mallRN_cate WHERE cate_sub = '0' ORDER BY cate ASC";
			$mysql->query($sql);
			
			$cate_matching = array();
			while($row = $mysql->fetch_array()){
				$cate_matching[$row['matching']] = $row['cate'];			
			}
		}
		
		$sql			= "SELECT goods_require_info FROM mallRN_configuration WHERE uid = 1";
		$require_data	= $mysql->get_one($sql);

		if($require_data) {
			$require_data_array = array();
			$require_data2 = explode("|*|",$require_data);
			for($i = 0, $k = 1, $cnt=count($require_data2); $i<$cnt; $i++) {
				$require_data3	= explode("|",$require_data2[$i]);
				if($require_data3[1]==0) continue;				
				
				$require_data4	= explode("\r\n",(specialStrReplace($require_data3[2])));				
				$require_data_array2 = array();
				foreach($require_data4 as $k2 => $v2) {
					$require_data5	= explode("-", $v2);
					if(!isset($require_data5[1])) $require_data5[1] = "";			
					
					$require_data_array2[] = trim($require_data5[0])."|상품상세 참조|".trim($require_data5[1]);
				}

				$require_data_array[$k] = join("|*|", $require_data_array2);
				$k++;
			}
			unset($require_data, $require_data2, $require_data3, $require_data4, $require_data5, $require_data_array2);
		}

		include_once(PATH_LIB.'/PHPExcel/IOFactory.php');

		$file = $_FILES['excel']['tmp_name'];

		$objReader = PHPExcel_IOFactory::createReader($fileType);
		//$objReader->setReadDataOnly(true);	

		$objPHPExcel = $objReader->load($file);
		$sheet = $objPHPExcel->getSheet(0);

		$num_rows = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		
		$field_arr = array('name','cate','consumer_price','price','orig_price','brand','make','origin','model','goods_code','display_use','sale_use','qty','keyword','detail','explains','option','image1','image2','image3','other_image','require_info','delivery_type');

		$item_array2 = array('cate','name','name_code_able','price','orig_price','consumer_price','commission','explains','qty_type','qty','require_info','display_use','sale_use','goods_code','detail','brand','make','origin','model','delivery_type','delivery_price','keyword','cate_hide','signdate');		
		
		$tmp_block = "";		
		for($l = 2, $cnt = 0; $l <= $num_rows; $l++) {			
	        $rowData = $sheet->rangeToArray('A'.$l.':'.$highestColumn.$l, NULL, TRUE, FALSE);
			
			foreach ($field_arr as $k => $v) {
				${$v} = trim(addslashes($rowData[0][$k]));
			}

			$price		= priceLimit($price);
			$orig_price = priceLimit($orig_price);
			
			if(!$name || !$cate || !$price) continue;
			$cnt++;

			$commission	= round((($price - $orig_price) * 100) / $price, 2);

			$sql = "SELECT MAX(uid) FROM mallRN_goods";
			$maxUid = $mysql->get_one($sql);
			
			if(!$maxUid) $maxUid = '10000';
			else $maxUid++;

			$img_block	= floor($maxUid/10000);	
			$tmp_uid	= $maxUid.getCode(4);

			if($img_block!=$tmp_block) {				
				for($i = 1; $i < 4; $i ++) {
					if(!is_dir(IMAGE_FOLDER.$i.'/'.$img_block)) mkdir(IMAGE_FOLDER.$i.'/'.$img_block,0707);	
				}
				if(!is_dir(UPLOAD_FOLDER.'/'.$img_block)) mkdir(UPLOAD_FOLDER.'/'.$img_block,0707);	
				if(!is_dir(SN_INFO_FOLDER.'/'.$img_block)) mkdir(SN_INFO_FOLDER.'/'.$img_block,0707);	
			}
			
			if($matching==1) $cate = $cate_matching[$cate];
			
			if($require_info) $require_info = $require_data_array[$require_info];
			
			$sql = "SELECT used FROM mallRN_cate WHERE cate = '{$cate}'";
			if($mysql->get_one($sql)=='0') $cate_hide = 1;
			else $cate_hide = 0;

			$name_code_able = $name;
			if($qty=="무제한") {
				$qty_type	= "1";
				$qty		= "0";
			}
			else {
				$qty_type	= "0";
				$qty		= returnNumeric($qty);
			}

			if($display_use == 'Y') $display_use = 1;
			else					$display_use = 0;
			if($sale_use == 'Y')	$sale_use = 1;
			else					$sale_use = 0;

			$delivery_price		= 0;
			$delivery_type_tmp	= explode("|", $delivery_type);
			$delivery_type		= $delivery_type_tmp[0];
			if(!$delivery_type)	$delivery_type = 1;
			if(isset($delivery_type_tmp[1])) $delivery_price = preg_replace("/[^0-9]*/s", "", $delivery_type_tmp[1]);
			unset($delivery_type_tmp);

			$signdate = time();
			
			######################## 이미지 복사  #########################
			$base_img = "";
			for($i = 1; $i < 4; $i ++) {
				$image = ${"image".$i};
				if($image) {					
					$image		= explode("?", $image);
					$image		= $image[0];
					$orig_img	= getURLimg($image);
					
					if(!preg_match("/not found/i",$orig_img) && !preg_match("/302 Found/i",$orig_img)) {
						$save_name = $tmp_uid.".".getExtension($image);				
						file_put_contents(IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name, $orig_img);							
						${"image".$i} = $i.'/'.$img_block.'/'.$save_name;
					}
					if($i==1) {
						$base_img = IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name;
						$base_ext = getExtension($image);
					}
					unset($orig_img);
				}
				else {

					if($base_img) {
						$img_size = $IMG_DEFINE['image'.$i];						
						$save_name = $tmp_uid.".".$base_ext;
						$thumb = createThumbnail($base_img, $img_size, 'w');
						if($thumb) @rename($thumb, IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name);
						${"image".$i} = $i.'/'.$img_block.'/'.$save_name;
					}
				}
			}
			######################## 이미지 복사  #########################

			######################## 싱품등록  #########################			
			$sql = "INSERT INTO mallRN_goods SET";
			foreach ($item_array2 as $k => $v) {
				if($k==count($item_array2)-1) $sql .= " {$v} = '{$$v}'";
				else $sql .= " {$v} = '{$$v}',";
			}		
			$mysql->query($sql);
			$uid = $mysql->InsertNo();
			$re_uid = (100009999 - $uid);
			######################## 싱품등록  #########################	
			
			######################## 상품 분류 정보 #########################
			$sql = "INSERT INTO mallRN_goods_cate SET 
						guid		= '{$uid}', 
						cate		= '{$cate}',
						cate_rep	= '1'
					";
			$mysql->query($sql);						
			######################## 상품 분류 정보 #########################

			######################## 이미지 이름변경  #########################
			for($i = 1; $i < 4; $i ++) {
				$prev_image = ${"image".$i};
				if($prev_image) {
					${"image".$i} = str_replace($tmp_uid, $uid, $prev_image); 
					$save_name = $uid.".".getExtension($prev_image);
					@rename(IMAGE_FOLDER.$prev_image, IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name);
				}
			}
			
			$sql = "UPDATE mallRN_goods SET image1='{$image1}', image2='{$image2}', image3='{$image3}', re_uid = '{$re_uid}' WHERE uid='{$uid}'";
			$mysql->query($sql);
			unset($save_name);
			######################## 이미지 이름변경  #########################

			######################## 추가이미지 복사  #########################
			if($other_image) {
				$other_image = explode("|", $other_image);
				if(!is_dir(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid)) mkdir(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid,0707);

				$other_image2 = array();
				foreach ($other_image as $k => $v) {
					$v = trim($v);
					if($v) {
						$v			= explode("?", $v);
						$v			= $v[0];
						$orig_img	= getURLimg($v);

						if(!preg_match("/not found/i", $orig_img) && !preg_match("/302 Found/i", $orig_img)) {
							$idx = sprintf('%03d', $k+1);
							$save_name = "other_image_{$idx}.".getExtension($v);				
							file_put_contents(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid.'/'.$save_name, $orig_img);							
							$other_image2[] = $save_name;
						}
						unset($orig_img);
					}
				}
				$other_image = join(",",$other_image2);
				$sql = "UPDATE mallRN_goods SET other_image = '{$other_image}' WHERE uid='{$uid}'";
				$mysql->query($sql);
			}
			######################## 추가이미지 복사  #########################

			######################## 상세이미지 서버에 복사  #########################
			$cks = 0;
			if($img_copy=='1') {
				$detail_image2	= array();
				$arrimgurl		= array();
				$p				= explode("\n", stripslashes($explains));
				for($u=0; $u<count($p); $u++) {
					if(preg_match("/title=/i", $p[$u])) {
						$pos1 = strpos($p[$u], "title=", 0);	
						$pos2 = strpos($p[$u], "\"", $pos1)+1;
						$pos3 = strpos($p[$u], "\"", $pos2);

						$tmp_name = substr($p[$u], $pos1, ($pos3-$pos1));		
						$p[$u] = str_replace($tmp_name, "", $p[$u]);
					}
					
					while(preg_match("/[^=\"']*\.(gif|jpg|bmp|png)([?|\"|\'| |>]){1}/i", $p[$u], $val)){
						$arrimgurl[substr($val[0],0,-1)]=substr($val[0],0,-1);
						$p[$u]=str_replace(substr($val[0],0,-1),"",$p[$u]);
					}
				}
				
				if(!is_dir(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid)) mkdir(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid,0707);	
				$k2 = 0;
				foreach ($arrimgurl as $k => $v) {	
					$v			= trim($v);
					if(preg_match("/{$_SERVER['HTTP_HOST']}/i",$v)) continue;
					$filename	= substr(strrchr($v,"/"),1);
					$orig_img	= getURLimg($v);

					if(!preg_match("/not found/i",$orig_img) && !preg_match("/302 Found/i",$orig_img)) {
						$idx = sprintf('%03d', $k2+1);
						$save_name = "detail_image_{$idx}.".getExtension($filename);				
						file_put_contents(UPLOAD_FOLDER.'/'.$img_block.'/'.$uid.'/'.$save_name, $orig_img);							
						$detail_image2[] = $save_name;
						$k2++;
					}
					unset($orig_img);

					$explains = str_replace($v, UPLOAD_FOLDER.'/'.$img_block.'/'.$uid.'/'.$save_name,  $explains);
					$cks = 1;
				}				
			}

			if($cks==1) {
				$detail_image = join(",",$detail_image2);
				$sql = "UPDATE mallRN_goods SET explains = '{$explains}', detail_image = '{$detail_image}' WHERE uid='{$uid}'";
				$mysql->query($sql);
			}	
			######################## 상세이미지 서버에 복사  #########################

			######################## 상품 옵션 정보  #########################		
			if($option) {				
				$so_cnt				= 0;
				$optionData			= explode("|*|",$option);
				$optionDataTitle	= explode("|",$optionData[0]);
				
				foreach ($optionDataTitle as $k => $v) {
					${"opTitle".$k} = array();		
				}

				for($i = 1, $cnti = count($optionData); $i < $cnti; $i ++) {
					$op_value = array();
					$optionData2	= explode("||",$optionData[$i]);
					$op_value		= $optionData2[0];
					$optionDataTitle2 = explode("|", $op_value);
					foreach ($optionDataTitle as $k => $v) {
						${"opTitle".$k}[] = $optionDataTitle2[$k];
					}

					$op_price		= priceLimit(preg_replace("/[^0-9]*/s", "", $optionData2[1]));
					if($optionData2[2]=="무제한") {
						$op_qty_type = "1";
						$op_qty		 = "0";
					}
					else {
						$op_qty_type = "0";
						$op_qty = preg_replace("/[^0-9]*/s", "", $optionData2[2]);
						if($op_qty == 0) $so_cnt ++;
					}
					
					$op_used		= 1;					
					$op_code		= $optionData2[3];

					$sql = "INSERT INTO mallRN_goods_option SET 
								guid		= '{$uid}', 
								value		= '{$op_value}',
								price		= '{$op_price}',
								qty_type	= '{$op_qty_type}',
								qty			= '{$op_qty}',
								used		= '{$op_used}',
								code		= '{$op_code}',
								sequence	= '{$i}'
							";
					$mysql->query($sql);
				}

				if($so_cnt == 0)			$option_soldout = 0;
				else if($cnti == $so_cnt)	$option_soldout = 2;
				else						$option_soldout	= 1;

				$option_info = array();
				foreach ($optionDataTitle as $k => $v) {
					${"opTitle".$k} = array_unique(${"opTitle".$k});
					$option_info[] = $v."|".join(",", ${"opTitle".$k});		
				}
				$option_info = join("|*|", $option_info);

				$sql = "UPDATE mallRN_goods SET option_use = '1', option_info = '{$option_info}', option_soldout = '{$option_soldout}' WHERE uid='{$uid}'";
				$mysql->query($sql);
			}
			######################## 상품 옵션 정보  #########################	

			$tmp_block = $img_block;			
		}

		alertMsg("{$cnt}건의 상품이 등록 되었습니다.",$link_page);

	break;

    case "write" : 
		
		if(!$_POST['name'] || strlen($_POST['price']) == 0 || !$_FILES["image1"]['tmp_name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$_POST['signdate']			= time();
		
		$sql = "SELECT used FROM mallRN_cate WHERE cate = '{$_POST['cate']}'";
		if($mysql->get_one($sql)=='0') $_POST['cate_hide'] = 1;
		else $_POST['cate_hide'] = 0;

		array_push($item_array,'image1','image2','image3','cate_hide','signdate');
		
		######################## 싱품등록  #########################			
		$sql = "INSERT INTO mallRN_goods SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}		
		$mysql->query($sql);
		$uid	= $mysql->InsertNo();
		$re_uid = (100009999 - $uid);
		######################## 싱품등록  #########################		
		
		######################## 판매사 판매상태 체크  #########################
		if($_POST['vendor']) {
			$sql = "SELECT sell FROM mallRN_vendor WHERE id = '{$_POST['vendor']}'";
			if($mysql->get_one($sql) != 'A') {
				$sql = "UPDATE mallRN_goods SET vendor_hide = 1 WHERE uid = '{$uid}'";
				$mysql->query($sql);
			}
		}
		######################## 판매사 판매상태 체크  #########################

		######################## 이미지 이름변경  #########################
		for($i=1;$i<4;$i++) {
			${"image".$i} = str_replace($tmp_uid, $uid, $_POST['image'.$i]); 
			$save_name = $uid.".".getExtension($_POST['image'.$i]);
			@rename(IMAGE_FOLDER.$_POST['image'.$i], IMAGE_FOLDER.$i.'/'.$img_block.'/'.$save_name);
		}
		
		$sql = "UPDATE mallRN_goods SET image1 = '{$image1}', image2 = '{$image2}', image3 = '{$image3}', re_uid = '{$re_uid}' WHERE uid = '{$uid}'";
		$mysql->query($sql);
		unset($save_name);
		######################## 이미지 이름변경  #########################

		if(isset($_POST['temp_upload'])) {
			######################## 상품상세 이미지  #########################
			$ck_files = 0;
			$temp_upload = TEMP_UPLOAD_FOLDER.'/'.previlDecode($_POST['temp_upload']);
			$handle	= @opendir($temp_upload);	
			while ($file = @readdir($handle)) {
				if($file != '.' && $file != '..') {
					$ck_files = 1;
					break;
				}
			}
			@closedir($handle);	

			if($ck_files==1) {			
				copyTree($temp_upload, UPLOAD_FOLDER.'/'.$img_block.'/'.$uid);	
				if($_POST['explains']) {
					$explains = str_replace($temp_upload, UPLOAD_FOLDER.'/'.$img_block.'/'.$uid, $_POST['explains']);
					$sql = "UPDATE mallRN_goods SET explains = '{$explains}' WHERE uid='{$uid}'";
					$mysql->query($sql);
					unset($explains);
				}
			}		
			delTree($temp_upload);
			######################## 상품상세 이미지  #########################
		
			######################## 이용안내 이미지  #########################
			$temp_upload = SN_INFO_FOLDER.'/'.previlDecode($_POST['temp_upload']);
			if($_POST['information_use']!=1) {
				$ck_files = 0;			
				for($i=1; $i<5; $i++) {
					if($ck_files == 1) break;
					$handle	= @opendir($temp_upload.'/'.$i);	
					while ($file = @readdir($handle)) {
						if($file != '.' && $file != '..') {
							$ck_files = 1;
							break;
						}
					}
					@closedir($handle);
				}

				if($ck_files==1) {
					copyTree($temp_upload, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid);

					$delivery_info	= str_replace($temp_upload, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $_POST['delivery_info']);
					$refund_info	= str_replace($temp_upload, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $_POST['refund_info']);
					$exchange_info	= str_replace($temp_upload, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $_POST['exchange_info']);
					$as_info		= str_replace($temp_upload, SN_INFO_FOLDER.'/'.$img_block.'/'.$uid, $_POST['as_info']);
					$sql = "UPDATE mallRN_goods SET delivery_info = '{$delivery_info}', refund_info = '{$refund_info}', exchange_info = '{$exchange_info}', as_info = '{$as_info}' WHERE uid='{$uid}'";
					$mysql->query($sql);
					unset($delivery_info, $refund_info, $exchange_info, $as_info);
				}		
			}
			delTree($temp_upload);
			SetCookie("temp_upload", "", -999, "/");
			######################## 이용안내 이미지  #########################
		}
		
		######################## 상품 분류 정보 #########################
		for($i=1; $i<=$_POST['cate_max_cnt']; $i++) {
			$cate_no = $_POST['cate_no'.$i];
			if($cate_no) {
				if($_POST['cate']==$cate_no) $cate_rep = 1;
				else $cate_rep = 0;
				
				$sql = "INSERT INTO mallRN_goods_cate SET 
							guid		= '{$uid}', 
							cate		= '{$cate_no}',
							cate_rep	= '{$cate_rep}'
						";
				$mysql->query($sql);			
			}
		}
		######################## 상품 분류 정보 #########################

		######################## 상품 옵션 정보  #########################		
		if(isset($_POST['optionList_order'])) {
			$so_cnt				= 0;
			$optionList_order	= explode(",",$_POST['optionList_order']);

			for($i=0, $cnt = count($optionList_order); $i < $cnt; $i ++) {
				if(!$optionList_order[$i]) continue;

				$op_value = array();
				for($j=1; $j<=$option_order_cnt; $j++) {
					$op_value[] = trim(checkPostVar('option_value'.$j.$optionList_order[$i]));
				}
				$op_value = join("|",$op_value);
				
				$op_price		= priceLimit(checkPostVar('option_price'.$optionList_order[$i]));
				$op_qty_type	= checkPostVar('option_qty_type'.$optionList_order[$i]);
				if($op_qty_type != '1') $op_qty_type = '0';
				$op_qty			= checkPostVar('option_qty'.$optionList_order[$i]);
				if($op_qty_type == 0 && $op_qty == 0) $so_cnt ++;
				$op_used		= checkPostVar('option_used'.$optionList_order[$i]);
				if($op_used != '1') $op_used = '0';
				$op_code		= checkPostVar('option_code'.$optionList_order[$i]);

				$sql = "INSERT INTO mallRN_goods_option SET 
							guid		= '{$uid}', 
							value		= '{$op_value}',
							price		= '{$op_price}',
							qty_type	= '{$op_qty_type}',
							qty			= '{$op_qty}',
							used		= '{$op_used}',
							code		= '{$op_code}',
							sequence	= '{$i}'
						";
				$mysql->query($sql);
			}

			if($so_cnt == 0)			$option_soldout = 0;
			else if($cnt == $so_cnt)	$option_soldout = 2;
			else						$option_soldout	= 1;

			$sql = "UPDATE mallRN_goods SET option_soldout = '{$option_soldout}' WHERE uid='{$uid}'";
			$mysql->query($sql);
		}
		######################## 상품 옵션 정보  #########################		

		alertMsg("상품이 등록 되었습니다.",$link_page);

    break;	

	case "modify" :

		$uid				= checkPostVar('uid');		
		$_POST['moddate']	= time();

		if(!$uid || !$_POST['name'] || strlen($_POST['price'])==0) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$sql = "SELECT * FROM mallRN_goods WHERE uid='{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 상품이 존재하지 않거나 삭제 되었습니다.");

		$sql = "SELECT used FROM mallRN_cate WHERE cate = '{$_POST['cate']}'";
		if($mysql->get_one($sql)=='0') $_POST['cate_hide'] = 1;
		else $_POST['cate_hide'] = 0;

		array_push($item_array, 'cate_hide', 'moddate');
		
		######################## 상품 수정  #########################
		$sql = "UPDATE mallRN_goods SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}

		for($i=1; $i<4; $i++) {
			if($_POST['image'.$i]) $sql .= ", image{$i} = '".$_POST['image'.$i]."'";
		}

		$sql .= " WHERE uid='{$uid}'";		
		$mysql->query($sql);
		######################## 상품 수정  #########################

		######################## 업로드 이미지 체크  #########################
		$other_upload	= UPLOAD_FOLDER.'/'.$img_block.'/'.$uid;
		$other_image2	= array();
		if($_POST['other_image']) $other_image2 = explode(",",$_POST['other_image']);
		if($_POST['detail_image']) $other_image2 = array_merge($other_image2, explode(",",$_POST['detail_image']));
		
		$handle	= @opendir($other_upload);	
		while ($file = @readdir($handle)) {
			if($file != '.' && $file != '..') {
				if(count($other_image2)>0) {
					if(!in_array($file, $other_image2)) unlink("{$other_upload}/{$file}");
				}
				else unlink("{$other_upload}/{$file}");
			}
		}
		@closedir($handle);					
		unset($other_upload, $other_image2);
		######################## 업로드 이미지 체크  #########################
		
		if(isset($_POST['temp_upload'])) {
			######################## 이용안내 이미지  #########################
			$temp_upload = SN_INFO_FOLDER.'/'.previlDecode($_POST['temp_upload']);
			if($_POST['information_use'] == 1) {
				delTree($temp_upload);	
			}		
			SetCookie("temp_upload", "", -999, "/");
			######################## 이용안내 이미지  #########################
		}

		######################## 상품 분류 정보 #########################
		$cate_arr = array();
		for($i=1; $i<=$_POST['cate_max_cnt']; $i++) {
			$cate_no = $_POST['cate_no'.$i];
			if($cate_no) {
				if($_POST['cate']==$cate_no) $cate_rep = 1;
				else $cate_rep = 0;

				$sql = "SELECT count(*) FROM mallRN_goods_cate WHERE guid='{$uid}' && cate='{$cate_no}'";
				if($mysql->get_one($sql)==0) {			
					$sql = "INSERT INTO mallRN_goods_cate SET 
								guid		= '{$uid}', 
								cate		= '{$cate_no}',
								cate_rep	= '{$cate_rep}'
							";
				}
				else {
					$sql = "UPDATE mallRN_goods_cate SET cate_rep	= '{$cate_rep}'	WHERE  guid='{$uid}' && cate='{$cate_no}'";
				}
				$mysql->query($sql);			

				$cate_arr[] = $cate_no;
			}
		}
		
		$sql = "SELECT uid, cate FROM mallRN_goods_cate WHERE guid='{$uid}'";
		$mysql->query($sql);
		while($row = $mysql->fetch_array()){
			if(!in_array($row['cate'],$cate_arr)) {
				$sql = "DELETE FROM mallRN_goods_cate WHERE uid='{$row['uid']}'";
				$mysql->query2($sql);
			}
		}
		unset($cate_arr);
		######################## 상품 분류 정보 #########################

		######################## 상품 옵션 정보  #########################				
		$option_soldout = 0;
		if($_POST['optionList_order']) {			
			$so_cnt				= 0;
			$optionList_order	= explode(",",$_POST['optionList_order']);
			$option_arr			= array();			

			for($i = 0, $cnt = count($optionList_order); $i < $cnt; $i ++) {
				if(!$optionList_order[$i]) continue;

				$op_value = array();
				for($j=1; $j<=$option_order_cnt; $j++) {
					$op_value[] = trim(checkPostVar('option_value'.$j.$optionList_order[$i]));
				}
				$op_value = join("|",$op_value);
				
				$op_price		= priceLimit(checkPostVar('option_price'.$optionList_order[$i]));
				$op_qty_type	= checkPostVar('option_qty_type'.$optionList_order[$i]);
				if($op_qty_type!='1') $op_qty_type = '0';
				$op_qty			= checkPostVar('option_qty'.$optionList_order[$i]);			
				if($op_qty_type == 0 && $op_qty == 0) $so_cnt ++;
				$op_used		= checkPostVar('option_used'.$optionList_order[$i] );
				if($op_used!='1') $op_used = '0';
				$op_code		= checkPostVar('option_code'.$optionList_order[$i]);
				$op_uid			= checkPostVar('option_uid'.$optionList_order[$i]);

				if($op_uid)	{
					$sql = "UPDATE mallRN_goods_option SET 
								guid		= '{$uid}', 
								value		= '{$op_value}',
								price		= '{$op_price}',
								qty_type	= '{$op_qty_type}',
								qty			= '{$op_qty}',
								used		= '{$op_used}',
								code		= '{$op_code}',
								sequence	= '{$i}'
							WHERE  guid='{$uid}' && uid='{$op_uid}'
							";
					$mysql->query($sql);
					$option_arr[] = $op_uid;
				}
				else {
					$sql = "INSERT INTO mallRN_goods_option SET 
								guid		= '{$uid}', 
								value		= '{$op_value}',
								price		= '{$op_price}',
								qty_type	= '{$op_qty_type}',
								qty			= '{$op_qty}',
								used		= '{$op_used}',
								code		= '{$op_code}',
								sequence	= '{$i}'
							";
					$mysql->query($sql);
					$option_arr[] = $mysql->InsertNo();
				}				
			}

			if($so_cnt == 0)			$option_soldout = 0;
			else if($cnt == $so_cnt)	$option_soldout = 2;
			else						$option_soldout	= 1;

			$sql = "SELECT uid FROM mallRN_goods_option WHERE guid='{$uid}'";
			$mysql->query($sql);
			while($row = $mysql->fetch_array()){
				if(!in_array($row['uid'], $option_arr)) {
					$sql = "DELETE FROM mallRN_goods_option WHERE uid='{$row['uid']}'";
					$mysql->query2($sql);
				}
			}
			unset($option_arr);			
		}
		$sql = "UPDATE mallRN_goods SET option_soldout = '{$option_soldout}' WHERE uid='{$uid}'";
		$mysql->query($sql);
		######################## 상품 옵션 정보  #########################		

		alertMsg("상품이 수정 되었습니다.",$link_page);		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
