<?php

include_once('../common/ad_init.php');

define('VENDOR_FOLDER', '../../image/vendor');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_vendor';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('field', 'keyword', 'auth', 'sell', 'goods_auth', 'account_cycle', 'image1', 'image2', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page			= "vendor_list.php?{$addstring}";

if($mode == 'write' || $mode == 'modify') {
	$item_array			= array('auth', 'sell', 'delivery_type', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'goods_auth', 'commission', 'bank_name', 'bank_num', 'bank_owner', 'account_cycle', 'memo');
	$item_default		= array('delivery_type');
	$item_able_value	= array('auth' => ['R', 'Y', 'N'], 'sell' => ['R', 'A', 'N'], 'goods_auth' => ['A', 'P']);
}

switch($mode) {

	case "auth" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		$cnt		= count($item);
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql = "UPDATE mallRN_vendor SET auth = '{$value}' WHERE uid IN (".join(",", $item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}명의 판매사의 승인상태가 변경 되었습니다!", $link_page);

	break;

	case "sell" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		$cnt		= count($item);
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql	= "SELECT * FROM mallRN_vendor WHERE uid IN (".join(",", $item).")";
		$mysql->query($sql);

		$cnt = 0;
		while($row = $mysql->fetch_array()) {
			$sql = "UPDATE mallRN_vendor SET sell = '{$value}' WHERE uid = '{$row['uid']}'";	
			logMsg($sql);
			$mysql->query2($sql);

			if($value == 'A')	$sql = "UPDATE mallRN_goods SET vendor_hide = '0' WHERE vendor = '{$row['id']}'";
			else				$sql = "UPDATE mallRN_goods SET vendor_hide = '1' WHERE vendor = '{$row['id']}'";
			$mysql->query2($sql);
			
			$cnt ++;
		}
		$cnt = number_format($cnt);

		alertMsg("{$cnt}명의 판매사의 판매상태가 변경 되었습니다!", $link_page);

	break;

	case "goods_auth" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		$cnt		= count($item);
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql = "UPDATE mallRN_vendor SET goods_auth = '{$value}' WHERE uid IN (".join(",", $item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}명의 판매사의 상품승인이 변경 되었습니다!", $link_page);

	break;

	case "account_cycle" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		$cnt		= count($item);
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql = "UPDATE mallRN_vendor SET account_cycle = '{$value}' WHERE uid IN (".join(",", $item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}명의 판매사의 정산주기가 변경 되었습니다!", $link_page);

	break;


    case "write" :    
		
		if(!$_POST['id'] || !$_POST['passwd'] || !$_POST['comp_name'] || !$_POST['comp_license_no']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$_POST['passwd']	= md5($_POST['passwd']);
		$_POST['signdate']	= time();
		
		for($i=1;$i<3;$i++) {
			if(!preg_match("/none/i",$_FILES['image'.$i]['tmp_name']) && $_FILES['image'.$i]['tmp_name']) {									
				$_POST['image'.$i] = upFile($_FILES['image'.$i]['tmp_name'], $_FILES['image'.$i]['name'], VENDOR_FOLDER, 1, $_POST['id']."_image{$i}", 1);
			}
			else $_POST['image'.$i] = '';		
		}

		array_push($item_array,'id', 'passwd', 'image1', 'image2', 'signdate');

		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);

		$sql	= "SELECT goods_delivery_info, goods_refund_info, goods_exchange_info, goods_as_info FROM mallRN_configuration WHERE uid = 1";
		$data	= $mysql->one_row($sql);

		$sql = "INSERT INTO mallRN_vendor_configuration SET 
					vendor					= '{$_POST['id']}',
					goods_delivery_info		= '{$data['goods_delivery_info']}',
					goods_refund_info		= '{$data['goods_refund_info']}',
					goods_exchange_info		= '{$data['goods_exchange_info']}',
					goods_as_info			= '{$data['goods_as_info']}',
					delivery_p_price1		= '30000',
					delivery_p_price2		= '3000'
				";

		$mysql->query($sql);

		alertMsg("판매자가 등록 되었습니다.",$link_page);

    break;	
	
	case "modify" :
		
		$uid = checkPostVar('uid');

		if(!$uid || !$_POST['comp_name'] || !$_POST['comp_license_no']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$data = $mysql->one_row($sql)) logMsg("해당 판매자가 존재하지 않거나 삭제 되었습니다.");

		$sql = "UPDATE mallRN_vendor SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";		
		}

		for($i=1;$i<3;$i++) {
			if(!preg_match("/none/i",$_FILES["image".$i]['tmp_name']) && $_FILES["image".$i]['tmp_name']) {

				$up_file = upFile($_FILES["image".$i]['tmp_name'],$_FILES["image".$i]['name'], VENDOR_FOLDER, 1, $data['id']."_image{$i}", 1);
				$sql .= ", image{$i} = '{$up_file}'";
			}			
			else {
				if($_POST['img_del'.$i]=='1') {
					@unlink(VENDOR_FOLDER.'/'.$data['image'.$i]);
					$sql .= ", image{$i} = ''";
				}
			}			
		}
		
		$add_log = "";
		if($_POST['passwd']) {
			$sql .= ", passwd = '".md5($_POST['passwd'])."'";
			$add_log = "/ 비번변경";
		}

		$sql .= " WHERE uid = '{$uid}'";
		$mysql->query($sql);

		if($data['delivery_type'] != $_POST['delivery_type']) {
			if($_POST['delivery_type'] == 0) {
				$sql = "UPDATE mallRN_cart SET vendor_delivery = '{$data['id']}' WHERE vendor = '{$data['id']}'";
			}
			else {
				$sql = "UPDATE mallRN_cart SET vendor_delivery = '' WHERE vendor = '{$data['id']}'";
			}
			$mysql->query($sql);
		}

		if($data['sell'] != $_POST['sell']) {
			if($_POST['sell'] == 'A') {
				$sql = "UPDATE mallRN_goods SET vendor_hide = '0' WHERE vendor = '{$data['id']}'";
			}
			else {
				$sql = "UPDATE mallRN_goods SET vendor_hide = '1' WHERE vendor = '{$data['id']}'";
			}
			$mysql->query($sql);
		}

		if($data['commission'] != $_POST['commission']) {
			$moddate	= time();

			$sql = "SELECT uid, orig_price FROM mallRN_goods  WHERE vendor ='{$data['id']}' && commission_type = 0";			
			$mysql->query($sql);
			
			while($row = $mysql->fetch_array()) {
				$price = priceLimit($row['orig_price'] * 100 / (100 - $_POST['commission']));

				$sql = "UPDATE mallRN_goods SET price = '{$price}', commission = '{$_POST['commission']}', moddate = '{$moddate}' WHERE uid = '{$row['uid']}'";
				$mysql->query2($sql);
			}
		}		

		####################### 관리자 로그 ##########################	
		adminLog($my_id, "판매사정보변경{$add_log} - {$data['id']}", 8);
		####################### 관리자 로그 ##########################

		alertMsg("판매자 정보가 수정 되었습니다.",$link_page);

	break;

	case "delete" :
		
		define('IMAGE_FOLDER', '../../image/goods/img');
		define('UPLOAD_FOLDER', '../../image/goods/upload');
		define('SN_INFO_FOLDER', '../../image/sn_upload/information_use/goods');

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
			$mysql->query2($sql);	

			$sql = "DELETE FROM mallRN_goods_option WHERE guid = '{$uid}'";
			$mysql->query2($sql);	

			$sql = "DELETE FROM mallRN_goods_cate WHERE guid = '{$uid}'";
			$mysql->query2($sql);	

			$sql = "DELETE FROM mallRN_favorite_goods WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);
			
			$sql = "DELETE FROM mallRN_goods_recent_view WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);

			$sql = "DELETE FROM mallRN_goods_view WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);

			$sql = "DELETE FROM mallRN_review WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);

			$sql = "DELETE FROM mallRN_inquiry WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);

			$sql = "DELETE FROM mallRN_coupon WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);

			$sql = "DELETE FROM mallRN_cart WHERE g_uid = '{$uid}'";
			$mysql->query2($sql);	
		}
		
		
		$uid = checkPostVar('uid');

		if(!$uid) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql	= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		$data	= $mysql->one_row($sql);

		$sql	= "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_vendor_configuration WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_cate_matching WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_list_show_config WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_store_count_list WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_store_count_os WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_store_count_browser WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_store_count_site WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_store_count_keyword WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_store_count_referer WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_favorite_store WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		for($i = 1; $i < 3; $i ++) {		   				
			if($data["image{$i}"]) delFile(VENDOR_FOLDER."/".$data["image{$i}"]);
		}

		$sql = "SELECT uid FROM mallRN_goods WHERE vendor = '{$data['id']}'";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()){
			goodsDel($row['uid']);			
		}			
	
		alertMsg("판매사가 삭제 되었습니다!", $link_page);		

	break;

	case "memo" :
		
		$id = checkPostVar('id');
		$memo = checkPostVar('memo');

		if(!$id) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
	
		$sql = "UPDATE {$table_name} SET memo = '{$memo}' WHERE id = '{$id}'";
		$mysql->query($sql);

		####################### 관리자 로그 ##########################	
		adminLog($my_id, "판매사메모변경 - {$id}", 7);
		####################### 관리자 로그 ##########################

		logMsg("메모가 저장 되었습니다.","success");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
