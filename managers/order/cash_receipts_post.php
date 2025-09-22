<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode			= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

$table_name		= 'mallRN_order_cash_receipts';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$uid			= checkPostVar('uid');
if(!$uid && $mode != 'write') logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

######################## 파라미터 링크 추가  #########################
$addstring			= "";
$search_variable	=  array('field', 'keyword', 'date_type', 's_date', 'e_date', 'status', 'cash_type', 'pay_type', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}
######################## 파라미터 링크 추가  #########################

$link_page		= "cash_receipts_list.php?{$addstring}";
$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$signdate		= time();

$sql			= "SELECT payment_cp FROM mallRN_configuration WHERE uid = 1";
$payment_cp		= stripslashes($mysql->get_one($sql)); 

switch($mode) {
	case "write" :  

		if(!$_POST['name'] || !$_POST['price'] || !$_POST['goods_name'] || !$_POST['auth_number']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$item_array				= array('order_num', 'id', 'name', 'cell', 'email', 'price', 'goods_name', 'pay_type', 'tax_type', 'cash_type', 'auth_number', 'signdate');

		if(function_exists('mt_rand'))	$order_num = mt_rand(10000,99999);
		else							$order_num = rand(1,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
		$_POST['order_num']	= "c".date("ymd-Hi")."_".$order_num;

		$sql					= "SELECT cash_receipts_type FROM mallRN_configuration WHERE uid = 1";
		$shop_config			= $mysql->one_row($sql);	

		$_POST['tax_type']		= 0;
		if($shop_config['cash_receipts_type'] == 1) $_POST['tax_type']	= 1;

		$_POST['signdate']	= time();
		$_POST['cell']		= str_replace("-" ,"", $_POST['cell']);

		######################## 현금영수증 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);		
		######################## 현금영수증 등록  #########################

		alertMsg("현금영수증 개별등록이 완료 되었습니다.", $link_page);		

    break;	

	case "cancel" :
		$sql		= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		$data		= $mysql->one_row($sql);
		if(!$data) logMsg("현금영수증 발급 정보가 없거나 삭제 되었습니다.");

		if($data['status'] != '3') logMsg("발급완료상태일 때만 발급취소가 가능 합니다.");

		switch($payment_cp) {
			case "KCP" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/kcp/cash_receipts.php?order_num={$data['order_num']}&mode=cancel";
			break;
			case "NICEPAY" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/nicepay/cash_receipts.php?order_num={$data['order_num']}&mode=cancel";
			break;
			case "INICIS" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/inicis/cash_receipts.php?order_num={$data['order_num']}&mode=cancel";
			break;
		}		
		$rtn		= implode("", socketPost($URL, 'POST', 1)); //동기 실행			
		if(!preg_match("/True/i", $rtn)) alertMsg("현금영수증 발급취소가 되지 않았습니다. 처리결과메세지를 확인 해보시기 바랍니다.", $link_page);
		
		alertMsg("현금영수증 발급취소가 처리 되었습니다.", $link_page);

	break;

	case "resend" :
		$sql		= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		$data		= $mysql->one_row($sql);
		if(!$data) logMsg("현금영수증 발급 정보가 없거나 삭제 되었습니다.");

		if($data['status'] == '0' || $data['status'] == '3') logMsg("재발급가능한 상태가 아닙니다.");
		
		switch($payment_cp) {
			case "KCP" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/kcp/cash_receipts.php?order_num={$data['order_num']}";
			break;
			case "NICEPAY" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/nicepay/cash_receipts.php?order_num={$data['order_num']}";
			break;
			case "INICIS" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/inicis/cash_receipts.php?order_num={$data['order_num']}";
			break;
		}		
		$rtn		= implode("", socketPost($URL, 'POST', 1)); //동기 실행
		if(!preg_match("/True/i", $rtn)) alertMsg("현금영수증 재발급이 되지 않았습니다. 처리결과메세지를 확인 해보시기 바랍니다.", $link_page);
		
		alertMsg("현금영수증이 재발급 되었습니다.", $link_page);

	break;

	case "reject" :
		$sql		= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		$data		= $mysql->one_row($sql);
		if(!$data) logMsg("현금영수증 발급 정보가 없거나 삭제 되었습니다.");

		if($data['status'] != '0') logMsg("발급거절가능한 상태가 아닙니다.");

		$sql		= "UPDATE {$table_name} SET status = 1, status_date = '{$signdate}' WHERE uid = '{$uid}'";
		$mysql->query($sql);
				
		alertMsg("현금영수증이 발급이 거절 되었습니다.", $link_page);

	break;

	case "delete" :
		$sql		= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		$data		= $mysql->one_row($sql);
		if(!$data) logMsg("현금영수증 발급 정보가 없거나 삭제 되었습니다.");

		if($data['status'] != '1' && $data['status'] != '4') logMsg("삭제가능한 상태가 아닙니다.");

		$sql		= "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
		$mysql->query($sql);
				
		alertMsg("현금영수증이 삭제 되었습니다.", $link_page);

	break;

	case "apply" :
		$sql		= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		$data		= $mysql->one_row($sql);
		if(!$data) logMsg("현금영수증 발급 정보가 없거나 삭제 되었습니다.");

		if($data['status'] != '0') logMsg("발급가능한 상태가 아닙니다.");
		
		switch($payment_cp) {
			case "KCP" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/kcp/cash_receipts.php?order_num={$data['order_num']}";
			break;
			case "NICEPAY" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/nicepay/cash_receipts.php?order_num={$data['order_num']}";
			break;
			case "INICIS" :
				$URL		= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."plugin/inicis/cash_receipts.php?order_num={$data['order_num']}";
			break;
		}			
		$rtn		= implode("", socketPost($URL, 'POST', 1)); //동기 실행
		if(!preg_match("/True/i", $rtn)) alertMsg("현금영수증 발급이 되지 않았습니다. 처리결과메세지를 확인 해보시기 바랍니다.", $link_page);
		
		alertMsg("현금영수증이 발급 되었습니다.", $link_page);

	break;

	default:
		logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

}
		
?>
