<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

if(preg_match("/none/i",$_FILES["excel"]['tmp_name']) && !$_FILES["excel"]['tmp_name']) {
	logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
}

$ext = getExtension($_FILES["excel"]['name']);
if($ext!='xls' && $ext!='xlsx') {			
	logMsg('엑셀파일(xls, xlsx) 파일만 가능 합니다.');
}

$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

if($member_config['member_unavailable_id']) {
	$unavailable_id = explode(",", $member_config['member_unavailable_id']);
}
else $unavailable_id = "";

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

$field_arr	= array('name', 'id', 'passwd', 'email', 'tel', 'cell', 'postcode', 'address1', 'address2', 'birth', 'birth_sl', 'gender', 'marry', 'job', 'hobby', 'comp', 'comp_num', 'comp_owner', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'mailling', 'sms', 'level', 'mileage', 'add1', 'add2', 'add3', 'add4', 'add5');

$item_array	= array('name', 'id', 'passwd', 'level', 'mileage', 'tel', 'cell', 'postcode', 'address1', 'address2', 'email', 'birth', 'birth_sl', 'gender', 'marry', 'hobby', 'job', 'comp', 'comp_owner', 'comp_num', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'add1', 'add2', 'add3', 'add4', 'add5', 'mailling', 'sms', 'mailling_date', 'sms_date', 'auth', 'signdate');

$mailling_date	= time();
$sms_date		= time();
if($member_config['member_auth'] == 'A')	$auth = 'Y';
else										$auth = 'N';
$signdate		= time();

for($l = 2, $cnt = 0; $l <= $num_rows; $l++) {			
	$rowData = $sheet->rangeToArray('A'.$l.':'.$highestColumn.$l, NULL, TRUE, FALSE);
	
	foreach ($field_arr as $k => $v) {
		${$v} = trim(addslashes($rowData[0][$k]));
	}

	if(!$name || !$id || !$passwd || !$email) {
		continue;
	}
	
	$continue = 0;
	if($unavailable_id) {
		foreach($unavailable_id as $k => $v) {
			if($id == $v) {
				$continue = 1;
				break;
			}
		}	
	}

	$sql = "SELECT count(*) FROM mallRN_member WHERE id = '{$id}'";
	if($mysql->get_one($sql)>0) $continue = 1;

	if($continue == 1) continue;

	$cnt++;

	if($passwd_encoding == 1) $passwd = md5($passwd);
	
	if($birth_sl == "양력")		$birth_sl = 'S';
	else if($birth_sl == "음력") $birth_sl = 'L';
	else						$birth_sl = 'N';

	if($marry == "기혼")			$marry	= 'M';
	else if($marry == "미혼")		$marry	= 'S';
	else						$marry	= 'N';

	if($gender == "남성")			$gender	= 'M';
	else if($gender == "여성")	$gender	= 'F';
	else						$gender	= 'N';

	if($mailling != 'Y')		$mailling = 'N';
	if($sms != 'Y')				$sms = 'N';

	$tel		= str_replace("-", "", $tel);
	$cell		= str_replace("-", "", $cell);
	$comp_num	= str_replace("-", "", $comp_num);

	$job_arr	= explode(",", $member_config['member_form_job_info']);
	if(!in_array($job, $job_arr)) $job = '';

	$hobby_arr	= explode(",", $member_config['member_form_hobby_info']);
	if(!in_array($hobby, $hobby_arr)) $hobby = '';


	if($level > 99) $level = 1;

	######################## 회원등록  #########################			
	$sql = "INSERT INTO mallRN_member SET";
	foreach ($item_array as $k => $v) {
		if($k==count($item_array) - 1) $sql .= " {$v} = '{$$v}'";
		else $sql .= " {$v} = '{$$v}',";
	}		
	$mysql->query($sql);
	######################## 회원등록  #########################	

	######################## 마일리지  #########################
	if($mileage > 0) {
		$content	= "마일리지 이전";			
		$sql		= "INSERT INTO mallRN_mileage SET
							id				= '{$id}',
							content			= '{$content}',
							mileage			= '{$mileage}',
							signdate		= '{$signdate}'
					   ";
		$mysql->query($sql);
	}
	######################## 마일리지  #########################	
}

alertMsg("{$cnt}명의 회원이 등록 되었습니다.", "member_list.php");

?>
