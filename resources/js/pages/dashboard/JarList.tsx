import { usePage } from "@inertiajs/react";
import { useRef } from 'react'
import useResponsiveChartSize from '@/hooks/useResponsiveChartSize'
import type { Jars, Jar } from '@/types';

const iconByKey: Record<string, string> = {
  NEC: 'fa-solid fa-cart-shopping', 
  LTSS: 'fa-solid fa-piggy-bank',
  EDU: 'fa-solid fa-graduation-cap', 
  PLAY: 'fa-solid fa-gamepad', 
  FFA: 'fa-solid fa-chart-line', 
  GIVE: 'fa-solid fa-hand-holding-heart', 
}

// JarBox: hiển thị 1 hộp jar gồm icon, tên và số dư
function JarBox({ name = 'Unknown Jar', balance = 0, icon, className = '' }: { name?: string; balance?: number; icon?: React.ReactNode; className?: string }) {
    // Format số tiền theo chuẩn Việt Nam
    const formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(Number(balance) || 0)


    return (
    <div className={`${className} h-full p-7 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm dark:shadow-none flex items-center gap-3 bg-white dark:bg-[#0a0a0a]`}> 
            <div className="flex-shrink-0 w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                {icon}
            </div>

            <div className="flex-1 text-left">
                <div className="text-sm font-medium text-slate-700 dark:text-white whitespace-normal break-words">{name}</div>
                <div className="text-sm text-slate-500 dark:text-slate-400 mt-1">{formatted}</div>
            </div>
        </div>
    )
}

export default function JarList({ className }: {className:string}) {
    const wrapperRef = useRef<HTMLDivElement | null>(null)
    // measuredWidth được dùng để quyết định layout responsive đặc biệt
    const { measuredWidth } = useResponsiveChartSize(wrapperRef, { min: 220, max: 1600, scale: 1 })

    // logic responsive:
    // - medium (>=640) and <1000: show 3 columns x 2 rows (3 on top, 3 below)
    // - >=1000: original 6 columns
    const isThreeByTwo = typeof measuredWidth === 'number'  && measuredWidth < 1120

    const containerClass = `${className ?? ''} grid ${isThreeByTwo ? 'grid-cols-3 grid-rows-2' : 'grid-cols-1 sm:grid-cols-3 md:grid-cols-6'} gap-4 items-stretch`

    const {props} = usePage<Jars>();
    const jars = props?.jars ?? [];

    return (
        <div ref={wrapperRef} className={containerClass}>
            {Array.isArray(jars) && jars.map((j: Jar) => {
                const iconClass = iconByKey[j.key as string] ?? 'fa-solid fa-circle-dot'
                // Using <i> for FontAwesome class names present in project
                const icon = <i className={`${iconClass} text-slate-700 dark:text-white`} aria-hidden />
                return (
                    <JarBox key={j.id} name={j.label ?? j.key} balance={j.balance} icon={icon} />
                )
            })}
        </div>
    )
}