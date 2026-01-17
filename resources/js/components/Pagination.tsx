import { router, usePage } from '@inertiajs/react';
import PaginationButton from './PaginationButton';

// Component: Pagination
// Mục đích: component phân trang tái sử dụng cho paginator của Laravel (LengthAwarePaginator)

// Tham số (Props):
// - paginator: object do Laravel trả về (có thể có currentrpage, last_page, total, per_page, links, path...)
// - buildUrl?: tuỳ chọn, function nhận page number và trả URL (nếu muốn build URL thủ công)

// Hành vi chính :
// Bước 1 - Nhận dữ liệu paginator: component chấp nhận cả 2 dạng - nằm ở top-level
//           (paginator.current_page) hoặc nằm trong `meta` (paginator.meta.current_page).
// Bước 2 - Xây dải các nút trang quanh trang hiện tại (ví dụ ±2 trang) và hiển thị
//           các nút First/Prev/1..N/Next/Last.
// Bước 3 - Khi chuyển trang: component sẽ gọi router.get(basePath, { ...filters, page })
//           để giữ các bộ lọc hiện tại (controller cần gửi `filters` trong props).
// Bước 4 - Nếu truyền `buildUrl`, component sẽ dùng hàm đó để điều hướng thay vì basePath + data.

interface PaginatorShape {
    current_page?: number;
    last_page?: number;
    last?: number;
    total?: number;
    path?: string;
    meta?: { current_page?: number; last_page?: number };
}

interface Props {
    paginator: PaginatorShape | null | undefined;
    buildUrl?: (page: number) => string;
}

export default function Pagination({ paginator, buildUrl }: Props) {
    // 1. take props from server
    const { props } = usePage();

    if (!paginator) return null;

    const pg: PaginatorShape = paginator;

    // Support both shapes: paginator.current_page or paginator.meta.current_page
    const current = Number(
        paginator.current_page ?? paginator.meta?.current_page ?? 1,
    );
    const last = Number(
        paginator.last_page ?? paginator.meta?.last_page ?? paginator.last ?? 1,
    );

    // Build a small window of pages around current
    const range = 2; // pages on each side
    const from = Math.max(1, current - range);
    const to = Math.min(last, current + range);

    const pages: number[] = [];
    for (let p = from; p <= to; p++) pages.push(p);

    function goTo(page: number) {
        if (page < 1 || page > last || page === current) return;
        // Keep existing filters/query params (server exposes them under props.filters)
        const basePath = pg.path ?? window.location.pathname;
        const filters =
            props &&
            (props as { filters?: Record<string, string | number | undefined> })
                .filters
                ? (
                      props as {
                          filters?: Record<string, string | number | undefined>;
                      }
                  ).filters!
                : {};

        if (buildUrl) {
            const url = buildUrl(page);
            router.get(url, {}, { preserveState: true, preserveScroll: true });
            return;
        }

        const data = { ...filters, page };
        // Use router.get with path + data so query params are constructed correctly
        router.get(basePath, data, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    return (
        <nav
            className="flex items-center justify-between p-4"
            aria-label="Pagination"
        >
            <div className="flex items-center space-x-2">
                <PaginationButton
                    onClick={() => goTo(current - 1)}
                    disabled={current <= 1}
                >
                    <i className="fa-solid fa-left-long"></i>
                </PaginationButton>

                {from > 1 && (
                    <>
                        <PaginationButton
                            onClick={() => goTo(1)}
                            active={current === 1}
                        >
                            1
                        </PaginationButton>
                        {from > 2 && <span className="px-2">…</span>}
                    </>
                )}

                {pages.map((p) => (
                    <PaginationButton
                        key={p}
                        onClick={() => goTo(p)}
                        active={p === current}
                        aria-current={p === current ? 'page' : undefined}
                    >
                        {p}
                    </PaginationButton>
                ))}

                {to < last && (
                    <>
                        {to < last - 1 && <span className="px-2">…</span>}
                        <PaginationButton onClick={() => goTo(last)}>
                            {last}
                        </PaginationButton>
                    </>
                )}

                <PaginationButton
                    onClick={() => goTo(current + 1)}
                    disabled={current >= last}
                >
                    <i className="fa-solid fa-right-long"></i>
                </PaginationButton>
            </div>

            <div className="text-sm text-slate-500">
                Page {current} of {last} — {pg.total ?? 0} items
            </div>
        </nav>
    );
}
