<?php
header('Content-Type: text/html;charset=utf-8'); //header()는 로 캐릭터셋
//DB접속
include("../include/accsesDB.php");
?>
 
<?
$is_Insert = "Y"; // 데이터 저장 여부
  
 
$Pushday =date("Ymd",strtotime("-1 day"));
$todayWa = date("w"); // 요일 
 
 
//--------------------------------------
//경기도 push 전송
//--------------------------------------

$query = "SELECT city_key ,max(school_nm), count(*) FROM school_gyeonggi_do WHERE    key_date > ".  $Pushday." and board_type ='방과후강사' group by city_key";
$res = mysql_query($query, $conn);

if(mysqli_num_rows ($res)){
	while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) { // push 발송
		list( $resultDtlsCd , $resultDtlsComNm , $resultCdVal ) = syCoDetail("SH0001" , "" , $row[0] );
 
    $cityNm = $resultDtlsComNm;
    
		$push_query  = "SELECT a.Token , a.fcm_id , service_key  FROM  school_fcm_users a, school_fcm_yn  b WHERE a.fcm_id =  b.fcm_id   AND a.use_yn ='Y' AND b.send_yn='Y' AND  service_key  = '".  $cityNm."'   ";
		//$push_query  = "SELECT a.Token , a.fcm_id , service_key  FROM  school_fcm_users a, school_fcm_yn  b WHERE a.fcm_id =  b.fcm_id   AND a.use_yn ='Y' AND b.send_yn='Y' AND  service_key  = '수원'  ";
		$push_res  = mysql_query($push_query , $conn);

		if(mysqli_num_rows ($push_res)){
 
			unset($registrationIds );
				
			while ($push_row = mysqli_fetch_array($push_res, MYSQLI_BOTH)) {
				$registrationIds[]  = $push_row[0] ;

			}
			//--------------------------------------push 전송------------------
			echo  count($registrationIds )."=====".$row[0] ."<bR>" ;
			$headers = array(
					'Content-Type:application/json',
					'Authorization:key=AAAAoX5W2wE:APA91bE2pjzct_T-WO-WtYeydocp58Ayym3hkHoVrF3XErToY-YjXonEm2Wirs2Gqu__-OObOA_gnkMasDzf8tPeFsRlofK2rgdJ1MNM8jK6QnGcE3xUSavS2s48Os0CmUnuxnE1b1sx'
			);

			$msg = array
			(
					"body" =>  ' 경기도 '.$cityNm.'시  ['.$row[1] .' 외'.$row[2] .'건 ] 강사 구인 공고요청.',
					"title" => '방과후강사 공고'
					//"sound" => "mySound"
			);

			$data = array
			(

					"forwordUrl" => "https://cronya.pe.kr/school/schoolAdSend.php?url=https://cronya.pe.kr/school/schoolList.php?type=".$row[0]
					//	,"M_view" => $M_view
			);

			$fields = array
			(
					'registration_ids' => $registrationIds,
					'notification' => $msg,
					'data' => $data
			);



			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
			curl_close( $ch );


			// 푸시 전송 결과 반환.
			$obj = json_decode($response);

			// 푸시 전송시 성공 수량 반환.
			$cnt = $obj->{"success"};
			echo $cnt;
			//--------------------------------------push 전송------------------ 
		}
	}
}

 
 

?> 
 