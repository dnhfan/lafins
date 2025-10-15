import { usePage } from "@inertiajs/react"
import { useRef, useMemo } from 'react'
import useResponsiveChartSize from '@/hooks/useResponsiveChartSize'
import { createResponsiveOptions } from '@/lib/chartOptions'
import { DEFAULT_PALETTE } from '@/lib/chartColors'
import { Doughnut } from 'react-chartjs-2';
import {
    Chart as ChartJs,
    ArcElement,
    Tooltip,
    Legend,
    Title,
    SubTitle
} from 'chart.js'

ChartJs.register(ArcElement, Tooltip, Legend, Title, SubTitle);

/**
 * Xây dựng dữ liệu biểu đồ từ props của Inertia :
 */
const buildChartData = (props) => {
    // 1. Take raw in4
    const raw =
        // take jars in4 in props
        props?.jars ??
        []

    // 2. convert into array for using
    const items = Array.isArray(raw)
        ? raw
        : typeof raw === 'object' && raw !== null
        ? Object.keys(raw).map((k) => ({ label: k, value: raw[k] })) // take jars in4 and convert into array<obj>
        : []

    // 3. Take labels and value from array
    const labels = items.map((it) => it.label ?? it.name ?? it.key ?? '')
    // Ưu tiên dùng numeric 'balance', nếu không có dùng 'percentage' hoặc 'value'
    const values = items.map((it) => Number(it.balance ?? it.value ?? it.percentage ?? 0) || 0)

    // color
    const backgroundColor = DEFAULT_PALETTE

    // return -> data of chart
    return {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: labels.map((_, i) => backgroundColor[i % backgroundColor.length]),
                    borderColor: '#fff',
                    borderWidth: 2,
                },
            ],
        }
}

    // Plugin vẽ chữ ở giữa Doughnut (tổng)
    // Font sizes scale based on chart area size so the text shrinks/grows with the chart
    
    // Note: every Caculating in this part is caculated by AI :) so dont ask me bout this (actually the whole plugin :V)
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: (chart) => {
            const { ctx, chartArea } = chart
            if (!chartArea) return
            const { width, height, top, left } = chartArea
            const centerX = left + width / 2
            const centerY = top + height / 2

            const datasets = chart.data?.datasets ?? []
            const total = datasets[0]?.data?.reduce((s, v) => s + (Number(v) || 0), 0) ?? 0

            // base on the smaller dimension
            const base = Math.min(width, height)
            // scale the plugin relative to its normal size: 1 = original, 0.5 = half
            const scale = 0.5
            // choose sizes proportionally and apply scale; tweak multipliers to taste
            const mainFontPx = Math.max(8, Math.round(base * 0.11 * scale))
            const subFontPx = Math.max(6, Math.round(base * 0.07 * scale))

                    ctx.save()
                    // choose colors based on dark mode presence on documentElement
                    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
                    const mainColor = isDark ? '#ffffff' : '#0f172a'
                    const subColor = isDark ? '#9ca3af' : '#6b7280'

                    ctx.fillStyle = mainColor
                    ctx.font = `600 ${mainFontPx}px ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial`
                    ctx.textAlign = 'center'
                    ctx.textBaseline = 'middle'

                    const formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(total)
                    // draw main amount a little above center so the label can sit below it; offsets are scaled too
                    ctx.fillText(formatted, centerX, centerY - Math.round(subFontPx / 1.5))

                    ctx.fillStyle = subColor
                    ctx.font = `400 ${subFontPx}px ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial`
                    ctx.fillText('Tổng', centerX, centerY + Math.round(mainFontPx / 2.5))
                    ctx.restore()
        }
    }

    // Options for chart
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            // default to right; component will toggle to bottom on small screens via JS if needed
            legend: { position: 'right', labels: { boxWidth: 10, padding: 16, color: undefined } },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        const value = context.parsed ?? 0
                        const datasets = context.chart.data.datasets || []
                        const total = datasets[0]?.data?.reduce((s, v) => s + (Number(v) || 0), 0) || 0
                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0'
                        return `${context.label}: ${value.toLocaleString('vi-VN')} (${pct}%)`
                    }
                }
            }
        },
        elements: { arc: { borderRadius: 8, hoverOffset: 8 } }
    }

// Plugin to paint canvas background so the chart area appears black in dark mode
const canvasBackgroundPlugin = {
    id: 'canvasBackground',
    beforeDraw: (chart) => {
        if (typeof chart?.canvas === 'undefined') return
        const ctx = chart.canvas.getContext('2d')
        if (!ctx) return
        ctx.save()
        // draw behind existing content
        ctx.globalCompositeOperation = 'destination-over'
        // use requested color for canvas background
        ctx.fillStyle = 'oklch(0.145 0 0)'
        ctx.fillRect(0, 0, chart.width, chart.height)
        ctx.restore()
    }
}

// Main Component
export default function JarDistributionPie() {
    // 1. Take props from page
    const { props } = usePage();

    // 2. build data form props
    const chartData = buildChartData(props)
    
    // Kiểm tra defensive: đảm bảo datasets tồn tại và mỗi dataset có mảng data
    const okDatasets =
        chartData &&
        Array.isArray(chartData.datasets) &&
        chartData.datasets.every((d) => Array.isArray(d.data))

    if (!okDatasets) {
        // eslint-disable-next-line no-console
        console.warn('JarDistributionPie: chartData.datasets is invalid', chartData)
        return <div className="text-sm text-slate-500">Không có dữ liệu hợp lệ để hiển thị biểu đồ</div>
    }

    // 3. caculate size for chart
    // responsive sizing: use shared hook to compute size and debounce
    const containerRef = useRef(null)
    const { size, measuredWidth } = useResponsiveChartSize(containerRef, { min: 220, max: 420, scale: 0.9 })

    // allow legend to move to bottom when container is narrow (centralized helper)
    // we depend on measuredWidth so that when the wrapper grows back the options recompute
    // detect dark mode
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')

    // create theme-aware options (clone base options and set text colors)
    const themeOptions = useMemo(() => {
        const copy = JSON.parse(JSON.stringify(options))
        copy.plugins = copy.plugins || {}
        copy.plugins.legend = copy.plugins.legend || {}
        copy.plugins.legend.labels = copy.plugins.legend.labels || {}
        copy.plugins.legend.labels.color = isDark ? '#ffffff' : '#0f172a'
        copy.plugins.tooltip = copy.plugins.tooltip || {}
        copy.plugins.tooltip.titleColor = '#ffffff'
        copy.plugins.tooltip.bodyColor =  '#ffffff' 
        return copy
    }, [isDark])

    const responsiveOptions = useMemo(() => createResponsiveOptions(themeOptions, containerRef, 640), [size, measuredWidth, themeOptions])

    return (
    <div ref={containerRef} className="w-full p-4 rounded-lg shadow-sm dark:shadow-none" style={{ backgroundColor: 'oklch(0.145 0 0)' }}>
            
            <div className="flex-1 text-center ">
                <h3 className="text-m font-medium text-slate-700 dark:text-white mb-2">Jar Distribution</h3>
            </div>

            <div className="flex flex-col md:flex-row items-center justify-center gap-4">
                <div style={{ width: size, height: size }} className="flex items-center justify-center">
                    <Doughnut data={chartData} options={responsiveOptions} plugins={[centerTextPlugin, canvasBackgroundPlugin]} />
                </div>
            </div>
        </div>
    )
}
