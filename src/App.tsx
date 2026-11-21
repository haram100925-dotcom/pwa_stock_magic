import React, { useState, useEffect, useRef, useCallback } from 'react';
import { AdBanner } from './components/AdBanner';
import { OfflineIndicator } from './components/OfflineIndicator';
import { usePWAInstall } from './hooks/usePWAInstall';
import { Download } from 'lucide-react';

const BANNER_REFRESH_COOLDOWN_MS = 30_000; // 30 seconds minimum cooldown for AdSense policy compliance

export default function App() {
  const iframeRef = useRef<HTMLIFrameElement | null>(null);
  const lastTriggeredDetailUrlRef = useRef<string>('');
  const lastVisitedUrlRef = useRef<string>('');
  const lastBannerRefreshRef = useRef<number>(Date.now());
  const loadCountRef = useRef<number>(0);
  const triggerAdRef = useRef<(() => boolean) | null>(null);
  const mountTimeRef = useRef<number>(Date.now());
  const splashDismissedRef = useRef<boolean>(false);

  const [isIframeLoading, setIsIframeLoading] = useState(true);
  const [showSplash, setShowSplash] = useState(true);
  const [splashFading, setSplashFading] = useState(false);
  const [bannerRefreshKey, setBannerRefreshKey] = useState(0);
  const { isInstallable, install } = usePWAInstall();

  // The exact, canonical URL of the original webview site
  const mainUrl = 'https://cronya.pe.kr/stock/main.php';

  const dismissSplash = useCallback(() => {
    if (splashDismissedRef.current) return;
    splashDismissedRef.current = true;

    const elapsed = Date.now() - mountTimeRef.current;
    const minDisplayMs = 1100;
    const waitMs = Math.max(0, minDisplayMs - elapsed);

    setTimeout(() => {
      setSplashFading(true);
      setTimeout(() => {
        setShowSplash(false);
      }, 300);
    }, waitMs);
  }, []);

  // Safety timers so splash screen & progress bar never hang
  useEffect(() => {
    const progressTimer = setTimeout(() => {
      setIsIframeLoading(false);
    }, 2500);

    const splashSafetyTimer = setTimeout(() => {
      dismissSplash();
    }, 1800);

    return () => {
      clearTimeout(progressTimer);
      clearTimeout(splashSafetyTimer);
    };
  }, [dismissSplash]);

  // Refresh the bottom 50px banner on page navigation when 30s cooldown has elapsed
  const refreshBannerIfReady = useCallback(() => {
    const now = Date.now();
    if (now - lastBannerRefreshRef.current >= BANNER_REFRESH_COOLDOWN_MS) {
      lastBannerRefreshRef.current = now;
      setBannerRefreshKey((k) => k + 1);
    }
  }, []);

  // Check current same-origin iframe URL for both bottom banner refresh and stockDetail.php interstitial
  const checkIframeNavigation = useCallback(() => {
    try {
      const win = iframeRef.current?.contentWindow;
      if (!win) return;
      const currentHref = win.location.href;
      if (!currentHref || currentHref === 'about:blank') return;

      // 1. Bottom banner refresh on any page change (respecting 30s cooldown)
      if (!lastVisitedUrlRef.current) {
        lastVisitedUrlRef.current = currentHref;
      } else if (lastVisitedUrlRef.current !== currentHref) {
        lastVisitedUrlRef.current = currentHref;
        refreshBannerIfReady();
      }

      // 2. Full-screen interstitial ONLY when entering stockDetail.php
      if (currentHref.includes('stockDetail.php')) {
        if (lastTriggeredDetailUrlRef.current !== currentHref) {
          lastTriggeredDetailUrlRef.current = currentHref;
          triggerAdRef.current?.();
        }
      } else {
        lastTriggeredDetailUrlRef.current = '';
      }
    } catch {
      // Cross-origin access blocked (e.g. in preview environment) - ignore silently
    }
  }, [refreshBannerIfReady]);

  // Fast polling for same-origin iframe URL changes (catches stockDitalMove, links, history.back)
  useEffect(() => {
    const pollInterval = setInterval(() => {
      checkIframeNavigation();
    }, 250);

    return () => clearInterval(pollInterval);
  }, [checkIframeNavigation]);

  const handleIframeLoad = () => {
    setIsIframeLoading(false);
    loadCountRef.current += 1;
    dismissSplash();

    let checkedSameOrigin = false;
    try {
      const win = iframeRef.current?.contentWindow;
      if (win) {
        // Verify same-origin access works
        void win.location.href;
        checkedSameOrigin = true;
        checkIframeNavigation();
        win.addEventListener('pageshow', checkIframeNavigation);
        win.addEventListener('popstate', checkIframeNavigation);
      }
    } catch {
      // Cross-origin preview environment
    }

    // Fallback for cross-origin preview: refresh bottom banner on subsequent iframe loads
    if (!checkedSameOrigin && loadCountRef.current > 1) {
      refreshBannerIfReady();
    }
  };

  const handleAdMobReady = useCallback((triggerFn: () => boolean) => {
    triggerAdRef.current = triggerFn;
  }, []);

  return (
    <div className="fixed inset-0 w-full h-full bg-[#0B0F17] overflow-hidden select-none font-sans text-slate-100">
      {/* Launch Splash Screen (pay_15fdf67c.jpg) shown briefly when app opens */}
      {showSplash && (
        <div
          aria-hidden="true"
          className={`fixed inset-0 z-[90] bg-[#0B0F17] flex items-center justify-center transition-opacity duration-300 ${
            splashFading ? 'opacity-0 pointer-events-none' : 'opacity-100'
          }`}
          style={{ position: 'fixed', inset: 0, zIndex: 90 }}
        >
          <img
            src="./splash.jpg"
            alt="신마법공식 시작 화면"
            className="w-full h-full object-cover"
          />
        </div>
      )}

      {/* Top subtle indeterminate progress bar when iframe is loading (never blocks) */}
      {isIframeLoading && !showSplash && (
        <div className="fixed top-0 left-0 right-0 h-1 bg-slate-900 z-50 overflow-hidden pointer-events-none">
          <div className="h-full bg-gradient-to-r from-emerald-500 via-teal-400 to-amber-400 w-1/2 animate-[loadingBar_1.2s_ease-in-out_infinite]" />
        </div>
      )}

      {/* Offline Toast */}
      <OfflineIndicator />

      {/* Discrete floating install button (Only shown if browser permits install, no top header) */}
      {isInstallable && !showSplash && (
        <button
          onClick={install}
          title="홈 화면에 앱 설치하기"
          className="fixed top-3 right-3 z-40 flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-600/90 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg backdrop-blur-sm border border-emerald-400/40 active:scale-95 transition-all cursor-pointer animate-pulse"
        >
          <Download className="w-3.5 h-3.5" />
          <span>앱 설치</span>
        </button>
      )}

      {/* Main Viewport: Fixed from top:0 to bottom:50px so it NEVER overlaps with the bottom fixed banner */}
      <main
        className="fixed top-0 left-0 right-0 w-full bg-white overflow-hidden"
        style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: '50px',
          width: '100%',
          overflow: 'hidden',
          backgroundColor: '#ffffff',
        }}
      >
        <iframe
          ref={iframeRef}
          src={mainUrl}
          title="신마법공식 웹뷰"
          className="w-full h-full border-0 bg-white"
          style={{
            width: '100%',
            height: '100%',
            border: 'none',
            display: 'block',
          }}
          onLoad={handleIframeLoad}
          allow="fullscreen; clipboard-read; clipboard-write; autoplay"
        />
      </main>

      {/* Bottom Ad Banner (50px fixed, refreshes on page change with 30s cooldown) + Stock Detail Interstitial */}
      <AdBanner bannerKey={bannerRefreshKey} onAdMobReady={handleAdMobReady} />
    </div>
  );
}
