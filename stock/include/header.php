<?php 
include("/hosting/windmoter/html/stock/include/common.php");
include($mainRoot  ."/stock/include/accsesDB.php");



$query = "select  key_date  from stock_Insert_history order by  key_date  desc limit 1";

$res = mysql_query($query, $conn);
$updateDay =  date("Ymd");
if($res){
	$row = mysqli_fetch_assoc($res);
	$updateDay = $row["key_date"];
 
}
$updateViewDay =date( "Y-m-d", strtotime( $updateDay ) );
?>
<!DOCTYPE html>
<html>
<head>
       
         <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="naver-site-verification" content="5b2aef2d1ca41fd28a5b624046cbb2d2cf4b8c03"/>
        <title>신 마법공식 계산기 및 백테스트 결과 | 크로냐</title>
        
        <meta name="description" content="신 마법공식 계산기, 백테스트 결과, PBR·GP/A 기반 순위, 코스피·코스닥 상위 100, 가치투자 전략을 한 눈에 확인하세요. 크로냐에서 검증된 데이터로 종목을 탐색하세요.">
        <meta name="robots" content="index,follow">
        <meta property="og:type" content="website">
		<meta property="og:title" content="신 마법공식 계산기 및 백테스트 결과 | 크로냐">
		<meta property="og:description" content="신 마법공식 상위 100, PBR↓·GP/A↑ 기반 순위와 백테스트 결과를 확인하세요. 코스피·코스닥 가치투자 전략 제공.">
		<meta property="og:image" content="<? echo $hostUrl ?>/img/1_336x75.png">
		<meta property="og:url" content="<? echo $hostUrl ?>/stock/main.php">
        <meta property="og:locale" content="ko_KR">
        
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="신 마법공식 계산기 및 백테스트 결과 | 크로냐">
        <meta name="twitter:description" content="PBR·GP/A 기반 신 마법공식 상위 100과 백테스트 결과를 제공합니다.">
        <meta name="twitter:image" content="<? echo $hostUrl ?>/img/1_336x75.png">
        
        <meta name="author" content="크로냐 (Cronya)">
        <meta name="theme-color" content="#ffffff">
        <link rel="canonical" href="<? echo $hostUrl ?>/stock/main.php">
        
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "WebSite",
          "name": "크로냐 | 신 마법공식",
          "url": "<? echo $hostUrl ?>/stock/main.php",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "<? echo $hostUrl ?>/stock/include/stockTotalList.php?q={search_term}",
            "query-input": "required name=search_term"
          }
        }
        </script>
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Organization",
          "name": "크로냐 (Cronya)",
          "url": "<? echo $hostUrl ?>",
          "logo": "<? echo $hostUrl ?>/img/ic_launcher.png"
        }
        </script>
        
        
        <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
        <link rel="stylesheet" href="https://demos.jquerymobile.com/1.4.5/_assets/css/jqm-demos.css">
        <link rel="shortcut icon" href="<? echo $hostUrl ?>/img/ic_launcher.png">
  <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
        <script src="https://demos.jquerymobile.com/1.4.5/js/jquery.js"></script>
        <script src="https://demos.jquerymobile.com/1.4.5/_assets/js/index.js"></script>
        <script src="https://demos.jquerymobile.com/1.4.5/js/jquery.mobile-1.4.5.min.js"></script>      
        <!-- Sheet JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.14.3/xlsx.full.min.js"></script>
        <!--FileSaver savaAs 이용 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script type="text/javascript" src="//t1.daumcdn.net/kas/static/ba.min.js" async></script>
<script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "ca-pub-2281115817010682",
    enable_page_level_ads: true
  }); 
  /*
$( document ).on( "pagecreate", ".jqm-demos ui-page ui-page-theme-a ui-page-footer-fixed", function( event ) {  
$( this).find( ".daum_ddn_area" ).remove();
});*/
</script>
</head>
<body>
<div data-role="page" class="jqm-demos" data-quicklinks="true">
<script> 
//광고  hash 에서 삭제
 $( ".jqm-demos.ui-page.ui-page-theme-a.ui-page-footer-fixed" ).find( "ins" ).remove() ;
</script>
    <div data-role="header" class="jqm-header"><!-- 1_336x75.png , 2_322x75.png-->
    
                <h2><a href="<? echo $hostUrl ?>/stock/main.php" title="stock magic" data-ajax="false"><img src="/img/1_336x75.png" alt="stock"></a></h2>
                <p  >UPDATE DAY : <?php echo $updateViewDay?> 일     </p>
        <a href="#" class="jqm-navmenu-link ui-btn ui-btn-icon-notext ui-corner-all ui-icon-bars ui-nodisc-icon ui-alt-icon ui-btn-left">Menu</a> 
<!--         <a href= "javascript:window.location.reload(true);" class="ui-btn ui-btn-icon-notext ui-corner-all ui-icon-refresh ui-nodisc-icon ui-alt-icon ui-btn-right">Search</a> 
        <a href= "javascript:window.history.back();" class="ui-btn ui-btn-icon-notext ui-corner-all ui-icon-back ui-nodisc-icon ui-alt-icon ui-btn-right">Search</a> -->
      <a href="#" onclick="stockDateTotal()" class="jqm-search-link ui-nodisc-icon ui-alt-icon ui-btn-right ui-btn ui-icon-search ui-btn-icon-notext ui-corner-all" data-role="button" role="button">Search</a> 
    </div><!-- /header -->