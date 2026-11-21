<?php
header('Content-Type: text/html;charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>네이버 금융 PER/PBR/배당수익률 업데이트</title>
    <style>
    #align {   
        width:100%;   
        text-align:center;   
    }   
    #content {   
        margin:0 auto;
        width:800px;
        text-align:left;   
    }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    </style>
</head>
<body>
<div id="align">  
<div id="content">  
<h2>네이버 금융 PER/PBR/배당수익률 업데이트</h2>

<?php

$startTime = date("Y-m-d H:i:s");
$today = date("Ymd");

echo "<p class='info'>시작 시간: {$startTime}</p>";
echo "<hr>";

//DB접속
include("./include/accsesDB.php");

//--------------------------------------
// 네이버 금융에서 데이터 가져오기
//--------------------------------------
function getNaverFinancialData($sosok = 0, $page = 1) {
    // sosok: 0=코스피, 1=코스닥
    $url = "https://finance.naver.com/sise/sise_market_sum.naver?sosok={$sosok}&fieldIds=per&fieldIds=pbr&fieldIds=dividend_yield&page={$page}";
    
    $ch = curl_init();
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($httpCode != 200) {
        return false;
    }
    
    return $response;
}

//--------------------------------------
// HTML 파싱 함수
//--------------------------------------
function parseNaverStockData($html) {
    $stocks = [];
    
    // DOMDocument로 HTML 파싱
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);
    
    // 종목 테이블 행 찾기
    $rows = $xpath->query("//table[@class='type_2']//tr[contains(@onmouseover, 'mouseOver')]");
    
    foreach($rows as $row) {
        $cols = $xpath->query(".//td", $row);
        
        if($cols->length >= 12) {
            // 종목코드 추출
            $linkNode = $xpath->query(".//a[contains(@href, 'code=')]", $cols->item(1));
            if($linkNode->length > 0) {
                $href = $linkNode->item(0)->getAttribute('href');
                preg_match('/code=(\d+)/', $href, $matches);
                $code = isset($matches[1]) ? $matches[1] : '';
                
                if($code) {
                    // 종목명
                    $name = trim($linkNode->item(0)->nodeValue);
                    
                    // 현재가 (2번 컬럼)
                    $price = trim($cols->item(2)->nodeValue);
                    $price = str_replace(',', '', $price);
                    
                    // PER (5번 컬럼)
                    $per = trim($cols->item(10)->nodeValue);
                    $per = ($per == 'N/A' || $per == '') ? '0' : str_replace(',', '', $per);
                    
                    // PBR (7번 컬럼)
                    $pbr = trim($cols->item(11)->nodeValue);
                    $pbr = ($pbr == 'N/A' || $pbr == '') ? '0' : str_replace(',', '', $pbr);
                    
                    $stocks[] = [
                        'code' => $code,
                        'name' => $name,
                        'price' => $price,
                        'per' => $per,
                        'pbr' => $pbr 
                    ];
                }
            }
        }
    }
    
    return $stocks;
}

 

$totalUpdated = 0;
$totalSkipped = 0;
$totalNotFound = 0;

//--------------------------------------
// KOSPI 데이터 수집 (sosok=0)
//--------------------------------------
echo "<h3>📈 KOSPI 데이터 수집 중...</h3>";
$kospiUpdated = 0;
$kospiSkipped = 0;
$kospiNotFound = 0;

