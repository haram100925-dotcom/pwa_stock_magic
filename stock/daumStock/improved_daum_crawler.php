<?php
/**
 * 개선된 다움 증권 재무정보 크롤러
 * 실제 API 구조에 맞춰 데이터 파싱 개선
 */

class ImprovedDaumCrawler {
    private $isNo;
    private $baseUrl = 'http://wisefn.stock.daum.net/v1/company/cF1001.aspx';
    
    public function __construct($isNo) {
        $this->isNo = $isNo;
    }
    
    /**
     * 다움 증권 API에서 재무정보 크롤링
     */
    public function crawlFinancialData() {
        $url = $this->baseUrl . '?cmp_cd=' . $this->isNo . '&finGubun=';
        
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
        
        return $this->parseFinancialData($html);
    }
    
    /**
     * HTML에서 재무정보 파싱 (개선된 버전)
     */
    private function parseFinancialData($html) {
        $data = array();
        
        // HTML 파싱을 위한 DOMDocument 사용
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        
        // 모든 테이블을 찾아서 데이터 추출
        $tables = $xpath->query('//table');
        
        $allData = array();
        
        foreach ($tables as $tableIndex => $table) {
            $tableData = $this->extractTableData($xpath, $table);
            if (!empty($tableData)) {
                $allData[] = $tableData;
            }
        }
        
        // 추출된 데이터를 카테고리별로 정리
        $data = $this->categorizeData($allData);
        
        return $data;
    }
    
