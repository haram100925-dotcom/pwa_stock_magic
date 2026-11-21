<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
 //https://ppss.kr/archives/182133
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '' ?? ''); 
include("/hosting/windmoter/html/stock/include/header.php");
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
//roa높은순 per낮은순 
/*
$query_roa = " SELECT SUM(roaCnt ) AS roaCnt ,isu_cd ,isu_nm  ,per , roa , roe ,end_pr FROM  (
		SELECT * FROM  (
		SELECT @rowroa := @rowroa + 1 AS roaCnt ,   a.per , a.roa , a.roe  ,a.isu_cd ,a.isu_nm  ,a.end_pr  FROM stock_valuations a
		,(SELECT @rowroa:=0) TMP  , stock_daum_roe of  WHERE  a.isu_cd = of.isu_cd 
 
		AND (of.shares_cnt  * a.end_pr )  >57050000000  AND of.shares_cnt > 15000000 AND  a.dvd_yld > 0  AND   a.per < 100  AND
		magic_no ='Y' AND iisu_code ='-' AND a.roa > 0  AND per !=100000 AND per > 0  ".$marketQueryPu." ORDER BY  a.roe DESC, per ASC  LIMIT 100 
		) ta
		UNION ALL  
		SELECT * FROM  ( 
		SELECT @rownum := @rownum + 1 AS roaCnt  ,a.per , a.roa , a.roe  ,a.isu_cd ,a.isu_nm ,a.end_pr  FROM stock_valuations a
		,(SELECT @rownum:=0) TMP  , stock_daum_roe of  WHERE  a.isu_cd = of.isu_cd
 
		AND (of.shares_cnt  * a.end_pr )  >57050000000   AND of.shares_cnt > 15000000 AND  a.dvd_yld > 0  AND   a.per < 100  AND
		magic_no ='Y' AND iisu_code ='-' AND per !=100000 AND per > 0  AND a.roa > 0 	 ".$marketQueryPu." ORDER BY a.roa DESC, per ASC   LIMIT 100 
		) tb
	) m   GROUP BY isu_cd ORDER BY roaCnt ASC LIMIT 100 ";
	
	*/
$query_roa = "
SELECT SUM(gpaCnt ) AS gpaCnt ,isu_cd ,isu_nm  ,pbr , gpa , roe ,end_pr  FROM  (		
		SELECT  @rownum := @rownum + 1 AS gpaCnt ,sub.* FROM
		(
		SELECT  a.pbr , a.gpa , a.roe  ,a.isu_cd ,a.isu_nm ,a.end_pr  FROM stock_valuations a,
		  stock_daum_roe sdr  WHERE  a.isu_cd = sdr.isu_cd
 
		AND (sdr.shares_cnt  * a.end_pr )  >57050000000  and sdr.finance_cnt < 6 AND sdr.shares_cnt > 15000000 AND  a.dvd_yld > 0  AND   a.pbr < 100  AND
		magic_no ='Y' AND iisu_code ='-' AND a.pbr !=100000 AND a.pbr > 0  AND a.gpa >0   ".$marketQueryPu." 	 and a.finance_cnt < 5  ORDER BY   a.pbr ASC  
		) sub,(SELECT @rownum:=0) TMP  
				UNION ALL  
		SELECT   @rownum3 := @rownum3 + 1 AS gpaCnt ,sub.* FROM
		(
		SELECT  a.pbr , a.gpa , a.roe  ,a.isu_cd ,a.isu_nm ,a.end_pr  FROM stock_valuations a,
		  stock_daum_roe sdr  WHERE  a.isu_cd = sdr.isu_cd
 
		AND (sdr.shares_cnt  * a.end_pr )  >57050000000  and sdr.finance_cnt < 6  AND sdr.shares_cnt > 15000000 AND  a.dvd_yld > 0  AND   a.pbr < 100  AND
		magic_no ='Y' AND iisu_code ='-' AND a.pbr !=100000 AND a.pbr > 0  AND a.gpa >0 ".$marketQueryPu."   and a.finance_cnt < 5  ORDER BY   a.gpa DESC  
		) sub,(SELECT @rownum3:=0) TMP 
	)  m  GROUP BY isu_cd, isu_nm, pbr, gpa, roe, end_pr ORDER BY gpaCnt ASC LIMIT 100  ";

