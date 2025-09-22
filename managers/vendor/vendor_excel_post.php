<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('VENDOR_FOLDER', '../../image/vendor');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

if(preg_match("/none/i",$_FILES["excel"]['tmp_name']) && !$_FILES["excel"]['tmp_name']) {
	logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
}

$ext = getExtension($_FILES["excel"]['name']);
if($ext!='xls' && $ext!='xlsx') {			
	logMsg('엑셀파일(xls, xlsx) 파일만 가능 합니다.');
}

$fileType = 'Excel2007';
if($ext == "xls") $fileType = 'Excel5';	

$passwd_encoding = checkPostVar('passwd_encoding');

include_once(PATH_LIB.'/PHPExcel/IOFactory.php');

$file = $_FILES['excel']['tmp_name'];

$objReader = PHPExcel_IOFactory::createReader($fileType);
//$objReader->setReadDataOnly(true);	

$objPHPExcel = $objReader->load($file);
$sheet = $objPHPExcel->getSheet(0);

$num_rows = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

$field_arr	= array('id', 'passwd', 'auth', 'sell', 'goods_auth', 'delivery_type', 'commission', 'account_cycle', 'bank_name', 'bank_num', 'bank_owner', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_type', 'comp_item', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'image1', 'image2');

$item_array	= array('id', 'passwd', 'auth', 'sell', 'goods_auth', 'delivery_type', 'commission', 'account_cycle', 'bank_name', 'bank_num', 'bank_owner', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_type', 'comp_item', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'image1', 'image2', 'signdate');

$sql		= "SELECT goods_delivery_info, goods_refund_info, goods_exchange_info, goods_as_info FROM mallRN_configuration WHERE uid = 1";
$data		= $mysql->one_row($sql);
$signdate	= time();

for($l = 2, $cnt = 0; $l <= $num_rows; $l++) {			
	$rowData = $sheet->rangeToArray('A'.$l.':'.$highestColumn.$l, NULL, TRUE, FALSE);
	
	foreach ($field_arr as $k => $v) {
		${$v} = trim(addslashes($rowData[0][$k]));
	}

	if(!$id || !$comp_name || !$comp_owner || !$comp_license_no) {
		continue;
	}
	
	$sql = "SELECT count(*) FROM mallRN_vendor WHERE id = '{$id}'";
	if($mysql->get_one($sql)>0) continue;

	$cnt++;

	if($passwd_encoding == 1) $passwd = md5($passwd);
	
	if($auth == "승인완료")		$auth = 'Y';
	else if($auth == "승인보류")	$auth = 'N';
	else						$auth = 'R';

	if($sell == "판매준비")		$sell = 'R';
	else if($sell == "판매중지")	$sell = 'N';
	else						$sell = 'A';

	if($goods_auth == "수동승인")	$goods_auth = 'P';
	else						$goods_auth = 'A';
	
	if($delivery_type == "본사배송")	$delivery_type = '1';
	else							$delivery_type = '0';

	if($account_cycle == "주1회")			$account_cycle = '1';
	else if($account_cycle == "월1회")	$account_cycle = '3';
	else if($account_cycle == "자율")		$account_cycle = '4';
	else								$account_cycle = '2';

	for($i = 1; $i < 3; $i ++) {
		$image		= ${"image".$i};
		$save_name	= "";

		if($image) {
			$orig_img = getURLimg($image);
			
			if(!preg_match("/not found/i",$orig_img) && !preg_match("/302 Found/i",$orig_img)) {
				$save_name = "{$id}_image{$i}.".getExtension($image);
				writeFile(VENDOR_FOLDER.'/'.$save_name, $orig_img);
			}
		}
		${"image".$i} = $save_name;
	}
		
	######################## 판매사등록  #########################			
	$sql = "INSERT INTO mallRN_vendor SET";
	foreach ($item_array as $k => $v) {
		if($k==count($item_array) - 1) $sql .= " {$v} = '{$$v}'";
		else $sql .= " {$v} = '{$$v}',";
	}		
	$mysql->query($sql);	
	
	$sql = "INSERT INTO mallRN_vendor_configuration SET 
				vendor					= '{$id}',
				goods_delivery_info		= '{$data['goods_delivery_info']}',
				goods_refund_info		= '{$data['goods_refund_info']}',
				goods_exchange_info		= '{$data['goods_exchange_info']}',
				goods_as_info			= '{$data['goods_as_info']}',
				delivery_p_price1		= '30000',
				delivery_p_price2		= '3000'
			";

	$mysql->query($sql);
	######################## 마일리지  #########################	
}

alertMsg("{$cnt}명의 판매사가 등록 되었습니다.", "vendor_list.php");

?>
