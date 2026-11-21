<?php 
/***

 2. 안정성 , PER ,PBR , 수익성(ROE) ,  수익서 (시가배당률)
 https://wisefn.samsungpop.com:44305/v2/Screener/MarketPrice?i_mkt=0&i_size=0&i_ksc=0&i_basedate=20180907&i_frdate=20180907&i_todate=20180907&types=D%7CV%7CV%7CP%7CP&seqs=1%7C3%7C5%7C1%7C4&minValues=-999999999%7C-999999999%7C-999999999%7C14%7C1&maxValues=200%7C10%7C1%7C-999999999%7C-999999999&sortName=C_IDX&sortMode=1&page=1&psize=20
 

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
 $today =date("Ymd",strtotime("-1 day"));
//$todayWa = "5";

 
//--------------------------------------
//1. 공휴일은 데이터 저장 안함 
//--------------------------------------
if($todayWa == "6" ||  $todayWa == "0" ){ 
	$is_Insert = "N"; 
	$resultMsg = "토,일 요일 제외";
}  
//--------------------------------------
//1_1. 데이터가 늦게 업데이트 되므로 시간 체크
//--------------------------------------
$startTime = date("H" ) ;
if($is_Insert == "Y" && $startTime  < 14  ){ 
	$is_Insert = "N"; 
	$resultMsg = "4시 이전 에는 데이타 없음";
}  
echo "============". $startTime ; 
//DB접속
include("/hosting/windmoter/html/stock/include/accsesDB.php"); 
//--------------------------------------
//2. 오늘 날짜와 저장 DB  이미저장되어 있는지 체크 
//--------------------------------------
if($is_Insert == "Y" ){ 
	$query = "select  key_date  from samsung_save_top order by  key_date  desc limit 1";
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
echo "============". $startTime ; 
if($is_Insert == "Y"  ){ 

$urlStk ="https://wisefn.samsungpop.com:44305/v2/Screener/MarketPrice?i_mkt=0&i_size=0&i_ksc=0&i_basedate=".$today."&i_frdate=".$today."&i_todate=".$today."&types=D%7CV%7CV%7CP%7CP&seqs=1%7C3%7C5%7C1%7C4&minValues=-999999999%7C-999999999%7C-999999999%7C14%7C1&maxValues=200%7C10%7C1%7C-999999999%7C-999999999&sortName=C_IDX&sortMode=1&page=1&psize=20";
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
				$sql_magic = "insert into samsung_save_top
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
				
				$sql_magic = "UPDATE samsung_save_top
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