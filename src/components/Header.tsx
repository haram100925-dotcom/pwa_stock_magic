import React from 'react';
import { PWAInstallButton } from './PWAInstallButton';

interface HeaderProps {
  activeTab: 'magic' | 'graham' | 'webview' | 'guide';
  setActiveTab: (tab: 'magic' | 'graham' | 'webview' | 'guide') => void;
}

export const Header: React.FC<HeaderProps> = ({ activeTab, setActiveTab }) => {
  return (
    <header className="sticky top-0 z-40 w-full bg-[#0B0F17]/95 backdrop-blur-md border-b border-slate-800 px-4 md:px-8 py-3.5 flex items-center justify-between gap-8">
      {/* Zone 1: Single text element wordmark */}
      <a
        href="/"
        onClick={(e) => {
          e.preventDefault();
          setActiveTab('magic');
        }}
        className="flex items-center gap-2.5 text-lg font-bold tracking-tight text-white whitespace-nowrap shrink-0 group cursor-pointer"
      >
        <div className="w-7 h-7 rounded-lg bg-gradient-to-tr from-emerald-500 to-amber-500 flex items-center justify-center text-slate-950 font-extrabold text-sm shadow-md shadow-emerald-950">
          S
        </div>
        <span>크로냐 신마법공식</span>
      </a>

      {/* Zone 2: 4-5 clean single-line text navigation links */}
      <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-slate-400">
        <button
          onClick={() => setActiveTab('magic')}
          className={`hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer ${
            activeTab === 'magic' ? 'text-emerald-400 font-semibold' : ''
          }`}
        >
          신 마법공식
        </button>
        <button
          onClick={() => setActiveTab('graham')}
          className={`hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer ${
            activeTab === 'graham' ? 'text-emerald-400 font-semibold' : ''
          }`}
        >
          그레이엄 계산기
        </button>
        <button
          onClick={() => setActiveTab('webview')}
          className={`hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer ${
            activeTab === 'webview' ? 'text-emerald-400 font-semibold' : ''
          }`}
        >
          크로냐 원본 웹뷰
        </button>
        <button
          onClick={() => setActiveTab('guide')}
          className={`hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer ${
            activeTab === 'guide' ? 'text-emerald-400 font-semibold' : ''
          }`}
        >
          전략 가이드
        </button>
      </nav>

      {/* Zone 3: 1 primary action */}
      <div className="flex items-center gap-3 shrink-0">
        <PWAInstallButton />
      </div>
    </header>
  );
};
