<?php  
//header('Content-Type: text/html;charset=EUC-KR'); //header()
$siteType = $_REQUEST['siteType'];
$isNo = $_REQUEST['isNo']; 
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? ''); 
include("/hosting/windmoter/html/stock/include/header.php");
 
$_TOKEN = $_REQUEST["token"] ?? ''; 
$_TOKEN_FCM_ID = ""; // 토큰 키 값
$rowcount= 0;
$fcm_id= "";
$isInsertDate = "N";
if(strlen($_TOKEN)  > 0){ // 앱 인경우만

	// 1. 데이터베이스에서  계정 알리미 설정 정보가져오기
 
	$query = " SELECT  a.fcm_id   ,b.isu_cd
	FROM  stock_fcm_users a LEFT JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id  
	WHERE   a.use_yn ='Y' AND Token ='". $_TOKEN."'"; 
 
	$res  = mysql_query($query , $conn);
	if($res){
		$rowcount=mysqli_num_rows ($res);  
  	if($rowcount > 0){
  	  while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {	
  		  if( $isNo == $row["isu_cd"]  ) $isInsertDate = "Y";
  	    $fcm_id = $row["fcm_id"]; 
  	  }
  	}		
	}
}
/* 
 // 테스트 테블릿 폰
	$query = " SELECT  a.fcm_id   ,b.isu_cd
	FROM  stock_fcm_users a  LEFT JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id  
	WHERE   a.use_yn ='Y' AND  a.fcm_id  ='4278'"; 
	$res  = mysql_query($query , $conn);
	if($res){
		$rowcount=mysqli_num_rows ($res);  
	} 
	if($rowcount > 0){
	  while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {	
		  if( $isNo == $row["isu_cd"]  ) $isInsertDate = "Y";
	    $fcm_id = $row["fcm_id"]; 
	  }
	}
 */
// https://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:"..$isNo."|SERVICE_RECENT_ITEM:"..$isNo."&_callback=window.__jindo2_callback._3868
//현재 가격 -네이버 nv": 6210, 현재가격
$urlStk ="http://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:".$isNo."|SERVICE_RECENT_ITEM:".$isNo."&_callback=window.__jindo2_callback._3868";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://polling.finance.naver.com");
 

$json = curl_exec($curl_handle); 
 
$startNum = ( strrpos ( $json , "("  ) +1 ) ;
$endNum = (strlen( $json)-$startNum-1  );
$json = substr($json , $startNum ,$endNum);
$json = iconv("EUC-KR" ,"UTF-8",$json );
$result_array = json_decode($json);  

//$priceStock = $isNo; //현재가격
$priceStock = $result_array ->result->areas[0]->datas[0]->nv ; //현재가격
$isNm = $result_array ->result->areas[0]->datas[0]->nm ; //종목명
 

$urlStk ="https://stockwatch.co.kr/vscore/".$isNo ;
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); // 응답 값을 브라우저에 표시하지 말고 값을 리턴
//curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://search.itooza.com/");

