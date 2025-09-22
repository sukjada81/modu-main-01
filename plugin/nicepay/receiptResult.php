<?php

include "site_conf_inc.php";       // 환경설정 파일 include

$signdate		= time();
$rtns			= "False";

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
****************************************************************************************
* <현금영수증 발급 요청 파라미터>
****************************************************************************************
*/

$mid				= $SHOP_ID;									// 상점 아이디
$tid				= $SHOP_ID."0401".date("ymdHis").mt_rand(1000,9999);		// 상점 거래아이디
$moid				= $order_num2;								// 상점 주문번호
$receiptAmt			= $_POST['ReceiptAmt'];					// 현금영수증 요청금액
$goodsName			= $_POST['GoodsName'];					// 상품명
$receiptType		= $_POST['ReceiptType'];				// 증빙구분
$receiptTypeNo		= $_POST['ReceiptTypeNo'];			// 현금영수증 발급번호
$receiptSupplyAmt	= $_POST['ReceiptSupplyAmt'];		// 공급가액
$receiptVAT			= $_POST['ReceiptVAT'];					// 부가가치세
$receiptServiceAmt	= $_POST['ReceiptServiceAmt'];	// 봉사료
$receiptTaxFreeAmt	= $_POST['ReceiptTaxFreeAmt'];	// 면세금액
$charSet			= "utf-8";						// 응답전문 유형
$receiptRequestURL	= "https://webapi.nicepay.co.kr/webapi/cash_receipt.jsp";		//현금영수증 발급 요청 URL

/*
*******************************************************
* <해쉬암호화> (수정하지 마세요)
* SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
*******************************************************
*/ 
$ediDate = date("YmdHis");
$signData = bin2hex(hash('sha256', $mid.$receiptAmt.$ediDate.$moid.$SHOP_KEY, true));

/*
****************************************************************************************
* <현금영수증 발급 결과 파라미터 정의>
* 샘플페이지에서는 현금영수증 발급 결과 파라미터 중 일부만 예시되어 있으며, 
* 추가적으로 사용하실 파라미터는 연동메뉴얼을 참고하세요.
**********************************
*****************************************************
*/
$response = "";

try{
	$data = Array(
	'MID' => $mid,
	'TID' => $tid,
	'EdiDate' => $ediDate,
	'Moid' => $moid,
	'SignData' => $signData,
	'GoodsName' => $goodsName,
	'ReceiptAmt' => $receiptAmt,
	'ReceiptType' => $receiptType,
	'ReceiptTypeNo' => $receiptTypeNo,
	'ReceiptSupplyAmt' => $receiptSupplyAmt,
	'ReceiptVAT' => $receiptVAT,
	'ReceiptServiceAmt' => $receiptServiceAmt,
	'ReceiptTaxFreeAmt' => $receiptTaxFreeAmt,
	'CharSet' => $charSet
	);


	$response = reqPost($data, $receiptRequestURL);
	//jsonRespDump($response);

	$respArr = json_decode($response);
	foreach ( $respArr as $key => $value ){	
		$$key = $value;
	}

	if($ResultCode == '7001') {
		$bSucc			= ""; 
		$receipt_info	= "승인번호 : {$AuthCode}";	

		$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$AuthDate}', receipt_no = '{$TID}', receipt_info = '{$receipt_info}', receipt_status = 'NTRW', receipt_error = '', status = '3', status_date = '{$signdate}' WHERE order_num = '{$order_num}'";
		$mysql->query($sql);
		
		if(substr($order_num, 0, 1) != 'C') {
			$sql = "UPDATE mallRN_order_info SET cash_issued = 1 WHERE order_num = '{$order_num}'";
			$mysql->query($sql);
		}
		$rtns = "True";
	}
	else {
		$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$ResultMsg}', status_date = '{$signdate}'  WHERE order_num = '{$order_num}' && status = '0'";
		$mysql->query($sql);
	}
}catch(Exception $e){
	$e->getMessage();
	$ResultCode		= "9999";
	$ResultMsg		= "통신실패";

	$sql			= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$ResultMsg}', status_date = '{$signdate}'  WHERE order_num = '{$order_num}' && status = '0'";
	$mysql->query($sql);
}

echo $rtns;
exit;

?>