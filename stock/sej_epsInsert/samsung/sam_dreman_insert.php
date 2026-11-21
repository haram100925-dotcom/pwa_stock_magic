<?php 
/***


7.드레먼이라면?


역발상 투자의 제왕! David Dreman
데이빗 드레먼은 투자수익률이 벤치마크지수를 20년 이상 이겨내면서 '역발상 투자의 제왕'이라 불린다.
그는 주식시장에서 투자자의 감정적·심리적 반응이 똑같기 때문에 현명한 투자자라면 펀더멘털에 비해
 주가가 낮은 종목을 골라야 시장을 이길 수 잇으며, 역사가 이를 뒷받침한다고 말했으며
 낮은 주가수익비율(PER)/ 주가순자산비율(PBR), 높은 주가현금흐름비율(PCR)/배당수익률 등 역발상
 지표를 활용하여 비인기 종목에 투자하였다. 


시가 총액이 상위  80 % 이내  PER가 거래소 하위  60 % 이내  
이자비용발생부채/자본총계 < 1   총부채/총자산 < 업종 중간값   
EPS증가율 > KOSPI + KOSDAQ 중간값   EPS 증가율 > 업종 중간값   
금년 EPS성장율 > 전년 EPS 증가율   익년 EPS 증가율 > 금년 EPS증가율(F)   
배당수익률 > KOSPI + KOSDAQ 중간값  

https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/DD?I_COND1=1&I_COND1_VAL=80&I_COND2=1&I_COND2_VAL=60&I_COND3=1&I_COND4=1&I_COND5=1&I_COND6=1&I_COND7=1&I_COND8=1&I_COND9=1&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=20180810&I_FRDATE=20180810&I_TODATE=20180810&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20




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
	$query = "select  key_date  from samsung_dreman_top order by  key_date  desc limit 1";
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

$urlStk ="https://wisefn.samsungpop.com:44305/v2/Master/MarketPrice/DD?I_COND1=1&I_COND1_VAL=80&I_COND2=1&I_COND2_VAL=60&I_COND3=1&I_COND4=1&I_COND5=1&I_COND6=1&I_COND7=1&I_COND8=1&I_COND9=1&I_MKT=0&I_SIZE=0&I_KSC=0&I_BASEDATE=&I_FRDATE=&I_TODATE=&I_SORTCOLNAME=C_IDX&I_SORTMODE=1&I_PCNT=1&I_PSIZE=20";
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
				$sql_magic = "insert into samsung_dreman_top
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
				
				$sql_magic = "UPDATE samsung_dreman_top
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