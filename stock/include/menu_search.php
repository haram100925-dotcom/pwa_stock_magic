<style>
/* General Panel Styling */
.jqm-search-panel {
    background-color: #f9f9f9;
    border-left: 1px solid #ddd;
    box-shadow: -5px 0 15px rgba(0,0,0,0.1);
}

/* Search Input */
.jqm-search .ui-filterable .ui-input-search {
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #ddd;
    box-shadow: none;
    padding: 5px 10px;
}

.jqm-search .ui-input-search input {
    font-size: 16px;
    padding: 10px;
    border: none;
    background: transparent;
}

/* Listview Styling */
#stockViewTotal {
    margin-top: 15px;
}

#stockViewTotal li {
    border-bottom: 1px solid #eee;
}

#stockViewTotal li a {
    color: #333;
    font-weight: 500;
    padding: 15px 12px;
    display: block;
    text-decoration: none;
    transition: background-color 0.3s;
}

#stockViewTotal li a:hover {
    background-color: #f0f0f0;
}

/* Loader */
.loader {
  border: 6px solid #f3f3f3;
  border-top: 6px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1.5s linear infinite;
  margin: 20px auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Footer Info */
.panel-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 15px;
    background-color: #f1f1f1;
    border-top: 1px solid #ddd;
    font-size: 12px;
    color: #555;
    text-align: center;
}

</style>
<script> 

//상세
function stockDitalMove(isNo , isNm) { 

	location.href="https://cronya.pe.kr/stock/stockDetail.php?isNo="+isNo+"&isNm="+isNm;
}

//기본데이터 불러오기
var isStockDateTotal= true;
function stockDateTotal() { 
  if(isStockDateTotal){
   	$.ajax({ 
  	    type: 'post',
  	    dataType: 'text',
  	    url: 'https://cronya.pe.kr/stock/include/stockTotalList.php', 
  	    beforeSend: function() {
            $('#stockViewTotal').html('<li><div class="loader"></div></li>');
        },
  	    success: function (data) { 
  	        $('#stockViewTotal').html(data);
  	        isStockDateTotal =false;
  
  	    },
  	    error: function ( ) {
  	    	alert( "한국거래소 실패.");
  	    	$('#stockViewTotal').html('<li>데이터를 불러오는데 실패했습니다.</li>');
  	    }
  	});
	}
} 

</script> 
<div data-role="panel" class="jqm-search-panel" data-position="right" data-display="overlay" data-theme="a" role="region" aria-label="종목 검색 패널">   
    <div class="jqm-search" style="padding: 10px;">
        <?php // AD Area - You can place your ad banner here if needed ?>
        <label for="stockViewTotal" class="ui-hidden-accessible">종목 검색 결과</label>
        <ul id="stockViewTotal" class="jqm-list ui-listview" data-filter-placeholder="종목 이름 또는 코드로 검색" data-filter-reveal="true" aria-live="polite"> 
            <!-- Stock list will be loaded here by AJAX -->
        </ul>
    </div>

    <div class="panel-footer">
        광고문의: windmoter@gmail.com
    </div>
                           
    <iframe id="iframeSearch" title="종목 검색 프레임" style="display:none; width: 100%; height: 100%; border: none;"></iframe>
</div>