<?php
// // 다음 금융 크롤링 함수
// function getStockList($market = 'KOSPI', $page = 1) {
//     // 요청 URL (다음 금융 내부 API)
//     $url = "https://finance.daum.net/api/quotes/stocks?market={$market}&sectorCode=026&fieldName=marketCap&order=desc&perPage=30&page={$page}&changes=UPPER_LIMIT,RISE,EVEN,FALL,LOWER_LIMIT";

//     // cURL 초기화
//     $ch = curl_init();
    
//     // 중요: 다음 금융은 헤더(Referer)를 체크합니다. 없으면 차단됩니다.
//     $headers = [
//         'Referer: https://finance.daum.net/domestic/market_cap',
//         'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
//         'Accept: application/json, text/javascript, */*; q=0.01'
//     ];

//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 무시 (개발환경용)

//     $response = curl_exec($ch);
//     curl_close($ch);

//     return json_decode($response, true);
// }

// // === 실행 로직 ===

// $market = 'KOSPI'; // 또는 'KOSDAQ'
// $page = 1;
// $all_stocks = [];

// echo "데이터 수집 시작...<br>";

// // while(true) {
//     // API 호출
//     $data = getStockList($market, $page);

//     // 데이터가 없으면(마지막 페이지 도달 시) 종료
//     // if (empty($data['data'])) {
//     //     break;
//     // }
// echo "데이터  ".json_encode($data);
//     // 결과 병합
//     foreach ($data['data'] as $stock) {
//         $all_stocks[] = [
//             'code' => $stock['symbolCode'], // 종목코드 (A005930 등)
//             'name' => $stock['name'],       // 종목명
//             'price' => $stock['tradePrice'],// 현재가 
//         ];
//     }
    
//     echo "{$page} 페이지 수집 완료 (" . count($data['data']) . "개)<br>";
    
//     $page++;
    
//     // 차단 방지를 위해 1초 대기 (매너)
//     sleep(1); 
    
//     // 테스트를 위해 3페이지만 하고 멈추려면 아래 주석 해제
//     // if ($page > 3) break; 
// // }

// echo "총 " . count($all_stocks) . "개의 종목을 수집했습니다.<br>";
// echo "<pre>";
// print_r($all_stocks[0]); // 첫 번째 종목 샘플 출력
// echo "</pre>";

?>


<?php
// KIND의 엑셀 다운로드 주소 (회원가입/로그인 필요 없음)
$url = "https://kind.krx.co.kr/corpgeneral/corpList.do?method=download";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$html = curl_exec($ch);
curl_close($ch);

// KIND는 기본 인코딩이 EUC-KR이므로 UTF-8로 변환
$html = mb_convert_encoding($html, 'UTF-8', 'EUC-KR');
//echo "html : ".$html;
// 간단하게 정규식으로 <tr> 데이터만 뽑아보기
preg_match_all('/<tr>(.*?)<\/tr>/s', $html, $matches);

$final_list = [];
foreach ($matches[1] as $index => $row) {
    if ($index == 0) continue; // 헤더 제외
    
    // <td> 태그 안의 내용 추출
    preg_match_all('/<td.*?>(.*?)<\/td>/s', $row, $cells);
    
    $name   = trim(strip_tags($cells[1][0])); // 회사명
    $code   = trim(strip_tags($cells[1][1])); // 코스피/코스닥
    $sector = trim(strip_tags($cells[1][2])); // 코드
	    $sectorInfo = trim(strip_tags($cells[1][3])); //  업종정보
    //echo "name : ".$name." code : ".$code." sector : ".$sector." sectorInfo : ".$sectorInfo."<br>";
    // 6자리 코드로 맞추기 (005930 등)
    $code = str_pad($code, 6, "0", STR_PAD_LEFT);
    
    $final_list[] = [
        'code' => $code,
        'name' => $name,
        'sector' => $sector,
			'sectorInfo' => $sectorInfo
    ];
}

// 금융/보험업만 필터링 출력 예시
foreach ($final_list as $item) {
    if (strpos($item['sectorInfo'], '금융') !== false || strpos($item['sectorInfo'], '보험') !== false
	 || strpos($item['sectorInfo'], '증권') !== false || strpos($item['sectorInfo'], '은행') !== false) {
        echo "코드: {$item['code']} | 이름: {$item['name']} | 업종: {$item['sector']} | 업종정보: {$item['sectorInfo']}<br>";
    }
}
?>