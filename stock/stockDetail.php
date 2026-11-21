<?php  
//header('Content-Type: text/html;charset=EUC-KR'); //header()
$siteType = $_REQUEST['siteType'] ?? '';
$isNo = $_REQUEST['isNo']; 
 
$market_gubun_type = rawurldecode($_REQUEST['type'] ?? ''); 
include("/hosting/windmoter/html/stock/include/header.php");
 
$_TOKEN = $_REQUEST["token"] ?? ''; 
$_TOKEN_FCM_ID = ""; // 토큰 키 값
$rowcount= 0;
$fcm_id= "";
$isInsertDate = "N";
if(strlen($_TOKEN)  > 0){ // 앱 인경우만

	// 1. 데이터베이스에서  계정 알리미 설정 정보가져오기
 
	$query = " SELECT  a.fcm_id   ,b.isu_cd
	FROM  stock_fcm_users a LEFT JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id  
	WHERE   a.use_yn ='Y' AND Token ='". $_TOKEN."'"; 
 
	$res  = mysql_query($query , $conn);
	if($res){
		$rowcount=mysqli_num_rows ($res);  
  	if($rowcount > 0){
  	  while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {	
  		  if( $isNo == $row["isu_cd"]  ) $isInsertDate = "Y";
  	    $fcm_id = $row["fcm_id"]; 
  	  }
  	}		
	}
}
/* 
 // 테스트 테블릿 폰
	$query = " SELECT  a.fcm_id   ,b.isu_cd
	FROM  stock_fcm_users a  LEFT JOIN stock_fcm_push  b  	ON  a.fcm_id =  b.fcm_id  
	WHERE   a.use_yn ='Y' AND  a.fcm_id  ='4278'"; 
	$res  = mysql_query($query , $conn);
	if($res){
		$rowcount=mysqli_num_rows ($res);  
	} 
	if($rowcount > 0){
	  while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {	
		  if( $isNo == $row["isu_cd"]  ) $isInsertDate = "Y";
	    $fcm_id = $row["fcm_id"]; 
	  }
	}
 */
// https://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:"..$isNo."|SERVICE_RECENT_ITEM:"..$isNo."&_callback=window.__jindo2_callback._3868
//현재 가격 -네이버 nv": 6210, 현재가격
$urlStk ="https://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:".$isNo."|SERVICE_RECENT_ITEM:".$isNo."&_callback=window.__jindo2_callback._3868";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "https://polling.finance.naver.com");
 

$json = curl_exec($curl_handle); 
//echo $json;
$startNum = ( strrpos ( $json , "("  ) +1 ) ;
$endNum = (strlen( $json)-$startNum-1  );
$json = substr($json , $startNum ,$endNum);
$json = iconv("EUC-KR" ,"UTF-8",$json );
$result_array = json_decode($json);  

//$priceStock = $isNo; //현재가격
$priceStock = $result_array ->result->areas[0]->datas[0]->nv ; //현재가격
$isNm = $result_array ->result->areas[0]->datas[0]->nm ; //종목명
 

// $urlStk ="http://search.itooza.com/search.htm?seName=".urlencode(iconv("UTF-8","EUC-KR",$isNm)) ;
// $curl_handle = curl_init();
// curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
// curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); // 응답 값을 브라우저에 표시하지 말고 값을 리턴
// //curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://search.itooza.com/");

// curl_setopt ($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
// curl_setopt($curl_handle, CURLOPT_REFERER, 'http://search.itooza.com/');
	 
// $json = curl_exec($curl_handle);  
// $script_List = tagListData('table', $json ); 
// $allocation_List = tagListData('tr', $script_List[3]); // 년별 배당
//echo $urlStk;
/*
$urlStk ="http://www.sejongdata.com/business_include_fr/table_main0_bus_01.html?&no=".$isNo;
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT,  "http://www.sejongdata.com/"); 


$json = curl_exec($curl_handle);
$script_List = tagListData('table', $json); 
//print_r($script_List[1]  ."=====");
//print_r($script_List[4]  ."=====");
$profitability_List = tagListData('tr', $script_List[1]); // 년별 매출액 
$finance_List = tagListData('tr', $script_List[4]); // 년별 재무비율
//print_r($finance_List[0]  ."====="); 
*/

