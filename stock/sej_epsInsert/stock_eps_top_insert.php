<?php
 
/***
 * EPS(주당순이익) 상향 비율 상위
 *  EPS 정의
 *  	주당순이익는 기업이 1주당 얼마의 순이익을 냈는가를 나타내는 지표이다. 
 *   	예를 들어 주가 10,000원짜리 기업이 순이익 1,000원을 냈다면 주당순이익은 0.1이 된다. 
 *   	EPS는 주로 PER을 계산하기 위한 값으로 이용되며 단독으로는 별로 사용되지 않는다
 *   
 *   EPS가 높을수록 주식의 투자 가치는 높다고 볼 수 있다. 
	 EPS가 높다는 것은 그만큼 경영 실적이 좋다는 뜻이기 때문에 주가에도 좋은 영향을 미친다. 
	 
* -  3개월 단위(연결 재무제표)검색
* -  상위15개
*  - 상향비율 : (상향건수/전체건수)*100
*  
*  EPS는 기업이 벌어들인 순이익을 기업이 발행한 총 주식 수로 나눈 값이다. 따라서 EPS를 통해 기업이 1주당 얼마의 이익을 벌었는지 알 수 있다. 
*  가령 미국 시가총액 1위 기업인 애플의 작년 회계연도 EPS는 13.87달러로 나타났다. 애플이 1주당 13.87달러를 벌였다는 의미다.

EPS가 높을수록 주식의 투자 가치는 높다고 볼 수 있다. 
EPS가 높다는 것은 그만큼 경영 실적이 좋다는 뜻이기 때문에 주가에도 좋은 영향을 미친다. 

EPS는 순이익 규모가 늘면 높아지게 되고, 전환사채를 주식으로 바꾸거나 증자로 주식 수가 많아지면 낮아지게 된다. 

한편 EPS는 기업의 수익성을 분석하는 중요한 수치로 주가수익비율(PER)을 계산하는데도 쓰인다. PER은 기업의 현재 주가를 EPS로 나눈 값이다. PER을 통해 기업이 돈을 버는 것에 비해 주가가 싼지, 비싼지를 알아볼 수 있다.

가령 1년에 100억원의 순이익을 내는 A라는 기업이 총 1000만주를 발행한다고 치자. 우선 이 기업의 EPS는 주당 1000원이다. 그렇다면 PER은 현재의 주가를 EPS로 나눈 값이므로 이 기업의 현재 주가가 2000원이라고 치면 PER은 2가 된다. 

PER이 높으면 때문에 상대적으로 비싼 것이다. 반면 낮으면 주가가 싼 것이다. 하지만 PER이 높다고 해서 무조건 비싼 주식인 것만은 아니다. 증시 전체의 평균 PER과 비교할 때 그보다 높으면 비싼 주식이지만, 낮으면 싼 주식이라고도 해석할 수 있다. 비슷한 업종의 기업이나 경쟁 기업과 비교해도 마찬가지이다. 비슷한 업종을 가진 B라는 경쟁기업의 PER이 1인데 A기업의 PER이 2라면 A기업의 주식은 상대적으로 비싼 주식이라고 할 수 있다.

PER은 하나의 종목만을 대상으로 하는 것은 아니다. 업종별 또는 증권 시장 전체의 주가 수준을 재는 척도로 이용되기도 한다.

원문보기: 
http://biz.chosun.com/site/data/html_dir/2012/01/30/2012013000390.html#csidx5f9dc3f0d3adc6ea4c2578f2863160b 
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
	$query = "select  key_date  from stock_eps_increase_top order by  key_date  desc limit 1";
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
	$urlStk ="http://comp.fnguide.com/svo/ASPSYSTEM/ASP_item.asp?ReportGB=C&pageGB=2&pGB=17&monthGB=3&highlowGB=1&dataGB=";
	$json  =  protocalHttpWeb( $urlDomain , $urlStk );

	if($json){
		$script_List = tagListData('table', $json);
		$profitability_List = tagListData('tr', $script_List[3]); // 테이블
		$rowCount = 1;
		for($i=1;$i<count($profitability_List);$i++){ 
			 $dayList =  tagListData('td', $profitability_List [$i]); //종목코드
			 $stcokNo =  str_replace("A","", strip_tags(  preg_replace("/\s+/", "", $dayList[1]) ) ) ; //종목코드
			 $stcokNm =  "(". strip_tags( preg_replace("/\s+/", "", $dayList[6]) )." %)"; //종목명

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
				$sql_magic = "insert into stock_eps_increase_top
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
				
				$sql_magic = "UPDATE stock_eps_increase_top
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