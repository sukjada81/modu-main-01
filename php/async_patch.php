<?php
header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH', '../');

include_once('init.php');

/**
 * 외부 패치 서버 호출 제거:
 * 기존: $patch = implode("", socketPost("http://btob.kr/patch/patch.php"));
 * 대체: 패치 없음('0')으로 강제 세팅 → 아래 조건(if $patch[1] != '0')이 항상 false가 되어 DB작업 안 함
 */
$patch = [null, '0'];  // 패치 없음으로 처리
// $patch = explode("|*|", "ignored|*|0"); // (동등) 문자열 기반 초기화가 좋다면 이 라인 사용

if ($patch[1] != '0') {
    $patch2 = explode("|", addslashes($patch[1]));
    
    $sql       = "SELECT signdate FROM mallRN_configuration WHERE uid = 1";
    $open_time = $mysql->get_one($sql);

    if ($open_time < $patch2[0]) {
        $sql = "SELECT count(*) FROM mallRN_patch_check WHERE b_uid = '{$patch2[1]}'";
        if ($mysql->get_one($sql) == 0) {
            $sql = "INSERT INTO mallRN_patch_check SET b_uid = '{$patch2[1]}', subject = '{$patch2[2]}', signdate = '{$patch2[0]}'";
            $mysql->query($sql);
        }
    }
}
?>