// https://wcomp.fnguide.com/CompanyInfo/Snapshot?c_id=AA&menu_type=01&cmp_cd=000270
$cmpCd = str_pad(preg_replace('/\D/', '', $isNo), 6, '0', STR_PAD_LEFT);
$urlStk = "https://wcomp.fnguide.com/CompanyInfo/Snapshot?c_id=AA&menu_type=01&cmp_cd=" . $cmpCd;
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
// 헤더는 제외하고 content 만 받음
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");

$html = curl_exec($curl_handle);
curl_close($curl_handle);

$stockInfo = '';
if ($html) {
	$bizDate = '';
	$bizHeader = '';
	$bizItems = array();
	if (preg_match('/id="bizSummaryDate"[^>]*>\[([^\]]+)\]/', $html, $m)) {
		$bizDate = $m[1];
	}
	if (preg_match('/id="bizSummaryHeader"[^>]*>(.*?)<\/h3>/s', $html, $m)) {
		$bizHeader = trim(preg_replace("/\s+/", " ", str_replace('&nbsp;', ' ', strip_tags($m[1]))));
	}
	if (preg_match('/id="bizSummaryContent"[^>]*>(.*?)<\/ul>/s', $html, $m)) {
		preg_match_all('/<li[^>]*>(.*?)<\/li>/s', $m[1], $items);
		if (!empty($items[1])) {
			foreach ($items[1] as $li) {
				$line = trim(preg_replace("/\s+/", " ", str_replace('&nbsp;', ' ', strip_tags($li))));
				if ($line !== '') {
					$bizItems[] = $line;
				}
			}
		}
	}
	// 각 항목을 줄바꿈(<br>)으로 구분해서 출력
	$parts = array("Business Summary[{$bizDate}]");
	if ($bizHeader !== '') {
		$parts[] = $bizHeader;
	}
	$parts = array_merge($parts, $bizItems);
	$stockInfo = str_replace("작성일:", "정보:", implode("<br>", $parts));
}
  

 

 function tagListData($strTag, $content)
 {
 	$reg =  sprintf("/\<%s[^>]*\>(.*?)\<\/%s\>/is" , $strTag, $strTag) ;
 	preg_match_all($reg , $content, $matches , PREG_PATTERN_ORDER );
 	return $matches[1];
 }
 
 function tagReplace($strTag )
 {
 	$strTag = strip_tags(preg_replace("/\s+/", "",  str_replace("N/A", "0",     str_replace(",", "",   $strTag))  ) );
 	if($strTag =="" ){
 		$strTag = 0;
 	}
 	return $strTag;
 	
 }
 
?> 
 
 <div role="main" class="ui-content jqm-content" >
 <div style="margin: 12px 0; padding: 14px 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #ffffff;">
 	<div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
 		<div style="display:flex; align-items: baseline; gap: 8px; min-width: 180px;">
 			<h2 style="margin: 0; font-size: 20px; line-height: 1.2;"><?php echo $isNm ?></h2>
 			<span style="display:inline-block; padding: 2px 8px; font-size: 12px; color:#374151; background:#f3f4f6; border:1px solid #e5e7eb; border-radius: 999px;">A<?php echo $isNo ?></span>
 		</div>
 		<div style="display:flex; align-items:center; gap: 6px;">
 			<span style="font-size: 14px; color:#6b7280;">현재가</span>
 			<span style="font-size: 22px; font-weight: 700; color:#111827;"><?php echo number_format($priceStock) ?></span>
 			<span style="font-size: 14px; color:#6b7280;">원</span>
 		</div>
 	</div>
 </div>
 <form name="sendForm" action="./include/myInsert.php"  method="post">
<input type="hidden" name="fcm_id" id="fcm_id" value="<?echo $fcm_id?>">  
<input type="hidden" name="isu_cd" id="isu_cd" value="<?echo $isNo?>">  
<input type="hidden" name="isu_nm" id="isu_nm" value="<?echo $isNm?>">    
<input type="hidden" name="dataType" id="dataType" value="in">  
</form> 
<script> 
function mySend() { 
 	location.href="<? echo $hostUrl ?>/stock/myList.php"; 
}
function myInsertSend() {  
  var sendForm = document.sendForm; 
  if( parseInt(<?echo $rowcount?>) < 1   ){
     alert("안드로이드 앱 접속시 이용가능합니다.");
      	location.href="<? echo $hostUrl ?>/stock/myList.php"; 
  }else if( parseInt(<?echo $rowcount?>) > 9  ){
    alert("최대 10개 관심종목 등록 가능합니다.");
     	location.href="<? echo $hostUrl ?>/stock/myList.php"; 
  }else{
 
        sendForm.submit();
 
  }
}  
</script>  
  
