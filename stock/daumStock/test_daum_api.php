<?php
/**
 * 다움 증권 API 테스트 및 디버깅
 */

$isNo = $_GET['isNo'] ?? '005930'; // 삼성전자
$url = "http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=" . $isNo . "&finGubun=";

echo "<h2>다움 증권 API 테스트</h2>";
echo "<p>URL: " . $url . "</p>";

$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $url);
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($curl_handle, CURLOPT_REFERER, 'http://wisefn.stock.daum.net/');
curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);

$html = curl_exec($curl_handle);
$httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
curl_close($curl_handle);

echo "<h3>HTTP 응답 코드: " . $httpCode . "</h3>";

if ($httpCode === 200 && $html) {
    echo "<h3>HTML 내용 (처음 2000자):</h3>";
    echo "<pre>" . htmlspecialchars(substr($html, 0, 2000)) . "...</pre>";
    
    // 테이블 구조 분석
    echo "<h3>테이블 분석:</h3>";
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);
    
    $tables = $xpath->query('//table');
    echo "<p>발견된 테이블 수: " . $tables->length . "</p>";
    
    for ($i = 0; $i < $tables->length; $i++) {
        echo "<h4>테이블 " . ($i + 1) . ":</h4>";
        $table = $tables->item($i);
        
        // 테이블의 클래스 확인
        $class = $table->getAttribute('class');
        echo "<p>클래스: " . $class . "</p>";
        
        // 행 수 확인
        $rows = $xpath->query('.//tr', $table);
        echo "<p>행 수: " . $rows->length . "</p>";
        
        // 첫 번째 행의 셀 내용 확인
        if ($rows->length > 0) {
            $firstRow = $rows->item(0);
            $cells = $xpath->query('.//td | .//th', $firstRow);
            echo "<p>첫 번째 행의 셀 수: " . $cells->length . "</p>";
            
            echo "<p>첫 번째 행 내용:</p>";
            echo "<ul>";
            for ($j = 0; $j < $cells->length; $j++) {
                $cellText = trim($cells->item($j)->textContent);
                if (!empty($cellText)) {
                    echo "<li>" . htmlspecialchars($cellText) . "</li>";
                }
            }
            echo "</ul>";
        }
        
        // 테이블 내용 일부 출력
        echo "<p>테이블 내용 (처음 500자):</p>";
        echo "<pre>" . htmlspecialchars(substr($table->textContent, 0, 500)) . "...</pre>";
        
        echo "<hr>";
    }
    
} else {
    echo "<p>API 호출 실패 또는 데이터 없음</p>";
}
?>
