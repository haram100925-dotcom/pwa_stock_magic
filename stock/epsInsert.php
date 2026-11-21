<?php
header('Content-Type: text/html;charset=utf-8'); //header()는 로 캐릭터셋
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
margin:0 auto; /*위에서 센터로 놨음에도 또 이걸 해 주는 건 IE외의 다른 브라우저에선 이게 없으면 다시 왼쪽으로 붙기 때문이에요*/  
width:240px; /*이건 임의값입니다. 쓰실 데로 수정하세요*/  
text-align:left;   
}
</style>
</head>
<body>
<div id="align">  
<div id="content">  
 


<?php
$is_Insert = true; // 데이터 저장 여부
$today = date("Ymd");
$todayWa = date("w"); // 요일
$resultMsg = ""; // 저장결과

 /*테스트 데이터*/
// $today =date("Ymd",strtotime("-1 day"));
//$todayWa = "5";

 
$startTime = date("Y-m-d H:i:s" );

//--------------------------------------
//1. 공휴일은 데이터 저장 안함 
//--------------------------------------
if($todayWa == "6" ||  $todayWa == "0" ){
	$is_Insert = false;
	//$today = "20230602";
	$resultMsg = "토,일 요일 제외";
}  
//DB접속
include("./include/accsesDB.php");

