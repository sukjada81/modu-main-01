<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');

include_once('init.php');

$referer	= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$access_ip	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$param		= checkPostVar('param');

if(!$referer || !$access_ip || $access_ip != $_SERVER['SERVER_ADDR'] || !$param)	{
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

$param		= previlDecode($param);
if($param != date('Ymd', time() - 86400)) {	
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

if(!$shop_config['order_tracker_key'] || $shop_config['order_tracker_yn'] == 'N') {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function		
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/		
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

$tracker_url	= "https://info.sweettracker.co.kr";
$tracker_key	= $shop_config['order_tracker_key'];
$signdate		= time();

$rtn = getSendCurl("{$tracker_url}/api/v1/companylist?t_key={$tracker_key}");
$rtn = json_decode($rtn);

if(isset($rtn->code)) {
	$sql	= "INSERT INTO mallRN_delivery_api_log SET
					status			= '2',
					message			= '{$rtn->msg}',
					signdate		= '{$signdate}'
				";
	$mysql->query2($sql);
	exit;	
}

$company		= objectToArray($rtn->Company);
$delivery_code	= array();
foreach($company as $k => $v) {
	$v['Name']					= str_replace(array(".", "-", " "), "", $v['Name']);
	$delivery_code[$v['Name']]	= $v['Code'];
}


######################## 배송업체 정보 #############################
$delivery_info_array	= array();

if($shop_config['delivery_info']) {
	$delivery_info = explode("|*|", $shop_config['delivery_info']);	
	if($delivery_info[1] && $delivery_info[1] != '|||') {
		for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

			$delivery_info2 = explode("|", $delivery_info[$i]);
			
			if($delivery_info2[3] == 0) continue;

			$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];
		}
	}
}
######################## 배송업체 정보 #############################

$sql			= "SELECT uid, order_num, delivery_info FROM mallRN_order_goods WHERE reals = 1 && (status = 3 || (status = 7 && status2 = 4))";
$mysql->query($sql);

$tmps_array		= "";

while($row = $mysql->fetch_array()){
	if($row['delivery_info']) {
		$tmps			= explode("|", $row['delivery_info']);
		if(isset($delivery_info_array[$tmps[0]])) {
			$delivery		= str_replace(array(".", "-", " "), "", $delivery_info_array[$tmps[0]]);
			$num			= str_replace(array("-", " ", "\n", "\r"), "", $tmps[1]);

			if(!$num) continue;

			if(isset($delivery_code[$delivery])) {
				$code			= $delivery_code[$delivery];				
				$rtn			= "";
				
				if($tmps_array != '') {
					if($tmps_array[0] == $num) {
						if($tmps_array[1] == 1) $rtn =  $object = (object) ['completeYN' => $tmps_array[2], 'level' => $tmps_array[3]];
						else					$rtn =  $object = (object) ['code' => $tmps_array[2], 'msg' => $tmps_array[3]];
					}
				}

				if(!$rtn) {				
					$rtn = getSendCurl("{$tracker_url}/api/v1/trackingInfo?t_key={$tracker_key}&t_code={$code}&t_invoice={$num}");					
					$rtn = json_decode($rtn);				
				}

				if(isset($rtn->code)) {
					$status		= 2;
					$message	= $rtn->msg;
					$tmps_array	= array($num, 2, $rtn->code, $rtn->msg);
				}
				else {									
					if($rtn->completeYN == 'Y' || $rtn->level == '6') {
						orderStatus4($row['order_num'], $row['uid'], 'auto');
						$status	= 1;
					}
					else {
						$status = 0;
					}
					$message	= "";

					$tmps_array	= array($num, 1, $rtn->completeYN, $rtn->level);				
				}
			}
			else {
				$code		= '';
				$status		= 2;
				$message	= '택배사코드 미매칭 택배사명 확인 요망';
			}

			$sql	= "INSERT INTO mallRN_delivery_api_log SET
							order_num		= '{$row['order_num']}',
							og_uid			= '{$row['uid']}',
							delivery_name	= '{$delivery}',
							delivery_num	= '{$num}',
							delivery_code	= '{$code}',
							status			= '{$status}',
							message			= '{$message}',
							signdate		= '{$signdate}'
					";
			$mysql->query2($sql);
		}		
	}
}

$sql	= "SELECT status, order_num FROM mallRN_delivery_api_log WHERE uid > 0 ORDER BY uid DESC LIMIT 1";
if($data	= $mysql->one_row($sql)) {
	if($data['status'] == 2 && $data['order_num'] == '') {					
		$sql	= "INSERT INTO mallRN_delivery_api_log SET
					status			= '0',
					message			= 'APIKEY 정상변경완료',
					signdate		= '{$signdate}'
				";
		$mysql->query2($sql);
	}
}

?>
