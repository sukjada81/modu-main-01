<?php

include "site_conf_inc.php";       // 환경설정 파일 include

$signdate		= time();
$rtns			= "False";


//step1. 요청을 위한 파라미터 설정    
$key           = $SHOP_KEY2;
$iv            = $SHOP_ID2;
$type          = "Issue";
$paymethod     = "Receipt";
$timestamp     = date("YmdHis");
$clientIp      = $_SERVER['REMOTE_ADDR'];		
$mid           = $SHOP_ID;
$goodName      = $_POST['GoodsName'];
$crPrice       = $_POST['ReceiptAmt'];
$supPrice      = $_POST['ReceiptSupplyAmt'];
$tax           = $_POST['ReceiptVAT'];
$srcvPrice     = $_POST['ReceiptServiceAmt'];
$buyerName     = $_POST['name'];
$buyerEmail    = $_POST['email'];
$buyerTel      = $_POST['cell'];
$useOpt        = $_POST['ReceiptType'];
$regNum        = $_POST['ReceiptTypeNo'];

// AES 암호화 (regNum)
$enregNum = base64_encode(openssl_encrypt($regNum, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));

// SHA512 Hash 암호화
// INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid + crPrice + supPrice + srcvPrice + enregNum
$hashData = hash("sha512",(string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$crPrice.(string)$supPrice.(string)$srcvPrice.(string)$enregNum);


//step2. key=value 로 post 요청

$data = array(
	'type' => $type,
	'paymethod' => $paymethod,
	'timestamp' => $timestamp,
	'clientIp' => $clientIp,
	'mid' => $mid,
	'goodName' => $goodName,
	'crPrice' => $crPrice,
	'supPrice' => $supPrice,
	'tax' => $tax,
	'srcvPrice' => $srcvPrice,
	'buyerName' => $buyerName,
	'buyerEmail' => $buyerEmail,
	'buyerTel' => $buyerTel,
	'regNum' => $enregNum,
	'useOpt' => $useOpt,
	'hashData'=> $hashData
);


$url = "https://iniapi.inicis.com/api/v1/receipt";  

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

$respArr = json_decode($response);
foreach ( $respArr as $key => $value ){
	$$key = $value;
}

if($resultCode == '00') {
	$bSucc			= ""; 
	$receipt_info	= "승인번호 : {$authNo}";	

	$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$authDate} {$authTime}', receipt_no = '{$tid}', receipt_info = '{$receipt_info}', receipt_status = 'NTRW', receipt_error = '', status = '3', status_date = '{$signdate}' WHERE order_num = '{$order_num}'";
	$mysql->query($sql);
	
	if(substr($order_num, 0, 1) != 'C') {
		$sql = "UPDATE mallRN_order_info SET cash_issued = 1 WHERE order_num = '{$order_num}'";
		$mysql->query($sql);
	}
	$rtns = "True";
}
else {
	$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$resultMsg}', status_date = '{$signdate}'  WHERE order_num = '{$order_num}' && status = '0'";
	$mysql->query($sql);
}

echo $rtns;
exit;

?>