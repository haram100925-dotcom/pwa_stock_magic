import React from 'react';
import { BookOpen, Check, TrendingUp, Layers, HelpCircle } from 'lucide-react';

export const StrategyGuide: React.FC = () => {
  return (
    <div className="w-full space-y-6">
      <div className="pb-3 border-b border-slate-800">
        <h2 className="text-lg font-bold text-white flex items-center gap-2">
          <BookOpen className="w-5 h-5 text-emerald-400" />
          가치투자 전략 및 신 마법공식 가이드
        </h2>
        <p className="text-xs text-slate-400 mt-1">
          크로냐 주식 가치투자 시스템의 핵심 알고리즘과 퀀트 투자 원칙을 안내합니다.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Strategy 1: New Magic Formula */}
        <div className="bg-[#131B2A] border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
          <div className="flex items-center gap-2 text-sm font-bold text-emerald-400">
            <TrendingUp className="w-4 h-4" />
            <span>신(新) 마법공식 (New Magic Formula)</span>
          </div>

          <p className="text-xs text-slate-300 leading-relaxed">
            로버트 노비-마르크스(Novy-Marx) 교수의 총자산총이익률(GP/A) 논문과 전통 가치지표(PBR)를 결합하여, 수익성이 높으면서도 극단적으로 저평가된 우량주를 발굴하는 전략입니다.
          </p>

          <div className="p-3 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5 text-xs font-mono text-slate-300">
            <div><strong className="text-amber-400">선정 기준:</strong></div>
            <div className="text-[11px] text-slate-400 pl-2 space-y-1">
              <div>• 시가총액 500억 이상 & 상장 주식수 1,500만 주 이상</div>
              <div>• 배당수익률 &gt; 0% & PBR &lt; 100배 이하</div>
              <div>• 금융/보험 지주사 제외 (재무제표 특수성 고려)</div>
              <div>• <span className="text-emerald-300 font-bold">PBR 낮은 순위</span> + <span className="text-amber-300 font-bold">GP/A 높은 순위</span> 합산 상위 100위</div>
            </div>
          </div>

          <div className="text-xs text-slate-400">
            <strong>GP/A 산출식:</strong> 매출총이익 ÷ 자산총계 (영업 효율성 측정)
          </div>
        </div>

        {/* Strategy 2: Graham Net-Net */}
        <div className="bg-[#131B2A] border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
          <div className="flex items-center gap-2 text-sm font-bold text-amber-400">
            <Layers className="w-4 h-4" />
            <span>벤저민 그레이엄 순유동자산 (NCAV) 전략</span>
          </div>

          <p className="text-xs text-slate-300 leading-relaxed">
            가치투자의 창시자 벤저민 그레이엄이 고안한 가장 보수적인 담배꽁초(Cigar Butt) 투자 전략으로, 기업의 순유동자산보다 주가가 낮게 거래되는 안전마진 종목을 매수합니다.
          </p>

          <div className="p-3 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5 text-xs font-mono text-slate-300">
            <div><strong className="text-amber-400">핵심 공식:</strong></div>
            <div className="text-[11px] text-slate-400 pl-2 space-y-1">
              <div>• 순유동자산(NCAV) = 유동자산 - 총부채</div>
              <div>• 주당 NCAV = 순유동자산 ÷ 총발행주식수</div>
              <div>• 그레이엄 매수 기준: <span className="text-amber-300 font-bold">현재 주가 &lt; 주당 NCAV × 66.7%</span></div>
              <div>• 매도 원칙: 매수가 대비 50% 상승 또는 2년 경과 시 리밸런싱</div>
            </div>
          </div>

          <div className="text-xs text-slate-400">
            <strong>핵심 가치:</strong> 고정자산이 0원이 되더라도 원금을 회수할 수 있는 극단적 안전마진 확보.
          </div>
        </div>
      </div>

      {/* Practical Guide */}
      <div className="bg-[#131B2A] border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
        <h3 className="text-sm font-bold text-slate-200 flex items-center gap-2">
          <HelpCircle className="w-4 h-4 text-cyan-400" />
          실전 포트폴리오 운용 수칙
        </h3>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-300">
          <div className="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <div className="flex items-center gap-1.5 font-bold text-white">
              <Check className="w-4 h-4 text-emerald-400" />
              <span>20~30종목 분산투자</span>
            </div>
            <p className="text-slate-400 text-[11px] leading-relaxed">
              개별 종목의 단기 악재나 실적 변동성을 방어하기 위해 상위 20~30개 종목에 동일 비중으로 분산 투자합니다.
            </p>
          </div>

          <div className="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <div className="flex items-center gap-1.5 font-bold text-white">
              <Check className="w-4 h-4 text-emerald-400" />
              <span>연 1회 정기 리밸런싱</span>
            </div>
            <p className="text-slate-400 text-[11px] leading-relaxed">
              잦은 매매를 피하고 1년에 1회 정해진 주기(예: 매년 4~5월 사업보고서 공시 후)에 순위를 재산출하여 교체합니다.
            </p>
          </div>

          <div className="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
            <div className="flex items-center gap-1.5 font-bold text-white">
              <Check className="w-4 h-4 text-emerald-400" />
              <span>PWA 앱 북마크 활용</span>
            </div>
            <p className="text-slate-400 text-[11px] leading-relaxed">
              관심 있는 종목을 별표(Star)로 즐겨찾기하여 My List에 저장해두고 주가 변동과 지표 변화를 추적하세요.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
