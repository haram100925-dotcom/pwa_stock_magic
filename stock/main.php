<?php
header('Content-Type: text/html;charset=utf-8');
include("./include/header.php");
?>

<main class="ft-main" role="main">
  <div class="ft-meta-bar">
    <span class="ft-meta-bar-title">가치투자 전략 대시보드</span>
    <span class="ft-meta-bar-date">기준일 <?php echo htmlspecialchars($updateViewDay, ENT_QUOTES, 'UTF-8'); ?></span>
  </div>

  <!-- Top 2-Column Strategy Grid -->
  <div class="ft-grid-2" role="region" aria-label="핵심 가치투자 전략">
    <!-- 1. 신 마법공식 -->
    <section class="ft-card">
      <div class="ft-card-head">
        <div>
          <div class="ft-card-kicker">PBR 낮은 순 · GP/A 높은 순</div>
          <h2 class="ft-card-title"><span class="ft-market-kospi">신</span> 마법공식 Top 100</h2>
        </div>
        <button type="button" class="ft-help-btn" data-ft-modal="modalNewMagic" aria-label="신 마법공식 기준 보기">기준 ?</button>
      </div>

      <div class="ft-action-list">
        <a href="/stock/magicNewList.php" class="ft-action-row">
          <span class="ft-action-label">전 종목 Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PBR↓ · GP/A↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/magicNewList.php?type=1" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kospi">코스피</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PBR↓ · GP/A↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/magicNewList.php?type=2" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kosdaq">코스닥</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PBR↓ · GP/A↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
      </div>

      <div class="ft-link-list">
        <a href="https://now24.tistory.com/123" target="_blank" rel="noopener" class="ft-ext-link">
          <span><span class="ft-ext-badge">분석</span>신 마법공식 TOP 10 종목분석 11월</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <a href="https://now24.tistory.com/110" target="_blank" rel="noopener" class="ft-ext-link">
          <span><span class="ft-ext-badge">리포트</span>추천종목 년도별 수익률 (2023~2026)</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      </div>
    </section>

    <!-- 2. 벤저민 그레이엄 -->
    <section class="ft-card">
      <div class="ft-card-head">
        <div>
          <div class="ft-card-kicker">PER ≤ 15 · PBR &lt; 1.5 · 안전마진</div>
          <h2 class="ft-card-title">벤저민 그레이엄 (가치투자)</h2>
        </div>
        <button type="button" class="ft-help-btn" data-ft-modal="modalGraham" aria-label="그레이엄 가치투자 기준 보기">기준 ?</button>
      </div>

      <div class="ft-highlight-strip">
        <div>
          <div class="ft-highlight-title">추천 공식 중 누적 최고수익</div>
          <div class="ft-highlight-sub">추천 종목 수익률 분석 (2019–2025)</div>
        </div>
        <a href="https://now24.tistory.com/112" target="_blank" rel="noopener" class="ft-btn-sm">성과 보기</a>
      </div>

      <div class="ft-action-list">
        <a href="/stock/contrarianList.php" class="ft-action-row">
          <span class="ft-action-label">전 종목 Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PBR↓ · PER↓ · 배당↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/contrarianList.php?type=1" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kospi">코스피</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">가치투자</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/contrarianList.php?type=2" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kosdaq">코스닥</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">가치투자</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
      </div>
    </section>
  </div>

  <!-- Middle Ad Slot -->
  <div class="ft-ad-slot">
    <?php include($mainRoot . "/stock/include/AD_320_100.php"); ?>
  </div>

  <!-- Second 2-Column Strategy Grid -->
  <div class="ft-grid-2" role="region" aria-label="추가 가치투자 전략">
    <!-- 3. 피터 린치 -->
    <section class="ft-card">
      <div class="ft-card-head">
        <div>
          <div class="ft-card-kicker">ROE 높은 순 · ROA 높은 순</div>
          <h2 class="ft-card-title">피터 린치 (이기는 투자)</h2>
        </div>
        <button type="button" class="ft-help-btn" data-ft-modal="modalLynch" aria-label="피터 린치 기준 보기">기준 ?</button>
      </div>

      <div class="ft-action-list">
        <a href="/stock/winList.php" class="ft-action-row">
          <span class="ft-action-label">전 종목 Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">ROA↑ · ROE↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/winList.php?type=1" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kospi">코스피</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">성장가치</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/winList.php?type=2" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kosdaq">코스닥</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">성장가치</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
      </div>

      <div class="ft-link-list">
        <a href="https://now24.tistory.com/111" target="_blank" rel="noopener" class="ft-ext-link">
          <span><span class="ft-ext-badge">성과</span>피터 린치 이기는 투자 3년 실전 누적 성과</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      </div>
    </section>

    <!-- 4. 기존 마법공식 -->
    <section class="ft-card">
      <div class="ft-card-head">
        <div>
          <div class="ft-card-kicker">PER 낮은 순 · ROA 높은 순</div>
          <h2 class="ft-card-title">마법공식 Top 100</h2>
        </div>
        <button type="button" class="ft-help-btn" data-ft-modal="modalMagic" aria-label="마법공식 기준 보기">기준 ?</button>
      </div>

      <div class="ft-action-list">
        <a href="/stock/magicList.php" class="ft-action-row">
          <span class="ft-action-label">전 종목 Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PER↓ · ROA↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/magicList.php?type=1" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kospi">코스피</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PER↓ · ROA↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
        <a href="/stock/magicList.php?type=2" class="ft-action-row">
          <span class="ft-action-label"><span class="ft-market-kosdaq">코스닥</span> Top 100</span>
          <span style="display:flex;align-items:center;gap:6px;">
            <span class="ft-action-meta">PER↓ · ROA↑</span>
            <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </span>
        </a>
      </div>
    </section>
  </div>

  <!-- Market Gauge Chart Card -->
  <section class="ft-card ft-section-gap" aria-label="시장 공포탐욕 지수">
    <div class="ft-card-head" style="margin-bottom:8px;">
      <div>
        <div class="ft-card-kicker">실시간 시장 심리</div>
        <h2 class="ft-card-title">코스피 공포·탐욕 지수</h2>
      </div>
    </div>
    <iframe title="코스피 공포탐욕 지수 차트" class="ft-chart-iframe" scrolling="no" src="./include/amcharts.html"></iframe>
  </section>

  <!-- Discovery & Crypto Insights Grid -->
  <div class="ft-grid-2 ft-section-gap">
    <section class="ft-card">
      <div class="ft-card-head">
        <div>
          <div class="ft-card-kicker">수급 · 펀더멘털 스크리닝</div>
          <h2 class="ft-card-title">종목발굴 투자</h2>
        </div>
        <button type="button" class="ft-help-btn" data-ft-modal="modalDiscovery" aria-label="종목발굴 방식 보기">안내 ?</button>
      </div>

      <div class="ft-action-list">
        <a href="/stock/resultStock/samForeignerResult.php" class="ft-action-row">
          <span class="ft-action-label">외국인 5일 순매수</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <a href="/stock/recommendEps/allocationList.php" class="ft-action-row">
          <span class="ft-action-label">외국인 지분증가 상위 Top 15</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <a href="/stock/recommendEps/roeTopList.php" class="ft-action-row">
          <span class="ft-action-label">자기자본이익률(ROE) 상위</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <a href="/stock/contrarianList.php" class="ft-action-row">
          <span class="ft-action-label">그레이엄 가치투자 (PER↓ · PBR↓ · 배당↑)</span>
          <svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      </div>
    </section>

    <section class="ft-card">
      <div class="ft-card-head">
        <div>
          <div class="ft-card-kicker">글로벌 매크로 · 코인 동향</div>
          <h2 class="ft-card-title">비트코인 및 해외 금융</h2>
        </div>
      </div>

      <div class="ft-link-list" style="margin-top:0;">
        <?php
        function protocalHttpWeb($urlDomain, $urlStk) {
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
            curl_setopt($curl_handle, CURLOPT_HEADER, 0);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, $urlDomain);
            $json = curl_exec($curl_handle);
            curl_close($curl_handle);
            return $json;
        }
        function tagListData($strTag, $content) {
            $reg = sprintf("/\<%s[^>]*\>(.*?)\<\/%s\>/is", $strTag, $strTag);
            preg_match_all($reg, $content, $matches, PREG_PATTERN_ORDER);
            return $matches[1];
        }

        $urlDomain = "https://now24.tistory.com";
        $urlStk = "https://now24.tistory.com/category/%ED%95%B4%EC%99%B8%20%EA%B8%88%EC%9A%A9%20%EA%B2%BD%EC%A0%9C";
        $json = protocalHttpWeb($urlDomain, $urlStk);

        $renderedLinks = 0;
        if ($json) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($json, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();
            $xpath = new DOMXPath($dom);
            $listItems = $xpath->query('//div[@id="body" and @class="list"]//li');

            if ($listItems) {
                foreach ($listItems as $item) {
                    $links = $item->getElementsByTagName('a');
                    foreach ($links as $link) {
                        $href = htmlspecialchars($urlDomain . $link->getAttribute('href'), ENT_QUOTES, 'UTF-8');
                        $text = htmlspecialchars(trim($link->nodeValue), ENT_QUOTES, 'UTF-8');
                        if ($text !== '') {
                            echo '<a href="' . $href . '" target="_blank" rel="noopener" class="ft-ext-link"><span>' . $text . '</span><svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></a>';
                            $renderedLinks++;
                        }
                    }
                }
            }
        }
        if ($renderedLinks === 0) {
            echo '<a href="' . $urlStk . '" target="_blank" rel="noopener" class="ft-ext-link"><span>해외 금융·가상자산 최신 글 바로가기</span><svg class="ft-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></a>';
        }
        ?>
      </div>
    </section>
  </div>
