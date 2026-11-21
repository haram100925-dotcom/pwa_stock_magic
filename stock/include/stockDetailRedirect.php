<?
$isNo = $_REQUEST['isNo'];
$isNm = rawurldecode($_REQUEST['isNm']); 
?>
<script> 
//상세
 
parent.stockDitalMove('<?echo $isNo ?>' ,'<?echo $isNm ?>') ;
 
</script> 
  
       