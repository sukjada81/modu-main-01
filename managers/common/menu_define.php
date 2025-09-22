<?php 

if(!defined('__MANAGERS__')) exit; // 개별 페이지 접근 불가

$MENU_ARR = Array();

$MENU_ARR[0] = Array();
$MENU_ARR[0][] = ["상품관리","goods/goods_list.php",'<i class="fas fa-gift"></i>', 0];
$MENU_ARR[0][] = ["상품분류관리","goods/cate.php","상품분류를 생성하고 수정 하실 수 있습니다.", "", "분류접근권한, 상품분류등록, 상품분류수정, 상품분류삭제, 상품분류순서변경, 엑셀다운로드, 노출항목설정"];
$MENU_ARR[0][] = ["상품등록","goods/goods_info.php","상품을 등록하거나 등록된 상품을 수정 하실 수 있습니다.<br />상품 기본정보만 입력 하셔도 등록이 가능 합니다.", "", "상품명, 상품설명, 검색엔진, 옵션, 브랜드, 제조사, 원산지, 아이콘, 아용안내, 배송, 교환, 환불, AS"];
$MENU_ARR[0][] = ["상품리스트","goods/goods_list.php","등록된 상품 리스트 확인 및 검색을 할 수 있습니다.", "", "상품관리, 상품조회, 분류변경, 상품엑셀다운로드, 상품진열변경, 상품판매변경, 상품삭제, 상품복사"];
$MENU_ARR[0][] = ["상품진열관리","goods/goods_display.php?type=1","등록된 상품의 진열여부 및 진열순서를 관리 할 수 있습니다.<br />분류상품진열인 경우 상품 진열 우선순위가 0인 상품만 노출되며 진열순서를 변경 할 수 있습니다.", "1", "노출순서, 상품진열순서, 인기상품, 추천상품, 신상품"];
$MENU_ARR[0][] = ["상품정보일괄수정","goods/goods_modify_list.php","등록된 상품의 가격, 재고등 자주 변경되는 정보를 수정 할 수 있습니다.<br />마일리지, 배송비, 상품명, 판매가, 이미지사이즈를 일괄 변경 할 수 있습니다.", "", "상품진열우선순위변경, 상품마일리지변경, 상품배송비변경, 상품명변경, 상품가격변경, 상품이미지사이즈변경"];
$MENU_ARR[0][] = ["상품일괄등록","goods/goods_adds.php","다수 상품정보를 엑셀파일로 만들어 일괄적으로 상품을 등록 할 수 있습니다.<br />본사 상품만 등록이 가능 하며 판매사 상품은 판매사 관리자 페이지에서 등록이 가능 합니다.", "", "상품엑셀등록, 상품EXCEL, 분류코드매칭, 타서버이미지복사"];
$MENU_ARR[0][] = ["모음전관리","goods/exhibition_list.php?type=1","등록된 상품들을 분류와 상관없이 모아서 보여주는 모음을 등록 하실 수 있습니다.<br />할인적용할 경우 특정기간동안 할인된 가격으로 판매가 됩니다.<br />먼저 모음전 등록 후 모음전 상품관리에서 상품을 관리 하시면 됩니다.","2", "모음전, 기획전, 이벤트, 할인"];
$MENU_ARR[0][] = ["상품랭킹","goods/goods_statistics_type.php?type=sales","각종 상품랭킹을 확인 하실 수 있습니다<br />펀매금액 및 판매수량 랭킹은 상위30개만 노출되며 집계기간내 취소/반품이 적용되나 취소/반품만 있는 경우(마이너스 값) 노출이 되지 않습니다.<br />상품클릭 랭킹 및 관심상품 랭킹은 상위30개만 노출되며 1년이 지난 데이터는 자동 삭제 되며 관심상품 랭킹은 실시간 데이터를 반영 합니다.", "20", "상품통계, 통계"];

