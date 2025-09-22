<?php

ini_set('memory_limit', -1); // 메모리 제한을 해제해준다. 
header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

define('EXCEL_FOLDER', '../../image/excel');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$type = checkPostVar('type');
if(!$type) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

if(phpversion() < '5.2.0') {
    logMsg('PHP버전 5.2이상일 경우에만 지원 됩니다.');
}

$name			= isset($_POST['name']) ? $_POST['name'] : "{$type}_table_".date("Ymd");
$passwd			= checkPostVar('passwd');
$title_bgcolor	= 'FFABCDEF';

if(preg_match("/[^a-zA-Z0-9가-힣ㄱ-ㅎㅏ-ㅣ_.\-]/u",$name)) logMsg("파일명에 사용하지 못하는 특수문자가 있습니다.");

function saleTypeWhere($type, $items) {
	
	$search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 's_range1' => 5, 'e_range1' => 5, 'range1' => 2, 'vendor' => 1, 'level' => 1, 'type' => 1, 'status' => 1, 'mobile' => 1, 'sort' => 0);
	$multi_array		= array("title", "order_num");

	$where = "";
	switch($type) {
		case '1' :
			if(!$items) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			$where = " && a.uid IN ({$items})";
		break;
		
		case '2' :				
			foreach ($search_variable as $k  =>  $v) {
				if(preg_match("/keyword/i",$k)) $value = isset($_GET[$k]) ?  urldecode($_GET[$k]) : '';
				else $value = isset($_GET[$k]) ? $_GET[$k] : '';
				
				if($v==1 && strlen($value)>0) {
					$where	.= "&& a.{$k} = '{$value}' ";
				}
				else if($v == 2 && strlen($value) > 0) {
					if($k == 'e_date') {
						$date_type	= checkGetVar('date_type');
						$s_date		= checkGetVar('s_date');

						if(!$s_date) $where .= "&& from_unixtime(a.{$date_type}) < '{$value} 23:59:59' ";
						else $where .= "&& from_unixtime(a.{$date_type}) BETWEEN '{$s_date}' AND '{$value} 23:59:59' ";
					}
					else if($k == 'range1') {
						$s_range1	= checkGetVar('s_range1');
						$e_range1	= checkGetVar('e_range1');		

						if(!$s_range1 && !$e_range1) continue;
						if(!$s_range1) $where .= "&& a.{$value} < {$e_range1} ";
						else if(!$e_range1) $where .=  "&& a.{$value} > {$s_range1} ";
						else $where .= "&& a.{$value} BETWEEN '{$s_range1}' AND '{$e_range1}' ";
					}							
				}
			}
		break;		
	}

	return $where;
}

function vendorSaleTypeWhere($type, $items) {
	
	$search_variable	= array('field' => 0, 'keyword' => 0, 'date_type' => 5, 's_date' => 5, 'e_date' => 2, 'vendor' => 1, 'type' => 1, 'status' => 1, 'sort' => 0);
	$multi_array		= array("title", "order_num");

	$where = "";
	switch($type) {
		case '1' :
			if(!$items) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			$where = " && a.uid IN ({$items})";
		break;
		
		case '2' :				
			foreach ($search_variable as $k  =>  $v) {
				if(preg_match("/keyword/i",$k)) $value = isset($_GET[$k]) ?  urldecode($_GET[$k]) : '';
				else $value = isset($_GET[$k]) ? $_GET[$k] : '';
				
				if($v==1 && strlen($value)>0) {
					$where	.= "&& a.{$k} = '{$value}' ";
				}
				else if($v == 2 && strlen($value) > 0) {
					if($k == 'e_date') {
						$date_type	= checkGetVar('date_type');
						$s_date		= checkGetVar('s_date');

						if(!$s_date) $where .= "&& from_unixtime(a.{$date_type}) < '{$value} 23:59:59' ";
						else $where .= "&& from_unixtime(a.{$date_type}) BETWEEN '{$s_date}' AND '{$value} 23:59:59' ";
					}										
				}
			}		
			
		break;		
	}

	return $where;
}

include_once(PATH_LIB.'/PHPExcel.php');

