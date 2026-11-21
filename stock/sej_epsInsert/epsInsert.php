<?php
header('Content-Type: text/html;charset=utf-8'); //header()는 로 캐릭터셋
?> 
<meta charset="utf-8">
 
 
 


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
	$script_List = tagListData('table', $json);
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
//------------------------함수-----------------------//

$is_Insert = "Y"; // 데이터 저장 여부
$is_todayWa_Insert = "Y";
$today = date("Ymd");
$todayWa = date("w"); // 요일
$resultMsg = ""; // 저장결과

 /*테스트 데이터*/
 //$today =date("Ymd",strtotime("-4 day"));
//$todayWa = "5";

 
$startTime = date("Y-m-d H:i:s" );

//--------------------------------------
//1. 공휴일은 데이터 저장 안함 
//--------------------------------------
if($todayWa == "6" ||  $todayWa == "0" ){
	$is_Insert = "N";
	$is_todayWa_Insert = "N";
	$resultMsg = "토,일 요일 제외";
}  
//DB접속
include("../include/accsesDB.php");
		echo $is_Insert;
//--------------------------------------
//2. 오늘 날짜와 저장 DB  이미저장되어 있는지 체크 
//--------------------------------------
if($is_Insert == "Y" ){
 
	$query = "select  key_date  from stock_allocation_top order by  key_date  desc limit 1";
	$res = mysql_query($query, $conn);

	if($res){
		$row = mysqli_fetch_assoc($res);	
		echo $today."==". $row["key_date"];
		if($today == $row["key_date"]){
			$is_Insert  = "N";
			$resultMsg =$today. " 이미 저장됨";
		} 
	}
} 

				 
if($is_Insert == "Y" ){ 
				//--------------------------------------
				//6.거래정보 저장
				//-------------------------------------- 
				include("stock_allocation_insert.php");  
				include("stock_eps_top_insert.php");
					//			 echo $is_Insert."3================";
				include("stock_eps_left_insert.php");
				include("stock_operating_profit_top_insert.php");
				include("stock_roeTop_top_insert.php");
				include("stock_roic_top_insert.php");		

				//--------------------------------------
				//7. 업데이트 정보 설치 모든 앱에 푸쉬 함
				//--------------------------------------
				// 헤더 부분
				/*
				$headers = array(
						'Content-Type:application/json',
						'Authorization:key=AIzaSyBLnCIOIlKXsErfB_jviXmrcLNrT3FEAbY'
				);
				
				// 푸시 내용, data 부분을 자유롭게 사용해 클라이언트에서 분기할 수 있음.
				$arr = array();
				$arr['to'] = "/topics/notice-for-all" ;
				$arr['notification'] = array();
				$arr['notification']['body'] = ' 한국거래소 ['.$startTime .'] 최신 데이터 업데이트 되었습니다.';
				$arr['notification']['title'] = 'Stock Magic 공지';
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://gcm-http.googleapis.com/gcm/send');
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($arr));
				$response = curl_exec($ch);
				curl_close($ch);
				
				// 푸시 전송 결과 반환.
				$obj = json_decode($response);
				
				// 푸시 전송시 성공 수량 반환.
				$cnt = $obj->{"success"};
				  
	 
				$urlStk ="https://cronya.pe.kr/stock/include/stockTotalList.php";
				$curl_handle = curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
				// 헤더는 제외하고 content 만 받음
				curl_setopt($curl_handle, CURLOPT_HEADER, 0);
				curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl_handle, CURLOPT_USERAGENT,  "https://cronya.pe.kr");
				
				
				$json = curl_exec($curl_handle);
				$priceStock = $isNo; //현재가격
				
				$myfile = fopen( "/hosting/windmoter/html/stock/include/newfile.html", "w") or die("Unable to open file!"); 
				fwrite($myfile,$json);
				fclose($myfile);
				*/
		 
 
	}else{
		$resultMsg =$today. " stk 연동코드가 없음";
	}
/* 삼성 증권 2018.12.28 만료*/
//--------------------------------------
if($is_todayWa_Insert == "Y" ){
 
	$query = "select  key_date  from samsung_dreman_top order by  key_date  desc limit 1";
	$res = mysql_query($query, $conn);

	if($res){
		$row = mysqli_fetch_assoc($res);	
		echo $today."==". $row["key_date"];
		if($today == $row["key_date"]){
			$is_todayWa_Insert  = "N";
			$resultMsg =$today. " 이미 저장됨";
		} 
	}
} 
if($is_todayWa_Insert == "Y" ){ 
				//--------------------------------------
				//6-1.삼성증권 거래정보 저장
				//--------------------------------------
				
				include("samsung/sam_foreigner_insert.php");
				//include("samsung/sam_peter_lynch_insert.php");
				//include("samsung/sam_dreman_insert.php");
				//include("samsung/sam_johnNep_insert.php");
				//include("samsung/sam_save_insert.php");
				//include("samsung/sam_templeton_insert.php");
}	
echo $resultMsg;
?>
 
