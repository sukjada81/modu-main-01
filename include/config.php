<?php

define('SHOP_VERSION',			'1.250813.0001'); // 버전

define('CONF_ROOT',				'');		// 웹하위 폴더 설치시 설치 하위 폴더명 ex)shop/
define('CONF_FLOAT_CNT',		0);			// 금액 소수점 자리 수 

define('ESCAFE_PATTERNS',		array('/union/i','/sleep/i','/update/i','/delete/i','/drop/i','/script/i','/iframe/i'));  // SQL Injection용 패턴
define('ESCAFE_REPLACES',		array('u&#110;ion','s&#108;eep','u&#112;date','de&#108;ete','d&#114;op', 's&#67;ript', 'i&#70;rame')); // SQL Injection용 치환값

define('ESCAFE_RE_PATTERNS',	array('/u&#110;ion/i','/s&#108;eep/i','/u&#112;date/i','/de&#108;ete/i','/d&#114;op/i','/s&#67;ript/i','/i&#70;rame/i'));  // SQL Injection용 패턴
define('ESCAFE_RE_REPLACES',	array('union','sleep','update','delete','drop','script','iframe')); // SQL Injection용 치환값

define('MOBILE_AGENT',			'phone|samsung|lgtel|mobile|[^A]skt|nokia|blackberry|BB10|android|sony');

date_default_timezone_set("Asia/Seoul");

$PHP_SELF = $_SERVER['PHP_SELF'];

?>