<?php
    /* ============================================================================== */
    /* =   PAGE : 결제 요청 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   이 페이지는 표준웹을 통해서 결제자가 결제 요청을 하는 페이지             = */
    /* =   입니다. 아래의 ※ 필수, ※ 옵션 부분과 매뉴얼을 참조하셔서 연동을        = */
    /* =   진행하여 주시기 바랍니다.                                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://kcp.co.kr/technique.requestcode.do                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2016   NHN KCP Inc.   All Rights Reserverd.               = */
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */

    include "plugin/kcp/site_conf_inc.php";

    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */
?>

<script type="text/javascript">
		
	function m_Completepayment( FormOrJson, closeEvent ) {
		var frm = document.order_info; 	 
		GetField( frm, FormOrJson ); 
		
		if( frm.res_cd.value == "0000" ) {
			//alert("결제 승인 요청 전,\n\n반드시 결제창에서 고객님이 결제 인증 완료 후\n\n리턴 받은 ordr_chk 와 업체 측 주문정보를\n\n다시 한번 검증 후 결제 승인 요청하시기 바랍니다."); 
			frm.submit(); 
		}
		else {
			alertify.alert( "[" + frm.res_cd.value + "] " + frm.res_msg.value );
			closeEvent();
			if(frm.res_cd.value == "3001") cp_cancel();
		}
	}

</script>

<?php
    /* ============================================================================== */
    /* =   Javascript source Include                                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */
?>

<script type="text/javascript" src='<?=$g_conf_js_url?>'></script>

<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   Javascript source Include END                                            = */
    /* ============================================================================== */
?>
<script type="text/javascript">
	 
	function jsf__pay( form ) {
		try
		{
			KCP_Pay_Execute( form ); 
		}
		catch (e)
		{
			/* IE 에서 결제 정상종료시 throw로 스크립트 종료 */ 
		}
	}            
	
	function pay_post(){
		jsf__pay(document.order_info);
	}

</script>


<!-- 주문정보 입력 form : order_info -->
<form name="order_info" method="post" action="plugin/kcp/pp_cli_hub.php" target="HFrm" >

<!-- 주문번호 -->
<input type="hidden" name='ordr_idxx' value="" />

<!-- 상품명 -->
<input type="hidden" name='good_name' value='<?php echo($CP_GOODS_NAME)?>' />

<!-- 결제금액 -->
<input type="hidden" name='good_mny'  value='0' />

<!-- 주문자명 -->
<input type="hidden" name='buyr_name' value="" />

<!-- 주문자 연락처1 -->
<input type="hidden" name='buyr_tel1' value='' />

<!-- 휴대폰번호 -->
<input type="hidden" name='buyr_tel2' value='' />

<!-- 주문자 E-mail -->
<input type="hidden" name='buyr_mail' value='' />

<?php
/* ============================================================================== */
/* =   결제 수단 정보 설정                                                 = */
/* = -------------------------------------------------------------------------- = */
/* =   결제에 필요한 결제 수단 정보를 설정합니다.                               = */
/* =                                                                            = */
/* =  신용카드 : 100000000000, 계좌이체 : 010000000000, 가상계좌 : 001000000000 = */
/* =  포인트   : 000100000000, 휴대폰   : 000010000000, 상품권   : 000000001000 = */
/* =                                                                            = */
/* =  위와 같이 설정한 경우 표준웹에서 설정한 결제수단이 표시됩니다.            = */
/* =  표준웹에서 여러 결제수단을 표시하고 싶으신 경우 설정하시려는 결제         = */
/* =  수단에 해당하는 위치에 해당하는 값을 1로 변경하여 주십시오.               = */
/* =                                                                            = */
/* =  예) 신용카드, 계좌이체, 가상계좌를 동시에 표시하고자 하는 경우            = */
/* =  pay_method = "111000000000"                                               = */
/* =  신용카드(100000000000), 계좌이체(010000000000), 가상계좌(001000000000)에  = */
/* =  해당하는 값을 모두 더해주면 됩니다.                                       = */
/* =                                                                            = */
/* = ※ 필수                                                                    = */
/* =  KCP에 신청된 결제수단으로만 결제가 가능합니다.                            = */
/* = -------------------------------------------------------------------------- = */
?>

