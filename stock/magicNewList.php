<?php
header('Content-Type: text/html;charset=utf-8');

$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '');
include("/hosting/windmoter/html/stock/include/header.php");

if ($market_gubun_type == 1) {
    $market_gubun_nm = "전 종목";
    $market_display_html = '<span class="ft-market-kospi">코스피</span>';
    $marketQueryPu = " and market_gubun = 'STK' ";
    $activeTab = '1';
} else if ($market_gubun_type == 2) {
    $market_gubun_nm = "전 종목";
    $market_display_html = '<span class="ft-market-kosdaq">코스닥</span>';
    $marketQueryPu = " and market_gubun = 'KSQ' ";
    $activeTab = '2';
} else {
    $market_gubun_nm = "전 종목";
    $market_display_html = '전 종목';
    $marketQueryPu = "  ";
    $activeTab = '';
}

$query_roa = "
SELECT SUM(gpaCnt) AS gpaCnt, isu_cd, isu_nm, pbr, gpa, roe, end_pr FROM (
    SELECT @rownum := @rownum + 1 AS gpaCnt, sub.* FROM (
        SELECT a.pbr, a.gpa, a.roe, a.isu_cd, a.isu_nm, a.end_pr
        FROM stock_valuations a, stock_daum_roe sdr
        WHERE a.isu_cd = sdr.isu_cd
        AND (sdr.shares_cnt * a.end_pr) > 57050000000
        and sdr.finance_cnt < 6
        AND sdr.shares_cnt > 15000000
        AND a.dvd_yld > 0
        AND a.pbr < 100
        AND magic_no = 'Y'
        AND iisu_code = '-'
        AND a.pbr != 100000
        AND a.pbr > 0
        AND a.gpa > 0 " . $marketQueryPu . "
        and a.finance_cnt < 5
        ORDER BY a.pbr ASC
    ) sub, (SELECT @rownum := 0) TMP
    UNION ALL
    SELECT @rownum3 := @rownum3 + 1 AS gpaCnt, sub.* FROM (
        SELECT a.pbr, a.gpa, a.roe, a.isu_cd, a.isu_nm, a.end_pr
        FROM stock_valuations a, stock_daum_roe sdr
        WHERE a.isu_cd = sdr.isu_cd
        AND (sdr.shares_cnt * a.end_pr) > 57050000000
        and sdr.finance_cnt < 6
        AND sdr.shares_cnt > 15000000
        AND a.dvd_yld > 0
        AND a.pbr < 100
        AND magic_no = 'Y'
        AND iisu_code = '-'
        AND a.pbr != 100000
        AND a.pbr > 0
        AND a.gpa > 0 " . $marketQueryPu . "
        and a.finance_cnt < 5
        ORDER BY a.gpa DESC
    ) sub, (SELECT @rownum3 := 0) TMP
) m GROUP BY isu_cd, isu_nm, pbr, gpa, roe, end_pr ORDER BY gpaCnt ASC LIMIT 100";

$res_roa = mysql_query($query_roa, $conn);

$today = date("Ymd");
$todayTime = date("H");
$todayWa = date("w");
$is_Insert = true;
if ($todayWa == "6" || $todayWa == "7") {
    $is_Insert = false;
}
if ($is_Insert) {
    $query = "select key_date from stock_magic_New_top where key_date =" . $today;
    $res = mysql_query($query, $conn);
    if ($res) {
        $rowcount = mysqli_num_rows($res);
        if ($rowcount > 0) {
            $is_Insert = false;
        }
    }
}

function GenerateString($length) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string_generated = "";
    $nmr_loops = $length;
    while ($nmr_loops--) {
        $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string_generated;
}
?>

<script>
function stockDitalMove(isNo, isNm) {
    location.href = "<?php echo $hostUrl; ?>/stock/stockDetail.php?isNo=" + isNo + "&isNm=" + isNm;
}
function stockResultMove(nowDate) {
    location.href = "<?php echo $hostUrl; ?>/stock/resultStock/magicNewResult.php?nowDate=" + nowDate;
}

function exportExcel() {
    var wb = XLSX.utils.book_new();
    var newWorksheet = excelHandler.getWorksheet();
    XLSX.utils.book_append_sheet(wb, newWorksheet, excelHandler.getSheetName());
    var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
    saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), excelHandler.getExcelFileName());
}

var excelHandler = {
    getExcelFileName: function () { return 'magicNewData.xlsx'; },
    getSheetName: function () { return 'list'; },
    getExcelData: function () { return document.getElementById('tableData'); },
    getWorksheet: function () { return XLSX.utils.table_to_sheet(this.getExcelData()); }
};

function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}
</script>

