
  			

 <?php
  
//전종목 검색 시 html 떨굴 데이터
include("./accsesDB.php"); 
 
                  	$query = "select market_gubun, isu_cd ,isu_nm  from stock_valuations"  ;
                  	$res = mysql_query($query, $conn);
                  	
                  	if($res){
       
                      $rowCount = 1;
                      while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) { 

                  ?>                   
                  <li onclick="parent.stockDitalMove('<?echo $row[1]?>' ,'<?echo rawurlencode($row[2])?>')" style="cursor:pointer">
                     <table width="100%"  >
                   
                      <tbody>
        
                  <tr> 
                  <td   ><?if($row[0] =="KSQ" ){  echo "<b style='color:red'>코스피</B>";
                  }else if($row[0] =="STK"){ echo "<b style='color:blue'>코스닥</B>"; 
                  }else{ echo "<b style='color:blue'>코낵스</B>"; 
                  }?></td>
                  <td   width="70%" ><?echo $row[2]?></td> 
                  </tr>
        
                  		</tbody>
                     </table>	 
                 </li>
                  <?
                      $rowCount++;
                     }
                  }?>  
                  
              