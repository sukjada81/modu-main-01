<?php 

if(!defined('__MANAGERS__')) exit; // 개별 페이지 접근 불가

$goods_msg	= "";
$sql		= "SELECT goods_auth FROM mallRN_vendor WHERE id = '{$v_my_id}'";
if($mysql->get_one($sql) == 'P') $goods_msg = "<br />상품등록/수정시 쇼핑몰에 노출이 되지 않으며 관리자가 승인 처리 해 줘야만 쇼핑몰에 노출이 됩니다.";

$MENU_ARR = Array();

$MENU_ARR[0] = Array();
$MENU_ARR[0][] = ["상품관리","goods/goods_list.php",'<i class="fas fa-gift"></i>'];
$MENU_ARR[0][] = ["상품등록","goods/goods_info.php","상품을 등록하거나 등록된 상품을 수정 하실 수 있습니다.<br />상품 기본정보만 입력 하셔도 등록이 가능 합니다.{$goods_msg}", "", "상품명, 상품설명, 검색엔진, 옵션, 브랜드, 제조사, 원산지, 아이콘, 아용안내, 배송, 교환, 환불, AS"];
$MENU_ARR[0][] = ["상품리스트","goods/goods_list.php","등록된 상품 리스트 확인 및 검색을 할 수 있습니다.{$goods_msg}", "", "상품관리, 상품조회, 분류변경, 상품엑셀다운로드, 상품진열변경, 상품판매변경, 상품삭제, 상품복사"];
$MENU_ARR[0][] = ["상품진열관리","goods/goods_display.php","등록된 상품의 스토어메인 진열여부 및 진열순서를 관리 할 수 있습니다.", "", "상품진열순서"];
$MENU_ARR[0][] = ["상품정보일괄수정","goods/goods_modify_list.php","등록된 상품의 가격, 재고등 자주 변경되는 정보를 수정 할 수 있습니다.<br />배송비, 상품명, 판매가, 이미지사이즈를 일괄 변경 할 수 있습니다.{$goods_msg}", "", "상품진열우선순위변경, 상품마일리지변경, 상품배송비변경, 상품명변경, 상품가격변경, 상품이미지사이즈변경"];
$MENU_ARR[0][] = ["일괄상품등록","goods/goods_adds.php","다수 상품정보를 엑셀파일로 만들어 일괄적으로 상품을 등록 할 수 있습니다.{$goods_msg}", "", "상품엑셀등록, 상품EXCEL, 분류코드매칭, 타서버이미지복사"];
$MENU_ARR[0][] = ["상품랭킹","goods/goods_statistics_type.php?type=sales","각종 상품랭킹을 확인 하실 수 있습니다<br />펀매금액 및 판매수량 랭킹은 상위30개만 노출되며 집계기간내 취소/반품이 적용되나 취소/반품만 있는 경우(마이너스 값) 노출이 되지 않습니다.<br />상품클릭 랭킹 및 관심상품 랭킹은 상위30개만 노출되며 1년이 지난 데이터는 자동 삭제 되며 관심상품 랭킹은 실시간 데이터를 반영 합니다.", "3", "상품통계, 통계"];