for($page = 1; $page <= 40; $page++) { // 최대 40페이지 (약 2000개 종목)
    $html = getNaverFinancialData(0, $page);
    
    if(!$html) {
        echo "<p class='error'>KOSPI 페이지 {$page} 데이터 가져오기 실패</p>";
        break;
    }
    
    $stocks = parseNaverStockData($html);
    
    if(empty($stocks)) {
        echo "<p class='info'>KOSPI 페이지 {$page}에서 더 이상 데이터 없음</p>";
        break;
    }
    
    foreach($stocks as $stock) {
        $isu_cd = $stock['code'];
        $per = $stock['per'];
        $pbr = $stock['pbr'];
        
 
        // stock_valuations 업데이트
        $sql = "UPDATE stock_valuations 
                SET per = '$per', 
                    pbr = '$pbr'
                WHERE isu_cd = '$isu_cd' ";
        
        $res = mysql_query($sql, $conn);
        if($res) {
            $affected = mysqli_affected_rows($conn);
            if($affected > 0) {
                $kospiUpdated++;
            } else {
                $kospiNotFound++;
            }
        }
    }
    
    echo "<p>KOSPI 페이지 {$page} 처리 완료 (종목수: ".count($stocks).")</p>";
    flush();
    ob_flush();
    
    // 서버 부하 방지를 위한 딜레이
    usleep(100000); // 0.1초 대기
}

echo "<p class='success'>✅ KOSPI 업데이트 완료: {$kospiUpdated}개 종목 업데이트</p>";
echo "<p class='info'>ℹ️ {$kospiNotFound}개 종목 미발견</p>";
echo "<hr>";

//--------------------------------------
// KOSDAQ 데이터 수집 (sosok=1)
//--------------------------------------
echo "<h3>📊 KOSDAQ 데이터 수집 중...</h3>";
$kosdaqUpdated = 0; 
$kosdaqNotFound = 0;

for($page = 1; $page <= 40; $page++) { // 최대 40페이지
    $html = getNaverFinancialData(1, $page);
    
    if(!$html) {
        echo "<p class='error'>KOSDAQ 페이지 {$page} 데이터 가져오기 실패</p>";
        break;
    }
    
    $stocks = parseNaverStockData($html);
    
    if(empty($stocks)) {
        echo "<p class='info'>KOSDAQ 페이지 {$page}에서 더 이상 데이터 없음</p>";
        break;
    }
    
    foreach($stocks as $stock) {
        $isu_cd = $stock['code'];
        $per = $stock['per'];
        $pbr = $stock['pbr'];
      
        
        // stock_valuations 업데이트
        $sql = "UPDATE stock_valuations 
                SET per = '$per', 
                    pbr = '$pbr'
                WHERE isu_cd = '$isu_cd' ";
        
        $res = mysql_query($sql, $conn);
        if($res) {
            $affected = mysqli_affected_rows($conn);
            if($affected > 0) {
                $kosdaqUpdated++;
            } else {
                $kosdaqNotFound++;
            }
        }
    }
    
    echo "<p>KOSDAQ 페이지 {$page} 처리 완료 (종목수: ".count($stocks).")</p>";
    flush();
    ob_flush();
    
    // 서버 부하 방지를 위한 딜레이
    usleep(100000); // 0.2초 대기
}

echo "<p class='success'>✅ KOSDAQ 업데이트 완료: {$kosdaqUpdated}개 종목 업데이트</p>";
echo "<p class='info'>ℹ️  {$kosdaqNotFound}개 종목 미발견</p>";
echo "<hr>";

//--------------------------------------
// 최종 결과
//--------------------------------------
$totalUpdated = $kospiUpdated + $kosdaqUpdated;
$totalNotFound = $kospiNotFound + $kosdaqNotFound;

$endTime = date("Y-m-d H:i:s");

echo "<h3 class='success'>🎉 네이버 금융 데이터 수집 완료</h3>";
echo "<ul>";
echo "<li><strong>총 업데이트:</strong> {$totalUpdated}개 종목</li>";
echo "<li><strong>미발견:</strong> {$totalNotFound}개 종목 (DB에 없음)</li>";
echo "<li><strong>시작 시간:</strong> {$startTime}</li>";
echo "<li><strong>종료 시간:</strong> {$endTime}</li>";
echo "</ul>";

mysqli_close($conn);
?>

</div>  
</div> 
</body>
</html>
