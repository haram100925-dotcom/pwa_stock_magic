<?php
/**
 * 다움 증권 API 상세 디버깅
 */

$isNo = $_GET['isNo'] ?? '005930';
$url = "http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=" . $isNo . "&finGubun=";

echo "<h2>다움 증권 API 상세 디버깅</h2>";
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
    echo "<h3>1. JavaScript 변수 추출</h3>";
    
    // changeFin 변수 추출
    if (preg_match('/var changeFin\s*=\s*(\[.*?\]);/s', $html, $matches)) {
        echo "<h4>changeFin 변수 발견:</h4>";
        echo "<pre>" . htmlspecialchars($matches[1]) . "</pre>";
        
        $decoded = json_decode($matches[1], true);
        if ($decoded) {
            echo "<h4>JSON 디코딩 결과:</h4>";
            echo "<pre>" . print_r($decoded, true) . "</pre>";
        }
    } else {
        echo "<p>changeFin 변수를 찾을 수 없습니다.</p>";
    }
    
    // 기타 JavaScript 변수들
    $jsPatterns = [
        'changeQuarter' => '/var changeQuarter\s*=\s*(\[.*?\]);/s',
        'companyData' => '/var companyData\s*=\s*(\{.*?\});/s',
        'viewState' => '/name="__VIEWSTATE".*?value="([^"]*)"/s'
    ];
    
    foreach ($jsPatterns as $name => $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            echo "<h4>{$name} 변수 발견:</h4>";
            echo "<pre>" . htmlspecialchars(substr($matches[1], 0, 500)) . "...</pre>";
        }
    }
    
    echo "<h3>2. 숨겨진 필드 분석</h3>";
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);
    
    $hiddenInputs = $xpath->query('//input[@type="hidden"]');
    echo "<p>숨겨진 필드 수: " . $hiddenInputs->length . "</p>";
    
    foreach ($hiddenInputs as $input) {
        $name = $input->getAttribute('name');
        $value = $input->getAttribute('value');
        echo "<p><strong>{$name}:</strong> " . htmlspecialchars(substr($value, 0, 100)) . "...</p>";
    }
    
    echo "<h3>3. 테이블 상세 분석</h3>";
    $tables = $xpath->query('//table');
    echo "<p>발견된 테이블 수: " . $tables->length . "</p>";
    
    for ($i = 0; $i < $tables->length; $i++) {
        echo "<h4>테이블 " . ($i + 1) . ":</h4>";
        $table = $tables->item($i);
        
        $class = $table->getAttribute('class');
        $id = $table->getAttribute('id');
        echo "<p>클래스: {$class}, ID: {$id}</p>";
        
        $rows = $xpath->query('.//tr', $table);
        echo "<p>행 수: " . $rows->length . "</p>";
        
        // 모든 행 출력
        foreach ($rows as $rowIndex => $row) {
            $cells = $xpath->query('.//td | .//th', $row);
            echo "<p><strong>행 {$rowIndex}:</strong> ";
            $cellTexts = array();
            foreach ($cells as $cell) {
                $cellTexts[] = trim($cell->textContent);
            }
            echo implode(' | ', $cellTexts) . "</p>";
        }
    }
    
    echo "<h3>4. 전체 HTML 길이: " . strlen($html) . " 문자</h3>";
    echo "<h3>5. HTML 내용 (처음 3000자):</h3>";
    echo "<pre>" . htmlspecialchars(substr($html, 0, 3000)) . "...</pre>";
    
} else {
    echo "<p>API 호출 실패 또는 데이터 없음</p>";
}
?>



