<?php

ini_set('memory_limit', -1); // 메모리 제한을 해제해준다. 

include_once('../common/ad_init.php');

$mysql->msgType(1);

define('EXCEL_FOLDER', '../../image/excel');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$type = checkPostVar('type');
if(!$type) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

if(phpversion() < '5.2.0') {
    logMsg('PHP버전 5.2이상일 경우에만 지원 됩니다.');
}

$name	= isset($_POST['name']) ? $_POST['name'] : "{$type}_table_".date("Ymd");
$passwd = checkPostVar('passwd');

if(preg_match("/[^a-zA-Z0-9가-힣ㄱ-ㅎㅏ-ㅣ_.\-]/u",$name)) logMsg("파일명에 사용하지 못하는 특수문자가 있습니다.");

if($passwd) {
	
	$zipFile = EXCEL_FOLDER."/{$name}.zip";
	
	header("content-type: application/attachment");
	header("content-length: ".filesize($zipFile));
	header("content-disposition: attachment; filename=\"{$name}.zip\"");
	header("content-transfer-encoding: binary");
	@readfile($zipFile);
	unlink($zipFile);
}
else {
	
	$fileName = EXCEL_FOLDER."/{$name}.xlsx";

	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"{$name}.xlsx\"");
	header("Cache-Control: max-age=0");
	@readfile($fileName);
	unlink($fileName);
}
?>