<?php

define('DEFAULT_PATH',	'../');
include_once('init.php');

if($shop_config['naverpay_used'] == 0) Error("네이버페이 미사용 상태 입니다");

$query	= $_SERVER['QUERY_STRING']; 
if(!$query) Error("필수정보가 넘어오지 못했습니다."); 

$vars	= array(); 
foreach(explode('&', $query) as $pair) { 
	list($key, $value)	= explode('=', $pair);
	$key				= urldecode($key); 
	$value				= urldecode($value); 
	$vars[$key][]		= $value; 
} 
$itemIds = $vars['ITEM_ID']; 
if (count($itemIds) < 1) Error('ITEM_ID 는 필수입니다.'); 

header("Content-type: text/xml; charset=utf-8"); 
header("Last-Modified: ".gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache"); 

$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE used = 1 ORDER BY sequence ASC";
$mysql->query($sql);

$cate_array = Array();
while($row=$mysql->fetch_array()){    
	$cate_array[$row['cate']] = stripslashes($row['cate_name']);	
}

echo('<'.'?xml version="1.0" encoding="utf-8"?'.">\n");
?> 

<response>

<?php 

for($p = 0; $p < count($itemIds); $p++) {
	$uid			= str_replace("SHOP_", "", $itemIds[$p]);
	$sql			= "SELECT * FROM mallRN_goods WHERE uid = '{$uid}'";
	$data			= $mysql->one_row($sql);
	if(!$data) continue;
	
	$goods_info		= getGoodsInfo($data);
	
	$id				= "SHOP_".$data['uid'];
	$name			= $goods_info['name'];
	$image			= str_replace(DEFAULT_PATH, ABSOLUTE_PATH_SHOP, $goods_info['image1']);
	$thumb			= str_replace(DEFAULT_PATH, ABSOLUTE_PATH_SHOP, $goods_info['image3']);
	$url			= ABSOLUTE_PATH_SHOP."{$Main}?channel=view&uid={$uid}";
	$description	= str_replace("&nbsp;", " ", html2txt($data['detail']));
	if(!$description) $description = "-";
	$price			= (int) str_replace(",", "", $goods_info['price']);
	
	$quantity		= 99999;
	if($data['qty_type'] == 0) $quantity = $data['qty'];
	if($data['sale_use'] == 0) $quantity = 0;

	if(substr($data['cate'], 9, 3) !='000') {
		$cate4 = $cate_array[$data['cate']];
		$caid4 = $data['cate'];
	} 
	else $cate4 = $caid4 = '';
	if(substr($data['cate'],6 , 3) !='000') {
		$cate3 = $cate_array[substr($data['cate'],0 , 9)."000"];
		$caid3 = substr($data['cate'],0,9);
	} 
	else $cate3 = $caid3 = '';
	if(substr($data['cate'], 3, 3) != '000') {
		$cate2 = $cate_array[substr($data['cate'], 0, 6)."000000"];
		$caid2 = substr($data['cate'],0,6);
	}
	else $cate2 = $caid2 = '';
	$cate1	= $cate_array[substr($data['cate'],0,3)."000000000"];
	$caid1	= substr($data['cate'],0,3);

	$OPTIONS = "";
	if($data['option_use'] == 1) {		
		$option_info = explode("|*|", $data['option_info']);		
		for($ii=0, $cnt = count($option_info); $ii < $cnt; $ii ++) {
			$option_info2	= explode("|", $option_info[$ii]);
			$OPTIONS .= "<option name='{$option_info2[0]}'>";
			$option_info3	= explode(",", $option_info2[1]);
			foreach($option_info3 as $k => $v) {
				$OPTIONS .= " <select>{$v}</select> ";
			}
			$OPTIONS .= "</option>\n";
		}
	}

?>
<item id="<?=$id?>">
	<name><![CDATA[ <?=$name?> ]]></name>
	<url><![CDATA[ <?=$url?>> ]]></url>
	<description><![CDATA[ <?=$description?> ]]></description>
	<image><![CDATA[ <?=$image?>> ]]></image>
	<thumb><![CDATA[ <?=$thumb?>> ]]></thumb>
	<price><?=$price?></price> 
	<quantity><?=$quantity?></quantity>
	<category>
		<first id="<?=$caid1?>"><?=$cate1?></first>
		<second id="<?=$caid2?>"><?=$cate2?></second>
		<third id="<?=$caid3?>"><?=$cate3?></third>
		<fourth id="<?=$caid4?>"><?=$cate4?></fourth>
	</category>
	<options>
		<?=$OPTIONS?>
	</options>
</item>

<?php
}
//end while; 
echo('</response>'); 
?>