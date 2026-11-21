<?php 
include("/hosting/windmoter/html/stock/include/common.php");
include($mainRoot  ."/stock/include/accsesDB.php");
$token = $_POST["Token"];
//$token = $_REQUEST['Token'];

if(isset($token)){
 

	$query = "INSERT INTO stock_fcm_users(Token ,use_yn,update_dt) Values ('$token','Y', NOW() ) ON DUPLICATE KEY UPDATE Token = '$token' ,use_yn = 'Y' ,update_dt = NOW() ; ";
 
  mysql_query($query, $conn);
}
 


?>erettt  