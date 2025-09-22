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

	$mysql = new mysqlClass();

	$order_num		= $_REQUEST["orderNumber"];
	$merchantData	= $_REQUEST["merchantData"];

	$addInfo		= explode("|", previlDecode($merchantData));
	$direct			= $addInfo[0];
	$my_id			= $addInfo[1];
	$Main			= "../../index.php";

	if($direct == 1)	$movePage = "{$Main}?channel=order&direct=1";
	else				$movePage = "{$Main}?channel=order";

	if(!$order_num) alert("필수정보 누락으로 결제가 중지 되었습니다.", $movePage);
			
	$cart_id		= getCartId($my_id);	

	$sql = "SELECT pay_total, pay_status FROM mallRN_order_info WHERE order_num = '{$order_num}'";
	$order_infos = $mysql->one_row($sql);

	if($order_infos['pay_status'] == 'C') { 
		movePage("{$Main}?channel=order_ok&order_num={$order_num}");
	}	

	$pay_total	= $order_infos['pay_total'];	
	
	############################### 상품수량 체크 ###################################
	if($rtn = checkCartOrder($direct)) {
		if($rtn == 1) {
			
			$card_info = "상품품절 및 재고수량 초과로 결제실패처리";

			$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			if($direct == 1) {
				$sql = "SELECT count(*) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && direct = 1";
				if($mysql->get_one($sql) == 0)	{
					alert("상품품절로 인해 주문 하실 수 없습니다.", "{$Main}?channel=cart");
				}
				else {
					alert("상품재고수량 초과로 주문수량이 변경 되었습니다.", $movePage);				
				}
			}
			else {
				alert("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.", "{$Main}?channel=cart");
			}		
		}
		else if($rtn == 2) {
			alert("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
		}
	}
	############################### 상품수량 체크 ###################################

	$response	= "";
	$bSucc		= "";

	include "site_conf_inc.php";       // 환경설정 파일 include

    require_once('libs/INIStdPayUtil.php');
    require_once('libs/HttpClient.php');
 
	$util = new INIStdPayUtil();

	try {

		//#############################
		// 인증결과 파라미터 수신
		//#############################

		if (strcmp("0000", $_REQUEST["resultCode"]) == 0) {

			//############################################
			// 1.전문 필드 값 설정(***가맹점 개발수정***)
			//############################################

			$mid        = $_REQUEST["mid"];
			$timestamp  = $util->getTimestamp();
			$charset    = "UTF-8";
			$format     = "JSON";
			$authToken  = $_REQUEST["authToken"]; 
			$authUrl    = $_REQUEST["authUrl"];
			$netCancel  = $_REQUEST["netCancelUrl"];        
			$merchantData = $_REQUEST["merchantData"];

			//#####################
			// 2.signature 생성
			//#####################
			$signParam["authToken"] = $authToken;   // 필수
			$signParam["timestamp"] = $timestamp;   // 필수
			// signature 데이터 생성 (모듈에서 자동으로 signParam을 알파벳 순으로 정렬후 NVP 방식으로 나열해 hash)
			$signature = $util->makeSignature($signParam);


			//#####################
			// 3.API 요청 전문 생성
			//#####################
			$authMap["mid"]        = $mid;       // 필수
			$authMap["authToken"]  = $authToken; // 필수
			$authMap["signature"]  = $signature; // 필수
			$authMap["timestamp"]  = $timestamp; // 필수
			$authMap["charset"]    = $charset;   // default=UTF-8
			$authMap["format"]     = $format;    // default=XML

			try {

				$httpUtil = new HttpClient();

				//#####################
				// 4.API 통신 시작
				//#####################

				$authResultString = "";
				if ($httpUtil->processHTTP($authUrl, $authMap)) {
					$authResultString = $httpUtil->body;

				} else {
					/*
					echo "Http Connect Error\n";
					echo $httpUtil->errormsg;

					throw new Exception("Http Connect Error");
					*/

					$card_info = "Http Connect Error! {$httpUtil->errormsg}";

					$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alert("내부 오류로 인해 결제가 실패 되었습니다.", $movePage);
				}

				//############################################################
				//5.API 통신결과 처리(***가맹점 개발수정***)
				//############################################################
				
				$resultMap = json_decode($authResultString, true);

				if($resultMap["resultCode"] = '0000') {
					$adds		= '';
					$pay_status	= "C";
					switch($resultMap["payMethod"]) {
						case "Card" : 
							if($resultMap["CARD_Interest"] == '1')	$adds = ',무이자';					
							if($resultMap["CARD_Quota"] == '0')		$CardQuota = '일시불';
							$pay_info	= "카드사코드 : {$resultMap['CARD_Code']} ($CardQuota{$adds}), 승인시간 : {$resultMap['applDate']} {$resultMap['applTime']}, 승인번호 : {$resultMap['applNum']}";
						break;
						case "DirectBank" : 
							$pay_info	= "은행코드 : {$resultMap['ACCT_BankCode']}, 승인시간 : {$resultMap['applDate']} {$resultMap['applTime']}";		
						break;
						case "VBank" : 
							$pay_info	= "입금은행명 : {$resultMap['vactBankName']}, 입금계좌번호 : {$resultMap['VACT_Num']}, 입금예금주명 : {$resultMap['VACT_Name']}, 입금만료일 : {$resultMap['VACT_Date']}";		
							$pay_status	= "B";
						break;
						case "HPP" : 
							$pay_info	= "승인시간 : {$resultMap['applDate']} {$resultMap['applTime']}";							
						break;
					}				

					$sql = "UPDATE mallRN_order_info SET pay_status = '{$pay_status}', pay_info = '{$pay_info}', pay_number = '{$resultMap['tid']}', reals = 1 WHERE order_num = '{$order_num}'";
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
				}
				else {
					$card_info = "거래번호 : {$resultMap['tid']}, 결제금액 : {$resultMap['TotPrice']}, 결과 메세지 : [{$resultMap['resultCode']}] {$resultMap['resultMsg']}";

					$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$resultMap['tid']}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alert("결제가 실패 되었습니다. [{$ResultMsg}]", $movePage);
				}	

			} catch (Exception $e) {
				//    $s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
				//####################################
				// 실패시 처리(***가맹점 개발수정***)
				//####################################
				//---- db 저장 실패시 등 예외처리----//
				//$s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
				//echo $s;
				$bSucc = "false";				
			}

			if($bSucc == "false") {
				//#####################
				// 망취소 API
				//#####################

				$netcancelResultString = ""; // 망취소 요청 API url(고정, 임의 세팅 금지)
				if ($httpUtil->processHTTP($netCancel, $authMap)) {
					$netcancelResultString = $httpUtil->body;
				} else {
					/*
					echo "Http Connect Error\n";
					echo $httpUtil->errormsg;

					throw new Exception("Http Connect Error");
					*/	
						
					$sql	= "SELECT pay_info FROM mallRN_order_info WHERE order_num = '{$order_num}'";
					$card_info = $mysql->get_one($sql)." 망취소 실패 확인 요망.";

					$sql = "UPDATE mallRN_order_info SET pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alert("내부 오류로 인해 망취소가 실패 되었습니다. 관리자에게 문의 바랍니다.", $movePage);
				}

				//echo "<br/>## 망취소 API 결과 ##<br/>";
				
				/*##XML output##*/
				//$netcancelResultString = str_replace("<", "&lt;", $$netcancelResultString);
				//$netcancelResultString = str_replace(">", "&gt;", $$netcancelResultString);

				// 취소 결과 확인
				//echo "<p>". $netcancelResultString . "</p>";

				$resultMap2 = json_decode($netcancelResultString, true);

				if($resultMap2["resultCode"] = '0000') {
					$card_info = "내부 오류로 인해 결제가 취소 되었습니다.";

					$sql	= "UPDATE mallRN_order_info SET reals = 0,  pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$resultMap['tid']}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					$sql	= "UPDATE mallRN_order_goods SET reals = 0 WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alert("내부 오류로 인해 결제가 취소 되었습니다.", $movePage);
				}
				else {
					$sql	= "SELECT pay_info FROM mallRN_order_info WHERE order_num = '{$order_num}'";
					$card_info = $mysql->get_one($sql)." 망취소 실패 확인 요망. [{$resultMap2['resultCode']}] {$resultMap2['resultMsg']}";

					$sql = "UPDATE mallRN_order_info SET pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
					$mysql->query($sql);

					alert("내부 오류로 인해 망취소가 실패 되었습니다. 관리자에게 문의 바랍니다.", $movePage);
				}
			}

			if($direct)	$where	= " && direct = 1";
			else 		$where	= " && selects = 1";
			
			$sql		= "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where}";
			$mysql->query($sql);

			movePage("{$Main}?channel=order_ok&order_num={$order_num}");

		} else {
			//인증 실패 하는 경우 결과코드, 메시지
			$ResultCode = $_REQUEST["resultCode"]; 	
			$ResultMsg	= $_REQUEST["resultMsg"];			
		}
	} catch (Exception $e) {
		//$s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
		
		//에러일 경우 결과코드, 메시지
		$ResultCode = $e->getCode(); 	
		$ResultMsg	= $e->getMessage();		
	}

	$card_info = "결과 메세지 : [{$ResultCode}] {$ResultMsg}";

	$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
	$mysql->query($sql);

	alert("결제가 실패 되었습니다. [{$ResultMsg}]", $movePage);
?>