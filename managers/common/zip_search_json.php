<?php
/**
 * 주소검색 API (제거, 카카오만 사용)
 * - 이 엔드포인트는 항상 "다음(카카오) 주소검색"만 사용하라는 신호를 반환합니다.
 * - 프런트에서는 zipcode === "DAUM_ONLY" 인 경우 카카오 우편번호 팝업을 실행하세요.
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// CORS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 기존 공통 초기화는 불필요 (등 외부 검색 제거)
// include_once('../common/ad_init.php');

// GET 파라미터(호환성 유지용)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// 반환 배열 (고정 응답)
$response = [
    [
        "label"   => "'다음(카카오) 주소검색'을 사용하세요.",
        "zipcode" => "DAUM_ONLY",   // 프런트에서 이 값을 체크하여 카카오 팝업을 실행
        "address" => ""
    ]
];

// JSON 출력
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