$MENU_ARR[1] = Array();
$MENU_ARR[1][] = ["주문관리","order/order_list.php",'<i class="fas fa-credit-card"></i>', 0];
$MENU_ARR[1][] = ["주문리스트","order/order_list.php","주문리스트를 확인 하고 입금대기중, 결제완료 상태로 변경 할 수 있습니다.<br />주문취소는 입금대기중상태일 경우 선택 일괄처리가 가능하며 모든상품이 결제완료/배송준비중일 경우 주문취소버튼이 노출되며 개별처리가 가능 합니다.<br />일부상품 및 일부수량 취소/교환/반품은 주문내역 상세보기 페이지에서 상품별로 처리가능 합니다.<br />주문삭제는 모든 주문상품이 주문취소일 경우에만 가능하며 상세내역에서 삭제 하시면 됩니다.", "", "주문상태변경, 주문취소, 결제완료, 일부상품, 일부수량, 엑셀다운로드, 노출항목설정"];
$MENU_ARR[1][] = ["단계별주문리스트","order/order_status_list.php?status=0","주문처리 단계별 주문상황을 확인 할 수 있습니다.<br />교환상품 배송중 이후에는 자동배송완료 설정이 적용이되며 상품 처리상태가 배송완료 처리가 됩니다.<br />상품구매시 지급 마일리지가 있으면 구매확정일 경우 회원에게 지급이 됩니다.", "15", "주문단계"];
$MENU_ARR[1][] = ["본사배송주문리스트","order/headquarters_order_list.php?status=0","본사배송 주문상품을 각 단계별 주문상황을 확인하고 배송준비중, 배송중, 배송완료 상태로 변경 할 수 있습니다.<br />배송정책이 조건부 배송비이고 배송비가 발생할 경우 주문번호별로 첫번째 조건부 배송 상품에 배송비가 노출됩니다.", "14", "주문상태변경, 배송정보, 송장번호"];
$MENU_ARR[1][] = ["송장번호일괄등록","order/order_delivery_excel.php","본사배송 주문상품의 송장번호를 일괄등록 할 수 있습니다.", "", "본사배송, 배송정보, 송장번호"];
$MENU_ARR[1][] = ["교환/반품/취소접수","order/order_change_list.php","교환/반품/취소 접수된 내역을 조회하고 처리를 할 수 있습니다.<br />전체상품 취소일 경우 본사에서만 처리되어 판매사에 본사로 노출 됩니다.", "", "취소요청, 반품요청, 교환요청, 취소승인, 반품승인, 교환승인, 환불"];
$MENU_ARR[1][] = ["전자결제취소연동로그","order/order_cancel_cp_log_list.php","전자결제 취소처리 연동 로그기록을 확인 하실 수 있습니다.<br />연동오류로 취소가되지 않은 경우 CP사 관리자페이지에서 직접취소 처리후 수동처리완료를 해 주시면 알림에 나타나지 않습니다.<br />카드결제는 언제든 취소가 가능하며 실시간계좌이체일 경우 2일이내 핸드폰결제일 경우 당월에만 취소 가증 합니다.<br />실시간계좌이체 취소일 경우 당일취소만 수수료가 환급됩니다.","" , "로그, 주문취소, 전자결제"];
$MENU_ARR[1][] = ["현금영수증관리","order/cash_receipts_list.php","현금영수증 신청내역을 조회/발급 하실 수 있습니다.<br />실시간계좌이체, 가상계좌이체일일 경우 KCP(NICE페이멑츠) 결제창에서 현금영수증 발행 안되게 설정 하셔야 중복 발행이 되지 않습니다.<br />현금영수증 발급 방법 설정에서 자동발급, 관리자 수동발급을 선택 하실 수 있습니다.<br />발급이후 전체취소/반품이나 부분취소/반품일 경우 자동으로 취소되거나 부분취소 처리 됩니다.", "", "현금영수증발행, 현금용수증요청, 현금영수증취소"];
$MENU_ARR[1][] = ["매출통계","order/sales_statistics.php","각종 매출통계 및 매출내역을 확인 할 수 있습니다.<br />상품구매금액, 배송비는 매출발생으로 마일리지 및 쿠폰/상품할인, CP수수료는 매출차감으로 집계 됩니다.<br />주문취소시 매출발생금액은 매출차감으로 매출차감금액은 매출발생으로 매출내역이 등록됩니다.", "13", "매출발생, 매출차감, 통계"];
$MENU_ARR[1][] = ["마진통계","order/margin_statistics.php","각종 마진통계 및 마진내역을 확인 할 수 있습니다.<br />마진은 상품판매마진(수수료) - 마일리지 - 쿠폰 - 할인 - CP수수료이며 마진률은 상품판매금액 / 마진 입니다.<br />상품판매마진(수수료)은 마진발생으로 마일리지 및 쿠폰/상품할인, CP수수료는 마진차감으로 집계 되며 배송비는 집계에서 제외 됩니다.<br />주문취소시 마진발생금액은 마진차감으로 마진차감금액은 마진발생으로 마진내역이 등록됩니다.", "24", "마진발생, 마진차감, 마진, 마진률, 통계"];