<div style="margin: 6px 0; font-size: 12px; color: #6b7280;">
    <a href="https://finance.daum.net/quotes/A<?php echo  $isNo  ?>" target="_blank" style="color:#2563eb; text-decoration:none;">카카오 증권시세</a>
    <span style="margin: 0 6px;">·</span>
    <a href="http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=<?php echo  $isNo  ?>&finGubun=MAIN" target="_blank" style="color:#2563eb; text-decoration:none;">Daum 재무정보</a>
 </div>

<div style="margin-top: 16px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 12px; background: #ffffff;">
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#2563eb;"></span>
        <h3 style="margin:0; font-size:16px;">1년 주가 현황</h3>
    </div>
    <div style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <img src="https://t1.daumcdn.net/media/finance/chart/kr/daumstock/y/A<?php echo $isNo?>.png" style="width: 100%; display:block;">
    </div>
</div>
 

    <div style="margin-top: 16px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 12px; background: #ffffff;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#059669;"></span>
            <h3 style="margin:0; font-size:16px;">기업 정보</h3>
        </div>
        <div style="font-size: 13px; color:#374151; line-height: 1.6;">
            <?php echo $stockInfo; ?>
        </div>
    </div>
<?php //다음 광고 
include("./include/AD_320_100.php"); 

// 다움 증권 크롤러 클래스 로드 (대안/향상된 크롤러 제거)
require_once './daumStock/daum_financial_crawler.php';

