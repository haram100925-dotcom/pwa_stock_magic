<?php 
include("/hosting/windmoter/html/stock/include/common.php");
include($mainRoot  ."/stock/include/accsesDB.php");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache");


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
        <title>종목 발굴</title>
        <meta name="description" content="종목발굴무료">
        <meta name="robots" content="index,follow">
         <link rel="canonical" href="http://https://cronya.pe.kr/stock/recommendEps/main.php">
        <meta property="og:title" content="종목발굴무료">
		<meta property="og:description" content="마법공식,가치투자,이기는투자,종목발굴,주식,외국인지분상위,영업이익율,자기자본이익률,PER,ROA,ROE">
		<meta property="og:image" content="https://cronya.pe.kr/img/2_322x75.png>
		<meta property="og:url" content="https://cronya.pe.kr/">
        <link rel="canonical" href="https://cronya.pe.kr/stock/main.php">
        
        <link rel="stylesheet" href="https://demos.jquerymobile.com/1.4.5/css/themes/default/jquery.mobile-1.4.5.min.css">
        <link rel="stylesheet" href="https://cronya.pe.kr/css/jqm-demos1_4_5.css">
        <link rel="shortcut icon" href="https://cronya.pe.kr/img/ic_launcher.png">
  <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
        <script src="https://demos.jquerymobile.com/1.4.5/js/jquery.js"></script>
        <script src="https://demos.jquerymobile.com/1.4.5/_assets/js/index.js"></script>
        <script src="https://demos.jquerymobile.com/1.4.5/js/jquery.mobile-1.4.5.min.js"></script>         
        <script type="text/javascript" src="//t1.daumcdn.net/adfit/static/ad.min.js"></script>  
</head>
<body>
<div data-role="page" class="jqm-demos" data-quicklinks="true">
<script> 
//광고  hash 에서 삭제
  $( ".jqm-demos.ui-page.ui-page-theme-a.ui-page-footer-fixed" ).find( "ins" ).remove() ;
</script>
    <div data-role="header" class="jqm-header"  style="background-color:#f9f7f8 "><!-- 1_336x75.png , 2_322x75.png-->
    
                <h2><a href="https://cronya.pe.kr/stock/recommendEps/main.php" title="stock magic" data-ajax="false"><img src="/img/2_322x75.png" alt="stock"></a></h2>
                <p>UPDATE DAY : <?php echo $updateViewDay?> 일 </p>
        <a href="#" class="jqm-navmenu-link ui-btn ui-btn-icon-notext ui-corner-all ui-icon-bars ui-nodisc-icon ui-alt-icon ui-btn-left">Menu</a> 
<!--         <a href= "javascript:window.location.reload(true);" class="ui-btn ui-btn-icon-notext ui-corner-all ui-icon-refresh ui-nodisc-icon ui-alt-icon ui-btn-right">Search</a>  
        <a href= "javascript:window.history.back();" class="ui-btn ui-btn-icon-notext ui-corner-all ui-icon-back ui-nodisc-icon ui-alt-icon ui-btn-right">Search</a>-->
           <a href="#" class="jqm-search-link ui-nodisc-icon ui-alt-icon ui-btn-right ui-btn ui-icon-search ui-btn-icon-notext ui-corner-all" data-role="button" role="button">Search</a> 
    </div><!-- /header -->