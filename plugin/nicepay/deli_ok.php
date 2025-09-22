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

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 1";
$shop_config = $mysql->one_row($sql);

$SHOP_ID			= trim($shop_config['payment_shop_id']);
$SHOP_KEY			= trim($shop_config['payment_shop_key']);

$order_num = checkGetVar('order_num');
if(!$order_num) {
	echo "False";
	exit;
}

$sql	= "SELECT pay_number, address1, address2 FROM mallRN_order_info WHERE escrow = '1' && order_num='{$order_num}'";
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


/*
*******************************************************
* <해쉬암호화> (수정하지 마세요)
* SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
*******************************************************
*/ 
$ediDate = date("YmdHis");
$merchantKey = $SHOP_KEY;
$signData = bin2hex(hash('sha256', $tid.$mid.$reqType.$ediDate.$merchantKey, true));	
	

// API CALL foreach 예시
function jsonRespDump($resp){
	$respArr = json_decode($resp);
	foreach ( $respArr as $key => $value ){
		//echo "$key=". $value."<br />";
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

$response = "";

	$data = Array(
	'MID' => $mid,
	'TID' => $tid,
	'EdiDate' => $ediDate,
	'SignData' => $signData,
	'ReqType' => $reqType,
	'DeliveryCoNm' => $deliveryCoNm,
	'BuyerAddr' => $buyerAddr,
	'InvoiceNum' => $invoiceNum,
	'RegisterName' => $registerName,
	'ConfirmMail' => $confirmMail,
	'CharSet' => $charSet
	);
	
	$response = reqPost($data, $escrowRequestURL);
	//jsonRespDump($response);	
?>