curl_setopt ($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
curl_setopt($curl_handle, CURLOPT_REFERER, 'http://search.itooza.com/');
	 
$json = curl_exec($curl_handle);  
$script_List = tagListData('table', $json ); 
$allocation_List = tagListData('tr', $script_List[3]); // 년별 배당
//echo $urlStk;
/*
$urlStk ="http://www.sejongdata.com/business_include_fr/table_main0_bus_01.html?&no=".$isNo;
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://www.sejongdata.com/"); 


$json = curl_exec($curl_handle);
$script_List = tagListData('table', $json); 
//print_r($script_List[1]  ."=====");
//print_r($script_List[4]  ."=====");
$profitability_List = tagListData('tr', $script_List[1]); // 년별 매출액 
$finance_List = tagListData('tr', $script_List[4]); // 년별 재무비율
//print_r($finance_List[0]  ."====="); 
*/

//http://comp.fnguide.com/svo/ASPSYSTEM/ASP_Company.asp?pGB=17&GiCode=A039790
$urlStk ="http://comp.fnguide.com/svo/ASPSYSTEM/ASP_Company.asp?pGB=17&GiCode=A".$isNo;
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://comp.fnguide.com/");


$json = curl_exec($curl_handle);
$script_List = tagListData('table', $json);
$stockInfo =    str_replace("작성일:", "정보:", str_replace("&nbsp;", "",preg_replace("/\s+/", "",$script_List[13]))) ;
  

 

 function tagListData($strTag, $content)
 {
 	$reg =  sprintf("/\<%s[^>]*\>(.*?)\<\/%s\>/is" , $strTag, $strTag) ;
 	preg_match_all($reg , $content, $matches , PREG_PATTERN_ORDER );
 	return $matches[1];
 }
 
 function tagReplace($strTag )
 {
 	$strTag = strip_tags(preg_replace("/\s+/", "",  str_replace("N/A", "0",     str_replace(",", "",   $strTag))  ) );
 	if($strTag =="" ){
 		$strTag = 0;
 	}
 	return $strTag;
 	
 }
 
?> 
 
 <div role="main" class="ui-content jqm-content" >
 <table><tr><td  > <h2><?php echo $isNm  ?>  </h2></td>
 <td > 
 <?if($isInsertDate =="Y"){?>
  <button onclick="mySend();" class="ui-btn ui-icon-arrow-r ui-btn-icon-left ui-shadow ui-corner-all"> 나의 관심종목</button></td></tr></table>
 <?}else{?>
  <button onclick="myInsertSend();" class="ui-btn ui-shadow ui-corner-all ui-btn-icon-left ui-icon-eye"> 종목 등록(알림)</button></td></tr></table>
 <?}?>

 <form name="sendForm" action="./include/myInsert.php"  method="post">
<input type="hidden" name="fcm_id" id="fcm_id" value="<?echo $fcm_id?>">  
<input type="hidden" name="isu_cd" id="isu_cd" value="<?echo $isNo?>">  
<input type="hidden" name="isu_nm" id="isu_nm" value="<?echo $isNm?>">    
<input type="hidden" name="dataType" id="dataType" value="in">  
</form> 
<script> 
function mySend() { 
 	location.href="<? echo $hostUrl ?>/stock/myList.php"; 
}
function myInsertSend() {  
  var sendForm = document.sendForm; 
  if( parseInt(<?echo $rowcount?>) < 1   ){
     alert("안드로이드 앱 접속시 이용가능합니다.");
      	location.href="<? echo $hostUrl ?>/stock/myList.php"; 
  }else if( parseInt(<?echo $rowcount?>) > 9  ){
    alert("최대 10개 관심종목 등록 가능합니다.");
     	location.href="<? echo $hostUrl ?>/stock/myList.php"; 
  }else{
 
        sendForm.submit();
 
  }
}  
 
</script>  
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    document.charset = "euc-kr";
      google.charts.load('current', {'packages':['corechart']});


    //--------------------------수익성-------------------//      
      google.charts.setOnLoadCallback(drawVisualization);  
 
      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        // 그래프 색 , { role: "style" }
        var data = google.visualization.arrayToDataTable([
         ['날짜', '매출액', '영업이익', '순이익'  ],
  <?php 
  $dayList =  tagListData('td', $profitability_List [0]); //날짜
  $salesList =  tagListData('td', $profitability_List [1]); //매출액
  $operatingList =  tagListData('td', $profitability_List [2]); //영업이익
  $netProfitList =  tagListData('td', $profitability_List [3]); //순이익
  $comba = ",";
  $yearNum = 0;
  for($i=1;$i<count($salesList);$i++)
  { 
  		if(count($salesList) == ($i+1) ){
  			$comba = "";
  		}
  		$yearText = substr(  strip_tags(preg_replace("/\s+/", "", $dayList[1]) ) , 0, 4);
        print_r( " ['". sprintf("%04s",   $yearText+$yearNum ) .".12',  ".tagReplace(  $salesList[$i]  ) ." ,   
        		 ". tagReplace( $operatingList[$i] ) ." ,          ". tagReplace(  $netProfitList[$i] ) ."  ]".$comba."");
        $yearNum++;

  }

  ?>         
     	 ]);

 
        var view = new google.visualization.DataView(data);
        view.setColumns([0, 1,

                         { calc: "stringify",
                           sourceColumn: 1,
                           type: "string",
                           role: "annotation" },
                           2,   3,                { calc: "stringify",
            sourceColumn: 3,
            type: "string",
            color: 'red' ,
            role: "annotation" }
                         ]);
        
	    var options = {
	     // title : ' [ 수익성 ] ', 
	      hAxis: {showTextEvery:3, maxAlternation:4,maxTextLines:1,textStyle:{fontSize: '9'}},
	      vAxes: {0: {logScale: false}, 1: {logScale: false}},
	      series:{0:{type: "bars", targetAxisIndex:0}, 1:{type: "line", targetAxisIndex:1}},
	      legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
		    //  isStacked: true,
	      chartArea: {  top:"20%" ,left :"2%" ,right: "2%"},
		      
	   //  legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
	      seriesType: 'bars',  
	        bar: {groupWidth: "100%"}, 
	      series: {2: {type: 'line' ,color: 'red'}} //,color: 'red' 
	
	 
	    };
	    var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
	    chart.draw(view, options); 
  	}
