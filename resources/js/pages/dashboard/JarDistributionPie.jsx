import { usePage } from "@inertiajs/react"
import { useRef, useState, useEffect, useMemo } from 'react'
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
    const backgroundColor = ['#FF9AA2', '#FFB7B2', '#FFDAC1', '#E2F0CB', '#B5EAD7', '#C7CEEA']

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
            ctx.fillStyle = '#0f172a'
            ctx.font = `600 ${mainFontPx}px ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial`
            ctx.textAlign = 'center'
            ctx.textBaseline = 'middle'

            const formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(total)
            // draw main amount a little above center so the label can sit below it; offsets are scaled too
            ctx.fillText(formatted, centerX, centerY - Math.round(subFontPx / 1.5))

            ctx.fillStyle = '#6b7280'
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
            legend: { position: 'right', labels: { boxWidth: 10, padding: 16 } },
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
    // responsive sizing: measure parent width and clamp size so the chart remains readable
    const containerRef = useRef(null) //useRef create a pointer point to a DOM 
    const [size, setSize] = useState(320)

    useEffect(() => {
        // cacu size
        function updateSize() {
            const parent = containerRef.current
            if (!parent) return
            const parentWidth = parent.clientWidth
            // choose a chart size based on available width but clamp between 220 and 420
            const computed = Math.round(Math.max(220, Math.min(420, parentWidth * 0.9)))
            setSize(computed)
        }
        // change by size
        updateSize()
        const ro = new ResizeObserver(updateSize)
        if (containerRef.current) ro.observe(containerRef.current)
        window.addEventListener('resize', updateSize)
        return () => {
            ro.disconnect()
            window.removeEventListener('resize', updateSize)
        }
    }, [])

    // allow legend to move to bottom when container is narrow
    const responsiveOptions = useMemo(() => {
        const opts = { ...options }
        try {
            const parent = containerRef.current
            const w = parent?.clientWidth ?? 800
            if (w < 640) {
                opts.plugins = { ...(opts.plugins || {}), legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8 } } }
            }
        } catch (e) {
            // ignore
        }
        return opts
    }, [size])

    return (
        <div ref={containerRef} className="w-full p-4 bg-white rounded-lg shadow-sm">
            
            <div className="flex-1 text-center ">
                <h3 className="text-m font-medium text-slate-700 mb-2">Jar Distribution</h3>
            </div>

            <div className="flex flex-col md:flex-row items-center justify-center gap-4">
                <div style={{ width: size, height: size }} className="flex items-center justify-center">
                    <Doughnut data={chartData} options={responsiveOptions} plugins={[centerTextPlugin]} />
                </div>
            </div>
        </div>
    )
}
