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
if($mode != 'secStatus0' && $mode != 'secStatus1' && $mode != 'secStatus9' && $mode != 'statusChange2' && $mode != 'delivery') {
	if(!$order_num) {
		logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
	}
}

######################## 파라미터 링크 추가  #########################
$addstring			= "";
$search_variable	=  array('field', 'keyword','field2','keyword2','field3','keyword3','field4','keyword4', 'date_type', 's_date', 'e_date', 's_range1', 'e_range1', 'range1', 'member', 'mobile', 'pay_type', 'pay_status', 'cash_receipts', 'new', 'use_mileage', 'use_coupon', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}
######################## 파라미터 링크 추가  #########################

$tmp_status	= checkGetVar('status');
$link_page	= "order_list.php?{$addstring}";
$link_page2	= "order_change_list.php?{$addstring}";
$link_page3	= "headquarters_order_list.php?status={$tmp_status}{$addstring}";
$re_page	= "order_info.php?order_num={$order_num}{$addstring}";
$signdate	= time();

$sql		= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
$payment_cp	= stripslashes($mysql->get_one($sql)); 

switch($mode) {
	case "secStatus0" :

		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$sql = "SELECT order_num FROM {$table_name} WHERE uid = '{$item[$i]}'";
			$order_num = $mysql->get_one($sql);

			$sql = "DELETE FROM mallRN_order_sales WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			$sql	= "SELECT * FROM {$table_name2} WHERE order_num = '{$order_num}'";
			$mysql->query($sql);
			
			whilE($row = $mysql->fetch_array()) {

				if($row['status'] > 6) continue;

				if($row['status'] != 0) {
					$sql = "UPDATE {$table_name2} SET status = '0', status_date = '{$signdate}' WHERE uid = '{$row['uid']}'";
					$mysql->query2($sql);
					
					$sql = "INSERT INTO mallRN_order_log SET
								order_num	= '{$order_num}',
								og_uid		= '{$row['uid']}',
								id			= '{$my_id}',
								prev_status	= '{$row['status']}',
								status		= '0',
								signdate	= '{$signdate}'
							";
					$mysql->query2($sql);
				}				
			}

			$sql = "UPDATE {$table_name} SET sales_issued = 0, status_date = '0', pay_status = 'A' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);
		}
		alertMsg("{$i}건의 주문 상품이 입금대기중 상태로 변경 되었습니다!", $link_page);

	break;

	case "secStatus1" :

		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$sql = "SELECT order_num FROM {$table_name} WHERE uid = '{$item[$i]}'";
			$order_num = $mysql->get_one($sql);
			
			orderStatus1($order_num, $my_id);			
		}
		alertMsg("{$i}건의 주문 상품이 결제완료 상태로 변경 되었습니다!", $link_page);

	break;

	case "secStatus9" :

		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $i2 = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$sql		= "SELECT order_num, pay_status FROM {$table_name} WHERE uid = '{$item[$i]}'";
			$data		= $mysql->one_row($sql);
			$order_num	= $data['order_num'];

			if($data['pay_status'] == 'C') continue;			 
			
			orderStatus9($order_num, $my_id);	

			$i2 ++;
		}
		alertMsg("{$i2}건의 주문이 취소 되었습니다!", $link_page);

	break;

	case "status95" :   //결제완료시 주문취소

		$sql		= "UPDATE mallRN_order_goods SET status = '9' WHERE order_num = '{$order_num}'"; 
		$mysql->query($sql);

		orderStatus95($order_num, $my_id, 1);

		orderStatusX5Sales($order_num);

		$sql		= "SELECT pay_type FROM mallRN_order_info WHERE order_num = '{$order_num}'";
		$pay_type	= $mysql->get_one($sql);
		if($pay_type == 'C' || $pay_type == 'R' || $pay_type == 'H') {

			$pay_type_array	= array('C' => '카드결제', 'R' => '실시간계좌이체', 'H' => '휴대폰결제');
			
			$sql			= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
			$payment_cp		= stripslashes($mysql->get_one($sql)); 

			switch($payment_cp) {
				case "KCP" :
					$URL	= ABSOLUTE_PATH_SHOP."plugin/kcp/order_cancel.php?mode=cancel&order_num={$order_num}";
				break;
				case "NICEPAY" :
					$URL	= ABSOLUTE_PATH_SHOP."plugin/nicepay/order_cancel.php?mode=cancel&order_num={$order_num}";
				break;
				case "INICIS" :
					$URL	= ABSOLUTE_PATH_SHOP."plugin/inicis/order_cancel.php?mode=cancel&order_num={$order_num}";
				break;
			}
			$rtns	= implode("", socketPost($URL, 'POST', 1)); //동기 실행

			if(!preg_match("/\|\*\|SUCCESS/i", $rtns)) alertMsg("주문이 취소 되었으나 {$pay_type_array[$pay_type]}취소가 실패 되었습니다.<br />전자결제취소처리 연동로그기록을 확인 해 보시기 바랍니다.", $link_page);
		}

		alertMsg("주문이 취소 되었습니다.", $link_page);

	break;

	case "statusChange" : case "statusChange2" :
		
		$item				= checkPostVar('item');
		$status				= checkPostVar('status');
		$delivery			= checkPostVar('delivery');
		$delivery_number	= checkPostVar('delivery_number');
		$add_query			= "";
				
		if(!$item || !$status) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		if($status == 3) {			
			$sms_send_array	= array();
			$sms_cnt_array	= array();
		}
		
		if($delivery && $delivery_number) {
			$add_query = "delivery_info = '{$delivery}|{$delivery_number}'";
		}

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$sql	= "SELECT * FROM {$table_name2} WHERE uid = '{$item[$i]}'";
			$data	= $mysql->one_row($sql);
			if($data && $data['status'] < 5) {
				$order_num	= $data['order_num'];

				if($data['status'] != $status) {
					$sql = "UPDATE {$table_name2} SET status = '{$status}', status_date = '{$signdate}' WHERE uid = '{$data['uid']}'";
					$mysql->query($sql);
					
					$sql = "INSERT INTO mallRN_order_log SET
								order_num	= '{$order_num}',
								og_uid		= '{$data['uid']}',
								id			= '{$my_id}',
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
									socketPost(ABSOLUTE_PATH_SHOP."plugin/kcp/deli_ok.php?order_num={$order_num}");
								break;
								case "NICEPAY" :
									socketPost(ABSOLUTE_PATH_SHOP."plugin/nicepay/deli_ok.php?order_num={$order_num}");
								break;
								case "INICIS" :
									socketPost(ABSOLUTE_PATH_SHOP."plugin/inicis/deli_ok.php?order_num={$order_num}");
								break;
							}
						}
					}
				}

				if($status == 4 && $data['mileage']) {
					$mileage = $data['mileage'] * $data['qty'];
					
					$sql		= "SELECT id FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
					$id			= $mysql->get_one($sql);

					$content	= addslashes($data['g_name'])." 상품구매 마일리지 적립";

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
	case "status1" :
		
		orderStatus1($order_num, $my_id);
		alertMsg("결제완료 처리가 되었습니다!", $re_page);

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
				$sql	= "SELECT * FROM {$table_name2} WHERE order_num = '{$order_num}' && uid = '{$uid}' && vendor_delivery = ''";
				$data	= $mysql->one_row($sql);
				if($data) {
					if($data['status'] == 1 || $data['status'] == 2) {
						$sql = "UPDATE {$table_name2} SET status = '3', status_date = '{$signdate}', delivery_info = '{$delivery}|{$delivery_number}' WHERE uid = '{$data['uid']}'";
						$mysql->query($sql);
						
						$sql = "INSERT INTO mallRN_order_log SET
									order_num	= '{$order_num}',
									og_uid		= '{$data['uid']}',
									id			= '{$my_id}',
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
									socketPost(ABSOLUTE_PATH_SHOP."plugin/kcp/deli_ok.php?order_num={$order_num}");
								break;
								case "NICEPAY" :
									socketPost(ABSOLUTE_PATH_SHOP."plugin/nicepay/deli_ok.php?order_num={$order_num}");
								break;
								case "INICIS" :
									socketPost(ABSOLUTE_PATH_SHOP."plugin/inicis/deli_ok.php?order_num={$order_num}");
								break;
							}
						}						
					}
				}				

				$cnt ++;
			}
		}

		status3Sms($sms_send_array, $sms_cnt_array);

		alertMsg("{$cnt}건의 주문상품의 배송정보가 등록 되었습니다!", "headquarters_order_list.php?status=3");

	break;

	case "delete" :
		
		$sql = "SELECT * FROM {$table_name} WHERE order_num = '{$order_num}'";
		if(!$data = $mysql->one_row($sql)) logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");

		if($info['pay_total'] - $info['cancel_total'] - $info['refund_total'] != 0) logMsg("모든 상품이 취소완료일 경우에만 삭제가 가능 합니다.");

		$sql = "DELETE FROM {$table_name} WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		$sql = "DELETE FROM {$table_name2} WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_order_delivery WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_order_status_change WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_order_cancel_cp_log WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		alertMsg("주문이 삭제 되었습니다!", $link_page);

	break;

	case "memo" :
		
		$memo = checkPostVar('memo');
	
		$sql = "UPDATE {$table_name} SET memo = '{$memo}' WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		logMsg("메모가 저장 되었습니다.","success");

	break;

	case "address" :

		$postcode = checkPostVar('rece_postcode');
		$address1 = checkPostVar('rece_address1');
		$address2 = checkPostVar('rece_address2');
	
		$sql = "UPDATE {$table_name} SET postcode = '{$postcode}', address1 = '{$address1}', address2 = '{$address2}' WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		logMsg("배송지가 수정 되었습니다.","success");

	break;

	case "status_cancel" : 
		
		$uid			= checkPostVar('uid');

		$sql			= "SELECT og_uid, status, status2 FROM mallRN_order_status_change WHERE order_num = '{$order_num}' && uid = '{$uid}'";
		if(!$data	= $mysql->one_row($sql)) {
			logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");
		}
		
		$status_array	= array("7" => "교환", "8" => "반품", "9" => "취소"); 
		$ttl			= $status_array[$data['status']];
		
		$sql			= "UPDATE mallRN_order_status_change SET status2 = 9, status_date = '{$signdate}', manager = '{$my_name}' WHERE order_num = '{$order_num}' && uid = '{$uid}'";
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
					id				= '{$my_id}',
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
					id				= '{$my_id}',
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

	case "status_change_85" : case "status_change_95" :

		$uid		= checkPostVar('uid');
		$manager	= checkPostVar('manager');
		$refund		= checkPostVar('refund', 0);
		$delivery	= str_replace(",", "", checkPostVar('delivery', 0));
		$mileage	= checkPostVar('mileage', 0);
		$coupon		= checkPostVar('coupon', 0);
		$refund_fee	= checkPostVar('refund_fee', 0);
		$delivery2	= checkPostVar('delivery2', 0);
		$where		= "";

		if(!$uid) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql		= "SELECT * FROM mallRN_order_status_change WHERE uid = '{$uid}'";
		if(!$data = $mysql->one_row($sql)) logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");

		if($data['status2'] == 5) logMsg("이미 환불처리가 완료되었습니다.");

		$order_num	= $data['order_num'];

		$sql	= "SELECT count(*) FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		if($mysql->get_one($sql) == 0) logMsg("해당주문이 삭제되었거나 존재하지 않습니다.");

		$sql	= "UPDATE mallRN_order_status_change SET status2 = 5, status_date = '{$signdate}', manager = '{$manager}', refund = '{$refund}', delivery = '{$delivery}', delivery2 = '{$delivery2}', mileage = '{$mileage}', coupon = '{$coupon}', refund_fee = '{$refund_fee}' WHERE uid = '{$uid}'";
		$mysql->query($sql);

		if($data['og_uid']) {
			orderStatus95_partial($order_num, $my_id, $data['og_uid']);
			$where = " && og_uid = '{$data['og_uid']}'";

			if($delivery2) orderGoodsDelivery1Change($delivery2, $order_num, $data['og_uid']);
		}
		else {
			orderStatus95($order_num, $my_id, 1);
		}

		orderStatusX5Sales($order_num, $where);

		$sql		= "SELECT pay_type, status_date FROM mallRN_order_info WHERE order_num = '{$order_num}'";
		$info		= $mysql->one_row($sql);
		$pay_type	= $info['pay_type'];
		
		if($pay_type == 'R') {
			if($mode == 'status_change_95') {
				if(!(date("Y-m-d", $info['status_date']) == date("Y-m-d") || date("Y-m-d", $info['status_date'] + 86400) == date("Y-m-d"))) $pay_type = '';
			}
			else $pay_type = '';
		}
		else if($pay_type == 'H') {
			if($mode == 'status_change_95') {
				if(date("Y-m", $info['status_date']) != date("Y-m")) $pay_type = '';
			}
			else $pay_type = '';
		}
		
		if($pay_type == 'C' || $pay_type == 'R' || $pay_type == 'H') {

			$refund_total	= (int) $refund - (int) $refund_fee - (int) $delivery2;

			if($refund_total > 0) {
				
				orderStatusX5SalesCP($order_num, $pay_type, $refund_total, $info['status_date']);
				
				$pay_type_array	= array('C' => '카드결제', 'R' => '실시간계좌이체', 'H' => '휴대폰결제');
				
				$sql			= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
				$payment_cp		= stripslashes($mysql->get_one($sql)); 

				switch($payment_cp) {
					case "KCP" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/kcp/order_cancel.php?mode=partial_cancel&order_num={$order_num}&og_uid={$data['og_uid']}&refund={$refund_total}";
					break;
					case "NICEPAY" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/nicepay/order_cancel.php?mode=partial_cancel&order_num={$order_num}&og_uid={$data['og_uid']}&refund={$refund_total}";
					break;
					case "INICIS" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/inicis/order_cancel.php?mode=partial_cancel&order_num={$order_num}&og_uid={$data['og_uid']}&refund={$refund_total}";
					break;
				}
				$rtns	= implode("", socketPost($URL, 'POST', 1)); //동기 실행

				if(!preg_match("/\|\*\|SUCCESS/i", $rtns)) iframeViewMsg("환불처리가 완료 되었으나 {$pay_type_array[$pay_type]}취소가 실패 되었습니다.<br />전자결제취소처리 연동로그기록을 확인 해 보시기 바랍니다.");
			}
		}
				
		iframeViewMsg("환불처리가 완료 되었습니다.");

	break;

	case "status_change2_95" :

		$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		if(!$info = $mysql->one_row($sql)) logMsg("해당주문이 삭제되었거나 존재하지 않습니다.");

		$_POST['id']		= $info['id'];		
		$_POST['name']		= $info['name'];
		$_POST['vendor']	= "";
		$_POST['status']	= "9";
		$_POST['status2']	= "5";
		$_POST['og_uid']	= checkPostVar('og_uid', 0);
		$_POST['bank_info'] = checkPostVar('bank_name')."|".checkPostVar('bank_num')."|".checkPostVar('bank_owner');
		$_POST['signdate']	= time();		
				
		$item_array		= array('id', 'name', 'vendor', 'order_num', 'og_uid', 'reason', 'bank_info', 'status', 'status2', 'signdate');

		$sql = "INSERT INTO mallRN_order_status_change SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		$uid			= $mysql->InsertNo();

		$manager	= checkPostVar('manager');
		$refund		= checkPostVar('refund', 0);
		$delivery	= str_replace(",", "", checkPostVar('delivery', 0));
		$mileage	= checkPostVar('mileage', 0);
		$coupon		= checkPostVar('coupon', 0);
		$refund_fee	= checkPostVar('refund_fee', 0);
		$delivery2	= checkPostVar('delivery2', 0);
		
		$sql		= "SELECT * FROM mallRN_order_status_change WHERE uid = '{$uid}'";
		if(!$data = $mysql->one_row($sql)) logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");

		$sql		= "UPDATE mallRN_order_status_change SET status2 = 5, status_date = '{$signdate}', manager = '{$manager}', refund = '{$refund}', delivery = '{$delivery}', delivery2 = '{$delivery2}', mileage = '{$mileage}', coupon = '{$coupon}', refund_fee = '{$refund_fee}' WHERE uid = '{$uid}'";
		$mysql->query($sql);
		
		$where = $where2 = "";
		if($data['og_uid']) {
			$where		= " && og_uid = '{$data['og_uid']}'";
			$where2		= " && uid = '{$data['og_uid']}'";
			
			$sql		= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' {$where2}"; 
			$ginfo		= $mysql->one_row($sql);
			$def_qty	= $ginfo['qty'];

			$proc_qty	= checkPostVar('proc_qty', 0);
			if(!$proc_qty) $proc_qty = $def_qty;

			if($def_qty != $proc_qty) {  //부분수량 취소일 경우
				orderGoodsQtyChange($order_num, $ginfo, $proc_qty, $def_qty, $data['og_uid']);
			}		
		}
		
		$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 {$where2}";
		$mysql->query($sql);

		whilE($row = $mysql->fetch_array()) {
			$sql = "UPDATE mallRN_order_goods SET status = '9' WHERE order_num = '{$order_num}' && reals = 1 && uid = '{$row['uid']}'";
			$mysql->query2($sql);
			
			if($row['delivery_type'] == 4 && $row['delivery_price'] > 0)  {
				orderGoodsDelivery4Change($order_num, $row);
			}			
		}		

		if($data['og_uid']) {
			orderStatus95_partial($order_num, $my_id, $data['og_uid']);

			if($delivery2) orderGoodsDelivery1Change($delivery2, $order_num, $data['og_uid']);
		}
		else {
			orderStatus95($order_num, $my_id, 1);
		}	

		orderStatusX5Sales($order_num, $where);
		
		$sql		= "SELECT pay_type FROM mallRN_order_info WHERE order_num = '{$order_num}'";
		$pay_type	= $mysql->get_one($sql);
		
		if($pay_type == 'C') {

			$refund_total	= (int) $refund - (int) $refund_fee - (int) $delivery2;

			if($refund_total > 0) {

				orderStatusX5SalesCP($order_num, $pay_type, $refund_total, $info['status_date']);

				$sql			= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
				$payment_cp		= stripslashes($mysql->get_one($sql)); 

				switch($payment_cp) {
					case "KCP" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/kcp/order_cancel.php?mode=partial_cancel&order_num={$order_num}&og_uid={$data['og_uid']}&refund={$refund_total}";
					break;
					case "NICEPAY" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/nicepay/order_cancel.php?mode=partial_cancel&order_num={$order_num}&og_uid={$data['og_uid']}&refund={$refund_total}";
					break;
					case "INICIS" :
						$URL	= ABSOLUTE_PATH_SHOP."plugin/inicis/order_cancel.php?mode=partial_cancel&order_num={$order_num}&og_uid={$data['og_uid']}&refund={$refund_total}";
					break;
				}
				$rtns	= implode("", socketPost($URL, 'POST', 1)); //동기 실행

				if(!preg_match("/\|\*\|SUCCESS/i", $rtns)) iframeViewMsg("주문취소처리가 완료 되었으나 카드결제취소가 실패 되었습니다.<br />전자결제취소처리 연동로그기록을 확인 해 보시기 바랍니다.");
			}
		}

		iframeViewMsg("주문취소가 완료 되었습니다.");

	break;

	case "status_change2_71" : case "status_change2_81" :

		$sql	= "SELECT * FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		if(!$info = $mysql->one_row($sql)) logMsg("해당주문이 삭제되었거나 존재하지 않습니다.");

		$_POST['id']		= $info['id'];		
		$_POST['name']		= $info['name'];
		$_POST['status']	= checkPostVar('status');
		$_POST['status2']	= "1";
		$_POST['og_uid']	= checkPostVar('og_uid', 0);

		$sql		= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 && uid = '{$_POST['og_uid']}'";
		$ginfo		= $mysql->one_row($sql);

		$_POST['vendor']	= $ginfo['vendor'];

		if($_POST['status'] == '7') $_POST['bank_info'] = "";
		else {
			if($info['pay_type'] == 'B' || $info['pay_type'] == 'V') {
				$_POST['bank_info'] = checkPostVar('bank_name')."|".checkPostVar('bank_num')."|".checkPostVar('bank_owner');
				if($_POST['bank_info'] == '||') logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
			}
			else $_POST['bank_info'] = "";
		}

		$_POST['signdate']	= time();		
				
		$item_array		= array('id', 'name', 'vendor', 'order_num', 'og_uid', 'reason', 'bank_info', 'message', 'status', 'status2', 'signdate');

		$sql = "INSERT INTO mallRN_order_status_change SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
			
		$def_qty	= $ginfo['qty'];

		$proc_qty	= checkPostVar('proc_qty', 0);
		if(!$proc_qty) $proc_qty = $def_qty;

		if($def_qty != $proc_qty) {  //부분수량 취소일 경우							
			orderGoodsQtyChange($order_num, $ginfo, $proc_qty, $def_qty, $_POST['og_uid']);			
		}
		
		$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 && uid = '{$_POST['og_uid']}'";
		$mysql->query($sql);

		whilE($row = $mysql->fetch_array()) {
			$sql = "UPDATE mallRN_order_goods SET status = '{$_POST['status']}', status2 = 1, status_date = '{$_POST['signdate']}' WHERE uid = '{$row['uid']}'";
			$mysql->query2($sql);
			
			if($row['delivery_type'] == 4 && $row['delivery_price'] > 0 && $_POST['status'] == 8)  {
				orderGoodsDelivery4Change($order_num, $row);
			}

			$sql = "INSERT INTO mallRN_order_log SET
						order_num	= '{$order_num}',
						og_uid		= '{$row['uid']}',
						id			= '{$my_id}',
						prev_status	= '{$row['status']}',
						status		= '{$_POST['status']}',
						status2		= '1',
						signdate	= '{$_POST['signdate']}'
					";
			$mysql->query2($sql);
		}
		
		$status_array	= array("7" => "교환", "8" => "반품");
		$TTL			= $status_array[$_POST['status']];

		iframeViewMsg("{$TTL}요청이 완료 되었습니다.");

	break;

	default:
		logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

}
		
?>
