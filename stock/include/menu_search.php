<script>
// 종목 상세 이동
function stockDitalMove(isNo, isNm) {
  location.href = "https://cronya.pe.kr/stock/stockDetail.php?isNo=" + isNo + "&isNm=" + isNm;
}

// 전체 종목 검색 데이터 최초 1회 로드
var isStockDateTotal = true;
function stockDateTotal() {
  if (isStockDateTotal) {
    $.ajax({
      type: 'post',
      dataType: 'text',
      url: 'https://cronya.pe.kr/stock/include/stockTotalList.php',
      beforeSend: function () {
        $('#stockViewTotal').html('<li style="justify-content:center;border:none;"><div class="ft-loader"></div></li>');
      },
      success: function (data) {
        $('#stockViewTotal').html(data);
        isStockDateTotal = false;
        var input = document.getElementById('ftGlobalSearchInput');
        if (input && input.value.trim()) {
          input.dispatchEvent(new Event('input'));
        }
      },
      error: function () {
        $('#stockViewTotal').html('<li style="color:#8b95a1;border:none;">종목 데이터를 불러오지 못했습니다.</li>');
      }
    });
  }
}
</script>

<aside id="ftSearchDrawer" class="ft-drawer ft-drawer-right" aria-hidden="true" aria-label="전체 종목 검색">
  <div class="ft-drawer-head">
    <span class="ft-drawer-title">종목 검색</span>
    <button type="button" class="ft-icon-btn" data-ft-close-drawer aria-label="검색 닫기">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M18 6 6 18M6 6l12 12"></path>
      </svg>
    </button>
  </div>

  <div class="ft-drawer-body">
    <div class="ft-search-box" style="margin-bottom:0;">
      <svg class="ft-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
      </svg>
      <label for="ftGlobalSearchInput" class="ui-hidden-accessible">종목 이름 또는 코드로 검색</label>
      <input id="ftGlobalSearchInput" type="search" class="ft-search-input" placeholder="종목명 또는 시장명 입력.." autocomplete="off" />
    </div>

    <ul id="stockViewTotal" class="ft-search-results" aria-live="polite">
      <!-- AJAX loaded stock list -->
    </ul>
    <div id="ftGlobalSearchEmpty" class="ft-empty-state">검색 결과가 없습니다.</div>
  </div>

  <div class="ft-drawer-foot">
    광고문의: windmoter@gmail.com
  </div>

  <iframe id="iframeSearch" title="종목 검색 프레임" style="display:none; width:100%; height:100%; border:none;"></iframe>
</aside>