//--------------------------------------
//2. 오늘 날짜와 저장 DB  이미저장되어 있는지 체크 
//--------------------------------------
if($is_Insert ){

	//$query = "select  key_date  from stock_Insert_history order by  key_date  desc limit 1";
	$query = "select  key_date  from stock_valuations order by  key_date  desc limit 1";
	$res = mysql_query($query, $conn);

	if($res){
		$row = mysqli_fetch_assoc($res);	
		if($today == $row["key_date"]){
			$is_Insert = false;
			$resultMsg =$today. " 이미 저장됨";
		} 
	}
} 
//--------------------------------------
//3. 오늘 날짜로 krx. 데이터가 있는지 체크
//--------------------------------------
/*
 * 
 	$sql = "insert into history values(1, '경복궁')";
	mysql_query($sql, $conn);
 $i = 0;
while ($i < mysql_num_fields($result)) {
    echo "Information for column $i:<br />\n";
    $meta = mysql_fetch_field($result, $i);
    if (!$meta) {
        echo "No information available<br />\n";
    }
    echo "<pre>
blob:         $meta->blob
max_length:   $meta->max_length
multiple_key: $meta->multiple_key
name:         $meta->name
not_null:     $meta->not_null
numeric:      $meta->numeric
primary_key:  $meta->primary_key
table:        $meta->table
type:         $meta->type
default:      $meta->def
unique_key:   $meta->unique_key
unsigned:     $meta->unsigned
zerofill:     $meta->zerofill
</pre>";
    $i++;
}
mysql_free_result($result);
 
/*
 * 기본키
 *
 bld:MKD/04/0403/04030800/mkd04030800
name:form

 * http://marketdata.krx.co.kr/contents/COM/GenerateOTP.jspx?bld=MKD%2F04%2F0403%2F04030800%2Fmkd04030800&name=form *
   http://marketdata.krx.co.kr/contents/COM/GenerateOTP.jspx?bld=MKD%2F13%2F1302%2F13020401%2Fmkd13020401&name=form
 * 
 * 30009 투자 지표 eps :  https://marketdata.krx.co.kr/mdi#document=040308  XXXXXXXXXX 사라짐 201911210
 * PER/PBR/배당수익률 (개별종목) http://marketdata.krx.co.kr/mdi#document=13020401
https://data.krx.co.kr/mdi#document=13020401
코스닥
market_gubun:KSQ
gubun:1 
isu_cd:KR7005930003  
isu_srt_cd:A005930
schdate:20160614
fromdate:20160607
todate:20160614 
code:KlpkxzOvQAzs1CtZFnCkWNxgalr3khMgXYw0lTajSqi7UveIWE769jNApvDap/pIg2Hy5o2TsSuokglUGb3GNEdSWtXsfUc7RQqCBPcBQg4oEYLYpfHO8p+gvfxdjV7M
 

 * 
http://marketdata.krx.co.kr/contents/MKD/99/MKD99000001.jspx?market_gubun=STK&gubun=1&isu_srt_cd=A005930&schdate=20160618&fromdate=20160607&todate=20160618&code=4weM/Vcy3PLI23uYwcEX8eNtBbyhge4e/m7ewoWxBXH2KgypzEMhAiZIb9B+Jc5i63B7Znh0Zz5dAsnak6KNFHHECxe9y08C9lxVXrZJ6o4cWnQfvbwDQyl3E7JaI7aV

*
*코스피
*market_gubun:STK
gubun:1
isu_cdnm:A005930/삼성전자
isu_cd:KR7005930003
isu_nm:삼성전자
isu_srt_cd:A005930
schdate:20160614
fromdate:20160607
todate:20160614
pagePath:/contents/MKD/04/0403/04030800/MKD04030800.jsp
code:KlpkxzOvQAzs1CtZFnCkWJ/9xBEkabfoaq/avpOspwoCthXwI45m/f4Gwdsd87SkMWNqvCdA94nLqYlcVME9Q9QWIvIvnXo1nKXT3/wHFaHFqo6q6oCGB73ytnIdMigd

http://marketdata.krx.co.kr/contents/MKD/99/MKD99000001.jspx?market_gubun=STK&gubun=1&schdate=2&code=onU/FrwFkurWr1qEGgPEhjK83FHpfrtMjIIge96K3L1SNcLxUgVvBwsNVhctu7wmuCnhRobM9xKI7OA4PmFrfbpT0FHxW8eWXuL73DSoLtm/2NeF+ZRHmcE5v9TRV6g8
http://marketdata.krx.co.kr/contents/MKD/99/MKD99000001.jspx?market_gubun=KSQ&gubun=1&isu_cdnm=A005930%2F%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isu_cd=KR7005930003&isu_nm=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isu_srt_cd=A005930&schdate=20160614&fromdate=20160607&todate=20160614&pagePath=%2Fcontents%2FMKD%2F04%2F0403%2F04030800%2FMKD04030800.jsp&code=KlpkxzOvQAzs1CtZFnCkWNxgalr3khMgXYw0lTajSqi7UveIWE769jNApvDap%2FpIg2Hy5o2TsSuokglUGb3GNEdSWtXsfUc7RQqCBPcBQg4oEYLYpfHO8p%2BgvfxdjV7M
http://marketdata.krx.co.kr/contents/MKD/99/MKD99000001.jspx?market_gubun=STK&gubun=1&isu_cdnm=A005930%2F%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isu_cd=KR7005930003&isu_nm=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isu_srt_cd=A005930&schdate=20191210&fromdate=20191202&todate=20191209&pagePath=%2Fcontents%2FMKD%2F13%2F1302%2F13020401%2FMKD13020401.jsp&code=4epICglQ1SgbvSJRUANXUFmiSZTZvY7kkrpb1J4IlNg1DHo5JAqsUb7DOLW07k4igHlM1FH8L0fG3O2BussK/Dr1JyJjGQPxj3C87x8/1qXH+yRrmSgTjYzmSpn4E+6a&pageFirstCall=Y
http://marketdata.krx.co.kr/contents/MKD/99/MKD99000001.jspx?market_gubun=STK&gubun=1&isu_cdnm=A005930%2F%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isu_cd=KR7005930003&isu_nm=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isu_srt_cd=A005930&schdate=20191210&fromdate=20191202&todate=20191209&pagePath=%2Fcontents%2FMKD%2F13%2F1302%2F13020401%2FMKD13020401.jsp&code=4epICglQ1SgbvSJRUANXUM6kGYuURxwyKsKtY8cjcY3uAZE0kXx%2FfF2bWm1BA8%2FrlLaYTE2F9h8vldxaeheO7VnDFN8UEfGaqZE8A1J%2FuoUff%2F%2FvMNjBNvOqy4t5ISvm

*/  
//$today = date("Ymd"); 
if( $is_Insert ){
$urlStk ="http://marketdata.krx.co.kr/contents/COM/GenerateOTP.jspx?bld=MKD%2F13%2F1302%2F13020401%2Fmkd13020401&name=form";
 
	
	$options = array( CURLOPT_RETURNTRANSFER => true, // return web page 
	                  CURLOPT_HEADER => false, // do not return headers 
	                  CURLOPT_FOLLOWLOCATION => true, // follow redirects 
	                  CURLOPT_USERAGENT => "spider", // who am i 
	                  CURLOPT_AUTOREFERER => true, // set referer on redirect 
	                  CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect 
	                  CURLOPT_TIMEOUT => 120, // timeout on response 
	                  CURLOPT_MAXREDIRS => 10, // stop after 10 redirects 
	                  ); 
	                  $ch = curl_init( $urlStk ); 
	                  curl_setopt_array( $ch, $options ); 
	                  $stkCode = curl_exec( $ch ); 
	                  $err = curl_errno( $ch ); 
	                  $errmsg = curl_error( $ch ); 
	                  $header = curl_getinfo( $ch ); 
echo $today."~~~~ ~~~~~~~~~~~~~~~stkCode~~~~~~~~".$errmsg;
$urlKey = "http://marketdata.krx.co.kr/contents/COM/GenerateOTP.jspx";
 
 
	if(true){
		//코스피------------------------------------------------------		
 
  // http://data.krx.co.kr/comm/bldAttendant/getJsonData.cmd?bld=dbms/MDC/STAT/standard/MDCSTAT03501&searchType=1&mktId=STK&trdDd=20210118&tboxisuCd_finder_stkisu0_0=005930%2F%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isuCd=KR7005930003&isuCd2=KR7005930003&codeNmisuCd_finder_stkisu0_0=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&param1isuCd_finder_stkisu0_0=STK&strtDd=20210111&endDd=20210118&csvxls_isNo=false  
    $url = "http://data.krx.co.kr/comm/bldAttendant/getJsonData.cmd";
     
    $ch = curl_init();                                 //curl 초기화
    curl_setopt($ch, CURLOPT_URL, $url);               //URL 지정하기
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:  application/x-www-form-urlencoded","Host: data.krx.co.kr"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //요청 결과를 문자열로 반환 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);      //connection timeout 10초 
    //curl_setopt($ch, CURLOPT_POSTFIELDS, "bld=dbms/MDC/STAT/standard/MDCSTAT01501&locale=ko_KR&mktId=STK&trdDd=".$today."&share=1&money=1&csvxls_isNo=false");       //POST data 
    curl_setopt($ch, CURLOPT_POSTFIELDS, "bld=dbms/MDC/STAT/standard/MDCSTAT03501&searchType=1&mktId=STK&trdDd=".$today."&tboxisuCd_finder_stkisu0_0=005930%2F%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isuCd=KR7005930003&isuCd2=KR7005930003&codeNmisuCd_finder_stkisu0_0=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&param1isuCd_finder_stkisu0_0=STK&strtDd=".$today."&csvxls_isNo=false&endDd=".$today);    
    curl_setopt($ch, CURLOPT_POST, 1);              //true시 post 전송 
    curl_setopt($ch, CURLOPT_USERAGENT, "data.krx.co.kr");
    curl_setopt($ch, CURLOPT_REFERER, 'http://data.krx.co.kr'); // https, http 구분하세요
    $json = curl_exec($ch); 
   // echo  " $json====". $json; 
 
    curl_close($ch);  
				//	 echo "====json".$json;
                					 				
		//현재 날짜 json 데이터 있는경우만  DB 저장한다

		if(true){
				//--------------------------------------
				//4-1. 조회 시세 저장
				//--------------------------------------		
				$sql = "insert into stock_valuations_json ( id_date, market_gubun 	,json_date)
				values( '$today' ,'STK' , '$json') "; 
				mysql_query($sql, $conn); 
				$sql = "DELETE   FROM stock_valuations_json WHERE json_date = '{\"result\":[]}' ";
				mysql_query($sql, $conn);
				
			$user = json_decode($json);  //  파싱 
					 //echo  "$user->result[0]-====".$user->CURRENT_DATETIME;
                     //echo  "$user->output[0]->ISU_SRT_CD-====".$user->output[0]->ISU_SRT_CD;
			if(empty($user->output)){ // 실제 값이 없으면 실패
				$is_Insert = false;
				$resultMsg =$today. " stk 연동 성공 & 데이터 없음";
			}else{
				
//--------------------------------------
//4. 오늘 날짜로 krx. 코스피 데이터 저장
//--------------------------------------
				$arrayJson =  $user->output;			
				$numTrue = 0;
				$numFalse = 0;
				$numTotal = 0;
				foreach($arrayJson as $key => $value)
				{
					$numTotal++;
			//		echo $is_Insert ."====".$value->isu_cd;
			//o floor(100.123456*100)/100; // 제일 간단
			//ROE = PBR / PER 
					$end_pr = str_replace(",", "",  $value->TDD_CLSPRC) ;
					$prv_eps =  sprintf("%2.2f", (float)str_replace(",", "",  $value->EPS)) ;
					$per 		 =  sprintf("%2.2f" ,  str_replace(",", "",  $value->PER) ); // 소스점 
					$bps		 =  sprintf("%2.2f" ,  str_replace(",", "",  $value->BPS) );
					$pbr		 =  sprintf("%2.2f" ,  str_replace(",", "",  $value->PBR) );  // 소스점
					$stk_dvd	 =  str_replace(",", "",  $value->DPS) ;
					$dvd_yld =  sprintf("%2.2f" ,  str_replace(",", "", $value->DVD_YLD) );  // 소스점
					if($pbr <  0.01 || $per  <    0.01 ){
						$roe = 0;
					}else{
						$roe		 =  sprintf("%2.2f" ,  $pbr / $per );	
					}
					// roa 총자산이익률 ROA = 순이익/자산총계 >>>>> EPS 주당순이익(Earning Per Share) /BPS 주당 순자산
					if($prv_eps <  0.01 || $bps  <    0.01 ){
						$roa = 0;
					}else{
						$roa		 =  sprintf("%2.2f" ,  ($prv_eps / $bps)*1000 );
					}
					
					$sql = "insert into stock_valuations 
					(
					 key_date
					, market_gubun
					, isu_cd 	
					, isu_nm 
					, iisu_code
					, end_pr
					, prv_eps
					, per
					
					, bps
					, pbr
					, stk_dvd
					, dvd_yld
					, roe
					, roa
					)
					values( '$today' ,'STK'
					, '$value->ISU_SRT_CD'	
					, '$value->ISU_ABBRV' 
					, '-'  
					, '$end_pr'
					, '$prv_eps'
					, '$per'
					, '$bps'
					, '$pbr'
					, '$stk_dvd'
					, '$dvd_yld'
					, '0'
					, '0'
					 )";
					
					//echo $sql;
					$res = mysql_query($sql, $conn);
					if($res){
						$numTrue++;
					}else{
						$numFalse++;
					}
				}
				$resultMsg = $resultMsg . "코스피저장 : 총=".$numTotal." 성공=".$numTrue." 실패=".$numFalse;
				
				//코스닥------------------------------------------------------ 
    $url = "http://data.krx.co.kr/comm/bldAttendant/getJsonData.cmd";
     
    $ch = curl_init();                                 //curl 초기화
    curl_setopt($ch, CURLOPT_URL, $url);               //URL 지정하기
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:  application/x-www-form-urlencoded","Host: data.krx.co.kr"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //요청 결과를 문자열로 반환 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);      //connection timeout 10초 
    curl_setopt($ch, CURLOPT_POSTFIELDS, "bld=dbms/MDC/STAT/standard/MDCSTAT03501&searchType=1&mktId=KSQ&trdDd=".$today."&tboxisuCd_finder_stkisu0_0=005930%2F%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&isuCd=KR7005930003&isuCd2=KR7005930003&codeNmisuCd_finder_stkisu0_0=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90&param1isuCd_finder_stkisu0_0=STK&strtDd=".$today."&csvxls_isNo=false&endDd=".$today);       //POST data 
    //curl_setopt($ch, CURLOPT_POSTFIELDS, "bld=dbms/MDC/STAT/standard/MDCSTAT01501&locale=ko_KR&mktId=STK&trdDd=".$today."&share=1&money=1&csvxls_isNo=false");       //POST data 
    curl_setopt($ch, CURLOPT_POST, 1);              //true시 post 전송 
    curl_setopt($ch, CURLOPT_USERAGENT, "data.krx.co.kr");
    curl_setopt($ch, CURLOPT_REFERER, 'http://data.krx.co.kr'); // https, http 구분하세요
    $jsonKsq = curl_exec($ch); 
 	  curl_close($ch);		
				//현재 날짜 json 데이터 있는경우만  DB 저장한다
				if($jsonKsq){
				//--------------------------------------
				//4-1. 조회 시세 저장
				//--------------------------------------		
				$sql = "insert into stock_valuations_json ( id_date, market_gubun 	,json_date)
				values( '$today' ,'KSQ' , '$jsonKsq') "; 
				mysql_query($sql, $conn); 
					
					$user = json_decode($jsonKsq);  //  파싱
//--------------------------------------
//5. 오늘 날짜로 KSQ. 코스닥 데이터 저장
//--------------------------------------
					$arrayJson =  $user->output;
					$numTrue = 0;
					$numFalse = 0;
					$numTotal = 0;
					foreach($arrayJson as $key => $value)
					{
						$numTotal++;
						//ROE = PBR / PER
					$end_pr = str_replace(",", "",  $value->TDD_CLSPRC) ;
					$prv_eps =  sprintf("%2.2f", (float)str_replace(",", "",  $value->EPS)) ;
					$per 		 =  sprintf("%2.2f" ,  str_replace(",", "",  $value->PER) ); // 소스점 
					$bps		 =  sprintf("%2.2f" ,  str_replace(",", "",  $value->BPS) ); // 소스점 str_replace(",", "",  $value->BPS) ;
					$pbr		 =  sprintf("%2.2f" ,  str_replace(",", "",  $value->PBR) );  // 소스점
					$stk_dvd	 =  str_replace(",", "",  $value->DPS) ;
					$dvd_yld =  sprintf("%2.2f" ,  str_replace(",", "", $value->DVD_YLD) );  // 소스점
						if($pbr <  0.01 || $per  <    0.01 ){
							$roe = 0;
						}else{
							$roe		 =  sprintf("%2.2f" ,  $pbr / $per );
						}
						// roa 총자산이익률 ROA = 순이익/자산총계 >>>>> EPS 주당순이익(Earning Per Share) /BPS 주당 순자산
						if($prv_eps <  0.01 || $bps  <    0.01 ){
							$roa = 0;
						}else{
							$roa		 =  sprintf("%2.2f" ,  ($prv_eps / $bps)*1000 );
						}
						$sql = "insert into stock_valuations 
						(
						 key_date
						, market_gubun
						, isu_cd 	
						, isu_nm 
						, iisu_code
						, end_pr
						, prv_eps
						, per
						
						, bps
						, pbr
						, stk_dvd
						, dvd_yld
						, roe
						, roa
						)
						values(  '$today' ,'KSQ' 
					, '$value->ISU_SRT_CD'	
					, '$value->ISU_ABBRV' 
					, '-' 
						, '$end_pr'
						, '$prv_eps'
						, '$per'
						, '$bps'
						, '$pbr'
						, '$stk_dvd'
						, '$dvd_yld'
						, '0'
						, '0'
						 )";
						//echo $sql;
						$res = mysql_query($sql, $conn);
						if($res){
							$numTrue++;
						}else{
							$numFalse++;
						}	
					}	
					$resultMsg = $resultMsg . "/ 코스닥저장 : 총=".$numTotal." 성공=".$numTrue." 실패=".$numFalse;
				}
				//--------------------------------------
				//7. 이전데이터 삭제
				//--------------------------------------		
				$sql = "DELETE FROM stock_valuations where key_date <> '$today' ";
				//echo $sql;
				mysql_query($sql, $conn); 
				
				//--------------------------------------
				//8. 마법공식 제외 종목 선청----금용 보험업 종목 - 마법공식사용- 분기만다
				//--------------------------------------
				
				$sql = "UPDATE stock_valuations ta , (
						SELECT   isu_cd
						FROM  skock_Finance     ) tb
						SET
						magic_no = 'N'
						WHERE
						ta.isu_cd = tb.isu_cd";
				mysql_query($sql, $conn);
				//--------------------------------------
				//7. ROA 저장 (ROA = 순이익/자본총계)
				//--------------------------------------				
				/*
				$sql = "
				update stock_valuations ta , (
						select (b.`t0`+b.`t1`+b.`t2`+b.`t3`+b.`t4`) / (c.`t0`+c.`t1`+c.`t2`+c.`t3`+c.`t4`) as c_total
						,c.isu_cd
						from  skock_net_profit b,skock_capital c
						where c.isu_cd = b.`isu_cd` ) tb
						set
						ta.roa = tb.c_total
						where
						ta.`isu_cd` = tb.isu_cd";
				mysql_query($sql, $conn);			*/				
				$sql = "
				update stock_valuations ta , (
						select isu_cd,roa ,roe ,gpa ,finance_cnt
						from  stock_daum_roe ) tb
						set
						ta.roa = tb.roa
						,						ta.roe = tb.roe
						,						ta.gpa = tb.gpa		
						,           ta.finance_cnt = tb.finance_cnt				
						where
						ta.`isu_cd` = tb.isu_cd";
				mysql_query($sql, $conn);						
					
				
				/*-------------------마법공식 제외 종목 선청----금용 보험업 종목 - 마법공식사용- 분기만다-------------------------*/
				$sql = " UPDATE stock_valuations ta , (
				SELECT   isu_cd
				FROM  skock_Finance     ) tb
				SET
				magic_no = 'N'
				WHERE
				ta.isu_cd = tb.isu_cd";
				mysql_query($sql, $conn); 
				//--------------------------------------
				//6. 데이터 저장 히스토리
				//--------------------------------------
				$sql = "insert into stock_Insert_history (key_date ,start_date ,result_msg ) values( '$today' ,  '$startTime'	,'$resultMsg')";
	 
				mysql_query($sql, $conn);
				
				mysqli_close($conn);		
/*
				//--------------------------------------
				//7. 업데이트 정보 설치 모든 앱에 푸쉬 함
				//--------------------------------------
				// 헤더 부분
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
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
				$response = curl_exec($ch);
				curl_close($ch);
				
				// 푸시 전송 결과 반환.
				$obj = json_decode($response);
				
				// 푸시 전송시 성공 수량 반환.
				$cnt = $obj->{"success"};
				*/ 
			} 
		}else{
			$resultMsg =$today. " stk 연동 실패";
		}
	}else{
		$resultMsg =$today. " stk 연동코드가 없음";
	}
} 
echo $resultMsg;
?>
</div>  
</div> 
<script>
//	location.href="https://cronya.pe.kr/stock/main.php";
</script>
</body>
</html>