$MENU_ARR[2] = Array();
$MENU_ARR[2][] = ["디자인관리","design/design.php",'<i class="xi-browser-text xi-x"></i>', 0];
$MENU_ARR[2][] = ["디자인설정","design/design.php","쇼핑몰 디자인과 관련된 설정을 하실 수 있는 곳 입니다.<br />사용하는 스킨에따라 작동되지 않을 수 있습니다.", "", "메인디자인, 인기상품, 추천상품, 신상품"];
$MENU_ARR[2][] = ["상단메뉴관리","design/top_menu.php","쇼핑몰 상단 메뉴를 관리 하실 수 있는 곳 입니다.<br />메뉴추가 하거나 순서를 변경 하신 후 '저장하기'를 누르셔야 적용이 됩니다.<br />사용하는 스킨에따라 작동되지 않을 수 있습니다.", "", ""];
$MENU_ARR[2][] = ["배너관리","design/banner_list.php","쇼핑몰에 사용되는 배너를 관리 하실 수 있습니다.<br />사용하는 스킨에따라 노출되는 배너가 다를 수 있습니다.<br />출력위치만 선택되면 노출순서를 변경 할 수 있습니다.", "", "배너등록, 배너수정, 배너삭제"];
$MENU_ARR[2][] = ["팝업관리","design/popup_list.php","쇼핑몰 메인에 사용되는 팝업을 관리 하실 수 있습니다.<br />동일 위치의 팝업은 멀티팝업으로 노출 됩니다.", "", "팝업등록, 팝업수정, 팝업삭제"];
$MENU_ARR[2][] = ["스킨선택","design/skin.php","스킨폴더에 업로드된 스킨중에서 선택하여 스킨을 사용하거나 변경 하실 수 있습니다.<br />작업중인 스킨사용시 고객에는 적용이 되지 않으며 사용자 페이지 접속시 skin_ing=Y 파라미터로 접속시에만 적용이 됩니다.", "", "스킨변경, 작업중인스킨, 스킨작업"];
$MENU_ARR[2][] = ["자동메일발송관리","design/mail_common.php","자동 메일 발송시 사용되는 디자인 및 문구를 설정 하실 수 있습니다.", "7", "회원가입메일, 인증메일, 휴먼회원메일, 주문메일"];
$MENU_ARR[2][] = ["추가페이지관리","design/add_page_list.php","쇼핑몰에 추가로 필요한 페이지를 관리 하실 수 있는 곳 입니다.<br />회사소개, 쇼핑몰 안내등 추가로 페이지를 만들어 노출 시킬 수 있습니다.", "", "추가페이지"];

$MENU_ARR[3] = Array();
$MENU_ARR[3][] = ["회원관리","member/member_list.php",'<i class="xi-group xi-x"></i>', 0];
$MENU_ARR[3][] = ["회원관리","member/member_list.php","등록된 회원 리스트를 확인 하거나 검색 하실 수 있습니다. 관리자 등급은 회원탈퇴가 되지 않으니 일반화원등급으로 변경후 탈퇴 처리 하시면 됩니다.<br />회원의 주문건수 및 주문금액은 결제완료된 주문에 대해서만 집계합니다.<br />회원탈퇴시 주문/게시물 관련 정보는 비회원으로 변경되며 마일리지/쿠폰 등 회원에 관련된 정보들은 모두 삭제 되며 복구 할 수 없습니다.<br />회원등급평가는 주문일 기준으로 날자조회 후 구매확정된 주문상품금액합계를 등급별로 설정된 구매금액을 적용하여 자동으로 등급이 변경 됩니다.", "22", "회원목록, 아이디, 쿠폰발급, 문자발송, 엑셀다운로드, 노출항목설정, 등급평가, 등급변경"];
$MENU_ARR[3][] = ["회원일괄등록","member/member_adds.php","다수 회원정보를 엑셀파일로 만들어 일괄적으로 회원을 등록 할 수 있습니다."];
$MENU_ARR[3][] = ["휴면회원관리","member/member_sleep_list.php","등록된 후면회원 리스트를 확인 하거나 검색 하실 수 있습니다.<br />쇼핑몰에 마지막 로그인이 1년 이상 경과된 고객들은 자동으로 휴면회원 처리되며, 개인정보가 분리 보관되며 이를 휴면회원이라고 합니다.<br />휴면회원으로 전환되기 한달전에 등록된 회원 이메일로 자동 안내 메일이 발송 됩니다.", "", ""];
$MENU_ARR[3][] = ["탈퇴회원관리","member/member_withdrawal_list.php","탈퇴한 회원 내역을 확인 하거나 탈퇴사유 통계를 확인 하실  수 있습니다.", "10", "탈퇴사유, 통계"];
$MENU_ARR[3][] = ["회원통계","member/member_statistics.php","회원가입통계, 지역별회원통계, 성별회원통계, 연령별회원통계를 확인 하실 수 있습니다.", "19", "지역별, 성별, 연령별, 통계"];
$MENU_ARR[3][] = ["쿠폰관리","member/coupon_list.php","쿠폰을 생성하고 리스트를 확인 하실  수 있습니다.<br />관리자수동발급 쿠폰은 회원리스트에서 발급 할 수 있습니다.", "12", "쿠폰등록, 쿠폰생성, 쿠폰수정, 쿠폰삭제, 쿠폰발급"];
$MENU_ARR[3][] = ["마일리지관리","member/mileage_list.php","마일리지 내역을 확인 하거나 마일리지 삭제로그기록을 확인 하실  수 있습니다.<br />실수로 삭제한 마일리지일 경우 마일리지 삭제로그기록에서 복구를 하실 수 있습니다.", "17", "마일리지등록, 마일리지삭제, 마일리지지급"];
$MENU_ARR[3][] = ["문자메시지관리","member/sms_auto.php","문자메시지알림 설정, 문자메시지발송 및 발송된 문자메시지 내역을 확인 하실 수 있습니다.<br />6개월이 지난 발송내역은 삭제 됩니다.", "9", "SMS, SMS알림, 문자메세지알림, 문자발송내역"];
$MENU_ARR[3][] = ["개인정보접속로그","member/admin_log_list.php","관리자 및 판매사의 개인정보접속로그를 확인 할 수 있습니다.<br />개인정보취급자가 개인정보처리시스템에 접속한 기록을 월 1회 이상 정기적으로 확인/감독하여야 합니다.<br />시스템 이상 유무의 확인 등을 위해 최소 2년 이상 접속기록을 보존/관리하여야 하며 2년이 지난 접속기록은 자동삭제 됩니다.", "18", "개인정보, 로그"];

