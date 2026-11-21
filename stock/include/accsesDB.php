<?php 
$conn = mysqli_connect('localhost', 'windmoter', 'rhkr7257!', 'windmoter');
if (!$conn) {
    die('DB 연결실패: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8'); // charset UTF8


/**
 * PHP 7.x 이상 버전과의 호환성을 위한 mysql_* 함수 폴리필(Polyfill)
 * 이 파일은 DB 연결($conn)이 설정된 후에 include 되어야 합니다.
 */

// mysql_query 함수가 정의되어 있지 않은 경우에만 새로 정의합니다.
if (!function_exists('mysql_query')) {

    /**
     * mysql_query의 동작을 흉내 내는 함수.
     * 내부적으로 mysqli_query를 올바른 인자 순서로 호출합니다.
     *
     * @param string $query SQL 쿼리 문자열
     * @param mysqli|null $link_identifier (무시됨) 기존 mysql_query 와의 호환성을 위한 인자
     * @return mysqli_result|bool
     */
    function mysql_query($query, $link_identifier = null) {
        // 전역 mysqli 연결 객체($conn)에 접근합니다.
        // 이 변수명은 실제 프로젝트에서 사용하는 DB 연결 변수명과 일치해야 합니다.
        global $conn; 

        if (!$conn) {
            // 데이터베이스 연결이 없는 경우 오류를 기록하거나 false를 반환합니다.
            error_log('mysql_query compatibility error: DB connection not found.');
            return false;
        }

        // mysqli_query를 올바른 인자 순서($conn, $query)로 호출합니다.
        return mysqli_query($conn, $query);
    }
}
?>