$res_roa = mysql_query($query_roa, $conn);

//하루에 한번 순위 저장 (여부체크)
//$query = "select  key_date  from stock_Insert_history order by  key_date  desc limit 1";
$today = date("Ymd"); 
$todayTime = date("H");
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
	$query = "select  key_date  from stock_magic_New_top where   key_date  =". $today;
	$res = mysql_query($query, $conn);
	
	if($res){
		$rowcount=mysqli_num_rows ($res); 
		if($rowcount  > 0){
			$is_Insert = false; 
		}
	}
}

?>   
<?php  
    function GenerateString($length)  
    {  
        $characters  = "0123456789";  
        $characters .= "abcdefghijklmnopqrstuvwxyz";  
        $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";  
          
        $string_generated = "";  
          
        $nmr_loops = $length;  
        while ($nmr_loops--)  
        {  
            $string_generated .= $characters[mt_rand(0, strlen($characters))];  
        }  
          
        return $string_generated;  
    }  
?> 
<script> 
//상세
function stockDitalMove(isNo , isNm) { 

	location.href="<? echo $hostUrl ?>/stock/stockDetail.php?isNo="+isNo+"&isNm="+isNm;
}
//결과이동
function stockResultMove(nowDate) { 

	location.href="<? echo $hostUrl ?>/stock/resultStock/magicNewResult.php?nowDate="+nowDate;
}  


function exportExcel(){
  // step 1. workbook 생성
  var wb = XLSX.utils.book_new();

  // step 2. 시트 만들기 
  var newWorksheet = excelHandler.getWorksheet();

  // step 3. workbook에 새로만든 워크시트에 이름을 주고 붙인다.  
  XLSX.utils.book_append_sheet(wb, newWorksheet, excelHandler.getSheetName());

  // step 4. 엑셀 파일 만들기 
  var wbout = XLSX.write(wb, {bookType:'xlsx',  type: 'binary'});

  // step 5. 엑셀 파일 내보내기 
  saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), excelHandler.getExcelFileName());
}

var excelHandler = {
    getExcelFileName : function(){
        return 'magicNewData.xlsx';	//파일명
    },
    getSheetName : function(){
        return 'list';	//시트명
    },
    getExcelData : function(){
        return document.getElementById('tableData'); 	//TABLE id
    },
    getWorksheet : function(){
        return XLSX.utils.table_to_sheet(this.getExcelData());
    }
}

function s2ab(s) { 
  var buf = new ArrayBuffer(s.length); //convert s to arrayBuffer
  var view = new Uint8Array(buf);  //create uint8array as viewer
  for (var i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet
  return buf;
}
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                 <h3><b style='color:red'>신</B> 마법공식 <?echo $market_gubun_nm  ?> Top 100 <a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a>
                </h3>

            <style>
            .toolbar-inline { display:flex; align-items:center; gap:8px; flex-wrap:nowrap; }
            #excelDownload { min-width: 140px; }
            </style>

            <div class="toolbar-inline">
            <?php    
       $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
       // 브라우저 User-Agent 체크 (Chrome, Firefox, Safari, Edge 등)
       $is_browser = preg_match('/(Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)/i', $user_agent);
       
       if($is_browser){ // 브라우저인 경우만
    ?>
                 <button type="button" id="excelDownload" onclick='exportExcel()'    class="ui-btn ui-btn-inline ui-icon-action ui-btn-icon-left">다운로드</button>
    <?php 
       }
    ?>
            <?
            $calendar_year="2019";
            $calendar_morth = "02";
            $calendar_day = "20";
            include($mainRoot  ."/calendar_op/calendar_date.php");
?>
            </div>
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
				<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※ 신 마법공식 ※</h3> <br> 
				- 시가총액 500억 이상 종목  <br>
				- 상장 주식수 1500 만개 이상<br>
				- 배당수익률 0% 이상 종목<br>
				- PBR 100배 이하 종목<br>
				- 금용 보험 업종 제외  <br>
				- 관심종목 제외  <br>
				- GP/A= 영업이익/자산총계<br>
      	- 영업이익 = 매출 총이익 -(판매비+ 관리비)<br> 
				- PBR 낮은 순 GP/A 높은 순 합산 100위 선정 
				</div>   
<?php
// Read all rows into an array so we can render both the mobile list and a hidden table for Excel export
$rows = array();
while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
	$rows[] = $row;
}
?>          
<ul data-role="listview" data-filter="true" data-filter-placeholder="종목명으로 검색.." data-inset="true">
 
   <li ><table width="100%"  >
 
    <tbody>
