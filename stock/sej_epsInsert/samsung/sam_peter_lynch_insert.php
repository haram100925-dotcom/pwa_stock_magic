<?php 
/***

 4.피터린치

PER < 업종 평균 PER PER / EPS증가율 < 1 
장기차입금 / 자기자본 비율 > 업종 중간 값 0 < EPS 증가율 <   50  %  
PER / 배당수익률 <  10      0.1x 거래소 시가총액 > 종목 시가 총액  
(임직원과 특수 관계인 + 임직원과 특수관계인 제외 5% 이상 대주주)의 주식수 변동 (매수/매도 >  1.5   )  
외국인 보유지분 <  50 % 이고 특정 운용사 / 자문사 보유지분 <    10 %  

https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/PL?I_COND1=1&I_COND2=1&I_COND6=1&I_COND4=1&I_COND4_VAL=50&I_COND3=1&I_COND3_VAL=10&I_COND7=1&I_COND7_VAL=0.15&I_COND5=1&I_COND5_VAL=1.5&I_COND8_VAL=50&I_COND8=1&I_COND8_VAL2=10&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=20180810&I_FRDATE=20180810&I_TODATE=20180810&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20


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
// $today =date("Ymd",strtotime("-1 day"));
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
	$query = "select  key_date ,rank1_end_pr ,rank2_end_pr  from samsung_peter_lynch_top order by  key_date  desc limit 1";
	$res = mysql_query($query, $conn); 
	if($res){

		$row = mysqli_fetch_assoc($res);	 
		if($today == $row["key_date"]){
			if( 0 ==  $row["rank1_end_pr"] || 0 ==  $row["rank2_end_pr"] ){ // 데이터 보정
		    $sql_delete = "delete from  samsung_peter_lynch_top		 where 	key_date =". $row["key_date"]; 
		    echo $sql_delete;
  			$re_sdelete = mysql_query($sql_delete, $conn);
		  }else{
  			$is_Insert = "N"; 
  			$resultMsg =$today. " 이미 저장됨";
			}
		} 
	}
} 
//--------------------------------------
//3. 오늘 날짜로 krx. 데이터가 있는지 체크
//--------------------------------------
echo "============".$today ;

if($is_Insert == "Y"  ){ 

$urlStk ="https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/PL?I_COND1=1&I_COND2=1&I_COND6=1&I_COND4=1&I_COND4_VAL=50&I_COND3=1&I_COND3_VAL=10&I_COND7=1&I_COND7_VAL=0.15&I_COND5=1&I_COND5_VAL=1.5&I_COND8_VAL=50&I_COND8=1&I_COND8_VAL2=10&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=&I_FRDATE=&I_TODATE=&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "https://wisefn.samsungpop.com");



$json = curl_exec($curl_handle); 
//echo "============".$json;
  
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
				$sql_magic = "insert into samsung_peter_lynch_top
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
				
				$sql_magic = "UPDATE samsung_peter_lynch_top
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