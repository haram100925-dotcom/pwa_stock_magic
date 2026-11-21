<?php
/**
 * 대안 재무정보 크롤러
 * 여러 증권사 API를 시도하여 재무 데이터 수집
 */

class AlternativeFinancialCrawler {
    private $isNo;
    
    public function __construct($isNo) {
        $this->isNo = $isNo;
    }
    
    /**
     * 여러 소스에서 재무정보 수집
     */
    public function crawlFinancialData() {
        $allData = array();
        
        // 1. 네이버 증권 API 시도
        $naverData = $this->crawlNaverFinancial();
        if ($naverData) {
            $allData = array_merge_recursive($allData, $naverData);
        }
        
        // 2. FnGuide API 시도
        $fnguideData = $this->crawlFnGuide();
        if ($fnguideData) {
            $allData = array_merge_recursive($allData, $fnguideData);
        }
        
        // 3. 다음 증권 API 시도 (다른 엔드포인트)
        $daumData = $this->crawlDaumAlternative();
        if ($daumData) {
            $allData = array_merge_recursive($allData, $daumData);
        }
        
        return $this->processAllData($allData);
    }
    
    /**
     * 네이버 증권에서 재무정보 크롤링
     */
    private function crawlNaverFinancial() {
        $url = "https://finance.naver.com/item/main.nhn?code=" . $this->isNo;
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $html = curl_exec($curl_handle);
        $httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        
        return $this->parseNaverData($html);
    }
    
    /**
     * FnGuide에서 재무정보 크롤링
     */
    private function crawlFnGuide() {
        $url = "http://comp.fnguide.com/svo/ASPSYSTEM/ASP_Company.asp?pGB=17&GiCode=A" . $this->isNo;
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $html = curl_exec($curl_handle);
        $httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        
        return $this->parseFnGuideData($html);
    }
    
    /**
     * 다음 증권 대안 API 크롤링
     */
    private function crawlDaumAlternative() {
        $endpoints = [
            'summary' => 'http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=' . $this->isNo . '&finGubun=SUMMARY',
            'main' => 'http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=' . $this->isNo . '&finGubun=MAIN',
            'quarter' => 'http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=' . $this->isNo . '&finGubun=Q'
        ];
        
        $allData = array();
        
        foreach ($endpoints as $type => $url) {
            $data = $this->fetchUrl($url);
            if ($data) {
                $parsed = $this->parseDaumData($data, $type);
                if ($parsed) {
                    $allData = array_merge_recursive($allData, $parsed);
                }
            }
        }
        
        return $allData;
    }
    
    /**
     * URL에서 데이터 가져오기
     */
    private function fetchUrl($url) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($curl_handle, CURLOPT_REFERER, 'http://wisefn.stock.daum.net/');
        
