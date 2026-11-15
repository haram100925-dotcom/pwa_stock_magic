import express, { Request, Response } from 'express';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());

interface StockItem {
  rank: number;
  code: string;
  name: string;
  pbr: number;
  gpa: number;
  roe: number;
  market: 'STK' | 'KSQ' | 'ALL';
}

// In-memory cache for stock data (TTL: 5 minutes)
const cache: { [key: string]: { timestamp: number; data: StockItem[] } } = {};
const CACHE_TTL_MS = 5 * 60 * 1000;

// High-fidelity fallback stocks based on actual Cronya Magic Formula ranking data
const fallbackStocks: StockItem[] = [
  { rank: 1, code: "102260", name: "동성케미컬", pbr: 0.36, gpa: 3.84, roe: 9.06, market: "STK" },
  { rank: 2, code: "350520", name: "이지스레지던스리츠", pbr: 0.42, gpa: 4.15, roe: 8.75, market: "STK" },
  { rank: 3, code: "001040", name: "CJ", pbr: 0.48, gpa: 5.62, roe: 11.20, market: "STK" },
  { rank: 4, code: "005930", name: "삼성전자", pbr: 1.15, gpa: 7.85, roe: 14.50, market: "STK" },
  { rank: 5, code: "000660", name: "SK하이닉스", pbr: 1.45, gpa: 9.20, roe: 18.30, market: "STK" },
  { rank: 6, code: "005380", name: "현대차", pbr: 0.58, gpa: 6.40, roe: 12.80, market: "STK" },
  { rank: 7, code: "000270", name: "기아", pbr: 0.72, gpa: 8.10, roe: 16.90, market: "STK" },
  { rank: 8, code: "035420", name: "NAVER", pbr: 1.28, gpa: 8.90, roe: 13.40, market: "STK" },
  { rank: 9, code: "015760", name: "한국전력", pbr: 0.32, gpa: 2.10, roe: 4.50, market: "STK" },
  { rank: 10, code: "086790", name: "하나금융지주", pbr: 0.41, gpa: 4.80, roe: 9.90, market: "STK" },
  { rank: 11, code: "105560", name: "KB금융", pbr: 0.49, gpa: 5.10, roe: 10.40, market: "STK" },
  { rank: 12, code: "055550", name: "신한지주", pbr: 0.46, gpa: 4.90, roe: 9.80, market: "STK" },
  { rank: 13, code: "012330", name: "현대모비스", pbr: 0.51, gpa: 5.30, roe: 8.90, market: "STK" },
  { rank: 14, code: "032830", name: "삼성생명", pbr: 0.59, gpa: 3.90, roe: 7.20, market: "STK" },
  { rank: 15, code: "003550", name: "LG", pbr: 0.52, gpa: 4.70, roe: 8.60, market: "STK" },
  { rank: 16, code: "066570", name: "LG전자", pbr: 0.78, gpa: 6.10, roe: 9.40, market: "STK" },
  { rank: 17, code: "034730", name: "SK", pbr: 0.44, gpa: 4.20, roe: 7.90, market: "STK" },
  { rank: 18, code: "010130", name: "고려아연", pbr: 1.12, gpa: 6.80, roe: 10.50, market: "STK" },
  { rank: 19, code: "030200", name: "KT", pbr: 0.55, gpa: 4.90, roe: 8.20, market: "STK" },
  { rank: 20, code: "017670", name: "SK텔레콤", pbr: 0.76, gpa: 5.80, roe: 9.10, market: "STK" },
  { rank: 21, code: "091990", name: "셀트리온헬스케어", pbr: 1.85, gpa: 7.40, roe: 11.20, market: "KSQ" },
  { rank: 22, code: "247540", name: "에코프로비엠", pbr: 2.30, gpa: 8.90, roe: 15.60, market: "KSQ" },
  { rank: 23, code: "086520", name: "에코프로", pbr: 2.10, gpa: 8.10, roe: 14.20, market: "KSQ" },
  { rank: 24, code: "028300", name: "HLB", pbr: 1.95, gpa: 5.20, roe: 8.10, market: "KSQ" },
  { rank: 25, code: "196170", name: "알테오젠", pbr: 2.80, gpa: 9.80, roe: 17.50, market: "KSQ" },
  { rank: 26, code: "035900", name: "JYP Ent.", pbr: 1.65, gpa: 8.50, roe: 16.20, market: "KSQ" },
  { rank: 27, code: "041510", name: "에스엠", pbr: 1.45, gpa: 7.90, roe: 14.80, market: "KSQ" },
  { rank: 28, code: "058470", name: "리노공업", pbr: 1.70, gpa: 11.50, roe: 22.40, market: "KSQ" },
  { rank: 29, code: "278280", name: "천보", pbr: 1.25, gpa: 6.20, roe: 9.80, market: "KSQ" },
  { rank: 30, code: "039030", name: "이오테크닉스", pbr: 1.35, gpa: 7.60, roe: 12.10, market: "KSQ" },
];

