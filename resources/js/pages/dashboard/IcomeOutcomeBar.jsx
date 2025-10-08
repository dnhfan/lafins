import React, { useRef, useEffect, useState, useMemo } from "react";
import { usePage } from '@inertiajs/react';
import { Bar } from 'react-chartjs-2';
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
                backgroundColor: ['#B5EAD7', ],
            },
            {
                label: ['OutCome'],  
                data: [out],
                backgroundColor: ['#FF6962', ],
            }
        ]
    }
}

const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Income - Outcome' },
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
    const [size, setSize] = useState(320)

    useEffect(() => {
        function updateSize() {
            const parent = containerRef.current
            if (!parent) return
            const parentWidth = parent.clientWidth
            const computed = Math.round(Math.max(220, Math.min(720, parentWidth * 0.9)))
            setSize(computed)
        }
        updateSize()
        const ro = new ResizeObserver(updateSize)
        if (containerRef.current) ro.observe(containerRef.current)
        window.addEventListener('resize', updateSize)
        return () => {
            ro.disconnect()
            window.removeEventListener('resize', updateSize)
        }
    }, [])

    const responsiveOptions = useMemo(() => {
        const opts = { ...baseOptions }
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
                <h3 className="text-m font-medium text-slate-700 mb-2">Income / Outcome</h3>
            </div>

            <div className="flex flex-col md:flex-row items-center justify-center gap-4">
                <div style={{ width: size, height: Math.round(size * 0.6) }} className="flex items-center justify-center">
                    <Bar data={chartData} options={responsiveOptions} />
                </div>
            </div>
        </div>
    )
}
