import React, { useRef, useEffect, useMemo } from 'react';
import useResponsiveChartSize from '@/hooks/useResponsiveChartSize';
import JarCard from './jar-card';
import type { Jars, Jar } from '@/types';
import { usePage } from '@inertiajs/react';

export default function JarList({ className = '', onPercentChange }: { className?: string; onPercentChange?: (id: number | string, percent: number) => void }) {
  const wrapperRef = useRef<HTMLDivElement | null>(null);
  const { measuredWidth } = useResponsiveChartSize(wrapperRef, { min: 220, max: 1600, scale: 1 });

  const isThreeByTwo = typeof measuredWidth === 'number' && measuredWidth < 1120;
  const containerClass = `${className ?? ''} grid ${isThreeByTwo ? 'grid-cols-3 grid-rows-2' : 'grid-cols-1 sm:grid-cols-3 md:grid-cols-6'} gap-4 items-stretch`;

  const { props } = usePage<Jars>();
  const jars = useMemo(() => props?.jars ?? [], [props?.jars]);

  // Debug: log jars payload received from server
  useEffect(() => {
     
    console.log('JarList: received props.jars from server ->', jars);
     
    console.log('Full page props ->', props);
  }, [jars, props]);

  // Ensure we render exactly the 6 canonical jars in a consistent order.
  const keysOrder = ['NEC', 'LTSS', 'EDU', 'PLAY', 'FFA', 'GIVE'];
  const jarsByKey: Record<string, Jar | undefined> = {};
  jars.forEach((j) => {
    if (j && j.key) jarsByKey[j.key] = j;
  });

  const arranged: Jar[] = keysOrder.map((k) =>
    jarsByKey[k] ?? ({ id: k as unknown as number, label: k, key: k, percentage: 0, balance: 0 } as unknown as Jar)
  );

  return (
    <div ref={wrapperRef} className={containerClass}>
      {arranged.map((j) => (
        <JarCard key={String(j.id)} jar={j} onPercentChange={onPercentChange} />
      ))}
    </div>
  );
}
