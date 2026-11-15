import React, { useState } from 'react';
import { Calculator, ShieldAlert, CheckCircle2, AlertTriangle, ArrowRight } from 'lucide-react';

interface PresetStock {
  name: string;
  code: string;
  currentPrice: number;
  currentAssets: number; // 억원
  totalLiabilities: number; // 억원
  sharesCount: number; // 만주
}

export const GrahamCalculator: React.FC = () => {
  const presets: PresetStock[] = [
    {
      name: '동성케미컬',
      code: '102260',
      currentPrice: 3820,
      currentAssets: 3850,
      totalLiabilities: 1980,
      sharesCount: 4210,
    },
    {
      name: 'CJ',
      code: '001040',
      currentPrice: 118000,
      currentAssets: 154200,
      totalLiabilities: 112000,
      sharesCount: 2909,
    },
    {
      name: '현대차',
      code: '005380',
      currentPrice: 245000,
      currentAssets: 1085000,
      totalLiabilities: 620000,
      sharesCount: 21366,
    },
  ];

  const [stockName, setStockName] = useState('동성케미컬');
  const [currentPrice, setCurrentPrice] = useState<number>(3820);
  const [currentAssets, setCurrentAssets] = useState<number>(3850); // in 100M KRW (억원)
  const [totalLiabilities, setTotalLiabilities] = useState<number>(1980); // in 100M KRW (억원)
  const [sharesCount, setSharesCount] = useState<number>(4210); // in 10k shares (만주)

  const applyPreset = (preset: PresetStock) => {
    setStockName(preset.name);
    setCurrentPrice(preset.currentPrice);
    setCurrentAssets(preset.currentAssets);
    setTotalLiabilities(preset.totalLiabilities);
    setSharesCount(preset.sharesCount);
  };

  // Calculations:
  // NCAV (억원) = 유동자산 - 총부채
  const ncavEok = currentAssets - totalLiabilities;
  // 주당 순유동자산가치 (NCAV/주) = (NCAV * 1억) / (주식수 * 1만) = (NCAV * 10000) / 주식수
  const ncavPerShare = sharesCount > 0 ? Math.round((ncavEok * 10000) / sharesCount) : 0;
  // 안전마진 (%) = ((주당 NCAV - 현재가) / 주당 NCAV) * 100
  const safetyMargin = ncavPerShare > 0 ? ((ncavPerShare - currentPrice) / ncavPerShare) * 100 : 0;
  // 그레이엄 매수 적정가 (NCAV의 66.7%)
  const grahamTargetPrice = Math.round(ncavPerShare * 0.667);

  return (
    <div className="w-full space-y-6">
      {/* Kicker header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-2 pb-2 border-b border-slate-800">
        <div>
          <h2 className="text-lg font-bold text-white flex items-center gap-2">
            <Calculator className="w-5 h-5 text-amber-400" />
            벤저민 그레이엄 순유동자산(NCAV) 가치투자 계산기
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            기업이 당장 청산했을 때 주주에게 배분될 수 있는 순유동자산가치(청산가치)와 안전마진을 산출합니다.
          </p>
        </div>

        {/* Preset Buttons */}
        <div className="flex items-center gap-1.5 flex-wrap">
          <span className="text-xs text-slate-500 mr-1">예시 종목:</span>
          {presets.map((p) => (
            <button
              key={p.code}
              onClick={() => applyPreset(p)}
              className={`px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors cursor-pointer ${
                stockName === p.name
                  ? 'bg-amber-950/70 text-amber-300 border border-amber-800/60'
                  : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'
              }`}
            >
              {p.name}
            </button>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Left Form: Inputs */}
        <div className="lg:col-span-6 bg-[#131B2A] border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-bold text-slate-200">재무제표 데이터 입력</h3>
            <span className="text-[11px] text-slate-500">단위: 억원 / 만주</span>
          </div>

          <div className="space-y-3">
            <div>
              <label className="block text-xs font-medium text-slate-300 mb-1">
                종목명
              </label>
              <input
                type="text"
                value={stockName}
                onChange={(e) => setStockName(e.target.value)}
                className="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500 font-semibold"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  현재 주가 (원)
                </label>
                <input
                  type="number"
                  value={currentPrice || ''}
                  onChange={(e) => setCurrentPrice(Number(e.target.value))}
                  className="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-mono tabular-nums text-white focus:outline-none focus:border-amber-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  발행주식 총수 (만 주)
                </label>
                <input
                  type="number"
                  value={sharesCount || ''}
                  onChange={(e) => setSharesCount(Number(e.target.value))}
                  className="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-mono tabular-nums text-white focus:outline-none focus:border-amber-500"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-medium text-emerald-400 mb-1">
                  유동자산 (억 원)
                </label>
                <input
                  type="number"
                  value={currentAssets || ''}
                  onChange={(e) => setCurrentAssets(Number(e.target.value))}
                  className="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-mono tabular-nums text-emerald-300 focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-rose-400 mb-1">
                  총부채 (억 원)
                </label>
                <input
                  type="number"
                  value={totalLiabilities || ''}
                  onChange={(e) => setTotalLiabilities(Number(e.target.value))}
                  className="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-mono tabular-nums text-rose-300 focus:outline-none focus:border-rose-500"
                />
              </div>
            </div>
          </div>

          <div className="p-3 bg-slate-900/60 rounded-xl border border-slate-800 text-[11px] text-slate-400 leading-relaxed">
            💡 <strong>순유동자산(NCAV)이란?</strong> 유동자산에서 모든 부채(유동부채+비유동부채)를 차감한 금액으로, 공장이나 토지 등 고정자산을 0원으로 가정해도 주주에게 남는 순 현금성 가치입니다.
          </div>
        </div>

        {/* Right Output: Results */}
        <div className="lg:col-span-6 bg-[#131B2A] border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
          <div>
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-sm font-bold text-slate-200">
                {stockName} 가치평가 분석 결과
              </h3>
              <div className="flex items-center gap-1.5">
                {safetyMargin >= 33.3 ? (
                  <span className="flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800 text-[11px] font-semibold">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    그레이엄 기준 충족
                  </span>
                ) : safetyMargin > 0 ? (
                  <span className="flex items-center gap-1 px-2 py-0.5 rounded bg-amber-950 text-amber-400 border border-amber-800 text-[11px] font-semibold">
                    <AlertTriangle className="w-3.5 h-3.5" />
                    보통 저평가
                  </span>
                ) : (
                  <span className="flex items-center gap-1 px-2 py-0.5 rounded bg-rose-950 text-rose-400 border border-rose-800 text-[11px] font-semibold">
                    <ShieldAlert className="w-3.5 h-3.5" />
                    안전마진 부족
                  </span>
                )}
              </div>
            </div>

            {/* Key Metric Highlight Cards */}
            <div className="grid grid-cols-2 gap-3 my-4">
              <div className="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800">
                <span className="text-[11px] text-slate-400 block mb-1">
                  주당 순유동자산가치 (NCAV/주)
                </span>
                <span className="text-xl font-bold font-mono tabular-nums text-white">
                  {ncavPerShare.toLocaleString()} 원
                </span>
                <span className="text-[10px] text-slate-500 block mt-1">
                  순유동자산 총액: {ncavEok.toLocaleString()} 억
                </span>
              </div>

              <div className="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800">
                <span className="text-[11px] text-slate-400 block mb-1">
                  안전마진 (Margin of Safety)
                </span>
                <span
                  className={`text-xl font-bold font-mono tabular-nums ${
                    safetyMargin > 0 ? 'text-emerald-400' : 'text-rose-400'
                  }`}
                >
                  {safetyMargin.toFixed(1)}%
                </span>
                <span className="text-[10px] text-slate-500 block mt-1">
                  (NCAV 대비 현재가 할인율)
                </span>
              </div>
            </div>

            {/* Graham Target Comparison */}
            <div className="p-4 rounded-xl bg-slate-900 border border-slate-800/80 space-y-2">
              <div className="flex justify-between items-center text-xs">
                <span className="text-slate-400">현재 시장가:</span>
                <span className="font-mono tabular-nums font-semibold text-slate-200">
                  {currentPrice.toLocaleString()} 원
                </span>
              </div>
              <div className="flex justify-between items-center text-xs">
                <span className="text-slate-400">그레이엄 목표 매수가 (NCAV × 66.7%):</span>
                <span className="font-mono tabular-nums font-bold text-amber-400">
                  {grahamTargetPrice.toLocaleString()} 원
                </span>
              </div>
              <div className="pt-2 border-t border-slate-800/80 flex justify-between items-center text-xs">
                <span className="text-slate-400">현재가 대비 적정가 괴리율:</span>
                <span className="font-mono tabular-nums font-bold text-cyan-400">
                  {currentPrice > 0
                    ? (((grahamTargetPrice - currentPrice) / currentPrice) * 100).toFixed(1)
                    : 0}
                  %
                </span>
              </div>
            </div>
          </div>

          <div className="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <span>벤저민 그레이엄 퀀트 기준:</span>
            <span className="font-mono text-emerald-400">주가 &lt; (NCAV × 0.67) 매수 전략</span>
          </div>
        </div>
      </div>
    </div>
  );
};