$MENU_ARR[1] = Array();
$MENU_ARR[1][] = ["주문관리","order/order_list.php",'<i class="fas fa-credit-card"></i>'];
$MENU_ARR[1][] = ["주문리스트","order/order_list.php","주문리스트를 확인 할 수 있습니다.", "", "엑셀다운로드"];
$MENU_ARR[1][] = ["단계별주문리스트","order/order_status_list.php?status=0","각 단계별 주문상황을 확인하고 배송준비중, 배송중 상태로 변경 할 수 있습니다.<br />배송정책이 조건부 배송비이고 배송비가 발생할 경우 주문번호별로 첫번째 조건부 배송 상품에 배송비가 노출됩니다.<br />본사배송일 경우 배송처리 부분이 나타나지 않고 본사에서 처리 합니다.", "2", "주문상태변경, 엑셀다운로드, 배송정보, 송장번호"];
$MENU_ARR[1][] = ["송장번호일괄등록","order/order_delivery_excel.php","주문상품의 송장번호를 일괄등록 할 수 있습니다.", "", "배송정보, 송장번호"];
$MENU_ARR[1][] = ["교환/반품/취소접수","order/order_change_list.php","교환/반품/취소 접수된 내역을 조회하고 처리를 할 수 있습니다.", "", "취소승인, 반품승인, 교환승인"];
$MENU_ARR[1][] = ["매출통계","order/sales_statistics.php","매출통계를 확인 할 수 있습니다.<br />매출금액은 상품 + 배송비 - 수수료 입니다.<br />매출집계는 결제완료시 정산금액 증가(+)하고 주문취소/반품 완료시 매출금액이 감소(-)됩니다.", "","통계 수수료"];
$MENU_ARR[1][] = ["매출상세내역","order/sales_detail.php","매출상세내역를 확인 할 수 있습니다.", "", "매출, 수수료"];

$MENU_ARR[2] = Array();
$MENU_ARR[2][] = ["정산관리","calculate/calculate_statistics.php",'<i class="xi-receipt xi-x"></i>'];
$MENU_ARR[2][] = ["정산통계","calculate/calculate_statistics.php","정산통계를 확인 할 수 있습니다.<br />정산금액은 상품 + 배송비 - 수수료 입니다.<br />정산확정일은 구매확정일(매출발생), 구매확정후 주문취소/반품 완료일(매출차감) 입니다.<br />정산집계는 구매확정시 정산금액 증가(+)하고 구매확정 후 주문취소/반품 완료시 정산금액이 감소(-)됩니다.", "","통계 수수료"];
$MENU_ARR[2][] = ["정산상세내역","calculate/calculate_detail.php","정산상세내역를 확인 할 수 있습니다.<br />정산확정일은 구매확정일(매출발생), 구매확정후 주문취소/반품 완료일(매출차감) 입니다.", "", "정산내역, 수수료"];
$MENU_ARR[2][] = ["정산내역","calculate/calculate_list.php","정산내역을 확인 할 수 있습니다.<br />세금계산서를 발행하시면 관리자가 확인 후 정산금액을 입금 합니다.","" ,"세금계산서"];

$MENU_ARR[3] = Array();
$MENU_ARR[3][] = ["스토어설정","store/basic_info.php",'<i class="xi-shop xi-x"></i>'];
$MENU_ARR[3][] = ["스토어정보설정","store/basic_info.php","스토어 관련 정보를 설정 하는 곳 입니다.", "", "스토어명, 고객센터, 운영시간, 교환지, 반품지, 주소"];
$MENU_ARR[3][] = ["메인디자인설정","store/design.php","스토어 메인 페이지의 디자인을 설정 하는 곳 입니다.", "", "스토어메인, 인기상품, 추천상품, 신상품"];
$MENU_ARR[3][] = ["구매후기관리","store/review_list.php","등록된 구매후기를 확인 하실 수 있습니다.", "", "구매추기, 상품후기, 리뷰"];
$MENU_ARR[3][] = ["상품문의관리","store/inquiry_list.php","등록된 상품문의를 확인 하고 답변하실 수 있습니다.","", "문의, 답변"];
$MENU_ARR[3][] = ["접속통계","store/count_statistics.php","스토어에 방문한 방문자수 통계를 확인 하실 수 있습니다.<br />6개월이 지난 접속경로는 자동 삭제 됩니다.", "1", "통계, 카운터"];

