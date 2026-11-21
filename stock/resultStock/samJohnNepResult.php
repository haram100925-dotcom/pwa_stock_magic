<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
 
$nowDate = $_REQUEST['nowDate'];
 

include("/hosting/windmoter/html/stock/recommendEps/include/header.php"); 
 
//roa높은순 per낮은순 

	$query_roa = "select  		a.key_date				
	,a.rank1_cd, a.rank1_nm,	a.rank1_end_pr 			,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank1_cd ) as rank1_now_per
	,a.rank2_cd, a.rank2_nm,	a.rank2_end_pr			,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank2_cd ) as rank2_now_per
	,a.rank3_cd, a.rank3_nm,	a.rank3_end_pr     		,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank3_cd ) as rank3_now_per
	,a.rank4_cd, a.rank4_nm,	a.rank4_end_pr     		,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank4_cd ) as rank4_now_per
	,a.rank5_cd, a.rank5_nm,	a.rank5_end_pr      		,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank5_cd ) as rank5_now_per
	,a.rank6_cd, a.rank6_nm,	a.rank6_end_pr      		,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank6_cd ) as rank6_now_per
	,a.rank7_cd, a.rank7_nm,	a.rank7_end_pr      		,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank7_cd ) as rank7_now_per
	,a.rank8_cd, a.rank8_nm,	a.rank8_end_pr      		,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank8_cd ) as rank8_now_per
	,a.rank9_cd, a.rank9_nm,	a.rank9_end_pr     	 	,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank9_cd ) as rank9_now_per
	,a.rank10_cd, a.rank10_nm,	a.rank10_end_pr     ,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank10_cd ) as rank10_now_per
	,a.rank11_cd, a.rank11_nm,	a.rank11_end_pr     ,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank11_cd ) as rank11_now_per
	,a.rank12_cd, a.rank12_nm,	a.rank12_end_pr     ,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank12_cd ) as rank12_now_per
	,a.rank13_cd, a.rank13_nm,	a.rank13_end_pr     ,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank13_cd ) as rank13_now_per
	,a.rank14_cd, a.rank14_nm,	a.rank14_end_pr     ,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank14_cd ) as rank14_now_per
	,a.rank15_cd, a.rank15_nm,	a.rank15_end_pr	   ,(SELECT b.end_pr  FROM stock_valuations b WHERE  b.isu_cd = a.rank15_cd ) as rank15_now_per
	 from samsung_johnNep_top a";
	if($nowDate  != ""){
		$query_roa = $query_roa ." where a.key_date=  ".$nowDate;
	} 
	     
	$query_roa = $query_roa ." order by  a.key_date  desc limit 1";	  
  $res_roa = mysql_query($query_roa, $conn);

?>  
 
<script>  
<?php
$rowcount=mysqli_num_rows ($res_roa);
if($rowcount  <1){
?>
alert( "<?php echo $nowDate?> 일 데이터가 없습니다.");
history.back();
</script> <bR><bR> <bR>
<?php echo $nowDate?> 일 데이터가 없습니다.
<?php 
}else{?>

//상세
function stockDitalMove(isNo , isNm) { 

	location.href="https://cronya.pe.kr/stock/stockDetail.php?siteType=C&isNo="+isNo+"&isNm="+isNm;
}

//결과이동
function stockResultMove(nowDate) { 

	location.href="<? echo $hostUrl ?>/stock/resultStock/samJohnNepResult.php?nowDate="+nowDate;
} 
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                <h3><?php echo $_REQUEST['nowDate']?> 가치투자자 존네프<a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
                <?
	include($mainRoot  ."/calendar_op/samEps_calendar_date.html");
?>    <bR>                   
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
		 <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※ 가치투자자 존네프 ※</h3> <br> 
 
  -  가수익비율(PER)이 낮은 주식을 선택하였다. PER가 업종평균 이하, 순이익 증가율은
7% 초과, 매년 배당을 늘리고 성장하는 업종에 속해 있는가 등의 잣대를 활용해 투자를 결정했다<br><br>
  -  PER < 업종 평균 PER PEG < 업종 평균 PEG 11 종목 <br> 
  -  최근 3년간 각연도의 매출액 증가율 > 0 EBITDA margin > 업종평균 <br> 
  -  OPM > 업종평균 OPM 최근 3회계년도 간 전년대비 주당 잉여현금 흐름 증가율 > 0 <br>
				</div>                 
<ul data-role="listview"> 
   <li ><table width="100%"  >
 
    <tbody>
<tr> 
 <td width="5%" >N</td>
<td data-priority="2" width="40%" >종목명</td>
 
<?php if($nowDate  != ""){?>
<td width="20%" >수익</td> 
 <?php }?>
</tr>
		</tbody> 
   </table>	 </li>   
<?php
$rowCount = 1;
//while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
	$row = mysqli_fetch_assoc($res_roa); 

for($i=1;$i<16;$i++){   
	$super = 0;
	if($row["rank".$i."_now_per"] > 0 ){
		$super = (($row["rank".$i."_now_per"] /$row["rank".$i."_end_pr"] )-1) * 100;
	}
	$superColor = "red";
	if($super <0 ){
		$superColor = "blue";
	}
if($rowCount == 3){
	//다음 광고 
		include($mainRoot  ."/stock/include/AD_320_50.php"); 	
}
if($rowCount == 10){
	//다음 광고
	include($mainRoot  ."/stock/include/AD_320_100.php"); 
}
if( $row["rank".$i."_nm"]  != ""){
?>
    <li><a href="javascript:stockDitalMove('<?echo $row["rank".$i."_cd"] ?>' ,'<?echo substr($row["rank".$i."_nm"],0, strrpos($row["rank".$i."_nm"], '(') );    ?>')">
    
        <table width="100%" >
 
 
<tr> 
 <td width="5%" ><b style='color:red'> <?echo $rowCount ?></b> </td>
<td width="40%" > <?echo $row["rank".$i."_nm"] ?> </td> 
<!-- td width="20%" ><?echo number_format(  $row["rank".$i."_end_pr"]);	 ?>원                             </td -->  
<?php if($nowDate  != ""){?>
<td width="20%"  ><b style='color:<?php echo$superColor?>'><?echo   sprintf("%2.2f" , $super );  ?>%   </B>                        </td> 
 <?php }?>
</tr> 
    </table> </a></li> 
<?php 
}
$rowCount++;
}?> 

<BR><BR><BR><BR><BR><BR>

</ul>
</div>       

   
 <!-- menu_left -->
<?php 
include($mainRoot  ."/stock/recommendEps/include/menu_left.php");?>
 <!-- footer -->
<?php 
include($mainRoot  ."/stock/include/footer.php");?>

<!-- search panel -->    
<?php 
include($mainRoot  ."/stock/include/menu_search.php");?> 
                
</div> 
</body>
</html> 

<?php 
} ?>

