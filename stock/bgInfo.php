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
                
</div> 
</body>
</html>

