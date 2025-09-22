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
		 echo"<script>opener.top.location.href = '{$url}'; self.close();</script>";
		 exit;
	}

	function logMsgOpener($msg, $type = 'error') {
		if(!is_array($msg)) $msg = addslashes($msg);
		else $msg = join(", ", $msg);
		echo "<script>";
		echo "opener.top.mobile_cp_cancel2();";
		
		if($type=='error') echo "opener.top.alertify.error('{$msg}');";
		else if($type=='log') echo "opener.top.alertify.warning('{$msg}');";
		else if($type=='success') echo "opener.top.alertify.success('{$msg}');";
		
		echo "opener.top.$('#alertify-ok').trigger('click');";
		echo "opener.top.only_num_formatCk();";	
		echo "</script>";
		exit;
	}

	function alertMsgOpener($msg, $url = '') {
		$msg = addslashes($msg);
		echo "<script>";
		echo "opener.top.mobile_cp_cancel2();";
		echo "opener.top.alertify.defaults.url = '{$url}';";
		echo "opener.top.alertify.alert('{$msg}');";		
		echo "</script>";
		exit;
	}

	$mysql = new mysqlClass();

	/* ============================================================================== */
    /* =   PAGE : 지불 요청 및 결과 처리 PAGE                                       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://kcp.co.kr/technique.requestcode.do                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2016   NHN KCP Inc.   All Rights Reserverd.               = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */

    include "site_conf_inc.php";       // 환경설정 파일 include
    require "pp_cli_hub_lib.php";              // library [수정불가]

    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* =   POST 형식 체크부분                                                       = */
    /* = -------------------------------------------------------------------------- = */
    if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    {
        echo("잘못된 경로로 접속하였습니다.");
        exit;
    }
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* =   01. 지불 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx         = $_POST[ "req_tx"         ]; // 요청 종류
    $tran_cd        = $_POST[ "tran_cd"        ]; // 처리 종류
    /* = -------------------------------------------------------------------------- = */
    $cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
    $ordr_idxx      = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호
    $good_name      = $_POST[ "good_name"      ]; // 상품명
    /* = -------------------------------------------------------------------------- = */
    $res_cd         = "";                         // 응답코드
    $res_msg        = "";                         // 응답메시지
    $res_en_msg     = "";                         // 응답 영문 메세지
    $tno            = $_POST[ "tno"            ]; // KCP 거래 고유 번호
    $vcnt_yn        = $_POST[ "vcnt_yn"        ]; // 가상계좌 에스크로 사용 유무
    /* = -------------------------------------------------------------------------- = */
    $buyr_name      = $_POST[ "buyr_name"      ]; // 주문자명
    $buyr_tel1      = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호
    $buyr_tel2      = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호
    $buyr_mail      = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소
    /* = -------------------------------------------------------------------------- = */
    $use_pay_method = $_POST[ "use_pay_method" ]; // 결제 방법
    $bSucc          = "";                         // 업체 DB 처리 성공 여부
    /* = -------------------------------------------------------------------------- = */
    $app_time       = "";                         // 승인시간 (모든 결제 수단 공통)
    $amount         = "";                         // KCP 실제 거래 금액
    $coupon_mny     = "";                         // 쿠폰금액
    /* = -------------------------------------------------------------------------- = */
    $card_cd        = "";                         // 신용카드 코드
    $card_name      = "";                         // 신용카드 명
    $app_no         = "";                         // 신용카드 승인번호
    $noinf          = "";                         // 신용카드 무이자 여부
    $quota          = "";                         // 신용카드 할부개월
    $partcanc_yn    = "";                         // 부분취소 가능유무
    $card_bin_type_01 = "";                       // 카드구분1
    $card_bin_type_02 = "";                       // 카드구분2
    $card_mny       = "";                         // 카드결제금액
    /* = -------------------------------------------------------------------------- = */
    $bank_name      = "";                         // 은행명
    $bank_code      = "";                         // 은행코드
    $bk_mny         = "";                         // 계좌이체결제금액
    /* = -------------------------------------------------------------------------- = */
    $bankname       = "";                         // 입금할 은행명
    $depositor      = "";                         // 입금할 계좌 예금주 성명
    $account        = "";                         // 입금할 계좌 번호
    $va_date        = "";                         // 가상계좌 입금마감시간
    /* = -------------------------------------------------------------------------- = */
    $pnt_issue      = "";                         // 결제 포인트사 코드
    $pnt_amount     = "";                         // 적립금액 or 사용금액
    $pnt_app_time   = "";                         // 승인시간
    $pnt_app_no     = "";                         // 승인번호
    $add_pnt        = "";                         // 발생 포인트
    $use_pnt        = "";                         // 사용가능 포인트
    $rsv_pnt        = "";                         // 총 누적 포인트
    /* = -------------------------------------------------------------------------- = */
    $commid         = "";                         // 통신사 코드
    $mobile_no      = "";                         // 휴대폰 코드
    /* = -------------------------------------------------------------------------- = */
    $shop_user_id   = $_POST[ "shop_user_id"   ]; // 가맹점 고객 아이디
    $tk_van_code    = "";                         // 발급사 코드
    $tk_app_no      = "";                         // 상품권 승인 번호
    /* = -------------------------------------------------------------------------- = */
    $cash_yn        = $_POST[ "cash_yn"        ]; // 현금영수증 등록 여부
    $cash_authno    = "";                         // 현금 영수증 승인 번호
    $cash_tr_code   = $_POST[ "cash_tr_code"   ]; // 현금 영수증 발행 구분
    $cash_id_info   = $_POST[ "cash_id_info"   ]; // 현금 영수증 등록 번호
    $cash_no        = "";                         // 현금 영수증 거래 번호       
    /* ============================================================================== */
    /* =   01-1. 에스크로 지불 요청 정보 설정                                       = */
    /* = -------------------------------------------------------------------------- = */  
    $escw_used      = $_POST[  "escw_used"     ]; // 에스크로 사용 여부
    $pay_mod        = $_POST[  "pay_mod"       ]; // 에스크로 결제처리 모드
    $deli_term      = $_POST[  "deli_term"     ]; // 배송 소요일
    $bask_cntx      = $_POST[  "bask_cntx"     ]; // 장바구니 상품 개수
    $good_info      = $_POST[  "good_info"     ]; // 장바구니 상품 상세 정보
    $rcvr_name      = $_POST[  "rcvr_name"     ]; // 수취인 이름
    $rcvr_tel1      = $_POST[  "rcvr_tel1"     ]; // 수취인 전화번호
    $rcvr_tel2      = $_POST[  "rcvr_tel2"     ]; // 수취인 휴대폰번호
    $rcvr_mail      = $_POST[  "rcvr_mail"     ]; // 수취인 E-Mail
    $rcvr_zipx      = $_POST[  "rcvr_zipx"     ]; // 수취인 우편번호
    $rcvr_add1      = $_POST[  "rcvr_add1"     ]; // 수취인 주소
    $rcvr_add2      = $_POST[  "rcvr_add2"     ]; // 수취인 상세주소
    $escw_yn        = "";                         // 에스크로 여부
    /* = -------------------------------------------------------------------------- = */
    /* =   01. 지불 요청 정보 설정 END                                              = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화(변경 불가)                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =       결제에 필요한 인스턴스를 생성하고 초기화 합니다.                     = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus = new C_PP_CLI;

    $c_PayPlus->mf_clear();
    /* ------------------------------------------------------------------------------ */
    /* =   02. 인스턴스 생성 및 초기화 END                                          = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 승인 요청 정보 설정                                                = */
    /* = -------------------------------------------------------------------------- = */

	$is_mobile = preg_match('/'.MOBILE_AGENT.'/i', $_SERVER['HTTP_USER_AGENT']);
	$order_num	= $ordr_idxx;	

	$addInfo	= explode("|", previlDecode(checkPostVar('param_opt_1')));
	$direct		= $addInfo[0];
	$my_id		= $addInfo[1];

	$Main		= "../../index.php";
	$cart_id	= getCartId($my_id);
	
	if($is_mobile == 1) {
		echo "<script> opener = window.open('', 'orderForm'); </script>";
	}

	$sql = "SELECT pay_total, pay_status FROM mallRN_order_info WHERE order_num = '{$order_num}'";
	$order_infos = $mysql->one_row($sql);

	if($order_infos['pay_status'] == 'C') { 
		if($is_mobile == 1) openerMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
		else parentMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
	}	

	$pay_total	= $order_infos['pay_total'];
	
	############################### 상품수량 체크 ###################################
	if($rtn = checkCartOrder($direct)) {
		if($rtn == 1) {
			
			$card_info = "상품품절 및 재고수량 초과로 결제실패처리";

			$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			if(!$is_mobile) echo "<script>parent.closeEvent();</script>";
			
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
			if(!$is_mobile) echo "<script>parent.closeEvent();</script>";

			if($is_mobile == 1) alertMsgOpener("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
			else alertMsg("선택된 장바구니 상품 정보가 없습니다.", "{$Main}?channel=cart");
		}
	}
	############################### 상품수량 체크 ###################################

    if ( $req_tx == "pay" )
    {
            /* 1원은 실제로 업체에서 결제하셔야 될 원 금액을 넣어주셔야 합니다. 결제금액 유효성 검증 */
            $c_PayPlus->mf_set_ordr_data( "ordr_mony", $pay_total);                                   

            $c_PayPlus->mf_set_encx_data( $_POST[ "enc_data" ], $_POST[ "enc_info" ] );
    }
    /* ------------------------------------------------------------------------------ */
    /* =   03.  처리 요청 정보 설정 END                                             = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   04. 실행                                                                 = */
    /* = -------------------------------------------------------------------------- = */
    if ( $tran_cd != "" )
    {
        $c_PayPlus->mf_do_tx(  "", $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
                              $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                              $cust_ip, $g_conf_log_level, 0, 0, $g_conf_log_path); // 응답 전문 처리

        $res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
        $res_msg = iconv("euc-kr", "utf-8", $c_PayPlus->m_res_msg); // 결과 메시지
        /* $res_en_msg = $c_PayPlus->mf_get_res_data( "res_en_msg" );  // 결과 영문 메세지 */
    }
    else
    {
        $c_PayPlus->m_res_cd  = "9562";
        $c_PayPlus->m_res_msg = "연동 오류| tran_cd값이 설정되지 않았습니다.";
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   04. 실행 END                                                             = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   05. 승인 결과 값 추출                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   수정하지 마시기 바랍니다.                                                = */
    /* = -------------------------------------------------------------------------- = */
    if ( $req_tx == "pay" )
    {
        if( $res_cd == "0000" )
        {
            $tno       = $c_PayPlus->mf_get_res_data( "tno"       ); // KCP 거래 고유 번호
            $amount    = $c_PayPlus->mf_get_res_data( "amount"    ); // KCP 실제 거래 금액
            $pnt_issue = $c_PayPlus->mf_get_res_data( "pnt_issue" ); // 결제 포인트사 코드
            $coupon_mny = $c_PayPlus->mf_get_res_data( "coupon_mny" ); // 쿠폰금액

    /* = -------------------------------------------------------------------------- = */
    /* =   05-1. 신용카드 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "100000000000" )
            {
                $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드사 코드
                //$card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드사 명
				$card_name   = iconv("euc-kr","utf-8",$c_PayPlus->mf_get_res_data( "card_name" )); // 카드 종류
                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인시간
                $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인번호
                $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부
                $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월 수
                $partcanc_yn = $c_PayPlus->mf_get_res_data( "partcanc_yn" ); // 부분취소 가능유무
                $card_bin_type_01 = $c_PayPlus->mf_get_res_data( "card_bin_type_01" ); // 카드구분1
                $card_bin_type_02 = $c_PayPlus->mf_get_res_data( "card_bin_type_02" ); // 카드구분2
                $card_mny = $c_PayPlus->mf_get_res_data( "card_mny" ); // 카드결제금액

                /* = -------------------------------------------------------------- = */
                /* =   05-1.1. 복합결제(포인트+신용카드) 승인 결과 처리             = */
                /* = -------------------------------------------------------------- = */
                if ( $pnt_issue == "SCSK" || $pnt_issue == "SCWB" )
                {
                    $pnt_amount   = $c_PayPlus->mf_get_res_data ( "pnt_amount"   ); // 적립금액 or 사용금액
                    $pnt_app_time = $c_PayPlus->mf_get_res_data ( "pnt_app_time" ); // 승인시간
                    $pnt_app_no   = $c_PayPlus->mf_get_res_data ( "pnt_app_no"   ); // 승인번호
                    $add_pnt      = $c_PayPlus->mf_get_res_data ( "add_pnt"      ); // 발생 포인트
                    $use_pnt      = $c_PayPlus->mf_get_res_data ( "use_pnt"      ); // 사용가능 포인트
                    $rsv_pnt      = $c_PayPlus->mf_get_res_data ( "rsv_pnt"      ); // 총 누적 포인트
                }
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-2. 계좌이체 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "010000000000" )
            {
                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"   );  // 승인 시간
                //$bank_name = $c_PayPlus->mf_get_res_data( "bank_name"  );  // 은행명
				$bank_name = iconv("euc-kr","utf-8",$c_PayPlus->mf_get_res_data( "bank_name"  )); // 은행명
                $bank_code = $c_PayPlus->mf_get_res_data( "bank_code"  );  // 은행코드
                $bk_mny    = $c_PayPlus->mf_get_res_data( "bk_mny"     );  // 계좌이체결제금액
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-3. 가상계좌 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "001000000000" )
            {
				//$bankname  = $c_PayPlus->mf_get_res_data( "bankname"  ); // 입금할 은행 이름
				$bankname  = iconv("euc-kr","utf-8",$c_PayPlus->mf_get_res_data( "bankname"  )); // 입금할 은행 이름
                //$depositor = $c_PayPlus->mf_get_res_data( "depositor" ); // 입금할 계좌 예금주
				$depositor = iconv("euc-kr","utf-8",$c_PayPlus->mf_get_res_data( "depositor" )); // 입금할 계좌 예금주
                $account   = $c_PayPlus->mf_get_res_data( "account"   ); // 입금할 계좌 번호
                $va_date   = $c_PayPlus->mf_get_res_data( "va_date"   ); // 가상계좌 입금마감시간
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-4. 포인트 승인 결과 처리                                              = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "000100000000" )
            {
                $pnt_amount   = $c_PayPlus->mf_get_res_data( "pnt_amount"   ); // 적립금액 or 사용금액
                $pnt_app_time = $c_PayPlus->mf_get_res_data( "pnt_app_time" ); // 승인시간
                $pnt_app_no   = $c_PayPlus->mf_get_res_data( "pnt_app_no"   ); // 승인번호 
                $add_pnt      = $c_PayPlus->mf_get_res_data( "add_pnt"      ); // 발생 포인트
                $use_pnt      = $c_PayPlus->mf_get_res_data( "use_pnt"      ); // 사용가능 포인트
                $rsv_pnt      = $c_PayPlus->mf_get_res_data( "rsv_pnt"      ); // 총 누적 포인트
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-5. 휴대폰 승인 결과 처리                                              = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "000010000000" )
            {
                $app_time  = $c_PayPlus->mf_get_res_data( "hp_app_time"  ); // 승인 시간
                $commid    = $c_PayPlus->mf_get_res_data( "commid"       ); // 통신사 코드
                $mobile_no = $c_PayPlus->mf_get_res_data( "mobile_no"    ); // 휴대폰 번호
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-6. 상품권 승인 결과 처리                                              = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "000000001000" )
            {
                $app_time    = $c_PayPlus->mf_get_res_data( "tk_app_time"  ); // 승인 시간
                $tk_van_code = $c_PayPlus->mf_get_res_data( "tk_van_code"  ); // 발급사 코드
                $tk_app_no   = $c_PayPlus->mf_get_res_data( "tk_app_no"    ); // 승인 번호
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-7. 현금영수증 결과 처리                                               = */
    /* = -------------------------------------------------------------------------- = */
            $cash_authno  = $c_PayPlus->mf_get_res_data( "cash_authno"  ); // 현금 영수증 승인 번호
            $cash_no      = $c_PayPlus->mf_get_res_data( "cash_no"      ); // 현금 영수증 거래 번호            
        }
    /* = -------------------------------------------------------------------------- = */
    /* =   05-8. 에스크로 여부 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
        $escw_yn = $c_PayPlus->mf_get_res_data( "escw_yn"  ); // 에스크로 여부 
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   05. 승인 결과 처리 END                                                   = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   06. 승인 및 실패 결과 DB처리                                             = */
    /* = -------------------------------------------------------------------------- = */
    /* =       결과를 업체 자체적으로 DB처리 작업하시는 부분입니다.                 = */
    /* = -------------------------------------------------------------------------- = */

	if($escw_used == 'Y')	$escw_used2 = 1;
	else					$escw_used2 = 0;

	$CK_CP_DB_ERROR			= "Y";
	
    if ( $req_tx == "pay" )
    {

    /* = -------------------------------------------------------------------------- = */
    /* =   06-1. 승인 결과 DB 처리(res_cd == "0000")                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =        각 결제수단을 구분하시어 DB 처리를 하시기 바랍니다.                 = */
    /* = -------------------------------------------------------------------------- = */
        if( $res_cd == "0000" )
        {
            // 06-1-1. 신용카드
            if ( $use_pay_method == "100000000000" )
            {
                // 06-1-1-1. 복합결제(신용카드 + 포인트)
                if ( $pnt_issue == "SCSK" || $pnt_issue == "SCWB" )
                {
                }
            }
            // 06-1-2. 계좌이체
            if ( $use_pay_method == "010000000000" )
            {
            }
            // 06-1-3. 가상계좌
            if ( $use_pay_method == "001000000000" )
            {
            }
            // 06-1-4. 포인트
            if ( $use_pay_method == "000100000000" )
            {
            }
            // 06-1-5. 휴대폰
            if ( $use_pay_method == "000010000000" )
            {
            }
            // 06-1-6. 상품권
             if ( $use_pay_method == "000000001000" )
            {
            }

			if($pay_total != $amount) {
				$bSucc = "false"; // DB 작업 실패 또는 금액 불일치의 경우 "false" 로 세팅						
			}

			if($bSucc != "false" ) {
				$adds		= '';
				$pay_status	= "C";
				switch($use_pay_method) {
					case "100000000000" : 
						if($noinf == 'Y')	$adds = ',무이자';					
						if($quota == '00')	$quota = '일시불';
						$pay_info	= "{$card_name} ($quota{$adds}), 승인시간 : {$app_time}, 승인번호 : {$app_no}";
					break;
					case "010000000000" : 
						$pay_info	= "은행명 : {$bank_name}, 은행코드 : {$bank_code}, 승인시간 : {$app_time}";		
					break;
					case "001000000000" : 
						$pay_info	= "입금은행명 : {$bankname}, 입금계좌번호 : {$account}, 입금예금주 : {$depositor}";		
						$pay_status	= "B";
					break;
					case "000010000000" : 
						$pay_info	= "승인시간 : {$app_time}, 통신사 : {$commid}, 휴대혼번호 : {$mobile_no}";							
					break;
				}				

				$sql = "UPDATE mallRN_order_info SET pay_status = '{$pay_status}', pay_info = '{$pay_info}', pay_number = '{$tno}', escrow = '{$escw_used2}', reals = 1 WHERE order_num = '{$order_num}'";
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

        }
    /* = -------------------------------------------------------------------------- = */
    /* =   06.-2 승인 및 실패 결과 DB처리                                             = */
    /* ============================================================================== */
    
        else if ( $req_cd != "0000" )
        {

			$card_info = "거래번호 : {$tno}, 결제금액 : {$amount}, 결과 메세지 : [{$res_cd}] {$res_msg}";

			$sql = "UPDATE mallRN_order_info SET pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$tno}', escrow='{$escw_used2}' WHERE order_num = '{$order_num}'";
			$mysql->query($sql);

			if($is_mobile == 1) {
				logMsgOpener("결제가 실패 되었습니다. [{$res_msg}]");
			}
			else {
				echo "<script>parent.closeEvent();</script>";
				logMsg("결제가 실패 되었습니다. [{$res_msg}]");
			}
        }
    }
    /* = -------------------------------------------------------------------------- = */
    /* =   06. 승인 및 실패 결과 DB 처리 END                                        = */
    /* = ========================================================================== = */


    /* = ========================================================================== = */
    /* =   07. 승인 결과 DB 처리 실패시 : 자동취소                                  = */
    /* = -------------------------------------------------------------------------- = */
    /* =      승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해         = */
    /* =      DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로          = */
    /* =      승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                   = */
    /* =                                                                            = */
    /* =      DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"        = */
    /* =      로 설정해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의    = */
    /* =      값을 설정하시면 됩니다.)                                              = */
    /* = -------------------------------------------------------------------------- = */
    
    // 승인 결과 DB 처리 에러시 bSucc값을 false로 설정하여 거래건을 취소 요청
    
    if ( $req_tx == "pay" )
    {
        if( $res_cd == "0000" )
        {
            if ( $bSucc == "false" )
            {
                $c_PayPlus->mf_clear();

                $tran_cd = "00200000";

    /* ============================================================================== */
    /* =   07-1.자동취소시 에스크로 거래인 경우                                     = */
    /* = -------------------------------------------------------------------------- = */
                // 취소시 사용하는 mod_type
                $bSucc_mod_type = "";

                // 에스크로 가상계좌 건의 경우 가상계좌 발급취소(STE5)
                if ( $escw_yn == "Y" && $use_pay_method == "001000000000" )
                {
                    $bSucc_mod_type = "STE5";
                }
                // 에스크로 가상계좌 이외 건은 즉시취소(STE2)
                else if ( $escw_yn == "Y" )
                {
                    $bSucc_mod_type = "STE2";
                }
                // 에스크로 거래 건이 아닌 경우(일반건)(STSC)
                else
                {
                    $bSucc_mod_type = "STSC"; 
                }
    /* = -------------------------------------------------------------------------- = */
    /* =   07-1. 자동취소시 에스크로 거래인 경우 처리 END                           = */
    /* = ========================================================================== = */
                
                $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
                $c_PayPlus->mf_set_modx_data( "mod_type", $bSucc_mod_type              );  // 원거래 변경 요청 종류
                $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
                $c_PayPlus->mf_set_modx_data( "mod_desc", "가맹점 결과 처리 오류 - 가맹점에서 취소 요청" );  // 변경 사유

                $c_PayPlus->mf_do_tx(  "", $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
                              $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                              $cust_ip, $g_conf_log_level, 0, 0, $g_conf_log_path);

                $res_cd  = $c_PayPlus->m_res_cd;
				$res_msg = iconv("euc-kr", "utf-8", $c_PayPlus->m_res_msg); // 결과 메시지

				$card_info = "내부 오류로 인해 결제가 취소 되었습니다. 결과 메세지 : [{$res_cd}] {$res_msg}";

				$sql	= "UPDATE mallRN_order_info SET reals = 0,  pay_status = 'D' , pay_info = '{$card_info}', pay_number = '{$tno}' WHERE order_num = '{$order_num}'";
				$mysql->query($sql);

				$sql	= "UPDATE mallRN_order_goods SET reals = 0 WHERE order_num = '{$order_num}'";
				$mysql->query($sql);

				if($is_mobile == 1) {
					logMsgOpener("내부 오류로 인해 결제가 취소 되었습니다.");
				}
				else {
					echo "<script>parent.closeEvent();</script>";
					logMsg("내부 오류로 인해 결제가 취소 되었습니다.");
				}
            }
        }
    }
        // End of [res_cd = "0000"]
    /* = -------------------------------------------------------------------------- = */
    /* =   07. 승인 결과 DB 처리 END                                                = */
    /* = ========================================================================== = */


    /* ============================================================================== */
    /* =   08. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */

	if($direct)	$where	= " && direct = 1";
	else 		$where	= " && selects = 1";
	
	$sql		= "DELETE FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where}";
	$mysql->query($sql);

	if($is_mobile == 1) openerMovePage("{$Main}?channel=order_ok&order_num={$order_num}");
	else parentMovePage("{$Main}?channel=order_ok&order_num={$order_num}");

?>