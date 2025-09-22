<?php
    /* ============================================================================== */
    /* =   PAGE : 등록/변경 처리 PAGE                                               = */
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
    require "./pp_cli_hub_lib_cash.php";
    include "./site_conf_inc_cash.php";
    /* ============================================================================== */
	

    /* ============================================================================== */
    /* =   01. 요청 정보 설정                                                       = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx     = $_POST[ "req_tx"     ];                             // 요청 종류
    $trad_time  = $_POST[ "trad_time"  ];                             // 원거래 시각
    /* = -------------------------------------------------------------------------- = */
    $ordr_idxx  = $_POST[ "ordr_idxx"  ];                             // 주문 번호
    $buyr_name  = $_POST[ "buyr_name"  ];                             // 주문자 이름
    $buyr_tel1  = $_POST[ "buyr_tel1"  ];                             // 주문자 전화번호
    $buyr_mail  = $_POST[ "buyr_mail"  ];                             // 주문자 E-Mail
    $good_name  = $_POST[ "good_name"  ];                             // 상품 정보
    $comment    = $_POST[ "comment"    ];                             // 비고
    /* = -------------------------------------------------------------------------- = */
    $corp_type     = $_POST[ "corp_type"      ];                      // 사업장 구분
    $corp_tax_type = $_POST[ "corp_tax_type"  ];                      // 과세/면세 구분
    $corp_tax_no   = $_POST[ "corp_tax_no"    ];                      // 발행 사업자 번호
    $corp_nm       = $_POST[ "corp_nm"        ];                      // 상호
    $corp_owner_nm = $_POST[ "corp_owner_nm"  ];                      // 대표자명
    $corp_addr     = $_POST[ "corp_addr"      ];                      // 사업장 주소
    $corp_telno    = $_POST[ "corp_telno"     ];                      // 사업장 대표 연락처
    /* = -------------------------------------------------------------------------- = */
    $tr_code    = $_POST[ "tr_code"    ];                             // 발행용도
    $id_info    = $_POST[ "id_info"    ];                             // 신분확인 ID
    $amt_tot    = $_POST[ "amt_tot"    ];                             // 거래금액 총 합
    $amt_sup    = $_POST[ "amt_sup"    ];                             // 공급가액
    $amt_svc    = $_POST[ "amt_svc"    ];                             // 봉사료
    $amt_tax    = $_POST[ "amt_tax"    ];                             // 부가가치세
    /* = -------------------------------------------------------------------------- = */
    $mod_type   = $_POST[ "mod_type"   ];                             // 변경 타입
    $mod_value  = $_POST[ "mod_value"  ];                             // 변경 요청 거래번호
    $mod_gubn   = $_POST[ "mod_gubn"   ];                             // 변경 요청 거래번호 구분
    $mod_mny    = $_POST[ "mod_mny"    ];                             // 변경 요청 금액
    $rem_mny    = $_POST[ "rem_mny"    ];                             // 변경처리 이전 금액
    /* = -------------------------------------------------------------------------- = */
    $cust_ip    = getenv( "REMOTE_ADDR" );                            // 요청 IP
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화                                              = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus  = new C_PAYPLUS_CLI;
    $c_PayPlus->mf_clear();
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정, 실행                                            = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 승인 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        // 업체 환경 정보
        if ( $req_tx == "pay" )
        {
            $tx_cd = "07010000"; // 현금영수증 등록 요청

            // 현금영수증 정보
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "user_type",      $g_conf_user_type );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "trad_time",      $trad_time        );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "tr_code",        $tr_code          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "id_info",        $id_info          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_tot",        $amt_tot          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_sup",        $amt_sup          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_svc",        $amt_svc          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_tax",        $amt_tax          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "pay_type",       "PAXX"            ); 

            // 주문 정보
            $c_PayPlus->mf_set_ordr_data( "ordr_idxx",  $ordr_idxx );
            $c_PayPlus->mf_set_ordr_data( "good_name",  $good_name );
            $c_PayPlus->mf_set_ordr_data( "buyr_name",  $buyr_name );
            $c_PayPlus->mf_set_ordr_data( "buyr_tel1",  $buyr_tel1 );
            $c_PayPlus->mf_set_ordr_data( "buyr_mail",  $buyr_mail );
            $c_PayPlus->mf_set_ordr_data( "comment",    $comment   );

		    // 가맹점 정보
            $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_type",       $corp_type     );

            if ( $corp_type == "1" ) // 입점몰인 경우 판매상점 DATA 전문 생성
            {
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_tax_type",   $corp_tax_type );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_tax_no",     $corp_tax_no   );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_sell_tax_no",$corp_tax_no   );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_nm",         $corp_nm       );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_owner_nm",   $corp_owner_nm );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_addr",       $corp_addr     );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_telno",      $corp_telno    );
            }

            $c_PayPlus->mf_set_ordr_data( "rcpt_data", $rcpt_data_set );
            $c_PayPlus->mf_set_ordr_data( "corp_data", $corp_data_set );
        }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-2. 취소 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            if ( $mod_type == "STSQ" )
            {
                $tx_cd = "07030000"; // 조회 요청
            }
            else
            {
                $tx_cd = "07020000"; // 취소 요청
            }

            $c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type   );      // 원거래 변경 요청 종류
            $c_PayPlus->mf_set_modx_data( "mod_value",  $mod_value  );
            $c_PayPlus->mf_set_modx_data( "mod_gubn",   $mod_gubn   );
            $c_PayPlus->mf_set_modx_data( "trad_time",  $trad_time  );

            if ( $mod_type == "STPC" ) // 부분취소
            {
                $c_PayPlus->mf_set_modx_data( "mod_mny",  $mod_mny  );
                $c_PayPlus->mf_set_modx_data( "rem_mny",  $rem_mny  );
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03-3. 실행                                                               = */
    /* ------------------------------------------------------------------------------ */
    if ( $tx_cd != "" )
    {
        $c_PayPlus->mf_do_tx( "", $g_conf_home_dir, $g_conf_site_id, "", $tx_cd, "",
                              $g_conf_pa_url, $g_conf_pa_port, "payplus_cli_slib", $ordr_idxx,
                              $cust_ip, $g_conf_log_level, 0, 0, $g_conf_log_path ); // 응답 전문 처리

        $res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
        $res_msg = $c_PayPlus->m_res_msg;
        /* $res_en_msg = $c_PayPlus->mf_get_res_data( "res_en_msg" );  // 결과 영문 메세지 */ 
    }
    else
    {
        $c_PayPlus->m_res_cd  = $res_cd = "9562";
        $c_PayPlus->m_res_msg = $res_msg = "연동 오류 tran_cd값이 설정되지 않았습니다.";
    }

	$signdate		= time();	
	$rtns			= "False";
	$res_msg		= iconv("euc-kr", "utf-8", $res_msg);	

    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. 승인 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        if ( $req_tx == "pay" )
        {			

            if ( $res_cd == "0000" )
            {
                $cash_no    = $c_PayPlus->mf_get_res_data( "cash_no"    );       // 현금영수증 거래번호
                $receipt_no = $c_PayPlus->mf_get_res_data( "receipt_no" );       // 현금영수증 승인번호
                $app_time   = $c_PayPlus->mf_get_res_data( "app_time"   );       // 승인시간(YYYYMMDDhhmmss)
                $reg_stat   = $c_PayPlus->mf_get_res_data( "reg_stat"   );       // 등록 상태 코드
                $reg_desc   = $c_PayPlus->mf_get_res_data( "reg_desc"   );       // 등록 상태 설명

    /* = -------------------------------------------------------------------------- = */
    /* =   04-1. 승인 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
    /* =         승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해      = */
    /* =         DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로       = */
    /* =         승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                = */
    /* =         DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"     = */
    /* =         로 세팅해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의 = */
    /* =         값을 세팅하시면 됩니다.)                                           = */
    /* = -------------------------------------------------------------------------- = */
				$bSucc			= "";             // DB 작업 실패일 경우 "false" 로 세팅

				$receipt_info	= "승인번호 : {$receipt_no}";	
				$receipt_info	= iconv("euc-kr", "utf-8", $receipt_info);	

				$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$app_time}', receipt_no = '{$cash_no}', receipt_info = '{$receipt_info}', receipt_status = '{$reg_stat}', receipt_error = '', status = '3', status_date = '{$signdate}' WHERE order_num = '{$ordr_idxx}'";
				mysqli_query($mysql->con, $sql) or $bSucc = "false";
				
				if(substr($ordr_idxx, 0, 1) != 'C') {
					$sql = "UPDATE mallRN_order_info SET cash_issued = 1 WHERE order_num = '{$ordr_idxx}'";
					$mysql->query($sql);
				}

    /* = -------------------------------------------------------------------------- = */
    /* =   04-2. DB 작업 실패일 경우 자동 승인 취소                                 = */
    /* = -------------------------------------------------------------------------- = */
                if ( $bSucc == "false" )
                {
                    $c_PayPlus->mf_clear();

                    $tx_cd = "07020000"; // 취소 요청

                    $c_PayPlus->mf_set_modx_data( "mod_type",  "STSC"     );                    // 원거래 변경 요청 종류
                    $c_PayPlus->mf_set_modx_data( "mod_value", $cash_no   );
                    $c_PayPlus->mf_set_modx_data( "mod_gubn",  "MG01"     );

                    $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_id, "", $tx_cd, "",
                                  $g_conf_pa_url, $g_conf_pa_port, "payplus_cli_slib", $ordr_idxx,
                                  $cust_ip, "3", 0, 0 , $g_conf_key_dir, $g_conf_log_dir);

                    $res_cd		= $c_PayPlus->m_res_cd;
                    $res_msg	= iconv("euc-kr", "utf-8", '내부에러로 인한 자동취소 - '.$c_PayPlus->m_res_msg);
					
					$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$res_msg}', status_date = '{$signdate}'  WHERE order_num = '{$ordr_idxx}' && status = '0'";
					$mysql->query($sql);
                }
				else {
					$rtns = "True";
				}

            }    // End of [res_cd = "0000"]

    /* = -------------------------------------------------------------------------- = */
    /* =   04-3. 등록 실패를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
            else
            {

				$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$res_msg}', status_date = '{$signdate}'  WHERE order_num = '{$ordr_idxx}' && status = '0'";
				$mysql->query($sql);

            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   05. 변경 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            if ( $res_cd == "0000" )
            {
                $cash_no    = $c_PayPlus->mf_get_res_data( "cash_no"    );       // 현금영수증 거래번호
                $receipt_no = $c_PayPlus->mf_get_res_data( "receipt_no" );       // 현금영수증 승인번호
                $app_time   = $c_PayPlus->mf_get_res_data( "app_time"   );       // 승인시간(YYYYMMDDhhmmss)
                $reg_stat   = $c_PayPlus->mf_get_res_data( "reg_stat"   );       // 등록 상태 코드
                $reg_desc   = $c_PayPlus->mf_get_res_data( "reg_desc"   );       // 등록 상태 설명
                $amt_tot    = $c_PayPlus->mf_get_res_data( "amt_tot"    );       // 거래금액 총 합
                $ordr_idxx  = $c_PayPlus->mf_get_res_data( "ordr_idxx"  );       // 주문번호

				$sql			= "SELECT receipt_info FROM mallRN_order_cash_receipts WHERE order_num = '{$order_num}'";
				$receipt_info	= $mysql->get_one($sql);
				
				if($mode == 'partial_cancel')	$receipt_info	.= ", 부분취소승인번호 : {$receipt_no}, 부분취소금액 : {$_POST['mod_mny']}";
				else							$receipt_info	.= ", 취소승인번호 : {$receipt_no}";	

				$receipt_info	= iconv("euc-kr", "utf-8", $receipt_info);
				
				if($mode == 'partial_cancel') {
					$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$app_time}', receipt_cancel_no = '{$cash_no}', receipt_info = '{$receipt_info}', receipt_status = '{$reg_stat}', receipt_error = '', status = '3', status_date = '{$signdate}', partial_price = partial_price +  {$_POST['mod_mny']} WHERE order_num = '{$order_num}'";
				}
				else {
					$sql = "UPDATE mallRN_order_cash_receipts SET receipt_time = '{$app_time}', receipt_no = '{$cash_no}', receipt_info = '{$receipt_info}', receipt_status = '{$reg_stat}', receipt_error = '', status = '4', status_date = '{$signdate}' WHERE order_num = '{$order_num}'";
				}
				$mysql->query($sql);

				if(substr($order_num, 0, 1) != 'C' && $mode == 'cancel') {
					$sql = "UPDATE mallRN_order_info SET cash_issued = 0 WHERE order_num = '{$order_num}'";
					$mysql->query($sql);
				}

				$rtns = "True";
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-1. 변경 실패를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
            else
            {
				$sql		= "UPDATE mallRN_order_cash_receipts SET receipt_error = '{$res_msg}', status_date = '{$signdate}'  WHERE order_num = '{$order_num}' && status = '3'";
				$mysql->query($sql);				
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   06. 인스턴스 CleanUp                                                     = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus->mf_clear();
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   07. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */

	echo $rtns;
	exit;
?>