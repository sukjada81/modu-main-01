<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

$mode			= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$order_num		= checkPostVar('order_num');

if(!$order_num) {
	logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
}

if($my_id) {
	$sql	= "SELECT count(*) FROM mallRN_order_info WHERE order_num = '{$order_num}' && id = '{$my_id}' && reals = 1";
}
else {
	if(!isset($_COOKIE['guestOrder1'])) alert("먼저 로그인을 하시기 바랍니다.", "back");
	
	$guestOrder1	= base64_decode($_COOKIE['guestOrder1']);
	$tmps			= explode("|", $guestOrder1);
	$cell			= $tmps[0];
	$name			= $tmps[1];
	$passwd			= previlDecode($_COOKIE['guestOrder2']);

	$sql = "SELECT count(*) FROM mallRN_order_info WHERE name = '{$name}' && cell = '{$cell}' && passwd = '{$passwd}' && order_num = '{$order_num}' && reals = 1 ORDER BY uid DESC";
	if($mysql->get_one($sql) == 0) {
		alert("일치하는 정보가 존재 하지 않습니다.", "back");
	}	
	$sql	= "SELECT count(*) FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
}
if($mysql->get_one($sql) == 0) alert('해당주문이 삭제되었거나 존재하지 않습니다.', 'back');

######################## 파라미터 링크 추가  #########################
$addstring			= "";
$search_variable	=  array('status', 's_date', 'e_date', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}
######################## 파라미터 링크 추가  #########################

if(!$my_id && isset($_COOKIE['guestOrder1']))	$link_page	= "../{$Main}?channel=order_list_guest&{$addstring}";
else											$link_page	= "../{$Main}?channel=order_list&{$addstring}";
$re_page	= "../{$Main}?channel=order_detail&order_num={$order_num}{$addstring}";
$signdate	= time();

