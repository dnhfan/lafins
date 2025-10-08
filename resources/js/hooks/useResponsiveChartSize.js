import { useEffect, useState } from 'react'

/**
 * useResponsiveChartSize
 * - containerRef: ref to the wrapper element
 * - opts: { min, max, scale, debounceMs }
 * returns: { size }
 *
 * Comments (vi):
 * Hook này chuẩn hoá logic đo kích thước cho các biểu đồ.
 * - Có debounce để tránh cập nhật quá nhanh khi resize.
 * - Dùng ResizeObserver khi có, fallback sang window.resize khi không có.
 * - Bảo vệ SSR (typeof window === 'undefined').
 */
export default function useResponsiveChartSize(containerRef, opts = {}) {
  const { min = 220, max = 720, scale = 0.9, debounceMs = 80 } = opts
  const [size, setSize] = useState(320)
  const [measuredWidth, setMeasuredWidth] = useState(null)

  useEffect(() => {
    // SSR guard
    if (typeof window === 'undefined') return

    let mounted = true
    let t = null

    function compute() {
      const parent = containerRef?.current
      if (!parent) return
      const parentWidth = parent.clientWidth || 0
      // store the raw measured width so callers can react to it even when size is clamped
      setMeasuredWidth(parentWidth)
      const computed = Math.round(Math.max(min, Math.min(max, parentWidth * scale)))
      if (mounted) setSize(computed)
    }

    function onResizeDebounced() {
      if (t) clearTimeout(t)
      t = setTimeout(compute, debounceMs)
    }

    // initial compute
    compute()

    let ro
    try {
      if (typeof ResizeObserver !== 'undefined') {
        ro = new ResizeObserver(onResizeDebounced)
        if (containerRef.current) ro.observe(containerRef.current)
      } else {
        window.addEventListener('resize', onResizeDebounced)
      }
    } catch {
      // fallback if ResizeObserver construction fails (avoid unused-catch-param lint)
      window.addEventListener('resize', onResizeDebounced)
    }

    return () => {
      mounted = false
      if (t) clearTimeout(t)
      try {
        if (ro) ro.disconnect()
      } catch {
        // ignore errors during disconnect
      }
      window.removeEventListener('resize', onResizeDebounced)
    }
  }, [containerRef, min, max, scale, debounceMs])

  return { size, measuredWidth }
}
