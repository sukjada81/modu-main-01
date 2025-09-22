<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
header("Content-Type: text/html; charset=euc-kr");

define('DEFAULT_PATH',	'../../');
define('PATH_LIB',			'lib');
define('PATH_INCLUDE',		'include');

include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/lib.Shop.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');  

$mysql = new mysqlClass();

if($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) {
	echo "False";
	exit;
}

include "site_conf_inc.php";       // 환경설정 파일 include

$order_num = checkGetVar('order_num');
if(!$order_num) {
	echo "False";
	exit;
}

$sql	= "SELECT * FROM mallRN_order_info WHERE escrow = '1' && order_num='{$order_num}'";
$data	= $mysql->one_row($sql);

if(!$data['pay_number']) {
	echo "False";
	exit;
}

$sql	= "SELECT count(*) FROM mallRN_order_goods WHERE order_num = '{$order_num}' && (status = 1 || status = 2)";
if($mysql->get_one($sql) > 0) {
	echo "False";
	exit;
}

$sql	= "SELECT delivery_info FROM mallRN_order_goods WHERE order_num = '{$order_num}' ORDER BY status_date DESC LIMIT 1";
$data2	= $mysql->one_row($sql);

if($data2['delivedry_info']) {
	$goods_delivery		= explode("|", $data2['delivery_info']);

	$delivery_info		= getDeliveryInfo($goods_delivery[0]);
	$DELI_NAME			= $delivery_info[0];
	$DELI_NUM			= $goods_delivery[1];
}
else {
	echo "False";
	exit;
}

include "site_conf_inc.php";       // 환경설정 파일 include

$mid = $SHOP_ID;								//상점 ID
$tid = $data['pay_number'];						// 거래 번호
$reqType = "03";								// 요청타입(03:배송등록)
$deliveryCoNm = $DELI_NAME;						// 배송업체명
$buyerAddr = $data['address1']." ".$data['address2'];				// 배송지 주소
$invoiceNum = $DELI_NUM;						// 송장번호
$registerName = "관리자";							// 등록자이름
$confirmMail = 2;								// 구매결정 메일발송 여부
$charSet = 'utf-8';								// 응답파라미터 인코딩 방식
$escrowRequestURL = "https://webapi.nicepay.co.kr/webapi/escrow_process.jsp"; 	//에스크로 요청 URL


//step1. 요청을 위한 파라미터 설정

    $key         		= $SHOP_KEY2;
    $type        		= "Dlv";													//"Dlv" 고정	
    $mid         		= $SHOP_ID;							
    $clientIp    		= $_SERVER['REMOTE_ADDR'];									//상점 임의 설정 가능 (상점측 서버 구분을 위함)
    $timestamp   		= date("YmdHis");
    $tid         		= $data['pay_number'];										//에스크로 결제 승인TID	
	$oid		 		= $order_num;
	$price		 		= $data['pay_total'];
	$report		 		= "I";														//에스크로 등록형태 ["I":등록, "U":변경]	
	$invoice			= $DELI_NUM;											//운송장번호
	$registName			= "관리자";							
	$exCode		 		= "9999";													//택배사코드 참고(https://manual.inicis.com/code/#gls)
	$exName		 		= $DELI_NAME;							
	$charge		 		= "SH";														//배송비 지급형태 ("SH":판매자부담, "BH":구매자부담)	
	$invoiceDay		 	= date("Y-m-d H:i:s");									//배송등록 확인일자 (String 으로 timestamp 사용 가능)
	$sendName		 	= $data['name'];
	$sendTel		 	= $data['cell'];
	$sendPost		 	= $data['postcode'];
	$sendAddr1		 	= $data['address1'];
	$recvName		 	= $data['name2'];
	$recvTel		 	= $data['cell2'];
	$recvPost		 	= $data['postcode'];
	$recvAddr		 	= $data['address1'];

	// hash => INIAPIKey + type + timestamp + clientIp + mid + oid + tid + price
    $plainText = (string)$key.(string)$type.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$oid.(string)$tid.(string)$price;
	
	// hash 암호화
	$hashData = hash("sha512",$plainText); 


    //step2. key=value 로 post 요청
    
    $data = array(
        'type' => $type,
        'mid' => $mid,
        'clientIp' => $clientIp,
        'timestamp' => $timestamp,
        'tid' => $tid,
        'oid' => $oid,
        'price' => $price,
		'report' => $report,
		'invoice' => $invoice,
		'registName' => $registName,
		'exCode' => $exCode,
		'exName' => $exName,
		'charge' => $charge,
		'invoiceDay' => $invoiceDay,
		'sendName' => $sendName,
		'sendTel' => $sendTel,
		'sendPost' => $sendPost,
		'sendAddr1' => $sendAddr1,
		'recvName' => $recvName,
		'recvTel' => $recvTel,
		'recvPost' => $recvPost,
		'recvAddr' => $recvAddr,
        'hashData'=> $hashData
	);
		
	// Request URL
    $url = "https://iniapi.inicis.com/api/v1/escrow";  
    
    $ch = curl_init();                                                      // curl 초기화
    curl_setopt($ch, CURLOPT_URL, $url);                                    // 전송 URL 지정하기
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                         // 요청 결과를 문자열로 반환 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);                           // connection timeout 10초 
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));          // POST data
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                            // (※ 로컬 테스트에서만 사용) 원격 서버의 인증서가 유효한지 검사 안함
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));   // 전송헤더 설정
    curl_setopt($ch, CURLOPT_POST, 1);                                      // post 전송 
     
	$response = curl_exec($ch);
    curl_close($ch);

	//step3. 요청 결과
	//echo $response;
?>