//--------------------------이익율-------------------//
   google.charts.setOnLoadCallback(financeDrawVisualization);  
      
   function financeDrawVisualization() {
        // Some raw data (not necessarily accurate)
        // 그래프 색 , { role: "style" }
        var data = google.visualization.arrayToDataTable([
         ['날짜', '영업이이율', '순이익율'   ],
  <?php 
  $dayList =  tagListData('td', $finance_List [0]); //날짜
  $salesList =  tagListData('td', $finance_List [2]); //영업이이율
  $operatingList =  tagListData('td', $finance_List [3]); //순이익율 
  $comba = ",";
  $yearNum = 0;
  for($i=1;$i<count($salesList);$i++)
  { 
  		if(count($salesList) == ($i+1) ){
  			$comba = "";
  		}
  		$yearText = substr(  strip_tags(preg_replace("/\s+/", "", $dayList[1]) ) , 0, 4);
        print_r( " ['". sprintf("%04s",   $yearText+$yearNum ) .".12',  ".tagReplace(   $salesList[$i] ) ." ,   
        		 ".tagReplace (  $operatingList[$i]  ) ."   ]".$comba."");
        $yearNum++;

  }

  ?>         
     	 ]);
        var view = new google.visualization.DataView(data);
        view.setColumns([0, 1,

                         { calc: "stringify",
                           sourceColumn: 1,
                           type: "string",
                           role: "annotation" },
                           2,                   { calc: "stringify",
            sourceColumn: 2,
            type: "string",
            color: 'red' ,
            role: "annotation" }
                         ]);
        
	    var options = {
	   //   title : ' [ 이익율 ] ',
	      hAxis: {showTextEvery:3, maxAlternation:4,maxTextLines:1,textStyle:{fontSize: '9'}},
	      vAxes: {0: {logScale: false}, 1: {logScale: false}},
	      series:{0:{type: "bars", targetAxisIndex:0}, 1:{type: "line", targetAxisIndex:1}},
	      legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
		    //  isStacked: true,
	      chartArea: {  top:"20%" ,left :"2%" ,right: "2%"},
		      
	   //  legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
	      seriesType: 'bars',  
	        bar: {groupWidth: "100%"}, 
	      series: {2: {type: 'line' ,color: 'red'}} //,color: 'red' 
	
	 
	    };
	    var chart = new google.visualization.ComboChart(document.getElementById('chart_div_finance'));
	    chart.draw(view, options); 
  	}

 //--------------------------안정성-------------------//
   google.charts.setOnLoadCallback(stabilityDrawVisualization);  
      
   function stabilityDrawVisualization() {
        // 부채총계 /자본총계 = 부채비율, 순이익/매출액 = 순이익률
        var data = google.visualization.arrayToDataTable([
         ['날짜', '부채비율', '순이익율'   ],
  <?php 
  $dayList =  tagListData('td', $finance_List [0]); //날짜
  $salesList =  tagListData('td', $finance_List [1]); //부채비율
  $operatingList =  tagListData('td', $finance_List [3]); //순이익율 
  $comba = ",";
  $yearNum = 0;
  for($i=1;$i<count($salesList);$i++)
  { 
  		if(count($salesList) == ($i+1) ){
  			$comba = "";
  		}
  		$yearText = substr(  strip_tags(preg_replace("/\s+/", "", $dayList[1]) ) , 0, 4);
        print_r( " ['". sprintf("%04s",   $yearText+$yearNum ) .".12',  ". tagReplace(  $salesList[$i]  ) ." ,   
        		 ". tagReplace(  $operatingList[$i]  ) ."   ]".$comba."");
        $yearNum++;

  }

  ?>         
     	 ]);
        var view = new google.visualization.DataView(data);
        view.setColumns([0, 1,

                         { calc: "stringify",
                           sourceColumn: 1,
                           type: "string",
                           role: "annotation" },
                           2,                   { calc: "stringify",
            sourceColumn: 2,
            type: "string",
            color: 'red' ,
            role: "annotation" }
                         ]);
        
	    var options = {
	   //   title : ' [ 안정성 ] ',
	      hAxis: {showTextEvery:3, maxAlternation:4,maxTextLines:1,textStyle:{fontSize: '9'}},
	      vAxes: {0: {logScale: false}, 1: {logScale: false}},
	      series:{0:{type: "bars", targetAxisIndex:0}, 1:{type: "line", targetAxisIndex:1}},
	      legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
		    //  isStacked: true,
	      chartArea: {  top:"20%" ,left :"2%" ,right: "2%"},
		      
	   //  legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
	      seriesType: 'bars',  
	        bar: {groupWidth: "100%"}, 
	      series: {2: {type: 'line' ,color: 'red'}} //,color: 'red' 
	
	 
	    };
	    var chart = new google.visualization.ComboChart(document.getElementById('chart_div_stability'));
	    chart.draw(view, options); 
  	}  


   //--------------------------배당-------------------//   
   <?php   
   
  
   ?>
     google.charts.setOnLoadCallback(allocationDrawVisualization);  
        
     function allocationDrawVisualization() {
          // 배당 성향 =주당순이익(개별) /주당배당금
          var data = google.visualization.arrayToDataTable([
           ['날짜', '시가배당율', '배당성향'   ],
    <?php 
    $dayList =  tagListData('th', $allocation_List [0]); //날짜
    $salesList =  tagListData('td', $allocation_List [7]); //순이익율
    $perList =  tagListData('td', $allocation_List [1]); //주당순이익(개별)
    $operatingList =  tagListData('td', $allocation_List [6]); //주당배당금

    $comba = ",";
    $yearNum = 0;
    for($i=1;$i<count($salesList);$i++)
    { 
    	
    	if(tagReplace($perList[$i])  <  0.01 ||  tagReplace($operatingList[$i])  <    0.01 ){
    		$operatingPuList = 0;
    	}else{
    		$operatingPuList = tagReplace($perList[$i]) /  tagReplace($operatingList[$i]); // 배당율
    	}
    		 
    		if(count($salesList) == ($i+1) ){
    			$comba = "";
    		}
    		$yearText =    "20". substr(   tagReplace(  $dayList[$i]) ,0,5)   ; 
          print_r( " ['". $yearText ."' ,  ".  tagReplace(  $salesList[$i] ) ." ,   
          		 ".tagReplace (  $operatingPuList ) ."   ]".$comba."");
          $yearNum++;

    }

    ?>         
       	 ]);
          var view = new google.visualization.DataView(data);
          view.setColumns([0, 1,

                           { calc: "stringify",
                             sourceColumn: 1,
                             type: "string",
                             role: "annotation" },
                             2,                   { calc: "stringify",
              sourceColumn: 2,
              type: "string",
              color: 'red' ,
              role: "annotation" }
                           ]);
          
  	    var options = {
  	  //    title : ' [ 배당 ] ',
	      hAxis: {showTextEvery:3, maxAlternation:4,maxTextLines:1,textStyle:{fontSize: '9'}},
	      vAxes: {0: {logScale: false}, 1: {logScale: false}},
	      series:{0:{type: "bars", targetAxisIndex:0}, 1:{type: "line", targetAxisIndex:1}},
	      legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
	    //  isStacked: true,
    	  chartArea: {  top:"20%" ,left :"2%" ,right: "2%"},
		      
	   //  legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
	      seriesType: 'bars',  
	        bar: {groupWidth: "100%"}, 
	      series: {2: {type: 'line' ,color: 'red'}} //,color: 'red' 


	      	
  	 
  	    };
  	    var chart = new google.visualization.ComboChart(document.getElementById('chart_div_allocation'));
  	    chart.draw(view, options); 
    	}   	



     //--------------------------기업가치-------------------//
       google.charts.setOnLoadCallback(valueDrawVisualization);  
          
       function valueDrawVisualization() {
            // 부채총계 /자본총계 = 부채비율, 순이익/매출액 = 순이익률
            var data = google.visualization.arrayToDataTable([
             ['날짜', 'roe', 'per' , 'pbr'   ],
      <?php 
    $dayList =  tagListData('th', $allocation_List [0]); //날짜
    $roeList =  tagListData('td', $allocation_List [8]); //roe
    $perList =  tagListData('td', $allocation_List [3]); //per
    $pbrList =  tagListData('td', $allocation_List [5]); //pbr
      $comba = ",";
      $yearNum = 0;
      for($i=1;$i<count($salesList);$i++)
      { 
      		if(count($salesList) == ($i+1) ){
      			$comba = "";
      		}
    		$yearText =  "20".substr(   tagReplace(  $dayList[$i]) ,0,5)   ; 
            print_r( " ['". $yearText ."',  ".  tagReplace(   $roeList[$i]) ." ,".   tagReplace(   $perList[$i])   ." ,   
            		 ".   tagReplace(   $pbrList[$i])  ."   ]".$comba."");
            $yearNum++;

      }

      ?>         
         	 ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,

                             { calc: "stringify",
                               sourceColumn: 1,
                               type: "string",
                               role: "annotation" },
                               2,                   { calc: "stringify",
                sourceColumn: 2,
                type: "string",
                color: 'red' ,
                role: "annotation" },3
                             ]);
            
    	    var options = {
    	   //   title : ' [ 기업가치 ] ',
    	      hAxis: {showTextEvery:3, maxAlternation:4,maxTextLines:1,textStyle:{fontSize: '9'}},
    	      vAxes: {0: {logScale: false}, 1: {logScale: false}},
    	      series:{0:{type: "bars", targetAxisIndex:0}, 1:{type: "line", targetAxisIndex:1}},
    	      legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
    		    //  isStacked: true,
    	      chartArea: {  top:"20%" ,left :"2%" ,right: "2%"},
    		      
    	   //  legend: {position: 'top', textStyle: {fontSize: 12}, maxLines:3}, 
    	      seriesType: 'bars',  
    	        bar: {groupWidth: "100%"}, 
    	      series: {2: {type: 'line' ,color: 'red'}} //,color: 'red' 
    	
    	 
    	    };
    	    var chart = new google.visualization.ComboChart(document.getElementById('chart_div_value'));
    	    chart.draw(view, options); 
      	}     		
    </script>
