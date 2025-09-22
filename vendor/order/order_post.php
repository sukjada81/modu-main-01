<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode			= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

$table_name		= 'mallRN_order_info';
$table_name2	= 'mallRN_order_goods';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$order_num		= checkPostVar('order_num');

if($mode != 'secStatus0' && $mode != 'secStatus1' && $mode != 'statusChange2' && $mode != 'delivery') {
	if(!$order_num) {
		logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
	}

	$sql = "SELECT count(*) FROM mallRN_order_goods WHERE vendor_delivery = '{$v_my_id}' && order_num = '{$order_num}' && reals = 1";
	if($mysql->get_one($sql) == 0) {
		logMsg('해당주문이 삭제되었거나 존재하지 않습니다.');
	}
}

######################## 파라미터 링크 추가  #########################
$addstring			= "";
$search_variable	=  array('field', 'keyword', 'date_type', 's_date', 'e_date', 's_range1', 'e_range1', 'range1', 'member', 'mobile', 'pay_type', 'pay_status', 'cash_receipts', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}
######################## 파라미터 링크 추가  #########################

$tmp_status	= checkGetVar('status');
$link_page	= "order_list.php?{$addstring}";
$link_page2	= "order_change_list.php?{$addstring}";
$link_page3	= "order_status_list.php?status={$tmp_status}{$addstring}";
$re_page	= "order_info.php?order_num={$order_num}{$addstring}";
$signdate	= time();

