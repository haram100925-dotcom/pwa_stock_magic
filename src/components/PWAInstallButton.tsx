import React, { useState } from 'react';
import { usePWAInstall } from '../hooks/usePWAInstall';
import { Download, Share, PlusSquare, X, CheckCircle2 } from 'lucide-react';

export const PWAInstallButton: React.FC = () => {
  const { isInstallable, isInstalled, isIOS, install } = usePWAInstall();
  const [showIOSGuide, setShowIOSGuide] = useState(false);
  const [justInstalled, setJustInstalled] = useState(false);

  if (isInstalled) {
    return (
      <div className="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-950/40 border border-emerald-800/40 text-emerald-400 text-xs font-medium">
        <CheckCircle2 className="w-3.5 h-3.5" />
        <span>앱 설치됨</span>
      </div>
    );
  }

  const handleInstallClick = async () => {
    const success = await install();
    if (success) {
      setJustInstalled(true);
      setTimeout(() => setJustInstalled(false), 3000);
    }
  };

  return (
    <>
      {isInstallable && (
        <button
          onClick={handleInstallClick}
          className="flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-3 py-1.5 text-xs font-semibold text-white shadow-md shadow-emerald-900/30 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition-all whitespace-nowrap cursor-pointer"
        >
          <Download className="w-3.5 h-3.5" />
          <span>{justInstalled ? '설치 완료!' : '앱 설치 (PWA)'}</span>
        </button>
      )}

      {isIOS && !isInstallable && (
        <button
          onClick={() => setShowIOSGuide(true)}
          className="flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:bg-slate-700 active:scale-95 transition-all whitespace-nowrap cursor-pointer"
        >
          <Download className="w-3.5 h-3.5 text-amber-400" />
          <span>iOS 홈 화면 추가</span>
        </button>
      )}

      {/* Fallback direct installation prompt for desktop/mobile browsers that support manual prompt or info */}
      {!isInstallable && !isIOS && !isInstalled && (
        <button
          onClick={() => alert('브라우저 우측 상단 주소창의 설치 아이콘(⊕) 또는 메뉴에서 [홈 화면에 추가]를 눌러 앱을 설치할 수 있습니다.')}
          className="hidden md:flex items-center gap-1.5 rounded-lg border border-slate-800 bg-slate-900/80 px-2.5 py-1.5 text-xs font-medium text-slate-400 hover:text-slate-200 hover:border-slate-700 transition-all whitespace-nowrap cursor-pointer"
        >
          <Download className="w-3.5 h-3.5 text-slate-400" />
          <span>앱 설치 안내</span>
        </button>
      )}

      {/* iOS Installation Guide Modal */}
      {showIOSGuide && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4">
          <div className="w-full max-w-sm rounded-2xl bg-[#131B2A] border border-slate-800 p-6 shadow-2xl text-slate-100">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-amber-400" />
                iPhone / iPad 앱 설치 방법
              </h3>
              <button
                onClick={() => setShowIOSGuide(false)}
                className="text-slate-400 hover:text-white p-1 rounded-md"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="mt-4 space-y-3.5 text-xs text-slate-300 leading-relaxed">
              <div className="flex items-start gap-3">
                <div className="flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 text-amber-400 shrink-0 font-bold">
                  1
                </div>
                <p>
                  Safari 하단 도구 막대의 <strong>공유(Share)</strong> <Share className="w-3.5 h-3.5 inline mx-1 text-sky-400" /> 버튼을 탭합니다.
                </p>
              </div>

              <div className="flex items-start gap-3">
                <div className="flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 text-amber-400 shrink-0 font-bold">
                  2
                </div>
                <p>
                  메뉴를 아래로 스크롤하여 <strong>'홈 화면에 추가'</strong> <PlusSquare className="w-3.5 h-3.5 inline mx-1 text-emerald-400" /> 항목을 선택합니다.
                </p>
              </div>

              <div className="flex items-start gap-3">
                <div className="flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 text-amber-400 shrink-0 font-bold">
                  3
                </div>
                <p>
                  우측 상단의 <strong>'추가'</strong>를 누르면 홈 화면에 전용 앱 아이콘이 생성되어 전체 화면으로 실행됩니다.
                </p>
              </div>
            </div>

            <button
              onClick={() => setShowIOSGuide(false)}
              className="mt-6 w-full rounded-xl bg-slate-800 hover:bg-slate-700 py-2.5 text-xs font-semibold text-white transition-colors cursor-pointer"
            >
              확인 완료
            </button>
          </div>
        </div>
      )}
    </>
  );
};
