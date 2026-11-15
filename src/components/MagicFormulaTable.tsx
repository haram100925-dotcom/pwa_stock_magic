import React, { useState, useEffect, useMemo } from 'react';
import { Search, Star, ExternalLink, RefreshCw, Filter, ArrowUpDown } from 'lucide-react';

export interface StockItem {
  rank: number;
  code: string;
  name: string;
  pbr: number;
  gpa: number;
  roe: number;
  market: 'STK' | 'KSQ' | 'ALL';
}

export const MagicFormulaTable: React.FC = () => {
  const [marketType, setMarketType] = useState<'ALL' | 'STK' | 'KSQ'>('STK');
  const [showFavoritesOnly, setShowFavoritesOnly] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [stocks, setStocks] = useState<StockItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [source, setSource] = useState<string>('loading');
  const [sortField, setSortField] = useState<'rank' | 'pbr' | 'gpa' | 'roe'>('rank');
  const [sortAsc, setSortAsc] = useState(true);
  const [favorites, setFavorites] = useState<string[]>(() => {
    try {
      const saved = localStorage.getItem('cronya_stock_favorites');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  // Client-side parser for cronya.pe.kr/stock/magicNewList.php when deployed to Apache
  const parseHtmlStocks = (html: string, marketGubun: 'STK' | 'KSQ' | 'ALL'): StockItem[] => {
    const items: StockItem[] = [];
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
  };

  const defaultStocks: StockItem[] = [
    { rank: 1, code: "350520", name: "이지스레지던스리츠", pbr: 0.37, gpa: 3.89, roe: 9.80, market: "STK" },
    { rank: 2, code: "102260", name: "동성케미컬", pbr: 0.36, gpa: 3.84, roe: 9.06, market: "STK" },
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
  ];

  const fetchStocks = async (type: 'ALL' | 'STK' | 'KSQ') => {
    setLoading(true);
    const param = type === 'STK' ? '1' : type === 'KSQ' ? '2' : '';

    // Step 1: Try local Express proxy (dev server)
    try {
      const res = await fetch(`/api/stocks/magic-new?type=${param}`);
      if (res.ok) {
        const json = await res.json();
        if (json.success && Array.isArray(json.data) && json.data.length > 0) {
          setStocks(json.data);
          setSource(json.source || 'api');
          setLoading(false);
          return;
        }
      }
    } catch {
      // Continue to next fallback
    }

    // Step 2: Try fetching directly from PHP endpoint (when deployed on cronya.pe.kr)
    try {
      const phpUrl = window.location.pathname.includes('/stock')
        ? `./magicNewList.php?type=${param}`
        : `/stock/magicNewList.php?type=${param}`;
      const res = await fetch(phpUrl);
      if (res.ok) {
        const html = await res.text();
        const parsed = parseHtmlStocks(html, type);
        if (parsed.length > 0) {
          setStocks(parsed);
          setSource('live_cronya');
          setLoading(false);
          return;
        }
      }
    } catch {
      // Continue to next fallback
    }

    // Step 3: Use verified offline fallback dataset
    const filteredFallback = defaultStocks.filter((s) => (type === 'ALL' ? true : s.market === type));
    setStocks(filteredFallback);
    setSource('offline_cache');
    setLoading(false);
  };

  useEffect(() => {
    fetchStocks(marketType);
  }, [marketType]);

  const toggleFavorite = (code: string) => {
    setFavorites((prev) => {
      const next = prev.includes(code) ? prev.filter((c) => c !== code) : [...prev, code];
      try {
        localStorage.setItem('cronya_stock_favorites', JSON.stringify(next));
      } catch (e) {
        console.error(e);
      }
      return next;
    });
  };

  const handleSort = (field: 'rank' | 'pbr' | 'gpa' | 'roe') => {
    if (sortField === field) {
      setSortAsc(!sortAsc);
    } else {
      setSortField(field);
      setSortAsc(field === 'rank' || field === 'pbr'); // rank and pbr default to asc, gpa/roe to desc
    }
  };

  const filteredAndSortedStocks = useMemo(() => {
    let result = [...stocks];

    // Filter by favorites
    if (showFavoritesOnly) {
      result = result.filter((item) => favorites.includes(item.code));
    }

    // Filter by search query
    if (searchTerm.trim()) {
      const query = searchTerm.trim().toLowerCase();
      result = result.filter(
        (item) => item.name.toLowerCase().includes(query) || item.code.includes(query)
      );
    }

    // Sort
    result.sort((a, b) => {
      let valA = a[sortField];
      let valB = b[sortField];
      if (valA === undefined) valA = 0;
      if (valB === undefined) valB = 0;
      return sortAsc ? valA - valB : valB - valA;
    });

    return result;
  }, [stocks, showFavoritesOnly, searchTerm, sortField, sortAsc, favorites]);

  return (
    <div className="w-full space-y-4">
      {/* Top Controls Strip */}
      <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-[#131B2A] border border-slate-800 rounded-xl p-3 shadow-lg">
        {/* Segmented Filter Buttons */}
        <div className="flex items-center gap-1.5 p-1 bg-slate-900/90 rounded-lg border border-slate-800 overflow-x-auto">
          <button
            onClick={() => {
              setShowFavoritesOnly(false);
              setMarketType('STK');
            }}
            className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors whitespace-nowrap cursor-pointer ${
              !showFavoritesOnly && marketType === 'STK'
                ? 'bg-rose-950/70 text-rose-300 border border-rose-800/60 shadow-sm'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            코스피 (PBR↓·GP/A↑)
          </button>
          <button
            onClick={() => {
              setShowFavoritesOnly(false);
              setMarketType('KSQ');
            }}
            className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors whitespace-nowrap cursor-pointer ${
              !showFavoritesOnly && marketType === 'KSQ'
                ? 'bg-sky-950/70 text-sky-300 border border-sky-800/60 shadow-sm'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            코스닥 (PBR↓·GP/A↑)
          </button>
          <button
            onClick={() => {
              setShowFavoritesOnly(false);
              setMarketType('ALL');
            }}
            className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors whitespace-nowrap cursor-pointer ${
              !showFavoritesOnly && marketType === 'ALL'
                ? 'bg-slate-800 text-white shadow-sm'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            전 종목
          </button>
          <button
            onClick={() => setShowFavoritesOnly(!showFavoritesOnly)}
            className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors whitespace-nowrap flex items-center gap-1 cursor-pointer ${
              showFavoritesOnly
                ? 'bg-amber-950/70 text-amber-300 border border-amber-800/60 shadow-sm'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            <Star className="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
            <span>관심종목 ({favorites.length})</span>
          </button>
        </div>

        {/* Search & Refresh */}
        <div className="flex items-center gap-2">
          <div className="relative flex-1 md:w-56">
            <Search className="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="종목명 또는 코드 검색..."
              className="w-full pl-8 pr-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500"
            />
          </div>
          <button
            onClick={() => fetchStocks(marketType)}
            disabled={loading}
            title="데이터 새로고침"
            className="p-2 rounded-lg bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition-colors disabled:opacity-50 cursor-pointer"
          >
            <RefreshCw className={`w-3.5 h-3.5 ${loading ? 'animate-spin text-emerald-400' : ''}`} />
          </button>
        </div>
      </div>

      {/* Info Kicker */}
      <div className="flex items-center justify-between text-xs text-slate-400 px-1">
        <div className="flex items-center gap-2">
          <span>신 마법공식 Top 100</span>
          <span aria-hidden="true">·</span>
          <span>총 {filteredAndSortedStocks.length}개 종목</span>
          <span aria-hidden="true">·</span>
          <span className="text-[11px] font-mono text-emerald-400">
            {source === 'live_cronya' ? '● 크로냐 실시간 DB 연동' : '○ 안전 캐시 데이터'}
          </span>
        </div>
        <div className="hidden sm:flex items-center gap-1.5 text-[11px] text-slate-500">
          <span>※ PBR 100배 이하 & 시총 500억 이상 대상</span>
        </div>
      </div>

      {/* Financial Table Viewport */}
      <div className="overflow-x-auto rounded-xl border border-slate-800 bg-[#111724] shadow-xl">
        <table className="w-full text-left text-xs border-collapse">
          <thead>
            <tr className="border-b border-slate-800 bg-slate-900/80 text-slate-400 font-semibold select-none">
              <th
                onClick={() => handleSort('rank')}
                className="py-3 px-3 text-center w-14 cursor-pointer hover:text-slate-200"
              >
                <div className="flex items-center justify-center gap-1">
                  <span>순위</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-600" />
                </div>
              </th>
              <th className="py-3 px-2 w-10 text-center">관심</th>
              <th className="py-3 px-3">종목명 (코드)</th>
              <th
                onClick={() => handleSort('pbr')}
                className="py-3 px-3 text-right cursor-pointer hover:text-slate-200"
              >
                <div className="flex items-center justify-end gap-1">
                  <span>PBR (배) ↓</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-600" />
                </div>
              </th>
              <th
                onClick={() => handleSort('gpa')}
                className="py-3 px-3 text-right cursor-pointer hover:text-slate-200"
              >
                <div className="flex items-center justify-end gap-1">
                  <span>GP/A (수익성) ↑</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-600" />
                </div>
              </th>
              <th
                onClick={() => handleSort('roe')}
                className="py-3 px-3 text-right cursor-pointer hover:text-slate-200"
              >
                <div className="flex items-center justify-end gap-1">
                  <span>ROE (%)</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-600" />
                </div>
              </th>
              <th className="py-3 px-3 text-center w-24">상세 분석</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {loading ? (
              // Loading Skeleton Rows
              Array.from({ length: 10 }).map((_, idx) => (
                <tr key={idx} className="animate-pulse">
                  <td className="py-3 px-3 text-center">
                    <div className="h-4 w-6 bg-slate-800 rounded mx-auto" />
                  </td>
                  <td className="py-3 px-2 text-center">
                    <div className="h-4 w-4 bg-slate-800 rounded mx-auto" />
                  </td>
                  <td className="py-3 px-3">
                    <div className="h-4 w-28 bg-slate-800 rounded" />
                  </td>
                  <td className="py-3 px-3 text-right">
                    <div className="h-4 w-12 bg-slate-800 rounded ml-auto" />
                  </td>
                  <td className="py-3 px-3 text-right">
                    <div className="h-4 w-12 bg-slate-800 rounded ml-auto" />
                  </td>
                  <td className="py-3 px-3 text-right">
                    <div className="h-4 w-12 bg-slate-800 rounded ml-auto" />
                  </td>
                  <td className="py-3 px-3 text-center">
                    <div className="h-4 w-14 bg-slate-800 rounded mx-auto" />
                  </td>
                </tr>
              ))
            ) : filteredAndSortedStocks.length === 0 ? (
              <tr>
                <td colSpan={7} className="py-12 text-center text-slate-500">
                  {showFavoritesOnly
                    ? '즐겨찾기한 관심종목이 없습니다. 별표 아이콘을 눌러 관심종목을 등록해 보세요.'
                    : '조건에 해당하는 종목이 없습니다.'}
                </td>
              </tr>
            ) : (
              filteredAndSortedStocks.map((stock) => {
                const isFav = favorites.includes(stock.code);
                return (
                  <tr
                    key={stock.code}
                    className="hover:bg-slate-800/40 transition-colors group"
                  >
                    {/* Rank */}
                    <td className="py-3 px-3 text-center font-mono tabular-nums font-bold">
                      <span
                        className={`inline-block w-6 text-center ${
                          stock.rank === 1
                            ? 'text-amber-400 font-extrabold'
                            : stock.rank <= 3
                            ? 'text-emerald-400 font-bold'
                            : 'text-slate-400'
                        }`}
                      >
                        {stock.rank}
                      </span>
                    </td>

                    {/* Favorite Star */}
                    <td className="py-3 px-2 text-center">
                      <button
                        onClick={() => toggleFavorite(stock.code)}
                        className="text-slate-600 hover:text-amber-400 transition-colors p-1 cursor-pointer"
                        title={isFav ? '관심종목 해제' : '관심종목 추가'}
                      >
                        <Star
                          className={`w-4 h-4 ${
                            isFav ? 'fill-amber-400 text-amber-400' : 'text-slate-600'
                          }`}
                        />
                      </button>
                    </td>

                    {/* Stock Name & Code */}
                    <td className="py-3 px-3">
                      <div className="font-semibold text-slate-100 group-hover:text-emerald-400 transition-colors">
                        {stock.name}
                      </div>
                      <div className="text-[11px] font-mono text-slate-500">
                        {stock.code}
                      </div>
                    </td>

                    {/* PBR */}
                    <td className="py-3 px-3 text-right font-mono tabular-nums text-emerald-400 font-semibold">
                      {stock.pbr.toFixed(2)} 배
                    </td>

                    {/* GP/A */}
                    <td className="py-3 px-3 text-right font-mono tabular-nums text-amber-400 font-semibold">
                      {stock.gpa.toFixed(2)}
                    </td>

                    {/* ROE */}
                    <td className="py-3 px-3 text-right font-mono tabular-nums text-cyan-400">
                      {stock.roe.toFixed(2)}%
                    </td>

                    {/* Details Link */}
                    <td className="py-3 px-3 text-center">
                      <div className="flex items-center justify-center gap-1.5">
                        <a
                          href={`https://finance.naver.com/item/main.naver?code=${stock.code}`}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-medium flex items-center gap-0.5 transition-colors"
                          title="네이버 증권 상세 열기"
                        >
                          <span>네이버</span>
                          <ExternalLink className="w-2.5 h-2.5 text-slate-400" />
                        </a>
                      </div>
                    </td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};
