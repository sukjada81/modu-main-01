<?php

require_once('plugin/inicis/site_conf_inc.php');
require_once('plugin/inicis/libs/INIStdPayUtil.php');
$SignatureUtil = new INIStdPayUtil();

$mid 			= $SHOP_ID; 			// 상점아이디			
$signKey 		= $SHOP_KEY; 			// 웹 결제 signkey

$mKey 			= $SignatureUtil->makeHash($signKey, "sha256");

if($SHOP_ID == "INIpayTest")	$script_url    = "https://stgstdpay.inicis.com/stdjs/INIStdPay.js";
else							$script_url    = "https://stdpay.inicis.com/stdjs/INIStdPay.js";

?>

<script language="javascript" type="text/javascript" src="<?php echo($script_url)?>" charset="UTF-8"></script>
<script type="text/javascript">
	function paybtn() {
		INIStdPay.pay('SendPayForm_id');
	}
	
	function cp_cancel2() {
		cp_cancel();
	}
</script>


<!-- 주문정보 입력 form : SendPayForm_id -->
<form name="payForm" id="SendPayForm_id" method="post" target="HFrm">

<input type="hidden" name="version" value="1.0" />

<!-- 결제 수단 -->
<input type="hidden" name="gopaymethod" value="" />

<!-- 상점 아이디 -->
<input type="hidden" name="mid" value="<?php echo($mid)?>" />

<!-- 상품 주문번호 -->
<input type="hidden" name="oid" value="" />

<!-- 결제금액 -->
<input type="hidden" name="price" value="" />

<!-- timestamp -->
<input type="hidden" name="timestamp" value="" />

<!-- signature -->
<input type="hidden" name="signature" value="" />

<!-- mKey -->
<input type="hidden" name="mKey" value="<?php echo($mKey)?>" />

<!--  통화구분 -->
<input type="hidden" name="currency" value="WON" />

<!-- 상품명 -->
<input type="hidden" name="goodname" value="<?php echo($CP_GOODS_NAME)?>" />

<!-- 주문자명 -->
<input type="hidden" name="buyername" value="" />

<!-- 주문자 연락처 -->
<input type="hidden" name="buyertel" value="" />

<!-- 주문자 E-mail -->
<input type="hidden" name="buyeremail" value="" />

<!-- 결과수신 URL -->
<input type="hidden" name="returnUrl" value="<?php echo(ABSOLUTE_PATH_SHOP.'plugin/inicis/INIstdpay_return.php')?>" />

<!-- 결제창 닫기 URL -->
<input type="hidden" name="closeUrl" value="<?php echo(ABSOLUTE_PATH_SHOP.'plugin/inicis/close.php')?>" />

<!-- 최대 할부개월수 -->
<input type="hidden" name='quotabase' value="<?php echo($payment_install_range2)?>" />

<!-- 응답 파라미터 인코딩 방식 -->
<input type="hidden" name="charset" value="UTF-8" />

<!-- 가맹점 임의 데이터 -->
<input type="hidden" name="merchantData" value="" />

<!--  acceptmethod -->
<input type="hidden" name="acceptmethod" value="HPP(1):below1000:no_receipt" />

</form>