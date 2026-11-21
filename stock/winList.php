<?php
header('Content-Type: text/html;charset=utf-8');

$market_gubun_type = rawurldecode($_REQUEST['type'] ?? '');
include("./include/header.php");

if ($market_gubun_type == 1) {
    $market_display_html = '<span class="ft-market-kospi">코스피</span>';
    $marketQueryPu = " and market_gubun = 'STK' ";
    $activeTab = '1';
} else if ($market_gubun_type == 2) {
    $market_display_html = '<span class="ft-market-kosdaq">코스닥</span>';
    $marketQueryPu = " and market_gubun = 'KSQ' ";
    $activeTab = '2';
} else {
    $market_display_html = '전 종목';
    $marketQueryPu = "  ";
    $activeTab = '';
}

$query_roa = " SELECT 1 AS dummy_col, a.isu_cd, a.isu_nm, a.per, a.PBR, a.dvd_yld, a.end_pr
FROM stock_valuations a, stock_daum_roe ofc
WHERE a.isu_cd = ofc.isu_cd and a.PER <= 15
AND (ofc.shares_cnt * a.end_pr) > 17050000000 AND ofc.shares_cnt > 15000000 AND a.dvd_yld > 0
and a.PER != -9999999
and ofc.finance_cnt < 6
AND (a.per / a.prv_eps) < 1
AND (a.per / a.dvd_yld) < 4
and magic_no = 'Y'
and a.stk_dvd > 0 and a.prv_eps > 0 " . $marketQueryPu . " and a.finance_cnt < 5
ORDER BY a.roa asc, a.roe desc LIMIT 100 ";

$res_roa = mysql_query($query_roa, $conn);

$today = date("Ymd");
$todayWa = date("w");
$is_Insert = true;
if ($todayWa == "6" || $todayWa == "7") {
    $is_Insert = false;
}
if ($is_Insert) {
    $query = "select key_date from stock_win_top where key_date =" . $today;
    $res = mysql_query($query, $conn);
    if ($res) {
        $rowcount = mysqli_num_rows($res);
        if ($rowcount > 0) {
            $is_Insert = false;
        }
    }
}
?>

<script>
function stockDitalMove(isNo, isNm) {
    location.href = "https://cronya.pe.kr/stock/stockDetail.php?isNo=" + isNo + "&isNm=" + isNm;
}
function stockResultMove(nowDate) {
    location.href = "<?php echo $hostUrl; ?>/stock/resultStock/winResult.php?nowDate=" + nowDate;
}
</script>

<main class="ft-main" role="main">
  <div class="ft-page-head">
    <div>
      <h1 class="ft-page-title">피터 린치 이기는 투자 <?php echo $market_display_html; ?> Top 100</h1>
      <p class="ft-page-sub">PEG &lt; 1 · 배당 대비 저평가 성장주 랭킹 · 기준일 <?php echo htmlspecialchars($updateViewDay, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <button type="button" class="ft-help-btn" data-ft-modal="strategyModal">전략 기준 ?</button>
  </div>

  <div class="ft-controls-bar">
    <nav class="ft-segmented" aria-label="시장 구분">
      <a href="/stock/winList.php" class="ft-seg-btn <?php echo $activeTab === '' ? 'is-active' : ''; ?>">전 종목</a>
      <a href="/stock/winList.php?type=1" class="ft-seg-btn <?php echo $activeTab === '1' ? 'is-active' : ''; ?>">코스피</a>
      <a href="/stock/winList.php?type=2" class="ft-seg-btn <?php echo $activeTab === '2' ? 'is-active' : ''; ?>">코스닥</a>
    </nav>

    <div class="ft-toolbar-actions">
      <?php
      $calendar_year = "2019";
      $calendar_morth = "08";
      $calendar_day = "21";
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

  <section class="ft-rank-card" aria-label="피터 린치 이기는 투자 Top 100">
    <div class="ft-rank-head">
      <span>순위</span>
      <span>종목명</span>
      <span>PER</span>
      <span>PBR</span>
      <span>배당률</span>
    </div>

    <ul id="ftRankList" class="ft-rank-list">
      <?php
      $rowCount = 1;
      if ($res_roa) {
          while ($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)) {
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
                <a href="javascript:stockDitalMove('<?php echo htmlspecialchars($row['isu_cd'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo rawurlencode($row[2]); ?>')" class="ft-rank-row-link">
                  <span class="<?php echo $rankClass; ?>"><?php echo $rowCount; ?></span>
                  <span class="ft-rank-name"><?php echo htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="ft-rank-metric"><?php echo htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8'); ?>배</span>
                  <span class="ft-rank-metric is-strong"><?php echo sprintf("%2.2f", $row[4]); ?>배</span>
                  <span class="ft-rank-metric" style="color:var(--ft-positive);font-weight:600;"><?php echo htmlspecialchars($row[5], ENT_QUOTES, 'UTF-8'); ?>%</span>
                </a>
              </li>
              <?php
              if ($is_Insert) {
                  if ($rowCount == 1) {
                      $sql_magic = "insert into stock_win_top
                      (key_date, rank1_cd, rank1_nm, rank1_end_pr)
                      values('$today', '$row[1]', '$row[2]', '$row[6]')";
                      mysql_query($sql_magic, $conn);
                  } else if ($rowCount > 1 && $rowCount < 21) {
                      $sql_magic = "UPDATE stock_win_top
                      SET rank" . $rowCount . "_cd = '$row[1]', rank" . $rowCount . "_nm = '$row[2]', rank" . $rowCount . "_end_pr = '$row[6]'
                      WHERE key_date =" . $today;
                      mysql_query($sql_magic, $conn);
                  }
              }
              $rowCount++;
          }
      }
      ?>
    </ul>
    <div id="ftRankEmpty" class="ft-empty-state">검색 조건에 맞는 종목이 없습니다.</div>
  </section>
</main>

<div id="ftModalOverlay-strategyModal" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="strategyModalTitle">
    <div class="ft-modal-header">
      <h3 id="strategyModalTitle" class="ft-modal-title">피터 린치 (이기는 투자) 기준</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>상장주식수 1,500만 주 이상 · 배당수익률 0% 이상</li>
        <li>PER / EPS 증가율 &lt; 1 · PER / 배당수익률 &lt; 4</li>
        <li>EPS 증가율 &lt; 50% · 금융·보험 업종 제외</li>
        <li>ROE 높은 순 · ROA 높은 순 정렬 상위 100위</li>
      </ul>
    </div>
  </div>
</div>

<?php
include("./include/menu_left.php");
include("./include/footer.php");
include("./include/menu_search.php");
?>
</div>
</body>
</html>
