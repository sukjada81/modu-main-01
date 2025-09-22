<?php

include "site_conf_inc.php";       // 환경설정 파일 include

$signdate		= time();
$rtns			= "False";

// API CALL foreach 예시
function jsonRespDump($resp){ 
	$respArr = json_decode($resp);
	foreach ( $respArr as $key => $value ){
		echo "$key=". $value."<br />";
	}
}

//Post api call
function reqPost(Array $data, $url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);					//connection timeout 15 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));	//POST data
	curl_setopt($ch, CURLOPT_POST, true);
	$response = curl_exec($ch);
	curl_close($ch);	 
	return $response;
}
/*
function reqPost(Array $data, $url){
	$requestData = stream_context_create(array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded;charset=utf-8"',
			'content' => http_build_query($data),
			'timeout' => 15
		)
	));
	
	$response = file_get_contents($url, FALSE, $requestData);
	return $response;
}
*/

$mid				= $SHOP_ID;
$moid				= $order_num2;
$cancelMsg			= "주문취소요청";
$tid				= $_POST['TID'];			
$cancelAmt			= $_POST['CancelAmt']; 
$partialCancelCode	= $_POST['PartialCancelCode'];
$ediDate			= date("YmdHis");
$signData			= bin2hex(hash('sha256', $mid . $cancelAmt . $ediDate . $SHOP_KEY, true));

try{
	$data = Array(
		'TID' => $tid,
		'MID' => $mid,
		'Moid' => $moid,
		'CancelAmt' => $cancelAmt,
		'CancelMsg' => iconv("UTF-8", "EUC-KR", $cancelMsg),
		'PartialCancelCode' => $partialCancelCode,
		'EdiDate' => $ediDate,
		'SignData' => $signData,
		'CharSet' => 'utf-8'
	);	

	
	$response = reqPost($data, "https://webapi.nicepay.co.kr/webapi/cancel_process.jsp"); //취소 API 호출
	//jsonRespDump($response);

	$respArr = json_decode($response);
	foreach ( $respArr as $key => $value ){
		$$key = $value;
	}

	if($ResultCode == '2001') {

		$CancelAmt		= (int) $CancelAmt;

		$sql			= "SELECT receipt_info FROM mallRN_order_cash_receipts WHERE order_num = '{$order_num}'";
		$receipt_info	= $mysql->get_one($sql);
		
		if($mode == 'partial_cancel')	$receipt_info	.= ", 부분취소승인번호 : {$CancelAuthCode}, 부분취소금액 : {$CancelAmt}";
		else							$receipt_info	.= ", 취소승인번호 : {$CancelAuthCode}";	

		if($mode == 'partial_cancel') {
			$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$CancelDate}{$CancelTime}', receipt_cancel_no = '{$TID}', receipt_info = '{$receipt_info}', receipt_status = 'NTRW', receipt_error = '', status = '3', status_date = '{$signdate}', partial_price = partial_price +  {$CancelAmt} WHERE order_num = '{$order_num}'";
		}
		else {
			$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$CancelDate}{$CancelTime}', receipt_no = '{$TID}', receipt_info = '{$receipt_info}', receipt_status = 'NTRW', receipt_error = '', status = '4', status_date = '{$signdate}' WHERE order_num = '{$order_num}'";
		}
		$mysql->query($sql);

		if(substr($order_num, 0, 1) != 'C' && $mode == 'cancel') {
			$sql = "UPDATE mallRN_order_info SET cash_issued = 0 WHERE order_num = '{$order_num}'";
			$mysql->query($sql);
		}

		$rtns = "True";

	}
	else {
		$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$ResultMsg}', status_date = '{$signdate}'  WHERE order_num = '{$order_num}' && status = '3'";
		$mysql->query($sql);
	}

}catch(Exception $e){
	$e->getMessage();
	$ResultCode = "9999";
	$ResultMsg = "통신실패";

	$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$ResultMsg}', status_date = '{$signdate}'  WHERE order_num = '{$order_num}' && status = '3'";
	$mysql->query($sql);
}

echo $rtns;
exit;

?>