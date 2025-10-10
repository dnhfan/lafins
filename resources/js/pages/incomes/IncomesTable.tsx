import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';

// Kiểu paginator đơn giản
type Paginator<T> = { data: T[]; meta?: Record<string, any> };

// Type guard: kiểm tra xem value có phải paginator không
function isPaginator<T>(v: unknown): v is Paginator<T> {
  return typeof v === 'object' && v !== null && Array.isArray((v as any).data);
}

// Giao diện props rõ ràng
interface IncomesPageProps {
  incomes?: any[] | Paginator<any>;
  loading?: boolean;
  error?: string | null;
  // ... các props khác nếu có ...
}

export default function IncomesTable() {

  // 1. Đọc props từ server do Inertia cung cấp
  const { props } = usePage();

  // 2. DEBUG: Ghi log dữ liệu controller gửi tới console trình duyệt khi điều hướng tới trang Incomes
  useEffect(() => {
      // Thu hẹp log xuống các khóa liên quan để dễ đọc, nhưng vẫn in full props
      if (typeof console.groupCollapsed === 'function') {
          console.groupCollapsed('Incomes props');
      }
      console.log('full props:', props);
      
      if (typeof console.groupEnd === 'function') {
          console.groupEnd();
      }
  }, [props]);

  // 3. Lấy data từ props
  // Dữ liệu từ backend thường gắn `incomes` vào props của trang; viết code phòng ngừa
  const raw: unknown = (props as any).incomes;

  // Dùng type guard để chuẩn hóa
  const incomes = isPaginator(raw)
    ? raw.data
    : Array.isArray(raw)
      ? raw
      : [];

  // Lấy trạng thái loading/error từ props nếu có, nếu không thì dùng giá trị mặc định
  const loading = (props as any).loading ?? false;
  const error = (props as any).error ?? null;

  const [editing, setEditing] = useState<any | null>(null);

  // Danh sách đã lọc đơn giản hiện tại (sẽ kết nối tìm kiếm/lọc sau)
  const filtered = Array.isArray(incomes) ? incomes : [];

  // format
  function formatCurrency(value: number | string) {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      maximumFractionDigits: 0,
    }).format(Number(value) || 0);
  }

  function handleDelete(id: number | string) {
    if (!confirm('Bạn có chắc muốn xoá mục này?')) return;
    // Thực hiện xoá bằng Inertia; backend xử lý route tương ứng
    Inertia.delete(`/incomes/${id}`, { preserveState: false });
  }

  return (
    <>
      {/* Bảng thu nhập */}
      {loading ? (
        <p className="text-center py-6">Loading...</p>
      ) : error ? (
        <p className="text-center text-red-500 py-6">Lỗi: {String(error)}</p>
      ) : (
        <div className="overflow-x-auto">
          <div className="bg-white dark:bg-slate-800 border rounded-lg shadow-sm overflow-hidden">
            <table className="min-w-full text-sm">
              <thead className="bg-gray-50 dark:bg-slate-700">
                <tr className="text-left text-xs uppercase text-slate-500">
                  <th className="p-3 sticky top-0">Date</th>
                  <th className="p-3 sticky top-0">Source</th>
                  <th className="p-3 sticky top-0">Description</th>
                  <th className="p-3 sticky top-0 text-right">Amount</th>
                  <th className="p-3 sticky top-0 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((i: any, idx: number) => {
                  const formatted = i.formatted_amount ?? formatCurrency(i.amount);
                  return (
                    <tr
                      key={i.id}
                      className={`border-t ${idx % 2 === 0 ? 'bg-white dark:bg-slate-800' : 'bg-gray-50 dark:bg-slate-700'} hover:bg-gray-100 dark:hover:bg-slate-600`}
                    >
                      <td className="p-3 align-top">{i.date}</td>
                      <td className="p-3 align-top">{i.source}</td>
                      <td className="p-3 align-top truncate max-w-[28rem]">{i.description}</td>
                      <td className="p-3 align-top text-right font-mono">{formatted}</td>
                      <td className="p-3 align-top flex gap-2 justify-center">
                        <button
                          onClick={() => setEditing(i)}
                          title="Edit"
                          className="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-blue-50 text-blue-700 hover:bg-blue-100"
                        >
                          {/* pencil icon */}
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3">
                            <path d="M17.414 2.586a2 2 0 010 2.828l-9.193 9.193a1 1 0 01-.464.263l-4 1a1 1 0 01-1.213-1.213l1-4a1 1 0 01.263-.464L14.586 2.586a2 2 0 012.828 0z" />
                          </svg>
                          <span className="hidden sm:inline">Edit</span>
                        </button>
                        <button
                          onClick={() => handleDelete(i.id)}
                          title="Delete"
                          className="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-red-50 text-red-700 hover:bg-red-100"
                        >
                          {/* trash icon */}
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3">
                            <path fillRule="evenodd" d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zm2 6a1 1 0 10-2 0v6a1 1 0 001 1h6a1 1 0 001-1V8a1 1 0 10-2 0v6H8V8z" clipRule="evenodd" />
                          </svg>
                          <span className="hidden sm:inline">Delete</span>
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
            {Array.isArray(filtered) && filtered.length === 0 && (
              <div className="p-6 text-center text-sm text-slate-500">Không có mục thu nhập nào.</div>
            )}
          </div>
        </div>
      )}
    </>
  );
}