<tr> 
<td width="5%" >N</td>
<td data-priority="2" width="40%" >종목명</td>
<td width="20%" >PBR ↓</td>
<td width="20%" >GP/A ↑</td>
<td width="15%" >ROE</td>
</tr>
		</tbody>
   </table>	 </li>
<?php 
$rowCount = 1;
foreach ($rows as $row) {
if($rowCount == 3){
	//다음 광고 
		include($mainRoot  ."/stock/include/AD_320_50.php"); 	
}
if($rowCount == 13){
	//다음 광고
	include($mainRoot  ."/stock/include/AD_320_100.php"); 
}

if($rowCount == 33){
	//다음 광고 
	//	include($mainRoot  ."/stock/include/AD_320_50_list30.php"); 	
}

 
	?>
    <li><a href="javascript:stockDitalMove('<?echo $row[1]?>' ,'<?echo rawurlencode($row[2])?>')">
    
    <table width="100%" >
 

<tr> 
<td width="5%" ><b style='color:red'> <?echo $rowCount ?></b> </td>
<td width="40%" > <?echo $row[2] ?> </td>
<td width="20%" ><?echo $row[3]?> 배</td>
<td width="20%" ><?echo sprintf("%2.2f" , $row[4] );	 ?></td>
<td width="15%" ><?echo $row[5]?>%</td>
</tr>
	
    </table> </a></li> 
<?php  
//magic 통계를 위해 insert 한다
if($is_Insert  && $market_gubun_nm = "전 종목"  && (int)$todayTime  > 11){
	
	if($rowCount == 1){
			$sql_magic = "insert into stock_magic_New_top
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
		
		$sql_magic = "UPDATE stock_magic_New_top 
		SET
		rank" . $rowCount . "_cd =  '$row[1]' ,rank" . $rowCount . "_nm ='$row[2]' ,rank" . $rowCount . "_end_pr = '$row[6]' 
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


<!-- Hidden table used by Excel export (keeps existing JS export working) -->
<table id="tableData" style="display:none;">
	<thead>
		<tr>
			<th>랭킹</th>
			<th>종목명</th>
			<th>PBR</th>
			<th>GP/A</th>
			<th>ROE</th>
		</tr>
	</thead>
	<tbody>
<?php
$rowCount = 1;
foreach ($rows as $row) {
	echo "<tr>\n";
	echo "<td>". $rowCount ."</td>\n";
	echo "<td>".htmlspecialchars($row[2])."</td>\n";
	echo "<td>". $row[3] ."</td>\n";
	echo "<td>". sprintf("%2.2f", $row[4])."</td>\n";
	echo "<td>". $row[5] ."</td>\n";
	echo "</tr>\n";
	$rowCount++;
}
?>
	</tbody>
</table>


</div>       
 
 <!-- menu_left --> 
<?php 
include($mainRoot  ."/stock/include/menu_left.php");?>
 <!-- footer -->
<?php 
include($mainRoot  ."/stock/include/footer.php");?>

<!-- search panel -->    
<?php 
include($mainRoot  ."/stock/include/menu_search.php");?> 

                
</div> 
</body>
</html>

