<?php
header('Content-Type: text/html;charset=utf-8'); //header()는 로 캐릭터셋

// 1. Composer로 설치한 라이브러리를 불러옵니다.
// 파일 경로는 실제 위치에 맞게 조정해주세요.
/*
your-service-account-file.json 부분을 실제 키 파일 이름으로 변경해주세요.
your-firebase-project-id 부분을 실제 Firebase 프로젝트 ID로 변경해주세요. (프로젝트 설정에서 확인 가능)
require __DIR__ . '/../include/vendor/autoload.php';의 경로가 실제 vendor 폴더 위치와 맞는지 확인해주세요

require __DIR__ . '/../include/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

// 2. FCM v1 API 인증을 위한 액세스 토큰을 받아오는 함수
function getAccessToken() {
    // 1단계에서 다운로드한 서비스 계정 키 파일의 전체 경로를 입력하세요.
    $keyFilePath = '/hosting/windmoter/html/stock/push/your-service-account-file.json';

    $credentials = new ServiceAccountCredentials(
        'https://www.googleapis.com/auth/firebase.messaging',
        $keyFilePath
    );

    $handler = HttpHandlerFactory::build();
    $token = $credentials->fetchAuthToken($handler);
    
    return $token['access_token'];
}

//DB접속
include("../include/accsesDB.php");
  
 
$Pushday =date("Ymd",strtotime("-1 day"));
$todayWa = date("w"); // 요일
$stockMinNum = (int)($_REQUEST['stockMinNum'] ?? 0); // 카운트
$stockMaxNum = (int)($_REQUEST['stockMaxNum'] ?? 10); // 카운트
 
//--------------------------------------종목별 구룹------------------  
$query = "  SELECT ANY_VALUE(a.fcm_id) AS fcm_id, ANY_VALUE(b.isu_nm) AS isu_nm, b.isu_cd
	FROM  stock_fcm_users a   JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id   
	WHERE   a.use_yn ='Y' AND b.send_yn ='Y' AND IFNULL(b.target_pr ,0) > 0  GROUP BY b.isu_cd LIMIT ".$stockMinNum .", ". $stockMaxNum;

$res = mysqli_query($conn, $query);
 
if($res && mysqli_num_rows($res) > 0){
	while ($row = mysqli_fetch_assoc($res)) { // push 발송


    //현재 가격 -네이버 nv": 6210, 현재가격
    $urlStk ="http://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:".$row["isu_cd"]."|SERVICE_RECENT_ITEM:".$row["isu_cd"]."&_callback=window.__jindo2_callback._3868";

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
    // 헤더는 제외하고 content 만 받음
    curl_setopt($curl_handle, CURLOPT_HEADER, 0);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://polling.finance.naver.com");
     
    $json = curl_exec($curl_handle); 
    $startNum = ( strrpos ( $json , "("  ) +1 ) ;
    $endNum = (strlen( $json)-$startNum-1  );
    $json = substr($json , $startNum ,$endNum);
    $json = iconv("EUC-KR" ,"UTF-8",$json );
    $result_array = json_decode($json);  
     
    $priceStock = 0;
    if (isset($result_array->result->areas[0]->datas[0]->nv)) {
        $priceStock = $result_array->result->areas[0]->datas[0]->nv; //현재가격
    }

    if ($priceStock > 0) {
		$push_query  = " SELECT  a.Token
	              FROM  stock_fcm_users a   JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id  
	              WHERE   a.use_yn ='Y' AND b.send_yn ='Y' AND IFNULL(b.target_pr ,0) <= ".$priceStock."     and isu_cd    = '".$row["isu_cd"]."'  ";
 
echo $push_query;
		$push_res  = mysqli_query($conn, $push_query);
 
		if($push_res && mysqli_num_rows($push_res) > 0){
			$registrationIds = array();
			while ($push_row = mysqli_fetch_assoc($push_res)) {
				$registrationIds[] = $push_row['Token']; 
	
			}

            // --- FCM HTTP v1 API로 변경된 부분 ---
            try {
                $accessToken = getAccessToken();
            } catch (Exception $e) {
                echo "<BR>액세스 토큰 생성 오류: " . $e->getMessage();
                continue; // 다음 종목으로 건너뛰기
            }

            // Firebase 프로젝트 ID를 입력하세요.
            $projectId = 'your-firebase-project-id';
            $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ];

            $success_count = 0;
            $failure_count = 0;

            // v1 API는 한 번에 한 토큰에만 보낼 수 있으므로, 각 토큰마다 반복해서 요청합니다.
            foreach ($registrationIds as $token) {
                $msg = [
                    "body" =>   $row['isu_nm']." ". number_format($priceStock) .'원 알림가에 도달 하였습니다.',
                    "title" => 'STOCK MAGIC'
                ];

                $data = [
                    "forwordUrl" => "https://cronya.pe.kr/stock/stockDetail.php?isNo=".$row['isu_cd']."&isNm=".rawurlencode($row['isu_nm'])
                ];

                $fields = [
                    'message' => [
                        'token' => $token,
                        'notification' => $msg,
                        'data' => $data
                    ]
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200) $success_count++; else $failure_count++;
            }

            echo "<BR>=======> Push sent. Success: $success_count, Failure: $failure_count";
		}
    }
	}
}
 
 

?> 
 