<p> <table><tr><td width="50%">  <a href="https://m.finance.daum.net/quotes/A<?php echo  $isNo  ?>/home"  target="_blank"> 
    카카오 증권시세 </a>
   </td><td width="50%" >
    <a href="http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=<?php echo  $isNo  ?>&finGubun=MAIN"  target="_blank"> 
    Daum 재무정보</a>     </td><td></table>
<!-- <img src="http://imgfinance.naver.net/chart/mobile/area/year10/<?php echo $isNo?>_end.png" style="width: 100%; "> -->
 <?php //다음 광고 
		include($mainRoot  ."/stock/include/AD_320_50.php"); 	   
?>
 [1년 주가 현황] <br>
  <img src="http://imgfinance.naver.net/chart/mobile/area/year/<?php echo $isNo?>_end.png" style="width: 100%; "> 
<!--   [일별 주가 현황] <br>
   <img src="http://imgfinance.naver.net/chart/mobile/candle/day/<?php echo $isNo?>_end.png" style="width: 100%; ">-->

<br>
    <div>   <?php print_r($stockInfo);?> </div>
<?php //다음 광고 
include("./include/AD_320_100.php"); 
?>
<!-- 
[수익성] <br>
<div id="chart_div" style="width: 100%; height: 300px;" ></div>  </p>
<p>[이익율]  
<div id="chart_div_finance" style="width: 100%; height: 300px;"  ></div>  </p> 
<p>[안정성]    
<div id="chart_div_stability" style="width: 100%; height: 300px;"></div></p>  -->
<p>[배당]   
<div id="chart_div_allocation" style="width: 100%; height: 300px;"></div></p> 
<p>[기업가치]  
<div id="chart_div_value" style="width: 100%; height: 300px;"></div>   </p> 

   
 </div>
 
   
 <!-- menu_left -->
<?php 

if("C" == $siteType){
	include("./recommendEps/include/menu_left.php");
}else{
	include("./include/menu_left.php");
}
?>
 <!-- footer -->
<?php 
include("./include/footer.php");?>

<!-- search panel -->   
<?php 
include("./include/menu_search.php");?> 
                
</div> 
</body>
</html>
 
 