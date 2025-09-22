<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", @$_SERVER['HTTP_REFERER'])) Error("정상적으로 등록하세요!");

//item data를 생성한다. 
class ItemStack { 
	var $id; 
	var $name; 
	var $tprice; 
	var $uprice; 
	var $option;
	var $count; 
	
	//option이 여러 종류라면, 선택된 옵션을 슬래시(/)로 구분해서 표시하는 것을 권장한다. 
	function ItemStack($_id, $_name, $_tprice, $_uprice, $_option, $_count) { 
		$this->id		= $_id; 
		$this->name		= $_name; 
		$this->tprice	= $_tprice; 
		$this->uprice	= $_uprice; 
		$this->option	= $_option;
		$this->count	= $_count; 
	} 
	
	function makeQueryString() { 
		$ret	= 'ITEM_ID=' . urlencode($this->id); 
		//$ret	.= '&EC_MALL_PID=' . urlencode($this->id); 		 
		$ret	.= '&ITEM_NAME=' . urlencode($this->name); 
		$ret	.= '&ITEM_COUNT=' . $this->count; 
		$ret	.= '&ITEM_OPTION=' . urlencode($this->option);
		$ret	.= '&ITEM_TPRICE=' . $this->tprice; 
		$ret	.= '&ITEM_UPRICE=' . $this->uprice; 
		return $ret; 
	} 
}; 

$uid			= checkGetVar('uid');
$option			= checkGetVar('option');

if($shop_config['naverpay_used'] == 0) Error("네이버페이 미사용 상태 입니다");
$test_mode		= "";
if($shop_config['naverpay_mode'] == 0) $test_mode = "test-";

$shopId			= $shop_config['naverpay_shop_id'];
$certiKey		= $shop_config['naverpay_key1'];

$sql			= "SELECT * FROM mallRN_goods WHERE uid ='{$uid}'";
$data			= $mysql->one_row($sql);

$goods_info		= getGoodsInfo($data);

$suid			= "SHOP_".$data['uid'];
$name			= $goods_info['name'];
$ouprice		= (int) str_replace(",", "", $goods_info['price']);
$totalMoney		= 0; 
$carr_price		= 0;

$backUrl		= ABSOLUTE_PATH_SHOP."{$Main}?channel=view/{$uid}"; 
$queryString	= 'SHOP_ID='.urlencode($shopId); 
$queryString	.= '&CERTI_KEY='.urlencode($certiKey); 

$options		= explode("|*|", $option);

foreach($options as $k => $v) {
	if($v) {
		$option	= explode("|", $v);
		$oqty	= $option[1] ? $option[1] : 1;	
		if($option[0] == '0') {
			if($data['qty_type'] == 0 && $data['qty'] < $oqty) Error("해당 상품이 재고량 {$data['qty']}개를 초과 했습니다.");	
			$op_price		= 0;
			$n_option		= "";
		}
		else {
			$n_option		= array();

			$sql	= "SELECT value, qty_type, qty, price FROM mallRN_goods_option WHERE uid = '{$option[0]}'";
			$odata	= $mysql->one_row($sql);

			if($odata['qty_type'] == 0 && $odata['qty'] < $oqty) Error("선택옵션({$odata['value']})이 재고량 {$odata['qty']}개를 초과 했습니다.");

			if($odata['price'] > 0)	{
				$add_price = " (+".number_format($odata['price'], CONF_FLOAT_CNT).")";			
			}
			else if($odata['price'] < 0) {
				$add_price = " (".number_format($odata['price'], CONF_FLOAT_CNT).")";
			}
			else $add_price = "";

			$option_info	= explode("|*|", $data['option_info']);		
			$value_info		= explode("|", $odata['value']);
			
			$option_array	= array();
			foreach($option_info as $k => $v) {
				$option_info2	= explode("|", $v);
				$option_array[] = $option_info2[0]." : ".$value_info[$k];
			}		
			$n_option[]		= join(" / ", $option_array).$add_price;

			$op_price		= $odata['price'];
		}

		$count			= $oqty; 
		$uprice			= $ouprice + $op_price;
		$tprice			= ($uprice * $count); 

		/************************* 배송비 관련 ***********************/	
		if($data['delivery_type'] = 4) { 
			$carr_price += $data['delivery_price'];				
		}	
		else if($data['delivery_type'] = 5) {
			$carr_price += $data['delivery_price'] * $count;
		}
		/************************* 배송비 관련 ***********************/

		if(count($n_option)>0) $noption = join('/',$n_option);
		else $noption = "";	
		
		$item			= new ItemStack($suid, $name, $tprice, $uprice, $noption, $count); 
		$totalMoney		+= $tprice; 
		$queryString .= '&'.$item->makeQueryString(); 		
	}
}

/************************* 배송비 관련 ***********************/
$carr_price_all = 0;

