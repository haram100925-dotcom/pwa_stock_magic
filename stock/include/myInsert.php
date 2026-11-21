<?php
header('Content-Type: text/html;charset=utf-8'); //header() 

//DB접속
include("/hosting/windmoter/html/stock/include/accsesDB.php");
 
$dataType = $_POST['dataType'];
$id = $_POST['id'];
$target_pr = $_POST['target_pr'.$id];
$send_yn = $_POST['send_yn'.$id];
if (empty($send_yn)) {
  $send_yn ="N";
} 
$memo = $_POST['memo'.$id];
//등록
$fcm_id = $_POST['fcm_id'];
$isu_cd   = $_POST['isu_cd'];  
$in_pr    = 0;

$isReult = "N";
 
	if("up" == $dataType){
	  $query = "UPDATE stock_fcm_push 
    	SET	
    	target_pr = '$target_pr' , 
    	memo = '$memo' , 
    	send_yn = '$send_yn' ,   
    	update_dt =  NOW() 
    	
    	WHERE
    	id = '". $id ."'" ;
  }else if("del" == $dataType){
    $query = "
      DELETE FROM stock_fcm_push 
  	    WHERE
  		id = '". $id ."'" ;
  }else if("in" == $dataType){  
//이미 관심종목등록, 등록 수10개 넘었는지
 
  $isInsert = "Y";
	$query = "select  key_date  from stock_fcm_push WHERE fcm_id = '". $fcm_id ."'  AND isu_cd = '". $isu_cd ."' " ;
	$res = mysql_query($query, $conn);

	if($res){
			$rowcount=mysqli_num_rows ($res);  
		if(rowcount > 0){ 
			$resultMsg = " 이미 저장된 종목입니다.";
			$isInsert = "N";
		} 
	}
	$query = "select  fcm_id  from stock_fcm_push WHERE fcm_id = '". $fcm_id ."' " ;
	$res = mysql_query($query, $conn);

	if($res){
			$rowcount=mysqli_num_rows ($res);  
			 
		if($rowcount > 9){ 
			$resultMsg = "관심종목은 최대10개 등록가능합니다.";
		  $isInsert = "N";
		} 
	}	 
  if($isInsert == "Y"){
    //현재 가격 -네이버 nv": 6210, 현재가격 
    $urlStk ="http://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:".$isu_cd."|SERVICE_RECENT_ITEM:".$isu_cd."&_callback=window.__jindo2_callback._3868";
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
    $in_pr = $result_array ->result->areas[0]->datas[0]->nv ; //현재가격
    $isu_nm = $result_array ->result->areas[0]->datas[0]->nm ; //종목명
    
    
         
        $query = "INSERT INTO stock_fcm_push 
        	(  
        	fcm_id, 
        	isu_cd, 
        	isu_nm, 
        	in_pr, 
        	target_pr, 
        	memo, 
        	send_yn,   
        	update_dt
        	)
        	VALUES
        	( 
        	'$fcm_id', 
        	'$isu_cd', 
        	'$isu_nm', 
        	'$in_pr', 
        	'0', 
        	'', 
        	'N',  
        	NOW() 
        	)";
    }
  }    
 	$res = mysql_query($query, $conn);
	if($res ) $isReult ="Y";
 
?>   
 

  
<script type="text/javascript">
<!--
<? if (  $isReult=="Y"){
	if("up" == $dataType){ ?>
	  alert(" 알리미 설정이 변경 되었습니다.");
<? 	}else if("del" == $dataType){ ?>

<?  }else if("in" == $dataType){   	 
      if(  $resultMsg !=""){
      echo "alert('".$resultMsg."');";
      }else{
     ?>    
    alert(" 관심종목으로 등록 되었습니다.");  
  <?  }
    }
}else{
  if("in" == $dataType && $resultMsg !=""){
    echo "alert('".$resultMsg."');";
  } else{?>
  alert(" 변경 중 에러가 발생하였습니다.");
<?}
}  ?> 
location.href="/stock/myList.php";
//-->
</script>  
