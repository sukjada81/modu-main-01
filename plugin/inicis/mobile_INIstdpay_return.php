<?php
 
    @error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
	header("Content-Type: text/html; charset=utf-8");

	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	define('DEFAULT_PATH',	'../../');
	define('PATH_LIB',			'lib');
	define('PATH_INCLUDE',		'include');

	include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');   
	include_once(DEFAULT_PATH.PATH_LIB.'/lib.Shop.php');   
	include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
	include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
	include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');  

	function openerMovePage($url) {
		 echo"<script>parent.location.href = '{$url}'; self.close();</script>";
		 exit;
	}

	function alertMsgOpener($msg, $url = '') {
		$msg = addslashes($msg);
		echo "<script>";
		echo "parent.mobile_cp_cancel2();";
		echo "parent.alertify.defaults.url = '{$url}';";
		echo "parent.alertify.alert('{$msg}');";		
		echo "</script>";
		exit;
	}

	$mysql = new mysqlClass();

	include "site_conf_inc.php";       // 환경설정 파일 include

	/*
	****************************************************************************************
	* <인증 결과 파라미터>
	****************************************************************************************
	*/
	$P_STATUS    = $_REQUEST["P_STATUS"];
    $P_RMESG1    = $_REQUEST["P_RMESG1"];
    $P_TID       = $_REQUEST["P_TID"];
    $P_REQ_URL   = $_REQUEST["P_REQ_URL"];
    $P_NOTI      = $_REQUEST["P_NOTI"];
    $P_AMT       = $_REQUEST["P_AMT"];

	$addInfo	= explode("|", previlDecode($P_NOTI));
	$direct		= $addInfo[0];
	$my_id		= $addInfo[1];
	$order_num	= $addInfo[2];

	if($direct == 1)	$movePage = "{$Main}?channel=order&direct=1";
	else				$movePage = "{$Main}?channel=order";

	if(!$order_num) {
		alertMsgOpener("필수정보 누락으로 중지 되었습니다.", $movePage);		
	}

	$Main		= "../../index.php";	
	$cart_id	= getCartId($my_id);

	$sql = "SELECT pay_total, pay_status FROM mallRN_order_info WHERE order_num = '{$order_num}'";
	$order_infos = $mysql->one_row($sql);

	if($order_infos['pay_status'] == 'C') { 
		openerMovePage("{$Main}?channel=order_ok&order_num={$order_num}");		
	}	

	$pay_total	= $order_infos['pay_total'];	

	if($pay_total != $P_AMT) {
		alertMsgOpener("결제금액이 일치하지 않습니다.", "{$Main}?channel=cart");		
	}

	############################### 상품수량 체크 ###################################
	if($rtn = checkCartOrder($direct)) {
		if($rtn == 1) {
			
			$card_info = "상품품절 및 재고수량 초과로 결제실패처리";

			$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			if($direct == 1) {
				$sql = "SELECT count(*) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && direct = 1";
				if($mysql->get_one($sql) == 0)	{
					alertMsgOpener("상품품절로 인해 주문 하실 수 없습니다.", "{$Main}?channel=cart");					
				}
				else {
					alertMsgOpener("상품재고수량 초과로 주문수량이 변경 되었습니다.", "{$Main}?channel=order&direct=1");
				}
			}
			else {
				alertMsgOpener("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.", "{$Main}?channel=cart");
			}		
		}
		else if($rtn == 2) {
			alertMsgOpener("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
		}
	}
	############################### 상품수량 체크 ###################################

	$response	= "";
	$bSucc		= "";

   if ($_REQUEST["P_STATUS"] === "00") {             // 인증이 P_STATUS===00 일 경우만 승인 요청
 
        $id_merchant = substr($P_TID,'10','10');     // P_TID 내 MID 구분
        $data = array(
        
         'P_MID' => $id_merchant,         // P_MID
         'P_TID' => $P_TID                // P_TID
        );
 
 
        // curl 통신 시작 
        
        $ch = curl_init();                                                //curl 초기화
        curl_setopt($ch, CURLOPT_URL, $_REQUEST["P_REQ_URL"]);            //URL 지정하기
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                   //요청 결과를 문자열로 반환 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);                     //connection timeout 10초 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                      //원격 서버의 인증서가 유효한지 검사 안함
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));    //POST 로 $data 를 보냄
        curl_setopt($ch, CURLOPT_POST, 1);                                //true시 post 전송 
 
 
        $response = curl_exec($ch);
        curl_close($ch);
    
		parse_str($response, $out);
		//print_r($out);
		foreach ( $out as $key => $value ){
			$$key = $value;
		}
		
		$P_FN_NM		= iconv("euc-kr", "utf-8", $P_FN_NM);
		$P_VACT_NAME	= iconv("euc-kr", "utf-8", $P_VACT_NAME);
	
		if($P_STATUS == '00') {
			$adds		= '';
			$pay_status	= "C";
			switch($P_TYPE) {
				case "CARD" : 
					if($P_CARD_INTEREST == '1') $adds = ',무이자';					
					if($P_RMESG2 == '00') $P_RMESG2 = '일시불';
					$pay_info	= "{$P_FN_NM} ($P_RMESG2{$adds}), 승인시간 : {$P_AUTH_DT}, 승인번호 : {$P_AUTH_NO}";
				break;
				case "BANK" : 
					$pay_info	= "은행명 : {$P_FN_NM}, 은행코드 : {$P_FN_CD1}, 승인시간 : {$P_AUTH_DT}";		
				break;
				case "VBANK" : 
					$pay_info	= "입금은행명 : {$P_FN_NM}, 입금계좌번호 : {$P_VACT_NUM}, 입금예금주 : {$P_VACT_NAME}, 입금만료일 : {$P_VACT_DATE}";		
					$pay_status	= "B";
				break;
				case "CELLPHONE" : 
					$pay_info	= "승인시간 : {$P_AUTH_DT}";							
				break;
			}				

			$sql = "UPDATE mallRN_order_info SET pay_status = '{$pay_status}', pay_info = '{$pay_info}', pay_number = '{$P_TID}', reals = 1 WHERE order_num = '{$order_num}'";
			mysqli_query($mysql->con, $sql) or $bSucc = "false";

			if( $bSucc != "false") {
				$sql	= "UPDATE mallRN_order_goods SET reals = 1 WHERE order_num = '{$order_num}'";
				mysqli_query($mysql->con, $sql) or $bSucc = "false";
			}

			if( $bSucc != "false") {
				goodsOrderQtyChange($order_num);
			}
			
			if( $bSucc != "false" && $pay_status == 'C') {
				orderStatus1($order_num, $my_id);					
			}
			
			if($bSucc == "false") {

				switch($P_TYPE) {
					case "CARD" :		$pay_type = "Card"; break;
					case "BANK" :		$pay_type = "Acct"; break; 
					case "VBANK" :		$pay_type = "Vacct"; break;
					case "CELLPHONE" : 	$pay_type = "HPP"; break;
				}			

				//step1. 요청을 위한 파라미터 설정
				$key         = $SHOP_KEY2;
				$type        = "Refund";
				$paymethod   = $pay_type;
				$timestamp   = date("YmdHis");
				$clientIp    = $_SERVER['REMOTE_ADDR'];
				$mid         = $SHOP_ID;
				$tid         = $P_TID;
				$msg         = "취소요청";
				
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

				parse_str($response, $out);
				
				if($out['resultCode'] == '00') {
					$card_info = "내부 오류로 인해 결제가 취소 되었습니다.";

					$sql	= "UPDATE mallRN_order_info SET reals = 0,  pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$P_TID}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					$sql	= "UPDATE mallRN_order_goods SET reals = 0 WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alertMsgOpener("내부 오류로 인해 결제가 취소 되었습니다.", $movePage);				
				}
				else {					
					$sql	= "SELECT pay_info FROM mallRN_order_info WHERE order_num = '{$order_num}'";
					$card_info = $mysql->get_one($sql)." 망취소 실패 확인 요망.";

					$sql = "UPDATE mallRN_order_info SET pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alertMsgOpener("내부 오류로 인해 망취소가 실패 되었습니다. 관리자에게 문의 바랍니다.");
				}
			}

			if($direct)	$where	= " && direct = 1";
			else 		$where	= " && selects = 1";
			
			$sql		= "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where}";
			$mysql->query($sql);

			openerMovePage("{$Main}?channel=order_ok&order_num={$order_num}");			
		}
		else {
			$card_info = "거래번호 : {$P_TID}, 결제금액 : {$P_AMT}, 결과 메세지 : [{$P_STATUS}] {$P_RMESG1}";

			$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$P_TID}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			alertMsgOpener("결제가 실패 되었습니다. [{$P_RMESG1}]", $movePage);			
		}	
	}
	else {
		//인증 실패 하는 경우 결과코드, 메시지
		$ResultCode = $P_STATUS; 	
		$ResultMsg	= $P_RMESG1;

		$card_info = "결제금액 : {$P_AMT}, 결과 메세지 : [{$ResultCode}] {$ResultMsg}";

		$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		alertMsgOpener("결제가 실패 되었습니다. [{$ResultMsg}]", $movePage);			
	}
?>