$shippingType	= 'PAYED'; 
if($data['delivery_type'] = 1) { 
	if($data['vendor']) {
		$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$data['vendor']}'";
		$vshop_config	= $mysql->one_row($sql);

		$shop_config['delivery_type']		= $vshop_config['delivery_type'];		
		$shop_config['delivery_p_price1']	= $vshop_config['delivery_p_price1'];
		$shop_config['delivery_p_price2']	= $vshop_config['delivery_p_price2'];		
	}

	if($shop_config['delivery_type'] == 'F') $shippingType		= 'FREE'; 
	else if($shop_config['delivery_type']=='D') $shippingType	= "CASH_ON_DELIVERY";
	else {
		if($totalMoney < $shop_config['delivery_p_price1'])  $carr_price_all += $shop_config['delivery_p_price1'];	
	}
}	
else if($data['delivery_type'] = 2) $shippingType = 'FREE'; 
else if($data['delivery_type'] = 3) $shippingType = "CASH_ON_DELIVERY";

$shippingPrice = $carr_price_all + $carr_price;
if($shippingPrice==0) $shippingType = 'FREE'; 
/************************* 배송비 관련 ***********************/

$queryString	.= '&SHIPPING_TYPE='.$shippingType; 
$queryString	.= '&SHIPPING_PRICE='.$shippingPrice; 
$queryString	.= '&RESERVE1=&RESERVE2=&RESERVE3=&RESERVE4=&RESERVE5='; 
$queryString	.= '&BACK_URL='.$backUrl; 
if(isset($_COOKIE["NVADID"])) $queryString	.= '&SA_CLICK_ID='.$_COOKIE["NVADID"]; //CTS 

// CPA 스크립트 가이드 설치 업체는 해당 값 전달 
if(isset($_COOKIE["CPAValidator"])) $queryString .= '&CPA_INFLOW_CODE='.urlencode($_COOKIE["CPAValidator"]); 
if(isset($_COOKIE["NA_CO"])) $queryString .= '&NAVER_INFLOW_CODE='.urlencode($_COOKIE["NA_CO"]); 

$totalPrice		= (int) $totalMoney + (int) $shippingPrice; 
$queryString	.= '&TOTAL_PRICE='.$totalPrice;

//echo($queryString."<br/>\n");

$req_addr = "ssl://{$test_mode}pay.naver.com";
$req_url = 'POST /customer/api/order.nhn HTTP/1.1'; // utf-8 
//$req_url = 'POST /customer/api/CP949/order.nhn HTTP/1.1'; // euc-kr 
$req_host = "{$test_mode}pay.naver.com";
$req_port = 443; 
$nc_sock = @fsockopen($req_addr, $req_port, $errno, $errstr); 

if ($nc_sock) { 
	fwrite($nc_sock, $req_url."\r\n" ); 
	fwrite($nc_sock, "Host: ".$req_host.":".$req_port."\r\n" ); 
	fwrite($nc_sock, "Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n"); 
	//fwrite($nc_sock, "Content-type: application/x-www-form-urlencoded; charset=CP949\r\n"); 
	fwrite($nc_sock, "Content-length: ".strlen($queryString)."\r\n"); 
	fwrite($nc_sock, "Accept: */*\r\n"); 
	fwrite($nc_sock, "\r\n"); 
	fwrite($nc_sock, $queryString."\r\n"); 
	fwrite($nc_sock, "\r\n"); 

	// get header 
	$headers	= "";
	while(!feof($nc_sock)){ 
		$header=fgets($nc_sock,4096); 
		if($header=="\r\n"){ 
			break; 
		} 
		else { 
			$headers .= $header; 
		} 
	} 
	
	// get body 
	$bodys		= "";
	while(!feof($nc_sock)){ 
		$bodys.= @fgets($nc_sock,4096); 
	} 
	
	fclose($nc_sock); 
	
	$resultCode = substr($headers,9,3); 
	
	if ($resultCode == 200) { 
		// success 
		$orderId = $bodys; 
	} 
	else { 
		// fail 
		echo $bodys; 
	} 
}
else { 
	echo "$errstr ($errno)<br>\n"; 
	exit(-1); 
	//에러처리 
} 

$orderUrl = "https://{$test_mode}pay.naver.com/customer/order.nhn";
?> 
<html> 
<body> 
<form name="frm" method="get" action="<?=$orderUrl?>"> 
<input type="hidden" name="ORDER_ID" value="<?=$orderId?>"> 
<input type="hidden" name="SHOP_ID" value="<?=$shopId?>"> 
<input type="hidden" name="TOTAL_PRICE" value="<?=$totalPrice?>"> 
</form> 
</body> 
<script> <? if ($resultCode == 200) { ?>
document.frm.target = "_top"; 
document.frm.submit(); 
<? } ?> 
</script> 
</html>