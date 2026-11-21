<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '');
 
include("/hosting/windmoter/html/stock/recommendEps/include/header.php");
//roa높은순 per낮은순 

	$query_roa = "select  		key_date,				rank1_cd, rank1_nm,	rank1_end_pr
	,rank2_cd, rank2_nm,	rank2_end_pr
	,rank3_cd, rank3_nm,	rank3_end_pr
	,rank4_cd, rank4_nm,	rank4_end_pr
	,rank5_cd, rank5_nm,	rank5_end_pr
	,rank6_cd, rank6_nm,	rank6_end_pr
	,rank7_cd, rank7_nm,	rank7_end_pr
	,rank8_cd, rank8_nm,	rank8_end_pr
	,rank9_cd, rank9_nm,	rank9_end_pr
	,rank10_cd, rank10_nm,	rank10_end_pr
	,rank11_cd, rank11_nm,	rank11_end_pr
	,rank12_cd, rank12_nm,	rank12_end_pr
	,rank13_cd, rank13_nm,	rank13_end_pr
	,rank14_cd, rank14_nm,	rank14_end_pr
	,rank15_cd, rank15_nm,	rank15_end_pr		
	  from stock_allocation_top where key_date=  ".$updateDay." order by  key_date  desc limit 1";
  $res_roa = mysql_query($query_roa, $conn);
 
?>  
 
<script> 
//상세
function stockDitalMove(isNo , isNm) { 

	location.href="https://cronya.pe.kr/stock/stockDetail.php?siteType=C&isNo="+isNo+"&isNm="+isNm;
}

//결과이동
function stockResultMove(nowDate) { 

	location.href="<? echo $hostUrl ?>/stock/resultStock/allocationResult.php?nowDate="+nowDate;
}   
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                <h3>외국인 지분증가 상위 Top 15 <a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
            <?
	include($mainRoot  ."/calendar_op/recommendEps_calendar_date.html");
?>    <bR>                 
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
		 <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※ 외국인 지분증가 상위 ※</h3> <br> 
 
  -  일주일 단위 검색<br>
  -  상위15개 <br>
  - 과거지분율(B)	지분율(A)	=변동(A-B)<br>
				</div>                 
<ul data-role="listview" >
   <li ><table width="100%"  >
 
    <tbody>
<tr> 
<td width="5%" >N</td>
<td data-priority="2" width="70%" >종목명(변동 %) </td>
 
</tr>
		</tbody>
   </table>	 </li>   
<?php
$rowCount = 1;
//while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
	$row = mysqli_fetch_assoc($res_roa); 
 
for($i=1;$i<16;$i++){   
if($rowCount == 3){
	//다음 광고 
		include($mainRoot  ."/stock/include/AD_320_50.php"); 	
}
if($rowCount == 13){
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

