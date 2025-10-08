/**
 * createResponsiveOptions
 * - baseOptions: chart.js options object
 * - containerRef: ref to measure width
 * - breakpoint: width to switch legend position
 *
 * Comments (vi):
 * Hàm này tách phần logic chuyển vị trí legend dựa trên chiều rộng wrapper.
 */
export function createResponsiveOptions(baseOptions, containerRef, breakpoint = 640) {
  const opts = { ...baseOptions }
  try {
    const w = containerRef?.current?.clientWidth ?? 800
    if (w < breakpoint) {
      opts.plugins = {
        ...(opts.plugins || {}),
        legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8 } }
      }
    }
  } catch {
    // ignore measurement errors (no need to bind error variable)
  }
  return opts
}