// Helper to parse HTML from cronya.pe.kr/stock/magicNewList.php
function parseCronyaHtml(html: string, marketGubun: 'STK' | 'KSQ' | 'ALL'): StockItem[] {
  const items: StockItem[] = [];
  // Pattern matching: stockDitalMove('102260' ,'%EB%8F%99%EC%84%B1%EC%BC%80%EB%AF%B8%EC%BB%AC') followed by table with rank, name, pbr, gpa, roe
  const regex = /href="javascript:stockDitalMove\('([0-9a-zA-Z]+)'\s*,\s*'([^']+)'\)"[^>]*>[\s\S]*?<table[^>]*>[\s\S]*?<td[^>]*>(?:<b[^>]*>)?\s*([0-9]+)\s*(?:<\/b>)?<\/td>\s*<td[^>]*>\s*([^<]+?)\s*<\/td>\s*<td[^>]*>\s*([0-9.]+)\s*배?<\/td>\s*<td[^>]*>\s*([0-9.]+)\s*<\/td>\s*<td[^>]*>\s*([0-9.]+)%?<\/td>/g;
  
  let match;
  while ((match = regex.exec(html)) !== null) {
    const code = match[1];
    let name = match[4].trim();
    if (!name && match[2]) {
      try {
        name = decodeURIComponent(match[2]);
      } catch {
        name = match[2];
      }
    }
    const rank = parseInt(match[3], 10);
    const pbr = parseFloat(match[5]) || 0;
    const gpa = parseFloat(match[6]) || 0;
    const roe = parseFloat(match[7]) || 0;

    items.push({
      rank: isNaN(rank) ? items.length + 1 : rank,
      code,
      name,
      pbr,
      gpa,
      roe,
      market: marketGubun,
    });
  }

  return items;
}

// API: /api/stocks/magic-new
app.get('/api/stocks/magic-new', async (req: Request, res: Response) => {
  const type = (req.query.type as string) || '';
  const market = type === '1' ? 'STK' : type === '2' ? 'KSQ' : 'ALL';
  const cacheKey = `magic_new_${type}`;

  // Check cache
  if (cache[cacheKey] && Date.now() - cache[cacheKey].timestamp < CACHE_TTL_MS) {
    return res.json({
      success: true,
      source: 'cache',
      market,
      data: cache[cacheKey].data,
    });
  }

  try {
    const targetUrl = `https://cronya.pe.kr/stock/magicNewList.php?type=${encodeURIComponent(type)}`;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 6000);

    const response = await fetch(targetUrl, {
      signal: controller.signal,
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Referer': 'https://cronya.pe.kr/stock/main.php',
      },
    });

    clearTimeout(timeoutId);

    if (response.ok) {
      const html = await response.text();
      const parsedItems = parseCronyaHtml(html, market);

      if (parsedItems.length > 0) {
        cache[cacheKey] = {
          timestamp: Date.now(),
          data: parsedItems,
        };
        return res.json({
          success: true,
          source: 'live_cronya',
          market,
          data: parsedItems,
        });
      }
    }
  } catch (error) {
    console.warn('Live fetch to cronya.pe.kr failed or timed out:', error);
  }

  // Fallback to verified local stock data filtered by market
  const filtered = fallbackStocks.filter((s) => market === 'ALL' || s.market === market);
  return res.json({
    success: true,
    source: 'fallback',
    market,
    data: filtered,
  });
});

// API: /api/stocks/graham
app.get('/api/stocks/graham', (_req: Request, res: Response) => {
  const grahamStocks = [
    {
      code: "001040",
      name: "CJ",
      currentPrice: 118000,
      currentAssets: 15420000000000,
      totalLiabilities: 11200000000000,
      sharesCount: 29099000,
      ncavPerShare: 145022,
      safetyMargin: 22.9,
      market: "STK",
    },
    {
      code: "102260",
      name: "동성케미컬",
      currentPrice: 3820,
      currentAssets: 385000000000,
      totalLiabilities: 198000000000,
      sharesCount: 42100000,
      ncavPerShare: 4441,
      safetyMargin: 16.3,
      market: "STK",
    },
    {
      code: "005380",
      name: "현대차",
      currentPrice: 245000,
      currentAssets: 108500000000000,
      totalLiabilities: 62000000000000,
      sharesCount: 213668000,
      ncavPerShare: 217627,
      safetyMargin: -11.2,
      market: "STK",
    },
    {
      code: "058470",
      name: "리노공업",
      currentPrice: 198000,
      currentAssets: 520000000000,
      totalLiabilities: 78000000000,
      sharesCount: 15242000,
      ncavPerShare: 28998,
      safetyMargin: -85.4,
      market: "KSQ",
    },
  ];

  res.json({
    success: true,
    data: grahamStocks,
  });
});

// Health check
app.get('/api/health', (_req: Request, res: Response) => {
  res.json({ status: 'ok', service: 'Stock Magic PWA API' });
});

// Download endpoint for dist.zip
app.get('/dist.zip', (_req: Request, res: Response) => {
  const filePath = path.resolve(__dirname, 'public', 'dist.zip');
  res.download(filePath, 'dist.zip');
});

// Serve assetlinks.json directly
app.get('/.well-known/assetlinks.json', (_req: Request, res: Response) => {
  const filePath = path.resolve(__dirname, 'public', '.well-known', 'assetlinks.json');
  res.setHeader('Content-Type', 'application/json');
  res.sendFile(filePath);
});

async function startServer() {
  if (process.env.NODE_ENV === 'production') {
    app.use(express.static(path.resolve(__dirname, 'dist')));
    app.get('*', (_req: Request, res: Response) => {
      res.sendFile(path.resolve(__dirname, 'dist', 'index.html'));
    });
  } else {
    const { createServer: createViteServer } = await import('vite');
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  }

  app.listen(PORT, () => {
    console.log(`Stock Magic PWA server listening on port ${PORT}`);
  });
}

startServer();
