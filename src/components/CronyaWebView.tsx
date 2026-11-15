import React, { useState } from 'react';
import { ExternalLink, RefreshCw, Globe, Maximize2, Minimize2, AlertCircle } from 'lucide-react';

export const CronyaWebView: React.FC = () => {
  const [currentUrl, setCurrentUrl] = useState('https://cronya.pe.kr/stock/main.php');
  const [iframeKey, setIframeKey] = useState(1);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [loadError, setLoadError] = useState(false);

  const refreshIframe = () => {
    setIframeKey((prev) => prev + 1);
    setLoadError(false);
  };

  return (
    <div className={`w-full flex flex-col ${isFullscreen ? 'fixed inset-0 z-50 bg-[#0B0F17]' : 'space-y-3'}`}>
      {/* WebView Browser Navigation Bar */}
      <div className="flex items-center justify-between gap-3 bg-[#131B2A] border border-slate-800 rounded-xl px-4 py-2.5 shadow-lg">
        <div className="flex items-center gap-2 flex-1 min-w-0">
          <Globe className="w-4 h-4 text-emerald-400 shrink-0" />
          <div className="flex-1 min-w-0 px-3 py-1 bg-slate-900 border border-slate-800 rounded-lg text-xs font-mono text-slate-300 truncate">
            {currentUrl}
          </div>
        </div>

        {/* Toolbar Action Buttons */}
        <div className="flex items-center gap-2 shrink-0">
          <button
            onClick={refreshIframe}
            title="새로고침"
            className="p-1.5 rounded-lg bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 hover:text-white transition-colors cursor-pointer"
          >
            <RefreshCw className="w-3.5 h-3.5" />
          </button>

          <button
            onClick={() => setIsFullscreen(!isFullscreen)}
            title={isFullscreen ? '창 축소' : '전체화면'}
            className="p-1.5 rounded-lg bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 hover:text-white transition-colors cursor-pointer"
          >
            {isFullscreen ? <Minimize2 className="w-3.5 h-3.5" /> : <Maximize2 className="w-3.5 h-3.5" />}
          </button>

          <a
            href={currentUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
          >
            <span>새 탭으로 열기</span>
            <ExternalLink className="w-3 h-3" />
          </a>
        </div>
      </div>

      {/* Embedded Iframe Container */}
      <div className="relative w-full rounded-xl border border-slate-800 overflow-hidden bg-slate-900 shadow-2xl flex-1 min-h-[650px]">
        {loadError ? (
          <div className="absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-[#0B0F17]">
            <AlertCircle className="w-12 h-12 text-amber-400 mb-3" />
            <h3 className="text-base font-bold text-white mb-1">
              외부 사이트 직접 임베딩 보안 제한
            </h3>
            <p className="text-xs text-slate-400 max-w-md mb-4">
              브라우저의 혼합 콘텐츠(Mixed Content) 또는 사이트의 보안 설정으로 인해 웹뷰 내 임베딩이 차단될 수 있습니다. 새 탭에서 열거나 상단의 PWA 내장 신 마법공식 대시보드를 이용해 주세요.
            </p>
            <a
              href="https://cronya.pe.kr/stock/main.php"
              target="_blank"
              rel="noopener noreferrer"
              className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-md hover:bg-emerald-500 transition-colors"
            >
              크로냐 원본 사이트 새 창으로 열기
            </a>
          </div>
        ) : (
          <iframe
            key={iframeKey}
            src="https://cronya.pe.kr/stock/main.php"
            title="크로냐 주식 원본 웹뷰"
            className="w-full h-full min-h-[650px] border-none bg-white"
            onError={() => setLoadError(true)}
            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
          />
        )}
      </div>
    </div>
  );
};
