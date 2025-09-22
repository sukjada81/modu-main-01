<?php
    /* ============================================================================== */
    /* =   PAGE : 주문취소 PAGE                                               = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.02.   KCP Inc.   All Rights Reserved.                = */
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* = 라이브러리 및 사이트 정보 include                                          = */
    /* = -------------------------------------------------------------------------- = */
	include "site_conf_inc.php";       // 환경설정 파일 include
    require "pp_cli_hub_lib.php";              // library [수정불가]
    /* ============================================================================== */
	

    /* ============================================================================== */
    /* =   01. 요청 정보 설정                                                       = */
    /* = -------------------------------------------------------------------------- = */
   
    /* = -------------------------------------------------------------------------- = */
	 $cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화                                              = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus = new C_PP_CLI;
    $c_PayPlus->mf_clear();
    /* ============================================================================== */


    /* = ========================================================================== = */
    /* =   03. 주문취소							                                  = */
    /* = -------------------------------------------------------------------------- = */
    
    $tran_cd = "00200000";

	// 취소시 사용하는 mod_type
	$bSucc_mod_type = $cancel_mode_type;
	
	$c_PayPlus->mf_set_modx_data( "tno"			, $cancel_tno					);  // KCP 원거래 거래번호
	$c_PayPlus->mf_set_modx_data( "mod_type"	, $bSucc_mod_type				);  // 원거래 변경 요청 종류
	$c_PayPlus->mf_set_modx_data( "mod_ip"		, $cust_ip						);  // 변경 요청자 IP
	$c_PayPlus->mf_set_modx_data( "mod_desc"	, $cancel_msg					);  // 변경 사유
	$c_PayPlus->mf_set_modx_data( "rem_mny"     , strval($cancel_rem_mny)		);  // 취소 가능 잔액
    $c_PayPlus->mf_set_modx_data( "mod_mny"     , strval($cancel_mod_mny)			);  // 취소 요청 금액

	$c_PayPlus->mf_do_tx(  "", $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "", $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx, $cust_ip, $g_conf_log_level, 0, 0, $g_conf_log_path);

	$res_cd  = $c_PayPlus->m_res_cd;
	$res_msg = iconv("euc-kr", "utf-8", $c_PayPlus->m_res_msg); // 결과 메시지

	if($res_cd != '0000') {
		$status = 1;
		$rtns	= "|*|FALSE";
		$rem_mny = 0;
	}
	else {
		$status = 0;
		$rtns	= "|*|SUCCESS";
	
		$rem_mny = $c_PayPlus->mf_get_res_data( "panc_rem_mny" ); // 취소요청후 잔액
		if(!$rem_mny) $rem_mny = $cancel_rem_mny2;
	}

	$sql	= "INSERT INTO mallRN_order_cancel_cp_log SET
					order_num		= '{$order_num}',
					og_uid			= '{$og_uid}',
					price			= '{$cancel_mod_mny}',
					rem_price		= '{$rem_mny}',
					pay_type		= '{$cancel_pay_type}',					
					pay_number		= '{$cancel_tno}',
					status			= '{$status}',
					message			= '{$res_msg}',
					signdate		= '{$signdate}'
				";
	$mysql->query($sql);

    /* ============================================================================== */
    /* =   03. 인스턴스 CleanUp                                                     = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus->mf_clear();
    /* ============================================================================== */

	echo $rtns;
	exit;
?>