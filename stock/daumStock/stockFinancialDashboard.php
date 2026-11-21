<?php
/**
 * 주식 재무정보 대시보드
 * 다움 증권 API를 사용하여 재무정보를 크롤링하고 그래프로 표시
 */

$isNo = $_REQUEST['isNo'] ?? '005930'; // 기본값: 삼성전자
$siteType = $_REQUEST['siteType'] ?? '';

include("/hosting/windmoter/html/stock/include/header.php");

// 다움 증권 크롤러 클래스 로드 (대안/향상된 크롤러 제거)
require_once 'daum_financial_crawler.php';

// 재무 데이터 크롤링
$crawler = new DaumFinancialCrawler($isNo);
$financialData = $crawler->crawlFinancialData();

// 현재 주가 정보 가져오기 (기존 코드 활용)
$urlStk = "https://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:".$isNo."|SERVICE_RECENT_ITEM:".$isNo."&_callback=window.__jindo2_callback._3868";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, "https://polling.finance.naver.com");

$json = curl_exec($curl_handle);
$startNum = (strrpos($json, "(") + 1);
$endNum = (strlen($json) - $startNum - 1);
$json = substr($json, $startNum, $endNum);
$json = iconv("EUC-KR", "UTF-8", $json);
$result_array = json_decode($json);

$priceStock = $result_array->result->areas[0]->datas[0]->nv ?? 0;
$isNm = $result_array->result->areas[0]->datas[0]->nm ?? '종목명';
?>