        $html = curl_exec($curl_handle);
        $httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);
        
        if ($httpCode === 200 && $html) {
            return $html;
        }
        
        return false;
    }
    
    /**
     * 네이버 데이터 파싱
     */
    private function parseNaverData($html) {
        $data = array();
        
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        
        // 재무 테이블 찾기
        $tables = $xpath->query('//table[@class="tb_type1 tb_num tb_type1_ifrs"]');
        
        foreach ($tables as $table) {
            $this->extractTableData($xpath, $table, $data);
        }
        
        return $data;
    }
    
    /**
     * FnGuide 데이터 파싱
     */
    private function parseFnGuideData($html) {
        $data = array();
        
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        
        // 테이블에서 데이터 추출
        $tables = $xpath->query('//table');
        
        foreach ($tables as $table) {
            $this->extractTableData($xpath, $table, $data);
        }
        
        return $data;
    }
    
    /**
     * 다음 데이터 파싱
     */
    private function parseDaumData($html, $type) {
        $data = array();
        
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        
        // JavaScript 변수에서 데이터 추출
        if (preg_match('/var changeFin\s*=\s*(\[.*?\]);/s', $html, $matches)) {
            $jsonData = $matches[1];
            $decoded = json_decode($jsonData, true);
            if ($decoded) {
                $data['years'] = $decoded;
            }
        }
        
        // 테이블에서 데이터 추출
        $tables = $xpath->query('//table');
        foreach ($tables as $table) {
            $this->extractTableData($xpath, $table, $data);
        }
        
        return $data;
    }
    
    /**
     * 테이블에서 데이터 추출
     */
    private function extractTableData($xpath, $table, &$data) {
        $rows = $xpath->query('.//tr', $table);
        
        foreach ($rows as $rowIndex => $row) {
            $cells = $xpath->query('.//td | .//th', $row);
            $rowData = array();
            
            foreach ($cells as $cellIndex => $cell) {
                $cellText = trim($cell->textContent);
                $rowData[] = $cellText;
            }
            
            if (!empty($rowData)) {
                $data['table_data'][] = $rowData;
            }
        }
    }
    
    /**
     * 모든 데이터 처리
     */
    private function processAllData($allData) {
        $processed = array(
            'enterprise_value' => array('dates' => array(), 'ev' => array(), 'per' => array(), 'pbr' => array(), 'market_cap' => array()),
            'dividend' => array('dates' => array(), 'dividend_per_share' => array()),
            'stability' => array('dates' => array(), 'debt_ratio' => array(), 'net_profit_margin' => array()),
            'profitability' => array('dates' => array(), 'operating_profit_margin' => array(), 'net_profit_margin' => array()),
            'revenue' => array('dates' => array(), 'revenue' => array(), 'operating_profit' => array(), 'roe' => array())
        );
        
        // 연도별 데이터 처리
        if (isset($allData['years']) && is_array($allData['years'])) {
            $this->processYearlyData($allData['years'], $processed);
        }
        
        // 테이블 데이터 처리
        if (isset($allData['table_data'])) {
            foreach ($allData['table_data'] as $rowData) {
                $this->processTableRow($rowData, $processed);
            }
        }
        
        return $processed;
    }
    
    /**
     * 연도별 데이터 처리
     */
    private function processYearlyData($years, &$processed) {
        foreach ($years as $yearData) {
            if (is_array($yearData) && count($yearData) >= 2) {
                $year = $yearData[0];
                $data = $yearData[1];
                
                if (is_array($data)) {
                    $processed['enterprise_value']['dates'][] = $year;
                    $processed['dividend']['dates'][] = $year;
                    $processed['stability']['dates'][] = $year;
                    $processed['profitability']['dates'][] = $year;
                    $processed['revenue']['dates'][] = $year;
                    
                    // 각 데이터 타입별로 값 추출
                    foreach ($data as $index => $value) {
                        $this->categorizeValue($index, $value, $year, $processed);
                    }
                }
            }
        }
    }
    
    /**
     * 테이블 행 데이터 처리
     */
    private function processTableRow($rowData, &$processed) {
        if (count($rowData) < 2) return;
        
        $header = $rowData[0];
        $values = array_slice($rowData, 1);
        
        // 헤더가 날짜인지 확인
        if (preg_match('/(\d{4})/', $header, $matches)) {
            $year = $matches[1];
            
            foreach ($values as $index => $value) {
                $this->categorizeValue($index, $value, $year, $processed);
            }
        }
    }
    
    /**
     * 값 분류
     */
    private function categorizeValue($index, $value, $year, &$processed) {
        $value = $this->cleanNumericValue($value);
        
        // 인덱스 기반으로 분류
        switch ($index) {
            case 0: // PER
                $processed['enterprise_value']['per'][] = $value;
                break;
            case 1: // PBR
                $processed['enterprise_value']['pbr'][] = $value;
                break;
            case 2: // ROE
                $processed['revenue']['roe'][] = $value;
                break;
            case 3: // 매출액
                $processed['revenue']['revenue'][] = $value;
                break;
            case 4: // 영업이익
                $processed['revenue']['operating_profit'][] = $value;
                break;
            case 5: // 순이익
                $processed['profitability']['net_profit_margin'][] = $value;
                break;
            case 6: // 부채비율
                $processed['stability']['debt_ratio'][] = $value;
                break;
            case 7: // 배당
                $processed['dividend']['dividend_per_share'][] = $value;
                break;
        }
    }
    
    /**
     * 숫자 값 정리
     */
    private function cleanNumericValue($value) {
        if (empty($value)) return 0;
        
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        if (empty($cleaned)) return 0;
        
        return floatval($cleaned);
    }
    
    /**
     * JSON 형태로 데이터 반환
     */
    public function getFinancialDataAsJson() {
        $data = $this->crawlFinancialData();
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

// 사용 예시
if (isset($_GET['isNo'])) {
    $isNo = $_GET['isNo'];
    $crawler = new AlternativeFinancialCrawler($isNo);
    $data = $crawler->crawlFinancialData();
    
    if ($data) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => '데이터를 가져올 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    }
}
?>
