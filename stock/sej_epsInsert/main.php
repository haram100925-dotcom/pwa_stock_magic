<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
//DB접속


include("./include/header.php"); 
?>   
<!-- content -->
    
    <div  class="ui-content jqm-content">
  
  		<!-- Hero Intro: 신 마법공식 정의 및 CTA -->
  		<section style="background:#ffffff;border:1px solid #eee;border-radius:8px;padding:16px;margin-bottom:16px">
  			<h1 style="margin:0 0 8px 0;font-size:20px;line-height:1.4">신 마법공식: PBR↓ · GP/A↑ 기반의 가치주 선별 전략</h1>
  			<p style="margin:0 0 12px 0;color:#444">시가총액, 상장주식수, 배당, 업종 필터를 통과한 종목 중에서 PBR이 낮고 GP/A(영업이익/자산총계)가 높은 기업을 함께 고려하여 상위 100개 종목을 선별하는 전략입니다. 아래 리스트와 메뉴에서 코스피·코스닥별 순위와 백테스트 결과를 확인하세요.</p>
  			<div>
  				<a href="/stock/magicNewList.php" data-ajax="false" class="ui-btn ui-btn-inline ui-mini ui-btn-b" aria-label="신 마법공식 상위 100 보기">상위 100 바로보기</a>
  				<a href="#stockViewTotal" class="ui-btn ui-btn-inline ui-mini" onclick="stockDateTotal()" aria-label="종목 검색 패널 열기">종목 검색</a>
  			</div>
  		</section>
  
                
                	                     <?php   	//다음 광고
	//include($mainRoot  ."/stock/include/AD_320_50.php");?>   
	
	
	           
        <div class="ui-grid-a ui-responsive" role="region" aria-label="메인 콘텐츠 그리드">
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
                    <p><a href="https://now24.tistory.com/99" data-ajax="false" class="ui-link"><img src="/school/img/new.png" width="35"   height="25"> 신 마법공식 Top10 종목 분석 </a></p> 
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
					<p><a href="https://now24.tistory.com/100" data-ajax="false" class="ui-link"><img src="/school/img/new.png" width="35"   height="25"> 마법공식 종목 분석 </a></p> 
        		</div>
        		        		
 
        	</div>  
        				 <iframe frameBorder='0' scrolling='no' src='./include/amcharts.html' style='width:100%; height:100%; min-height:300px;'></iframe> 
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
                         <p><a   style="background-color: #fdf7fa; " href="/stock/contrarianList.php" data-ajax="false">그레이엄 가치투자 (PER↓,PBR↓,배당↑)</a></p>   
        		
        		</div>
        	</div>   
 
        	<div class="ui-block-b">
        		<div class="jqm-block-content">
        			<h3>비트코인(가상자산)</h3>
                            <?php 
 
                          
                            function protocalHttpWeb ($urlDomain , $urlStk )
                            {
                                $curl_handle = curl_init();
                                curl_setopt($curl_handle, CURLOPT_URL, $urlStk); 
                                curl_setopt($curl_handle, CURLOPT_HEADER, 0);
                                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($curl_handle, CURLOPT_USERAGENT,  $urlDomain);

                                $json = curl_exec($curl_handle); 
                                return $json;
                            } 
                            function tagListData($strTag, $content)
                            {
                                $reg =  sprintf("/\<%s[^>]*\>(.*?)\<\/%s\>/is" , $strTag, $strTag) ;
                                preg_match_all($reg , $content, $matches , PREG_PATTERN_ORDER );
                                return $matches[1];
                            }


                            $urlDomain = "https://now24.tistory.com"; 
							$urlStk ="https://now24.tistory.com/category/%ED%95%B4%EC%99%B8%20%EA%B8%88%EC%9A%A9%20%EA%B2%BD%EC%A0%9C";
							$json  =  protocalHttpWeb( $urlDomain , $urlStk ); 
							//echo 	$json;
							// $script_List = tagListData('table', $json);
							// $html = $script_List[0];
							// $html = preg_replace('/(<a)/', '<br>$1', $html);    
							// $html = preg_replace('/<a href="([^"]*)"/', '<a href="'.$urlDomain.'$1" data-ajax="false"', $html); 
 

							$dom = new DOMDocument();
							libxml_use_internal_errors(true); // HTML 파싱 오류를 무시합니다.
							
							$dom->loadHTML(mb_convert_encoding($json, 'HTML-ENTITIES', 'UTF-8')); // UTF-8로 인코딩
							libxml_clear_errors(); 
							// XPath를 사용하여 <li> 요소를 선택합니다.
							$xpath = new DOMXPath($dom);
							$listItems = $xpath->query('//div[@id="body" and @class="list"]//li'); 

							foreach ($listItems as $item) {
								$links = $item->getElementsByTagName('a');
								
								foreach ($links as $link) {
									// 링크의 href 속성과 텍스트 내용을 출력합니다.
									echo '<a href="' . $urlDomain . $link->getAttribute('href') . '">' . $link->nodeValue . '</a><br><br>';
								}
							} 
							?>
        		</div>
        	</div> 
      	 
        </div> 
      
             
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

   
<?php 
//공지사항
//include($mainRoot  ."/stock/include/notic.php"); 

?> 
</div> 
 



<!--   <iframe src="http://m.sej.woobi.co.kr/stock/sej_epsInsert/epsInsert.php" style="visibility:hidden;display:none"></iframe>  -->
</body>
</html>

