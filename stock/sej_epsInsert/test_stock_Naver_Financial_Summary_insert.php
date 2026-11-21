<?php 
/***  
재무분석 daum roe roa-1000개
https://cronya.pe.kr/stock/sej_epsInsert/stock_Naver_Financial_Summary_insert.php?stockMinNum=0&stockMaxNum=1000
1. for 돌면서 종목을 가져온다: stock_valuations :market_gubun 시장구분KSQ ,STK
select isu_cd from stock_valuations where market_gubun ='STK'

"2. isu_cd 값을 가지고.web 파싱
당기순이익 :  각자 별로 stock_daum_roe 에서 isu_cd 같은것 type_nm 중  last_dt 값을 가져와 
if (select last_dt from stock_valuations where isu_cd ='058630'  )
//# update

.web 파싱총 배열 갯수와 last_dt 몇번째 배열에 있는지 체크
update stock_valuations  set 

where isu_cd ='058630' and type_nm ='당기순이익'

}else{
//# insert
}

자산총계
if (select last_dt from stock_valuations where isu_cd ='058630' and type_nm ='자산총계'  )
//# update

}else{
//# insert
}
자본총계
순이익률
영업이익률
ROE(%)
ROA(%)"

3. 

var changeFin = [["2014/12<span class='multi-row'>(IFRS연결)</span>",
"2015/12<span class='multi-row'>(IFRS별도)</span>",
"2016/12<span class='multi-row'>(IFRS별도)</span>",
"2017/12<span class='multi-row'>(IFRS별도)</span>"],
["2017/12<span class='multi-row'>(IFRS별도)</span>",
"2018/03<span class='multi-row'>(IFRS연결)</span>",
"2018/06<span class='multi-row'>(IFRS연결)</span>",
"2018/09<span class='multi-row'>(IFRS연결)</span>"]];
var changeFinData = [
[
[["매출액","307","278","305","275"],
["영업이익","25","77","42","49"],
["영업이익(발표기준)","25","77","42","49"],
["세전계속사업이익","-49","37","58","45"],
["당기순이익","-81","14","37","26"],
["당기순이익(지배)","-81","14","37","26"],
["당기순이익(비지배)","0","0","",""]],

[["68","69","67","60"],["16","13","9","5"],["16","13","9","5"],["6","14","17","5"],["2","10","13","3"],["2","10","13","3"],["","0","0","0"]]
],
[
[["자산총계","405","367","391","444"],["부채총계","229","143","119","100"],["자본총계","176","224","271","345"],["자본총계(지배)","178","224","271","345"],["자본총계(비지배)","-3","","",""],["자본금","89","96","96","98"]],
[["444","439","426","417"],["100","103","91","85"],["345","335","334","332"],["345","333","333","331"],["","3","2","2"],["98","98","98","98"]]
],
[
[["영업활동현금흐름","59","80","14","66"],["투자활동현금흐름","65","-15","-14","-30"],["재무활동현금흐름","-127","-35","19","-28"]],
[["35","11","-2","0"],["6","-19","-8","26"],["0","2","-4","0"]]
],
[
[["CAPEX","9","1","13","10"]
,["FCF","50","80","1","56"]
,["이자발생부채","145","67","77","32"]
,["영업이익률","8.07","27.56","13.78","17.67"]
,["순이익률","-26.50","5.21","12.26","9.56"]
,["ROE(%)","-59.88","6.07","15.08","8.55"]
,["ROA(%)","-16.38","3.49","9.87","6.31"],["부채비율","130.50","63.56","43.94","28.89"],["자본유보율","103.12","156.02","183.14","221.95"],["EPS(원)","-537","76","196","137"],["PER(배)","-8.84","66.97","27.49","34.23"],["BPS(원)","1,014","1,189","1,421","1,763"],["PBR(배)","4.69","4.29","3.79","2.65"],["현금DPS(원)","0","0","0",""],["현금배당수익률","0.00","0.00","0.00",""],["현금배당성향(%)","0.00","0.00","0.00",""],
["발행주식수(보통주)","17,819","19,108","19,108","19,544"]],

[["4","11","4","1"],
["31","0","-6","-1"],
["32","32","29","29"],
["23.36","19.31","13.78","8.87"],
["2.42","13.90","19.19","4.37"],
["8.55","","",""],
["6.31","","",""],
["28.89","30.87","27.28","25.42"],["221.95","231.17","244.00","246.65"],["8","50","66","14"],["34.23","","",""],["1,763","1,702","1,702","1,692"],["2.65","2.59","2.14","2.02"],["","","",""],["","","",""],["","0.00","0.00","0.00"],["19,544","19,544","19,544","19,544"]]
]
];
 */

 
