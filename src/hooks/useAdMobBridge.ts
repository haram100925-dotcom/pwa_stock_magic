import { useEffect, useState, useCallback } from 'react';

export const ADMOB_INTERSTITIAL_UNIT_ID = 'ca-app-pub-2281115817010682/1881691673';

declare global {
  interface Window {
    AndroidBridge?: {
      showBanner?: (position: string) => void;
      hideBanner?: () => void;
      showInterstitial?: (unitId?: string) => void;
      showInterstitialAd?: (unitId?: string) => void;
      isAdReady?: () => boolean;
    };
  }
}

export function useAdMobBridge() {
  const [hasAndroidBridge, setHasAndroidBridge] = useState(false);
  const [admobStatus, setAdmobStatus] = useState<string>('Web AdSense / PWA Mode');
  const [interstitialActive, setInterstitialActive] = useState(false);
  const [interstitialKey, setInterstitialKey] = useState(0);
  const [countdown, setCountdown] = useState(6);

  const defaultAdClientId = 'ca-pub-2281115817010682';
  const defaultBannerSlot = '2118625088';

  useEffect(() => {
    if (typeof window !== 'undefined' && window.AndroidBridge) {
      setHasAndroidBridge(true);
      setAdmobStatus('TWA / Native AdMob Connected');
      window.AndroidBridge.showBanner?.('bottom');
    }
  }, []);

  // Countdown timer effect when interstitial modal is active
  useEffect(() => {
    if (!interstitialActive) return;

    const interval = setInterval(() => {
      setCountdown((prev) => (prev <= 1 ? 0 : prev - 1));
    }, 1000);

    return () => clearInterval(interval);
  }, [interstitialActive, interstitialKey]);

  // Auto-close modal cleanly when countdown reaches 0
  useEffect(() => {
    if (interstitialActive && countdown === 0) {
      setInterstitialActive(false);
    }
  }, [interstitialActive, countdown]);

  const closeInterstitial = useCallback(() => {
    setInterstitialActive(false);
  }, []);

  const executeInterstitial = useCallback((): boolean => {
    // 1. If running inside a native Android WebView wrapper with AdMob SDK bridge
    if (typeof window !== 'undefined' && window.AndroidBridge) {
      const bridge = window.AndroidBridge;
      const ready = typeof bridge.isAdReady === 'function' ? bridge.isAdReady() : true;

      if (ready && (bridge.showInterstitialAd || bridge.showInterstitial)) {
        try {
          if (bridge.showInterstitialAd) {
            bridge.showInterstitialAd(ADMOB_INTERSTITIAL_UNIT_ID);
          } else if (bridge.showInterstitial) {
            if (bridge.showInterstitial.length >= 1) {
              bridge.showInterstitial(ADMOB_INTERSTITIAL_UNIT_ID);
            } else {
              bridge.showInterstitial();
            }
          }
          setAdmobStatus(`Native Interstitial Requested (${ADMOB_INTERSTITIAL_UNIT_ID})`);
          return true;
        } catch (err) {
          console.warn('Native interstitial failed, falling back to Web full-screen overlay', err);
        }
      }
    }

    // 2. Web / PWA Full-Screen Interstitial Overlay
    setCountdown(6);
    setInterstitialKey((k) => k + 1);
    setInterstitialActive(true);
    return true;
  }, []);

  /**
   * Called when entering a stock detail page (stockDetail.php).
   */
  const handleDetailNavigation = useCallback((): boolean => {
    if (interstitialActive) {
      return false;
    }
    return executeInterstitial();
  }, [executeInterstitial, interstitialActive]);

  return {
    hasAndroidBridge,
    admobStatus,
    interstitialActive,
    interstitialKey,
    countdown,
    adClientId: defaultAdClientId,
    admobInterstitialUnitId: ADMOB_INTERSTITIAL_UNIT_ID,
    bannerSlot: defaultBannerSlot,
    handleDetailNavigation,
    executeInterstitial,
    closeInterstitial,
  };
}
