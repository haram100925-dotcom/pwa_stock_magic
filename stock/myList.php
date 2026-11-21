<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
 
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? ''); 
include("/hosting/windmoter/html/stock/include/header.php");
 
$_TOKEN = $_REQUEST["token"] ?? ''; 
$_TOKEN_FCM_ID = ""; // 토큰 키 값
$rowcount = 0; // 미리 0으로 만들어 둡니다.
 
if(strlen($_TOKEN)  > 0){ // 앱 인경우만

	// 1. 데이터베이스에서  계정 알리미 설정 정보가져오기
 
	$query = " SELECT  a.fcm_id , b.isu_nm ,b.isu_cd ,b.in_pr  ,b.target_pr  ,b.memo   , b.send_yn , DATE_FORMAT(b.create_dt , '%Y %c/%e %H') ,b.id
	FROM  stock_fcm_users a   JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id  
	WHERE   a.use_yn ='Y' AND Token ='". $_TOKEN."'"; 
 
	$res  = mysql_query($query , $conn);
	if($res){
		$rowcount=mysqli_num_rows ($res);  
	}
} 

 
?>   
 

  
<script type="text/javascript">
<!--
 
var appBainding = "N"; // 앱에서 호출 여부 체크
$(function(){
    try{
      window.HybridApp.requestData();
    }catch(e){
    }
    var broswerInfo = navigator.userAgent; 
   // alert("<?php echo $_TOKEN;?>");
 
    setTimeout(function() { // 앱체크
      if( appBainding =="N"  && broswerInfo.indexOf("Version/")>-1){ // 앱인경우
        
        getAndroidData(0);
      }else if( appBainding =="N" ) {
        	document.getElementById('verUp').style.display = "";
       document.getElementById('verUp').innerHTML =  " <a href='#'> (안드로이드 앱에서만 지원 가능합니다. )        <br> Google Play 실행 후 '마법공식' 검색    <br> APP 설치 후 접속 </a>"
       +"<Br><Br><a   target='_blank' href='https://play.google.com/store/apps/details?id=web.stock.com.stock&hl=ko'>  ▶▷ 마법공식Google Play store 이동  </a>";
      }else{
       // 정상
      }
    }, 500);
});
 
 
// app에서 버전 정보 보냄 
function getAndroidData(data){   
    appBainding ="Y";
    if(parseFloat(data) <  1.11 || "" == "<?php echo $_TOKEN;?>"  ){   
 	 	document.getElementById('verUp').style.display = "";
        if(parseFloat(data) == 0){
        	document.getElementById('verUp').innerHTML =  " <a href='#'> (하위 버전으로 '제거' 후 재설치 해야 알림을 받을수있습니다. )<br> Google Play APP .실행 후 '마법공식' 검색 후 설치</a> ";
        }else{
        	document.getElementById('verUp').innerHTML = "<a   target='_blank' href='market://details?id=web.stock.com.stock'>  >Google Play 이동 ('제거' 후 재설치 해야 알림을 받을수있습니다.) </a>";
        }
    }
  
 } 
 
 //글자수제한
 $(document).ready(function() {
   
    $("[id^='memo']").on('keyup', function() {
        if($(this).val().length > 100) {
            $(this).val($(this).val().substring(0, 100));
        }
    });
        // #숫자만입력
   $("[id^='target_pr']").on("keyup", function() {
     
         var regNumber = /^[0-9]*$/;
          var temp =$(this).val();
          if(!regNumber.test(temp))
          { 
              $(this).val(temp.replace(/[^0-9]/g,""));
          } 
   });
});

 
//-->
</script> 
<script> 
//상세
function stockDitalMove(isNo , isNm) { 

	location.href="<? echo $hostUrl ?>/stock/stockDetail.php?isNo="+isNo+"&isNm="+isNm;
}
//결과이동
function myInsertSend(id , dataType) {  
  var sendForm = document.sendForm; 
  if("up" == dataType && ($("#target_pr"+id).val() ==""  || $("#target_pr"+id).val() < 10)){
    alert("알리미 목표가를 입력해주세요.");
  }else{
     $("#id").val(id) ; 
     $("#dataType").val(dataType) ; 
     
    if("del" == dataType){
      if (confirm("관심종목을  삭제하시겠습니까?") == true){    //확인
        sendForm.submit();
      }else{   //취소      
          return;      
      }
    }else if("up" == dataType){ 
        if($("#send_yn"+id).is(":checked") )     { 
          $("#send_yn"+id).val("Y") ;
        }else{
          $("#send_yn"+id).val("N") ;        
        }
        sendForm.submit();
    }
  }
}  
 
</script>  

