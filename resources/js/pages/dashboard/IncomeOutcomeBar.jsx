import React, { useRef, useEffect, useState, useMemo } from "react";
import { usePage } from '@inertiajs/react';
import { Bar } from 'react-chartjs-2';
import useResponsiveChartSize from '@/hooks/useResponsiveChartSize'
import { createResponsiveOptions } from '@/lib/chartOptions'
import { INCOME_COLOR, OUTCOME_COLOR } from '@/lib/chartColors'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

/**
 * Tạo dữ liệu biểu đồ cho cột Thu nhập/Chi tiêu.
 */
const buildChartData = (props) => {
    const summary = props?.summary ?? props ?? {}
    const inc = Number(summary.total_income) || 0
    const out = Number(summary.total_outcome) || 0

    return {
        labels: ['Money'],
        datasets: [
            {
                label: ['Income'],  
                data: [inc],
                backgroundColor: [INCOME_COLOR, ],
            },
            {
                label: ['OutCome'],  
                data: [out],
                backgroundColor: [OUTCOME_COLOR, ],
            }
        ]
    }
}

const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
    legend: { position: 'top' },
    title: { display: true, text: 'Compare Income - OutCome with bar Chart' },
        tooltip: {
            callbacks: {
                label: (context) => {
                    const value = context.parsed?.y ?? context.parsed ?? 0
                    return `${context.dataset.label ?? ''}: ${Number(value).toLocaleString('vi-VN')}`
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => Number(value).toLocaleString('vi-VN')
            }
        }
    }
}

// plugin to paint canvas background black when dark mode is active
const canvasBackgroundPlugin = {
    id: 'canvasBackground',
    beforeDraw: (chart) => {
        if (typeof document === 'undefined') return
        const isDark = document.documentElement.classList.contains('dark')
        if (!isDark) return
        const ctx = chart.canvas.getContext('2d')
        ctx.save()
        ctx.globalCompositeOperation = 'destination-over'
        ctx.fillStyle = '#000000'
        ctx.fillRect(0, 0, chart.width, chart.height)
        ctx.restore()
    }
}

export default function IcomeOutcomeBar() {
    // 1. take props in page
    const { props } = usePage()

    // 2. Build data from Inertia props (or fallbacks)
    const chartData = useMemo(() => buildChartData(props), [props])

    // Defensive: ensure datasets exist and data arrays are arrays
    const okDatasets = chartData && Array.isArray(chartData.datasets) && chartData.datasets.every((d) => Array.isArray(d.data))
    if (!okDatasets) {
        // eslint-disable-next-line no-console
        console.warn('IcomeOutcomeBar: chartData.datasets is invalid', chartData)
        return <div className="text-sm text-slate-500">Không có dữ liệu hợp lệ để hiển thị biểu đồ</div>
    }

    // 3. sizing / responsive legend
    const containerRef = useRef(null)
    // use shared hook to compute size (debounced, SSR safe)
    const { size, measuredWidth } = useResponsiveChartSize(containerRef, { min: 220, max: 720, scale: 0.9 })
    // theme-aware options: adjust legend, title and tooltip colors for dark mode
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
    const themeBaseOptions = useMemo(() => {
        const copy = JSON.parse(JSON.stringify(baseOptions))
        copy.plugins = copy.plugins || {}
        copy.plugins.legend = copy.plugins.legend || {}
        copy.plugins.legend.labels = copy.plugins.legend.labels || {}
        copy.plugins.legend.labels.color = isDark ? '#ffffff' : '#0f172a'
        copy.plugins.title = copy.plugins.title || {}
        copy.plugins.title.color = isDark ? '#ffffff' : '#0f172a'
        copy.plugins.tooltip = copy.plugins.tooltip || {}
        copy.plugins.tooltip.titleColor =  '#ffffff' 
        copy.plugins.tooltip.bodyColor =  '#ffffff' 
        // y-axis tick color
        copy.scales = copy.scales || {}
        copy.scales.y = copy.scales.y || {}
        copy.scales.y.ticks = copy.scales.y.ticks || {}
        copy.scales.y.ticks.color = isDark ? '#ffffff' : '#0f172a'
        return copy
    }, [isDark])

    const responsiveOptions = useMemo(() => createResponsiveOptions(themeBaseOptions, containerRef, 640), [size, measuredWidth, themeBaseOptions])

    return (
    <div ref={containerRef} className="w-full p-4 rounded-lg shadow-sm dark:shadow-none" style={{ backgroundColor: 'oklch(0.145 0 0)' }}>

            <div className="flex-1 text-center ">
                <h3 className="text-m font-medium text-slate-700 dark:text-white mb-2">Income / Outcome</h3>
            </div>

            <div className="flex flex-col md:flex-row items-center justify-center gap-4">
                <div style={{ width: size, height: Math.round(size * 0.6) }} className="flex items-center justify-center">
                    <Bar data={chartData} options={responsiveOptions} plugins={[canvasBackgroundPlugin]} />
                </div>
            </div>
        </div>
    )
}
