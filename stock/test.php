<?php
header('Content-Type: text/html;charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
    #align {   
        width:100%;   
        text-align:center;   
    }   
    #content {   
        margin:0 auto;
        width:240px;
        text-align:left;   
    }
    </style>
</head>
<body>
<div id="align">  
<div id="content">  

<?php

//------------------------함수-----------------------//
//http 통신 web문서 가져오기
function protocalHttpWeb ($urlDomain , $urlStk )
{
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
	// 헤더는 제외하고 content 만 받음
	curl_setopt($curl_handle, CURLOPT_HEADER, 0);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_USERAGENT,  $urlDomain);
	//$script_List = tagListData('table', $json);
	$json = curl_exec($curl_handle); 
	return $json;
} 
//html 테이블에서 추출
function tagListData($strTag, $content)
{
	$reg =  sprintf("/\<%s[^>]*\>(.*?)\<\/%s\>/is" , $strTag, $strTag) ;
	preg_match_all($reg , $content, $matches , PREG_PATTERN_ORDER );
	return $matches[1];
}
//숫자 금액표시
function tagReplace($strTag )
{
	$strTag = strip_tags(preg_replace("/\s+/", "",  str_replace("N/A", "0",     str_replace(",", "",   $strTag))  ) );
	if($strTag =="" ){
		$strTag = 0;
	}
	return $strTag;

}

$is_Insert = true; // 데이터 저장 여부
$today = date("Ymd");
$todayWa = date("w"); // 요일
$resultMsg = ""; // 저장결과

$startTime = date("Y-m-d H:i:s");

 

//--------------------------------------
//3. 다음 금융 크롤링 함수
// https://finance.daum.net/api/quotes/stocks?market=KOSPI&fieldName=marketCap&order=desc&perPage=3000&page=1&changes=UPPER_LIMIT,RISE,EVEN,FALL,LOWER_LIMIT
// https://finance.daum.net/api/quotes/stocks?market=KOSDAQ&fieldName=marketCap&order=desc&perPage=3000&page=1&changes=UPPER_LIMIT,RISE,EVEN,FALL,LOWER_LIMIT
//--------------------------------------
function getStockList($market = 'KOSPI', $perPage = 3000) {
    // 요청 URL (다음 금융 내부 API)
    $url = "https://finance.daum.net/api/quotes/stocks?market={$market}&fieldName=marketCap&order=desc&perPage={$perPage}&page=1&changes=UPPER_LIMIT,RISE,EVEN,FALL,LOWER_LIMIT";

    // cURL 초기화
    $ch = curl_init();
    
    // 중요: 다음 금융은 헤더(Referer)를 체크합니다. 없으면 차단됩니다.
    $headers = [
        'Referer: https://finance.daum.net/domestic/market_cap',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
        'Accept: application/json, text/javascript, */*; q=0.01'
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 무시 (개발환경용)

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

//--------------------------------------
//4. 데이터 수집 및 저장
//--------------------------------------
if($is_Insert){
    echo "데이터 수집 시작...<br>";
    
    // KOSPI 수집
    $dataKospi = getStockList('KOSPI', 3000);
    echo json_encode($dataKospi);
}

echo $resultMsg;
?>
</div>  
</div> 
</body>
</html>
