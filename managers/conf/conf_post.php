<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/common');

$mysql->msgType(1);

$mode				= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$signdate			= time();
$_POST['signdate']	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {
    case "basic" :    

		$item_array = array('basic_url','basic_name','basic_admin','basic_email','basic_cs_time1','basic_cs_time2','basic_cs_time3','basic_cs_time4','basic_title','basic_description','basic_keyword','basic_real_keyword','comp_name','comp_owner','comp_license_no1','comp_license_no2','comp_type','comp_item','comp_email','comp_tel','comp_fax','comp_postcode','comp_address1','comp_address2','comp_rtn_postcode','comp_rtn_address1','comp_rtn_address2');
		
		$basic_url		= checkPostVar('basic_url');
		$basic_name		= checkPostVar('basic_name');
		$basic_admin	= checkPostVar('basic_admin');
		$basic_email	= checkPostVar('basic_email');

		if(!$basic_url || !$basic_name || !$basic_admin || !$basic_email) logMsg("필수 정보가 넘어오지 못했습니다.");		

		######################## 이미지 등록  #########################
		if(!preg_match("/none/i",$_FILES['image1']['tmp_name']) && $_FILES['image1']['tmp_name']) {									
			$image1 = upFile($_FILES['image1']['tmp_name'], $_FILES['image1']['name'], UPLOAD_FOLDER, 1, "basic_image", 1);
			$image1 = ", basic_image = '{$image1}'";
		}
		else {
			$image1 = "";
		}
		######################## 이미지 등록  #########################
		
		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " {$image1} WHERE uid='1'";
		$mysql->query($sql);
		logMsg("기본정보가 설정 되었습니다.","success");

    break;	


	case "payment" :    
			
		$item_array = array('payment_type_b','payment_type_c','payment_type_r','payment_type_v','payment_type_h','payment_cp','payment_shop_id','payment_shop_key','payment_install_range','payment_escrow_v','payment_complex_tax','payment_bank_info','payment_commission_c','payment_commission_r','payment_commission_r2','payment_commission_v','payment_commission_h','cash_receipts_used','cash_receipts_require','cash_receipts_method','naverpay_used','naverpay_mode','naverpay_test_id','naverpay_shop_id','naverpay_key1','naverpay_key2','naverpay_key3');
		$item_default = array('payment_type_b','payment_type_c','payment_type_r','payment_type_v','payment_type_h','payment_escrow_v');

		if(isset($_POST['bank_order'])) {
			$bank_order					= explode(",",$_POST['bank_order']);		
			$_POST['payment_bank_info']	= multiPostVar($bank_order, array('payment_bank_name','payment_bank_num','payment_bank_owner','payment_bank_used'));
		}
		else $_POST['payment_bank_info'];
		
		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='1'";
		$mysql->query($sql);

		$payment_shop_key2	= checkPostVar('payment_shop_key2');
		$payment_shop_id2	= checkPostVar('payment_shop_id2');
		if($payment_shop_key2 || $payment_shop_id2) {
			$sql = "UPDATE mallRN_configuration SET payment_shop_id = '{$payment_shop_id2}', payment_shop_key = '{$payment_shop_key2}' WHERE uid = 2";
			$mysql->query($sql);
		}

		logMsg("결제정책이 설정 되었습니다.","success");

    break;	

	case "delivery" :
		
		$item_array = array('delivery_type','delivery_d_price','delivery_p_type','delivery_p_price1','delivery_p_price2','delivery_info');

		if(isset($_POST['delivery_order'])) {
			$delivery_order		= explode(",", $_POST['delivery_order']);
			$delivery_max_num	= $_POST['delivery_max_num'];		
			$_POST['delivery_info']	= $delivery_max_num."|*|".multiPostVar($delivery_order, array('delivery_num','delivery_name','delivery_url','delivery_used'));
		}
		else $_POST['delivery_info'] = "";

		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v, '');
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='1'";
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
			
			$sql = "SELECT count(*) FROM mallRN_delivery_configuration WHERE title='{$title}'";
			if($mysql->get_one($sql)>0) logMsg("{$title} 지역은 이미 등록되어 있습니다.");

			$sql = "INSERT INTO mallRN_delivery_configuration SET
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

			$sql = "DELETE FROM mallRN_delivery_configuration WHERE uid='{$uid}'";
			$mysql->query($sql);
			
			logMsg("지역별 추가배송비가 삭제 되었습니다.","success");

		}
		else if($mode2 == 'mod') {
			
			$uid = checkPostVar('uid');
			$used = checkPostVar('used');

			if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

			$sql = "UPDATE mallRN_delivery_configuration SET used='{$used}' WHERE uid='{$uid}'";
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

		$sql = "UPDATE mallRN_configuration SET delivery_im_areas{$num}_used = '{$used}', delivery_im_areas{$num}_price = '{$price}' WHERE uid='1'";
		$mysql->query($sql);
		
		if($num == 1) logMsg("제주도 추가배송설정이 변경 되었습니다.","success");
		else logMsg("제주도외 도서산간 추가배송설정이 변경 되었습니다.","success");	
		
	break;

	case "delivery_im__areas_exception" :
		$uid	= checkPostVar('uid');

		if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT count(*) FROM mallRN_im_areas_except WHERE vendor = '' && p_uid = '{$uid}'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_im_areas_except SET p_uid = '{$uid}', vendor = ''";
			$mysql->query($sql);
			logMsg("해당지역이 제외 되었습니다.","success");
		}
		else {
			$sql = "DELETE FROM mallRN_im_areas_except WHERE vendor = '' && p_uid = '{$uid}'";
			$mysql->query($sql);
			logMsg("해당지역이 제외가 해제 되었습니다.","success");
		}		
		
	break;

	case "delivery_im__areas_del" :
		$uid	= checkPostVar('uid');

		if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");
		
		$sql	= "DELETE FROM mallRN_im_areas WHERE uid = '{$uid}' && base = '1'";
		$mysql->query($sql);

		$sql	= "DELETE FROM mallRN_im_areas_except WHERE p_uid = '{$uid}'";
		$mysql->query($sql);

		iframeViewMsgParent("해당지역이 삭제 되었습니다.");		
		
	break;

	case "delivery_im_areas_add" :
		$postcode	= checkPostVar('postcode');
		if(!is_numeric($postcode) || strlen($postcode) != 5) logMsg("우편번호를 정확히 입력 하세요.");
		$address	= checkPostVar('address');
		$signdate	= time();

		if(!$postcode || !$address) logMsg("정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT count(*) FROM mallRN_im_areas WHERE vendor = '' && postcode = '{$postcode}'";
		if($mysql->get_one($sql) > 0) logMsg("이미 등록된 지역 입니다.");

		$sql = "INSERT INTO mallRN_im_areas SET postcode = '{$postcode}', address = '{$address}', base = '1', signdate = '{$signdate}'";
		$mysql->query($sql);

		iframeViewMsgParent2("지역이 추가 되었습니다.");			

	break;

	case "member" :

		$item_array = array('member_auth','member_auth_email','member_auth_cell','member_unavailable_id','member_mileage_yn','member_mileage_validity_yn','member_mileage_validity','member_mileage_validity_type','member_mileage_join','member_mileage_order','member_limit_count','member_limit_minute','member_admin_auth','member_login_limit_minute','member_form_tel','member_form_cell','member_form_address','member_form_birth','member_form_gender','member_form_marry','member_form_job','member_form_hobby','member_form_job_info','member_form_hobby_info','member_form_mailling','member_form_sms','member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item','member_form_add1_title','member_form_add1','member_form_add2_title','member_form_add2','member_form_add3_title','member_form_add3','member_form_add4_title','member_form_add4','member_form_add5_title','member_form_add5');
		$item_default = array('member_auth_email','member_auth_cell');

		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid = '2'";
		$mysql->query($sql);

		$social_array		= array("NAVER", "KAKAO", "GOOGLE", "PAYCO");
		foreach($social_array as $k => $v) {

			$used		= checkPostVar('social_used_'.$v);
			$api_id		= checkPostVar('social_api_id_'.$v);
			$api_key	= checkPostVar('social_api_key_'.$v);


			$sql = "SELECT count(*) FROM mallRN_configuration_social WHERE site = '{$v}'";
			if($mysql->get_one($sql) == 0) {
				$sql = "INSERT INTO mallRN_configuration_social SET
							site		= '{$v}',
							used		= '{$used}',
							api_id		= '{$api_id}',
							api_key		= '{$api_key}'
						";							
			}
			else {
				$sql = "UPDATE mallRN_configuration_social SET
							used		= '{$used}',
							api_id		= '{$api_id}',
							api_key		= '{$api_key}'
						WHERE site = '{$v}'
						";							
			}
			$mysql->query($sql);
		}

		logMsg("회원정책이 설정 되었습니다.","success");

	break;

	case "member_level" :
		$mode2	= isset($_GET['mode2']) ? add_escape_re_string($_GET['mode2']) : add_escape_re_string($_POST['mode2']);

		if(!$mode2) logMsg("정보가 제대로 넘어오지 못했습니다.");
		if($mode2 == 'add') {

			$item_array = array('level','name','discount','mileage','delivery_free','signdate');
			$item_default = array('delivery_free');
		
			if(!$_POST['name']) logMsg("필수정보[등급명]을 입력 하시기 바랍니다."); 
			
			$sql = "SELECT MAX(level) FROM mallRN_member_level WHERE level < 100";
			$level = $mysql->get_one($sql);
			if($level) $_POST['level'] = $level + 1;
			else $_POST['level'] = 1;

			if($_POST['level'] >= 90) logMsg("등급을 더이상 등록할 수 없습니다."); 

			$sql = "INSERT INTO mallRN_member_level SET";
			foreach ($item_array as $k => $v) {
				if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
				else $_POST[$v] = checkPostVar($v);
				if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
				else $sql .= " {$v} = '{$_POST[$v]}',";
			}
			$mysql->query($sql);

			$uid = $mysql->InsertNo();
			echo "<script>parent.memberLevelInsert({$uid},{$_POST['level']});</script>";

			logMsg("회원 등급이 추가 되었습니다.","success");

		}
		else if($mode2 == 'del') {

			$uid = checkPostVar('uid');
			if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

			$sql = "SELECT level FROM mallRN_member_level WHERE uid = '{$uid}'";
			$level = $mysql->get_one($sql);

			if($level == 1 || $level == 100) logMsg("기본 등급은 삭제 하실 수 없습니다.");

			$sql = "DELETE FROM mallRN_member_level WHERE uid='{$uid}'";
			$mysql->query($sql);

			logMsg("회원 등급이 삭제 되었습니다.","success");

		}
		else if($mode2 == 'mod') {
			
			$uid = checkPostVar('uid');
			if(!$uid) logMsg("정보가 제대로 넘어오지 못했습니다.");

			$item_array = array('name','discount','mileage','delivery_free');
			$item_default = array('delivery_free');

			if(!isset($_POST['name'])) logMsg("필수정보[등급명]을 입력 하시기 바랍니다."); 

			$sql = "UPDATE mallRN_member_level SET";
			foreach ($item_array as $k => $v) {
				if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
				else $_POST[$v] = checkPostVar($v);
				if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
				else $sql .= " {$v} = '{$_POST[$v]}',";
			}
			$sql .= " WHERE uid='{$uid}'";
			$mysql->query($sql);

			logMsg("회원 등급이 변경 되었습니다.","success");

		}
	break;

	case "goods" :
		
		$multi_arr = array('option','brand','make','origin','require');

		foreach ($multi_arr as $k => $v) {
			if($_POST[$v.'_order']) {
				$order = explode(",", $_POST[$v.'_order']);
				if($k==0 || $k==4) {
					$_POST['goods_'.$v.'_info']	= multiPostVar($order, array('goods_'.$v.'_name','goods_'.$v.'_used','goods_'.$v.'_info'));
				}
				else $_POST['goods_'.$v.'_info'] = multiPostVar($order, array('goods_'.$v.'_name','goods_'.$v.'_used'));			
			}
		}
		
		if(isset($_POST['icon_order'])) $_POST['icon_order'] = str_replace(",","|",$_POST['icon_order']);
		else $_POST['icon_order'] = '';
		$_POST['goods_icon_info'] = $_POST['icon_order'];

		$item_array = array('goods_price_limit1','goods_price_limit2','goods_soldout','goods_engine_naver','goods_engine_daum','goods_option_info','goods_brand_info','goods_make_info','goods_origin_info','goods_require_info','goods_icon_info','goods_delivery_info','goods_refund_info','goods_exchange_info','goods_as_info');		
		$item_default = array('goods_engine_naver','goods_engine_daum');

		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='1'";
		$mysql->query($sql);
		
		logMsg("상품환경정보가 설정 되었습니다.","success");

	break;


	case "etcs" :

		if(isset($_POST['cancel_order'])) {
			$cancel_order					= explode(",",$_POST['cancel_order']);		
			$_POST['order_cancel_info']	= multiPostVar($cancel_order, array('cancel_name','cancel_used'));
		}
		else $_POST['order_cancel_info'];

		if(isset($_POST['message_order'])) {
			$message_order					= explode(",",$_POST['message_order']);		
			$_POST['order_message_info']	= multiPostVar($message_order, array('message_name','message_used'));
		}
		else $_POST['order_message_info'];
		
		$sql				= "SELECT order_tracker_key FROM mallRN_configuration WHERE uid = 1";
		$order_tracker_key	= $mysql->get_one($sql);

		$item_array = array('order_cancel_info', 'order_message_info', 'order_auto_completed1','order_auto_completed2','order_auto_completed3','order_tracker_yn','order_tracker_key','sms_yn','sms_key','sms_secret', 'sms_pfid','sms_calling_number','sms_admin_number1','sms_admin_number2','sms_admin_number3');
		
		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v, '');
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='1'";
		$mysql->query($sql);

		if($order_tracker_key != $_POST['order_tracker_key']) {
			$sql	= "SELECT status, order_num FROM mallRN_delivery_api_log WHERE uid > 0 ORDER BY uid DESC LIMIT 1";
			if($data	= $mysql->one_row($sql)) {
				if($data['status'] == 2 && $data['order_num'] == '') {					
					socketPost(ABSOLUTE_PATH_SHOP."php/async_tracker.php?param=".previlEncode(date('Ymd', time() - 86400)), 'POST', 0); //비동기 실행
					logMsg("기타정책정보가 설정 되었습니다.<br />APIKEY변경으로 스마트택배API연동이 실행 되었습니다.<br />로그기록을 확인 하시기 바랍니다.","success");
				}
			}
		}

		logMsg("기타정책정보가 설정 되었습니다.","success");

	break;

	case "push" :

		$item_array = array('push_apiKey', 'push_authDomain', 'push_projectId', 'push_storageBucket', 'push_messagingSenderId', 'push_appId', 'push_server_key', 'push_server_key2');
		
		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);
			if($k == count($item_array) - 1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid = '1'";
		$mysql->query($sql);

		$push_info = "var info_apiKey = '{$_POST['push_apiKey']}';\nvar info_authDomain = '{$_POST['push_authDomain']}';\nvar info_projectId = '{$_POST['push_projectId']}';\nvar info_storageBucket = '{$_POST['push_storageBucket']}';\nvar info_messagingSenderId = '{$_POST['push_messagingSenderId']}';\nvar info_appId = '{$_POST['push_appId']}';";

		file_put_contents("../../include/firebase-key-info.js", $push_info);

		logMsg("웹푸시(알림)이 설정 되었습니다.","success");

	break;


	case "agree1" : case "agree2" : case "agree6" :

		if($mode == "agree1") {
			$num	= 1;
			$msg	= "이용약관이";
		}
		else if($mode == "agree6") {
			$num	= 6;
			$msg	= "판매이용약관이";
		}
		else {
			$num = 2;
			$msg	= "개인정보취급방침이";
		}

		$agree_default	= checkPostVar('agree_default');

		if($agree_default == 1) {
			$sql					= "SELECT agreement_info{$num} FROM mallRN_configuration WHERE uid = 3";
			$agreement_default_info	= $mysql->get_one($sql);

			$sql					= "UPDATE mallRN_configuration SET agreement_info{$num} = '{$agreement_default_info}' WHERE uid = '2'";
			$mysql->query($sql);

			alertMsg("{$msg} 초기화 되었습니다.","agree{$num}_info.php");
		}
		else {
			$agreement_info	= checkPostVar('explains');
			$sql = "UPDATE mallRN_configuration SET agreement_info{$num} = '{$agreement_info}' WHERE uid = '2'";
			$mysql->query($sql);
			
			logMsg("{$msg} 설정 되었습니다.","success");
		}

	break;

	case "agree3" :
	
		$agreement_info3	= checkPostVar('explains1');
		$agreement_info4	= checkPostVar('explains2');
		$agreement_info5	= checkPostVar('explains3');

		$sql = "UPDATE mallRN_configuration SET agreement_info3 = '{$agreement_info3}', agreement_info4 = '{$agreement_info4}', agreement_info5 = '{$agreement_info5}' WHERE uid = '2'";
		$mysql->query($sql);

		logMsg("개인정보 수집 및 이용 동의가 설정 되었습니다.","success");		

	break;

	case "script" :
	
		$script_naver_tag			= checkPostVar('script_naver_tag');
		$script_google_analytics	= checkPostVar('script_google_analytics');
		$script_top_code			= checkPostVar('script_top_code');
		$script_bottom_code			= checkPostVar('script_bottom_code');

		$sql = "UPDATE mallRN_configuration SET script_naver_tag = '{$script_naver_tag}', script_google_analytics = '{$script_google_analytics}', script_top_code = '{$script_top_code}', script_bottom_code = '{$script_bottom_code}' WHERE uid = '1'";
		$mysql->query($sql);

		logMsg("외부 스크립트가 설정 되었습니다.","success");		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
