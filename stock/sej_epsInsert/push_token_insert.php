<?php 
include("/hosting/windmoter/html/stock/include/common.php");
include($mainRoot  ."/stock/include/accsesDB.php");
$token = $_POST["Token"];
//$token = $_REQUEST['Token'];
//$query = "INSERT INTO housRental_fcm_users(Token ,use_yn ) Values ('66666666666666','Y')  ";
  
//mysql_query($query, $conn);
if(isset($token)){
 

	$query = "INSERT INTO stock_fcm_users(Token ,use_yn ) Values ('$token','Y') ON DUPLICATE KEY UPDATE Token = '$token' ,use_yn = 'Y'; ";
 
  mysql_query($query, $conn);
}
 


?>  