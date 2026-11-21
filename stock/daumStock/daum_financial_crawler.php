<?php
/**
 * 다움 증권 재무정보 크롤링 클래스
 * URL: http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=005930&finGubun=
 */

class DaumFinancialCrawler {
    private $isNo;
    private $baseUrl = 'http://wisefn.stock.daum.net/v1/company/cF1001.aspx';
    private $debug = false;
    private $lastHtml = '';
    
    public function __construct($isNo, $debug = false) {
        $this->isNo = $isNo;
        $this->debug = (bool)$debug;
    }
    
    /**
     * 다움 증권 API에서 재무정보 크롤링
     */
    public function crawlFinancialData() {
        // MAIN을 지정해야 표 본문 데이터가 채워지는 경우가 있어 명시
        $url = $this->baseUrl . '?cmp_cd=' . $this->isNo . '&finGubun=MAIN';
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($curl_handle, CURLOPT_REFERER, 'http://wisefn.stock.daum.net/');
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl_handle, CURLOPT_ENCODING, ''); // gzip/deflate 허용
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Connection: keep-alive'
        ]);
        
        $html = curl_exec($curl_handle);
        $httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        $this->lastHtml = $html;
        
        $parsed = $this->parseFinancialData($html);
        // 비어있으면 스크립트에서 날짜라도 추출 (var changeFin)
        if ($this->isAllEmpty($parsed)) {
            $scriptParsed = $this->parseDatesFromScripts($html);
            if (!empty($scriptParsed['dates'])) {
                // 모든 블록에 동일한 dates 설정
                $parsed = [
                    'enterprise_value' => ['dates' => $scriptParsed['dates']],
                    'dividend' => ['dates' => $scriptParsed['dates']],
                    'stability' => ['dates' => $scriptParsed['dates']],
                    'profitability' => ['dates' => $scriptParsed['dates']],
                    'revenue' => ['dates' => $scriptParsed['dates']]
                ];
            }
        }
        return $parsed;
    }
    
    /**
     * HTML에서 재무정보 파싱
     */
    private function parseFinancialData($html) {
        $data = array();
        
        // HTML 파싱을 위한 DOMDocument 사용
        $dom = new DOMDocument();
        // 문자셋 감지 후 UTF-8로 정규화
        $charset = $this->detectCharset($html);
        if ($charset !== 'UTF-8') {
            $normalized = @iconv($charset, 'UTF-8//IGNORE', $html);
            if ($normalized && strlen($normalized) > 0) {
                $this->lastHtml = $normalized;
                @$dom->loadHTML(mb_convert_encoding($normalized, 'HTML-ENTITIES', 'UTF-8'));
            } else {
                $this->lastHtml = $html;
                @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            }
        } else {
            $this->lastHtml = $html;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        }
        $xpath = new DOMXPath($dom);
        if ($this->debug) {
            $table = $this->getMainTable($xpath);
            if ($table) {
                $this->debugEcho("<h4>#cTB26 outerHTML (first 2000 chars)</h4><pre>" . htmlspecialchars(substr($dom->saveHTML($table),0,2000)) . "...</pre>");
            } else {
                $this->debugEcho("<div style='color:red;'>#cTB26 테이블을 찾지 못했습니다.</div>");
            }
            $hasChangeFin = preg_match('/var\s+changeFin\s*=\s*\[/', $this->lastHtml) ? 'Y' : 'N';
            $hasChangeQuarter = preg_match('/var\s+changeFinData\s*=\s*\[/', $this->lastHtml) ? 'Y' : 'N';
            $this->debugEcho("<div>changeFin 존재: {$hasChangeFin}, changeQuarter 존재: {$hasChangeQuarter}</div>");
        }
        
        // 우선 스크립트 구조에서 직접 파싱 (가장 신뢰도 높음)
        $scriptParsed = $this->parseFromChangeFinStructures();
        
        // 1. 기업가치 데이터 (PER, PBR, ROA)
        $data['enterprise_value'] = $this->parseEnterpriseValue($xpath);
        if (empty($data['enterprise_value']['dates']) && !empty($scriptParsed['dates'])) {
            $data['enterprise_value']['dates'] = $scriptParsed['dates'];
        }
        if (empty($data['enterprise_value']['per']) && !empty($scriptParsed['series']['PER(배)'])) {
            $data['enterprise_value']['per'] = $scriptParsed['series']['PER(배)'];
        }
        if (empty($data['enterprise_value']['pbr']) && !empty($scriptParsed['series']['PBR(배)'])) {
            $data['enterprise_value']['pbr'] = $scriptParsed['series']['PBR(배)'];
        }
        if (empty($data['enterprise_value']['roa']) && !empty($scriptParsed['series']['ROA(%)'])) {
            $data['enterprise_value']['roa'] = $scriptParsed['series']['ROA(%)'];
        }
        
        // 2. 배당 데이터
        $data['dividend'] = $this->parseDividendData($xpath);
        if (empty($data['dividend']['dates']) && !empty($scriptParsed['dates'])) {
            $data['dividend']['dates'] = $scriptParsed['dates'];
        }
        if (empty($data['dividend']['cash_dividend_payout_ratio']) && !empty($scriptParsed['series']['현금배당성향(%)'])) {
            $data['dividend']['cash_dividend_payout_ratio'] = $scriptParsed['series']['현금배당성향(%)'];
        }
        if (empty($data['dividend']['cash_dividend_yield']) && !empty($scriptParsed['series']['현금배당수익률'])) {
            $data['dividend']['cash_dividend_yield'] = $scriptParsed['series']['현금배당수익률'];
        }
        
        // 3. 안정성 데이터 (부채비율, 순이익율)
        $data['stability'] = $this->parseStabilityData($xpath);
        if (empty($data['stability']['dates']) && !empty($scriptParsed['dates'])) {
            $data['stability']['dates'] = $scriptParsed['dates'];
        }
        if (empty($data['stability']['debt_ratio']) && !empty($scriptParsed['series']['부채비율'])) {
            $data['stability']['debt_ratio'] = $scriptParsed['series']['부채비율'];
        }
        if (empty($data['stability']['net_profit_margin']) && !empty($scriptParsed['series']['순이익률'])) {
            $data['stability']['net_profit_margin'] = $scriptParsed['series']['순이익률'];
        }
        
        // 4. 이익율 데이터 (영업이익율, 순이익율)
        $data['profitability'] = $this->parseProfitabilityData($xpath);
        if (empty($data['profitability']['dates']) && !empty($scriptParsed['dates'])) {
            $data['profitability']['dates'] = $scriptParsed['dates'];
        }
        if (empty($data['profitability']['operating_profit_margin']) && !empty($scriptParsed['series']['영업이익률'])) {
            $data['profitability']['operating_profit_margin'] = $scriptParsed['series']['영업이익률'];
        }
        if (empty($data['profitability']['net_profit_margin']) && !empty($scriptParsed['series']['순이익률'])) {
            $data['profitability']['net_profit_margin'] = $scriptParsed['series']['순이익률'];
        }
        
        // 5. 수익성 데이터 (매출액, 영업이익(발표기준), ROE)
        $data['revenue'] = $this->parseRevenueData($xpath);
        if (empty($data['revenue']['dates']) && !empty($scriptParsed['dates'])) {
            $data['revenue']['dates'] = $scriptParsed['dates'];
        }
        if (empty($data['revenue']['revenue']) && !empty($scriptParsed['series']['매출액'])) {
            $data['revenue']['revenue'] = $scriptParsed['series']['매출액'];
        }
        if (empty($data['revenue']['operating_profit_announced']) && !empty($scriptParsed['series']['영업이익(발표기준)'])) {
            $data['revenue']['operating_profit_announced'] = $scriptParsed['series']['영업이익(발표기준)'];
        }
        if (empty($data['revenue']['roe']) && !empty($scriptParsed['series']['ROE(%)'])) {
            $data['revenue']['roe'] = $scriptParsed['series']['ROE(%)'];
        }
        
        return $data;
    }

    private function debugEcho($html) {
        if ($this->debug) {
            echo $html;
        }
    }

    /**
     * HTML 내 메타/내용을 기준으로 문자셋 추정
     */
    private function detectCharset($html) {
        if (!is_string($html) || $html === '') return 'UTF-8';
        if (preg_match('/<meta[^>]*charset\s*=\s*"?([a-zA-Z0-9\-]+)"?/i', $html, $m)) {
            return strtoupper($m[1]);
        }
        if (preg_match('/<meta[^>]*http-equiv\s*=\s*"content-type"[^>]*content\s*=\s*"[^"]*charset=([a-zA-Z0-9\-]+)"/i', $html, $m)) {
            return strtoupper($m[1]);
        }
        $sample = substr($html, 0, 4000);
        $utf8 = @mb_convert_encoding($sample, 'UTF-8', 'UTF-8');
        if ($utf8 !== false && preg_match('/Ã|¼|½|¾|¿|Â||||/', $utf8)) {
            return 'EUC-KR';
        }
        return 'UTF-8';
    }

    /**
     * 메인 재무 테이블(cTB26) 반환
     */
    private function getMainTable($xpath) {
        $nodeList = $xpath->query('//*[@id="cTB26"]');
        if ($nodeList && $nodeList->length > 0) {
            return $nodeList->item(0);
        }
        return null;
    }

    /**
     * 헤더의 연/분기 날짜 라벨 추출 (row1의 th.sub)
     */
    private function extractHeaderDates($xpath, $table) {
        $dates = [];
        if (!$table) return $dates;
        $ths = $xpath->query('.//tr[contains(@class, "row1")]/th[contains(@class, "sub")]', $table);
        foreach ($ths as $th) {
            $text = trim($th->textContent);
            // 괄호 설명 제거, 숫자/./만 남김, / → .
            $text = preg_replace('/\s*\(.*?\)\s*/', '', $text);
            $text = str_replace('/', '.', $text);
            $text = preg_replace('/[^0-9\.]/', '', $text);
            if ($text !== '') $dates[] = $text;
        }
        return $dates;
    }

    /**
     * changeFin 등 스크립트 블록에서 라벨에 해당하는 숫자 시퀀스 추출
     */
    private function extractSeriesFromScriptsByLabel($label, $expectedCount) {
        if (!is_string($this->lastHtml) || $this->lastHtml === '') return [];
        // changeFin 또는 changeQuarter 블록 스캔
        $block = '';
        if (preg_match('/var\s+changeFin\s*=\s*(\[.*?\]);/s', $this->lastHtml, $m)) {
            $block = $m[1];
        } elseif (preg_match('/var\s+changeFinData\s*=\s*(\[.*?\]);/s', $this->lastHtml, $m)) {
            $block = $m[1];
        } else {
            // 스크립트 블록이 안 보이면 HTML 전체에서 라벨 포함 라인으로 시도
            $block = $this->lastHtml;
        }
        // DEBUG: changeFin 샘플/길이/라벨 목록 출력
        if ($this->debug) {
            $this->debugEcho("<div>changeFin block length: " . strlen($block) . "</div>");
            $this->debugEcho("<pre style=\"white-space:pre; max-height:800px; overflow:auto; border:1px dashed #ccc;\">" . htmlspecialchars(substr($block, 0, 200000)) . "</pre>");
        }

        // 0) 정규식으로 JS 객체 내 라벨/값 배열 직접 추출 시도
        $labelNoSpace = trim(preg_replace('/\s+/', '', $label));
        $labelPattern = preg_quote($labelNoSpace, '/');
        $re = '/\{[^\}]*?(?:n|name|title)\s*:\s*[\'\"]?([^\'\"\}]+)[\'\"]?[^\}]*?(?:data|values|ds)\s*:\s*\[([^\]]*)\]/s';
        if (preg_match_all($re, $block, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $entry) {
                $lblRaw = trim(preg_replace('/\s+/', '', $entry[1]));
                if ($lblRaw === '' || !preg_match('/' . $labelPattern . '/u', $lblRaw)) continue;
                $arrBody = $entry[2];
                if (preg_match_all('/-?\d{1,3}(?:,\d{3})*(?:\.\d+)?|-?\d+(?:\.\d+)?/', $arrBody, $numsM)) {
                    $nums = array_map(function($s){ return floatval(str_replace(',', '', $s)); }, $numsM[0]);
                    if (!empty($nums)) {
                        if ($expectedCount > 0 && count($nums) >= $expectedCount) {
                            $nums = array_slice($nums, -$expectedCount);
                        }
                        return $nums;
                    }
                }
            }
        }
        // 1) 먼저 JSON으로 파싱 시도
        $decoded = json_decode($block, true);
        if (is_array($decoded) && !empty($decoded)) {
            // 각 항목에서 label 키 유사 필드(n,name,title)와 값 배열(data,values,ds) 탐색
            foreach ($decoded as $entry) {
                if (!is_array($entry)) continue;
                $lbl = $entry['n'] ?? ($entry['name'] ?? ($entry['title'] ?? ''));
                if (!is_string($lbl)) $lbl = '';
                $lblNoSpace = trim(preg_replace('/\s+/', '', $lbl));
                if ($lblNoSpace === '' || mb_strpos($lblNoSpace, trim(preg_replace('/\s+/', '', $label))) === false) continue;
                $series = $entry['data'] ?? ($entry['values'] ?? ($entry['ds'] ?? null));
                if (is_array($series)) {
                    $nums = [];
                    foreach ($series as $v) {
                        if (is_string($v)) { $v = floatval(str_replace(',', '', $v)); }
                        if (is_numeric($v)) { $nums[] = floatval($v); }
                    }
                    if (!empty($nums)) {
                        if ($expectedCount > 0 && count($nums) >= $expectedCount) {
                            $nums = array_slice($nums, -$expectedCount);
                        }
                        return $nums;
                    }
                }
            }
        }
        // 해당 라벨이 포함된 JSON 조각 라인만 추출
        $lines = preg_split('/\n|\r/', $block);
        $joined = [];
        foreach ($lines as $ln) {
            if (mb_strpos($ln, $label) !== false) {
                $joined[] = $ln;
            }
        }
        $text = implode("\n", $joined);
        if ($text === '') $text = $block; // 라벨 라인 분리 실패 시 블록 전체에서 수치 추출 시도
        // 숫자 추출 (천단위 콤마, 음수, 소수 허용)
        if (!preg_match_all('/-?\d{1,3}(?:,\d{3})*(?:\.\d+)?|-?\d+(?:\.\d+)?/', $text, $mm)) {
            return [];
        }
        $nums = [];
        foreach ($mm[0] as $raw) {
            $v = floatval(str_replace(',', '', $raw));
            $nums[] = $v;
        }
        if (empty($nums)) return [];
        // 뒤에서 expectedCount개를 취함 (최근 연/분기 컬럼 순으로 가정)
        if ($expectedCount > 0 && count($nums) >= $expectedCount) {
            $nums = array_slice($nums, -$expectedCount);
        }
        return $nums;
    }

    /**
     * changeFin/changeFinData를 직접 파싱하여 필요한 지표 시리즈 구성
     */
    private function parseFromChangeFinStructures() {
        $out = [
            'dates' => [],
            'series' => []
        ];
        if (!is_string($this->lastHtml) || $this->lastHtml === '') return $out;

        // 1) changeFin → 날짜
        $dates = [];
        if (preg_match('/var\s+changeFin\s*=\s*(\[.*?\]);/s', $this->lastHtml, $m)) {
            $raw = $m[1];
            $arr = json_decode($raw, true);
            if (is_array($arr) && count($arr) >= 2) {
                $annual = is_array($arr[0]) ? $arr[0] : [];
                $quarter = is_array($arr[1]) ? $arr[1] : [];
                $cleanup = function($s){
                    $s = strip_tags($s);
                    $s = trim($s);
                    $s = str_replace('/', '.', $s);
                    // YYYY 또는 YYYY.MM 형태만 남김
                    if (preg_match('/^(\d{4})(?:\.(\d{2}))?$/', $s, $mm)) {
                        return isset($mm[2]) ? $mm[1].'.'.$mm[2] : $mm[1];
                    }
                    return $s;
                };
                foreach ($annual as $d) { $dates[] = $cleanup($d); }
                foreach ($quarter as $d) { $dates[] = $cleanup($d); }
            }
        }
        $out['dates'] = $dates;

        // 2) changeFinData → 시리즈
        if (!preg_match('/var\s+changeFinData\s*=\s*(\[.*?\]);/s', $this->lastHtml, $m)) {
            return $out;
        }
        $raw = $m[1];
        $data = json_decode($raw, true);
        if (!is_array($data)) return $out;

        // helper: 라벨 부분일치로 series 찾기 (annual 0번 배열의 라벨 기준, 같은 index의 quarterly 사용)
        $findSeries = function(array $group, string $labelContains) {
            if (!is_array($group) || count($group) < 2) return [];
            $annualRows = $group[0];
            $quarterRows = $group[1];
            if (!is_array($annualRows) || !is_array($quarterRows)) return [];
            foreach ($annualRows as $idx => $row) {
                if (!is_array($row) || count($row) < 2) continue;
                $label = (string)$row[0];
                if (mb_strpos($label, $labelContains) === false) continue;
                $annualVals = [];
                for ($i = 1; $i < count($row); $i++) {
                    $annualVals[] = floatval(str_replace(',', '', (string)$row[$i]));
                }
                $quarterVals = [];
                if (isset($quarterRows[$idx]) && is_array($quarterRows[$idx])) {
                    foreach ($quarterRows[$idx] as $v) {
                        $quarterVals[] = floatval(str_replace(',', '', (string)$v));
                    }
                }
                return array_merge($annualVals, $quarterVals);
            }
            return [];
        };

        // group mapping based on provided structure
        // group0: 매출/영업이익/영업이익(발표기준)...
        // group3: 각종 비율, EPS/PER/BPS/PBR/배당 등
        $g0 = $data[0] ?? null;
        $g3 = $data[3] ?? null;

        // Build series we need and store in keys
        $out['series']['매출액'] = $g0 ? $findSeries($g0, '매출액') : [];
        $out['series']['영업이익(발표기준)'] = $g0 ? $findSeries($g0, '영업이익(발표기준)') : [];
        $out['series']['PER(배)'] = $g3 ? $findSeries($g3, 'PER') : [];
        $out['series']['PBR(배)'] = $g3 ? $findSeries($g3, 'PBR') : [];
        $out['series']['ROA(%)'] = $g3 ? $findSeries($g3, 'ROA') : [];
        $out['series']['ROE(%)'] = $g3 ? $findSeries($g3, 'ROE') : [];
        $out['series']['현금배당성향(%)'] = $g3 ? $findSeries($g3, '현금배당성향') : [];
        $out['series']['현금배당수익률'] = $g3 ? $findSeries($g3, '현금배당수익률') : [];
        $out['series']['부채비율'] = $g3 ? $findSeries($g3, '부채비율') : [];
        $out['series']['영업이익률'] = $g3 ? $findSeries($g3, '영업이익률') : [];
        $out['series']['순이익률'] = $g3 ? $findSeries($g3, '순이익률') : [];

        return $out;
    }
    /**
     * HTML 원문에서 특정 라벨 행의 숫자값들을 정규식으로 추출 (DOM 실패 대비)
     */
    private function extractValuesByLabelFromHtml($label, $expectedCount) {
        if (!is_string($this->lastHtml) || $this->lastHtml === '') return [];
        $html = $this->lastHtml;
        // 모든 row에서 라벨 td와 숫자 span을 추출한 뒤 부분일치로 매핑
        if (preg_match_all('/<tr[^>]*>([\s\S]*?)<\/tr>/u', $html, $trM)) {
            foreach ($trM[1] as $rowHtml) {
                // 라벨 셀 찾기
                if (!preg_match('/<td[^>]*class=\"c1\s+txt\"[^>]*>([\s\S]*?)<\/td>/u', $rowHtml, $labelM)) continue;
                $labelHtml = $labelM[1];
                $plain = trim(strip_tags($labelHtml)); // 예: 영업이익(발표기준), PER(배)
                if ($plain === '' || mb_strpos($plain, $label) === false) continue;
                if (preg_match_all('/<span[^>]*class=\"cBk\"[^>]*>([^<]+)<\/span>/u', $rowHtml, $mm)) {
                    $nums = [];
                    foreach ($mm[1] as $raw) {
                        $nums[] = floatval(str_replace(',', '', trim($raw)));
                    }
                    if (!empty($nums)) {
                        if ($expectedCount > 0 && count($nums) >= $expectedCount) {
                            $nums = array_slice($nums, -$expectedCount);
                        }
                        return $nums;
                    }
                }
            }
        }
        return [];
    }

    private function isAllEmpty($data) {
        if (!is_array($data) || empty($data)) return true;
        foreach (['enterprise_value','dividend','stability','profitability','revenue'] as $k) {
            if (isset($data[$k]) && is_array($data[$k]) && !empty($data[$k])) {
                // dates만 있고 값이 비어도 표시를 시도하도록 비어있지 않음으로 처리
                return false;
            }
        }
        return true;
    }

    // var changeFin = [...] 에서 날짜 후보 추출
    private function parseDatesFromScripts($html) {
        $out = ['dates' => []];
        if (!is_string($html) || $html === '') return $out;
        if (preg_match('/var\s+changeFin\s*=\s*(\[.*?\]);/s', $html, $m)) {
            $raw = $m[1];
            // 태그 제거 후 연-월 패턴 추출
            $plain = strip_tags($raw);
            // 2021/12, 2024/03 같은 형식 추출
            if (preg_match_all('/(20\d{2})\/(0[1-9]|1[0-2])/', $plain, $mm)) {
                $candidates = array_map(function($y, $mo){ return $y.'.'.sprintf('%02d', (int)$mo); }, $mm[1], $mm[2]);
                // 중복 제거 및 정렬
                $candidates = array_values(array_unique($candidates));
                sort($candidates);
                $out['dates'] = $candidates;
                return $out;
            }
            // 연도만 있는 경우 2021 형태 추출
            if (preg_match_all('/(20\d{2})/', $plain, $yy)) {
                $years = array_values(array_unique($yy[1]));
                sort($years);
                $out['dates'] = $years;
                return $out;
            }
        }
        return $out;
    }
    
    /**
     * 기업가치 데이터 파싱
     */
    private function parseEnterpriseValue($xpath) {
        $data = array();
        
        // cTB26 테이블에서 헤더 날짜 및 지표 행 파싱
        $table = $this->getMainTable($xpath);
        if (!$table) return $data;
        $dates = $this->extractHeaderDates($xpath, $table);
        if (!empty($dates)) $data['dates'] = $dates;

        $rows = $xpath->query('.//tbody/tr[starts-with(@class, "row")]', $table);
        // DEBUG 출력: TR/TD
        $this->debugEcho("<!-- ENTERPRISE_VALUE TR/TD DEBUG START -->\n");
        foreach ($rows as $dr) {
            $this->debugEcho("<div style='color:blue;'>[TR] " . htmlspecialchars(trim($dr->textContent)) . "</div>\n");
            $tds = $xpath->query('.//td', $dr);
            foreach ($tds as $td) {
                $this->debugEcho("<div style='color:green;margin-left:20px;'>[TD] " . htmlspecialchars(trim($td->textContent)) . "</div>\n");
            }
        }
        $this->debugEcho("<!-- ENTERPRISE_VALUE TR/TD DEBUG END -->\n");
        foreach ($rows as $row) {
            $labelNode = $xpath->query('.//td[contains(@class, "c1") and contains(@class, "txt")]', $row)->item(0);
            if (!$labelNode) continue;
            $label = trim(preg_replace('/\s+/', '', $labelNode->textContent));
            $valueNodes = $xpath->query('.//td[contains(@class, "num")]', $row);
            if ($valueNodes->length === 0) continue;
            $values = [];
            foreach ($valueNodes as $vn) {
                $values[] = $this->cleanNumericValue(trim($vn->textContent));
            }
            if (strpos($label, 'PER') !== false) {
                $data['per'] = $values;
            } elseif (strpos($label, 'PBR') !== false) {
                $data['pbr'] = $values;
            } elseif (strpos($label, 'ROA') !== false) {
                $data['roa'] = $values;
            }
        }
        // Fallback: 스크립트에서 보강
        $cnt = isset($data['dates']) && is_array($data['dates']) ? count($data['dates']) : 0;
        if ($cnt > 0) {
            if (empty($data['per'])) $data['per'] = $this->extractSeriesFromScriptsByLabel('PER', $cnt);
            if (empty($data['pbr'])) $data['pbr'] = $this->extractSeriesFromScriptsByLabel('PBR', $cnt);
            if (empty($data['roa'])) $data['roa'] = $this->extractSeriesFromScriptsByLabel('ROA', $cnt);
            // 최종 보강: HTML에서 직접 해당 행 추출
            if (empty($data['per'])) $data['per'] = $this->extractValuesByLabelFromHtml('PER', $cnt);
            if (empty($data['pbr'])) $data['pbr'] = $this->extractValuesByLabelFromHtml('PBR', $cnt);
            if (empty($data['roa'])) $data['roa'] = $this->extractValuesByLabelFromHtml('ROA', $cnt);
        }
        
        return $data;
    }
    
    /**
     * 배당 데이터 파싱
     */
    private function parseDividendData($xpath) {
        $data = array();
        
        $table = $this->getMainTable($xpath);
        if (!$table) return $data;
        $dates = $this->extractHeaderDates($xpath, $table);
        if (!empty($dates)) $data['dates'] = $dates;

        $rows = $xpath->query('.//tbody/tr[starts-with(@class, "row")]', $table);
        // DEBUG 출력: TR/TD
        $this->debugEcho("<!-- DIVIDEND TR/TD DEBUG START -->\n");
        foreach ($rows as $dr) {
            $this->debugEcho("<div style='color:blue;'>[TR] " . htmlspecialchars(trim($dr->textContent)) . "</div>\n");
            $tds = $xpath->query('.//td', $dr);
            foreach ($tds as $td) {
                $this->debugEcho("<div style='color:green;margin-left:20px;'>[TD] " . htmlspecialchars(trim($td->textContent)) . "</div>\n");
            }
        }
        $this->debugEcho("<!-- DIVIDEND TR/TD DEBUG END -->\n");
        foreach ($rows as $row) {
            $labelNode = $xpath->query('.//td[contains(@class, "c1") and contains(@class, "txt")]', $row)->item(0);
            if (!$labelNode) continue;
            $label = trim(preg_replace('/\s+/', '', $labelNode->textContent));
            $valueNodes = $xpath->query('.//td[contains(@class, "num")]', $row);
            if ($valueNodes->length === 0) continue;
            $values = [];
            foreach ($valueNodes as $vn) { $values[] = $this->cleanNumericValue(trim($vn->textContent)); }
            if (strpos($label, '현금배당성향') !== false) {
                $data['cash_dividend_payout_ratio'] = $values;
            } elseif (strpos($label, '현금배당수익률') !== false) {
                $data['cash_dividend_yield'] = $values;
            }
        }
        // Fallback: 스크립트에서 보강
        $cnt = isset($data['dates']) && is_array($data['dates']) ? count($data['dates']) : 0;
        if ($cnt > 0) {
            if (empty($data['cash_dividend_payout_ratio'])) $data['cash_dividend_payout_ratio'] = $this->extractSeriesFromScriptsByLabel('현금배당성향', $cnt);
            if (empty($data['cash_dividend_yield'])) $data['cash_dividend_yield'] = $this->extractSeriesFromScriptsByLabel('현금배당수익률', $cnt);
            if (empty($data['cash_dividend_payout_ratio'])) $data['cash_dividend_payout_ratio'] = $this->extractValuesByLabelFromHtml('현금배당성향', $cnt);
            if (empty($data['cash_dividend_yield'])) $data['cash_dividend_yield'] = $this->extractValuesByLabelFromHtml('현금배당수익률', $cnt);
        }
        
        return $data;
    }
    
    /**
     * 안정성 데이터 파싱
     */
    private function parseStabilityData($xpath) {
        $data = array();
        
        $table = $this->getMainTable($xpath);
        if (!$table) return $data;
        $dates = $this->extractHeaderDates($xpath, $table);
        if (!empty($dates)) $data['dates'] = $dates;

        $rows = $xpath->query('.//tbody/tr[starts-with(@class, "row")]', $table);
        // DEBUG 출력: TR/TD
        $this->debugEcho("<!-- STABILITY TR/TD DEBUG START -->\n");
        foreach ($rows as $dr) {
            $this->debugEcho("<div style='color:blue;'>[TR] " . htmlspecialchars(trim($dr->textContent)) . "</div>\n");
            $tds = $xpath->query('.//td', $dr);
            foreach ($tds as $td) {
                $this->debugEcho("<div style='color:green;margin-left:20px;'>[TD] " . htmlspecialchars(trim($td->textContent)) . "</div>\n");
            }
        }
        $this->debugEcho("<!-- STABILITY TR/TD DEBUG END -->\n");
        foreach ($rows as $row) {
            $labelNode = $xpath->query('.//td[contains(@class, "c1") and contains(@class, "txt")]', $row)->item(0);
            if (!$labelNode) continue;
            $label = trim(preg_replace('/\s+/', '', $labelNode->textContent));
            $valueNodes = $xpath->query('.//td[contains(@class, "num")]', $row);
            if ($valueNodes->length === 0) continue;
            $values = [];
            foreach ($valueNodes as $vn) { $values[] = $this->cleanNumericValue(trim($vn->textContent)); }
            if (strpos($label, '부채비율') !== false) {
                $data['debt_ratio'] = $values;
            } elseif (strpos($label, '순이익율') !== false || strpos($label, '순이익률') !== false) {
                $data['net_profit_margin'] = $values;
            }
        }
        // Fallback
        $cnt = isset($data['dates']) && is_array($data['dates']) ? count($data['dates']) : 0;
        if ($cnt > 0) {
            if (empty($data['debt_ratio'])) $data['debt_ratio'] = $this->extractSeriesFromScriptsByLabel('부채비율', $cnt);
            if (empty($data['net_profit_margin'])) $data['net_profit_margin'] = $this->extractSeriesFromScriptsByLabel('순이익률', $cnt);
            if (empty($data['debt_ratio'])) $data['debt_ratio'] = $this->extractValuesByLabelFromHtml('부채비율', $cnt);
            if (empty($data['net_profit_margin'])) $data['net_profit_margin'] = $this->extractValuesByLabelFromHtml('순이익률', $cnt);
        }
        
        return $data;
    }
    
    /**
     * 이익율 데이터 파싱
     */
    private function parseProfitabilityData($xpath) {
        $data = array();
        
        $table = $this->getMainTable($xpath);
        if (!$table) return $data;
        $dates = $this->extractHeaderDates($xpath, $table);
        if (!empty($dates)) $data['dates'] = $dates;

        $rows = $xpath->query('.//tbody/tr[starts-with(@class, "row")]', $table);
        // DEBUG 출력: TR/TD
        $this->debugEcho("<!-- PROFITABILITY TR/TD DEBUG START -->\n");
        foreach ($rows as $dr) {
            $this->debugEcho("<div style='color:blue;'>[TR] " . htmlspecialchars(trim($dr->textContent)) . "</div>\n");
            $tds = $xpath->query('.//td', $dr);
            foreach ($tds as $td) {
                $this->debugEcho("<div style='color:green;margin-left:20px;'>[TD] " . htmlspecialchars(trim($td->textContent)) . "</div>\n");
            }
        }
        $this->debugEcho("<!-- PROFITABILITY TR/TD DEBUG END -->\n");
        foreach ($rows as $row) {
            $labelNode = $xpath->query('.//td[contains(@class, "c1") and contains(@class, "txt")]', $row)->item(0);
            if (!$labelNode) continue;
            $label = trim(preg_replace('/\s+/', '', $labelNode->textContent));
            $valueNodes = $xpath->query('.//td[contains(@class, "num")]', $row);
            if ($valueNodes->length === 0) continue;
            $values = [];
            foreach ($valueNodes as $vn) { $values[] = $this->cleanNumericValue(trim($vn->textContent)); }
            if (strpos($label, '영업이익율') !== false || strpos($label, '영업이익률') !== false) {
                $data['operating_profit_margin'] = $values;
            } elseif (strpos($label, '순이익율') !== false || strpos($label, '순이익률') !== false) {
                $data['net_profit_margin'] = $values;
            }
        }
        // Fallback
        $cnt = isset($data['dates']) && is_array($data['dates']) ? count($data['dates']) : 0;
        if ($cnt > 0) {
            if (empty($data['operating_profit_margin'])) $data['operating_profit_margin'] = $this->extractSeriesFromScriptsByLabel('영업이익률', $cnt);
            if (empty($data['net_profit_margin'])) $data['net_profit_margin'] = $this->extractSeriesFromScriptsByLabel('순이익률', $cnt);
            if (empty($data['operating_profit_margin'])) $data['operating_profit_margin'] = $this->extractValuesByLabelFromHtml('영업이익률', $cnt);
            if (empty($data['net_profit_margin'])) $data['net_profit_margin'] = $this->extractValuesByLabelFromHtml('순이익률', $cnt);
        }
        
        return $data;
    }
    
    /**
     * 수익성 데이터 파싱
     */
    private function parseRevenueData($xpath) {
        $data = array();
        
        $table = $this->getMainTable($xpath);
        if (!$table) return $data;
        $dates = $this->extractHeaderDates($xpath, $table);
        if (!empty($dates)) $data['dates'] = $dates;

        $rows = $xpath->query('.//tbody/tr[starts-with(@class, "row")]', $table);
        // DEBUG 출력: TR/TD
        $this->debugEcho("<!-- REVENUE TR/TD DEBUG START -->\n");
        foreach ($rows as $dr) {
            $this->debugEcho("<div style='color:blue;'>[TR] " . htmlspecialchars(trim($dr->textContent)) . "</div>\n");
            $tds = $xpath->query('.//td', $dr);
            foreach ($tds as $td) {
                $this->debugEcho("<div style='color:green;margin-left:20px;'>[TD] " . htmlspecialchars(trim($td->textContent)) . "</div>\n");
            }
        }
        $this->debugEcho("<!-- REVENUE TR/TD DEBUG END -->\n");
        foreach ($rows as $row) {
            $labelNode = $xpath->query('.//td[contains(@class, "c1") and contains(@class, "txt")]', $row)->item(0);
            if (!$labelNode) continue;
            $label = trim(preg_replace('/\s+/', '', $labelNode->textContent));
            $valueNodes = $xpath->query('.//td[contains(@class, "num")]', $row);
            if ($valueNodes->length === 0) continue;
            $values = [];
            foreach ($valueNodes as $vn) { $values[] = $this->cleanNumericValue(trim($vn->textContent)); }
            if (strpos($label, '매출액') !== false) {
                $data['revenue'] = $values;
            } elseif (strpos($label, '영업이익(발표기준)') !== false) {
                $data['operating_profit_announced'] = $values;
            } elseif (strpos($label, 'ROE') !== false || strpos($label, '자기자본이익률') !== false) {
                $data['roe'] = $values;
            }
        }
        // Fallback
        $cnt = isset($data['dates']) && is_array($data['dates']) ? count($data['dates']) : 0;
        if ($cnt > 0) {
            if (empty($data['revenue'])) $data['revenue'] = $this->extractSeriesFromScriptsByLabel('매출액', $cnt);
            if (empty($data['operating_profit_announced'])) $data['operating_profit_announced'] = $this->extractSeriesFromScriptsByLabel('영업이익(발표기준)', $cnt);
            if (empty($data['roe'])) $data['roe'] = $this->extractSeriesFromScriptsByLabel('ROE', $cnt);
            if (empty($data['revenue'])) $data['revenue'] = $this->extractValuesByLabelFromHtml('매출액', $cnt);
            if (empty($data['operating_profit_announced'])) $data['operating_profit_announced'] = $this->extractValuesByLabelFromHtml('영업이익(발표기준)', $cnt);
            if (empty($data['roe'])) $data['roe'] = $this->extractValuesByLabelFromHtml('ROE', $cnt);
        }
        
        return $data;
    }
    
    /**
     * 숫자 값 정리
     */
    private function cleanNumericValue($value) {
        // 쉼표, 공백 제거 및 숫자만 추출
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        return $cleaned ? floatval($cleaned) : 0;
    }
    
    /**
     * JSON 형태로 데이터 반환
     */
    public function getFinancialDataAsJson() {
        $data = $this->crawlFinancialData();
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

// 사용 예시 (이 파일이 직접 실행될 때만 동작하도록 보호)
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if (isset($_GET['isNo'])) {
        $isNo = $_GET['isNo'];
        $debug = isset($_GET['debug']) && ($_GET['debug'] === '1' || strtolower($_GET['debug']) === 'true');
        $crawler = new DaumFinancialCrawler($isNo, $debug);
        $data = $crawler->crawlFinancialData();
        
        if ($data) {
            if ($debug) {
                echo "<pre style=\"white-space:pre-wrap;\">" . htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . "</pre>";
            } else {
                echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } else {
            echo $debug
                ? "<div style=\"color:red;\">데이터를 가져올 수 없습니다.</div>"
                : json_encode(['error' => '데이터를 가져올 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        }
    }
}
?>
