<?php

include "site_conf_inc.php";       // 환경설정 파일 include

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

$respArr = json_decode($response);
foreach ( $respArr as $key => $value ){
	$$key = $value;
}

if($resultCode == '00') {
	$status = 0;
	$rtns	= "|*|SUCCESS";
	
	if(isset($prtcRemains))	$rem_mny = (int) $prtcRemains; // 취소요청후 잔액
	else					$rem_mny = 0;	
}
else {
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
				message			= '{$resultMsg}',
				signdate		= '{$signdate}'
			";
$mysql->query($sql);

echo $rtns;
exit;

?>