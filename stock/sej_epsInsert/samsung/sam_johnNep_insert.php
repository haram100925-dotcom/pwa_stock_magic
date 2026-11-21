<?php 
/***

6.존네프 
워렌버핏,피터린치와 더불어 미국월가의 '3대전설'로 불리는 존 네프는, 1964년부터 95년까지 31년간
 윈저펀드를 운영하면서 5,546%라는 수익을 올린 펀드매니저이다. 사람들이 주목하는 주식보다는
 잘 알려지지 않은 유망한 종목을 발굴하여 투자하는 것에 중점을 두어 '가치투자자', '역발상투자자'라
 불렸으며 무엇보다 주가수익비율(PER)이 낮은 주식을 선택하였다. PER가 업종평균 이하, 순이익 증가율은
7% 초과, 매년 배당을 늘리고 성장하는 업종에 속해 있는가 등의 잣대를 활용해 투자를 결정했다

PER < 업종 평균 PER PEG < 업종 평균 PEG 11 종목 
최근 3년간 각연도의 매출액 증가율 > 0 EBITDA margin > 업종평균 
OPM > 업종평균 OPM 최근 3회계년도 간 전년대비 주당 잉여현금 흐름 증가율 > 0 

https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/JN?I_COND1=1&I_COND2=1&I_COND3=1&I_COND4=1&I_COND5=1&I_COND6=1&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=20180810&I_FRDATE=20180810&I_TODATE=20180810&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20



 samsung_dreman_top
보기 samsung_foreigner_top
보기 samsung_johnNep_top
보기 samsung_peter_lynch_top
보기 samsung_save_top
보기 samsung_templeton_top
보기 samsung_top10


 
*/
?>
<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
 
</head>
<body>
<div id="align">  
<div id="content"> 
 <?php  
 
$is_Insert = "Y"; // 데이터 저장 여부
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
	$resultMsg = "토,일 요일 제외";
}  
//DB접속
include("/hosting/windmoter/html/stock/include/accsesDB.php"); 
//--------------------------------------
//2. 오늘 날짜와 저장 DB  이미저장되어 있는지 체크 
//--------------------------------------
if($is_Insert == "Y" ){ 
	$query = "select  key_date  from samsung_johnNep_top order by  key_date  desc limit 1";
	$res = mysql_query($query, $conn); 
	if($res){

		$row = mysqli_fetch_assoc($res);	 
		if($today == $row["key_date"]){
			$is_Insert = "N"; 
			$resultMsg =$today. " 이미 저장됨";
		} 
	}
} 
//--------------------------------------
//3. 오늘 날짜로 krx. 데이터가 있는지 체크
//--------------------------------------
echo "============".$today ;
//$today= '20180903';
if($is_Insert == "Y"  ){ 

$urlStk ="https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/JN?I_COND1=1&I_COND2=1&I_COND3=1&I_COND4=1&I_COND5=1&I_COND6=1&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=&I_FRDATE=&I_TODATE=&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "https://wisefn.samsungpop.com");



$json = curl_exec($curl_handle); 
//echo "============".$json;
echo "============".$urlStk;
  
$result_array = json_decode($json);  

 echo "============". $result_array ->data[0]->CMP_NM_KOR;  


	if($json){ 
		$rowCount = 1;
		foreach($result_array ->data as $item) { //foreach element in $arr 
			 $stcokNo =  str_replace("A","", strip_tags(  preg_replace("/\s+/", "", $item -> CMP_CD) ) ) ; //종목코드
			 $stcokNm =   strip_tags( preg_replace("/\s+/", "", $item -> CMP_NM_KOR) ) ; //종목명
 
			 //실시간 가격 
			 $priceStock = strip_tags( preg_replace("/\s+/", "", $item -> NowPrc) ) ; //가격;
			 $priceStock =str_replace("-","", $priceStock);
			 $priceStock =str_replace("+","", $priceStock);	 

		 	if($rowCount == 1){
				$sql_magic = "insert into samsung_johnNep_top
				(
				key_date,
				rank1_cd,
				rank1_nm,
				rank1_end_pr
				)
				values( '$today'
				, '$stcokNo'
				, '$stcokNm'
				, '$priceStock'
				)";
			
			}else  {
				
				$sql_magic = "UPDATE samsung_johnNep_top
				SET
				rank".$rowCount."_cd =  '$stcokNo' ,rank".$rowCount."_nm ='$stcokNm' ,rank".$rowCount."_end_pr = '$priceStock'
				WHERE  key_date  =". $today;
			}
			 
			$res_magic = mysql_query($sql_magic, $conn);
			if($res_magic){
			// 	성공
			} 
			if ($rowCount > 19) {
        		break;   
    		}
			$rowCount++;
		}
 
	}
 	 
 	 
 
} 
echo $resultMsg;
?>
</div>  
</div> 
<script>
	//location.href="https://cronya.pe.kr/stock/main.php";
</script>
</body>
</html>