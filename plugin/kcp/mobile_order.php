<?php
    /* ============================================================================== */
    /* =   PAGE : 결제 요청 PAGE                                             = */
    /* = -------------------------------------------------------------------------- = */
    /* =   아래의 ※ 필수, ※ 옵션 부분과 매뉴얼을 참조하셔서 연동을   = */
    /* =   진행하여 주시기 바랍니다.                                          = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://kcp.co.kr/technique.requestcode.do			        = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2016  NHN KCP Inc.   All Rights Reserverd.                = */
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php 파일을 수정하시기 바랍니다.    = */
    /* = -------------------------------------------------------------------------- = */
	
	@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
	header("Content-Type: text/html; charset=euc-kr");

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

?>
<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */
?>
<?php
    /* kcp와 통신후 kcp 서버에서 전송되는 결제 요청 정보 */
    $req_tx          = $_POST[ "req_tx"         ]; // 요청 종류         
    $res_cd          = $_POST[ "res_cd"         ]; // 응답 코드         
    $tran_cd         = $_POST[ "tran_cd"        ]; // 트랜잭션 코드     
    $ordr_idxx       = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호   
    $good_name       = $_POST[ "good_name"      ]; // 상품명            
    $good_mny        = $_POST[ "good_mny"       ]; // 결제 총금액       
    $buyr_name       = $_POST[ "buyr_name"      ]; // 주문자명          
    $buyr_tel1       = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호   
    $buyr_tel2       = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호
    $buyr_mail       = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소
    $use_pay_method  = $_POST[ "use_pay_method" ]; // 결제 방법          
    $enc_info        = $_POST[ "enc_info"       ]; // 암호화 정보       
    $enc_data        = $_POST[ "enc_data"       ]; // 암호화 데이터     
    $cash_yn         = $_POST[ "cash_yn"        ];
    $cash_tr_code    = $_POST[ "cash_tr_code"   ];
	/* 에스크로 지불 요청 정보 설정 */
	$escw_used      = $_POST[ "escw_used"   ];    // 에스크로 사용 여부
    $pay_mod        = $_POST[ "pay_mod"   ];   // 에스크로 결제처리 모드
    $deli_term      = $_POST[ "deli_term"   ];   // 배송 소요일
    $bask_cntx      = $_POST[ "bask_cntx"   ];    // 장바구니 상품 개수
    $good_info      = $_POST[ "good_info"   ];    // 장바구니 상품 상세 정보
    $rcvr_name      = $_POST[ "rcvr_name"   ];    // 수취인 이름
    $rcvr_tel1      = $_POST[ "rcvr_tel1"   ];    // 수취인 전화번호
    $rcvr_tel2      = $_POST[ "rcvr_tel2"   ];    // 수취인 휴대폰번호
	$rcvr_mail      = $_POST[ "rcvr_mail"   ];    // 수취인 E-Mail
    $rcvr_zipx      = $_POST[ "rcvr_zipx"   ];    // 수취인 우편번호
    $rcvr_add1      = $_POST[ "rcvr_add1"   ];    // 수취인 주소
    $rcvr_add2      = $_POST[ "rcvr_add2"   ];    // 수취인 상세주소
    /* 기타 파라메터 추가 부분 - Start - */
    $param_opt_1    = $_POST[ "param_opt_1"     ]; // 기타 파라메터 추가 부분
    $param_opt_2    = $_POST[ "param_opt_2"     ]; // 기타 파라메터 추가 부분
    $param_opt_3    = $_POST[ "param_opt_3"     ]; // 기타 파라메터 추가 부분
    /* 기타 파라메터 추가 부분 - End -   */

	$tablet_size     = "1.0"; // 화면 사이즈 고정
	$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
	$url = $HeaderProtocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<title>KCP 결제</title>

	<link type="text/css" rel="stylesheet" href="//cdn.jsdelivr.net/npm/xeicon@2.3.3/xeicon.min.css" />
	  
	<!-- 공통: font preload -->
	<link rel="preload" href="//cdn.kcp.co.kr/font/NotoSansCJKkr-Regular.woff" type="font/woff" as="font" crossorigin>
	<link rel="preload" href="//cdn.kcp.co.kr/font/NotoSansCJKkr-Medium.woff" type="font/woff" as="font" crossorigin>
	<link rel="preload" href="//cdn.kcp.co.kr/font/NotoSansCJKkr-Bold.woff" type="font/woff" as="font" crossorigin>
	<!-- //공통: font preload -->
	  
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=yes, target-densitydpi=medium-dpi">  
	<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" /> 
	<meta http-equiv="Pragma" content="no-cache"> 
	<meta http-equiv="Expires" content="-1">
	<link href="style.css" rel="stylesheet" type="text/css" id="cssLink"/>

	<!-- 공통: js -->
	<script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="js/front.js"></script>	
	<!-- //공통: js -->  

	<!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
	<script type="text/javascript" src="js/approval_key.js?t=1"></script>

	<script type="text/javascript">
		/* kcp web 결제창 호츨 (변경불가) */
		function call_pay_form() {
			var v_frm = document.order_info;
			
			v_frm.action = PayUrl;

			if (v_frm.Ret_URL.value == "") {
			  /* Ret_URL값은 현 페이지의 URL 입니다. */
			  //alert("연동시 Ret_URL을 반드시 설정하셔야 됩니다.");
			  return false;
			}
			else {
			  v_frm.submit();
			}
		}

		/* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청 (변경불가) */
		function chk_pay() {
			self.name = "tar_opener";
			var pay_form = document.pay_form;

			if (pay_form.res_cd.value == "3001" )
			{
			  //alertify.alert( "[" + frm.res_cd.value + "] " + frm.res_msg.value );
			  alert("사용자 취소");
			  pay_form.res_cd.value = "";

			  opener = window.open("", "orderForm");

			  opener.top.mobile_cp_cancel('<?=$ordr_idxx?>');
			  self.close();
			}

			if (pay_form.enc_info.value)
			  pay_form.submit();
		}

		function pay_post(){
			kcp_AJAX();			
		}	
		
		window.onpageshow = function(event) {
			if ( event.persisted || (window.performance && window.performance.navigation.type == 2)) {
				parent.mobile_cp_cancel2();
			}
		}

	</script>
