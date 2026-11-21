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
	 from samsung_dreman_top a";
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

	location.href="<? echo $hostUrl ?>/stock/resultStock/samdremanResult.php?nowDate="+nowDate;
} 
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                <h3><?php echo $_REQUEST['nowDate']?> 역발상 투자제왕 드레먼<a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
                <?
	include($mainRoot  ."/calendar_op/samEps_calendar_date.html");
?>    <bR>                   
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
		 <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※ 역발상 투자제왕 드레먼 ※</h3> <br> 
 
  -   낮은 주가수익비율(PER)/ 주가순자산비율(PBR), 높은 주가현금흐름비율(PCR)/배당수익률 등 역발상<br>
      지표를 활용하여 비인기 종목에 투자하였다.<br><br>
  -  시가 총액이 상위  80 % 이내  PER가 거래소 하위  60 % 이내  <br>
 -  이자비용발생부채/자본총계 < 1   총부채/총자산 < 업종 중간값   <br>
 -  EPS증가율 > KOSPI + KOSDAQ 중간값   EPS 증가율 > 업종 중간값   <br>
 -  금년 EPS성장율 > 전년 EPS 증가율   익년 EPS 증가율 > 금년 EPS증가율(F)  <br> 
 -  배당수익률 > KOSPI + KOSDAQ 중간값<br>
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
$rowCount++;
}?> 



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

