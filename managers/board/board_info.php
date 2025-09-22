<?php 

include_once("../common/top.php");

define('SKIN_FOLDER',	'./skin');
define('UPLOAD_FOLDER', '../../image/board');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","board_info.html");
$tpl->scan_area("main");

$mode				= isset($_GET['mode']) ? $_GET['mode'] : 'write';
$cate_max_num		= 100;
$access_array		= array('list', 'write', 'view', 'reply', 'comment');
$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify') {
	
	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

	$sql = "SELECT * FROM mallRN_board_manager WHERE uid = '{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('name', 'id', 'skin', 'record_num', 'types', 'start_page', 'view_type', 'secret_type', 'privacy_type', 'new_icon', 'upload_file', 'upload_size', 'links', 'cate_info', 'access_list', 'access_list_level', 'access_view', 'access_view_level', 'access_write', 'access_write_level', 'access_reply', 'access_reply_level', 'access_comment', 'access_comment_level', 'form_add1_title', 'form_add1', 'form_add2_title', 'form_add2', 'form_add3_title', 'form_add3', 'form_add4_title', 'form_add4', 'form_add5_title', 'form_add5', 'header', 'header_content', 'footer', 'footer_content');

	for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
		${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
	}
	
	${"checked_types_".$types}						= "checked='checked'";
	${"checked_start_page_".$start_page}			= "checked='checked'";
	${"checked_view_type_".$view_type}				= "checked='checked'";
	${"checked_privacy_type_".$privacy_type}		= "checked='checked'";
	${"checked_upload_file_".$upload_file}			= "checked='checked'";
	${"checked_links_".$links}						= "checked='checked'";
	${"checked_secret_type_".$secret_type}			= "checked='checked'";
	${"checked_access_list_".$access_list}			= "checked='checked'";
	${"checked_access_view_".$access_view}			= "checked='checked'";
	${"checked_access_write_".$access_write}		= "checked='checked'";
	${"checked_access_reply_".$access_reply}		= "checked='checked'";
	${"checked_access_comment_".$access_comment}	= "checked='checked'";
	${"checked_header_".$header}					= "checked='checked'";
	${"checked_footer_".$footer}					= "checked='checked'";
	$header_content									= add_escape_re_string($header_content);
	$footer_content									= add_escape_re_string($footer_content);

	if($cate_info) {
		$cate_info = explode("|*|", $cate_info);
		$cate_max_num = $cate_info[0];
		if($cate_max_num > 100) {
			for($i=1,$cnt=count($cate_info); $i<$cnt; $i++) {
				$cate_info2 = explode("|",$cate_info[$i]);

				$cate_num = $cate_info2[0];
				$cate_name = $cate_info2[1];
				
				$tpl->parse("loop_cate");
			}
		}
	}
	
	$readonly	= "readonly";
	$TTL		= "수정";	

}
else {
	
	$skin						= "basic_general";
	$record_num					= "10";
	$checked_types_0			= "checked='checked'";
	$checked_start_page_0		= "checked='checked'";
	$checked_view_type_0		= "checked='checked'";
	$checked_privacy_type_1		= "checked='checked'";
	$checked_upload_file_0		= "checked='checked'";
	$checked_links_0			= "checked='checked'";
	$checked_secret_type_0		= "checked='checked'";
	$checked_access_list_0		= "checked='checked'";
	$checked_access_view_0		= "checked='checked'";
	$checked_access_write_0		= "checked='checked'";
	$checked_access_reply_0		= "checked='checked'";
	$checked_access_comment_0	= "checked='checked'";
	$checked_header_0			= "checked='checked'";
	$checked_footer_0			= "checked='checked'";
	$new_icon					= "24";
	$upload_size				= "5";
	$access_list_level			= "";
	$access_write_level			= "";
	$access_view_level			= "";
	$access_reply_level			= "";
	$access_comment_level		= "";
	
	for($i=1;$i<6;$i++) {
		${"form_add".$i}			= "0";
		${"form_add".$i."_title"}	= "";
	}
		
	$mode		= "write";
	$readonly	= "";
	$TTL		= "등록";
	
}

$handle = opendir(SKIN_FOLDER);
while ($skin_info = readdir($handle)) {
	if(!preg_match("/\./i",$skin_info)) {		
		$SKIN_NAME = $skin_info;
		$tpl->parse("loop_skin");
		unset($SKIN_NAME);
	}
}
closedir($handle);

######################## 회원 등급 ##############################
$sql = "SELECT * FROM mallRN_member_level WHERE level < 100 ORDER BY level ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {

	$M_UID		= $row['uid'];
	$M_LEVEL	= $row['level'];
	$M_NAME		= stripslashes($row['name']);
	
	foreach($access_array as $k => $v) {
		$access = explode(",", ${"access_".$v."_level"});
		if(in_array($M_LEVEL, $access)) $M_CHECKED = "checked='checked'";
		else							$M_CHECKED = "";			
		$tpl->parse("loop_{$v}_level");
	}	
}
######################## 회원 등급 ##############################

for($i=1;$i<6;$i++) {
	$checked_form_add_0 = $checked_form_add_1 = $checked_form_add_2 = "";
	${"checked_form_add_".${"form_add".$i}}	= "checked='chedked'";
	$form_add_title							= ${"form_add".$i."_title"};
	$tpl->parse("loop_add");
}


$cate_num = $cate_name = "";
$tpl->parse("loop_cate");

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>