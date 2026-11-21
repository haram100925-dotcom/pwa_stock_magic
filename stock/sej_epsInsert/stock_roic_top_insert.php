<?php 
/***
 * ROIC  
 *  연결 , 17-18 년도 평균, 3개월
 *   영업이익율 : (영업이익 / 매출액) * 100
 *   
 ** 참고 항목에서 제외*********************
 *** 참고 항목에서 제외*********************
 *** 참고 항목에서 제외*********************
 *** 참고 항목에서 제외*********************
 *** 참고 항목에서 제외*********************
 *

ROIC(투하자본이익률) 기업이 실제 영업활동에 투입한 자산으로 영업이익을 얼마나 거뒀는지 나타내는 지표다. 
기업의 수익 창출 역량을 측정하는 데 활용되고 있다. ROIC는 ‘세후순영업이익(NOPAT)/영업투하자본(IC)’ 의 계산식으로 도출된다.
 여기서 세후순영업이익(NOPAT)은 영업활동에서 획득한 세후 이익을 말하며, 영업투하자본(IC)은 영업활동에서 투입된 자본을 의미한다.

그러나 업종간에 ROIC는 큰 편차를 가집니다.

인터넷사업처럼 자본이 별로 필요없는 업종은 ROIC가 높게 나오고, 철강,자동차 등의 중후장대 시설이 필요한 업종은 ROIC가 낮게 나오므로 감안을 해야 할 겁니다.

또한 같은 업종이라도 생산하는 제품이 다르다면 ROIC를 서로 비교하는 것은 잘못 판단할 수 있습니다.
[출처] 초보투자방법 32: 재무비율 - ROE, ROIC 추가|작성자 은인자중
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
include(__DIR__ . "/../include/accsesDB.php"); 
//--------------------------------------
//2. 오늘 날짜와 저장 DB  이미저장되어 있는지 체크 
//--------------------------------------
if($is_Insert == "Y" ){ 
	$query = "select  key_date  from stock_roic_top order by  key_date  desc limit 1";
	$res = mysql_query($query, $conn); 
	if($res){

			$row = mysqli_fetch_assoc($res);
    if($row && $today == $row["key_date"]){
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
	$urlStk ="http://comp.fnguide.com/svo/ASPSYSTEM/ASP_item.asp?ReportGB=C&pageGB=16&pGB=17&monthGB=1&highlowGB=1&dataGB=1";
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
			 
			 $query_roa = "SELECT isu_nm ,end_pr FROM stock_valuations WHERE isu_cd = '".  str_replace("A","", $stcokNo ) . "'";
			 $res_roa = mysql_query($query_roa, $conn);
			 
			 if($res_roa){
			 	$row = mysqli_fetch_assoc($res_roa);
				if($row) {
					$priceStock = $row["end_pr"] ;
					$stcokNm =  $row["isu_nm"] . $stcokNm; //종목명
				}
			 }
			 if($priceStock == 0  || $stcokNm == "" ){ //코넥스 종목 제외
			 	$stcokNm ="코넥스 종목";
			 }
		 	if($rowCount == 1){
				$sql_magic = "insert into stock_roic_top
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
				
				$sql_magic = "UPDATE stock_roic_top
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