<div role="main" class="ui-content jqm-content">
    <table>
        <tr>
            <td>
                <h2><?php echo $isNm ?> [<?php echo number_format($priceStock) ?> 원]</h2>
            </td>
        </tr>
    </table>

    <!-- Google Charts 라이브러리 로드 -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'bar']});
        
        // 재무 데이터 (PHP에서 JavaScript로 전달)
        const financialData = <?php echo json_encode($financialData, JSON_UNESCAPED_UNICODE); ?>;

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
            
            const dates = filtered.enterprise_value.dates || [];
            const per = filtered.enterprise_value.per || [];
            const pbr = filtered.enterprise_value.pbr || [];
            const roa = filtered.enterprise_value.roa || [];
            
            const maxLength = Math.max(dates.length, per.length, pbr.length, roa.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(per[i]) || 0,
                    parseFloat(pbr[i]) || 0,
                    parseFloat(roa[i]) || 0
                ]);
            }
            
            const options = withHAxis({
                title: '기업가치 분석',
                hAxis: {title: '날짜'},
                vAxis: {title: '값'},
                series: {
                    0: {type: 'line', color: '#ff7f0e'},   // PER
                    1: {type: 'line', color: '#2ca02c'},   // PBR
                    2: {type: 'line', color: '#1f77b4'}    // ROA
                },
                chartArea: {top: '10%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top'},
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
                hAxis: {title: '날짜'},
                vAxis: {title: '배당 지표(%)'},
                chartArea: {top: '10%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top'},
                height: 400,
                colors: ['#ff6b6b', '#1f77b4']
            });
            
            const chart = new google.visualization.ColumnChart(document.getElementById('chart_dividend'));
            chart.draw(data, options);
        }
        
        // 3. 안정성 그래프
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
            
            const dates = filtered.stability.dates || [];
            const debtRatio = filtered.stability.debt_ratio || [];
            const netProfitMargin = filtered.stability.net_profit_margin || [];
            
            const maxLength = Math.max(dates.length, debtRatio.length, netProfitMargin.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(debtRatio[i]) || 0,
                    parseFloat(netProfitMargin[i]) || 0
                ]);
            }
            
            const options = withHAxis({ 
                hAxis: {title: '날짜'},
                vAxis: {title: '비율 (%)'},
                series: {
                    0: {type: 'bars', color: '#ff7f0e'},
                    1: {type: 'bars', color: '#2ca02c'}
                },
                chartArea: {top: '10%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top'},
                height: 400
            });
            
            const chart = new google.visualization.ComboChart(document.getElementById('chart_stability'));
            chart.draw(data, options);
        }
        
        // 4. 이익율 그래프
        google.charts.setOnLoadCallback(drawProfitabilityChart);
        function drawProfitabilityChart() {
            const filtered = filterDataByMode(financialData, currentDateMode);
            if (!filtered.profitability || !filtered.profitability.dates) {
                document.getElementById('chart_profitability').innerHTML = '<p>이익율 데이터를 불러올 수 없습니다.</p>';
                return;
            }
            
            const data = new google.visualization.DataTable();
            data.addColumn('string', '날짜');
            data.addColumn('number', '영업이익율');
            data.addColumn('number', '순이익율');
            
            const dates = filtered.profitability.dates || [];
            const operatingProfitMargin = filtered.profitability.operating_profit_margin || [];
            const netProfitMargin = filtered.profitability.net_profit_margin || [];
            
            const maxLength = Math.max(dates.length, operatingProfitMargin.length, netProfitMargin.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(operatingProfitMargin[i]) || 0,
                    parseFloat(netProfitMargin[i]) || 0
                ]);
            }
            
            const options = withHAxis({
                title: '이익율 분석',
                hAxis: {title: '날짜'},
                vAxis: {title: '이익율 (%)'},
                series: {
                    0: {type: 'bars', color: '#1f77b4'},      // 영업이익율: 선
                    1: {type: 'bars', color: '#ff7f0e'}       // 순이익율: 막대
                },
                chartArea: {top: '10%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top'},
                height: 400
            });
            
            const chart = new google.visualization.ComboChart(document.getElementById('chart_profitability'));
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
            data.addColumn('number', 'ROE');
            
            const dates = filtered.revenue.dates || [];
            const revenue = filtered.revenue.revenue || [];
            const operatingProfit = filtered.revenue.operating_profit_announced || [];
            const roe = filtered.revenue.roe || [];
            
            const maxLength = Math.max(dates.length, revenue.length, operatingProfit.length, roe.length);
            
            for (let i = 0; i < maxLength; i++) {
                data.addRow([
                    formatDateForMode(dates[i] || '', currentDateMode),
                    parseFloat(revenue[i]) || 0,
                    parseFloat(operatingProfit[i]) || 0,
                    parseFloat(roe[i]) || 0
                ]);
            }
            
            const options = withHAxis({
                title: '수익성 분석',
                hAxis: {title: '날짜'},
                vAxis: {title: '금액 (억원)'},
                series: {
                    0: {type: 'bars', color: '#1f77b4'},
                    1: {type: 'bars', color: '#ff7f0e'},
                    2: {type: 'line', color: '#2ca02c'}
                },
                chartArea: {top: '10%', left: '10%', right: '10%', bottom: '20%'},
                legend: {position: 'top'},
                height: 400
            });
            
            const chart = new google.visualization.ComboChart(document.getElementById('chart_revenue'));
            chart.draw(data, options);
        }
    </script>

    <!-- 그래프 컨테이너들 -->
    <div style="margin-bottom: 10px;">
        <h3 style="margin-bottom:8px;">보기 범위</h3>
        <label style="margin-right:12px;"><input type="radio" name="dateMode" value="ANNUAL" checked onclick="currentDateMode='ANNUAL'; google.charts.setOnLoadCallback(() => {drawEnterpriseValueChart();drawDividendChart();drawStabilityChart();drawProfitabilityChart();drawRevenueChart();});"> 연간</label>
        <label><input type="radio" name="dateMode" value="QUARTER" onclick="currentDateMode='QUARTER'; google.charts.setOnLoadCallback(() => {drawEnterpriseValueChart();drawDividendChart();drawStabilityChart();drawProfitabilityChart();drawRevenueChart();});"> 분기</label>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>1. 기업가치 분석</h3>
        <div id="chart_enterprise_value" style="width: 100%; height: 400px;"></div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>2. 배당 분석</h3>
        <div id="chart_dividend" style="width: 100%; height: 400px;"></div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>3. 안정성 분석</h3>
        <div id="chart_stability" style="width: 100%; height: 400px;"></div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>4. 이익율 분석</h3>
        <div id="chart_profitability" style="width: 100%; height: 400px;"></div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>5. 수익성 분석</h3>
        <div id="chart_revenue" style="width: 100%; height: 400px;"></div>
    </div>

    <!-- 외부 링크 -->
    <div style="margin-top: 30px;">
        <table>
            <tr>
                <td width="50%">
                    <a href="https://finance.daum.net/quotes/A<?php echo $isNo ?>" target="_blank">
                        카카오 증권시세
                    </a>
                </td>
                <td width="50%">
                    <a href="http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=<?php echo $isNo ?>&finGubun=MAIN" target="_blank">
                        Daum 재무정보
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1년 주가 현황 -->
    <div style="margin-top: 30px;">
        <h3>1년 주가 현황</h3>
        <img src="https://t1.daumcdn.net/media/finance/chart/kr/daumstock/y/A<?php echo $isNo ?>.png" style="width: 100%;">
    </div>
</div>

<!-- menu_left -->
<?php
if("C" == $siteType){
    include("/hosting/windmoter/html/stock/recommendEps/include/menu_left.php");
} else {
    include("/hosting/windmoter/html/stock/include/menu_left.php");
}
?>

<!-- footer -->
<?php include("/hosting/windmoter/html/stock/include/footer.php"); ?>

<!-- search panel -->
<?php include("/hosting/windmoter/html/stock/include/menu_search.php"); ?>

</div>
</body>
</html>
