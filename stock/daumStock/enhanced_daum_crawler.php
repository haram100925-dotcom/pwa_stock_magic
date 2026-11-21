<?php
/**
 * 향상된 다움 증권 크롤러
 * 실제 다움 증권 API 구조에 맞춰 개선
 */

class EnhancedDaumCrawler {
    private $isNo;
    private $baseUrl = 'http://wisefn.stock.daum.net/v1/company/cF1001.aspx';
    
    public function __construct($isNo) {
        $this->isNo = $isNo;
    }
    
    /**
     * 다움 증권에서 재무정보 크롤링
     */
    public function crawlFinancialData() {
        // 여러 API 엔드포인트 시도
        $endpoints = [
            'main' => '?cmp_cd=' . $this->isNo . '&finGubun=',
            'annual' => '?cmp_cd=' . $this->isNo . '&finGubun=MAIN',
            'quarterly' => '?cmp_cd=' . $this->isNo . '&finGubun=Q',
            'summary' => '?cmp_cd=' . $this->isNo . '&finGubun=SUMMARY'
        ];
        
        $allData = array();
        
        foreach ($endpoints as $type => $endpoint) {
            $data = $this->fetchDataFromEndpoint($endpoint, $type);
            if ($data) {
                $allData = array_merge_recursive($allData, $data);
            }
        }
        
        return $this->processAndCleanData($allData);
    }
    
    /**
     * 특정 엔드포인트에서 데이터 가져오기
     */
    private function fetchDataFromEndpoint($endpoint, $type) {
        $url = $this->baseUrl . $endpoint;
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($curl_handle, CURLOPT_REFERER, 'http://wisefn.stock.daum.net/');
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
        
        $html = curl_exec($curl_handle);
        $httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        
        return $this->parseHtmlData($html, $type);
    }
    
    /**
     * HTML 데이터 파싱
     */
    private function parseHtmlData($html, $type) {
        $data = array();
        
        // HTML 파싱
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        
        // JavaScript 변수에서 데이터 추출 시도
        $scriptData = $this->extractScriptData($html);
        if ($scriptData) {
            $data = array_merge($data, $scriptData);
        }
        
        // 테이블에서 데이터 추출
        $tableData = $this->extractTableData($xpath);
        if ($tableData) {
            $data = array_merge($data, $tableData);
        }
        
        // 숨겨진 필드에서 데이터 추출
        $hiddenData = $this->extractHiddenData($xpath);
        if ($hiddenData) {
            $data = array_merge($data, $hiddenData);
        }
        
        return $data;
    }
    
    /**
     * JavaScript 변수에서 데이터 추출
     */
    private function extractScriptData($html) {
        $data = array();
        
        // changeFin 변수 추출 (연도별 데이터)
        if (preg_match('/var changeFin\s*=\s*(\[.*?\]);/s', $html, $matches)) {
            $jsonData = $matches[1];
            $decoded = json_decode($jsonData, true);
            if ($decoded) {
                $data['years'] = $decoded;
            }
        }
        
        // 기타 JavaScript 변수들 추출
        $patterns = [
            'changeFin' => '/var changeFin\s*=\s*(\[.*?\]);/s',
            'changeQuarter' => '/var changeQuarter\s*=\s*(\[.*?\]);/s',
            'companyData' => '/var companyData\s*=\s*(\{.*?\});/s'
        ];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $jsonData = $matches[1];
                $decoded = json_decode($jsonData, true);
                if ($decoded) {
                    $data[$key] = $decoded;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 테이블에서 데이터 추출
     */
    private function extractTableData($xpath) {
        $data = array();
        
        $tables = $xpath->query('//table');
        
        foreach ($tables as $tableIndex => $table) {
            $rows = $xpath->query('.//tr', $table);
            
            foreach ($rows as $rowIndex => $row) {
                $cells = $xpath->query('.//td | .//th', $row);
                $rowData = array();
                
                foreach ($cells as $cellIndex => $cell) {
                    $cellText = trim($cell->textContent);
                    $rowData[] = $cellText;
                }
                
                if (!empty($rowData)) {
                    $data['table_' . $tableIndex . '_row_' . $rowIndex] = $rowData;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 숨겨진 필드에서 데이터 추출
     */
    private function extractHiddenData($xpath) {
        $data = array();
        
        $hiddenInputs = $xpath->query('//input[@type="hidden"]');
        
        foreach ($hiddenInputs as $input) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            
            if ($name && $value) {
                $data['hidden_' . $name] = $value;
            }
        }
        
        return $data;
    }
    
    /**
     * 데이터 처리 및 정리
     */
    private function processAndCleanData($allData) {
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
        foreach ($allData as $key => $value) {
            if (strpos($key, 'table_') === 0) {
                $this->processTableRow($value, $processed);
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
        
        // 인덱스 기반으로 분류 (실제 API 구조에 맞춰 조정 필요)
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
        
        // 쉼표, 공백, N/A 제거
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
    $crawler = new EnhancedDaumCrawler($isNo);
    $data = $crawler->crawlFinancialData();
    
    if ($data) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => '데이터를 가져올 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    }
}
?>
