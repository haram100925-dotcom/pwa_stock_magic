<?php 
/***
 * ROE  높은순
 *  연결 , 당년도, 3개월 

투입한 자기자본이 얼마만큼의 이익을 냈는지를 나타내는 지표로 우리말로는 '자기자본이익률'이라고 한다. 

기업이 자기자본(주주지분)을 활용해 1년간 얼마를 벌어들였는가를 나타내는 대표적인 수익성 지표로 경영효율성을 표시해 준다.
자기자본이익률(ROE) ＝ 당기순이익/평균 자기자본 ×100

위 공식에 의해 ROE 가 산출되며, ROE가 10%이면 10억원의 자본을 투자했을 때 1억원의 이익을 냈다는 것을 보여주며 ROE가 20%이면 10억원의 자본을 투자했을 때 2억원의 이익을 냈다는 의미다.

따라서 ROE 가 높다는 것은 자기자본에 비해 그만큼 당기순이익을 많이 내 효율적인 영업활동을 했다는 뜻이다. 그렇기 때문에 이 수치가 높은 종목일수록 주식투자자의 투자수익률을 높여준다고 볼 수 있어 투자자 측면에선 이익의 척도가 된다. 

일반적으로 ROE 가 회사채수익률보다 높으면 양호한 것으로 평가되며 적어도 정기예금 금리는 넘어야 적절하다고 볼 수 있다. 

주주(투자자) 입장에서 보면 ROE가 시중금리보다 높아야 기업투자의 의미가 있는 것이다. 
ROE가 시중금리를 밑돌경우 투자자금을 은행에 예금하는 것이 더 낫기 때문이다.
[네이버 지식백과] ROE (시사상식사전, 박문각)
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
	$query = "select  key_date  from stock_roeTop_top order by  key_date  desc limit 1";
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
	$urlStk ="http://comp.fnguide.com/svo/ASPSYSTEM/ASP_item.asp?ReportGB=C&pageGB=14&pGB=17&monthGB=3&highlowGB=1&dataGB=1";
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
			 if($priceStock == 0  || $stcokNm == "" ){ //코넥스 종목 제외
			 	$stcokNm ="코넥스 종목";
			 }
		 	if($rowCount == 1){
				$sql_magic = "insert into stock_roeTop_top
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
				
				$sql_magic = "UPDATE stock_roeTop_top
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