$MENU_ARR[4] = Array();
$MENU_ARR[4][] = ["입점관리","vendor/vendor_list.php",'<i class="xi-box xi-x"></i>', 0];
$MENU_ARR[4][] = ["판매사관리","vendor/vendor_list.php","등록된 판매사 리스트를 확인 하거나 검색 하실 수 있습니다.<br />둥록된 판매사 정보를 수정 하실 수 있습니다.<br />주문내역이 있는 판매사는 삭제가 불가능 하며 주문내역이 없는 경우 판매사 수정페이지에 삭제가 가능 하며 삭제시 등록된 상품도 같이 삭제 됩니다. ", "", "승인상태, 정산주기, 판매상태, 상품승인"];
$MENU_ARR[4][] = ["판매사등록","vendor/vendor_info.php","판매사를 등록 하실 수 있습니다." ,"" ,"입점사등록"];
$MENU_ARR[4][] = ["판매사상품관리","vendor/vendor_goods_list.php","등록된 판매사 상품 리스트 확인 하거나 승인상태를 변경 하실 수 있습니다.", "" ,"상품승인"];
$MENU_ARR[4][] = ["판매사매출통계","vendor/sales_statistics.php","판매사 주문상품의 매출통계를 확인 할 수 있습니다.<br />매출금액은 상품 + 배송비 입니다.<br />매출집계는 결제완료시 매출금액 증가(+)하고 주문취소/반품 완료시 매출금액이 감소(-)됩니다.", "23" ,"통계, 매출"];
$MENU_ARR[4][] = ["정산통계","vendor/calculate_statistics.php","판매사 주문상품의 정산통계를 확인 할 수 있습니다.<br />정산금액은 상품 + 배송비 - 수수료 입니다.<br />정산확정일은 구매확정일(매출발생), 구매확정후 주문취소/반품 완료일(매출차감) 입니다.<br />정산집계는 구매확정시 정산금액 증가(+)하고 구매확정 후 주문취소/반품 완료시 정산금액이 감소(-)됩니다.", "16" ,"통계 수수료"];
$MENU_ARR[4][] = ["정산금액조회","vendor/calculate_amount.php","정산기간(정산확정일)을 선택하여 해당 기간의 판매사의 정산금액을 조회 할 수 있으며 정산내역에 등록 할 수 있습니다.<br />정산내역에 등록하기를 누르시면 판매사 정산내역에 자동등록이 되며 같은기간 기존 등록된 내역이 있는 경우 수정이 됩니다.<br />정산집계는 구매확정시 정산금액 증가(+)하고 구매확정 후 주문취소/반품 완료시 정산금액이 감소(-)됩니다.<br />정산내역에 등록이 된 경우 등록된 날자에는 집계가 되지 않으며 정산내역에서 삭제 하시면 다시 집계 됩니다." ,"" ,""];
$MENU_ARR[4][] = ["정산내역","vendor/calculate_list.php","판매사 정산내역을 확인 할 수 있습니다.<br />세금계산서는 판매사에서 발행한 세금계산서 여부입니다." ,"" ,"세금계산서"];
$MENU_ARR[4][] = ["판매사랭킹","vendor/vendor_statistics_type.php?type=sales","판매사 랭킹을 확인 하실 수 있습니다<br />매출랭킹, 수수료랭킹은 상품판매금액만 집계가 되며 집계기간내 취소/반품이 적용됩니다.<br />관심상점 랭킹은 상위30개만 노출되며 1년이 지난 데이터는 자동 삭제 되며 실시간 데이터를 반영 합니다.", "21" ,"통계"];

