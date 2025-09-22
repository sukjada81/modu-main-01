<?php

$SKIN_DEFINE = array();
$SKIN_DEFINE['skin_name']	= "seriesWhite";
$SKIN_DEFINE['skin_desc']	= "화이트베이스의 기본스킨";

$SKIN_DEFINE['related_goods']	= "6"; //연관상품 출력갯수

/*관리자 페이지 출력용 이미지 사이즈 정의 */
$SKIN_DEFINE['width']				= "1200px";
$SKIN_DEFINE['view_width']			= "880px";
$SKIN_DEFINE['image1']				= "500px X 500px"; 
$SKIN_DEFINE['image2']				= "350px X 350px";
$SKIN_DEFINE['image3']				= "180px X 180px";
$SKIN_DEFINE['exhibition_image']	= "400px X 150px";


/* 이미지 사이즈 정의 자동리사이징에 사용 */
$IMG_DEFINE = array();
$IMG_DEFINE['image1']		= 500; 
$IMG_DEFINE['image2']		= 350; 
$IMG_DEFINE['image3']		= 180; 


/* 사용 배너 정의 및 사이즈 정의 */
$BANNER_DEFINE = array();
$BANNER_DEFINE["LOGO"] = ["로고", '300px X 90px', '우선순위 한개만 고정으로 노출됩니다.', 'top', '1'];
$BANNER_DEFINE["TOPU"] = ["상단위", '1200px X 50px', '우선순위 한개만 고정으로 노출됩니다.', 'top', '1'];
$BANNER_DEFINE["TOPL"] = ["상단좌측", '220px X 90px', '우선순위 한개만 고정으로 노출됩니다.', 'top', '1'];
$BANNER_DEFINE["MAINT"] = ["메인위", '1600px X 500px', '여러개 등록시 우선순위대로 롤링됩니다.', 'main', '0'];
$BANNER_DEFINE["MAINCL"] = ["메인센터좌측", '580x X 200px', '여러개 등록시 우선순위대로 롤링됩니다.', 'main', '0'];
$BANNER_DEFINE["MAINCR"] = ["메인센터우측", '300px X 200px', '우선순위 두개만 고정으로 노출됩니다.', 'main', '2'];

/* 모바일샵 배너 정의 및 사이즈 정의 */
$MOBILE_BANNER_DEFINE = array();
$MOBILE_BANNER_DEFINE["LOGO"] = ["로고", '300px X 40px', '우선순위 한개만 고정으로 노출됩니다.', 'top', '1'];
$MOBILE_BANNER_DEFINE["MAINT"] = ["메인위", '720px X 320px', '여러개 등록시 우선순위대로 롤링됩니다.', 'main', '0'];

?>