// 재무 데이터 크롤링
$crawler = new DaumFinancialCrawler($isNo);
$financialData = $crawler->crawlFinancialData();
?>

    <!-- Google Charts 라이브러리 로드 -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'bar']});
 
        // 재무 데이터 (PHP에서 JavaScript로 전달)
        const financialData = <?php echo json_encode($financialData, JSON_UNESCAPED_UNICODE); ?>;
        // 날짜 정규화/필터 및 포매터
 
        // 날짜 정규화/필터 및 포매터
        const DATE_MODE_ANNUAL = 'ANNUAL';
        const DATE_MODE_QUARTER = 'QUARTER';
        let currentDateMode = DATE_MODE_ANNUAL;

        function cleanDate(raw) {
            if (!raw) return '';
            // 괄호 내용 제거, 슬래시를 점으로, 숫자/점만 유지
            let t = String(raw).replace(/\(.*?\)/g, '');
            t = t.replace(/\//g, '.');
            t = t.replace(/[^0-9.]/g, '');
            // YYYY 또는 YYYY.MM 형태만 허용
            const m = t.match(/^(\d{4})(?:\.(\d{1,2}))?$/);
            if (!m) return '';
            const y = m[1];
            const mo = m[2] ? String(m[2]).padStart(2, '0') : '';
            return mo ? `${y}.${mo}` : y;
        }

        function isQuarterDate(dateText) {
            if (!dateText) return false;
            // 분기: YYYY.MM 이면서 12가 아닌 월(01~11)을 분기로 간주
            const normalized = cleanDate(dateText);
            const m = normalized.match(/^(\d{4})\.(\d{2})$/);
            if (!m) return false;
            const month = parseInt(m[2], 10);
            return month !== null && month >= 1 && month <= 11;
        }

        function isAnnualDate(dateText) {
            if (!dateText) return false;
            // 연간: YYYY 또는 YYYY.12
            const normalized = cleanDate(dateText);
            if (/^\d{4}$/.test(normalized)) return true;
            if (/^\d{4}\.12$/.test(normalized)) return true;
            return false;
        }

        function monthToQuarterLabel(dateText) {
            const normalized = cleanDate(dateText);
            const m = normalized.match(/^(\d{4})(?:\.(\d{1,2}))?$/);
            if (!m) return dateText;
            const year = m[1];
            const month = m[2] ? parseInt(m[2], 10) : null;
            if (!month) return year;
            const q = Math.ceil(month / 3);
            return `${year}.Q${q}`;
        }

        function formatDateForMode(dateText, mode) {
            if (!dateText) return '';
            if (mode === DATE_MODE_ANNUAL) {
                const normalized = cleanDate(dateText);
                const m = normalized.match(/^(\d{4})/);
                return m ? m[1] : dateText;
            }
            // QUARTER
            return monthToQuarterLabel(dateText);
        }

        function filterDataByMode(fd, mode) {
            function filterSeries(dates, series) {
                const outDates = [];
                const outSeries = [];
                for (let i = 0; i < dates.length; i++) {
                    const d = dates[i] || '';
                    const ok = mode === DATE_MODE_ANNUAL ? isAnnualDate(d) : isQuarterDate(d);
                    if (ok) {
                        outDates.push(cleanDate(d));
                        outSeries.push(series[i] ?? 0);
                    }
                }
                return { dates: outDates, values: outSeries };
            }

            const out = JSON.parse(JSON.stringify(fd || {}));
            const blocks = [
                { key: 'enterprise_value', fields: ['per','pbr','roa'] },
                { key: 'dividend', fields: ['cash_dividend_payout_ratio','cash_dividend_yield'] },
                { key: 'stability', fields: ['debt_ratio','net_profit_margin'] },
                { key: 'profitability', fields: ['operating_profit_margin','net_profit_margin'] },
                { key: 'revenue', fields: ['revenue','operating_profit_announced','roe'] }
            ];
            for (const b of blocks) {
                const block = out[b.key];
                if (!block || !Array.isArray(block.dates)) continue;
                const filtered = {};
                for (const f of b.fields) {
                    const r = filterSeries(block.dates, block[f] || []);
                    filtered.dates = r.dates; // overwrite each time, same result
                    filtered[f] = r.values;
                }
                out[b.key] = Object.assign({ dates: filtered.dates || [] }, filtered);
            }
            return out;
        }

        function withHAxis(options) {
            // 공통 축/라벨 가독성 옵션
            options.hAxis = Object.assign({}, options.hAxis || {}, {
                slantedText: true,
                slantedTextAngle: 60,
                showTextEvery: 1,
                textStyle: { fontSize: 10 },
                allowContainerBoundaryTextOverflow: true
            });
            return options;
        }
        
        // 1. 기업가치 그래프
        google.charts.setOnLoadCallback(drawEnterpriseValueChart);
        function drawEnterpriseValueChart() {
            const filtered = filterDataByMode(financialData, currentDateMode);
            if (!filtered.enterprise_value || !filtered.enterprise_value.dates) {
                document.getElementById('chart_enterprise_value').innerHTML = '<p>기업가치 데이터를 불러올 수 없습니다.</p>';
                return;
            }
            
            const data = new google.visualization.DataTable();
            data.addColumn('string', '날짜');
            data.addColumn('number', 'PER(배)');
            data.addColumn('number', 'PBR(배)');
            data.addColumn('number', 'ROA(%)');
            data.addColumn('number', 'ROE(%)');
            
            const dates = filtered.enterprise_value.dates || [];
            const per = filtered.enterprise_value.per || [];
            const pbr = filtered.enterprise_value.pbr || [];
            const roa = filtered.enterprise_value.roa || [];
            const roe = (filtered.enterprise_value && filtered.enterprise_value.roe) || (filtered.revenue && filtered.revenue.roe) || [];
            
            const maxLength = Math.max(dates.length, per.length, pbr.length, roa.length, roe.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(per[i]) || 0,
                    parseFloat(pbr[i]) || 0,
                    parseFloat(roa[i]) || 0,
                    parseFloat(roe[i]) || 0
                ]);
            }
            
            const options = withHAxis({ 
                title: '기업가치 분석',
                titleTextStyle: { fontSize: 14, bold: true },
                hAxis: {title: '날짜'},
                vAxis: {title: '값'},
                series: {
                    0: {type: 'line', color: '#ff7f0e'},   // PER
                    1: {type: 'line', color: '#2ca02c'},   // PBR
                    2: {type: 'line', color: '#1f77b4'},   // ROA
                    3: {type: 'line', color: '#9467bd'}    // ROE
                },
                chartArea: {top: '18%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top', textStyle: {fontSize: 11}},
                height: 400
            });
            
            const chart = new google.visualization.ComboChart(document.getElementById('chart_enterprise_value'));
            chart.draw(data, options);
        }
        
        // 2. 배당 그래프 (막대그래프)
        google.charts.setOnLoadCallback(drawDividendChart);
        function drawDividendChart() {
            const filtered = filterDataByMode(financialData, currentDateMode);
            if (!filtered.dividend || !filtered.dividend.dates) {
                document.getElementById('chart_dividend').innerHTML = '<p>배당 데이터를 불러올 수 없습니다.</p>';
                return;
            }
            
            const data = new google.visualization.DataTable();
            data.addColumn('string', '날짜');
            data.addColumn('number', '현금배당성향(%)');
            data.addColumn('number', '현금배당수익률');
            
            const dates = filtered.dividend.dates || [];
            const payout = filtered.dividend.cash_dividend_payout_ratio || [];
            const yieldRate = filtered.dividend.cash_dividend_yield || [];
            
            const maxLength = Math.max(dates.length, payout.length, yieldRate.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(payout[i]) || 0,
                    parseFloat(yieldRate[i]) || 0
                ]);
            }
            
            const options = withHAxis({
                title: '배당 분석',
                titleTextStyle: { fontSize: 14, bold: true },
                hAxis: {title: '날짜'},
                vAxis: {title: '배당 지표(%)'},
                chartArea: {top: '18%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top', textStyle: {fontSize: 11}},
                height: 400,
                colors: ['#ff6b6b', '#1f77b4']
            });
            
            const chart = new google.visualization.ColumnChart(document.getElementById('chart_dividend'));
            chart.draw(data, options);
        }
        
        // 3. 안정성 그래프 (+ 영업이익율)
        google.charts.setOnLoadCallback(drawStabilityChart);
        function drawStabilityChart() {
            const filtered = filterDataByMode(financialData, currentDateMode);
            if (!filtered.stability || !filtered.stability.dates) {
                document.getElementById('chart_stability').innerHTML = '<p>안정성 데이터를 불러올 수 없습니다.</p>';
                return;
            }
            
            const data = new google.visualization.DataTable();
            data.addColumn('string', '날짜');
            data.addColumn('number', '부채비율');
            data.addColumn('number', '순이익율');
            data.addColumn('number', '영업이익율');
            
            const dates = filtered.stability.dates || [];
            const debtRatio = filtered.stability.debt_ratio || [];
            const netProfitMargin = filtered.stability.net_profit_margin || [];
            const operatingProfitMargin = (filtered.profitability && filtered.profitability.operating_profit_margin) || [];
            
            const maxLength = Math.max(dates.length, debtRatio.length, netProfitMargin.length, operatingProfitMargin.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(debtRatio[i]) || 0,
                    parseFloat(netProfitMargin[i]) || 0,
                    parseFloat(operatingProfitMargin[i]) || 0
                ]);
            }
            
            const options = withHAxis({ 
                title: '안정성 분석',
                titleTextStyle: { fontSize: 14, bold: true },
                hAxis: {title: '날짜'},
                vAxis: {title: '비율 (%)'},
                series: {
                    0: {type: 'bars', color: '#ff7f0e'},
                    1: {type: 'bars', color: '#2ca02c'},
                    2: {type: 'bars', color: '#1f77b4'}
                },
                chartArea: {top: '18%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top', textStyle: {fontSize: 11}},
                height: 400
            });
            
            const chart = new google.visualization.ComboChart(document.getElementById('chart_stability'));
            chart.draw(data, options);
        }
        
        // 5. 수익성 그래프
        google.charts.setOnLoadCallback(drawRevenueChart);
        function drawRevenueChart() {
            const filtered = filterDataByMode(financialData, currentDateMode);
            if (!filtered.revenue || !filtered.revenue.dates) {
                document.getElementById('chart_revenue').innerHTML = '<p>수익성 데이터를 불러올 수 없습니다.</p>';
                return;
            }
            
            const data = new google.visualization.DataTable();
            data.addColumn('string', '날짜');
            data.addColumn('number', '매출액');
            data.addColumn('number', '영업이익(발표기준)');
            
            const dates = filtered.revenue.dates || [];
            const revenue = filtered.revenue.revenue || [];
            const operatingProfit = filtered.revenue.operating_profit_announced || [];
            
            const maxLength = Math.max(dates.length, revenue.length, operatingProfit.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(revenue[i]) || 0,
                    parseFloat(operatingProfit[i]) || 0
                ]);
            }
            
            const options = withHAxis({
                title: '수익성 분석',
                titleTextStyle: { fontSize: 14, bold: true },
                hAxis: {title: '날짜'},
                vAxis: {title: '금액 (억원)'},
                series: {
                    0: {type: 'bars', color: '#1f77b4'},
                    1: {type: 'bars', color: '#ff7f0e'}
                },
                chartArea: {top: '18%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top', textStyle: {fontSize: 11}},
                height: 400
            });
            
            const chart = new google.visualization.ComboChart(document.getElementById('chart_revenue'));
            chart.draw(data, options);
        }
    </script>

    <!-- 그래프 컨테이너들 -->
    <div style="margin-bottom: 10px;">
        <h3 style="margin-bottom:8px;">보기 범위</h3>
        <label style="margin-right:12px;"><input type="radio" name="dateMode" value="ANNUAL" checked onclick="currentDateMode='ANNUAL'; google.charts.setOnLoadCallback(() => {drawEnterpriseValueChart();drawDividendChart();drawStabilityChart();drawRevenueChart();});"> 연간</label>
        <label><input type="radio" name="dateMode" value="QUARTER" onclick="currentDateMode='QUARTER'; google.charts.setOnLoadCallback(() => {drawEnterpriseValueChart();drawDividendChart();drawStabilityChart();drawRevenueChart();});"> 분기</label>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>1. 기업가치 분석</h3>
        <div id="chart_enterprise_value" style="width: 100%; height: 400px;"></div>
        <div style="margin-top: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa;">
 
            <ul style="margin: 0 0 0 18px; padding: 0; font-size: 14px; line-height: 1.6;">
                <li>5년 평균 ROE ≥ 10%</li>
                <li>현재 PER &gt; 0 그리고 ≤ 20</li>
                <li>현재 PBR ≤ 1.5</li>
            </ul>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>2. 배당 분석</h3>
        <div id="chart_dividend" style="width: 100%; height: 400px;"></div>
        <div style="margin-top: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa;">
 
            <ul style="margin: 0 0 0 18px; padding: 0; font-size: 14px; line-height: 1.6;">
                <li>현재 현금배당수익률 ≥ 3.5% (최근 1년 은행 정기예금 금리 이상)</li>
                <li>최근 결산 현금배당성향 ≥ 20% 그리고 ≤ 60%</li>
                <li>배당 안정성: 최근 3년간 배당금 삭감 이력 없음(유지 또는 증가)</li>
            </ul>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>3. 안정성 분석</h3>
        <div id="chart_stability" style="width: 100%; height: 400px;"></div>
        <div style="margin-top: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa;">
   
            <ul style="margin: 0 0 0 18px; padding: 0; font-size: 14px; line-height: 1.6;">
                <li>최근 결산 부채비율 ≤ 150%</li>
                <li>5년 평균 영업이익률 ≥ 8%</li>
            </ul>
        </div>
    </div>

    

    <div style="margin-bottom: 30px;">
        <h3>4. 수익성 분석</h3>
        <div id="chart_revenue" style="width: 100%; height: 400px;"></div>
        <div style="margin-top: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa;">
 
            <ul style="margin: 0 0 0 18px; padding: 0; font-size: 14px; line-height: 1.6;">
                <li>5년 매출액 CAGR ≥ 5%</li>
                <li>5년 영업이익 CAGR ≥ 5%</li>
                <li>지속성: 최근 5년 연간 당기순이익 적자 기록 없음</li>
            </ul>
        </div>
    </div>

    
<!-- 
[수익성] <br>
<div id="chart_div" style="width: 100%; height: 300px;" ></div>  </p>
<p>[이익율]  
<div id="chart_div_finance" style="width: 100%; height: 300px;"  ></div>  </p> 
<p>[안정성]    
<div id="chart_div_stability" style="width: 100%; height: 300px;"></div></p>  
<p>[배당]   
<div id="chart_div_allocation" style="width: 100%; height: 300px;"></div></p> 
<p>[기업가치]  
<div id="chart_div_value" style="width: 100%; height: 300px;"></div>   </p> -->

   
 </div>
 
   
 <!-- menu_left -->
<?php 

if("C" == $siteType){
	include("./recommendEps/include/menu_left.php");
}else{
	include("./include/menu_left.php");
}
?>
 <!-- footer -->
<?php 
include("./include/footer.php");?>

<!-- search panel -->   
<?php 
include("./include/menu_search.php");?> 
                
</div> 
</body>
</html>
 
 