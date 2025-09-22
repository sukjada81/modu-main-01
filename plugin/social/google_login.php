<?php

define('DEFAULT_PATH',		'../../');
define('PATH_LIB',			'lib');
define('PATH_INCLUDE',		'include');

include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');
include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');
include_once(DEFAULT_PATH.PATH_LIB.'/lib.Shop.php');
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');   

$is_mobile = preg_match('/'.MOBILE_AGENT.'/i', $_SERVER['HTTP_USER_AGENT']);

$mysql = new mysqlClass(); 

$sql	= "SELECT * FROM mallRN_configuration_social WHERE site = 'GOOGLE'";
$data	= $mysql->one_row($sql);

if($data['used'] != 1) {
	if(checkGetVar('logout') == 1) Header ('Location: ../../');
	else alert("구글 아이디로 로그인 미사용 상태 입니다.", "close");
}

if(!$data['api_id'] || !$data['api_key']) {
	alert("구글 아이디로 로그인 정보가 등록되지 않았습니다.", "close");
}

$ClientID		= trim($data['api_id']);
$ClientSecret	= trim($data['api_key']);

include 'hybridauth/autoload.php';

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;


$config = [
    'callback' => HttpClient\Util::getCurrentUrl(),
    'providers' => [
        'Google' => [
            'enabled' => true,
            'keys' => [
                'id' => $ClientID,
                'secret' => $ClientSecret,
            ],
            'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
        ],
	],	
];

try {
    $hybridauth		= new Hybridauth($config);
    $adapter		= $hybridauth->authenticate('Google');

	if (isset($_GET['logout'])) {       
        $adapter->disconnect();
		Header ('Location: ../../');
		exit;
    }

    $tokens			= $adapter->getAccessToken();
    $userProfile	= $adapter->getUserProfile();
	//print_r($userProfile);
	
	$sql = "SELECT count(*) FROM mallRN_member WHERE sns_id != '' && sns_id = '{$userProfile->identifier}'";
	if($mysql->get_one($sql) == 1) {
		
		$sql	= "SELECT id FROM mallRN_member WHERE sns_id = '{$userProfile->identifier}'";
		$id		= $mysql->get_one($sql);

		makeLogin($id, 0, CONF_KEY); 

		################# 로그인 시간 기록 ####################
		$signdate		= time();
		$sql = "UPDATE mallRN_member SET cnts = cnts + 1, login_time = '{$signdate}' WHERE id = '{$id}'";
		$mysql->query($sql);
		################# 로그인 시간 기록 ####################

		################# 장바구니 ####################
		$cart_id		= getCartId('');		
		$sql = "UPDATE mallRN_cart SET cart_id = '".base64_encode($id)."' WHERE cart_id = '{$cart_id}'";
		SetCookie("cartId", base64_encode($id), 0, "/");
		################# 장바구니 ####################

		echo "<script>opener.location.href = '../../'; self.close();</script>";
		exit;
	}

	$birth = $userProfile->birthYear.$userProfile->birthMonth.$userProfile->birthDay;
	if($userProfile->gender == 'U') $userProfile->gender = 'N';

	echo "<form name='registForm' action='../../../php/popup_sns_regist.php' method='post'>\n
			<input type='hidden' name='sns_id' value='{$userProfile->identifier}' />\n
			<input type='hidden' name='name' value='{$userProfile->name}' />\n
			<input type='hidden' name='email' value='{$userProfile->email}' />\n
			<input type='hidden' name='cell' value='{$userProfile->phone}' />\n
			<input type='hidden' name='gender' value='{$userProfile->gender}' />\n
			<input type='hidden' name='birth' value='{$birth}' />\n
			<input type='hidden' name='sns_type' value='google' />\n
		 </form>\n
		 <script>document.registForm.submit();</script>		 
		 ";	

} catch (\Exception $e) {
    echo $e->getMessage();
}

?>
