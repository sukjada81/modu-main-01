<?php

include "plugin/nicepay/site_conf_inc.php";

$returnURL			= ABSOLUTE_PATH_SHOP."plugin/nicepay/payResult.php"; // 결과페이지(절대경로) - 모바일 결제창 전용
$VbankExpDate		= date("Ymd", time() + ((86400) * 3)); // 가상계좌입금만료일 발급일 + 3일

?>

<!-- 아래 js는 PC 결제창 전용 js입니다.(모바일 결제창 사용시 필요 없음) -->
<script src="https://web.nicepay.co.kr/v3/webstd/js/nicepay-3.0.js" type="text/javascript"></script>
<script type="text/javascript">
//결제창 최초 요청시 실행됩니다.
function nicepayStart(){
	if(checkPlatform(window.navigator.userAgent) == "mobile"){//모바일 결제창 진입
		document.payForm.action = "https://web.nicepay.co.kr/v3/v3Payment.jsp";
		document.payForm.submit();
	}else{//PC 결제창 진입
		goPay(document.payForm);
	}
}

//[PC 결제창 전용]결제 최종 요청시 실행됩니다. <<'nicepaySubmit()' 이름 수정 불가능>>
function nicepaySubmit(){
	document.payForm.submit();
}

//[PC 결제창 전용]결제창 종료 함수 <<'nicepayClose()' 이름 수정 불가능>>
function nicepayClose(){
	//alert("결제가 취소 되었습니다");
	cp_cancel();
}

//pc, mobile 구분(가이드를 위한 샘플 함수입니다.)
function checkPlatform(ua) {
	if(ua === undefined) {
		ua = window.navigator.userAgent;
	}
	
	ua = ua.toLowerCase();
	var platform = {};
	var matched = {};
	var userPlatform = "pc";
	var platform_match = /(ipad)/.exec(ua) || /(ipod)/.exec(ua) 
		|| /(windows phone)/.exec(ua) || /(iphone)/.exec(ua) 
		|| /(kindle)/.exec(ua) || /(silk)/.exec(ua) || /(android)/.exec(ua) 
		|| /(win)/.exec(ua) || /(mac)/.exec(ua) || /(linux)/.exec(ua)
		|| /(cros)/.exec(ua) || /(playbook)/.exec(ua)
		|| /(bb)/.exec(ua) || /(blackberry)/.exec(ua)
		|| [];
	
	matched.platform = platform_match[0] || "";
	
	if(matched.platform) {
		platform[matched.platform] = true;
	}
	
	if(platform.android || platform.bb || platform.blackberry
			|| platform.ipad || platform.iphone 
			|| platform.ipod || platform.kindle 
			|| platform.playbook || platform.silk
			|| platform["windows phone"]) {
		userPlatform = "mobile";
	}
	
	if(platform.cros || platform.mac || platform.linux || platform.win) {
		userPlatform = "pc";
	}
	
	return userPlatform;
}
</script>

<!-- 주문정보 입력 form : order_info -->
<form name="payForm" method="post" action="plugin/nicepay/payResult.php" target="HFrm">

<!-- 결제 수단 -->
<input type="hidden" name="PayMethod" value="" />

<!-- 상품명 -->
<input type="hidden" name="GoodsName" value="<?php echo($CP_GOODS_NAME)?>" />

<!-- 결제금액 -->
<input type="hidden" name="Amt" value="0" />

<!-- 상점 아이디 -->
<input type="hidden" name="MID" value="<?php echo($SHOP_ID)?>" />

<!-- 상품 주문번호 -->
<input type="hidden" name="Moid" value="" />

<!-- 주문자명 -->
<input type="hidden" name="BuyerName" value="" />

<!-- 주문자 E-mail -->
<input type="hidden" name="BuyerEmail" value="" />

<!-- 주문자 연락처 -->
<input type="hidden" name="BuyerTel" value="" />

<!-- 인증완료 결과처리 URL -->
<input type="hidden" name="ReturnURL" value="<?php echo($returnURL)?>" />

<!-- 최대 할부개월수 -->
<input type="hidden" name='SelectQuota' value="<?php echo($payment_install_range)?>" />

<!-- 가상계좌입금만료일(YYYYMMDD) -->
<input type="hidden" name="VbankExpDate" value="<?php echo($VbankExpDate)?>" />

<!-- 휴대폰 상품구분, 휴대폰 소액결제 시 필수 (0:컨텐츠, 1:현물) -->
<input type="hidden" name="GoodsCl" value="1" />


<!-- 상품구분(실물(1),컨텐츠(0)) -->
<input type="hidden" name="GoodsCl" value="1" />	

<!-- 일반(0)/에스크로(1) --> 
<input type="hidden" name="TransType" value="0" />

<!-- 응답 파라미터 인코딩 방식 -->
<input type="hidden" name="CharSet" value="utf-8" />

<!-- 상점 예약필드 -->
<input type="hidden" name="ReqReserved" value="" />

<!-- 전문 생성일시 -->
<input type="hidden" name="EdiDate" value="" />

<!-- 해쉬값 -->
<input type="hidden" name="SignData" value="" />

</form>