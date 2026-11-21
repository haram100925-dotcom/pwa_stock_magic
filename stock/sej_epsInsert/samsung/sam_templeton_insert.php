<?php 
/***
5. 영적인 투자가 템플턴 
PER < 과거 5년 평균 PER EPS 성장률 > 업종평균 EPS 성장률 13 종목 
EBITDA margin > 과거 5년 평균 EBITDA margin 장기차입금/자본총계 비율 < 업종평균 
총자산/총부채 > 업종평균 ROE > 업종평균 

https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/JT?I_COND1=1&I_COND2=1&I_COND3=1&I_COND4=1&I_COND5=1&I_COND6=1&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=20180810&I_FRDATE=20180810&I_TODATE=20180810&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=10

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
	$query = "select  key_date  from samsung_templeton_top order by  key_date  desc limit 1";
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

if($is_Insert == "Y"  ){ 

$urlStk ="https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/JT?I_COND1=1&I_COND2=1&I_COND3=1&I_COND4=1&I_COND5=1&I_COND6=1&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=&I_FRDATE=&I_TODATE=&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "https://wisefn.samsungpop.com");



$json = curl_exec($curl_handle); 
echo "============".$json;
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
				$sql_magic = "insert into samsung_templeton_top
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
				
				$sql_magic = "UPDATE samsung_templeton_top
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