</head>
<body onload="chk_pay();">
	<center>
		<div style="margin-top:100px; width:80%; padding:50px 0; border:1px solid #dadada; background:#fafafa; border-radius:6px; font-size:20px; ">
			<i class="xi-browser-text xi-5x"></i>
			<br /><br />
			결제진행 중 입니다.
		</div>
	</center>

	<!-- 주문정보 입력 form : order_info -->
	<form name="order_info" method="post" action="pp_cli_hub.php" accept-charset="euc-kr" target="_blank">

	<!-- 주문번호 -->
	<input type="hidden" name='ordr_idxx' value="">

	<!-- 상품명 -->
	<input type="hidden" name='good_name' value='' />

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
	<!-- 공통정보 -->
	<input type="hidden" name="req_tx"          value="pay">                           <!-- 요청 구분 -->
	<input type="hidden" name="shop_name"       value="<?= $g_conf_site_name ?>">      <!-- 사이트 이름 --> 
	<input type="hidden" name="site_cd"         value="<?= $g_conf_site_cd   ?>">      <!-- 사이트 코드 -->
	<input type="hidden" name="currency"        value="410"/>                          <!-- 통화 코드 -->
	<!-- 결제등록 키 -->
	<input type="hidden" name="approval_key"    id="approval">
	<!-- 인증시 필요한 파라미터(변경불가)-->
	<input type="hidden" name="pay_method"      value="">
	<input type="hidden" name="van_code"        value="">
	<!-- 신용카드 설정 -->
	<input type="hidden" name="quotaopt"        value=""/>                           <!-- 최대 할부개월수 -->
	<!-- 가상계좌 설정 -->
	<input type="hidden" name="ipgm_date"       value=""/>
	<!-- 리턴 URL (kcp와 통신후 결제를 요청할 수 있는 암호화 데이터를 전송 받을 가맹점의 주문페이지 URL) -->
	<input type="hidden" name="Ret_URL"         value="<?=$url?>">
	<!-- 화면 크기조정 -->
	<input type="hidden" name="tablet_size"     value="<?=$tablet_size?>">

	<!-- 추가 파라미터 ( 가맹점에서 별도의 값전달시 param_opt 를 사용하여 값 전달 ) -->
	<input type="hidden" name="param_opt_1"     value="">
	<input type="hidden" name="param_opt_2"     value="">
	<input type="hidden" name="param_opt_3"     value="">

	<?php
		/* ============================================================================== */
		/* =   에스크로결제 사용시 필수 정보                                            = */
		/* = -------------------------------------------------------------------------- = */
		/* =   결제에 필요한 주문 정보를 입력 및 설정합니다.                            = */
		/* = -------------------------------------------------------------------------- = */
	?>
	  <!-- 에스크로 사용유무 에스크로 사용 업체(가상계좌, 계좌이체 해당)는 escw_used 를 Y로 세팅 해주시기 바랍니다.-->
	  <input type="hidden" name="escw_used" value="">
	  <!-- 장바구니 상품 개수 -->
	  <input type='hidden' name='bask_cntx' value="">
	  <!-- 장바구니 정보(상단 스크립트 참조) -->
	  <input type='hidden' name='good_info' value="">
	  <!-- 에스크로 결제처리모드 KCP 설정된 금액 결제(사용 : 설정된금액적용: 사용안함: -->
	  <input type="hidden" name='pay_mod'   value="">
	  <!-- 배송소요기간 -->
	  <input type="hidden" name='deli_term' value='05'>

	<?php
		/* = -------------------------------------------------------------------------- = */
		/* =   에스크로결제 사용시 필수 정보  END                                       = */
		/* ============================================================================== */
	?>
	<?php
		/* ============================================================================== */
		/* =   옵션 정보                                                                = */
		/* = -------------------------------------------------------------------------- = */
		/* =   ※ 옵션 - 결제에 필요한 추가 옵션 정보를 입력 및 설정합니다.             = */
		/* = -------------------------------------------------------------------------- = */
		/* 카드사 리스트 설정
		예) 비씨카드와 신한카드 사용 설정시
		<input type="hidden" name='used_card'    value="CCBC:CCLG">

		/*  무이자 옵션
				※ 설정할부    (가맹점 관리자 페이지에 설정 된 무이자 설정을 따른다)                             - "" 로 설정
				※ 일반할부    (KCP 이벤트 이외에 설정 된 모든 무이자 설정을 무시한다)                           - "N" 로 설정
				※ 무이자 할부 (가맹점 관리자 페이지에 설정 된 무이자 이벤트 중 원하는 무이자 설정을 세팅한다)   - "Y" 로 설정
		<input type="hidden" name="kcp_noint"       value=""/> */

		/*  무이자 설정
				※ 주의 1 : 할부는 결제금액이 50,000 원 이상일 경우에만 가능
				※ 주의 2 : 무이자 설정값은 무이자 옵션이 Y일 경우에만 결제 창에 적용
				예) BC 2,3,6개월, 국민 3,6개월, 삼성 6,9개월 무이자 : CCBC-02:03:06,CCKM-03:06,CCSS-03:06:04
		<input type="hidden" name="kcp_noint_quota" value="CCBC-02:03:06,CCKM-03:06,CCSS-03:06:09"/> */

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
			
		/* 결제창 한국어/영어 설정 옵션 (Y : 영어)
			<input type="hidden" name="eng_flag"        value="Y"/> */
			  
		/* 가맹점에서 관리하는 고객 아이디 설정을 해야 합니다. 상품권 결제 시 반드시 입력하시기 바랍니다.
			<input type="hidden" name="shop_user_id"    value=""/> */
			
		/* 복지포인트 결제시 가맹점에 할당되어진 코드 값을 입력해야합니다. 
			<input type="hidden" name="pt_memcorp_cd"   value=""/> */
			
		/* 결제창 현금영수증 노출 설정 옵션 (Y : 노출)
			<input type="hidden" name="disp_tax_yn"     value="Y"/> */
			
		/* = -------------------------------------------------------------------------- = */
		/* =   옵션 정보 END                                                            = */
		/* ============================================================================== */
	?>

	</form>

	<form name="pay_form" method="post" action="pp_cli_hub.php">
		<input type="hidden" name="req_tx"         value="<?=$req_tx?>">               <!-- 요청 구분          -->
		<input type="hidden" name="res_cd"         value="<?=$res_cd?>">               <!-- 결과 코드          -->
		<input type="hidden" name="tran_cd"        value="<?=$tran_cd?>">              <!-- 트랜잭션 코드      -->
		<input type="hidden" name="ordr_idxx"      value="<?=$ordr_idxx?>">            <!-- 주문번호           -->
		<input type="hidden" name="good_mny"       value="<?=$good_mny?>">             <!-- 휴대폰 결제금액    -->
		<input type="hidden" name="good_name"      value="<?=$good_name?>">            <!-- 상품명             -->
		<input type="hidden" name="buyr_name"      value="<?=$buyr_name?>">            <!-- 주문자명           -->
		<input type="hidden" name="buyr_tel1"      value="<?=$buyr_tel1?>">            <!-- 주문자 전화번호    -->
		<input type="hidden" name="buyr_tel2"      value="<?=$buyr_tel2?>">            <!-- 주문자 휴대폰번호  -->
		<input type="hidden" name="buyr_mail"      value="<?=$buyr_mail?>">            <!-- 주문자 E-mail      -->
		<input type="hidden" name="cash_yn"		   value="<?=$cash_yn?>">              <!-- 현금영수증 등록여부-->
		<input type="hidden" name="enc_info"       value="<?=$enc_info?>">
		<input type="hidden" name="enc_data"       value="<?=$enc_data?>">
		<input type="hidden" name="use_pay_method" value="<?=$use_pay_method?>">
		<input type="hidden" name="cash_tr_code"   value="<?=$cash_tr_code?>">
		
		 <!-- 에스크로 정보 -->
		<input type="hidden" name="escw_used"         value="<?=$escw_used ?>">  <!-- 에스크로 사용여부 -->
		<input type="hidden" name="deli_term"         value="<?=$deli_term ?>">  <!-- 배송 소요일 -->
		<input type="hidden" name="bask_cntx"         value="<?=$bask_cntx ?>">  <!-- 장바구니 상품 개수 -->
		<input type="hidden" name="good_info"         value="<?=$good_info ?>">  <!-- 장바구니 상품 상세 정보 -->
		<input type="hidden" name="rcvr_name"         value="<?=$rcvr_name ?>">  <!-- 수취인 이름 -->
		<input type="hidden" name="rcvr_tel1"         value="<?=$rcvr_tel1 ?>">  <!-- 수취인 전화번호 -->
		<input type="hidden" name="rcvr_tel2"         value="<?=$rcvr_tel2 ?>">  <!-- 수취인 휴대폰번호 -->
		<input type="hidden" name="rcvr_mail"         value="<?=$rcvr_mail ?>">  <!-- 수취인 E-Mail -->
		<input type="hidden" name="rcvr_zipx"         value="<?=$rcvr_zipx ?>">  <!-- 수취인 우편번호 -->
		<input type="hidden" name="rcvr_add1"         value="<?=$rcvr_add1 ?>">  <!-- 수취인 주소 -->
		<input type="hidden" name="rcvr_add2"         value="<?=$rcvr_add2 ?>">  <!-- 수취인 상세주소 -->

		<!-- 추가 파라미터 -->
		<input type="hidden" name="param_opt_1"	   value="<?=$param_opt_1?>">
		<input type="hidden" name="param_opt_2"	   value="<?=$param_opt_2?>">
		<input type="hidden" name="param_opt_3"	   value="<?=$param_opt_3?>">
	</form>
</body>
</html>