?>

        <meta charset="utf-8">

 <?php 
 
$is_Insert = "Y"; // 데이터 저장 여부
$today = date("Ymd");
$todayWa = date("w"); // 요일
$resultMsg = ""; // 저장결과

 /*테스트 데이터*/
 //$today =date("Ymd",strtotime("-4 day"));
//$todayWa = "5";

 
$startTime = date("Y-m-d H:i:s" );
$stockMinNum = $_REQUEST['stockMinNum']; // 카운트
$stockMaxNum = $_REQUEST['stockMaxNum']; // 카운트
$minkey = 0; // 메일 테이블 min 키값 
   
//DB접속
 include("/hosting/windmoter/html/stock/include/accsesDB.php");
//--------------------------------------
//1. 키 최소값 가져오기
//--------------------------------------
  
          $cmp_cd = "057540";
          $urlDomain = "http://wisefn.stock.daum.net/";
        	//외국인 1주  지분증가 상위 top15
        	$urlStk ="http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=057540&finGubun=MAIN";	
//          echo $urlStk."<BR>";
        	$curl_handle = curl_init();
        	curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
        	// 헤더는 제외하고 content 만 받음
        	curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        	curl_setopt($curl_handle, CURLOPT_USERAGENT,   $urlDomain);	
        	$json = curl_exec($curl_handle); 
        	//echo $json;
    			$startNum = ( strrpos ($json , "changeFin "   )  ) ; 
    			$endNum = ( strrpos (  $json , "Cmd_Financial"   ) ) ;
    			$detail_url = substr(  $json,$startNum, 	$endNum -  $startNum);
    			//echo   $detail_url ."<br><br>"; 
    	 
    			$endNum = ( strrpos (  $detail_url , "var"   ) ) ;
    			$changeFin = substr(  $detail_url, 12, 	$endNum -14) ;			

    			$changeFin =		json_decode(   strip_tags($changeFin) ); 
    			$changeFinData  = substr(  $detail_url,$endNum +20, 	10000) ;		
    					//				echo   strip_tags($changeFinData ) ."<br><br>"; 
    		  $changeFinData 			=  json_decode(  str_replace (";","" ,strip_tags($changeFinData ) ) );
    
    		  $roeDate = -9999999;
    		  $roaDate = -9999999;
    		  $assets_ta = -9999999; //자산총계 = 자기자본 +부체
    		  $operating_pf = -9999999; //영업이익 = 매출 총이익 -(판매비+ 관리비)
    		  $gpa = -9999999; //GP/A= 영업이익/자산총계    		  
 
  	      $lastAllDataCount = 0;
   		    $lastPutDataCount = 0;
   		    $lastDate = 0 ;    		  
    		  if(null !=$changeFinData && count($changeFinData) > 0 ){
    		    //roe 구하기
    		    $lastAllDataCount = 0;
     		    $lastPutDataCount = 0;

             //영업이익 구하기
      		    if( count($changeFinData[0][0]) > 0 ){
      		    		for($i=0;$i< count($changeFinData[0][0] );$i++){  
      		    		  $lastAllDataCount +=substr_count(implode( ' / ', $changeFinData[0][0][$i] ), "-");
// echo  substr_count(implode( ' / ', $changeFinData[0][0][$i] ), "-")."===". implode( ' / ', $changeFinData[0][0][$i] ); 
      		    
      		    		}
      		    } 
      		    if( count($changeFinData[0][1]) > 0 ){
      		    		for($i=0;$i< count($changeFinData[0][1] );$i++){  
      		    		  $lastAllDataCount +=substr_count(implode( ' / ', $changeFinData[0][1][$i] ), "-");
      		    		  		//   echo  substr_count(implode( ' / ', $changeFinData[0][1][$i] ), "-")."===". implode( ' / ', $changeFinData[0][1][$i] ); 
      		    
      		    		}
      		    }       		    
    		  }
echo $lastAllDataCount ;
      		  if($lastAllDataCount > 3 ){   echo "-----------"; }
?>
