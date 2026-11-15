import React, { useEffect, useRef } from 'react';
import { useAdMobBridge } from '../hooks/useAdMobBridge';
import { X } from 'lucide-react';

interface AdBannerProps {
  onAdMobReady?: (triggerFn: () => boolean) => void;
}

export const AdBanner: React.FC<AdBannerProps> = ({ onAdMobReady }) => {
  const {
    interstitialActive,
    interstitialKey,
    countdown,
    adClientId,
    admobInterstitialUnitId,
    handleDetailNavigation,
    closeInterstitial,
  } = useAdMobBridge();

  const adSlotId = '2118625088';
  const adContainerRef = useRef<HTMLDivElement | null>(null);
  const modalAdContainerRef = useRef<HTMLDivElement | null>(null);
  const pushedRef = useRef<boolean>(false);

  // Register the trigger function to the parent (App.tsx)
  useEffect(() => {
    if (onAdMobReady) {
      onAdMobReady(handleDetailNavigation);
    }
  }, [onAdMobReady, handleDetailNavigation]);

  // Bottom banner AdSense initialization (single execution guard)
  useEffect(() => {
    if (pushedRef.current) return;
    if (!adContainerRef.current) return;

    const ins = adContainerRef.current.querySelector('ins.adsbygoogle');
    if (!ins || ins.getAttribute('data-adsbygoogle-status')) return;

    try {
      if (typeof window !== 'undefined') {
        const win = window as unknown as { adsbygoogle?: unknown[] };
        win.adsbygoogle = win.adsbygoogle || [];
        win.adsbygoogle.push({});
        pushedRef.current = true;
      }
    } catch {
      // Ignore if AdSense blocked or already loaded
    }
  }, []);

  // Initialize full-screen AdSense unit inside the interstitial overlay whenever it opens
  useEffect(() => {
    if (!interstitialActive) return;

    const timer = setTimeout(() => {
      if (!modalAdContainerRef.current) return;
      const ins = modalAdContainerRef.current.querySelector('ins.adsbygoogle');
      if (!ins || ins.getAttribute('data-adsbygoogle-status')) return;

      try {
        if (typeof window !== 'undefined') {
          const win = window as unknown as { adsbygoogle?: unknown[] };
          win.adsbygoogle = win.adsbygoogle || [];
          win.adsbygoogle.push({});
        }
      } catch {
        // Ignore if AdSense blocked
      }
    }, 60);

    return () => clearTimeout(timer);
  }, [interstitialActive, interstitialKey]);

  return (
    <>
      {/* 1. Permanent Fixed 50px Bottom Strip Banner (Width 100%, Height 50px fixed, never shifts on scroll) */}
      <aside
        aria-label="하단 고정 광고"
        className="fixed bottom-0 left-0 right-0 w-full h-[50px] max-h-[50px] min-h-[50px] overflow-hidden bg-[#0B0F17] border-t border-slate-800 z-50 px-2 flex items-center justify-between select-none shadow-[0_-2px_10px_rgba(0,0,0,0.5)]"
        style={{
          position: 'fixed',
          bottom: 0,
          left: 0,
          right: 0,
          width: '100%',
          height: '50px',
          maxHeight: '50px',
          minHeight: '50px',
          zIndex: 50,
        }}
      >
        <div className="w-full flex items-center justify-between gap-2 h-full overflow-hidden">
          <div className="flex items-center gap-1 shrink-0">
            <span className="px-1.5 py-0.5 rounded bg-slate-800 text-[9px] font-mono font-bold text-slate-300 border border-slate-700">
              AD
            </span>
          </div>

          <div
            ref={adContainerRef}
            className="flex-1 flex items-center justify-center w-full min-w-0 h-[50px] max-h-[50px] overflow-hidden"
            style={{ height: '50px', maxHeight: '50px' }}
          >
            <ins
              className="adsbygoogle"
              style={{
                display: 'block',
                width: '100%',
                height: '50px',
                maxHeight: '50px',
                minHeight: '50px',
                overflow: 'hidden',
              }}
              data-ad-client={adClientId}
              data-ad-slot={adSlotId}
            />
          </div>

          <div className="hidden sm:flex items-center text-[9px] font-mono text-slate-600 shrink-0">
            {adClientId}
          </div>
        </div>
      </aside>

      {/* 2. True Full-Screen Interstitial Ad Overlay (Triggered on stockDetail.php navigation) */}
      {interstitialActive && (
        <div
          key={interstitialKey}
          role="dialog"
          aria-modal="true"
          aria-label="전면 광고"
          data-admob-unit={admobInterstitialUnitId}
          className="fixed inset-0 z-[100] w-screen h-screen bg-[#0B0F17] flex flex-col select-none overflow-hidden"
          style={{ position: 'fixed', inset: 0, width: '100vw', height: '100vh', zIndex: 100 }}
        >
          {/* Minimal Interstitial Top Bar (Ad badge + countdown + Close button) */}
          <div className="shrink-0 w-full h-12 px-4 flex items-center justify-between bg-slate-950/95 border-b border-slate-800/80">
            <div className="flex items-center gap-2">
              <span className="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-[11px] font-mono font-bold border border-slate-700">
                AD
              </span>
              <span className="text-xs text-slate-400">
                {countdown > 0 ? `${countdown}초 후 닫힘` : '닫기 가능'}
              </span>
            </div>
            <button
              onClick={closeInterstitial}
              aria-label="광고 닫기"
              className="flex items-center gap-1 px-3 py-1.5 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-600 active:scale-95 transition-all cursor-pointer"
            >
              <X className="w-4 h-4" />
              <span>닫기</span>
            </button>
          </div>

          {/* Full-Viewport Ad Creative Container (Fills 100% remaining height & width) */}
          <div
            ref={modalAdContainerRef}
            className="flex-1 w-full h-full flex items-center justify-center bg-[#0B0F17] p-2 overflow-hidden"
            style={{ width: '100%', height: 'calc(100vh - 48px)' }}
          >
            <ins
              className="adsbygoogle"
              style={{
                display: 'block',
                width: '100%',
                height: '100%',
                minHeight: 'calc(100vh - 64px)',
              }}
              data-ad-client={adClientId}
              data-ad-slot={adSlotId}
              data-ad-format="auto"
              data-full-width-responsive="true"
            />
          </div>
        </div>
      )}
    </>
  );
};
