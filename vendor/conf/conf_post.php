<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode				= isset($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];
$signdate			= time();
$_POST['signdate']	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {
   case "delivery" :
		
		$item_array = array('delivery_type','delivery_d_price','delivery_p_type','delivery_p_price1','delivery_p_price2','delivery_info');

		if(isset($_POST['delivery_order'])) {
			$delivery_order		= explode(",",$_POST['delivery_order']);
			$_POST['delivery_info']	= multiPostVar($delivery_order, array('delivery_num','delivery_used'));
		}
		else $_POST['delivery_info'] = "";

		$sql = "UPDATE mallRN_vendor_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v, '');
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE vendor = '{$v_my_id}'";
		$mysql->query($sql);

		logMsg("배송정책이 설정 되었습니다.","success");

	break;

	case "delivery_config" :
		$mode2	= isset($_GET['mode2']) ? add_escape_re_string($_GET['mode2']) : add_escape_re_string($_POST['mode2']);

		if(!$mode2) logMsg("정보가 제대로 넘어오지 못했습니다.");

		if($mode2 == 'add') {
			
			$title = checkPostVar('area1');
			if(isset($_POST['area2'])) $title .= " ".trim($_POST['area2']);
			$price	= checkPostVar('price');
			
			$sql = "SELECT count(*) FROM mallRN_delivery_configuration WHERE title = '{$title}' && vendor = '{$v_my_id}'";
			if($mysql->get_one($sql)>0) logMsg("{$title} 지역은 이미 등록되어 있습니다.");

			$sql = "INSERT INTO mallRN_delivery_configuration SET
						vendor		= '{$v_my_id}',
						title		= '{$title}',
						price		= '{$price}',
						used		= '1',
						signdate	= '{$signdate}'
					";

			$mysql->query($sql);

			$uid = $mysql->InsertNo();
			echo "<script>parent.deliveryConfInsert({$uid});</script>";
			logMsg("지역별 추가배송비가 추가 되었습니다.","success");
		}
		else if($mode2 == 'del') {
			
			$uid = $_POST['uid'];
			if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

			$sql = "DELETE FROM mallRN_delivery_configuration WHERE uid='{$uid}' && vendor = '{$v_my_id}'";
			$mysql->query($sql);
			
			logMsg("지역별 추가배송비가 삭제 되었습니다.","success");

		}
		else if($mode2 == 'mod') {
			
			$uid = checkPostVar('uid');
			$used = checkPostVar('used');

			if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

			$sql = "UPDATE mallRN_delivery_configuration SET used='{$used}' WHERE uid='{$uid}' && vendor = '{$v_my_id}'";
			$mysql->query($sql);

			logMsg("지역별 추가배송비 사용설정이 변경 되었습니다.","success");

		}
	break;
	
	case "delivery_im_areas" :
		$num	= checkPostVar('num', '', array('1','2'));
		$used	= checkPostVar('used', 0);
		$price	= checkPostVar('price');
		$price	= str_replace(",", "", $price);

		if(!$num) logMsg("정보가 제대로 넘어오지 못했습니다.");

		$sql = "UPDATE mallRN_vendor_configuration SET delivery_im_areas{$num}_used = '{$used}', delivery_im_areas{$num}_price = '{$price}' WHERE vendor = '{$v_my_id}'";
		$mysql->query($sql);
		
		if($num == 1) logMsg("제주도 추가배송설정이 변경 되었습니다.","success");
		else logMsg("제주도외 도서산간 추가배송설정이 변경 되었습니다.","success");	
		
	break;

	case "delivery_im__areas_exception" :
		$uid	= checkPostVar('uid');

		if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT count(*) FROM mallRN_im_areas_except WHERE vendor = '{$v_my_id}' && p_uid = '{$uid}'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_im_areas_except SET p_uid = '{$uid}', vendor = '{$v_my_id}'";
			$mysql->query($sql);
			logMsg("해당지역이 제외 되었습니다.","success");
		}
		else {
			$sql = "DELETE FROM mallRN_im_areas_except WHERE vendor = '{$v_my_id}' && p_uid = '{$uid}'";
			$mysql->query($sql);
			logMsg("해당지역이 제외가 해제 되었습니다.","success");
		}		
		
	break;

	case "delivery_im__areas_del" :
		$uid	= checkPostVar('uid');

		if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");
		
		$sql	= "DELETE FROM mallRN_im_areas WHERE uid = '{$uid}' && base = '1' && vendor = '{$v_my_id}'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_im_areas_except WHERE p_uid = '{$uid}' && vendor = '{$v_my_id}'";
		$mysql->query($sql);

		iframeViewMsgParent("해당지역이 삭제 되었습니다.");		
		
	break;

	case "delivery_im_areas_add" :
		$postcode	= checkPostVar('postcode');
		if(!is_numeric($postcode) || strlen($postcode) != 5) logMsg("우편번호를 정확히 입력 하세요.");
		$address	= checkPostVar('address');
		$signdate	= time();

		if(!$postcode || !$address) logMsg("정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT count(*) FROM mallRN_im_areas WHERE vendor = '{$v_my_id}' && postcode = '{$postcode}'";
		if($mysql->get_one($sql) > 0) logMsg("이미 등록된 지역 입니다.");

		$sql = "INSERT INTO mallRN_im_areas SET postcode = '{$postcode}', address = '{$address}', base = '1', signdate = '{$signdate}', vendor = '{$v_my_id}'";
		$mysql->query($sql);

		iframeViewMsgParent2("지역이 추가 되었습니다.");			

	break;

	case "goods" :
		
		$multi_arr = array('option','brand','make','origin');

		foreach ($multi_arr as $k => $v) {
			if($_POST[$v.'_order']) {
				$order = explode(",", $_POST[$v.'_order']);
				if($k==0 || $k==4) {
					$_POST['goods_'.$v.'_info']	= multiPostVar($order, array('goods_'.$v.'_name','goods_'.$v.'_used','goods_'.$v.'_info'));
				}
				else $_POST['goods_'.$v.'_info'] = multiPostVar($order, array('goods_'.$v.'_name','goods_'.$v.'_used'));			
			}
		}
		
		$item_array = array('goods_option_info','goods_brand_info','goods_make_info','goods_origin_info','goods_delivery_info','goods_refund_info','goods_exchange_info','goods_as_info');	

		$sql = "UPDATE mallRN_vendor_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);
			if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE vendor = '{$v_my_id}'";
		$mysql->query($sql);
		
		logMsg("상품환경정보가 설정 되었습니다.","success");

	break;
	
	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
