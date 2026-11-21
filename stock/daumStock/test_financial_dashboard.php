<?php
/**
 * 재무 대시보드 테스트 페이지
 */

$isNo = $_GET['isNo'] ?? '005930'; // 기본값: 삼성전자
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주식 재무정보 대시보드 테스트</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 200px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            display: inline-block;
            margin-right: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>주식 재무정보 대시보드 테스트</h1>
        
        <div class="info">
            <h3>사용법</h3>
            <p>1. 아래 입력창에 주식 코드를 입력하세요 (예: 005930 - 삼성전자)</p>
            <p>2. "대시보드 보기" 버튼을 클릭하여 재무정보 그래프를 확인하세요</p>
            <p>3. 다움 증권 API를 사용하여 다음 정보를 제공합니다:</p>
            <ul>
                <li>기업가치: EV(Enterprise Value), PER, PBR, 시가총액</li>
                <li>배당: 주당배당금 (막대그래프)</li>
                <li>안정성: 부채비율, 순이익율</li>
                <li>이익율: 영업이익율, 순이익율</li>
                <li>수익성: 매출액, 영업이익, ROE(자기자본이익률)</li>
            </ul>
        </div>

        <form method="GET" action="stockFinancialDashboard.php">
            <div class="form-group">
                <label for="isNo">주식 코드:</label>
                <input type="text" id="isNo" name="isNo" value="<?php echo htmlspecialchars($isNo); ?>" placeholder="예: 005930">
            </div>
            
            <div class="form-group">
                <label for="siteType">사이트 타입:</label>
                <select name="siteType" id="siteType">
                    <option value="">기본</option>
                    <option value="C">추천</option>
                </select>
            </div>
            
            <button type="submit">대시보드 보기</button>
        </form>

        <div class="links">
            <h3>관련 링크</h3>
            <a href="debug_daum_api.php?isNo=<?php echo $isNo; ?>" target="_blank">다움 API 상세 디버깅</a>
            <a href="https://finance.daum.net/quotes/A<?php echo $isNo; ?>" target="_blank">카카오 증권시세</a>
            <a href="http://wisefn.stock.daum.net/v1/company/cF1001.aspx?cmp_cd=<?php echo $isNo; ?>&finGubun=MAIN" target="_blank">다움 재무정보</a>
        </div>

        <div style="margin-top: 30px;">
            <h3>주요 주식 코드 예시</h3>
            <ul>
                <li>005930 - 삼성전자</li>
                <li>000660 - SK하이닉스</li>
                <li>035420 - 네이버</li>
                <li>207940 - 삼성바이오로직스</li>
                <li>006400 - 삼성SDI</li>
            </ul>
        </div>
    </div>
</body>
</html>