<!-- 결제수단-->
<input type="hidden" name='pay_method'   value="" />

<?php
    /* ============================================================================== */
    /* =   2. 가맹점 필수 정보 설정                                                 = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수 - 결제에 반드시 필요한 정보입니다.                               = */
    /* =   site_conf_inc.php 파일을 참고하셔서 수정하시기 바랍니다.                 = */
    /* = -------------------------------------------------------------------------- = */
    // 요청종류 : 승인(pay)/취소,매입(mod) 요청시 사용
?>

<!-- 요청 구분 -->
<input type='hidden' name='req_tx'       value='pay' />

<!-- 사이트 코드 -->
<input type="hidden" name='site_cd'      value="<?php echo($g_conf_site_cd)?>" />

<!-- 사이트 이름 --> 
<input type="hidden" name='shop_name'    value="<?php echo($g_conf_site_name)?>" />

<?php
    /*
    할부옵션 : 표준웹에서 카드결제시 최대로 표시할 할부개월 수를 설정합니다.(0 ~ 18 까지 설정 가능)
    ※ 주의  - 할부 선택은 결제금액이 50,000원 이상일 경우에만 가능, 50000원 미만의 금액은 일시불로만 표기됩니다
               예) value 값을 "5" 로 설정했을 경우 => 카드결제시 결제창에 일시불부터 5개월까지 선택가능
    */
?>

<!-- 최대 할부개월수 -->
<input type="hidden" name='quotaopt'     value="<?php echo($payment_install_range)?>" />

<!-- 통화 코드 -->
<input type="hidden" name='currency'     value="WON" />

<?php
    /* ============================================================================== */
    /* =   3. 표준웹 필수 정보(변경 불가)                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   결제에 필요한 주문 정보를 입력 및 설정합니다.                            = */
    /* = -------------------------------------------------------------------------- = */
?>

<!-- 표준웹 설정 정보입니다(변경 불가) -->
<input type="hidden" name="module_type"     value="<?php echo($module_type)?>" />

<!-- 복합 포인트 결제시 넘어오는 포인트사 코드 : OK캐쉬백(SCSK), 베네피아 복지포인트(SCWB) -->
<input type="hidden" name="epnt_issu"       value="" />

<?php
    /* ============================================================================== */
    /* =   3-1. 표준웹 에스크로결제 사용시 필수 정보                        = */
    /* = -------------------------------------------------------------------------- = */
    /* =   결제에 필요한 주문 정보를 입력 및 설정합니다.                            = */
    /* = -------------------------------------------------------------------------- = */
?>

<!-- 에스크로 사용 여부 : 반드시 Y 로 설정 -->
<input type="hidden" name="escw_used"       value="N" />

<!-- 에스크로 결제처리 모드 : 에스크로: Y, 일반: N, KCP 설정 조건: O  -->
<input type="hidden" name="pay_mod"         value="N" />

<!-- 배송 소요일 : 예상 배송 소요일을 입력 -->
<input type="hidden"  name="deli_term" value="05" />

<!-- 장바구니 상품 개수 : 장바구니에 담겨있는 상품의 개수를 입력(good_info의 seq값 참조) -->
<input type="hidden"  name="bask_cntx" value="<?php echo($TOTAL)?>"/>

<!-- 장바구니 상품 상세 정보 (자바 스크립트 샘플 create_goodInfo()가 온로드 이벤트시 설정되는 부분입니다.) -->
<input type="hidden" name="good_info"       value=""/>

<!-- 수취인 이름 -->
<input type="hidden" name="rcvr_name" value="" />

<!-- 수취인 전화번호 -->
<input type="hidden" name="rcvr_tel1" value="" />

<!-- 수취친 휴대폰번호 -->
<input type="hidden" name="rcvr_tel2" value="" />

<!-- 수취인 E-Mail -->
<input type="hidden" name="rcvr_mail" value="" />

<!-- 수취인 우편번호 -->
<input type="hidden" name="rcvr_zipx" value="" />

<!-- 수취인 주소 -->
<input type="hidden" name="rcvr_add1" value="" />

