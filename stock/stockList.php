<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '');

include("./include/header.php");
 
//per 낮은순
if($market_gubun_type ==1 ){
	$market_gubun_nm = "<b style='color:red'>코스피</B>";
	$query_roa = " SELECT isu_cd , 1,isu_nm  ,    CASE
		WHEN PER <= 0
		THEN 'NaN' 	ELSE PER
	END AS PER2,   ROE , dvd_yld  FROM   stock_valuations  where key_date ='".$updateDay."'
	and market_gubun = 'STK'    ORDER BY  PER2 ASC , ROE DESC  , dvd_yld desc   ";  
}else{
	$market_gubun_nm = "<b style='color:blue'>코스닥</B>";
	$query_roa = " SELECT isu_cd , 1,isu_nm  ,    CASE
		WHEN PER <= 0
		THEN 'NaN' 		ELSE PER
	END AS PER2,   ROE , dvd_yld  FROM   stock_valuations  where key_date ='".$updateDay."'
	and market_gubun = 'KSQ'   ORDER BY  PER2 ASC , ROE DESC  , dvd_yld desc    ";  
}

$res_roa = mysql_query($query_roa, $conn);


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
function detailMove(isNo , isNm) {
	location.href="https://cronya.pe.kr/stock/stockDetail.php?isNo="+isNo+"&isNm="+isNm;
}
function ozit_timer_test (){
	location.href="https://docs.google.com/spreadsheets/d/1dvvOMXiHBBykznXGaLvMbPTkSBruevYXJ7KsgbXRK4c/pubhtml?gid=0&single=true&<?php echo GenerateString(6)?>";
}
function isuMove(isNo , isNm) {
alert(isNo);
	// 로딩
	$.ajax({
	 // url: "https://script.google.com/macros/s/AKfycbyHQ6rSCvT4PTjEJJYfqqTpx5mdydHZcQ0tWG9eIqJmaVzLCB4A/exec",
	  url: "https://script.google.com/macros/s/AKfycbyxxhygJj4fITxVqqf5CA5uTLjZDRYdKx386HIeBGoBEPhIGPw/exec",
	  data: {"A": isNo    },
	  type: "POST",
        success:function(args){   
        	setTimeout("ozit_timer_test()", 10); 
        	  
        },   
      // beforeSend:showRequest,  
        error:function(e){  
            alert('관련데이터 정보가 없습니다.');  
        } 		  
	}); 
}
// https://script.google.com/macros/s/AKfycbyTrVVnccWp0w_vBUr0aQyVmZoe4q7d20FPUMcJfWF4_sJM7tI/exec
//  url: "https://script.google.com/macros/s/AKfycbyhQ2NXrP0qhzXycy4Bv8alYi7fJk-7kiS5rTaqi998A8fw7y4D/exec",
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                <h3><?php echo $market_gubun_nm?> 전 종목 리스트 <a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
             
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
				<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※<?php echo$market_gubun_nm?> 전종목  ※</h3> <br> 
				- PER  주가수익률    :  낮을수록 좋다 <br>
                      <!--주가를 주당순이익으로 나눈 것으로, 주가가 주당 순이익의 배율이 얼마인가를 나타내는 지표이다. 
                    그렇기에 PER가 낮을 경우 해당 회사가 거둔 이익에 비해 주가가 낮고 그에 따라 기업의 가치에 비해 주가가 저평가되어 있다는 의미로 볼 수 있다
                    PER은 수익성 대비 주가를 나타낸다. /-->
				- PBR  주가순자산비율  : 낮을수록 좋다 <br> 
                    <!--주가를 장부가치로 나눈 비율을 의미해요. PBR은 1보다 낮다면 저평가, 높다면 고평가되었다는 뜻인데요. 
                    장부가치에 비해 실제 시장가격이 얼마나 낮은지 높은지를 보여주기 때문이에요.
                    PBR은 자본 대비 얼마나 주가가 고평가되었는가 //-->
                
				- ROE  평균자기자본 대비 당기순이익<br>*** ROE = PBR / PER : ↑좋다 (자기자본이익률) <br> 
                    <!--ROE는 성장성이 얼마나 큰가(회사자본이 복리로 성장하는 비율) 
                    //-->
				- ROA  평균총자산 대비 당기순이익 (총자산이익률)<br>*** ROA = 순이익/자산총계 :  많을수로 좋다<br> 
                    <!--(ROA, return on assets) 세금을 차감한 순이익을 총자산으로 나눈 비율로 기업이 수익의 창출과정에서 
                    주어진 총자산을 얼마나 효율적으로 이용했는가를 측정하는 수익성 지표 //-->
				- PER 낮은 순 배당수익률이 높은 순으로 정렬 
				</div>                 
<ul data-role="listview" data-filter="true" data-filter-placeholder="Search fruits..." data-inset="true">

   <li ><table width="100%"  >
 
<tbody>
<tr> 
<td data-priority="2" width="40%" >종목명</td>
<td width="20%" >PER ↓</td>
<td width="20%" >ROE ↑</td>
<td width="20%" >배당수익률 ↑</td>
</tr>
		</tbody>
   </table>	 </li>
<?php 
$rowCount = 1;
while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
	if($rowCount == 10){
		//다음 광고
		include("./include/AD_320_100.php");
	}
	?>
    <li><a href="javascript:detailMove('<?echo $row[0]?>' , '<?echo rawurlencode($row[2])?>')">
    
    <table width="100%" >
 

<tr> 
<td width="40%" > <?echo $row[2] ?> </td>
<td width="20%" ><?echo $row[3]?> 배</td>
<td width="20%" ><?echo sprintf("%2.2f" , $row[4] );	 ?>%</td>
<td width="20%" ><?echo $row[5]?>%</td>
</tr>
	
    </table> </a></li> 
<?php
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