$MENU_ARR[5] = Array();
$MENU_ARR[5][] = ["모바일샵","mobile/mobile.php",'<i class="fas fa-tablet-alt"></i>', 0];
$MENU_ARR[5][] = ["모바일샵설정","mobile/mobile.php","모바일샵에 관련된 설정을 하실 수 있습니다." ,"" ,"모바일"];
$MENU_ARR[5][] = ["상단메뉴설정","mobile/top_menu.php","모바일샵 상단 메뉴를 관리 하실 수 있는 곳 입니다." ,"" , "모바일, 상단"];
$MENU_ARR[5][] = ["배너관리","mobile/banner_list.php","모바일샵에 사용되는 배너를 관리 하실 수 있습니다.<br />사용하는 스킨에따라 노출되는 배너가 다를 수 있습니다.<br />출력위치만 선택되면 노출순서를 변경 할 수 있습니다." ,"" ,"모바일, 배너등록, 배너수정, 배너삭제"];
$MENU_ARR[5][] = ["팝업관리","mobile/popup_list.php","모바일샵 메인에 사용되는 팝업을 관리 하실 수 있습니다.<br />동일 위치의 팝업은 멀티팝업으로 노출 됩니다.", "", "모바일, 팝업등록, 팝업수정, 팝업삭제"];

$MENU_ARR[6] = Array();
$MENU_ARR[6][] = ["기타서비스","board/board_list.php",'<i class="xi-message-o xi-x"></i>', 0];
$MENU_ARR[6][] = ["게시판관리","board/board_list.php","생성된 게시판 리스트를 확인 하거나 설정을 변경 하실 수 있습니다.<br />자주찾는질문, 1:1고객문의, 공지사항, , 판매사 1:1고객문의, 판매사 공지사항은 기본게시판으로 삭제 하실수 없습니다.", "", "게시판, 자주찾는질문, 1:1고객문의, 공지사항"];
$MENU_ARR[6][] = ["게시판보기","board/board.php?b_id=notice","게시판의 게시글을 확인 하실 수 있습니다.", "8", "","게시판, 자주찾는질문, 1:1고객문의, 공지사항"];
$MENU_ARR[6][] = ["구매후기관리","etcs/review_list.php","등록된 구매후기를 확인 하실 수 있습니다.", "", "구매추기, 상품후기, 리뷰"];
$MENU_ARR[6][] = ["상품문의관리","etcs/inquiry_list.php","상품문의 설정 및 등록된 상품문의를 확인 하실 수 있습니다.", "11", "문의, 답변"];
$MENU_ARR[6][] = ["접속통계","etcs/count_statistics.php","쇼핑몰에 방문한 방문자수 통계, 접속경로 및 다양한 접속통계를 확인 하실 수 있습니다.<br />6개월이 지난 접속경로는 자동 삭제 됩니다.", "4", "통계, 카운터"];
$MENU_ARR[6][] = ["검색어관리","etcs/keyword_list.php","고객이 검색한 검색어를 확인 하거나 통계를 확인 하실 수 있습니다.<br />6달이 지난 검색어 내역은 자동삭제 됩니다.","5", "검색어, 키워드"];
$MENU_ARR[6][] = ["자동완성검색어관리","etcs/keyword_autocomplete.php","자동완성검색어를 관리 하실 수 있습니다.<br />자동완성검색어는 고객 검색시 자동 수집되며 수동으로 등록 하실 수도 있습니다.","" , "자동완성, 검색어, 키워드"];
$MENU_ARR[6][] = ["스마트택배연동로그","etcs/delivery_api_log_list.php","스마트택배 API연동 로그기록을 확인 하실 수 있습니다.<br />에러메세지가 '키 정보를 찾을수 없습니다. 종료된 키 or 유효하지 않은 키입니다.'일 경우 API키를 확인 해 보시기 바랍니다. ","" , "로그, 스마트택배API"];

