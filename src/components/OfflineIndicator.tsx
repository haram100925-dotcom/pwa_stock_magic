import React from 'react';
import { useOnlineStatus } from '../hooks/useOnlineStatus';
import { WifiOff } from 'lucide-react';

export const OfflineIndicator: React.FC = () => {
  const isOnline = useOnlineStatus();

  if (isOnline) return null;

  return (
    <div
      role="status"
      aria-live="polite"
      className="fixed bottom-20 left-4 z-50 flex items-center gap-2 rounded-lg bg-amber-500/95 px-3.5 py-2 text-xs font-semibold text-slate-950 shadow-xl backdrop-blur-sm border border-amber-400"
    >
      <WifiOff className="w-4 h-4 text-slate-950 animate-pulse" />
      <span>오프라인 모드 — 캐시된 주식 분석 데이터를 표시 중입니다.</span>
    </div>
  );
};
