<?php
$siteType = $_REQUEST['siteType'] ?? '';
$isNo = $_REQUEST['isNo'] ?? '';

$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '');
include("/hosting/windmoter/html/stock/include/header.php");

$_TOKEN = $_REQUEST["token"] ?? '';
$_TOKEN_FCM_ID = "";
$rowcount = 0;
$fcm_id = "";
$isInsertDate = "N";
if (strlen($_TOKEN) > 0) {
    $query = " SELECT a.fcm_id, b.isu_cd
    FROM stock_fcm_users a LEFT JOIN stock_fcm_push b ON a.fcm_id = b.fcm_id
    WHERE a.use_yn ='Y' AND Token ='" . $_TOKEN . "'";

    $res = mysql_query($query, $conn);
    if ($res) {
        $rowcount = mysqli_num_rows($res);
        if ($rowcount > 0) {
            while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {
                if ($isNo == $row["isu_cd"]) $isInsertDate = "Y";
                $fcm_id = $row["fcm_id"];
            }
        }
    }
}

// 현재 가격 - 네이버 금융 실시간 폴링
$urlStk = "https://polling.finance.naver.com/api/realtime?query=SERVICE_ITEM:" . $isNo . "|SERVICE_RECENT_ITEM:" . $isNo . "&_callback=window.__jindo2_callback._3868";
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, "https://polling.finance.naver.com");

$json = curl_exec($curl_handle);
curl_close($curl_handle);

$priceStock = 0;
$isNm = rawurldecode($_REQUEST['isNm'] ?? '');
if ($json) {
    $startNum = (strrpos($json, "(") + 1);
    $endNum = (strlen($json) - $startNum - 1);
    $json = substr($json, $startNum, $endNum);
    $json = iconv("EUC-KR", "UTF-8", $json);
    $result_array = json_decode($json);
    if (isset($result_array->result->areas[0]->datas[0])) {
        $priceStock = $result_array->result->areas[0]->datas[0]->nv ?? 0;
        $isNm = $result_array->result->areas[0]->datas[0]->nm ?? $isNm;
    }
}

// FnGuide 기업 요약 정보
$cmpCd = str_pad(preg_replace('/\D/', '', $isNo), 6, '0', STR_PAD_LEFT);
$urlStk = "https://wcomp.fnguide.com/CompanyInfo/Snapshot?c_id=AA&menu_type=01&cmp_cd=" . $cmpCd;
$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
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
    $parts = array("Business Summary[{$bizDate}]");
    if ($bizHeader !== '') {
        $parts[] = $bizHeader;
    }
    $parts = array_merge($parts, $bizItems);
    $stockInfo = str_replace("작성일:", "정보:", implode("<br>", $parts));
}

function tagListData($strTag, $content) {
    $reg = sprintf("/\<%s[^>]*\>(.*?)\<\/%s\>/is", $strTag, $strTag);
    preg_match_all($reg, $content, $matches, PREG_PATTERN_ORDER);
    return $matches[1];
}

function tagReplace($strTag) {
    $strTag = strip_tags(preg_replace("/\s+/", "", str_replace("N/A", "0", str_replace(",", "", $strTag))));
    if ($strTag == "") {
        $strTag = 0;
    }
    return $strTag;
}
?>