$MENU_ARR[7] = Array();
$MENU_ARR[7][] = ["환경설정","conf/base_info.php",'<i class="xi-cog xi-x"></i>'];
$MENU_ARR[7][] = ["기본정보설정","conf/base_info.php","쇼핑몰의 기본적인 정보를 설정 할 수 있습니다.<br />해당 정보는 쇼핑몰 하단정보, 검색엔진 최적화, 메일발송등에 사용이 됩니다.", "", "관리자명, 관리자이메일, 고객센터운영시간, 검색엔지, 상단타이틀, 대표이미지, 검색엔진키워드, 아이콘, 교환주소, 반품지주소, 회사주소, 회사정보, 대표이메일, 대표전화, 대표팩스"];
$MENU_ARR[7][] = ["결제정책설정","conf/payment_info.php","쇼핑몰의 결제정책을 설정  할 수 있습니다.<br />전자결제 신청시 쇼핑몰솔루션 선택 하셔야 카드 수수료를 할인이 적용 됩니다.", "", "결제수단, 결제대행사, kcp, NICE페이먼츠, 할부기간, 에스크로, 현긍영수증, 세금계산서, 입금은행, 네이버페이"];
$MENU_ARR[7][] = ["배송정책설정","conf/delivery_info.php","배송비, 배송업체, 추가배송비등 배송에 관련된 정책을 설정  할 수 있습니다.", "", "배송비, 착불, 조건부, 택배사, 배송업체, 추가배송비, 도서산간"];
$MENU_ARR[7][] = ["회원정책설정","conf/member_info.php","회원적립금, 회원인증, 회원가입항목, 회원등급, 관리자 권한 등 회원에 관련된 정책을 설정  할 수 있습니다.<br />회원가입시 일반회원등급(LV1)으로 가입됩니다.<br />회원등급설정 중 추가적립률은 상품 마일리지 설정이 환경설정일 경우에만 적용되며 배송비무료는 본사배송상품만 적용 됩니다.", "3", "승인방식, 마일리지, 로그인, sns, 네이버로그인, 카카오로그인, 가입항목, 가입양식"];
$MENU_ARR[7][] = ["상품환경설정","conf/goods_info.php","상품등록에 사용되는 옵션, 제조회사, 브랜드, 상품 필수정보, 아이콘 등을 설정  할 수 있습니다.", "", "절사, 외부연동, 픔절상품, 지식쇼핑, 쇼핑하우, 검색엔진, 옵션, 브랜드, 제조사, 원산지, 아이콘, 아용안내, 배송, 교환, 환불, AS"];
$MENU_ARR[7][] = ["주문&SMS정책설정","conf/etcs_info.php","주문정책설정과 SMS정책을 설정 할 수 있습니다.", "", "사유, 요청사항, 배송완료, 구매확정, 주문취소, SMS, coolsms, 발신번호, 캐시충전, api"];
$MENU_ARR[7][] = ["약관&개인정보설정","conf/agree1_info.php","이용약관, 개인정보처리방침, 개인정보수집 동의항목 정책을 설정  할 수 있습니다.", "6", "이용약관, 개인정보처리방침, 개인정보수집동의항목, 판매이용약관"];
$MENU_ARR[7][] = ["웹푸시(알림)설정","conf/push_info.php","윕푸시(알림)을 설정 할 수 있습니다.<br />웹푸시(알림)은 구글 Firebase 클라우드 메시징 서비스를 이용합니다.<br />웹푸시(알림)을 사용 하시면 주문/회원가입/고객문의 시 실시간으로 알려 드립니다.<br />보안서버(https)가 적용된 경우에만 정상적으로 작동됩니다.", "", "알림, 실시간, 웹푸시, Firebase, fcm"];
$MENU_ARR[7][] = ["외부스크립트설정","conf/script_info.php","구글통계, 네이버메타데그등 외부 스크립트를 설정할 수 있습니다.", "", "구글통계, 네이버메타태그, 스크립트"];

$MENU_ARR[8] = Array();
$MENU_ARR[8][] = ["개발자","developer/db_table_list.php",'<i class="xi-cog xi-x"></i>', 1];
$MENU_ARR[8][] = ["디비테이블명세서","developer/db_table_list.php","디비(DataBase) 테이블 명세서를 확인 하실 수 있습니다.", "", "db, 디비, 데이타베이스, database, 명세서"];
$MENU_ARR[8][] = ["디비오류로그","developer/db_error_log.php","디비(DataBase) 오류 로그를 확인 할 수 있습니다.<br />1개월이 지난 디비 오류 로그는 자동 삭제 됩니다.","", "db, 오류, 로그, 디비, 데이타베이스, database"];

