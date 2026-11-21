<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
//DB접속


include("./include/header.php"); 
?>  

<!-- content -->
  
    <div  class="ui-content jqm-content">
  
                <h3>주요항목</h3>
                	                     <?php   	//다음 광고
	include($mainRoot  ."/stock/include/AD_320_50.php");?>            
        <div class="ui-grid-a ui-responsive">
        	<div class="ui-block-a">
        		<div class="jqm-block-content">
        			<h3><b style='color:red'>신</B>마법공식top 100 <a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>	
        			
              		<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
      				<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
      				<p><h3>※ <b style='color:red'>신</B> 마법공식  ※</h3> <br> 
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

        			<p><a href="/stock/magicNewList.php" data-ajax="false" class="ui-link">전 종목</a></p>
        			<p><a href="/stock/magicNewList.php?type=1" data-ajax="false" class="ui-link"><span style="color:red">코스피</span> (PBR↓,GP/A↑)</a></p>
        			<p><a href="/stock/magicNewList.php?type=2" data-ajax="false" class="ui-link"><span style="color:blue">코스닥</span> (PBR↓,GP/A↑)</a></p> 
        		</div>
        	</div>
        	
        	<div class="ui-block-b">
        		<div class="jqm-block-content">
        		
        			<h3>마법공식top 100 <a href="#popupCloseRight3" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>	
        			
              		<div data-role="popup" id="popupCloseRight3" class="ui-content" style="max-width:380px">
      				<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
      				<p><h3>※ 마법공식 투자 적략 ※</h3> <br> 
                   			<b>1단계:</b> 1년 동안 투자할 금액을 설정한다.<Br> 
      				     <b>2단계:</b>마법공식에 의해 순위가 매겨진 기업의 리스트를 얻는다 .<Br>
      				     <b>3단계:</b> 설정자금의 20~33%로 최상위 등수의 5~7개 기업의 주식을 구매한다.<Br>
      				     <b>4단계:</b> 설정자금이 소진될 때까지 2~3개월마다 2,3단계를 반복한다.<Br>
      				     (9~10개월 후면 20~30개 기업으로 구성된 마법공식 포트폴리오가 완성될 것이다)<Br>
      				     <b>5단계:</b> 각각의 개별주식은 1년 동안 보유한 후에 매도한다.<Br>
      				     <b>6단계:</b> 매도한 자금으로 다시 같은 수의 새로운 기업을 구매한다.<Br>
      				     <b>7단계:</b> 이 과정을 수년 동안 반복한다.
      				</div>   

        			<p><a href="/stock/magicList.php" data-ajax="false" class="ui-link">전 종목</a></p>
        			<p><a href="/stock/magicList.php?type=1" data-ajax="false" class="ui-link"><span style="color:red">코스피</span> (PER↓,ROA↑)</a></p>
        			<p><a href="/stock/magicList.php?type=2" data-ajax="false" class="ui-link"><span style="color:blue">코스닥</span> (PER↓,ROA↑)</a></p> 
        		</div>
        		        		
 
        	</div>   
	             <?php   	//다음 광고
	//include($mainRoot  ."/stock/include/AD_320_50_list40.php");
		include($mainRoot  ."/stock/include/AD_320_100.php");
		?>
	        	<div class="ui-block-a">
        		<div class="jqm-block-content">
        			<h3>종목발굴    투자 <a href="#popupCloseRight2" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
                
                                  		<div data-role="popup" id="popupCloseRight2" class="ui-content" style="max-width:380px">
      				<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
      				<p><h3>※ 종목발굴   투자방식 ※</h3> <br> 
                     <b>1단계:</b> 투자전략에 맞는 발굴 항목을 정한다.<Br>
				     <b>2단계:</b> 이전 상위 종목에 현재 추가 확인 <Br>
				     <b>예)</b> 1/1일 외국인 지분증가 상위 1위종목(삼성전자) 3/1일 현재 시세확인  <Br>
				     <b>3단계:</b> 이전 패턴을 통해 현재 투자종목을 선정 한다<Br> 
      				</div>   
      				                			<p><a   style="background-color: #fdf7fa; " href="/stock/resultStock/samForeignerResult.php" data-ajax="false">외국인5일 순매수</a></p>
                        <p><a   style="background-color: #fdf7fa; " href="/stock/recommendEps/allocationList.php" data-ajax="false"> 외국인 지분증가 상위 Top 15</a></p>  	       
                         <p><a   style="background-color: #fdf7fa; " href="/stock/recommendEps/roeTopList.php" data-ajax="false">자기자본이익률(ROE)  상위</a></p>   
             
        		</div>
        	</div>   
        	<div class="ui-block-b">
        		<div class="jqm-block-content">
        			<h3>그레이엄(가치투자)</h3>

                           <p><a href="/stock/contrarianList.php" data-ajax="false" > 전 종목  (PER↓,PBR↓,배당↑)  </a></p>
                            <p><a href="/stock/contrarianList.php?type=1" data-ajax="false" ><span style="color:red">코스피</span>  </a></p> 
                            <p><a href="/stock/contrarianList.php?type=2" data-ajax="false" style="color:blue"><span style="color:blue">코스닥</span>  </a></p> 
        		</div>
        	</div> 
      	
        </div> 
                
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

   
<?php 
//공지사항
//include($mainRoot  ."/stock/include/notic.php");

?> 
          
</div> 
 
</body>
</html>