$MENU_ARR[4] = Array();
$MENU_ARR[4][] = ["본사커뮤니티","board/board.php?b_id=vnotice",'<i class="xi-document xi-x"></i>'];
$MENU_ARR[4][] = ["본사공지사항","board/board.php?b_id=vnotice","본사공지사항을 확인 할 수 있습니다.","", "본사, 공지"];
$MENU_ARR[4][] = ["본사1:1문의","board/board.php?b_id=vcounsel","본사1:1문의사항을 확인 할 수 있습니다.","", "본사, 문의"];

$MENU_ARR[5] = Array();
$MENU_ARR[5][] = ["환경설정","conf/vendor_info.php",'<i class="xi-cog xi-x"></i>'];
$MENU_ARR[5][] = ["업체정보관리","conf/vendor_info.php","업체정보를 확인하거나 수정 할 수 있습니다.","" ,"계좌정보, 수수료, 회사주소, 회사정보, 대표이메일, 대표전화, 대표팩스, 담당자, 업태, 종목"];
$MENU_ARR[5][] = ["업체비밀번호변경","conf/vendor_passwd.php","업체 비밀번호를 변경 할 수 있습니다.", "", "비밀번호"];
if($v_my_delivery_type == 0) $MENU_ARR[5][] = ["배송정책설정","conf/delivery_info.php","배송비, 배송업체, 추가배송비등 배송에 관련된 정책을 설정 하는 곳 입니다.", "", "베송비, 배송업체, 추가배송비, 도서산간"];
$MENU_ARR[5][] = ["상품환경설정","conf/goods_info.php","상품등록에 사용되는 옵션, 브랜드, 제조회사, 원산지, 이용안내 등을 설정 하는 곳 입니디.","" ,"옵션, 브랜드, 제조회사, 원산지, 이용안내, 배송, 교환, 환불, AS"];

$SUB_MENU_ARR = Array();
$SUB_MENU_ARR[0] = "";
$SUB_MENU_ARR[1] = Array();
$SUB_MENU_ARR[1][] = ["방문자수통계","store/count_statistics.php"];
$SUB_MENU_ARR[1][] = ["운영체제통계","store/count_statistics_type.php?type=os"];
$SUB_MENU_ARR[1][] = ["브라우저통계","store/count_statistics_type.php?type=browser"];
$SUB_MENU_ARR[1][] = ["사이트통계","store/count_statistics_type.php?type=site"];
$SUB_MENU_ARR[1][] = ["검색어통계","store/count_statistics_type.php?type=keyword"];
$SUB_MENU_ARR[1][] = ["접속경로관리","store/count_referer.php"];

$SUB_MENU_ARR[2] = Array();
$SUB_MENU_ARR[2][] = ["입금대기중","order/order_status_list.php?status=0"];
$SUB_MENU_ARR[2][] = ["결제완료","order/order_status_list.php?status=1"];
$SUB_MENU_ARR[2][] = ["배송준비중","order/order_status_list.php?status=2"];
$SUB_MENU_ARR[2][] = ["배송중","order/order_status_list.php?status=3"];
$SUB_MENU_ARR[2][] = ["배송완료","order/order_status_list.php?status=4"];
$SUB_MENU_ARR[2][] = ["구매확정","order/order_status_list.php?status=5"];
$SUB_MENU_ARR[2][] = ["교환","order/order_status_list.php?status=7"];
$SUB_MENU_ARR[2][] = ["반품","order/order_status_list.php?status=8"];
$SUB_MENU_ARR[2][] = ["취소","order/order_status_list.php?status=9"];

$SUB_MENU_ARR[3] = Array();
$SUB_MENU_ARR[3][] = ["판매금액랭킹","goods/goods_statistics_type.php?type=sales"];
$SUB_MENU_ARR[3][] = ["판매수량랭킹","goods/goods_statistics_type.php?type=qtys"];
$SUB_MENU_ARR[3][] = ["상품클릭랭킹","goods/goods_statistics_type.php?type=click"];
$SUB_MENU_ARR[3][] = ["관심상품랭킹","goods/goods_statistics_type.php?type=favorite"];

?>