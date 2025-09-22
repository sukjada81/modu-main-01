<?php 

include_once("../common/top.php");

define('SKIN_FOLDER', '../../skin');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","skin.html");
$tpl->scan_area("main");

$sql = "SELECT design_skin, design_skin_ing FROM mallRN_configuration WHERE uid=1";
$skin_info = $mysql->one_row($sql);
if($skin_info['design_skin']) {
	include(SKIN_FOLDER."/{$skin_info['design_skin']}/info/skin_define.php");
	$SKIN_THUM = SKIN_FOLDER."/{$skin_info['design_skin']}/info/skin_thum.gif";
	$SKIN_NAME = $SKIN_DEFINE['skin_name'];
	$SKIN_PATH = CONF_ROOT."/skin/{$skin_info['design_skin']}";
	$SKIN_DESC = $SKIN_DEFINE['skin_desc'];
	$tpl->parse("is_skin");
	unset($SKIN_DEFINE, $IMG_DEFINE);
}
else $tpl->parse("no_skin");

if($skin_info['design_skin_ing']) {
	include(SKIN_FOLDER."/{$skin_info['design_skin_ing']}/info/skin_define.php");
	$SKIN_THUM = SKIN_FOLDER."/{$skin_info['design_skin_ing']}/info/skin_thum.gif";
	$SKIN_NAME = $SKIN_DEFINE['skin_name'];
	$SKIN_PATH = CONF_ROOT."/skin/{$skin_info['design_skin_ing']}";
	$SKIN_DESC = $SKIN_DEFINE['skin_desc'];
	$tpl->parse("is_skin_ing_info");
	$tpl->parse("is_skin_ing");
	unset($SKIN_DEFINE, $IMG_DEFINE);
}

$handle = opendir(SKIN_FOLDER);
while ($skin_info = readdir($handle)) {
	if(!preg_match("/\./i",$skin_info)) {		
		include(SKIN_FOLDER."/{$skin_info}/info/skin_define.php");
		$SKIN_THUM = SKIN_FOLDER."/{$skin_info}/info/skin_thum.gif";
		$SKIN_NAME = $SKIN_DEFINE['skin_name'];
		$SKIN_PATH = CONF_ROOT."/skin/{$skin_info}";
		$SKIN_DESC = $SKIN_DEFINE['skin_desc'];
		$tpl->parse("loop_skin");
		unset($SKIN_DEFINE, $IMG_DEFINE);
	}
}
closedir($handle);

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>