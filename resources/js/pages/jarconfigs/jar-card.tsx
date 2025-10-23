import React, { useState } from 'react';
import type { Jar } from '@/types';

interface JarCardProps {
  jar: Jar;
  className?: string;
  onPercentChange?: (id: number | string, percent: number) => void;
}

export default function JarCard({ jar, className = '', onPercentChange }: JarCardProps) {
  const [percent, setPercent] = useState<number>(Number(jar.percentage ?? 0));
  const iconClassMap: Record<string, string> = {
    NEC: 'fa-solid fa-cart-shopping',
    LTSS: 'fa-solid fa-piggy-bank',
    EDU: 'fa-solid fa-graduation-cap',
    PLAY: 'fa-solid fa-gamepad',
    FFA: 'fa-solid fa-chart-line',
    GIVE: 'fa-solid fa-hand-holding-heart',
  };

  const iconClass = iconClassMap[jar.key as string] ?? 'fa-solid fa-circle-dot';

  function handleBlur() {
    let v = Math.round(Number(percent));
    if (isNaN(v)) v = 0;
    if (v < 0) v = 0;
    if (v > 100) v = 100;
    setPercent(v);
    onPercentChange?.(jar.id as number | string, v);
  }

  return (
    <div className={`${className} p-3 border rounded-lg bg-white dark:bg-[#0a0a0a] shadow-sm`}>
      <div className="flex items-center gap-3">
        <div className="flex-shrink-0 w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
          <i className={`${iconClass} text-slate-700 dark:text-white`} aria-hidden />
        </div>

        <div className="flex-1 text-left">
          <div className="text-sm font-medium text-slate-700 dark:text-white">{jar.name ?? jar.key}</div>
        </div>

        <div className="w-24 text-right">
          <div className="flex items-center justify-end gap-2">
            <input
              type="number"
              min={0}
              max={100}
              value={percent}
              onChange={(e) => setPercent(Number(e.target.value))}
              onBlur={handleBlur}
              className="w-14 p-1 text-right border rounded"
            />
            <span className="text-sm text-slate-700 dark:text-white">%</span>
          </div>
        </div>
      </div>
    </div>
  );
}