</main>

<!-- Strategy Modals -->
<div id="ftModalOverlay-modalNewMagic" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="titleNewMagic">
    <div class="ft-modal-header">
      <h3 id="titleNewMagic" class="ft-modal-title">신 마법공식 선정 기준</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>시가총액 500억 이상 · 상장주식수 1,500만 주 이상</li>
        <li>배당수익률 0% 이상 · PBR 100배 이하</li>
        <li>금융·보험 업종 및 관리·관심종목 제외</li>
        <li>GP/A = 영업이익 / 자산총계</li>
        <li>PBR 낮은 순 + GP/A 높은 순 합산 상위 100위 선정</li>
      </ul>
    </div>
  </div>
</div>

<div id="ftModalOverlay-modalGraham" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="titleGraham">
    <div class="ft-modal-header">
      <h3 id="titleGraham" class="ft-modal-title">벤저민 그레이엄 가치투자 기준</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>BPS 5,000원 이상 · 시가총액 170.5억 이상 · 상장주식수 1,000만 주 이상</li>
        <li>PER 15 이하 · PBR 1.5 미만</li>
        <li>PBR × PER &lt; 22 이하 유지</li>
        <li>PBR · PER 낮은 순, 배당수익률 높은 순 정렬</li>
      </ul>
    </div>
  </div>
