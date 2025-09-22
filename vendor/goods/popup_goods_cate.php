<?php 

include_once("../common/popup_top.php");

$search_variable	= array('field','keyword','cate','date_type','s_date','e_date','field2','keyword2','field3','keyword3','field4','keyword4','display_use','sell_use','option_use','milage_type','delivery_type','engine_use','order_priority','qty_type','vendor');	

$addstring	= "";
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if(strlen($value)>0) $addstring .= "&{$v}={$value}";
}		

$where = goodsTypeWhere(2, '');

$sql = "SELECT count(*) FROM mallRN_goods a WHERE a.vendor = '{$v_my_id}'";
$TOTALS2 = $mysql->get_one($sql);	

if($where) {
	if(!preg_match("/b./i",$where)) {
		$sql = "SELECT count(*) FROM mallRN_goods a WHERE a.vendor = '{$v_my_id}' {$where}";
	}
	else {
		$sql = "SELECT count(*) FROM  mallRN_goods a, mallRN_goods_cate b WHERE a.vendor = '{$v_my_id}' && a.uid = b.guid {$where}";
	}
	$TOTALS1 = $mysql->get_one($sql);
}
else {
	$TOTALS1 = $TOTALS2;
}
$TOTALS1 = number_format($TOTALS1);
$TOTALS2 = number_format($TOTALS2);

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_goods_cate.html");
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

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>