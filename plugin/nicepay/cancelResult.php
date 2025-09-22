<?php

include "site_conf_inc.php";       // 환경설정 파일 include

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


$mid				= $SHOP_ID;
$moid				= $order_num2;		
$cancelMsg			= $cancel_msg;
$tid				= $cancel_tno;
$cancelAmt			= $cancel_rem_mny; 
$partialCancelCode	= $cancel_mode_type;

/*  
****************************************************************************************
* Signature : 요청 데이터에 대한 무결성 검증을 위해 전달하는 파라미터로 허위 결제 요청 등 결제 및 보안 관련 이슈가 발생할 만한 요소를 방지하기 위해 연동 시 사용하시기 바라며 
* 위변조 검증 미사용으로 인해 발생하는 이슈는 당사의 책임이 없음 참고하시기 바랍니다.
****************************************************************************************
 */

$ediDate = date("YmdHis");
$signData = bin2hex(hash('sha256', $mid . $cancelAmt . $ediDate . $SHOP_KEY, true));
$rtns	= "|*|FALSE";

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

	if($ResultCode == '2001' || $ResultCode == '2211') {
		$status = 0;
		$rtns	= "|*|SUCCESS";
	
		$rem_mny = (int) $RemainAmt; // 취소요청후 잔액
	}
	else {
		$status = 1;		
		$rem_mny = 0;
	}
}catch(Exception $e){
	$e->getMessage();
	$ResultCode = "9999";
	$ResultMsg = "통신실패";

	$status = 1;
	$rem_mny = 0;
}

$sql	= "INSERT INTO mallRN_order_cancel_cp_log SET
				order_num		= '{$order_num}',
				og_uid			= '{$og_uid}',
				price			= '{$cancel_mod_mny}',
				rem_price		= '{$rem_mny}',
				pay_type		= '{$cancel_pay_type}',					
				pay_number		= '{$cancel_tno}',
				status			= '{$status}',
				message			= '{$ResultMsg}',
				signdate		= '{$signdate}'
			";
$mysql->query($sql);

echo $rtns;
exit;

?>