<!-- content -->
<div role="main" class="ui-content jqm-content">
                <h3><b style='color:red'>나의 </B> 관심종목( 알리미) <a href="#popupCloseRight" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline">!</a></h3>
      <a href= "javascript:window.location.reload(true);" class="ui-btn ui-btn-icon-notext ui-corner-all ui-icon-refresh ui-nodisc-icon ui-alt-icon ui-btn-right">Search</a> 
				 <!--  안내표시 -->
				<div data-role="popup" id="popupCloseRight" class="ui-content" style="max-width:380px">
				<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
				<p><h3>※ 나의 관심종목 (알리미) ※</h3> <br> 
				- 나의 관심종목은 앱 삭제 시 소멸 됩니다. <br>
				 ( 개인 정보법에 의한 개인정보 취득 하지 않습니다.) <br> <br>
				- 알림가 알리미를 설정 <br> <br>
				- 알리미는  토,일 제외 09~16 시평일 5분단위 <br> 알림가달성 시 알리미 발송<br> <br>

				- 관심종목 메모는 100글자까지  저장가능합니다.<br> <br>
				- 최대 10개 까지 담을수 있습니다. 
				</div>     
 
	<p id="verUp" style="display: none">
	<a   target="_blank" href="https://play.google.com/store/apps/details?id=web.stock.com.stock&hl=ko">  
	>Google Play 이동 (하위 버전으로업데이트 해주세요) </a> </p>		
		
<form name="sendForm" action="./include/myInsert.php"  method="post">
<input type="hidden" name="id" id="id" value="">  
<input type="hidden" name="dataType" id="dataType" value="">  

<?
	include($mainRoot  ."/stock/include/AD_320_50.php"); 
	if($rowcount  > 0){	 
	  while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {	 
//현재 가격 -네이버 nv": 6210, 현재가격
$urlStk ="http://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:".$row[2]."|SERVICE_RECENT_ITEM:".$row[2]."&_callback=window.__jindo2_callback._3868"; 
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
//담기 시점 상승율
	$super = 0;
	if(  $priceStock > 0 ){
		$super = (( $priceStock  / $row[3] )-1) * 100;
	}
 	$superColor = "red";
 	$superInfo = "▲";
	if($super <0 ){
		$superColor = "blue";
		$superInfo = "▼";
	}
//목표가 시점 상승율
	$reper = 0;
	if(  $priceStock > 0 ){
		$reper = ((   $row[4] / $priceStock )-1) * 100;
	}
 	$reperColor = "red";
 	$reperInfo = "▲";
	if($reper <0 ){
		$reperColor = "blue";
		$reperInfo = "▼";
	}	
?>	 
<fieldset data-role="collapsible">
<legend> <?echo $row[1]?>  <!--(설정가 : <b style='color:blue'> <?echo number_format($row[4])   ?> </B> 원) --><br>
 <?echo $row[7]?> ( <?echo number_format($row[3])   ?>원)  <br> →<b style='color:<?php echo$superColor?>'> <?echo $superInfo?><?echo   sprintf("%2.2f" , $super );  ?></B>%  현 주가 
<?echo  number_format($priceStock ) ?> 원  </legend>
    
 			<h2>알리미 설정 </h2> 
 			 			<h3>알림가 <b style='color:blue'> <?echo number_format($row[4])   ?> </B> 원 <b style='color:<?php echo$reperColor?>'> <?echo $reperInfo?><?echo   sprintf("%2.2f" , $reper );  ?></B>%  </h3> 
				<div class="ui-field-contain">
		         <label for="name">목표가(원) :</label>
		         <input type="text" name="target_pr<?echo $row[8]?>" id="target_pr<?echo $row[8]?>" value="<? if( $row[4] > 0 ) echo $row[4]; ?>" numberOnly> 
				</div>

				<div class="ui-field-contain">
				<label for="textarea">메모:</label>
				<textarea cols="40" rows="8" name="memo<?echo $row[8]?>" id="memo<?echo $row[8]?>"><?echo $row[5]?> </textarea>
				</div>

				<div class="ui-field-contain">
					<label for="slider2">알리미 설정:</label>
          <input type="checkbox" data-role="flipswitch" name="send_yn<?echo $row[8]?>" id="send_yn<?echo $row[8]?>" 
          <?  if( $row[6] == "Y" ) { echo"checked  value='Y'"; }else{ echo " value='N'"; }?> >
				</div>  

				<a href="javascript:myInsertSend( '<?echo $row[8]?>' ,'up')" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-inline"  > 설정 </a>
			  <a href="javascript:myInsertSend( '<?echo $row[8]?>' ,'del')" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-inline"  > 관심종목 삭제 </a>
</fieldset>
<Br>
   
 <?  
    }
 } else{
 ?>
         <div class="ui-grid-a ui-responsive">
        	<div class="ui-block-a">
        		<div class="jqm-block-content">
        			<h3><b style='color:red'>나의 관심종목이 없습니다.</B></h3>	
        			
              <p> 
               종목상세 > 관심종목 등록주세요.<br>
               <a target='_blank'  href="https://cronya.pe.kr/stock/stockDetail.php?isNo=005930&isNm=%EC%82%BC%EC%84%B1%EC%A0%84%EC%9E%90">
               ▶ 삼성전자 담기</a><br>
               최대 10개 까지 담을수 있습니다. </p> 
        		</div>
        	</div>
       </div>

 <?  
 }
	include($mainRoot  ."/stock/include/AD_320_100.php"); 	
?>	   
</div>       
</form> 
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