<!-- 수취인 상세 주소 -->
<input type="hidden" name="rcvr_add2" value="" />

<!-- 기타 파라메터 추가 부분 - Start - -->
<input type="hidden" name='param_opt_1'	 value="" />

<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   3-1. 표준웹 에스크로결제 사용시 필수 정보  END                   = */
    /* ============================================================================== */
?>

<input type="hidden" name="res_cd"          value="" />
<input type="hidden" name="res_msg"         value="" />
<input type="hidden" name="enc_info"        value="" />
<input type="hidden" name="enc_data"        value="" />
<input type="hidden" name="ret_pay_method"  value="" />
<input type="hidden" name="tran_cd"         value="" />
<input type="hidden" name="use_pay_method"  value="" />

<!-- 주문정보 검증 관련 정보 : 표준웹 에서 설정하는 정보입니다 -->
<input type="hidden" name="ordr_chk"        value="" />

<!--  현금영수증 관련 정보 : 표준웹 에서 설정하는 정보입니다 -->
<input type="hidden" name="cash_yn"         value="" />
<input type="hidden" name="cash_tr_code"    value="" />
<input type="hidden" name="cash_id_info"    value="" />

<!-- 2012년 8월 18일 정자상거래법 개정 관련 설정 부분 -->
<!-- 제공 기간 설정 0:일회성 1:기간설정(ex 1:2012010120120131)  -->
<input type="hidden" name="good_expr" value="0" />

<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   3. 표준웹 필수 정보 END                                          = */
    /* ============================================================================== */
?>


<!-- 신용카드 결제시 OK캐쉬백 적립 여부를 묻는 창을 설정하는 파라미터 입니다. - 포인트 가맹점의 경우에만 창이 보여집니다.
<input type='hidden' name='save_ocb'        value='Y' />
<input type='hidden' name='kcp_noint'       value='' />
<input type='hidden' name='kcp_noint_quota' value='' />
<!-- 복합 포인트 결제시 넘어오는 포인트사 코드 : OK캐쉬백(SCSK), 복지(SCWB) 
<input type='hidden' name='epnt_issu'       value='' />
<!-- 포인트 결제시 복합 결제(신용카드+포인트) 여부를 결정할 수 있습니다.- N 일경우 복합결제 사용안함-->
<!--<input type="hidden" name="complex_pnt_yn" value="N">-->

<?php
 /* 현금영수증 등록 창을 출력 여부를 설정하는 파라미터 입니다
         ※ Y : 현금영수증 등록 창 출력
         ※ N : 현금영수증 등록 창 출력 안함 
	※ 주의 : 현금영수증 사용 시 KCP 상점관리자 페이지에서 현금영수증 사용 동의를 하셔야 합니다
 */
?>

<!-- 결제창 현금영수증 미사용 -->
<input type="hidden" name="disp_tax_yn"     value="N" />

<!-- 가상계좌 입금 기한 설정하는 파라미터 - 발급일 + 3일  -->
<input type="hidden" name="vcnt_expire_term" value="3" />

<?php
/* KCP는 과세상품과 비과세상품을 동시에 판매하는 업체들의 결제관리에 대한 편의성을 제공해드리고자, 
	   복합과세 전용 사이트코드를 지원해 드리며 총 금액에 대해 복합과세 처리가 가능하도록 제공하고 있습니다
	   복합과세 전용 사이트 코드로 계약하신 가맹점에만 해당이 됩니다
       상품별이 아니라 금액으로 구분하여 요청하셔야 합니다
	   총결제 금액은 과세금액 + 부과세 + 비과세금액의 합과 같아야 합니다. 
	   (good_mny = comm_tax_mny + comm_vat_mny + comm_free_mny)
	
	    <input type="hidden" name="tax_flag"       value="TG03">  <!-- 변경불가	   -->
	    <input type="hidden" name="comm_tax_mny"   value=""    >  <!-- 과세금액	   --> 
        <input type="hidden" name="comm_vat_mny"   value=""    >  <!-- 부가세	   -->
	    <input type="hidden" name="comm_free_mny"  value=""    >  <!-- 비과세 금액 --> */
?>
</form>

