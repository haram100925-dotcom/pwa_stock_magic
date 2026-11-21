<?php
header('Content-Type: text/html;charset=utf-8'); //header() 
//DB접속
include("./include/accsesDB.php");

include("./include/header.php"); 
?> 

<!-- content -->
    <div role="main" class="ui-content jqm-content">
                <h1>주요항목</h1> 
    
   </p> 
             
                     <b>2016-09-16 :</b> 마법공식 오픈.<Br>
				     <b>2016-10-01 :</b> push 오픈.<Br>
				     <b>2016-10-07 :</b>벤저민 그레이엄(가치투자) 오픈.<Br> 
				     <b>2016-10-07 :</b>피터 린치(이기는 투자).<Br> 
				     <b>2018-03-07 :</b>종목발굴 오픈.<Br>
				     <b>2018-04-07 :</b>투자결과 오픈.<Br> 
				      <Br><Br><Br>
				     <b>종목발굴 APP 오픈 예정 
				  <BR> <BR>    
				       ※ Stock Magic 아이폰&웹 접속 URL※
      			<BR> 
				      <input id="myInput"    readonly="readonly"  value=" https://cronya.pe.kr/">
				<button onclick="copy_to_clipboard()">클립보드로 복사</button>
				<a href="https://www.jamongpick.com/47/34098">
				성공투자를 위한 180여개의 항목 중 자신에게 적합한 항목을 선택 및 요청할 수 있고
핵심 포트폴리오, 전문가 매매전략, 핵심 포인트 시크릿 노트 요청자에게 무료로 제공해 드립니다.</a>
				<script>
				function copy_to_clipboard() {
				  var copyText = document.getElementById("myInput");
				  copyText.select();
				  document.execCommand("Copy");
				  alert("복사되었습니다. 웹브라우져 실행 후 URL로 접속해주세요.")
				}
				</script> 
				 <iframe frameBorder='0' scrolling='no' src='./include/amcharts.html' style='width:100%; height:100%; min-height:300px;'></iframe>
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

<?php 
//공지사항
include("./include/notic.php");

?> 
<!-- 

-- 클릭몬 광고
<script type="text/javascript">  
(function(cl,i,c,k,m,o,n){m=cl.location.protocol+c;o=cl.referrer;m+='&mon_rf='+encodeURIComponent(o);
n='<'+i+' type="text/javascript" src="'+m+'"></'+i+'>';cl.writeln(n);
})(document,'script','//mtab.clickmon.co.kr/pop/wp_m_pop.php?PopAd=CM_M_1003067%7C%5E%7CCM_A_1039443%7C%5E%7CAdver_M_1003115&iveiw=no');
  </script> -->
 
</body>
</html> 
