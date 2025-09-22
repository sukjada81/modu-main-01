<?php 

include_once("../common/popup_top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_db_table_info_print.html");
$tpl->scan_area("main");

$table_name = checkGetVar('table_name');
if(!$table_name) alert("정보가 제대로 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".MYSQL_DB."' && TABLE_NAME = '{$table_name}' ";
if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
$item_array = array('ENGINE', 'VERSION', 'TABLE_ROWS', 'TABLE_COLLATION', 'TABLE_COMMENT', 'CREATE_TIME');
	
foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);
}

$item_array = array('ORDINAL_POSITION', 'COLUMN_NAME', 'COLUMN_TYPE', 'IS_NULLABLE', 'COLUMN_KEY', 'EXTRA', 'COLUMN_DEFAULT', 'COLUMN_COMMENT');

$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".MYSQL_DB."' && TABLE_NAME = '{$table_name}' ";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	
	foreach($item_array as $k => $v) {
		${$v} = stripslashes($row[$v]);
	}

	if($EXTRA == 'auto_increment')	$EXTRA = "<i class='far fa-circle'></i>";
	else							$EXTRA = "";	

	$tpl->parse("loop_columns");
}


$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>