$SUB_MENU_ARR = Array();
$SUB_MENU_ARR[0] = "";
$SUB_MENU_ARR[1] = Array();
$SUB_MENU_ARR[1][] = ["메인진열","goods/goods_display.php?type=1", "인기상품, 추천상품, 신상품"];
$SUB_MENU_ARR[1][] = ["분류메인진열","goods/goods_display.php?type=2", "인기상품, 추천상품, 신상품"];
$SUB_MENU_ARR[1][] = ["분류상품진열","goods/goods_display.php?type=3"];
$SUB_MENU_ARR[2] = Array();
$SUB_MENU_ARR[2][] = ["모음전관리","goods/exhibition_list.php", "상품이벤트, 이벤트할인, 상품할인"];
$SUB_MENU_ARR[2][] = ["모음전상품관리","goods/exhibition_goods.php"];
$SUB_MENU_ARR[3] = Array();
$SUB_MENU_ARR[3][] = ["회원정책설정","conf/member_info.php"];
$SUB_MENU_ARR[3][] = ["회원등급설정","conf/member_level_info.php"];
$SUB_MENU_ARR[4] = Array();
$SUB_MENU_ARR[4][] = ["방문자수통계","etcs/count_statistics.php"];
$SUB_MENU_ARR[4][] = ["운영체제통계","etcs/count_statistics_type.php?type=os"];
$SUB_MENU_ARR[4][] = ["브라우저통계","etcs/count_statistics_type.php?type=browser"];
$SUB_MENU_ARR[4][] = ["사이트통계","etcs/count_statistics_type.php?type=site"];
$SUB_MENU_ARR[4][] = ["검색어통계","etcs/count_statistics_type.php?type=keyword"];
$SUB_MENU_ARR[4][] = ["접속경로관리","etcs/count_referer.php"];
$SUB_MENU_ARR[5] = Array();
$SUB_MENU_ARR[5][] = ["검색어내역","etcs/keyword_list.php"];
$SUB_MENU_ARR[5][] = ["검색어통계","etcs/keyword_statistics.php"];
$SUB_MENU_ARR[6] = Array();
$SUB_MENU_ARR[6][] = ["이용약관설정","conf/agree1_info.php"];
$SUB_MENU_ARR[6][] = ["개인정보처리방침설정","conf/agree2_info.php"];
$SUB_MENU_ARR[6][] = ["개인정보수집동의항목설정","conf/agree3_info.php"];
$SUB_MENU_ARR[6][] = ["판매이용약관설정","conf/agree6_info.php"];
$SUB_MENU_ARR[7] = Array();
$SUB_MENU_ARR[7][] = ["공통디자인설정","design/mail_common.php"];
$SUB_MENU_ARR[7][] = ["회원관련설정","design/mail_member.php"];
$SUB_MENU_ARR[7][] = ["주문관련설정","design/mail_order.php"];
$SUB_MENU_ARR[8] = Array();

$sql = "SELECT id, name FROM mallRN_board_manager ORDER BY uid ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	$board_name = stripslashes($row['name']);
	$SUB_MENU_ARR[8][] = [$board_name ,"board/board.php?b_id=".$row['id']];
}
unset($board_name);

$SUB_MENU_ARR[9] = Array();
$SUB_MENU_ARR[9][] = ["문자메시지알림설정","member/sms_auto.php"];
$SUB_MENU_ARR[9][] = ["문자메시지발송내역","member/sms_list.php"];

$SUB_MENU_ARR[10] = Array();
$SUB_MENU_ARR[10][] = ["탈퇴회원내역","member/member_withdrawal_list.php"];
$SUB_MENU_ARR[10][] = ["탈퇴사유통계","member/member_withdrawal_statistics.php"];

$SUB_MENU_ARR[11] = Array();
$SUB_MENU_ARR[11][] = ["상품문의","etcs/inquiry_list.php"];
$SUB_MENU_ARR[11][] = ["상품문의설정","etcs/inquiry_conf.php"];

$SUB_MENU_ARR[12] = Array();
$SUB_MENU_ARR[12][] = ["쿠폰리스트","member/coupon_list.php"];
$SUB_MENU_ARR[12][] = ["발급쿠폰리스트","member/coupon_down_list.php"];

$SUB_MENU_ARR[13] = Array();
$SUB_MENU_ARR[13][] = ["매출통계","order/sales_statistics.php"];
$SUB_MENU_ARR[13][] = ["결제수단매출통계","order/sales_statistics_type.php?type=pay"];
$SUB_MENU_ARR[13][] = ["회원등급매출통계","order/sales_statistics_type.php?type=level"];
$SUB_MENU_ARR[13][] = ["상품분류매출통계","order/sales_statistics_type.php?type=cate"];
$SUB_MENU_ARR[13][] = ["첫/재구매매출통계","order/sales_statistics_type.php?type=new"];
$SUB_MENU_ARR[13][] = ["지역별매출통계","order/sales_statistics_type.php?type=addr"];
$SUB_MENU_ARR[13][] = ["매출상세내역","order/sales_list.php"];

$SUB_MENU_ARR[14] = Array();
$SUB_MENU_ARR[14][] = ["입금대기중","order/headquarters_order_list.php?status=0"];
$SUB_MENU_ARR[14][] = ["결제완료","order/headquarters_order_list.php?status=1"];
$SUB_MENU_ARR[14][] = ["배송준비중","order/headquarters_order_list.php?status=2"];
$SUB_MENU_ARR[14][] = ["배송중","order/headquarters_order_list.php?status=3"];
$SUB_MENU_ARR[14][] = ["배송완료","order/headquarters_order_list.php?status=4"];
$SUB_MENU_ARR[14][] = ["구매확정","order/headquarters_order_list.php?status=5"];
$SUB_MENU_ARR[14][] = ["교환","order/headquarters_order_list.php?status=7"];
$SUB_MENU_ARR[14][] = ["반품","order/headquarters_order_list.php?status=8"];
$SUB_MENU_ARR[14][] = ["취소","order/headquarters_order_list.php?status=9"];

