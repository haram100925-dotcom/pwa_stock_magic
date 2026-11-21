<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '');

include("./include/header.php");

if($market_gubun_type ==1 ){
	$market_gubun_nm = "<b style='color:red'>코스피</B>";
	$marketQueryPu = 	" and market_gubun = 'STK' ";
}else if($market_gubun_type ==2){
	$market_gubun_nm = "<b style='color:blue'>코스닥</B>"; 
	$marketQueryPu = 	" and market_gubun = 'KSQ' ";
}else {
	$market_gubun_nm = "전 종목";
	$marketQueryPu = 	"  ";
}

$query_roa = " SELECT  1 AS dummy_col,a.isu_cd ,a.isu_nm  ,a.per , a.PBR , a.dvd_yld  ,a.end_pr FROM   stock_valuations  a , stock_daum_roe ofc  WHERE  a.isu_cd = ofc.isu_cd  and a.PER <= 15
AND (ofc.shares_cnt  * a.end_pr )  >17050000000  AND ofc.shares_cnt > 15000000 AND  a.dvd_yld > 0
and  a.PER  != -9999999
and ofc.finance_cnt < 6
AND (a.per/a.prv_eps) <1
AND (a.per/a.dvd_yld)  <4
and magic_no ='Y'
and a.stk_dvd > 0 and a.prv_eps >0   ".$marketQueryPu." 	 and a.finance_cnt < 5 ORDER BY  a.roa asc ,a.roe  desc  LIMIT 100  ";
 
$res_roa = mysql_query($query_roa, $conn);

//하루에 한번 순위 저장 (여부체크)
//$query = "select  key_date  from stock_Insert_history order by  key_date  desc limit 1";
$today = date("Ymd");
$todayWa = date("w"); // 요일
$is_Insert = true;
//--------------------------------------
//1. 공휴일은 데이터 저장 안함
//--------------------------------------
if($todayWa == "6" ||  $todayWa == "7" ){
	$is_Insert = false;
	//$resultMsg = "토,일 요일 제외";
}
if($is_Insert){
	$query = "select  key_date  from stock_win_top where   key_date  =". $today;
	$res = mysql_query($query, $conn);

	if($res){
		$rowcount=mysqli_num_rows ($res);
		if($rowcount  > 0){
			$is_Insert = false;
		}
	}
}

?>  
 
<script> 
//상세
function stockDitalMove(isNo , isNm) { 

	location.href="https://cronya.pe.kr/stock/stockDetail.php?isNo="+isNo+"&isNm="+isNm;
} 
//결과이동
function stockResultMove(nowDate) { 

	location.href="<? echo $hostUrl ?>/stock/resultStock/winResult.php?nowDate="+nowDate;
}  
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                <h3>이기는 투자  <?echo $market_gubun_nm?> Top 100  <a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
            <?
    $calendar_year = "2018";
    $calendar_morth = "02";
    $calendar_day = "24";
//	include($mainRoot  ."/calendar_op/calendar_date_fromTo.php");
?> 


            <?
            $calendar_year="2019";
            $calendar_morth = "08";
            $calendar_day = "21";
          	include($mainRoot  ."/calendar_op/calendar_date.php");
?>  
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
				 <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※ 피터 린치 (이기는 투자) ※</h3> <br> 
				- 주당 순 자산 170억 이상<br> 
				- 상장 주식수 1500 만개 이상<br>		
				- 배당수익률 0% 이상 종목<br>						
				- PER/EPS(주당순이익) 증가율 〈 1<br>
				- PER/배당수익률 〈 4<br>
				- EPS 증가율 〈 50%<br> 
				- 금용 보험 업종 제외  <br>
				- ROE 높은 순 ROA 높은 순 정렬 100 위 <br>
				</div>                 
<ul data-role="listview" data-filter="true" data-filter-placeholder="종목명으로 검색.." data-inset="true">

   <li ><table width="100%"  >
 
    <tbody>
<tr> 
<td width="5%" >N</td>
<td data-priority="2" width="40%" >종목명</td>
<td width="20%" >ROA ↑</td>
<td width="20%" >ROE ↑</td>
<td width="15%" >배당수익률</td>
</tr>
		</tbody>
   </table>	 </li>
<?php 
$rowCount = 1;
while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
if($rowCount == 3){
	//다음 광고 
		include($mainRoot  ."/stock/include/AD_320_50.php"); 	
}
if($rowCount == 13){
	//다음 광고
	include($mainRoot  ."/stock/include/AD_320_100.php"); 
}
	?>
    <li><a href="javascript:stockDitalMove('<?echo $row['isu_cd']?>' ,'<?echo rawurlencode($row[2])?>')">
    
    <table width="100%" >

<tr> 
<td width="5%" ><b style='color:red'> <?echo $rowCount ?></b> </td>
<td width="40%" > <?echo $row[2] ?> </td>
<td width="20%" ><?echo $row[3]?> %</td>
<td width="20%" ><?echo sprintf("%2.2f" , $row[4] );	 ?>%</td>
<td width="15%" ><?echo $row[5]?>%</td>
</tr>
	
	
    </table> </a></li> 
 
<?php
//magic 통계를 위해 insert 한다
if($is_Insert ){
	if($rowCount == 1){
		$sql_magic = "insert into stock_win_top
		(
		key_date,
		rank1_cd,
		rank1_nm,
		rank1_end_pr
		)
		values( '$today'
		, '$row[1]'
		, '$row[2]'
		, '$row[6]'
		)";

	}else if ($rowCount >  1 && $rowCount < 21){

	$sql_magic = "UPDATE stock_win_top
	SET
	rank".$rowCount."_cd =  '$row[1]' ,rank".$rowCount."_nm ='$row[2]' ,rank".$rowCount."_end_pr = '$row[6]'
	WHERE  key_date  =". $today;
	}

	//echo $sql_magic;
	$res_magic = mysql_query($sql_magic, $conn);
	if($res_magic){
	// 	성공
}
	}
$rowCount++;
 }?> 



</ul>
</div>       

   
 <!-- menu_left -->
<?php 
include("./include/menu_left.php");?>
 <!-- footer -->
<?php 
include("./include/footer.php");?>

<!-- search panel -->   
<?php 
include("./include/menu_search.php");?> 
                
</div> 
</body>
</html>

