<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","cate.html");
$tpl->scan_area("main");

######################## 분류 생성 ##############################
$tmps1	= "CATEname = [";
$tmps2	= "CATEnum	= [";
$cnts	= 0;
$sql = "SELECT cate, cate_name, cate_sub FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
$mysql->query($sql);

while($row=$mysql->fetch_array()){    
	$row['cate_name'] = addslashes($row['cate_name']);
	if($row['cate_sub']==1) {
	    if($cnts==1) { 
			$tmps1.= ",['".$row['cate_name']."→'";		
			$tmps2.= ",['".$row['cate']."'";		
		}
		else { 
			$tmps1.= "['".$row['cate_name']."→'";		
			$tmps2.= "['".$row['cate']."'";		
        }		
    } 
	else {
		if($cnts==1) {
			$tmps1.= ",['".$row['cate_name']."'";		
			$tmps2.= ",['".$row['cate']."'";		
		} 
		else {
			$tmps1.= "['".$row['cate_name']."'";		
			$tmps2.= "['".$row['cate']."'";		
		}	
	}
	$tmps1.= "]";
	$tmps2.= "]";	
	$cnts = 1;	
}

$tmps1.= "]";
$tmps2.= "]";
######################## 분류 생성 ##############################

######################## 회원 등급 ##############################
$sql = "SELECT * FROM mallRN_member_level WHERE level < 100 ORDER BY level ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {

	$uid	= $row['uid'];
	$level	= $row['level'];
	$name	= stripslashes($row['name']);
		
	$tpl->parse("loop_level");	
}
######################## 회원 등급 ##############################

$CATE_IMAGE1_HELP = isset($SKIN_DEFINE['cate1_image1']) ? $SKIN_DEFINE['cate1_image1'] : "사용안함";
$CATE_IMAGE2_HELP = isset($SKIN_DEFINE['cate1_image2']) ? $SKIN_DEFINE['cate1_image2'] : "사용안함";
$CATE_IMAGE3_HELP = isset($SKIN_DEFINE['cate1_image3']) ? $SKIN_DEFINE['cate1_image3'] : "사용안함";

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>