$SUB_MENU_ARR[15] = Array();
$SUB_MENU_ARR[15][] = ["입금대기중","order/order_status_list.php?status=0"];
$SUB_MENU_ARR[15][] = ["결제완료","order/order_status_list.php?status=1"];
$SUB_MENU_ARR[15][] = ["배송준비중","order/order_status_list.php?status=2"];
$SUB_MENU_ARR[15][] = ["배송중","order/order_status_list.php?status=3"];
$SUB_MENU_ARR[15][] = ["배송완료","order/order_status_list.php?status=4"];
$SUB_MENU_ARR[15][] = ["구매확정","order/order_status_list.php?status=5"];
$SUB_MENU_ARR[15][] = ["교환","order/order_status_list.php?status=7"];
$SUB_MENU_ARR[15][] = ["반품","order/order_status_list.php?status=8"];
$SUB_MENU_ARR[15][] = ["취소","order/order_status_list.php?status=9"];

$SUB_MENU_ARR[16] = Array();
$SUB_MENU_ARR[16][] = ["정산통계","vendor/calculate_statistics.php"];
$SUB_MENU_ARR[16][] = ["판매사별정산통계","vendor/calculate_statistics_type.php"];
$SUB_MENU_ARR[16][] = ["정산상세내역","vendor/calculate_detail.php"];

$SUB_MENU_ARR[17] = Array();
$SUB_MENU_ARR[17][] = ["마일리지관리","member/mileage_list.php"];
$SUB_MENU_ARR[17][] = ["마일리지통계","member/mileage_statistics.php"];
$SUB_MENU_ARR[17][] = ["마일리지삭제로그","member/mileage_log_list.php"];

$SUB_MENU_ARR[18] = Array();
$SUB_MENU_ARR[18][] = ["관리자개인정보접속로그","member/admin_log_list.php"];
$SUB_MENU_ARR[18][] = ["판매사개인정보접속로그","member/vendor_log_list.php"];

$SUB_MENU_ARR[19] = Array();
$SUB_MENU_ARR[19][] = ["회원가입통계","member/member_statistics.php"];
$SUB_MENU_ARR[19][] = ["지역별회원통계","member/member_statistics_type.php?type=addr"];
$SUB_MENU_ARR[19][] = ["성별회원통계","member/member_statistics_type.php?type=gender"];
$SUB_MENU_ARR[19][] = ["연령별회원통계","member/member_statistics_type.php?type=age"];

$SUB_MENU_ARR[20] = Array();
$SUB_MENU_ARR[20][] = ["판매금액랭킹","goods/goods_statistics_type.php?type=sales"];
$SUB_MENU_ARR[20][] = ["판매수량랭킹","goods/goods_statistics_type.php?type=qtys"];
$SUB_MENU_ARR[20][] = ["상품클릭랭킹","goods/goods_statistics_type.php?type=click"];
$SUB_MENU_ARR[20][] = ["관심상품랭킹","goods/goods_statistics_type.php?type=favorite"];

$SUB_MENU_ARR[21] = Array();
$SUB_MENU_ARR[21][] = ["매출랭킹","vendor/vendor_statistics_type.php?type=sales"];
$SUB_MENU_ARR[21][] = ["수수료랭킹","vendor/vendor_statistics_type.php?type=commission"];
$SUB_MENU_ARR[21][] = ["관심상점랭킹","vendor/vendor_statistics_type.php?type=favorite"];

$SUB_MENU_ARR[22] = Array();
$SUB_MENU_ARR[22][] = ["회원리스트","member/member_list.php"];
$SUB_MENU_ARR[22][] = ["회원등급평가","member/member_level.php"];

$SUB_MENU_ARR[23] = Array();
$SUB_MENU_ARR[23][] = ["매출통계","vendor/sales_statistics.php"];
$SUB_MENU_ARR[23][] = ["매출상세내역","vendor/sales_detail.php"];

$SUB_MENU_ARR[24] = Array();
$SUB_MENU_ARR[24][] = ["마진통계","order/margin_statistics.php"];
$SUB_MENU_ARR[24][] = ["결제수단마진통계","order/margin_statistics_type.php?type=pay"];
$SUB_MENU_ARR[24][] = ["회원등급마진통계","order/margin_statistics_type.php?type=level"];
$SUB_MENU_ARR[24][] = ["상품분류마진통계","order/margin_statistics_type.php?type=cate"];
$SUB_MENU_ARR[24][] = ["첫/재구매마진통계","order/margin_statistics_type.php?type=new"];
$SUB_MENU_ARR[24][] = ["지역별마진통계","order/margin_statistics_type.php?type=addr"];
$SUB_MENU_ARR[24][] = ["마진상세내역","order/margin_list.php"];

?>