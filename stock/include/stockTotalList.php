<?php
// 전종목 검색 시 html 떨굴 데이터
include("./accsesDB.php");

$query = "select market_gubun, isu_cd, isu_nm from stock_valuations";
$res = mysql_query($query, $conn);

if ($res) {
    while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {
        $marketLabel = '<span class="ft-market-kospi" style="font-size:12px;">코넥스</span>';
        if ($row[0] == "STK") {
            $marketLabel = '<span class="ft-market-kospi" style="font-size:12px;">코스피</span>';
        } elseif ($row[0] == "KSQ") {
            $marketLabel = '<span class="ft-market-kosdaq" style="font-size:12px;">코스닥</span>';
        }
        $codeEsc = htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8');
        $nameRaw = rawurlencode($row[2]);
        $nameEsc = htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8');
        ?>
        <li onclick="(window.stockDitalMove || parent.stockDitalMove)('<?php echo $codeEsc; ?>', '<?php echo $nameRaw; ?>')">
          <div style="display:flex;align-items:center;justify-content:space-between;width:100%;gap:10px;">
            <span style="font-weight:600;color:#191f28;"><?php echo $nameEsc; ?></span>
            <?php echo $marketLabel; ?>
          </div>
        </li>
        <?php
    }
}
?>