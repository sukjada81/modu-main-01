<?php
	@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
	header("Content-Type: text/html; charset=euc-kr");

    /* ============================================================================== */
    /* =   PAGE : 에스크로 구매확인 후 취소 요청 및 결과 처리 PAGE                  = */
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
    /* =   01. 구매후 취소 요청 정보 설정                                           = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx            = $_POST[ "req_tx"           ]; // 요청종류
    $cust_ip           = getenv( "REMOTE_ADDR"      ); // 요청 IP
    $tran_cd           = "";
    $res_cd            = "";                                                       // 응답코드
    $res_msg           = "";                                                       // 응답메시지
    /* ============================================================================== */
    $mod_type          = $_POST[ "mod_type"         ]; // 변경수단 
    $tno               = $_POST[ "tno"              ]; // 거래번호
    $mod_desc          = $_POST[ "mod_desc"         ]; // 취소사유
    $mod_depositor     = $_POST[ "mod_depositor"    ]; // 환불계좌주명(환불시에만 사용)
    $mod_account       = $_POST[ "mod_account"      ]; // 환불계좌번호(환불시에만 사용)
    $mod_bankcode      = $_POST[ "mod_bankcode"     ]; // 환불은행코드(환불시에만 사용)
    $mod_sub_type      = $_POST[ "mod_sub_type"     ]; // 취소상세구분
    $sub_mod_type      = $_POST[ "sub_mod_type"     ]; // 취소유형
    /* ============================================================================== */
    $vcnt_yn           = $_POST[ "vcnt_yn"          ]; // 상태변경시 계좌이체, 가상계좌 여부
    /* = -------------------------------------------------------------------------- = */
    $y_rem_mny         = $_POST[ "rem_mny"          ]; // 환불 가능 금액
    $y_mod_mny         = $_POST[ "mod_mny"          ]; // 환불 금액
    $y_tax_mny         = $_POST[ "tax_mny"          ]; // 부분취소 과세금액
    $y_free_mod_mny    = $_POST[ "free_mod_mny"     ]; // 부분취소 비과세금액
    $y_add_tax_mny     = $_POST[ "add_tax_mny"      ]; // 부분취소 부과세 금액
    $y_refund_account  = $_POST[ "a_refund_account" ]; // 환불계좌번호
    $y_refund_nm       = $_POST[ "a_refund_nm"      ]; // 환불계좌주명
    $y_bank_code       = $_POST[ "a_bank_code"      ]; // 은행코드
    $y_mod_desc_cd     = $_POST[ "mod_desc_cd"      ]; // 취소구분
    $y_mod_desc        = $_POST[ "mod_desc"         ]; // 취소사유
    /* = -------------------------------------------------------------------------- = */
    /* =   01. 구매후 취소 요청 정보 설정 END                                       = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화(변경 불가)                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =               결제에 필요한 인스턴스를 생성하고 초기화 합니다.             = */
    /* =               ※ 주의 ※ 이 부분은 변경하지 마십시오                       = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus  = new C_PP_CLI;
    $c_PayPlus->mf_clear();
    /* ------------------------------------------------------------------------------ */
    /* =   02. 인스턴스 생성 및 초기화 END                                          = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 에스크로 상태변경 요청                                             = */
    /* = -------------------------------------------------------------------------- = */
    if ( $req_tx == "mod_escrow" )
    {
        $c_PayPlus->mf_set_modx_data( "tno",        $_POST[ "tno"       ] );      // KCP 원거래 거래번호
        $c_PayPlus->mf_set_modx_data( "mod_ip",     $cust_ip              );      // 변경 요청자 IP
        $c_PayPlus->mf_set_modx_data( "mod_desc",   $_POST[ "mod_desc"  ] );      // 변경 사유

        if( $mod_type == "STE9_A"  || $mod_type == "STE9_AP" ||
            $mod_type == "STE9_AR" || $mod_type == "STE9_V"  ||
            $mod_type == "STE9_VP" )
        {
            $tran_cd = "70200200";
            $c_PayPlus->mf_set_modx_data( "mod_type"    , "STE9"         );
            $c_PayPlus->mf_set_modx_data( "mod_desc_cd" , $y_mod_desc_cd );
            $c_PayPlus->mf_set_modx_data( "mod_desc"    , $y_mod_desc    );

            if( $mod_type == "STE9_A")
            {
                $c_PayPlus->mf_set_modx_data( "sub_mod_type"    , "STSC"            );
                $c_PayPlus->mf_set_modx_data( "mod_sub_type"    , "MDSC03"          );
            }
            else if( $mod_type == "STE9_AP")
            {
                $c_PayPlus->mf_set_modx_data( "sub_mod_type"    , "STPC"            );
                $c_PayPlus->mf_set_modx_data( "part_canc_yn"    , "Y"               );
                $c_PayPlus->mf_set_modx_data( "rem_mny"         , $y_rem_mny        );
                $c_PayPlus->mf_set_modx_data( "amount"          , $y_mod_mny        );
                $c_PayPlus->mf_set_modx_data( "mod_mny"         , $y_mod_mny        );
                $c_PayPlus->mf_set_modx_data( "mod_sub_type"    , "MDSC04"          );
                //$c_PayPlus->mf_set_modx_data( "tax_flag"        , "TG03"            ); // 복합과세 부분취소
                //$c_PayPlus->mf_set_modx_data( "mod_tax_mny"     , $y_tax_mny        ); // 공급가 부분취소 금액
                //$c_PayPlus->mf_set_modx_data( "mod_free_mny"    , $y_free_mod_mny   ); // 비과세 부분취소 금액
                //$c_PayPlus->mf_set_modx_data( "mod_vat_mny"     , $y_add_tax_mny    ); // 부가세 부분취소 금액
            }
            else if( $mod_type == "STE9_AR")
            {
                $c_PayPlus->mf_set_modx_data( "sub_mod_type"    , "STHD"            );
                $c_PayPlus->mf_set_modx_data( "mod_mny"         , $y_mod_mny        );
                $c_PayPlus->mf_set_modx_data( "mod_sub_type"    , "MDSC04"          );
                $c_PayPlus->mf_set_modx_data( "mod_bankcode"    , $y_bank_code      );
                $c_PayPlus->mf_set_modx_data( "mod_account"     , $y_refund_account );
                $c_PayPlus->mf_set_modx_data( "mod_depositor"   , $y_refund_nm      );
            }
            else if( $mod_type == "STE9_V")
            {
                $c_PayPlus->mf_set_modx_data( "sub_mod_type"    , "STHD"            );
                $c_PayPlus->mf_set_modx_data( "mod_mny"         , $y_mod_mny        );
                $c_PayPlus->mf_set_modx_data( "mod_sub_type"    , "MDSC00"          );
                $c_PayPlus->mf_set_modx_data( "mod_bankcode"    , $y_bank_code      );
                $c_PayPlus->mf_set_modx_data( "mod_account"     , $y_refund_account );
                $c_PayPlus->mf_set_modx_data( "mod_depositor"   , $y_refund_nm      );
            }
            else if( $mod_type == "STE9_VP")
            {
                $c_PayPlus->mf_set_modx_data( "sub_mod_type"    , "STPD"            );
                $c_PayPlus->mf_set_modx_data( "mod_mny"         , $y_mod_mny        );
				$c_PayPlus->mf_set_modx_data( "rem_mny"         , $y_rem_mny        ); 
                $c_PayPlus->mf_set_modx_data( "mod_sub_type"    , "MDSC04"          );
                $c_PayPlus->mf_set_modx_data( "mod_bankcode"    , $y_bank_code      );
                $c_PayPlus->mf_set_modx_data( "mod_account"     , $y_refund_account );
                $c_PayPlus->mf_set_modx_data( "mod_depositor"   , $y_refund_nm      );
                //$c_PayPlus->mf_set_modx_data( "tax_flag"        , "TG03"            ); // 복합과세 부분취소                   
                //$c_PayPlus->mf_set_modx_data( "mod_tax_mny"     , $y_tax_mny        ); // 공급가 부분취소 금액
                //$c_PayPlus->mf_set_modx_data( "mod_free_mny"    , $y_free_mod_mny   ); // 비과세 부분취소 금액
                //$c_PayPlus->mf_set_modx_data( "mod_vat_mny"     , $y_add_tax_mny    ); // 부가세 부분취소 금액
                $c_PayPlus->mf_set_modx_data( "part_canc_yn"    , "Y"               );
            }
        }
        else
        {
            $tran_cd = "00200000";

            $c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type                           );      // 원거래 변경 요청 종류

            if ( $mod_type == "STE1")                                                                  // 상태변경 타입이 [배송요청]인 경우
            {
                $c_PayPlus->mf_set_modx_data( "deli_numb", $_POST[ "deli_numb" ] );      // 운송장 번호
                $c_PayPlus->mf_set_modx_data( "deli_corp", $_POST[ "deli_corp" ] );      // 택배 업체명
            }
            if ( $mod_type == "STE2" || $mod_type == "STE4" )                                       // 상태변경 타입이 [즉시취소] 또는 [취소]인 계좌이체, 가상계좌의 경우
            {
                if ( $vcnt_yn == "Y" )
                {
                    $c_PayPlus->mf_set_modx_data( "refund_account", $mod_account    );  // 환불수취계좌번호
                    $c_PayPlus->mf_set_modx_data( "refund_nm",      $mod_depositor  );  // 환불수취계좌주명
                    $c_PayPlus->mf_set_modx_data( "bank_code",      $mod_bankcode      );  // 환불수취은행코드
                }
            }
        }
    }
    /* = -------------------------------------------------------------------------- = */
    /* =   03. 에스크로 상태변경 요청 END                                           = */
    /* = -------------------------------------------------------------------------- = */
     
    /* ============================================================================== */
    /* =   04. 실행                                                                 = */
    /* = -------------------------------------------------------------------------- = */
    if ( $tran_cd != "" )
    {

        $c_PayPlus->mf_do_tx( "", $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
                              $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                              $cust_ip, $g_conf_log_level, 0, 0, $g_conf_log_path); // 응답 전문 처리

        $res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
        $res_msg = $c_PayPlus->m_res_msg; // 결과 메시지

    }
    else
    {
        $c_PayPlus->m_res_cd  = "9562";
        $c_PayPlus->m_res_msg = "연동 오류|Payplus Plugin이 설치되지 않았거나 tran_cd값이 설정되지 않았습니다.";
    }
    
    /* = -------------------------------------------------------------------------- = */
    /* =   04. 실행 END                                                             = */
    /* ============================================================================== */


    /* ================================================================================== */
    /* =   05.구매확인 후 취소 성공 결과 처리										    = */
    /* = ------------------------------------------------------------------------------ = */
    if ( $req_tx == "mod" )
    {
        if( $res_cd == "0000" )
        {
        } // End of [res_cd = "0000"]


    /* ================================================================================== */
    /* =   05.구매확인 후 취소 실패 결과 처리                                          = */
    /* ================================================================================== */
        else
        {
        }
    } // End of Process


    //* ============================================================================= */
    /* =   05. 폼 구성 및 결과페이지 호출                                           = */
    /* = -------------------------------------------------------------------------- = */

?>