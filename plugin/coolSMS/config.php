<?php

function get_config($coolsms_key, $coolsms_secret) {

	if(!$coolsms_key) {
		$mysqlTemp = new mysqlClass(); 

		$sql = "SELECT sms_yn, sms_key, sms_secret FROM mallRN_configuration WHERE uid = 1";
		$data = $mysqlTemp->one_row($sql);

		if($data['sms_yn'] == 'N') Error("SMS 사용하지 않음으로 선택될 경우 SMS관련 기능을 사용 할 수 없습니다.");

		$coolsms_key	= $data['sms_key'];
		$coolsms_secret = $data['sms_secret'];
		
		$mysqlTemp->close();
		unset($mysqlTemp, $data);
	}

	return array(
		"apiKey"	=> $coolsms_key,
		"apiSecret" => $coolsms_secret,
		"protocol"	=> "https",
		"domain"	=> "api.coolsms.co.kr",
		"prefix"	=> ""
	);
}
?>