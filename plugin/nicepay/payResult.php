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

	function logMsgOpener($msg, $type = 'error') {
		if(!is_array($msg)) $msg = addslashes($msg);
		else $msg = join(", ", $msg);
		echo "<script>";
		echo "parent.mobile_cp_cancel2();";
		
		if($type=='error') echo "parent.alertify.error('{$msg}');";
		else if($type=='log') echo "parent.alertify.warning('{$msg}');";
		else if($type=='success') echo "parent.alertify.success('{$msg}');";
		
		echo "parent.$('#alertify-ok').trigger('click');";
		echo "parent.only_num_formatCk();";	
		echo "</script>";
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
	$authResultCode = $_POST['AuthResultCode'];		// 인증결과 : 0000(성공)
	$authResultMsg = $_POST['AuthResultMsg'];		// 인증결과 메시지
	$nextAppURL = $_POST['NextAppURL'];				// 승인 요청 URL
	$txTid = $_POST['TxTid'];						// 거래 ID
	$authToken = $_POST['AuthToken'];				// 인증 TOKEN
	$payMethod = $_POST['PayMethod'];				// 결제수단
	$mid = $_POST['MID'];							// 상점 아이디
	$moid = $_POST['Moid'];							// 상점 주문번호
	$amt = $_POST['Amt'];							// 결제 금액
	$reqReserved = $_POST['ReqReserved'];			// 상점 예약필드
	$netCancelURL = $_POST['NetCancelURL'];			// 망취소 요청 URL
	
	$is_mobile = preg_match('/'.MOBILE_AGENT.'/i', $_SERVER['HTTP_USER_AGENT']);
	$order_num	= $moid;

	if(!$order_num) {
		if($is_mobile == 1) logMsgOpener("필수정보 누락으로 중지 되었습니다.");
		else logMsg("필수정보 누락으로 중지 되었습니다.");
	}

	$addInfo	= explode("|", previlDecode($reqReserved));
	$direct		= $addInfo[0];
	$my_id		= $addInfo[1];
	
	$Main		= "../../index.php";	
	$cart_id	= getCartId($my_id);

	if($authResultCode == 'I002') {
		if($is_mobile == 1) logMsgOpener("결제를 취소하였습니다.");
		else logMsg("결제를 취소하였습니다.");
	}

	/*
	****************************************************************************************
	* <승인 결과 파라미터 정의>
	* 샘플페이지에서는 승인 결과 파라미터 중 일부만 예시되어 있으며, 
	* 추가적으로 사용하실 파라미터는 연동메뉴얼을 참고하세요.
	****************************************************************************************
	*/

	$sql = "SELECT pay_total, pay_status FROM mallRN_order_info WHERE order_num = '{$order_num}'";
	$order_infos = $mysql->one_row($sql);

	if($order_infos['pay_status'] == 'C') { 
		if($is_mobile == 1) openerMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
		else parentMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
	}	

	$pay_total	= $order_infos['pay_total'];	

	if($pay_total != $amt) {
		if($is_mobile == 1) logMsgOpener("결제금액이 일치하지 않습니다.", "{$Main}?channel=cart");
		else alertMsg("결제금액이 일치하지 않습니다.", "{$Main}?channel=cart");
	}

	############################### 상품수량 체크 ###################################
	if($rtn = checkCartOrder($direct)) {
		if($rtn == 1) {
			
			$card_info = "상품품절 및 재고수량 초과로 결제실패처리";

			$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			if(!$is_mobile) echo "<script>parent.deleteLayer();</script>";
			
			if($direct == 1) {
				$sql = "SELECT count(*) FROM mallRN_cart WHERE cart_id = '{$cart_id}' && direct = 1";
				if($mysql->get_one($sql) == 0)	{
					if($is_mobile == 1) alertMsgOpener("상품품절로 인해 주문 하실 수 없습니다.", "{$Main}?channel=cart");
					else alertMsg("상품품절로 인해 주문 하실 수 없습니다.", "{$Main}?channel=cart");
				}
				else {
					if($is_mobile == 1) alertMsgOpener("상품재고수량 초과로 주문수량이 변경 되었습니다.", "{$Main}?channel=order&direct=1");				
					else alertMsg("상품재고수량 초과로 주문수량이 변경 되었습니다.", "{$Main}?channel=order&direct=1");				
				}
			}
			else {
				if($is_mobile == 1) alertMsgOpener("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.", "{$Main}?channel=cart");
				else alertMsg("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.", "{$Main}?channel=cart");
			}		
		}
		else if($rtn == 2) {
			if(!$is_mobile) echo "<script>parent.deleteLayer();</script>";

			if($is_mobile == 1) alertMsgOpener("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
			else alertMsg("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
		}
	}
	############################### 상품수량 체크 ###################################

	$response	= "";
	$bSucc		= "";
	
	if($authResultCode === "0000") {
		/*
		****************************************************************************************
		* <해쉬암호화> (수정하지 마세요)
		* SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
		****************************************************************************************
		*/	
		$ediDate = date("YmdHis");
		$signData = bin2hex(hash('sha256', $authToken . $mid . $amt . $ediDate . $SHOP_KEY, true));

		try{
			$data = Array(
				'TID' => $txTid,
				'AuthToken' => $authToken,
				'MID' => $mid,
				'Amt' => $amt,
				'EdiDate' => $ediDate,
				'SignData' => $signData,
				'CharSet' => 'utf-8'
			);		
			$response = reqPost($data, $nextAppURL); //승인 호출
			
			//jsonRespDump($response); //response json dump example

			$respArr = json_decode($response);
			foreach ( $respArr as $key => $value ){
				$$key = $value;
			}
			
			if($ResultCode == '3001' || $ResultCode == '4000' || $ResultCode == '4100' || $ResultCode == 'A000') {
				$adds		= '';
				$pay_status	= "C";
				switch($PayMethod) {
					case "CARD" : 
						if($CardInterest == '1') $adds = ',무이자';					
						if($CardQuota == '00') $CardQuota = '일시불';
						$pay_info	= "{$CardName} ($CardQuota{$adds}), 승인시간 : {$AuthDate}, 승인번호 : {$AuthCode}";
					break;
					case "BANK" : 
						$pay_info	= "은행명 : {$BankName}, 은행코드 : {$BankCode}, 승인시간 : {$AuthDate}, 승인번호 : {$AuthCode}";		
					break;
					case "VBANK" : 
						$pay_info	= "입금은행명 : {$VbankBankName}, 입금계좌번호 : {$VbankNum}, 입금만료일 : {$VbankExpDate}";		
						$pay_status	= "B";
					break;
					case "CELLPHONE" : 
						$pay_info	= "승인시간 : {$AuthDate}, 승인번호 : {$AuthCode}";							
					break;
				}				

				$sql = "UPDATE mallRN_order_info SET pay_status = '{$pay_status}', pay_info = '{$pay_info}', pay_number = '{$txTid}', reals = 1 WHERE order_num = '{$order_num}'";
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
				$card_info = "거래번호 : {$txTid}, 결제금액 : {$amt}, 결과 메세지 : [{$ResultCode}] {$ResultMsg}";

				$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$txTid}' WHERE order_num = '{$order_num}'";
				$mysql->query($sql);

				if($is_mobile == 1) {
					logMsgOpener("결제가 실패 되었습니다. [{$ResultMsg}]");
				}
				else {
					echo "<script>parent.deleteLayer();</script>";
					logMsg("결제가 실패 되었습니다. [{$ResultMsg}]");
				}
			}	
			
		}catch(Exception $e){
			$e->getMessage();
			$bSucc = "false";
		}

		if($bSucc == "false") {
			$data = Array(
				'TID' => $txTid,
				'AuthToken' => $authToken,
				'MID' => $mid,
				'Amt' => $amt,
				'EdiDate' => $ediDate,
				'SignData' => $signData,
				'NetCancel' => '1',
				'CharSet' => 'utf-8'
			);
			$response = reqPost($data, $netCancelURL); //예외 발생시 망취소 진행
			
			//jsonRespDump($response); //response json dump example

			$card_info = "내부 오류로 인해 결제가 취소 되었습니다.";

			$sql	= "UPDATE mallRN_order_info SET reals = 0,  pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$txTid}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			$sql	= "UPDATE mallRN_order_goods SET reals = 0 WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			if($is_mobile == 1) {
				logMsgOpener("내부 오류로 인해 결제가 취소 되었습니다.");
			}
			else {		
				echo "<script>parent.deleteLayer();</script>";
				logMsg("내부 오류로 인해 결제가 취소 되었습니다.");
			}
		}

		if($direct)	$where	= " && direct = 1";
		else 		$where	= " && selects = 1";
		
		$sql		= "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where}";
		$mysql->query($sql);

		if($is_mobile == 1) openerMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
		else parentMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
	}
	else {
		//인증 실패 하는 경우 결과코드, 메시지
		$ResultCode = $authResultCode; 	
		$ResultMsg = $authResultMsg;

		$card_info = "거래번호 : {$txTid}, 결제금액 : {$amt}, 결과 메세지 : [{$ResultCode}] {$ResultMsg}";

		$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$txTid}' WHERE order_num = '{$order_num}'";
		$mysql->query($sql);

		if($is_mobile == 1) {
			logMsgOpener("결제가 실패 되었습니다. [{$ResultMsg}]");
		}
		else {			
			echo "<script>parent.deleteLayer();</script>";
			logMsg("결제가 실패 되었습니다. [{$ResultMsg}]");
		}
	}
	
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

?>