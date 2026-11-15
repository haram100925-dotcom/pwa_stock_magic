import React from 'react';
import { TrendingUp, Calculator, Globe, BookOpen } from 'lucide-react';

interface MobileBottomNavProps {
  activeTab: 'magic' | 'graham' | 'webview' | 'guide';
  setActiveTab: (tab: 'magic' | 'graham' | 'webview' | 'guide') => void;
}

export const MobileBottomNav: React.FC<MobileBottomNavProps> = ({
  activeTab,
  setActiveTab,
}) => {
  return (
    <nav className="fixed bottom-0 left-0 right-0 z-40 bg-[#0B0F17]/95 backdrop-blur-md border-t border-slate-800 md:hidden">
      <div className="grid grid-cols-4 items-center h-16 max-w-md mx-auto px-2">
        <button
          onClick={() => setActiveTab('magic')}
          className={`flex flex-col items-center justify-center min-h-[44px] min-w-[44px] py-1 cursor-pointer transition-colors ${
            activeTab === 'magic' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'
          }`}
        >
          <TrendingUp className="w-5 h-5" />
          <span className="text-[10px] mt-1 tracking-tight">신마법공식</span>
        </button>

        <button
          onClick={() => setActiveTab('graham')}
          className={`flex flex-col items-center justify-center min-h-[44px] min-w-[44px] py-1 cursor-pointer transition-colors ${
            activeTab === 'graham' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'
          }`}
        >
          <Calculator className="w-5 h-5" />
          <span className="text-[10px] mt-1 tracking-tight">그레이엄</span>
        </button>

        <button
          onClick={() => setActiveTab('webview')}
          className={`flex flex-col items-center justify-center min-h-[44px] min-w-[44px] py-1 cursor-pointer transition-colors ${
            activeTab === 'webview' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'
          }`}
        >
          <Globe className="w-5 h-5" />
          <span className="text-[10px] mt-1 tracking-tight">웹뷰포털</span>
        </button>

        <button
          onClick={() => setActiveTab('guide')}
          className={`flex flex-col items-center justify-center min-h-[44px] min-w-[44px] py-1 cursor-pointer transition-colors ${
            activeTab === 'guide' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'
          }`}
        >
          <BookOpen className="w-5 h-5" />
          <span className="text-[10px] mt-1 tracking-tight">전략가이드</span>
        </button>
      </div>
    </nav>
  );
};
