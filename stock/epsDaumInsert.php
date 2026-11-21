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
//1. 공휴일은 데이터 저장 안함 
//--------------------------------------
if($todayWa == "6" ||  $todayWa == "0"){
    $is_Insert = false;
    $resultMsg = "토,일 요일 제외";
}

//DB접속
include("./include/accsesDB.php");

//--------------------------------------
//2. 오늘 날짜와 저장 DB 이미 저장되어 있는지 체크 
//--------------------------------------
if($is_Insert){
    $query = "SELECT key_date FROM stock_valuations ORDER BY key_date DESC LIMIT 1";
    $res = mysql_query($query, $conn);
    
    if($res){
        $row = mysqli_fetch_assoc($res);    
        if($row && $today == $row["key_date"]){
            $is_Insert = false;
            $resultMsg = $today. " 이미 저장됨";
        } 
    }
}

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
    $numTrueKospi = 0;
    $numFalseKospi = 0;
    $numTotalKospi = 0;
    
    if(!empty($dataKospi['data'])){
        foreach($dataKospi['data'] as $stock){
            $numTotalKospi++;
            
            // 종목코드 처리 (A005930 -> 005930, 최대 6자리)
            $isu_cd = $stock['symbolCode'];
            if(substr($isu_cd, 0, 1) == 'A'){
                $isu_cd = substr($isu_cd, 1);
            }
            // 6자리로 제한
            $isu_cd = substr($isu_cd, 0, 6);
            
            $isu_nm = $stock['name'];
            $end_pr = $stock['tradePrice'];
            
            $sql = "INSERT INTO stock_valuations 
                    (key_date, market_gubun, isu_cd, isu_nm, iisu_code, end_pr)
                    VALUES ('$today', 'STK', '$isu_cd', '$isu_nm', '-', '$end_pr')";
            
            $res = mysql_query($sql, $conn);
            if($res){
                $numTrueKospi++;
            }else{
                $numFalseKospi++;
            }
        }
        $resultMsg = "코스피 저장: 총=".$numTotalKospi." 성공=".$numTrueKospi." 실패=".$numFalseKospi;
        echo $resultMsg."<br>";
    }else{
        $resultMsg = $today. " KOSPI 데이터 없음";
        echo $resultMsg."<br>";
    }
    
    // KOSDAQ 수집
    $dataKosdaq = getStockList('KOSDAQ', 3000);
    $numTrueKosdaq = 0;
    $numFalseKosdaq = 0;
    $numTotalKosdaq = 0;
    
    if(!empty($dataKosdaq['data'])){
        foreach($dataKosdaq['data'] as $stock){
            $numTotalKosdaq++;
            
            // 종목코드 처리 (A005930 -> 005930, 최대 6자리)
            $isu_cd = $stock['symbolCode'];
            if(substr($isu_cd, 0, 1) == 'A'){
                $isu_cd = substr($isu_cd, 1);
            }
            // 6자리로 제한
            $isu_cd = substr($isu_cd, 0, 6);
            
            $isu_nm = $stock['name'];
            $end_pr = $stock['tradePrice'];
            
            $sql = "INSERT INTO stock_valuations 
                    (key_date, market_gubun, isu_cd, isu_nm, iisu_code, end_pr)
                    VALUES ('$today', 'KSQ', '$isu_cd', '$isu_nm', '-', '$end_pr')";
            
            $res = mysql_query($sql, $conn);
            if($res){
                $numTrueKosdaq++;
            }else{
                $numFalseKosdaq++;
            }
        }
        $resultMsg = $resultMsg . " / 코스닥 저장: 총=".$numTotalKosdaq." 성공=".$numTrueKosdaq." 실패=".$numFalseKosdaq;
        echo "코스닥 저장: 총=".$numTotalKosdaq." 성공=".$numTrueKosdaq." 실패=".$numFalseKosdaq."<br>";
    }else{
        $resultMsg = $resultMsg . " / KOSDAQ 데이터 없음";
        echo "KOSDAQ 데이터 없음<br>";
    }
    
    //--------------------------------------
    //5. 이전 데이터 삭제
    //--------------------------------------
    $sql = "DELETE FROM stock_valuations WHERE key_date <> '$today'";
    mysql_query($sql, $conn);
    
    //--------------------------------------
    //6. ROA/ROE/GPA 및 재무지표 업데이트 (stock_daum_roe 테이블에서)
    //--------------------------------------
    $sql = "UPDATE stock_valuations ta, (
                SELECT isu_cd, roa, roe, gpa, finance_cnt, eps, per, bps, pbr, stk_dvd, dvd_yld
                FROM stock_daum_roe
            ) tb
            SET
                ta.roa = tb.roa,
                ta.roe = tb.roe,
                ta.gpa = tb.gpa,
                ta.finance_cnt = tb.finance_cnt,
                ta.prv_eps = tb.eps,
                ta.per = tb.per,
                ta.bps = tb.bps,
                ta.pbr = tb.pbr,
                ta.stk_dvd = tb.stk_dvd,
                ta.dvd_yld = tb.dvd_yld
            WHERE
                ta.isu_cd = tb.isu_cd";
    mysql_query($sql, $conn);
    
    //--------------------------------------
    //7. 마법공식 제외 종목 설정 (금융/보험업 종목)
    //--------------------------------------
    $sql = "UPDATE stock_valuations ta, (
                SELECT isu_cd
                FROM skock_Finance
            ) tb
            SET
                ta.magic_no = 'N'
            WHERE
                ta.isu_cd = tb.isu_cd";
    mysql_query($sql, $conn);
    
    //--------------------------------------
    //8. 데이터 저장 히스토리
    //--------------------------------------
    $sql = "REPLACE INTO stock_Insert_history (key_date, start_date, result_msg) 
            VALUES('$today', '$startTime', '$resultMsg')";
            
    mysql_query($sql, $conn); 
    mysqli_close($conn);
}

echo $resultMsg;
?>
</div>  
</div> 
</body>
</html>
