<?php

include_once('../common/ad_init.php');

define('VENDOR_FOLDER', '../../image/vendor');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_vendor';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$item_array			= array('comp_name', 'comp_owner', 'comp_license_no', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'bank_name', 'bank_num', 'bank_owner');

switch($mode) {
    
	case "modify" :

		$_POST['comp_name']	= specialStrReplace3($_POST['comp_name']);
		$_POST['cont_name']	= specialStrReplace3($_POST['cont_name']);
		
		if(!$_POST['comp_name'] || !$_POST['comp_license_no']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE id = '{$v_my_id}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 판매자가 존재하지 않거나 삭제 되었습니다.");

		$sql = "UPDATE mallRN_vendor SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v, '');
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}

		for($i=1;$i<3;$i++) {
			if(!preg_match("/none/i",$_FILES["image".$i]['tmp_name']) && $_FILES["image".$i]['tmp_name']) {

				$up_file = upFile($_FILES["image".$i]['tmp_name'],$_FILES["image".$i]['name'], VENDOR_FOLDER, 1, $row['id']."_image{$i}", 1);
				$sql .= ", image{$i} = '{$up_file}'";
			}			
			else {
				if($_POST['img_del'.$i]=='1') {
					@unlink(VENDOR_FOLDER.'/'.$row['image'.$i]);
					$sql .= ", image{$i} = ''";
				}
			}			
		}
		
		$sql .= " WHERE id = '{$v_my_id}'";
		$mysql->query($sql);

		logMsg("업체 정보가 수정 되었습니다.", "success");

	break;

	case "passwd" :
		
		$orig_passwd	= checkPostVar('orig_passwd');
		$passwd			= md5(checkPostVar('passwd'));

		if(!$orig_passwd || !$passwd) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}	
		
		$sql			= "SELECT passwd FROM mallRN_vendor WHERE id='{$v_my_id}'";
		$db_passwd		= $mysql->get_one($sql);
		
		if(md5($orig_passwd) != $db_passwd) logMsg("비밀번호가 일치 하지 않습니다. 다시 입력 하시기 바랍니다.");

		$sql = "UPDATE mallRN_vendor SET passwd = '{$passwd}' WHERE id = '{$v_my_id}'";
		$mysql->query($sql);

		####################### 관리자 로그 ##########################	
		$signdate	= time();
		$sql		= "INSERT INTO mallRN_vendor_log SET id = '{$v_my_id}', content = '{$v_my_name} 비번변경', type = 4, acc_ip = '{$_SERVER['REMOTE_ADDR']}', signdate = '{$signdate}'";
		$mysql->query($sql);
		####################### 관리자 로그 ##########################
		
		logMsg("업체 비밀번호가 변경 되었습니다.", "success");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