switch($mode) {
	case "recipiency" :   //수취확인
		
		$uid		= checkPostVar('uid');
		if(!$uid) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		
		orderStatus4($order_num, $uid, $my_id, 1);

		alertMsg("수취확인 완료 되었습니다.", $link_page);

	break;

	case "confirmation" :   //구매확정
		
		$uid		= checkPostVar('uid');
		if(!$uid) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		
		orderStatus5($order_num, $uid, $my_id, 1);

		$mypage		= checkPostVar('mypage');
		if($mypage == 1) alertMsg("구매확정이 완료 되었습니다.", "../{$Main}?channel=mypage");
		else alertMsg("구매확정이 완료 되었습니다.", $link_page);

	break;

	case "status9s" :   //선택주문취소

		$uid = checkPostVar('uid');
		if(!$uid)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && uid = '{$uid}'";
		if($data = $mysql->one_row($sql)) {
			if($data['status'] == 0) {
				$sql = "UPDATE mallRN_order_goods SET status = '9', status2 = 5, status_date = '{$signdate}' WHERE uid = '{$data['uid']}'";
				$mysql->query3($sql);

				if($data['delivery_type'] == 4 && $data['delivery_price'] > 0) {
					orderGoodsDelivery4Change($order_num, $data);					
				}

				if($my_id)	$id = $my_id;
				else		$id	= "주문자";
				
				$sql = "INSERT INTO mallRN_order_log SET
							order_num	= '{$order_num}',
							og_uid		= '{$data['uid']}',
							id			= '{$id}',
							prev_status	= '{$data['status']}',
							status		= '9',
							status2		= '5',
							signdate	= '{$signdate}'
						";
				$mysql->query3($sql);
				
				goodsOrderQtyCancel($data['uid']);

				if($data['use_coupon'] && $data['coupon_uid']) {
					$sql = "UPDATE mallRN_coupon SET status = 0, usedate = '0' WHERE id = '{$my_id}' && uid = '{$data['coupon_uid']}'";
					$mysql->query3($sql);
				}				
			}
		}

		orderReset($order_num);

		alertMsg("상품의 주문이 취소 되었습니다.", $link_page);

	break;

	case "status9" :   //주문취소

		if($my_id)	$id = $my_id;
		else		$id	= "주문자";

		orderStatus9($order_num, $id, 1);

		alertMsg("주문이 취소 되었습니다.", $link_page);

	break;

	case "status95" :   //결제완료시 주문취소

		if($my_id)	$id = $my_id;
		else		$id	= "주문자";

		$sql		= "UPDATE mallRN_order_goods SET status = '9' WHERE order_num = '{$order_num}'"; 
		$mysql->query($sql);

		orderStatus95($order_num, $id, 1);

		orderStatusX5Sales($order_num);

		$sql		= "SELECT pay_type FROM mallRN_order_info WHERE order_num = '{$order_num}'";
		$pay_type	= $mysql->get_one($sql);
		if($pay_type == 'C' || $pay_type == 'V') {

			$pay_type_array	= array('C' => '카드결제', 'V' => '실시간계좌이체', 'H' => '휴대폰결제');
			
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

			if(!preg_match("/\|\*\|SUCCESS/i", $rtns)) alertMsg("주문이 취소 되었으나 {$pay_type_array[$pay_type]}취소가 실패 되었습니다.<br />관리자에게 문의 바랍니다.", $link_page);
		}
		
		alertMsg("주문이 취소 되었습니다.", $link_page);

	break;

	case "statusX1" :  //주문취소요청
		
		$status	= checkPostVar('status');
		$uid	= checkPostVar('uid');

		if($status != 9 && !$uid) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

		$sql	= "SELECT pay_status, pay_type, cancel_total, refund_total, name FROM mallRN_order_info WHERE order_num = '{$order_num}' && reals = 1";
		$data	= $mysql->one_row($sql);
		
		$status_array	= array("7" => "교환", "8" => "반품", "9" => "취소");
		$TTL			= $status_array[$status];
		$where			= "";
		if(!$uid) {
			if($data['pay_status'] != 'C' || $data['cancel_total'] != 0 || $data['refund_total'] != 0) {
				logMsg("주문취소 요청 불가능한 상태 입니다.");
			}	
			$_POST['vendor']	= "";
			$TTL2				= "주문취소";			
		}
		else {
			$sql	= "SELECT vendor FROM mallRN_order_goods WHERE uid = '{$uid}' && reals = 1";
			$_POST['vendor']	= $mysql->get_one($sql);

			$where				= " && uid = '{$uid}'";
			$TTL2				= "상품 {$TTL}";			
		}
		
		$_POST['reason']	= checkPostVar('reason');
		if(!$_POST['reason']) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

		if($status == 7) $_POST['bank_info'] = "";
		else {
			if($data['pay_type'] == 'B' || $data['pay_type'] == 'V') {
				$_POST['bank_info'] = checkPostVar('bank_name')."|".checkPostVar('bank_num')."|".checkPostVar('bank_owner');
				if($_POST['bank_info'] == '||') logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
			}
			else $_POST['bank_info'] = "";
		}
				
		if(!$my_id) {
			$passwd		= checkPostVar('passwd');
			if(!$passwd) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");			
			$passwd		= md5($passwd);
		
			$sql = "SELECT count(*) FROM mallRN_order_info WHERE order_num = '{$order_num}' && passwd = '{$passwd}' && reals = 1 ORDER BY uid DESC";
			if($mysql->get_one($sql) == 0) logMsg("일치하는 주문내역이 없습니다.");
		}
		
		$_POST['og_uid']	= $uid;
		$_POST['id']		= $my_id;
		$_POST['name']		= $data['name'];
		$_POST['status']	= $status;
		$_POST['status2']	= 1;
		$_POST['signdate']	= $signdate;
				
		$item_array		= array('id', 'name', 'vendor', 'order_num', 'og_uid', 'reason', 'bank_info', 'message', 'status', 'status2', 'signdate');

		$sql = "INSERT INTO mallRN_order_status_change SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);

		$sql	= "SELECT * FROM mallRN_order_goods WHERE order_num = '{$order_num}' && reals = 1 {$where}";
		$mysql->query($sql);

		whilE($row = $mysql->fetch_array()) {
			$sql = "UPDATE mallRN_order_goods SET status = '{$status}', status2 = 1, status_date = '{$signdate}' WHERE uid = '{$row['uid']}'";
			$mysql->query2($sql);
			
			if($where) {
				if($row['delivery_type'] == 4 && $row['delivery_price'] > 0 && ($status == 9 || $status == 8))  {
					orderGoodsDelivery4Change($order_num, $row);
				}
			}

			if($my_id)	$id = $my_id;
			else		$id	= "주문자";
			
			$sql = "INSERT INTO mallRN_order_log SET
						order_num	= '{$order_num}',
						og_uid		= '{$row['uid']}',
						id			= '{$id}',
						prev_status	= '{$row['status']}',
						status		= '{$status}',
						status2		= '1',
						signdate	= '{$signdate}'
					";
			$mysql->query2($sql);
		}		

		

		alertMsg("{$TTL2} 요청이 접수 되었습니다.", $link_page);

	break;

	case "status_cancel" : // 요청 취소
		
		$uid			= checkPostVar('uid');

		$sql			= "SELECT uid, og_uid, status, status2 FROM mallRN_order_status_change WHERE order_num = '{$order_num}' && og_uid = '{$uid}' && status2 = 1";
		if(!$data	= $mysql->one_row($sql)) {
			logMsg("해당 내역이 삭제되었거나 존재하지 않습니다.");
		}
		
		$status_array	= array("7" => "교환", "8" => "반품", "9" => "취소"); 
		$ttl			= $status_array[$data['status']];
		
		$sql			= "DELETE FROM mallRN_order_status_change WHERE order_num = '{$order_num}' && uid = '{$data['uid']}'";
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

		alertMsg("{$ttl}철회 처리가 되었습니다.", $link_page);

	break;

	default : logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

}
		
?>
