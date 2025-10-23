import { usePage } from "@inertiajs/react";
import type { Jars, Jar } from '@/types';

const iconByKey: Record<string, string> = {
  NEC: 'fa-solid fa-cart-shopping', 
  LTSS: 'fa-solid fa-piggy-bank',
  EDU: 'fa-solid fa-graduation-cap', 
  PLAY: 'fa-solid fa-gamepad', 
  FFA: 'fa-solid fa-chart-line', 
  GIVE: 'fa-solid fa-hand-holding-heart', 
}

// JarBox: hiển thị 1 hộp jar gồm icon, tên, phần trăm và số dư
function JarBox({ name = 'Unknown Jar', balance = 0, percentage, icon, className = '' }: { name?: string; balance?: number; percentage?: number; icon?: React.ReactNode; className?: string }) {
    // Format số tiền theo chuẩn Việt Nam
    const formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(Number(balance) || 0)


    return (
    <div className={`${className} h-full p-7 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm dark:shadow-none flex items-center gap-3 bg-white dark:bg-[#0a0a0a]`}> 
            <div className="flex-shrink-0 w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                {icon}
            </div>

                        <div className="flex-1 text-left">
                                <div className="flex items-baseline gap-3">
                                    <div className="text-sm font-medium text-slate-700 dark:text-white whitespace-normal break-words">{name}</div>
                                    {typeof percentage === 'number' && (
                                        <div className="text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{Number(percentage).toFixed(2)}%</div>
                                    )}
                                </div>
                                <div className="text-sm text-slate-500 dark:text-slate-400 mt-1">{formatted}</div>
                        </div>
        </div>
    )
}

export default function JarList({ className }: {className:string}) {
    // Use a consistent layout:
    // - mobile: single column
    // - sm: 3 columns
    // - md and up: 3 columns x 2 rows (always show two rows of three)
    const containerClass = `${className ?? ''} grid grid-cols-1 sm:grid-cols-3 md:grid-cols-3 md:grid-rows-2 gap-4 items-stretch`;

    const {props} = usePage<Jars>();
    const jars = props?.jars ?? [];

    return (
        <div className={containerClass}>
            {Array.isArray(jars) && jars.map((j: Jar) => {
                const iconClass = iconByKey[j.key as string] ?? 'fa-solid fa-circle-dot'
                // Using <i> for FontAwesome class names present in project
                const icon = <i className={`${iconClass} text-slate-700 dark:text-white`} aria-hidden />
                return (
                    <JarBox key={j.id} name={j.label ?? j.key} balance={j.balance} percentage={j.percentage} icon={icon} />
                )
            })}
        </div>
    )
}