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
include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');  

$mysql = new mysqlClass();

include "site_conf_inc.php";

$HeaderProtocol		= isset($_SERVER['HTTPS']) ? "https://" : "http://";
$returnURL			= $HeaderProtocol . $_SERVER["HTTP_HOST"]."/plugin/inicis/mobile_INIstdpay_return.php"; // 결과페이지(절대경로) - 모바일 결제창 전용
$notiURL			= $HeaderProtocol . $_SERVER["HTTP_HOST"]."/plugin/inicis/mx_rnoti.php"; // 

?>

<script> 

function on_pay() { 
	myform = document.mobileweb; 
	myform.action = "https://mobile.inicis.com/smart/payment/";
	myform.target = "_self";
	myform.submit(); 
}

window.onpageshow = function(event) {
    if ( event.persisted || (window.performance && window.performance.navigation.type == 2)) {
        parent.mobile_cp_cancel2();
    }
}
</script> 


<!-- 주문정보 입력 form : -->
<form name="mobileweb" id="" method="post" accept-charset="euc-kr">

<!-- 결제 수단 -->
<input type="hidden" name="P_INI_PAYMENT" value="" />

<!-- 상점 아이디 -->
<input type="hidden" name="P_MID" value="<?php echo($SHOP_ID)?>" />

<!-- 상품 주문번호 -->
<input type="hidden" name="P_OID" value="" />

<!-- 결제금액 -->
<input type="hidden" name="P_AMT" value="" />

<!-- 상품명 -->
<input type="hidden" name="P_GOODS" value="" />

<!-- 주문자명 -->
<input type="hidden" name="P_UMANE" value="" />

<!-- 주문자 연락처 -->
<input type="hidden" name="P_MOBILE" value="" />

<!-- 주문자 E-mail -->
<input type="hidden" name="P_EMAIL" value="" />

<!-- 결과수신 URL -->
<input type="hidden" name="P_NEXT_URL" value="<?php echo($returnURL)?>" />

<!-- P_NOTI_URL 가상계좌 입금통보 URL -->
<input type="hidden" name="P_NOTI_URL" value="<?php echo($notiURL)?>" />

<!-- 응답 파라미터 인코딩 방식 -->
<input type="hidden" name="P_CHARSET" value="UTF-8" />

<!--  P_HPP_METHOD 휴대폰결제 상품유형 [1:컨텐츠, 2:실물] -->
<input type="hidden" name="P_HPP_METHOD" value="2" />

<!-- 최대 할부개월수 -->
<input type="hidden" name='P_QUOTABASE' value="" />

<!-- 가맹점 임의 데이터 -->
<input type="hidden" name="P_NOTI" value="" />

<!--  P_RESERVED -->
<input type="hidden" name="P_RESERVED" value="below1000=Y&vbank_receipt=N&bank_receipt=N" />

</form>