<main class="ft-main" role="main">
  <div class="ft-page-head">
    <div>
      <h1 class="ft-page-title"><span class="ft-market-kospi">신</span> 마법공식 <?php echo $market_display_html; ?> Top 100</h1>
      <p class="ft-page-sub">PBR 낮은 순 · GP/A 높은 순 합산 랭킹 · 기준일 <?php echo htmlspecialchars($updateViewDay, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <button type="button" class="ft-help-btn" data-ft-modal="strategyModal">전략 기준 ?</button>
  </div>

  <div class="ft-controls-bar">
    <nav class="ft-segmented" aria-label="시장 구분">
      <a href="/stock/magicNewList.php" class="ft-seg-btn <?php echo $activeTab === '' ? 'is-active' : ''; ?>">전 종목</a>
      <a href="/stock/magicNewList.php?type=1" class="ft-seg-btn <?php echo $activeTab === '1' ? 'is-active' : ''; ?>">코스피</a>
      <a href="/stock/magicNewList.php?type=2" class="ft-seg-btn <?php echo $activeTab === '2' ? 'is-active' : ''; ?>">코스닥</a>
    </nav>

    <div class="ft-toolbar-actions">
      <?php
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $is_browser = preg_match('/(Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)/i', $user_agent);
      if ($is_browser) {
          ?>
          <button type="button" id="excelDownload" onclick="exportExcel()" class="ft-btn-secondary">엑셀 다운로드</button>
          <?php
      }
      $calendar_year = "2019";
      $calendar_morth = "02";
      $calendar_day = "20";
      include($mainRoot . "/calendar_op/calendar_date.php");
      ?>
    </div>
  </div>

  <div class="ft-search-box">
    <svg class="ft-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <circle cx="11" cy="11" r="8"></circle>
      <path d="m21 21-4.35-4.35"></path>
    </svg>
    <input id="ftRankSearchInput" type="search" class="ft-search-input" placeholder="종목명으로 빠르게 검색.." aria-label="종목명으로 검색" />
  </div>

  <?php
  $rows = array();
  if ($res_roa) {
      while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
          $rows[] = $row;
      }
  }
  ?>

  <section class="ft-rank-card" aria-label="신 마법공식 Top 100 목록">
    <div class="ft-rank-head">
      <span>순위</span>
      <span>종목명</span>
      <span>PBR ↓</span>
      <span>GP/A ↑</span>
      <span>ROE</span>
    </div>

    <ul id="ftRankList" class="ft-rank-list">
      <?php
      $rowCount = 1;
      foreach ($rows as $row) {
          if ($rowCount == 3) {
              echo '<li class="ft-inline-ad">';
              include($mainRoot . "/stock/include/AD_320_50.php");
              echo '</li>';
          }
          if ($rowCount == 13) {
              echo '<li class="ft-inline-ad">';
              include($mainRoot . "/stock/include/AD_320_100.php");
              echo '</li>';
          }
          $rankClass = $rowCount <= 10 ? 'ft-rank-num is-top' : 'ft-rank-num';
          ?>
          <li class="ft-rank-item">
            <a href="javascript:stockDitalMove('<?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo rawurlencode($row[2]); ?>')" class="ft-rank-row-link">
              <span class="<?php echo $rankClass; ?>"><?php echo $rowCount; ?></span>
              <span class="ft-rank-name"><?php echo htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="ft-rank-metric"><?php echo htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8'); ?>배</span>
              <span class="ft-rank-metric is-strong"><?php echo sprintf("%2.2f", $row[4]); ?></span>
              <span class="ft-rank-metric"><?php echo htmlspecialchars($row[5], ENT_QUOTES, 'UTF-8'); ?>%</span>
            </a>
          </li>
          <?php
          if ($is_Insert && $activeTab === '' && (int)$todayTime > 11) {
              if ($rowCount == 1) {
                  $sql_magic = "insert into stock_magic_New_top
                  (key_date, rank1_cd, rank1_nm, rank1_end_pr)
                  values('$today', '$row[1]', '$row[2]', '$row[6]')";
                  mysql_query($sql_magic, $conn);
              } else if ($rowCount > 1 && $rowCount < 21) {
                  $sql_magic = "UPDATE stock_magic_New_top
                  SET rank" . $rowCount . "_cd = '$row[1]', rank" . $rowCount . "_nm = '$row[2]', rank" . $rowCount . "_end_pr = '$row[6]'
                  WHERE key_date =" . $today;
                  mysql_query($sql_magic, $conn);
              }
          }
          $rowCount++;
      }
      ?>
    </ul>
    <div id="ftRankEmpty" class="ft-empty-state">검색 조건에 맞는 종목이 없습니다.</div>
  </section>

  <!-- Hidden table used by Excel export -->
  <table id="tableData" style="display:none;">
    <thead>
      <tr>
        <th>랭킹</th>
        <th>종목명</th>
        <th>PBR</th>
        <th>GP/A</th>
        <th>ROE</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $rowCount = 1;
      foreach ($rows as $row) {
          echo "<tr>\n";
          echo "<td>" . $rowCount . "</td>\n";
          echo "<td>" . htmlspecialchars($row[2]) . "</td>\n";
          echo "<td>" . $row[3] . "</td>\n";
          echo "<td>" . sprintf("%2.2f", $row[4]) . "</td>\n";
          echo "<td>" . $row[5] . "</td>\n";
          echo "</tr>\n";
          $rowCount++;
      }
      ?>
    </tbody>
  </table>
</main>

<!-- Strategy Criteria Modal -->
<div id="ftModalOverlay-strategyModal" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="strategyModalTitle">
    <div class="ft-modal-header">
      <h3 id="strategyModalTitle" class="ft-modal-title">신 마법공식 선정 기준</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>시가총액 500억 이상 · 상장 주식수 1,500만 주 이상</li>
        <li>배당수익률 0% 이상 · PBR 100배 이하</li>
        <li>금융·보험 업종 및 관심종목 제외</li>
        <li>GP/A = 영업이익 / 자산총계 (영업이익 = 매출총이익 - 판관비)</li>
        <li>PBR 낮은 순 + GP/A 높은 순 합산 상위 100위 선정</li>
      </ul>
    </div>
  </div>
</div>

<?php
include($mainRoot . "/stock/include/menu_left.php");
include($mainRoot . "/stock/include/footer.php");
include($mainRoot . "/stock/include/menu_search.php");
?>
</div>
</body>
</html>
