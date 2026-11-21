<?php 
/***
 * 외국인 지분증가 상위 
 * -  일주일 단위 검색
 * -  상위15개 
 *  - 과거지분율(B)	지분율(A)	변동(A-B)  표시
 
 SELECT CASE WHEN <비교대상> = <비교값> THEN <조건 일치 시..처리내용>
               WHEN <두번째 비교대상> = <비교값> THEN <조건처리내용>
               ELSE <조건에 해당하지 않을때 처리값>
               END TEST // TEST 라는 컬럼 이름으로 생성됨 FROM  stock_magic_top
               
               
                 WHERE key_date > '20180200' and key_date < '20180231'
                 
                 -- 한번에 떨어지지 않을 것으로 보임
 */

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
	$query = "select  key_date  from stock_allocation_top order by  key_date  desc limit 1";
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
echo "============".$is_Insert;
if($is_Insert == "Y"  ){ 

$urlDomain = "http://comp.fnguide.com/";
	//외국인 1주  지분증가 상위 top15
	$urlStk ="http://comp.fnguide.com/svo/ASPSYSTEM/ASP_item.asp?ReportGB=A&pageGB=12&pGB=17&monthGB=1&highlowGB=1&dataGB=W";
	$json  =  protocalHttpWeb( $urlDomain , $urlStk );

	if($json){
		$script_List = tagListData('table', $json);
		$profitability_List = tagListData('tr', $script_List[3]); // 테이블
		$rowCount = 1;
		for($i=1;$i<count($profitability_List);$i++){ 
			 $dayList =  tagListData('td', $profitability_List [$i]); //종목코드
			 $stcokNo =  str_replace("A","", strip_tags(  preg_replace("/\s+/", "", $dayList[1]) ) ) ; //종목코드
			 $stcokNm =  "(". strip_tags( preg_replace("/\s+/", "", $dayList[5]) )." %)"; //종목명

			 //$stcokSum =  tagListData('td', $dayList [3]); //과거지분율(B)	지분율(A)	변동(A-B) 
			 //전날 가격 
			 $priceStock = 0;
			 
			 $query_roa = "SELECT isu_nm ,end_pr FROM stock_valuations WHERE isu_cd = ".  str_replace("A","", $stcokNo );
			 $res_roa = mysql_query($query_roa, $conn);
			 
			 if($res_roa){
			 	$row = mysqli_fetch_assoc($res_roa);
			 	$priceStock = $row["end_pr"] ;
			 	$stcokNm =  $row["isu_nm"] . $stcokNm; //종목명
			 
			 }
		 	if($rowCount == 1){
				$sql_magic = "insert into stock_allocation_top
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
				
				$sql_magic = "UPDATE stock_allocation_top
				SET
				rank".$rowCount."_cd =  '$stcokNo' ,rank".$rowCount."_nm ='$stcokNm' ,rank".$rowCount."_end_pr = '$priceStock'
				WHERE  key_date  =". $today;
			}
			 
			$res_magic = mysql_query($sql_magic, $conn);
			if($res_magic){
			// 	성공
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