<main class="ft-main" role="main">
  <!-- Stock Summary Hero Card -->
  <section class="ft-detail-hero">
    <div class="ft-detail-top">
      <div>
        <h1 class="ft-detail-name"><?php echo htmlspecialchars($isNm, ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="ft-detail-meta">
          <span class="ft-mono">A<?php echo htmlspecialchars($isNo, ENT_QUOTES, 'UTF-8'); ?></span>
          <span aria-hidden="true">·</span>
          <a href="https://finance.daum.net/quotes/A<?php echo htmlspecialchars($isNo, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">카카오 증권시세</a>
          <span aria-hidden="true">·</span>
          <a href="http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=<?php echo htmlspecialchars($isNo, ENT_QUOTES, 'UTF-8'); ?>&finGubun=MAIN" target="_blank" rel="noopener">Daum 재무정보</a>
        </div>
      </div>

      <div class="ft-detail-price-wrap">
        <div class="ft-detail-price-label">현재가</div>
        <div class="ft-detail-price">
          <?php echo number_format((float)$priceStock); ?><span class="ft-detail-price-unit">원</span>
        </div>
      </div>
    </div>
  </section>

  <form name="sendForm" action="./include/myInsert.php" method="post">
    <input type="hidden" name="fcm_id" id="fcm_id" value="<?php echo htmlspecialchars($fcm_id, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="isu_cd" id="isu_cd" value="<?php echo htmlspecialchars($isNo, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="isu_nm" id="isu_nm" value="<?php echo htmlspecialchars($isNm, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="dataType" id="dataType" value="in">
  </form>
  <script>
  function mySend() {
    location.href = "<?php echo $hostUrl; ?>/stock/myList.php";
  }
  function myInsertSend() {
    var sendForm = document.sendForm;
    if (parseInt(<?php echo (int)$rowcount; ?>) < 1) {
      alert("안드로이드 앱 접속시 이용가능합니다.");
      location.href = "<?php echo $hostUrl; ?>/stock/myList.php";
    } else if (parseInt(<?php echo (int)$rowcount; ?>) > 9) {
      alert("최대 10개 관심종목 등록 가능합니다.");
      location.href = "<?php echo $hostUrl; ?>/stock/myList.php";
    } else {
      sendForm.submit();
    }
  }
  </script>

  <!-- 1Y Chart & Company Info -->
  <div class="ft-grid-2" style="margin-bottom:16px;">
    <section class="ft-chart-card" style="margin-bottom:0;">
      <h2 class="ft-chart-title">1년 주가 현황</h2>
      <div style="border:1px solid var(--ft-border);border-radius:12px;overflow:hidden;background:#ffffff;">
        <img src="https://t1.daumcdn.net/media/finance/chart/kr/daumstock/y/A<?php echo htmlspecialchars($isNo, ENT_QUOTES, 'UTF-8'); ?>.png" alt="<?php echo htmlspecialchars($isNm, ENT_QUOTES, 'UTF-8'); ?> 1년 주가 차트" style="width:100%;display:block;" />
      </div>
    </section>

    <section class="ft-chart-card" style="margin-bottom:0;">
      <h2 class="ft-chart-title">기업 정보</h2>
      <div style="font-size:13px;color:var(--ft-text-secondary);line-height:1.7;">
        <?php echo $stockInfo !== '' ? $stockInfo : '기업 요약 정보를 불러오지 못했습니다.'; ?>
      </div>
    </section>
  </div>

  <!-- Middle Ad Slot -->
  <div class="ft-ad-slot">
    <?php
    include("./include/AD_320_100.php");
    require_once './daumStock/daum_financial_crawler.php';
    $crawler = new DaumFinancialCrawler($isNo);
    $financialData = $crawler->crawlFinancialData();
    ?>
  </div>

  <!-- Google Charts Loader & Analytics -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart', 'bar']});

    const financialData = <?php echo json_encode($financialData, JSON_UNESCAPED_UNICODE); ?>;
    const DATE_MODE_ANNUAL = 'ANNUAL';
    const DATE_MODE_QUARTER = 'QUARTER';
    let currentDateMode = DATE_MODE_ANNUAL;

    function cleanDate(raw) {
      if (!raw) return '';
      let t = String(raw).replace(/\(.*?\)/g, '');
      t = t.replace(/\//g, '.').replace(/[^0-9.]/g, '');
      const m = t.match(/^(\d{4})(?:\.(\d{1,2}))?$/);
      if (!m) return '';
      const y = m[1];
      const mo = m[2] ? String(m[2]).padStart(2, '0') : '';
      return mo ? `${y}.${mo}` : y;
    }

    function isQuarterDate(dateText) {
      if (!dateText) return false;
      const normalized = cleanDate(dateText);
      const m = normalized.match(/^(\d{4})\.(\d{2})$/);
      if (!m) return false;
      const month = parseInt(m[2], 10);
      return month >= 1 && month <= 11;
    }

    function isAnnualDate(dateText) {
      if (!dateText) return false;
      const normalized = cleanDate(dateText);
      return /^\d{4}$/.test(normalized) || /^\d{4}\.12$/.test(normalized);
    }

    function monthToQuarterLabel(dateText) {
      const normalized = cleanDate(dateText);
      const m = normalized.match(/^(\d{4})(?:\.(\d{1,2}))?$/);
      if (!m) return dateText;
      const year = m[1];
      const month = m[2] ? parseInt(m[2], 10) : null;
      if (!month) return year;
      return `${year}.Q${Math.ceil(month / 3)}`;
    }

    function formatDateForMode(dateText, mode) {
      if (!dateText) return '';
      if (mode === DATE_MODE_ANNUAL) {
        const normalized = cleanDate(dateText);
        const m = normalized.match(/^(\d{4})/);
        return m ? m[1] : dateText;
      }
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
        { key: 'enterprise_value', fields: ['per', 'pbr', 'roa'] },
        { key: 'dividend', fields: ['cash_dividend_payout_ratio', 'cash_dividend_yield'] },
        { key: 'stability', fields: ['debt_ratio', 'net_profit_margin'] },
        { key: 'profitability', fields: ['operating_profit_margin', 'net_profit_margin'] },
        { key: 'revenue', fields: ['revenue', 'operating_profit_announced', 'roe'] }
      ];
      for (const b of blocks) {
        const block = out[b.key];
        if (!block || !Array.isArray(block.dates)) continue;
        const filtered = {};
        for (const f of b.fields) {
          const r = filterSeries(block.dates, block[f] || []);
          filtered.dates = r.dates;
          filtered[f] = r.values;
        }
        out[b.key] = Object.assign({ dates: filtered.dates || [] }, filtered);
      }
      return out;
    }

    function withHAxis(options) {
      options.hAxis = Object.assign({}, options.hAxis || {}, {
        slantedText: true,
        slantedTextAngle: 60,
        showTextEvery: 1,
        textStyle: { fontSize: 10, color: '#4E5968' },
        allowContainerBoundaryTextOverflow: true
      });
      options.vAxis = Object.assign({}, options.vAxis || {}, {
        textStyle: { fontSize: 11, color: '#4E5968' },
        gridlines: { color: '#E5E8EB' }
      });
      options.backgroundColor = '#ffffff';
      return options;
    }

    function drawEnterpriseValueChart() {
      const el = document.getElementById('chart_enterprise_value');
      if (!el) return;
      const filtered = filterDataByMode(financialData, currentDateMode);
      if (!filtered.enterprise_value || !filtered.enterprise_value.dates) {
        el.innerHTML = '<p style="color:#8B95A1;font-size:13px;">기업가치 데이터를 불러올 수 없습니다.</p>';
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
        titleTextStyle: { fontSize: 14, bold: true, color: '#191F28' },
        hAxis: { title: '날짜' },
        vAxis: { title: '값' },
        series: {
          0: { type: 'line', color: '#F59E0B' },
          1: { type: 'line', color: '#059669' },
          2: { type: 'line', color: '#3182F6' },
          3: { type: 'line', color: '#7C3AED' }
        },
        chartArea: { top: '18%', left: '12%', right: '6%', bottom: '22%' },
        legend: { position: 'top', textStyle: { fontSize: 11, color: '#4E5968' } }
      });

      const chart = new google.visualization.ComboChart(el);
      chart.draw(data, options);
    }

    function drawDividendChart() {
      const el = document.getElementById('chart_dividend');
      if (!el) return;
      const filtered = filterDataByMode(financialData, currentDateMode);
      if (!filtered.dividend || !filtered.dividend.dates) {
        el.innerHTML = '<p style="color:#8B95A1;font-size:13px;">배당 데이터를 불러올 수 없습니다.</p>';
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
        titleTextStyle: { fontSize: 14, bold: true, color: '#191F28' },
        hAxis: { title: '날짜' },
        vAxis: { title: '배당 지표(%)' },
        chartArea: { top: '18%', left: '12%', right: '6%', bottom: '22%' },
        legend: { position: 'top', textStyle: { fontSize: 11, color: '#4E5968' } },
        colors: ['#F04452', '#3182F6']
      });

      const chart = new google.visualization.ColumnChart(el);
      chart.draw(data, options);
    }

    function drawStabilityChart() {
      const el = document.getElementById('chart_stability');
      if (!el) return;
      const filtered = filterDataByMode(financialData, currentDateMode);
      if (!filtered.stability || !filtered.stability.dates) {
        el.innerHTML = '<p style="color:#8B95A1;font-size:13px;">안정성 데이터를 불러올 수 없습니다.</p>';
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
        titleTextStyle: { fontSize: 14, bold: true, color: '#191F28' },
        hAxis: { title: '날짜' },
        vAxis: { title: '비율 (%)' },
        series: {
          0: { type: 'bars', color: '#F59E0B' },
          1: { type: 'bars', color: '#059669' },
          2: { type: 'bars', color: '#3182F6' }
        },
        chartArea: { top: '18%', left: '12%', right: '6%', bottom: '22%' },
        legend: { position: 'top', textStyle: { fontSize: 11, color: '#4E5968' } }
      });

      const chart = new google.visualization.ComboChart(el);
      chart.draw(data, options);
    }

    function drawRevenueChart() {
      const el = document.getElementById('chart_revenue');
      if (!el) return;
      const filtered = filterDataByMode(financialData, currentDateMode);
      if (!filtered.revenue || !filtered.revenue.dates) {
        el.innerHTML = '<p style="color:#8B95A1;font-size:13px;">수익성 데이터를 불러올 수 없습니다.</p>';
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
        titleTextStyle: { fontSize: 14, bold: true, color: '#191F28' },
        hAxis: { title: '날짜' },
        vAxis: { title: '금액 (억원)' },
        series: {
          0: { type: 'bars', color: '#3182F6' },
          1: { type: 'bars', color: '#F59E0B' }
        },
        chartArea: { top: '18%', left: '12%', right: '6%', bottom: '22%' },
        legend: { position: 'top', textStyle: { fontSize: 11, color: '#4E5968' } }
      });

      const chart = new google.visualization.ComboChart(el);
      chart.draw(data, options);
    }

    function drawAllDetailCharts() {
      drawEnterpriseValueChart();
      drawDividendChart();
      drawStabilityChart();
      drawRevenueChart();
    }

    google.charts.setOnLoadCallback(drawAllDetailCharts);
    window.ftRedrawCharts = drawAllDetailCharts;

    function setDateMode(mode) {
      currentDateMode = mode;
      var btnAnnual = document.getElementById('btnModeAnnual');
      var btnQuarter = document.getElementById('btnModeQuarter');
      if (btnAnnual) btnAnnual.classList.toggle('is-active', mode === DATE_MODE_ANNUAL);
      if (btnQuarter) btnQuarter.classList.toggle('is-active', mode === DATE_MODE_QUARTER);
      google.charts.setOnLoadCallback(drawAllDetailCharts);
    }
  </script>

  <!-- Date Mode Segmented Toolbar -->
  <div class="ft-controls-bar" style="margin-top:4px;">
    <div>
      <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--ft-text-primary);">재무 데이터 분석</h2>
      <p style="margin:2px 0 0;font-size:12px;color:var(--ft-text-muted);">연간 및 분기별 재무 추이를 전환해 확인하세요</p>
    </div>
    <div class="ft-segmented" role="tablist" aria-label="재무 보기 범위">
      <button type="button" id="btnModeAnnual" class="ft-seg-btn is-active" onclick="setDateMode('ANNUAL')">연간</button>
      <button type="button" id="btnModeQuarter" class="ft-seg-btn" onclick="setDateMode('QUARTER')">분기</button>
    </div>
  </div>

  <div class="ft-grid-2">
    <section class="ft-chart-card">
      <h3 class="ft-chart-title">1. 기업가치 분석</h3>
      <div id="chart_enterprise_value" class="ft-chart-box"></div>
      <div class="ft-criteria-note">
        <ul class="ft-criteria-list">
          <li>5년 평균 ROE ≥ 10%</li>
          <li>현재 PER &gt; 0 그리고 ≤ 20</li>
          <li>현재 PBR ≤ 1.5</li>
        </ul>
      </div>
    </section>

    <section class="ft-chart-card">
      <h3 class="ft-chart-title">2. 배당 분석</h3>
      <div id="chart_dividend" class="ft-chart-box"></div>
      <div class="ft-criteria-note">
        <ul class="ft-criteria-list">
          <li>현재 현금배당수익률 ≥ 3.5% (최근 1년 예금 금리 이상)</li>
          <li>최근 결산 현금배당성향 ≥ 20% 그리고 ≤ 60%</li>
          <li>배당 안정성: 최근 3년간 배당금 삭감 이력 없음</li>
        </ul>
      </div>
    </section>

    <section class="ft-chart-card">
      <h3 class="ft-chart-title">3. 안정성 분석</h3>
      <div id="chart_stability" class="ft-chart-box"></div>
      <div class="ft-criteria-note">
        <ul class="ft-criteria-list">
          <li>최근 결산 부채비율 ≤ 150%</li>
          <li>5년 평균 영업이익률 ≥ 8%</li>
        </ul>
      </div>
    </section>

    <section class="ft-chart-card">
      <h3 class="ft-chart-title">4. 수익성 분석</h3>
      <div id="chart_revenue" class="ft-chart-box"></div>
      <div class="ft-criteria-note">
        <ul class="ft-criteria-list">
          <li>5년 매출액 CAGR ≥ 5%</li>
          <li>5년 영업이익 CAGR ≥ 5%</li>
          <li>지속성: 최근 5년 연간 당기순이익 적자 기록 없음</li>
        </ul>
      </div>
    </section>
  </div>
</main>

<?php
if ("C" == $siteType) {
    include("./recommendEps/include/menu_left.php");
} else {
    include("./include/menu_left.php");
}
include("./include/footer.php");
include("./include/menu_search.php");
?>
</div>
</body>
</html>