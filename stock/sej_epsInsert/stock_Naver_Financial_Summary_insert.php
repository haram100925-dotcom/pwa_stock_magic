<?php
/**
 * 네이버 금융 재무제표 데이터를 크롤링하여 stock_daum_roe 테이블에 저장
 * 사용법: epsDaumSummaryInsert.php?stockMinNum=0&stockMaxNum=1000
 */
header('Content-Type: text/html;charset=utf-8');

$is_Insert = "Y"; // 데이터 저장 여부
$today = date("Ymd");
$startTime = date("Y-m-d H:i:s");
$stockMinNum = isset($_REQUEST['stockMinNum']) ? $_REQUEST['stockMinNum'] : 0;
$stockMaxNum = isset($_REQUEST['stockMaxNum']) ? $_REQUEST['stockMaxNum'] : 100;
$minkey = 0;

//DB접속
 include("/hosting/windmoter/html/stock/include/accsesDB.php");

//--------------------------------------
//1. 키 최소값 가져오기
//--------------------------------------
$query = "SELECT MIN(`key`) as minkey FROM stock_valuations";
$res = mysql_query($query, $conn);
if($res){
    $row = mysqli_fetch_assoc($res);
    $minkey = $row["minkey"];
    
    //--------------------------------------
    //2. 데이터 종목 코드 가져오기
    //--------------------------------------
    $query_roa = "SELECT isu_cd FROM stock_valuations WHERE `key` >= ".($minkey + $stockMinNum)." AND `key` < ".($minkey + $stockMaxNum);
    $res_roa = mysql_query($query_roa, $conn);
    $rowCount = 1;
    
    if($res_roa){
        while($row = mysqli_fetch_array($res_roa, MYSQLI_BOTH)){
            $cmp_cd = $row[0];
            $urlDomain = "http://wisefn.stock.daum.net/";
            $urlStk = "http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=".$cmp_cd."&finGubun=MAIN";
            
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $urlStk);
            curl_setopt($curl_handle, CURLOPT_HEADER, 0);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, $urlDomain);
            $json = curl_exec($curl_handle);
            curl_close($curl_handle);
            
            // changeFin과 changeFinData 추출
            $startNum = strrpos($json, "changeFin ");
            $endNum = strrpos($json, "Cmd_Financial");
            $detail_url = substr($json, $startNum, $endNum - $startNum);
            
            $endNum = strrpos($detail_url, "var");
            $changeFin = substr($detail_url, 12, $endNum - 14);
            $changeFin = json_decode(strip_tags($changeFin));
            
            $changeFinData = substr($detail_url, $endNum + 20, 10000);
            $changeFinData = json_decode(str_replace(";", "", strip_tags($changeFinData)));
            
            // 초기값 설정
            $roeDate = -9999999;
            $roaDate = -9999999;
            $shares_cnt = 0;
            $assets_ta = -9999999;
            $operating_pf = -9999999;
            $gpa = -9999999;
            $finance_cnt = 0;
            $eps = -9999999;
            $per = -9999999;
            $bps = -9999999;
            $pbr = -9999999; // PBR(배)
            $stk_dvd = -9999999; // 주당배당금(현금배당수익률)
            $dvd_yld = -9999999; // 현금배당성향(%)
            
            $lastAllDataCount = 0;
            $lastPutDataCount = 0;
            $lastDate = 0;
            
            if(null != $changeFinData && count($changeFinData) > 0){
                // 재무제표 중 영업이익 마이너스 개수 체크
                if(count($changeFinData[0][0]) > 0){
                    for($i=0; $i<count($changeFinData[0][0]); $i++){
                        $finance_cnt += substr_count(implode(' / ', $changeFinData[0][0][$i]), "-");
                    }
                }
                if(count($changeFinData[0][1]) > 0){
                    for($i=0; $i<count($changeFinData[0][1]); $i++){
                        $finance_cnt += substr_count(implode(' / ', $changeFinData[0][1][$i]), "-");
                    }
                }
                
                // ROE 구하기 - changeFinData[3][0][5] 또는 [3][1][5]
                if(count($changeFinData[3][0][5]) > 0){
                    for($i=0; $i<count($changeFinData[3][0][5]); $i++){
                        if($changeFinData[3][0][5][$i] != ""){
                            $roeDate = $changeFinData[3][0][5][$i];
                        }
                    }
                }
                if(count($changeFinData[3][1][5]) > 0){
                    for($i=0; $i<count($changeFinData[3][1][5]); $i++){
                        if($changeFinData[3][1][5][$i] != ""){
                            $roeDate = $changeFinData[3][1][5][$i];
                        }
                    }
                }
                
                if($roeDate != -9999999 && $roeDate != "ROE(%)"){
                    // ROA 구하기 - changeFinData[3][0][6] 또는 [3][1][6]
                    if(count($changeFinData[3][0][6]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][6]); $i++){
                            if($changeFinData[3][0][6][$i] != ""){
                                $roaDate = $changeFinData[3][0][6][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][6]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][6]); $i++){
                            if($changeFinData[3][1][6][$i] != ""){
                                $roaDate = $changeFinData[3][1][6][$i];
                            }
                        }
                    }
                    
                    // EPS 구하기 - changeFinData[3][0][9] 또는 [3][1][9]
                    if(count($changeFinData[3][0][9]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][9]); $i++){
                            if($changeFinData[3][0][9][$i] != ""){
                                $eps = $changeFinData[3][0][9][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][9]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][9]); $i++){
                            if($changeFinData[3][1][9][$i] != ""){
                                $eps = $changeFinData[3][1][9][$i];
                            }
                        }
                    }
                    
                    // PER 구하기 - changeFinData[3][0][10] 또는 [3][1][10]
                    if(count($changeFinData[3][0][10]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][10]); $i++){
                            if($changeFinData[3][0][10][$i] != ""){
                                $per = $changeFinData[3][0][10][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][10]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][10]); $i++){
                            if($changeFinData[3][1][10][$i] != ""){
                                $per = $changeFinData[3][1][10][$i];
                            }
                        }
                    }
                    
                    // BPS 구하기 - changeFinData[3][0][11] 또는 [3][1][11]
                    if(count($changeFinData[3][0][11]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][11]); $i++){
                            if($changeFinData[3][0][11][$i] != ""){
                                $bps = $changeFinData[3][0][11][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][11]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][11]); $i++){
                            if($changeFinData[3][1][11][$i] != ""){
                                $bps = $changeFinData[3][1][11][$i];
                            }
                        }
                    }
                    
                    // PBR(배) 구하기 - changeFinData[3][0][12] 또는 [3][1][12]
                    if(count($changeFinData[3][0][12]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][12]); $i++){
                            if($changeFinData[3][0][12][$i] != ""){
                                $pbr = $changeFinData[3][0][12][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][12]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][12]); $i++){
                            if($changeFinData[3][1][12][$i] != ""){
                                $pbr = $changeFinData[3][1][12][$i];
                            }
                        }
                    }
                    
                    // 주당배당금(현금배당수익률) 구하기 - changeFinData[3][0][14] 또는 [3][1][14]
                    if(count($changeFinData[3][0][14]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][14]); $i++){
                            if($changeFinData[3][0][14][$i] != ""){
                                $stk_dvd = $changeFinData[3][0][14][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][14]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][14]); $i++){
                            if($changeFinData[3][1][14][$i] != ""){
                                $stk_dvd = $changeFinData[3][1][14][$i];
                            }
                        }
                    }
                    
                    // 현금배당성향(dvd_yld) 구하기 - changeFinData[3][0][15] 또는 [3][1][15]
                    if(count($changeFinData[3][0][15]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][15]); $i++){
                            if($changeFinData[3][0][15][$i] != ""){
                                $dvd_yld = $changeFinData[3][0][15][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][15]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][15]); $i++){
                            if($changeFinData[3][1][15][$i] != ""){
                                $dvd_yld = $changeFinData[3][1][15][$i];
                            }
                        }
                    }
                    
                    // 발행주식수(보통주) - changeFinData[3][0][16] 또는 [3][1][16]
                    if(count($changeFinData[3][0][16]) > 0){
                        for($i=0; $i<count($changeFinData[3][0][16]); $i++){
                            if($changeFinData[3][0][16][$i] != ""){
                                $shares_cnt = $changeFinData[3][0][16][$i];
                            }
                        }
                    }
                    if(count($changeFinData[3][1][16]) > 0){
                        for($i=0; $i<count($changeFinData[3][1][16]); $i++){
                            if($changeFinData[3][1][16][$i] != ""){
                                $shares_cnt = $changeFinData[3][1][16][$i];
                            }
                        }
                    }
                    
                    // 자산총계 구하기 - changeFinData[1][0][0] 또는 [1][1][0]
                    if(count($changeFinData[1][0][0]) > 0){
                        for($i=0; $i<count($changeFinData[1][0][0]); $i++){
                            if($changeFinData[1][0][0][$i] != ""){
                                $assets_ta = $changeFinData[1][0][0][$i];
                            }
                        }
                    }
                    if(count($changeFinData[1][1][0]) > 0){
                        for($i=0; $i<count($changeFinData[1][1][0]); $i++){
                            if($changeFinData[1][1][0][$i] != ""){
                                $assets_ta = $changeFinData[1][1][0][$i];
                            }
                        }
                    }
                    
                    // 영업이익 구하기 - changeFinData[0][0][1] 또는 [0][1][1]
                    if(count($changeFinData[0][0][1]) > 0){
                        for($i=0; $i<count($changeFinData[0][0][1]); $i++){
                            $lastAllDataCount++;
                            if($changeFinData[0][0][1][$i] != ""){
                                $operating_pf = $changeFinData[0][0][1][$i];
                                $lastPutDataCount = $lastAllDataCount;
                            }
                        }
                    }
                    if(count($changeFinData[0][1][1]) > 0){
                        for($i=0; $i<count($changeFinData[0][1][1]); $i++){
                            $lastAllDataCount++;
                            if($changeFinData[0][1][1][$i] != ""){
                                $operating_pf = $changeFinData[0][1][1][$i];
                                $lastPutDataCount = $lastAllDataCount;
                            }
                        }
                    }
                    
                    // 데이터 정제
                    $roeDate = str_replace(",", "", $roeDate);
                    $roaDate = str_replace(",", "", $roaDate);
                    $shares_cnt = str_replace(",", "", $shares_cnt);
                    $assets_ta = str_replace(",", "", $assets_ta);
                    $operating_pf = str_replace(",", "", $operating_pf);
                    $eps = str_replace(",", "", $eps);
                    $per = str_replace(",", "", $per);
                    $bps = str_replace(",", "", $bps);
                    $pbr = str_replace(",", "", $pbr);
                    $stk_dvd = str_replace(",", "", $stk_dvd);
                    $dvd_yld = str_replace(",", "", $dvd_yld);
                    
                    // 빈 문자열 및 유효하지 않은 값 검증
                    if($roeDate === "" || !is_numeric($roeDate)) $roeDate = -9999999;
                    if($roaDate === "" || !is_numeric($roaDate)) $roaDate = -9999999;
                    if($shares_cnt === "" || !is_numeric($shares_cnt)) $shares_cnt = 0;
                    else $shares_cnt = $shares_cnt ; // 단위 조정
                    if($assets_ta === "" || !is_numeric($assets_ta)) $assets_ta = -9999999;
                    if($operating_pf === "" || !is_numeric($operating_pf)) $operating_pf = -9999999;
                    if($eps === "" || !is_numeric($eps)) $eps = -9999999;
                    if($per === "" || !is_numeric($per)) $per = -9999999;
                    if($bps === "" || !is_numeric($bps)) $bps = -9999999;
                    if($pbr === "" || !is_numeric($pbr)) $pbr = -9999999;
                    if($stk_dvd === "" || !is_numeric($stk_dvd)) $stk_dvd = -9999999;
                    if($dvd_yld === "" || !is_numeric($dvd_yld)) $dvd_yld = -9999999;
                    
                    // GPA 계산
                    if($assets_ta > 0 && $operating_pf != -9999999){
                        $gpa = sprintf("%2.3f", $operating_pf / $assets_ta * 100);
                    }
                }
                
                // 날짜 구하기
                if($lastPutDataCount > 0){
                    if($lastPutDataCount > 5){
                        $lastDate = str_replace("(IFRS연결)", "", str_replace("(IFRS별도)", "", $changeFin[1][$lastPutDataCount-6]));
                    }else{
                        $lastDate = str_replace("(IFRS연결)", "", str_replace("(IFRS별도)", "", $changeFin[0][$lastPutDataCount-2]));
                    }
                }
                
                //--------------------------------------
                //3. 데이터 저장
                //--------------------------------------
                if($lastDate != "" && $lastDate != "0"){
                    $query = "SELECT last_dt FROM stock_daum_roe WHERE isu_cd = '$cmp_cd'";
                    $res = mysql_query($query, $conn);
                    
                    if($res && mysqli_num_rows($res) > 0){
                        // UPDATE
                        $sql = "UPDATE stock_daum_roe SET
                                last_dt = '$lastDate',
                                roa = '$roaDate',
                                roe = '$roeDate',
                                assets_ta = '$assets_ta',
                                operating_pf = '$operating_pf',
                                gpa = '$gpa',
                                finance_cnt = '$finance_cnt',
                                shares_cnt = '$shares_cnt',
                                eps = '$eps',
                                per = '$per',
                                bps = '$bps',
                                pbr = '$pbr',
                                stk_dvd = '$stk_dvd',
                                dvd_yld = '$dvd_yld',
                                create_dt = NOW()
                                WHERE isu_cd = '$cmp_cd'";
                        mysql_query($sql, $conn);
                        echo "UPDATE: ".$cmp_cd." - ".$lastDate."<br>";
                    }else{
                        // INSERT
                        $sql = "INSERT INTO stock_daum_roe 
                                (isu_cd, last_dt, roa, roe, assets_ta, operating_pf, gpa, finance_cnt, shares_cnt, eps, per, bps, pbr, stk_dvd, dvd_yld)
                                VALUES ('$cmp_cd', '$lastDate', '$roaDate', '$roeDate', '$assets_ta', '$operating_pf', '$gpa', '$finance_cnt', '$shares_cnt', '$eps', '$per', '$bps', '$pbr', '$stk_dvd', '$dvd_yld')";
                        mysql_query($sql, $conn);
                        echo "INSERT: ".$cmp_cd." - ".$lastDate."<br>";
                    }
                }
            }
        }
    }
}

echo "완료: ".$startTime." ~ ".date("Y-m-d H:i:s");
?>