    /**
     * 테이블에서 데이터 추출
     */
    private function extractTableData($xpath, $table) {
        $data = array();
        $rows = $xpath->query('.//tr', $table);
        
        foreach ($rows as $rowIndex => $row) {
            $cells = $xpath->query('.//td | .//th', $row);
            $rowData = array();
            
            foreach ($cells as $cellIndex => $cell) {
                $cellText = trim($cell->textContent);
                $rowData[] = $cellText;
            }
            
            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        
        return $data;
    }
    
    /**
     * 추출된 데이터를 카테고리별로 정리
     */
    private function categorizeData($allData) {
        $categorized = array(
            'enterprise_value' => array('dates' => array(), 'ev' => array(), 'per' => array(), 'pbr' => array(), 'market_cap' => array()),
            'dividend' => array('dates' => array(), 'dividend_per_share' => array()),
            'stability' => array('dates' => array(), 'debt_ratio' => array(), 'net_profit_margin' => array()),
            'profitability' => array('dates' => array(), 'operating_profit_margin' => array(), 'net_profit_margin' => array()),
            'revenue' => array('dates' => array(), 'revenue' => array(), 'operating_profit' => array(), 'roe' => array())
        );
        
        foreach ($allData as $tableData) {
            $this->processTableData($tableData, $categorized);
        }
        
        return $categorized;
    }
    
    /**
     * 테이블 데이터 처리
     */
    private function processTableData($tableData, &$categorized) {
        if (empty($tableData)) return;
        
        // 첫 번째 행이 헤더인지 확인
        $headerRow = $tableData[0] ?? array();
        
        // 날짜 컬럼 찾기
        $dateColumnIndex = $this->findDateColumn($headerRow);
        
        if ($dateColumnIndex === -1) return;
        
        // 데이터 행들 처리
        for ($i = 1; $i < count($tableData); $i++) {
            $row = $tableData[$i];
            if (count($row) <= $dateColumnIndex) continue;
            
            $date = $this->cleanDate($row[$dateColumnIndex]);
            if (empty($date)) continue;
            
            // 각 컬럼의 데이터 타입 확인 및 분류
            for ($j = 0; $j < count($row); $j++) {
                if ($j === $dateColumnIndex) continue;
                
                $header = $headerRow[$j] ?? '';
                $value = $this->cleanNumericValue($row[$j]);
                
                $this->categorizeValue($header, $value, $date, $categorized);
            }
        }
    }
    
    /**
     * 날짜 컬럼 찾기
     */
    private function findDateColumn($headerRow) {
        foreach ($headerRow as $index => $header) {
            $header = strtolower(trim($header));
            if (strpos($header, '년') !== false || 
                strpos($header, '월') !== false || 
                strpos($header, '일') !== false ||
                strpos($header, 'date') !== false ||
                strpos($header, '기간') !== false) {
                return $index;
            }
        }
        return -1;
    }
    
    /**
     * 날짜 정리
     */
    private function cleanDate($date) {
        $date = trim($date);
        if (empty($date)) return '';
        
        // 년도만 추출 (예: 2023년 -> 2023)
        if (preg_match('/(\d{4})년/', $date, $matches)) {
            return $matches[1];
        }
        
        // 월 정보가 있는 경우 (예: 2023.12 -> 2023.12)
        if (preg_match('/(\d{4})\.(\d{1,2})/', $date, $matches)) {
            return $matches[1] . '.' . $matches[2];
        }
        
        return $date;
    }
    
    /**
     * 값 분류
     */
    private function categorizeValue($header, $value, $date, &$categorized) {
        $header = strtolower(trim($header));
        
        // 기업가치 관련
        if (strpos($header, 'ev') !== false || strpos($header, '기업가치') !== false) {
            $categorized['enterprise_value']['dates'][] = $date;
            $categorized['enterprise_value']['ev'][] = $value;
        } elseif (strpos($header, 'per') !== false) {
            $categorized['enterprise_value']['dates'][] = $date;
            $categorized['enterprise_value']['per'][] = $value;
        } elseif (strpos($header, 'pbr') !== false) {
            $categorized['enterprise_value']['dates'][] = $date;
            $categorized['enterprise_value']['pbr'][] = $value;
        } elseif (strpos($header, '시가총액') !== false) {
            $categorized['enterprise_value']['dates'][] = $date;
            $categorized['enterprise_value']['market_cap'][] = $value;
        }
        
        // 배당 관련
        elseif (strpos($header, '주당배당금') !== false || strpos($header, '배당') !== false) {
            $categorized['dividend']['dates'][] = $date;
            $categorized['dividend']['dividend_per_share'][] = $value;
        }
        
        // 안정성 관련
        elseif (strpos($header, '부채비율') !== false) {
            $categorized['stability']['dates'][] = $date;
            $categorized['stability']['debt_ratio'][] = $value;
        } elseif (strpos($header, '순이익률') !== false && strpos($header, '영업') === false) {
            $categorized['stability']['dates'][] = $date;
            $categorized['stability']['net_profit_margin'][] = $value;
        }
        
        // 이익율 관련
        elseif (strpos($header, '영업이익률') !== false) {
            $categorized['profitability']['dates'][] = $date;
            $categorized['profitability']['operating_profit_margin'][] = $value;
        } elseif (strpos($header, '순이익률') !== false && strpos($header, '영업') !== false) {
            $categorized['profitability']['dates'][] = $date;
            $categorized['profitability']['net_profit_margin'][] = $value;
        }
        
        // 수익성 관련
        elseif (strpos($header, '매출액') !== false) {
            $categorized['revenue']['dates'][] = $date;
            $categorized['revenue']['revenue'][] = $value;
        } elseif (strpos($header, '영업이익') !== false && strpos($header, '율') === false) {
            $categorized['revenue']['dates'][] = $date;
            $categorized['revenue']['operating_profit'][] = $value;
        } elseif (strpos($header, 'roe') !== false || strpos($header, '자기자본이익률') !== false) {
            $categorized['revenue']['dates'][] = $date;
            $categorized['revenue']['roe'][] = $value;
        }
    }
    
    /**
     * 숫자 값 정리
     */
    private function cleanNumericValue($value) {
        if (empty($value)) return 0;
        
        // 쉼표, 공백, N/A 제거
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        // 빈 문자열이면 0 반환
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
    $crawler = new ImprovedDaumCrawler($isNo);
    $data = $crawler->crawlFinancialData();
    
    if ($data) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => '데이터를 가져올 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    }
}
?>