switch($mode) {
	
	case "statusChange" : case "statusChange2" :
		
		$item				= checkPostVar('item');
		$status				= checkPostVar('status');
		$delivery			= checkPostVar('delivery');
		$delivery_number	= checkPostVar('delivery_number');
		$add_query			= "";
				
		if(!$item || !$status) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		if($status == 3) {
			$sql		= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
			$payment_cp	= stripslashes($mysql->get_one($sql)); 

			$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
			$SHOP_URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;

			$sms_send_array	= array();
			$sms_cnt_array	= array();
		}
		
		if($delivery && $delivery_number) {
			$add_query = "delivery_info = '{$delivery}|{$delivery_number}'";
		}

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$sql	= "SELECT * FROM {$table_name2} WHERE uid = '{$item[$i]}' && vendor_delivery = '{$v_my_id}'";
			$data	= $mysql->one_row($sql);
			if($data && $data['status'] < 5) {
				$order_num	= $data['order_num'];

				if($data['status'] != $status) {
					$sql = "UPDATE {$table_name2} SET status = '{$status}', status_date = '{$signdate}' WHERE uid = '{$data['uid']}'";
					$mysql->query($sql);
					
					$sql = "INSERT INTO mallRN_order_log SET
								order_num	= '{$order_num}',
								og_uid		= '{$data['uid']}',
								id			= '판매사 : {$v_my_id}',
								prev_status	= '{$data['status']}',
								status		= '{$status}',
								signdate	= '{$signdate}'
							";
					$mysql->query($sql);
				}

				if($add_query) {
					$sql = "UPDATE {$table_name2} SET {$add_query} WHERE uid = '{$data['uid']}'";
					$mysql->query($sql);

					if($status == 3) {
						status3Mail($order_num, $data['uid']);

						$sql				= "SELECT name, cell FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
						$data2				= $mysql->one_row($sql);

						$delivery_info		= getDeliveryInfo($delivery);
						$sms_send_array[]	= [$order_num, stripslashes($data2['name']), stripslashes($data['g_name']), $delivery_info[0], $delivery_number, $data2['cell']];
						if(isset($sms_cnt_array[$order_num]))	$sms_cnt_array[$order_num] ++;
						else									$sms_cnt_array[$order_num] = 1;

						$sql		= "SELECT count(*) FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1 && escrow = '1' && pay_type = 'V'";
						if($mysql->get_one($sql) > 0) {
							switch($payment_cp) {
								case "KCP" :
									socketPost($SHOP_URL."plugin/kcp/deli_ok.php?order_num={$order_num}");
								break;
								case "NICEPAY" :
									socketPost($SHOP_URL."plugin/nicepay/deli_ok.php?order_num={$order_num}");
								break;
								case "INICIS" :
									socketPost($SHOP_URL."plugin/inicis/deli_ok.php?order_num={$order_num}");
								break;
							}
						}
					}
				}

				if($status == 4 && $data['mileage']) {
					$mileage = $data['mileage'] * $data['qty'];
					
					$sql		= "SELECT id FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
					$id			= $mysql->get_one($sql);

					$content	= $data['g_name']." 상품구매 마일리지 적립";

					$sql		= "SELECT count(*) FROM mallRN_mileage WHERE id = '{$id}' && order_num = '{$order_num}' && goods_uid = '{$data['uid']}'";
					if($mysql->get_one($sql) == 0) {
						saveMileageChange($id, $mileage, $content, $order_num, $data['uid']);
					}
				}
			}
		}	

		if($status == 3) {
			status3Sms($sms_send_array, $sms_cnt_array);
		}
		
		if($mode == "statusChange2")	alertMsg("{$cnt}건의 주문상품의 상태가 변경 되었습니다!", $link_page3);
		else							alertMsg("{$cnt}건의 주문상품의 상태가 변경 되었습니다!", $re_page);

	break;
	
	case "delivery" :
		$delivery	= checkPostVar('delivery');		
		if(!$delivery) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		if(preg_match("/none/i",$_FILES["excel"]['tmp_name']) && !$_FILES["excel"]['tmp_name']) {
			logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		}

		$ext = getExtension($_FILES["excel"]['name']);
		if($ext!='xls' && $ext!='xlsx') {			
			logMsg('엑셀파일(xls, xlsx) 파일만 가능 합니다.');
		}

		$sql		= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
		$payment_cp	= stripslashes($mysql->get_one($sql)); 

		$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
		$SHOP_URL = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT;

		$sms_send_array	= array();
		$sms_cnt_array	= array();

		$fileType = 'Excel2007';
		if($ext == "xls") $fileType = 'Excel5';	

		include_once(PATH_LIB.'/PHPExcel/IOFactory.php');

		$file = $_FILES['excel']['tmp_name'];

		$objReader = PHPExcel_IOFactory::createReader($fileType);
		//$objReader->setReadDataOnly(true);	

		$objPHPExcel = $objReader->load($file);
		$sheet = $objPHPExcel->getSheet(0);

		$num_rows = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
	
		$field_arr = array('signdate2', 'order_num', 'uid', 'delivery_number');

		for($l = 2, $cnt = 0; $l <= $num_rows; $l++) {			
	        $rowData = $sheet->rangeToArray('A'.$l.':'.$highestColumn.$l, NULL, TRUE, FALSE);
			
			foreach ($field_arr as $k => $v) {
				${$v} = trim(addslashes($rowData[0][$k]));
			}

			if($delivery_number) {
				$sql	= "SELECT * FROM {$table_name2} WHERE order_num = '{$order_num}' && uid = '{$uid}' && vendor_delivery = '{$v_my_id}'";
				$data	= $mysql->one_row($sql);
				if($data) {
					if($data['status'] == 1 || $data['status'] == 2) {
						$sql = "UPDATE {$table_name2} SET status = '3', status_date = '{$signdate}', delivery_info = '{$delivery}|{$delivery_number}' WHERE uid = '{$data['uid']}'";
						$mysql->query($sql);
						
						$sql = "INSERT INTO mallRN_order_log SET
									order_num	= '{$order_num}',
									og_uid		= '{$data['uid']}',
									id			= '판매사 : {$v_my_id}',
									prev_status	= '{$data['status']}',
									status		= '3',
									signdate	= '{$signdate}'
								";
						$mysql->query($sql);

						status3Mail($order_num, $data['uid']);

						$sql				= "SELECT name, cell FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
						$data2				= $mysql->one_row($sql);

						$delivery_info		= getDeliveryInfo($delivery);
						$sms_send_array[]	= [$order_num, stripslashes($data2['name']), stripslashes($data['g_name']), $delivery_info[0], $delivery_number, $data2['cell']];
						if(isset($sms_cnt_array[$order_num]))	$sms_cnt_array[$order_num] ++;
						else									$sms_cnt_array[$order_num] = 1;

						$sql		= "SELECT count(*) FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1 && escrow = '1' && pay_type = 'V'";
						if($mysql->get_one($sql) > 0) {
							switch($payment_cp) {
								case "KCP" :
									socketPost($SHOP_URL."plugin/kcp/deli_ok.php?order_num={$order_num}");
								break;
								case "NICEPAY" :
									socketPost($SHOP_URL."plugin/nicepay/deli_ok.php?order_num={$order_num}");
								break;
								case "INICIS" :
									socketPost($SHOP_URL."plugin/inicis/deli_ok.php?order_num={$order_num}");
								break;
							}
						}
					}
				}

				$cnt ++;
			}
		}

		status3Sms($sms_send_array, $sms_cnt_array);

		alertMsg("{$cnt}건의 주문상품의 배송정보가 등록 되었습니다!", "order_list.php?status=3");

	break;

	case "memo" :
		
		$memo = checkPostVar('memo');
	
		$sql = "UPDATE {$table_name} SET memo = '{$memo}' WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		logMsg("메모가 저장 되었습니다.","success");

	break;

	case "status_cancel" : 
		
		$uid			= checkPostVar('uid');

		$sql			= "SELECT og_uid, status, status2 FROM mallRN_order_status_change WHERE order_num = '{$order_num}' && uid = '{$uid}'";
		if(!$data	= $mysql->one_row($sql)) {
			logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");
		}
		
		$status_array	= array("7" => "교환", "8" => "반품", "9" => "취소"); 
		$ttl			= $status_array[$data['status']];
		
		$sql			= "UPDATE mallRN_order_status_change SET status2 = 9, status_date = '{$signdate}', manager = '{$v_my_name}' WHERE order_num = '{$order_num}' && uid = '{$uid}'";
		$mysql->query($sql);

		$sql			= "SELECT status FROM mallRN_order_log WHERE order_num = '{$order_num}' && og_uid = '{$data['og_uid']}' && status2 = 0 ORDER BY uid DESC LIMIT 1";
		$prev_status	= $mysql->get_one($sql);

		if(!$prev_status) {
			$sql		= "SELECT delivery_info FROM mallRN_order_goods WHERE order_num = '{$order_num}' && uid = '{$data['og_uid']}'";
			if(!$mysql->get_one($sql)) $prev_status = 2;
			else $prev_status = 3;
		}

		$sql			= "UPDATE mallRN_order_goods SET status = '{$prev_status}', status2 = 0, status_date = '{$signdate}' WHERE order_num = '{$order_num}' && uid = '{$data['og_uid']}' && status = {$data['status']}";
		$mysql->query($sql);

		$sql = "INSERT INTO mallRN_order_log SET
					order_num		= '{$order_num}',
					og_uid			= '{$data['og_uid']}',
					id				= '판매사 : {$v_my_id}',
					prev_status		= '{$data['status']}',
					prev_status2	= '{$data['status2']}',
					status			= '{$prev_status}',
					status2			= '0',
					signdate		= '{$signdate}'
				";
		$mysql->query($sql);

		alertMsg("{$ttl}거부 처리가 되었습니다.", $link_page2);

	break;
	
	case "status_change_72" : case "status_change_73" :  case "status_change_74" : case "status_change_82" : case "status_change_83" :

		$status_array	= array("72" => "교환승인", "73" => "회수완료", "73" => "교환발송완료", "82" => "반품승인", "83" => "회수완료"); 
		
		$uid			= checkPostVar('uid');
		$add_query		= "";
		
		$status			= substr($mode, -2, 1);
		$status2		= substr($mode, -1, 1);
		$ttl			= $status_array[$status.$status2];

		if($mode == "status_change_74") {
			$delivery			= checkPostVar("delivery");
			$delivery_number	= checkPostVar("delivery_number");

			if(!$delivery || !$delivery_number)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

			$add_query	= ", delivery_info = '{$delivery}|{$delivery_number}'";
		}

		$sql	= "SELECT og_uid, status, status2 FROM mallRN_order_status_change WHERE order_num = '{$order_num}' && uid = '{$uid}'";
		if(!$data	= $mysql->one_row($sql)) {
			logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");
		}

		$sql	= "UPDATE mallRN_order_status_change SET status2 = {$status2}, status_date = '{$signdate}' WHERE order_num = '{$order_num}' && uid = '{$uid}' && status = {$status}";
		$mysql->query($sql);

		$sql	= "UPDATE mallRN_order_goods SET status2 = {$status2}, status_date = '{$signdate}' {$add_query} WHERE order_num = '{$order_num}' && uid = '{$data['og_uid']}' && status = {$status}";
		$mysql->query($sql);

		$sql = "INSERT INTO mallRN_order_log SET
					order_num		= '{$order_num}',
					og_uid			= '{$data['og_uid']}',
					id				= '판매사 : {$v_my_id}',
					prev_status		= '{$data['status']}',
					prev_status2	= '{$data['status2']}',
					status			= '{$status}',
					status2			= '{$status2}',
					signdate		= '{$signdate}'
				";
		$mysql->query($sql);

		if($mode == "status_change_74") {
			status3Mail($order_num, $data['og_uid'], '교환');

			$sql				= "SELECT name, cell FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
			$data2				= $mysql->one_row($sql);
			$delivery_info		= getDeliveryInfo($delivery);
			
			$replace_code_array	= array('ORDER_NAME' => stripslashes($data2['name']), 'GOODS_NAME' => stripslashes($data['g_name']), 'DELIVERY_NAME' => $delivery_info[0], 'DELIVERY_NUM' => $delivery_number);
			mallSmsAuto('delivery', $data2['cell'], $replace_code_array);
		}
		
		alertMsg("{$ttl} 처리가 되었습니다.", $link_page2);

	break;	

}
		
?>
