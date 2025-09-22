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

if($shop_config['naverpay_used'] == 0) Error("네이버페이 미사용 상태 입니다");
$test_mode		= "";
if($shop_config['naverpay_mode'] == 0) $test_mode = "test-";

$shopId			= $shop_config['naverpay_shop_id'];
$certiKey		= $shop_config['naverpay_key1'];

$backUrl		= ABSOLUTE_PATH_SHOP."{$Main}?channel=cart"; 
$queryString	= 'SHOP_ID='.urlencode($shopId); 
$queryString	.= '&CERTI_KEY='.urlencode($certiKey); 

$where			= " && selects = 1";

############################### 상품수량 체크 ###################################
if($rtn = checkCartOrder('')) {
	if($rtn == 1) {
		Error("상품품절 및 재고수량 초과로 다시 장바구니에서 주문 하시기 바랍니다.");		
	}
	else if($rtn == 2) Error("선택된 장바구니 상품 정보가 없습니다.");
}
############################### 상품수량 체크 ###################################

$sql = "SELECT DISTINCT(vendor_delivery) FROM mallRN_cart WHERE cart_id = '{$cart_id}' {$where} ORDER BY contact DESC, vendor_delivery ASC";
$mysql->query($sql);

$SUM_PRICES					= 0;
$SUM_DELIVERY				= 0;
$G_DELIVERY_TYPE4_CK_ARRAY	= array();

while($row = $mysql->fetch_array()) {
	$VENDOR				= $row['vendor_delivery'];
	$VENDOR_DELIVERY3	= "1"; //착불배송
	$vendor_sell		= 0;
	if(!$VENDOR) {
		$DELIVERY_TYPE		= $shop_config['delivery_type'];
		$DELIVERY_PRICE1	= $shop_config['delivery_p_price1'];
		$DELIVERY_PRICE2	= $shop_config['delivery_p_price2'];
	}
	else {
		$sql	= "SELECT comp_name, sell FROM mallRN_vendor WHERE id = '{$VENDOR}'";
		$vinfo	= $mysql->one_row($sql);
		
		if($vinfo['sell'] != 'A') $vendor_sell = 1;

		$sql			= "SELECT * FROM mallRN_vendor_configuration WHERE vendor = '{$VENDOR}'";
		$vshop_config	= $mysql->one_row($sql);

		$DELIVERY_TYPE		= $vshop_config['delivery_type'];		
		$DELIVERY_PRICE1	= $vshop_config['delivery_p_price1'];
		$DELIVERY_PRICE2	= $vshop_config['delivery_p_price2'];
	}

	if($DELIVERY_TYPE != 'D') $VENDOR_DELIVERY3 = "0";

	$sql = "SELECT * FROM mallRN_cart WHERE cart_id = '{$cart_id}' && vendor_delivery = '{$VENDOR}' {$where} ORDER BY vendor_delivery ASC, uid DESC";
	$mysql->query2($sql);

	$VENDOR_PRICE				= 0;
	$VENDOR_DELIVERY			= 0;
	$VENDOR_DELIVERY_CK_PRICE	= 0;
	$VENDOR_DELIVERY_FREE		= 0;

	while($row2 = $mysql->fetch_array(2)) {
		$data				= getCartGoodsInfo($row2);

		$suid				= "SHOP_".$data['uid'];
		$name				= $data['name'];
		$uprice				= (int) str_replace(",", "", $data['price']);
		$count				= $data['qty'];
		$tprice				= $uprice * $count;
		$option				= $data['option'];
		$G_DELIVERY_PRICE	= $data['delivery_price'];
		
		$item				= new ItemStack($suid, $name, $tprice, $uprice, $option, $count); 	
		$queryString		.= '&'.$item->makeQueryString(); 
			
		$VENDOR_PRICE		+= $tprice;

		if($data['delivery_type'] == 1) { 
			$VENDOR_DELIVERY_CK_PRICE += $tprice;
		}
		else if($data['delivery_type'] == 2) $VENDOR_DELIVERY_FREE = 1;
		else if($data['delivery_type'] == 4) {
			if(in_array($data['uid'], $G_DELIVERY_TYPE4_CK_ARRAY)) {				
				$G_DELIVERY_PRICE = 0;
			}
			else $G_DELIVERY_TYPE4_CK_ARRAY[] = $data['uid'];
		}
		else if($data['delivery_type'] == 5) $G_DELIVERY_PRICE	= $data['delivery_price'] * $row2['qty'];

		if($data['delivery_type'] != 3) $VENDOR_DELIVERY3 = "0";
		
		$VENDOR_DELIVERY	+= $G_DELIVERY_PRICE;
	}
	
	if($VENDOR_DELIVERY_CK_PRICE && $VENDOR_DELIVERY_FREE == 0 && $DELIVERY_TYPE != 'F') {
		if($VENDOR_DELIVERY_CK_PRICE < $DELIVERY_PRICE1) {
			$VENDOR_DELIVERY += $DELIVERY_PRICE2;
		}
	}

	$SUM_PRICES			+= $VENDOR_PRICE;
	$SUM_DELIVERY		+= $VENDOR_DELIVERY;	
}

$shippingPrice		= $SUM_DELIVERY;
$totalMoney			= $SUM_PRICES;

$shippingType		= 'PAYED'; 
if($VENDOR_DELIVERY3 == 1) $shippingType = "CASH_ON_DELIVERY";
if($shippingPrice == 0) $shippingType = "FREE";

$queryString		.= '&SHIPPING_TYPE='.$shippingType; 
$queryString		.= '&SHIPPING_PRICE='.$shippingPrice; 
$queryString		.= '&RESERVE1=&RESERVE2=&RESERVE3=&RESERVE4=&RESERVE5='; 
$queryString		.= '&BACK_URL='.$backUrl; 
if(isset($_COOKIE["NVADID"])) $queryString	.= '&SA_CLICK_ID='.$_COOKIE["NVADID"]; //CTS 

// CPA 스크립트 가이드 설치 업체는 해당 값 전달 
if(isset($_COOKIE["CPAValidator"])) $queryString .= '&CPA_INFLOW_CODE='.urlencode($_COOKIE["CPAValidator"]); 
if(isset($_COOKIE["NA_CO"])) $queryString .= '&NAVER_INFLOW_CODE='.urlencode($_COOKIE["NA_CO"]); 
//$queryString .= '&MCST_CULTURE_BENEFIT_YN=TRUE';

$totalPrice			= (int) $totalMoney + (int) $shippingPrice; 
$queryString		.= '&TOTAL_PRICE='.$totalPrice;

echo($queryString."<br>\n"); 

exit;
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
		$bodys.=@fgets($nc_sock,4096); 
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

//리턴받은 order_id로 주문서 page를 호출한다. 
//echo ($orderId."<br>\n"); 

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