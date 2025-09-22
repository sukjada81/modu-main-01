<?php 

ini_set('memory_limit', -1); // 메모리 제한을 해제해준다. 
header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

define('EXCEL_FOLDER', '../../image/excel');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$status1	= checkPostVar('status1');
$status2	= checkPostVar('status2');

if(!$status1 && !$status2) logMsg('결제완료와 배송준비중 둘중 하나는 선택 되어야 됩니다.');

if(phpversion() < '5.2.0') {
    logMsg('PHP버전 5.2이상일 경우에만 지원 됩니다.');
}


$name			= "order_delivery_table_".date("Ymd");
$title_bgcolor = 'FFABCDEF';

include_once(PATH_LIB.'/PHPExcel.php');

$cells = array(
	array('A',20, 'signdate', '주문일시'),
	array('B',20, 'order_num',  '주문번호'),
	array('C',20, 'uid', '주문상품고유값'),
	array('D',20, 'delivery_number', '송장번호'),
	array('E',30, 'g_name', '주문상품명'),
	array('F',20, 'option', '옵션정보'),	
	array('G',15, 'qty', '주문상품수량'),
	array('H',15, 'status', '주문상태'),
	array('I',20, 'name2',  '수령자명'),
	array('J',20, 'cell2', '수령자연락처'),
	array('K',20, 'postcode', '배송지우편번호'),
	array('L',40, 'address1', '배송지'),
	array('M',40, 'message', '요청사항'),			
);	

$number_arr = array('G');
$cells_cnt	= count($cells);
$last_char	= $cells[$cells_cnt-1][0];

if($status1 == 1 && $status2 == 1) $where = " && (status = 1 || status = 2)";
else if($status1 == 1) $where = " && status = 1";
else if($status2 == 1) $where = " && status = 2";

$sql	= "SELECT c.*, b.name2, b.cell2, b.postcode, b.address1, b.address2, b.message FROM ( SELECT a.order_num, a.name2, a.cell2, a.postcode, a.address1, a.address2, a.message FROM mallRN_order_info a WHERE reals = 1 ) b JOIN mallRN_order_goods c ON b.order_num = c.order_num && vendor_delivery = '' {$where} ORDER BY c.uid ASC";
$mysql->query($sql);

$data = array();		
$data[] = array_column($cells, 3);

$status_array		= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array		= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 		

$tmp_order_num		= "";
while($row = $mysql->fetch_array()){
	$data2 = array();
	for($i=0; $i < $cells_cnt; $i++) {
		$v = $cells[$i][2];
		
		if($v == 'signdate')	$data2[] = date("Y-m-d H:i:s", $row[$v]);				
		else if($v == 'status') {
			if($row['status2'])	$data2[] = $status_array[$row['status']].$status2_array[$row['status2']];
			else					$data2[] = $status_array[$row['status']];
		}
		else if($v == 'address1') {
			$data2[] = stripslashes($row['address1']).' '.stripslashes($row['address2']);
		}
		else if($v == 'delivery_number') $data2[] = ''; 
		else $data2[] = stripslashes($row[$v]);
	}
				
	$data[] = $data2;
	$tmp_order_num	= $row['order_num'];
}	

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:{$last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($title_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);

for($i=0; $i<$cells_cnt; $i++) {
	$excel->setActiveSheetIndex(0)->getColumnDimension( $cells[$i][0] )->setWidth($cells[$i][1]);
}
$excel->getActiveSheet()->fromArray($data,NULL,'A1');
$rows_cnt = count($data);
foreach($number_arr as $k => $v) {
	$excel->getActiveSheet()->getStyle("{$v}2:{$v}{$rows_cnt}")->getNumberFormat()->setFormatCode('#,##0');
}

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$fileName = EXCEL_FOLDER."/{$name}.xlsx";
$baseName = basename($fileName);
$writer->save($fileName);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"{$name}.xlsx\"");
header("Cache-Control: max-age=0");
@readfile($fileName);
unlink($fileName);

?>