</div>

<div id="ftModalOverlay-modalLynch" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="titleLynch">
    <div class="ft-modal-header">
      <h3 id="titleLynch" class="ft-modal-title">피터 린치 (이기는 투자) 기준</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>상장주식수 1,500만 주 이상 · 배당수익률 0% 이상</li>
        <li>PER / EPS 증가율 &lt; 1 · PER / 배당수익률 &lt; 4</li>
        <li>금융·보험 업종 제외</li>
        <li>ROA · ROE 우수 기업 상위 100위 선정</li>
      </ul>
    </div>
  </div>
</div>

<div id="ftModalOverlay-modalMagic" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="titleMagic">
    <div class="ft-modal-header">
      <h3 id="titleMagic" class="ft-modal-title">조엘 그린블라트 마법공식 전략</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>1단계: 1년 동안 투자할 목표 자금을 설정합니다.</li>
        <li>2단계: PER↓ · ROA↑ 합산 순위 상위 종목을 확인합니다.</li>
        <li>3단계: 자금의 20~33%로 최상위 5~7개 종목을 분산 매수합니다.</li>
        <li>4단계: 2~3개월마다 반복해 20~30개 포트폴리오를 구축합니다.</li>
        <li>5단계: 개별 종목은 1년 보유 후 리밸런싱합니다.</li>
      </ul>
    </div>
  </div>
</div>

<div id="ftModalOverlay-modalDiscovery" class="ft-modal-overlay" aria-hidden="true">
  <div class="ft-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="titleDiscovery">
    <div class="ft-modal-header">
      <h3 id="titleDiscovery" class="ft-modal-title">종목발굴 투자 방식</h3>
      <button type="button" class="ft-icon-btn" data-ft-close-modal aria-label="닫기">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="ft-modal-body">
      <ul>
        <li>1단계: 투자 전략에 맞는 수급·재무 발굴 항목을 선택합니다.</li>
        <li>2단계: 과거 상위 종목과 현재 시세 변화를 비교 검증합니다.</li>
        <li>3단계: 반복되는 패턴을 기준으로 최종 투자 종목을 선정합니다.</li>
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