switch($type) {
	case "goods" : 
		$cells = array(
			array('A',15, 'uid', '상품번호'),
			array('B',15, 'vendor', '판매사'),
			array('C',20, 'name',  '상품명'),
			array('D',15, 'cate', '대표분류'),
			array('E',15, 'price', '판매가'),
			array('F',15, 'orig_price', '매입가(공급가)'),
			array('G',15, 'consumer_price', '소비자가'),
			array('H',15, 'commission', '수수료(마진)률'),
			array('I',15, 'mileage_type', '마일리지'),	
			array('J',15, 'delivery_type', '배송비'),	
			array('K',20, 'brand', '브랜드'),
			array('L',20, 'make', '제조사'),
			array('M',20, 'origin', '원산지'),
			array('N',20, 'model', '모델명'),
			array('O',20, 'goods_code', '자체상품코드'),
			array('P',15, 'display_use', '진열상태'),
			array('Q',15, 'sale_use', '판매상태'),	
			array('R',15, 'qty_type', '재고'),
			array('S',50, 'keyword', '검색 키워드'),
			array('T',50, 'detail', '상품간략설명'),
			array('U',50, 'detail_image_only', '상품상세설명'),
			array('V',50, 'option_use', '옵션'),
			array('W',20, 'image1', '상세이미지경로'),
			array('X',20, 'image2', '목록이미지경로'),
			array('Y',20, 'image3', '작은목록이미지경로'),
			array('Z',50, 'other_image', '추가이미지경로'),
			array('AA',15, 'moddate', '수정일'),
			array('AB',15, 'signdate', '등록일')
		);	

		$number_arr = array('E','F','G');
		$number_arr2 = array('H');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$swhere = "";
		$where	= goodsTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";

		if($swhere) {
			$sql = "SELECT c.* FROM ( SELECT a.uid FROM mallRN_goods a WHERE a.uid IN ( SELECT guid FROM mallRN_goods_cate WHERE {$swhere} ) {$where} ) b JOIN mallRN_goods c ON b.uid=c.uid ORDER BY c.{$sort}";
		}
		else {
			$sql = "SELECT a.* FROM mallRN_goods a WHERE a.uid>0 {$where} ORDER BY a.{$sort}";
		}
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$display_use_array		= array("0"=>"진열안함","1"=>"진열함"); 
		$sale_use_array			= array("0"=>"판매안함","1"=>"판매함"); 
		$mileage_type_array		= array("1"=>"환경설정 사용","2"=>"없음","3"=>"별도설정(회원등급별)","4"=>"별도설정(회원공통)");
		$delivery_type_array	= array("1"=>"환경설정 사용","2"=>"무료배송","3"=>"착불","4"=>"별도책정(고정)","5"=>"별도책정(개당)");
		
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i<$cells_cnt; $i++) {
				$v = $cells[$i][2];

				if($v=='option_use') {
					if($row[$v]=='1' && $row['option_info']) {
						$option_info = explode("|*|",$row['option_info']);						
						$option_name = array();
						for($ii=0,$cnt=count($option_info); $ii<$cnt; $ii++) {
							$option_info2	= explode("|",$option_info[$ii]);
							$option_name[]	= $option_info2[0];								
						}	
						unset($option_info, $option_info2);

						$sql = "SELECT * FROM mallRN_goods_option WHERE guid='{$row['uid']}' ORDER BY sequence ASC";
						$mysql->query2($sql);
						
						$options = array();
						while($row_op = $mysql->fetch_array(2)){
							$option_value	= explode("|",stripslashes($row_op['value']));
							$option_value2	= array();
							for($j=0, $cnt=count($option_value); $j<$cnt; $j++) {
								$option_value2[] = $option_name[$j]." : ".$option_value[$j];																		
							}

							$option_value2[]	= stripslashes($row_op['price'])."원";
							if($row_op['qty_type']==1) $option_value2[] = "무제한";
							else $option_value2[] = stripslashes($row_op['qty'])."개";

							$options[] = join(", ",$option_value2);
						}

						$data2[] = join("\r\n",$options);				
						unset($option_value, $option_value2, $options);
					}
					else $data2[] = "";	
				}
				else if($v=='detail_image_only') {
					if($row[$v]=='1') $data2[] = str_replace(CONF_ROOT.'image', ABSOLUTE_PATH_SHOP.'image', detailImageToTag($row['uid'], $row['detail_image_type'], $row['detail_image']));
					else $data2[] = str_replace("../../", ABSOLUTE_PATH_SHOP, add_escape_re_string($row['explains']));
				}
				else if($v=='cate') $data2[] = " ".$row[$v];
				else if($v=='display_use') $data2[] = $display_use_array[$row[$v]];
				else if($v=='sale_use') $data2[] = $sale_use_array[$row[$v]];
				else if($v=='mileage_type') $data2[] = $mileage_type_array[$row[$v]];
				else if($v=='delivery_type') {
					if($row[$v]=='4' || $row[$v]=='5') $delivery_price = "<br />".number_format($row['delivery_price']);
					else $delivery_price = "";
					$data2[] = $delivery_type_array[$row[$v]].$delivery_price;
				}
				else if($v=='qty_type') {
					if($row['option_use']==1) {
						$sql = "SELECT count(*) as cnt, sum(qty) as sum FROM mallRN_goods_option WHERE guid='{$row['uid']}' && qty_type=0";
						$data_option = $mysql->one_row($sql);						
						if($data_option['cnt']>0) $data2[] = "옵션".number_format($data_option['sum'])."개";
						else $data2[] = "옵션 무제한";
					}
					else {
						if($row[$v]=='1') $data2[] = "무제한";
						else $data2[] = number_format($row['qty'])."개";
					}
				}				
				else if($v=='image1' || $v=='image2' || $v=='image3') $data2[] = ABSOLUTE_PATH_SHOP.'image/goods/img'.$row[$v];
				else if($v=='moddate' || $v=='signdate') {
					if($row[$v] == '1970-01-01 09:00:00') $data2[] = "-";
					else $data2[] = date("Y-m-d H:i:s", $row[$v]);
				}		
				else if($v=='other_image') {
					if($row[$v]) {
						$tmps = explode(",", $row[$v]);
						$img_block		= floor($row['uid']/10000);
						$temp_upload	= $img_block.'/'.$row['uid'];		
						foreach($tmps as $k2 => $v2) {						
							$tmps[$k2] = ABSOLUTE_PATH_SHOP."image/goods/upload/".$temp_upload."/".$v2;
						}
						$data2[] = join(",", $tmps);
						unset($tmps);
					}
					else $data2[] = "";
				}
				else $data2[] = stripslashes($row[$v]);
			}
			$data[] = $data2;
		}	
		
	break;

	case "member" : 

		$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
		$member_config	= $mysql->one_row($sql);

		for($i = 1; $i < 6; $i ++) {
			if($member_config['member_form_add'.$i.'_title']) {
				${"add_title".$i}	= stripslashes($member_config['member_form_add'.$i.'_title']);
			}
			else ${"add_title".$i} = "추가필드 #{$i}";
		}	

		$cells = array(
			array('A',20, 'id', '아이디'),
			array('B',15, 'name',  '이름'),
			array('C',15, 'tel',  '전화번호'),
			array('D',15, 'cell', '핸드폰번호'),
			array('E',15, 'postcode', '우편번호'),
			array('F',40, 'address1', '주소'),
			array('G',20, 'address2', '상세주소'),
			array('H',20, 'email', '이메일'),
			array('I',15, 'birth', '생년월일'),	
			array('J',15, 'gender', '성별'),	
			array('K',15, 'marry', '결혼여부'),
			array('L',20, 'hobby', '관심분야'),
			array('M',15, 'job', '직업'),
			array('N',15, 'level', '등급'),
			array('O',15, 'mileage', '보유마일리지'),
			array('P',15, 'mailling', '메일수신여부'),
			array('Q',15, 'sms', 'sms수신여부'),	
			array('R',15, 'comp', '회사명'),
			array('S',15, 'comp_owner', '대표자명'),
			array('T',15, 'comp_num', '사업자등록번호'),
			array('U',15, 'comp_postcode', '회사소재지 우편번호'),
			array('V',40, 'comp_address1', '회사소재지'),
			array('W',20, 'comp_address2', '회사소재지 상세주소'),
			array('X',15, 'comp_type', '업종'),
			array('Y',15, 'comp_item', '업태'),
			array('Z',15, 'add1', $add_title1),
			array('AA',15, 'add2', $add_title2),
			array('AB',15, 'add3', $add_title3),
			array('AC',15, 'add4', $add_title4),
			array('AD',15, 'add5', $add_title5),
			array('AE',15, 'sns_type', 'sns_type'),
			array('AF',15, 'order_cnt', '주문완료건수'),
			array('AG',15, 'order_price', '주문완료금액'),
			array('AH',15, 'mobile', '모바일가입'),
			array('AI',15, 'auth', '가입승인'),
			array('AJ',15, 'reference', '추천인'),
			array('AK',30, 'login_time', '최종 로그인 시간'),
			array('AL',30, 'signdate', '가입일'),
		);	

		$number_arr = array('O','AF','AG');
		$number_arr2 = array('H');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		######################## 회원등급 #############################
		$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
		$mysql->query($sql);

		$level_array = array();
		while($row = $mysql->fetch_array()) {
			$level_array[$row['level']] = $row['name'];
		}
		######################## 회원등급 #############################

		$where	= memberTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";
		
		$sql = "SELECT a.* FROM mallRN_member a WHERE a.uid > 0 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$gender_array	= array("M" => "남성",		"F" => "여성",		"N" => "미선택"); 
		$marry_array	= array("M" => "기혼",		"S" => "미혼",		"N" => "미선택"); 
		$auth_array		= array("Y" => "승인완료",		"N" => "미승인"); 
		$mailling_array	= array("Y" => "수신허용",		"N" => "수신안함"); 
		$sms_array		= array("Y" => "수신허용",		"N" => "수신안함"); 
		$mobile_array	= array("Y" => "모바일",		"N" => "PC");
		
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i<$cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v == 'birth') {
					if($row['birth_sl'] == 'S') $sl = " 양력";
					else if($row['birth_sl'] == 'L') $sl = " 음력";
					else $sl = "";
					$data2[] = $row[$v].$sl;
				}
				else if($v=='level')	$data2[] = $level_array[$row[$v]];
				else if($v=='gender')	$data2[] = $gender_array[$row[$v]];
				else if($v=='marry')	$data2[] = $marry_array[$row[$v]];
				else if($v=='auth')		$data2[] = $auth_array[$row[$v]];
				else if($v=='mailling') $data2[] = $mailling_array[$row[$v]];
				else if($v=='sms')		$data2[] = $sms_array[$row[$v]];
				else if($v=='mobile')	$data2[] = $mobile_array[$row[$v]];
				else if($v=='order_cnt') {
					$sql = "SELECT count(*) FROM mallRN_order_info WHERE id = '{$row['id']}' && pay_status = 'C'";
					$data2[] = $mysql->get_one($sql);
				}
				else if($v=='order_price') {
					$sql	= "SELECT SUM(pay_total) as pay_total, SUM(cancel_total) as cancel_total, SUM(refund_total) as refund_total FROM mallRN_order_info WHERE id = '{$row['id']}' && pay_status = 'C'";
					$odata	= $mysql->one_row($sql);
					$data2[] = $odata['pay_total'] - $odata['cancel_total'] - $odata['refund_total'];
				}
				else if($v == 'login_time' || $v == 'signdate') $data2[] = date("Y-m-d H:i:s", $row[$v]);				
				else $data2[] = stripslashes($row[$v]);
			}
			$data[] = $data2;
		}	
		
	break;

	case "order" : 
		
		$cells = array(
			array('A',20, 'signdate', '주문일시'),
			array('B',20, 'order_num',  '주문번호'),
			array('C',20, 'id',  '회원아이디'),
			array('D',20, 'level',  '회원등급'),
			array('E',20, 'name',  '주문자명'),
			array('F',20, 'cell', '주문자연락처'),
			array('G',20, 'email', '주문자이메일'),
			array('H',20, 'vendor', '판매사'),
			array('I',15, 'g_uid', '상품UID'),
			array('J',30, 'g_name', '주문상품명'),
			array('K',20, 'option', '옵션정보'),	
			array('L',15, 'qty', '주문상품수량'),
			array('M',20, 'price', '주문상품금액'),
			array('N',15, 'status', '주문상태'),
			array('O',15, 'delivery_price', '개별배송비'),
			array('P',20, 'goods_total', '총상품금액'),
			array('Q',20, 'delivery_total', '총배송비'),
			array('R',20, 'use_mileage', '마일리지사용금액'),
			array('S',20, 'use_coupon', '쿠폰사용금액'),
			array('T',20, 'pay_total', '총주문금액'),
			array('U',20, 'cancel_total', '취소금액'),
			array('V',15, 'pay_type', '결제수단'),
			array('W',15, 'pay_status', '결제상태'),
			array('X',20, 'name2',  '수령자명'),
			array('Y',20, 'cell2', '수령자연락처'),
			array('Z',20, 'postcode', '배송지우편번호'),
			array('AA',40, 'address1', '배송지'),
			array('AB',40, 'message', '요청사항'),			
		);	

		$number_arr = array('L','M','O','P','Q','R','S','T');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		######################## 회원등급 #############################
		$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
		$mysql->query($sql);

		$level_array = array();
		while($row = $mysql->fetch_array()) {
			$level_array[$row['level']] = $row['name'];
		}
		######################## 회원등급 #############################

		$where2 = "";
		$swhere = "";
		$where	= orderTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";

		$sql  = "SELECT c.* FROM ( SELECT a.uid FROM mallRN_order_info a WHERE a.order_num IN ( SELECT order_num FROM mallRN_order_goods WHERE reals = 1 {$swhere} )  && reals = 1 {$where} ) b JOIN mallRN_order_info c ON b.uid=c.uid ORDER BY c.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$status_array		= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
		$status2_array		= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 
		$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
		$pay_status_array	= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");
		
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i < $cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v=='level')	{
					$sql = "SELECT level FROM mallRN_member WHERE id = '{$row['id']}'";
					$level = $mysql->get_one($sql);
					$data2[] = $level_array[$level];
					$level = "";
				}
				else if($v=='pay_type')		$data2[] = $pay_type_array[$row[$v]];
				else if($v=='pay_status')	$data2[] = $pay_status_array[$row[$v]];
				else if($v == 'signdate')	$data2[] = date("Y-m-d H:i:s", $row[$v]);			
				else if($v == 'address1') {
					$data2[] = stripslashes($row['address1']).' '.stripslashes($row['address2']);
				}
				else $data2[] = isset($row[$v]) ? stripslashes($row[$v]) : '';
			}
			
			$sql = "SELECT * FROM mallRN_order_goods WHERE order_num='{$row['order_num']}' && reals = 1 ORDER BY vendor_delivery ASC, uid DESC";
			$mysql->query2($sql);

			while($row2 = $mysql->fetch_array(2)) {
				$data2[7]	= stripslashes($row2['vendor']);
				$data2[8]	= stripslashes($row2['uid']);
				$data2[9]	= stripslashes($row2['g_name']);
				$data2[10]	= stripslashes($row2['option_name']);
				$data2[11]	= stripslashes($row2['qty']);
				$data2[12]	= stripslashes($row2['price']);
				
				if($row2['status2'])	$data2[13] = $status_array[$row2['status']].$status2_array[$row2['status2']];
				else					$data2[13] = $status_array[$row2['status']];

				$row2['delivery_price'] += $row2['delivery_add_price'];
				
				if($row2['delivery_type'] == 5) {
					if($row2['option']) {				
						if(isset($sum_delivery_option[$row2['g_uid']]) && $sum_delivery_option[$row2['g_uid']] > 0) {
							$G_DELIVERY_PRICE	= "0";					
						}
						else {
							$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$row['order_num']}' && g_uid = '{$row2['g_uid']}' && !(status = 9 && status2 = 5)";
							$option_qty			= $mysql->get_one($sql);
							$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($option_qty / $row2['delivery_type_qty']);
							$sum_delivery_option[$row2['g_uid']] = $option_qty;
						}
					}
					else {
						$G_DELIVERY_PRICE	= $row2['delivery_price'] * ceil($row2['qty'] / $row2['delivery_type_qty']);
					}
					
					$data2[14] = $G_DELIVERY_PRICE;
				}
				else $data2[14] = $row2['delivery_price'];

				$data2[15]	= stripslashes($row2['price'] * $row2['qty']);

				$data[] = $data2;
			}			
		}	
		
	break;

	case "order2" :  case "order3" : 
		
		$cells = array(
			array('A',20, 'signdate', '주문일시'),
			array('B',20, 'order_num',  '주문번호'),
			array('C',20, 'id',  '회원아이디'),
			array('D',20, 'name',  '주문자명'),
			array('E',15, 'g_uid', '주문상품UID'),
			array('F',30, 'g_name', '주문상품명'),
			array('G',20, 'option_name', '옵션정보'),	
			array('H',15, 'qty', '주문상품수량'),
			array('I',20, 'price', '주문상품금액'),
			array('J',15, 'status', '주문상태'),
			array('K',15, 'delivery_price', '개별배송비'),
			array('L',20, 'goods_total', '총상품금액'),
			array('M',20, 'delivery', '배송비'),
			array('N',20, 'delivery_info1',  '택배회사'),
			array('O',20, 'delivery_info2',  '송장번호'),
			array('P',15, 'pay_status', '결제상태'),
			array('Q',20, 'name',  '주문자명'),
			array('R',20, 'cell', '주문자연락처'),
			array('S',20, 'email', '주문자이메일'),
			array('T',20, 'name2',  '수령자명'),
			array('U',20, 'cell2', '수령자연락처'),
			array('V',20, 'postcode', '배송지우편번호'),
			array('W',40, 'address1', '배송지'),
			array('X',40, 'message', '요청사항')
		);	

		$number_arr = array('H','I','K','L','M');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$swhere = "";
		$where	= orderTypeWhere2($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";

		$delivery_info_array	= array();
		$sql = "SELECT delivery_info FROM mallRN_configuration WHERE uid = 1";
		if($delivery_info = $mysql->get_one($sql)){
			$delivery_info = explode("|*|", $delivery_info);

			if($delivery_info[1] && $delivery_info[1] != '|||') {
				for($i=1, $cnt = count($delivery_info); $i < $cnt; $i++) {

					$delivery_info2 = explode("|", $delivery_info[$i]);					
					if($delivery_info2[3] == 0) continue;
					$delivery_info_array[$delivery_info2[0]] = $delivery_info2[1];					
				}
			}
		}

		if($type == 'order2') $where = "&& c.vendor_delivery = '' ".$where;

		$sql	= "SELECT c.*, b.id, b.name, b.cell, b.email, b.pay_status, b.name2, b.cell2, b.postcode, b.address1, b.address2, b.message FROM ( SELECT a.order_num, a.id, a.name, a.cell, a.email, a.pay_status, a.name2, a.cell2, a.postcode, a.address1, a.address2, a.message FROM mallRN_order_info a WHERE a.reals = 1 {$swhere} ) b JOIN mallRN_order_goods c ON b.order_num = c.order_num  && c.reals = 1 {$where} ORDER BY c.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$status_array		= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
		$status2_array		= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 		
		$pay_status_array	= array("A" => "미결제", "B" => "미결제", "C" => "결제완료", "D" => "미결제");
		$sum_delivery_option = array();
		
		$tmp_order_num		= "";
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i < $cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v=='pay_status')	$data2[] = $pay_status_array[$row[$v]];
				else if($v == 'signdate')	$data2[] = date("Y-m-d H:i:s", $row[$v]);				
				else if($v == 'status') {
					if($row['status2'])	$data2[] = $status_array[$row['status']].$status2_array[$row['status2']];
					else					$data2[] = $status_array[$row['status']];
				}
				else if($v == 'delivery_price') {
					$row['delivery_price'] += $row['delivery_add_price'];
					
					if($row['delivery_type'] == 5) {
						if($row['option']) {				
							if(isset($sum_delivery_option[$row['order_num']."_".$row['g_uid']]) && $sum_delivery_option[$row['order_num']."_".$row['g_uid']] > 0) {
								$G_DELIVERY_PRICE	= "0";					
							}
							else {
								$sql				= "SELECT SUM(qty) FROM mallRN_order_goods WHERE order_num = '{$row['order_num']}' && g_uid = '{$row['g_uid']}' && !(status = 9 && status2 = 5)";
								$option_qty			= $mysql->get_one($sql);
								$G_DELIVERY_PRICE	= $row['delivery_price'] * ceil($option_qty / $row['delivery_type_qty']);
								$sum_delivery_option[$row['order_num']."_".$row['g_uid']] = $option_qty;
							}
						}
						else {
							$G_DELIVERY_PRICE	= $row['delivery_price'] * ceil($row['qty'] / $row['delivery_type_qty']);
						}
						$data2[] = $G_DELIVERY_PRICE;
					}
					else $data2[] = $row['delivery_price'];
				}
				else if($v == 'goods_total') $data2[]	= stripslashes($row['price'] * $row['qty']);
				else if($v == 'delivery') {
					$delivery = '0';
					if($tmp_order_num != $row['order_num']) {
						$sql		= "SELECT price FROM mallRN_order_delivery WHERE order_num = '{$row['order_num']}' && vendor = '{$row['vendor_delivery']}'";
						$delivery	= $mysql->get_one($sql);
						if(!$delivery) $delivery = '0';
					}
					$data2[]	= $delivery;
				}
				else if($v == 'delivery_info1') {
					if(!empty($row['delivery_info'])) {
						$tmps		= explode("|", $row['delivery_info']);
						$data2[]	= $delivery_info_array[$tmps[0]];
					}
					else $data2[]	= "";
				}
				else if($v == 'delivery_info2') {
					if(!empty($row['delivery_info'])) {
						$tmps		= explode("|", $row['delivery_info']);
						$data2[]	= $tmps[1];
					}
					else $data2[]	= "";
				}
				else if($v == 'address1') {
					$data2[] = stripslashes($row['address1']).' '.stripslashes($row['address2']);
				}
				else $data2[] = stripslashes($row[$v]);
			}
						
			$data[] = $data2;
			$tmp_order_num	= $row['order_num'];
		}	
		
	break;

	case "sale" : 

		$cells = array(
			array('A',25, 'order_num', '주문번호'),
			array('B',50, 'title',  '내역'),
			array('C',15, 'price',  '발생금액'),
			array('D',15, 'price2',  '차감금액'),
			array('E',15, 'type', '타입'),
			array('F',20, 'signdate', '등록일'),
		);	

		$number_arr = array('C', 'D');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$where	= saleTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";
		
		$sql = "SELECT a.* FROM mallRN_order_sales a WHERE a.uid > 0 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$type_array				= array("상품", "배송비", "마일리지", "쿠폰", "할인", "CP수수료");
				
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i<$cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v=='price')	{
					if($row['status'] == 0) $data2[] = $row['price'];
					else $data2[] = "0";
				}
				else if($v=='price2')	{
					if($row['status'] == 1) $data2[] = $row['price'];
					else $data2[] = "0";
				}
				else if($v=='type')	$data2[] = $type_array[$row[$v]];
				else if($v == 'signdate') $data2[] = date("Y-m-d H:i:s", $row[$v]);				
				else $data2[] = stripslashes($row[$v]);
			}
			$data[] = $data2;
		}	
		
	break;

	case "margin" : 
	
		$cells = array(
			array('A',25, 'order_num', '주문번호'),
			array('B',50, 'title',  '내역'),
			array('C',20, 'price_info1',  '판매가'),
			array('D',20, 'price_info2',  '매입가(공급가)'),
			array('E',15, 'price',  '발생금액'),
			array('F',15, 'price2',  '차감금액'),
			array('G',15, 'type', '타입'),
			array('H',20, 'signdate', '등록일'),
		);	

		$number_arr = array('C', 'D','E', 'F');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$where	= saleTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";
		
		$sql = "SELECT a.* FROM mallRN_order_sales a WHERE a.uid > 0 && type != 1 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$type_array				= array("상품", "배송비", "마일리지", "쿠폰", "할인", "CP수수료");
				
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i<$cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v=='price')	{
					if($row['type'] == 0) $price = $row['commission'];
					else $price = $row['price'];

					if($row['status'] == 0) $data2[] = $price;
					else $data2[] = "0";
				}
				else if($v=='price2')	{
					if($row['type'] == 0) $price = $row['commission'];
					else $price = $row['price'];

					if($row['status'] == 1) $data2[] = $price;
					else $data2[] = "0";
				}
				else if($v=='price_info1')	{
					if($row['type'] == 0) {
						$data2[] = $row['price'];
					}
					else $data2[] = "";					
				}
				else if($v=='price_info2')	{
					if($row['type'] == 0) {
						$data2[] = $row['price'] - $row['commission'];												
					}
					else $data2[] = "";					
				}
				else if($v=='type')	$data2[] = $type_array[$row[$v]];
				else if($v == 'signdate') $data2[] = date("Y-m-d H:i:s", $row[$v]);				
				else $data2[] = stripslashes($row[$v]);
			}
			$data[] = $data2;
		}	
		
	break;

	case "vendor_sale" : 

		$cells = array(
			array('A',25, 'order_num', '주문번호'),
			array('B',25, 'vendor',  '판매사아이디'),
			array('C',25, 'vendor_name',  '판매사명'),
			array('D',50, 'title',  '내역'),
			array('E',15, 'price',  '발생금액'),
			array('F',15, 'price2',  '차감금액'),			
			array('G',15, 'type', '타입'),
			array('H',20, 'signdate', '등록일'),

		);	

		$number_arr = array('E', 'F');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$where	= vendorSaleTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";

		######################## 판매사 정보 #############################
		$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
		$mysql->query($sql);

		$vendor_array = array();
		while($row = $mysql->fetch_array()){
			$vendor_id					= specialStrReplace($row['id']);
			$vendor_name				= specialStrReplace($row['comp_name']);
			$vendor_array[$vendor_id]	= $vendor_name;		
		}
		unset($vendor_id, $vendor_name);
		######################## 판매사 정보 #############################
		
		$sql = "SELECT a.* FROM mallRN_order_sales a WHERE a.uid > 0 && a.vendor != '' && a.type < 2 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$type_array				= array("상품", "배송비", "마일리지", "쿠폰", "할인", "CP수수료");
				
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i<$cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v=='price')	{
					if($row['status'] == 0) $data2[] = $row['price'];
					else $data2[] = "0";
				}
				else if($v=='price2')	{
					if($row['status'] == 1) $data2[] = $row['price'];
					else $data2[] = "0";
				}
				else if($v=='type')	$data2[] = $type_array[$row[$v]];
				else if($v=='vendor_name')	$data2[] = $vendor_array[$row['vendor']];
				else if($v == 'signdate') $data2[] = date("Y-m-d H:i:s", $row[$v]);				
				else $data2[] = stripslashes($row[$v]);
			}
			$data[] = $data2;
		}
		
		
	break;

	case "vendor_calculate" : 

		$cells = array(
			array('A',25, 'order_num', '주문번호'),
			array('B',25, 'vendor',  '판매사아이디'),
			array('C',25, 'vendor_name',  '판매사명'),
			array('D',50, 'title',  '내역'),
			array('E',15, 'price',  '발생금액'),
			array('F',15, 'price2',  '차감금액'),
			array('G',15, 'commission',  '발생수수료'),
			array('H',15, 'commission2',  '차감수수료'),
			array('I',15, 'total',  '발생정산금액'),
			array('J',15, 'total2',  '차감정산금액'),			
			array('K',15, 'type', '타입'),
			array('L',20, 'confirm_date', '정산확정일'),

		);	

		$number_arr = array('E', 'F', 'G', 'H', 'I', 'J');
		$cells_cnt	= count($cells);
		$last_char	= $cells[$cells_cnt-1][0];
		
		$item	= checkPostVar('item');

		if(!isset($_POST['down_type'])) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$where	= vendorSaleTypeWhere($_POST['down_type'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "confirm_date DESC";

		######################## 판매사 정보 #############################
		$sql = "SELECT * FROM mallRN_vendor ORDER BY comp_name ASC";
		$mysql->query($sql);

		$vendor_array = array();
		while($row = $mysql->fetch_array()){
			$vendor_id					= specialStrReplace($row['id']);
			$vendor_name				= specialStrReplace($row['comp_name']);
			$vendor_array[$vendor_id]	= $vendor_name;		
		}
		unset($vendor_id, $vendor_name);
		######################## 판매사 정보 #############################
		
		$sql = "SELECT a.* FROM mallRN_order_sales a WHERE a.uid > 0  && a.vendor != '' && a.type < 2 && confirmation = 1 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);

		$data = array();		
		$data[] = array_column($cells, 3);
		
		$type_array				= array("상품", "배송비", "마일리지", "쿠폰", "할인", "CP수수료");
				
		while($row = $mysql->fetch_array()){
			$data2 = array();
			for($i=0; $i<$cells_cnt; $i++) {
				$v = $cells[$i][2];
				
				if($v=='price')	{
					if($row['status'] == 0) $data2[] = $row['price'];
					else $data2[] = "0";
				}
				else if($v=='price2')	{
					if($row['status'] == 1) $data2[] = $row['price'];
					else $data2[] = "0";
				}
				else if($v=='commission')	{
					if($row['status'] == 0) $data2[] = $row['commission'];
					else $data2[] = "0";
				}
				else if($v=='commission2')	{
					if($row['status'] == 1) $data2[] = $row['commission'];
					else $data2[] = "0";
				}
				else if($v=='total')	{
					if($row['status'] == 0) $data2[] = $row['price'] - $row['commission'];
					else $data2[] = "0";
				}
				else if($v=='total2')	{
					if($row['status'] == 1) $data2[] = $row['price'] - $row['commission'];
					else $data2[] = "0";
				}
				else if($v=='type')	$data2[] = $type_array[$row[$v]];
				else if($v=='vendor_name')	$data2[] = $vendor_array[$row['vendor']];
				else if($v == 'confirm_date') $data2[] = date("Y-m-d H:i:s", $row[$v]);				
				else $data2[] = stripslashes($row[$v]);
			}
			$data[] = $data2;
		}	
		
	break;	
}

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:{$last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($title_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);

for($i=0; $i<$cells_cnt; $i++) {
	$excel->setActiveSheetIndex(0)->getColumnDimension( $cells[$i][0] )->setWidth($cells[$i][1]);
}
$excel->getActiveSheet()->fromArray($data,NULL,'A1');
$rows_cnt	= count($data);
$rows_cnt2	= $rows_cnt + 1;

foreach($number_arr as $k => $v) {
	$excel->getActiveSheet()->getStyle("{$v}2:{$v}{$rows_cnt}")->getNumberFormat()->setFormatCode('#,##0');
}

if(isset($number_arr2)) {
	foreach($number_arr2 as $k => $v) {
		$excel->getActiveSheet()->getStyle("{$v}2:{$v}{$rows_cnt}")->getNumberFormat()->setFormatCode('#,##0.00');
	}
}

if($type == 'sale') {
	$excel->getActiveSheet()->setCellValue("B{$rows_cnt2}", "매출합계")
							->setCellValue("C{$rows_cnt2}", "=SUM(C2:C{$rows_cnt})")
							->setCellValue("D{$rows_cnt2}", "=SUM(D2:D{$rows_cnt})")
							->setCellValue("E{$rows_cnt2}", "=SUM(C{$rows_cnt2}-D{$rows_cnt2})");

	$excel->getActiveSheet()->getStyle("C{$rows_cnt2}:D{$rows_cnt2}:E{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
	$excel->getActiveSheet()->getStyle("E{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
}
else if($type == 'margin') {
	$excel->getActiveSheet()->setCellValue("D{$rows_cnt2}", "마진합계")
							->setCellValue("E{$rows_cnt2}", "=SUM(E2:E{$rows_cnt})")
							->setCellValue("F{$rows_cnt2}", "=SUM(F2:F{$rows_cnt})")
							->setCellValue("G{$rows_cnt2}", "=SUM(E{$rows_cnt2}-F{$rows_cnt2})");

	$excel->getActiveSheet()->getStyle("E{$rows_cnt2}:F{$rows_cnt2}:G{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
	$excel->getActiveSheet()->getStyle("G{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
}
else if($type == 'vendor_sale') {
	$excel->getActiveSheet()->setCellValue("D{$rows_cnt2}", "합계")
							->setCellValue("E{$rows_cnt2}", "=SUM(E2:E{$rows_cnt})")
							->setCellValue("F{$rows_cnt2}", "=SUM(F2:F{$rows_cnt})")
							->setCellValue("G{$rows_cnt2}", "총매출")
							->setCellValue("H{$rows_cnt2}", "=SUM(E{$rows_cnt2}-F{$rows_cnt2})");

	$excel->getActiveSheet()->getStyle("E{$rows_cnt2}:F{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
	$excel->getActiveSheet()->getStyle("H{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
}
else if($type == 'vendor_calculate') {
	$excel->getActiveSheet()->setCellValue("D{$rows_cnt2}", "합계")
							->setCellValue("E{$rows_cnt2}", "=SUM(E2:E{$rows_cnt})")
							->setCellValue("F{$rows_cnt2}", "=SUM(F2:F{$rows_cnt})")
							->setCellValue("G{$rows_cnt2}", "=SUM(G2:G{$rows_cnt})")
							->setCellValue("H{$rows_cnt2}", "=SUM(H2:H{$rows_cnt})")
							->setCellValue("I{$rows_cnt2}", "=SUM(I2:I{$rows_cnt})")
							->setCellValue("J{$rows_cnt2}", "=SUM(J2:J{$rows_cnt})")
							->setCellValue("K{$rows_cnt2}", "총정산금액")
							->setCellValue("L{$rows_cnt2}", "=SUM(I{$rows_cnt2}-J{$rows_cnt2})");;

	$excel->getActiveSheet()->getStyle("E{$rows_cnt2}:F{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
	$excel->getActiveSheet()->getStyle("G{$rows_cnt2}:H{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
	$excel->getActiveSheet()->getStyle("I{$rows_cnt2}:J{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
	$excel->getActiveSheet()->getStyle("L{$rows_cnt2}")->getNumberFormat()->setFormatCode('#,##0');
}

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$fileName = EXCEL_FOLDER."/{$name}.xlsx";
$baseName = basename($fileName);
$writer->save($fileName);

switch($type) {
	case "member" :
		####################### 관리자 로그 ##########################	
		adminLog($my_id, "회원정보엑설다운", 4);
		####################### 관리자 로그 ##########################
	break;

	case "order" : case "order2" :  case "order3" : 
		####################### 관리자 로그 ##########################	
		adminLog($my_id, "주문정보엑설다운", 6);
		####################### 관리자 로그 ##########################
	break;
}

if($passwd) {	
	$zip = new ZipArchive();
	$zipFile = EXCEL_FOLDER."/{$name}.zip";
	if(file_exists($zipFile)) unlink($zipFile);
	$zipStatus = $zip->open($zipFile, ZipArchive::CREATE);
	if($zipStatus !== true) logMsg('파일생성이 실패 했습니다.[{$zipStatus}]');
	if(!$zip->setPassword($passwd)) logMsg('파일생성이 실패 했습니다.');
	if(!$zip->addFile($fileName, $baseName)) logMsg('파일생성이 실패 했습니다.');
	if (phpversion() >= '7.2.0') { 
		if(!$zip->setEncryptionName($baseName, ZipArchive::EM_AES_256)) logMsg('파일생성이 실패 했습니다.'); 
	}
	$zip->close();
	unlink($fileName);	
}
?>
<script>
parent.fileDown();
</script>