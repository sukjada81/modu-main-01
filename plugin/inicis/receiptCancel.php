<?php

include "site_conf_inc.php";       // 환경설정 파일 include

$signdate		= time();
$rtns			= "False";

//step1. 요청을 위한 파라미터 설정
$key			= $SHOP_KEY2;
$type			= $cancel_mode_type;
$paymethod		= $cancel_pay_type2;
$timestamp		= date("YmdHis");
$clientIp		= $_SERVER['REMOTE_ADDR'];
$mid			= $SHOP_ID;
$tid			= $cancel_tno;
$msg			= $cancel_msg;
$price			= $cancel_mod_mny;
$confirmPrice	= $cancel_rem_mny2;

if($mode == 'cancel') {

	// INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid
	$hashData = hash("sha512",(string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$tid); // hash 암호화

	//step2. key=value 로 post 요청
	$data = array(
		'type' => $type,
		'paymethod' => $paymethod,
		'timestamp' => $timestamp,
		'clientIp' => $clientIp,
		'mid' => $mid,
		'tid' => $tid,
		'msg' => $msg,
		'hashData'=> $hashData
	);
}
else {
	// INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid + price + confirmPrice
    $hashData = hash("sha512",(string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$tid.(string)$price.(string)$confirmPrice); // hash 암호화
	

    //step2. key=value 로 post 요청
    $data = array(
        'type' => $type,
        'paymethod' => $paymethod,
        'timestamp' => $timestamp,
        'clientIp' => $clientIp,
        'mid' => $mid,
        'tid' => $tid,
        'price' => $price,
		'confirmPrice' => $confirmPrice,
		'msg' => $msg,
        'hashData'=> $hashData
	);
}

$url = "https://iniapi.inicis.com/api/v1/refund";  

$ch = curl_init();                                                     
curl_setopt($ch, CURLOPT_URL, $url);                                   
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                        
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);                          
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));         
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                            
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));  
curl_setopt($ch, CURLOPT_POST, 1);                                      
 
$response = curl_exec($ch);
curl_close($ch);

echo $response;


$respArr = json_decode($response);
foreach ( $respArr as $key => $value ){
	$$key = $value;
}

if($resultCode == '00') {
	
	if(isset($prtcRemains))	$rem_mny = (int) $prtcRemains; // 취소요청후 잔액
	else					$rem_mny = 0;
	
	
	$sql			= "SELECT receipt_info FROM mallRN_order_cash_receipts WHERE order_num = '{$order_num}'";
	$receipt_info	= $mysql->get_one($sql);
	
	if($mode == 'partial_cancel')	$receipt_info	.= ", 부분취소금액 : {$prtcPrice}";
	else							$receipt_info	.= ", 취소승인번호 : {$cshrCancelNum}";	

	if($mode == 'partial_cancel') {
		$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$prtcDate} {$prtcTime}', receipt_cancel_no = '{$tid}', receipt_info = '{$receipt_info}', receipt_status = 'NTRW', receipt_error = '', status = '3', status_date = '{$signdate}', partial_price = partial_price +  {$prtcPrice} WHERE order_num = '{$order_num}'";
	}
	else {
		$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$cancelDate} {$cancelTime}', receipt_info = '{$receipt_info}', receipt_status = 'NTRW', receipt_error = '', status = '4', status_date = '{$signdate}' WHERE order_num = '{$order_num}'";
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

echo $rtns;
exit;

?>