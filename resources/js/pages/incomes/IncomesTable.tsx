import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';
import IncomeTableRow from './IncomeTableRow';
import IncomeModal from '../../components/IncomeModal';
import Pagination from '../../components/Pagination';
import IncomeController from '@/actions/App/Http/Controllers/IncomeController';


// Kiểu paginator đơn giản
type Paginator<T> = { data: T[]; meta?: Record<string, unknown> };

// Type guard: kiểm tra xem value có phải paginator không
function isPaginator<T>(v: unknown): v is Paginator<T> {
  return typeof v === 'object' && v !== null && 'data' in (v as object) && Array.isArray((v as Paginator<unknown>).data);
}

// Local paginator shape for passing to Pagination component
type PaginatorShape = {
  current_page?: number;
  last_page?: number;
  last?: number;
  total?: number;
  path?: string;
  meta?: { current_page?: number; last_page?: number };
};

// interfaces props 
interface Income {
  id: number | string;
  date?: string;
  source?: string;
  description?: string;
  amount?: number | string;
  formatted_amount?: string;
}

interface IncomesPageProps {
  incomes?: Income[] | Paginator<Income>;
  loading?: boolean;
  error?: string | null;
  // ... các props khác nếu có ...
}

export default function IncomesTable() {

  // 1. Đọc props từ server do Inertia cung cấp
  const { props } = usePage<IncomesPageProps & Record<string, unknown>>();

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
  const raw: unknown = props?.incomes as unknown;

  // Dùng type guard để chuẩn hóa
  const isPag = isPaginator<Income>(raw);
  const incomes = isPag
    ? raw.data
    : Array.isArray(raw)
      ? (raw as Income[])
      : [] as Income[];

  // Lấy trạng thái loading/error từ props nếu có, nếu không thì dùng giá trị mặc định
  const loading = props?.loading ?? false;
  const error = props?.error ?? null;

  const [editing, setEditing] = useState<Income | null>(null);

  // Danh sách đã lọc đơn giản hiện tại (sẽ kết nối tìm kiếm/lọc sau)
  const filtered: Income[] = Array.isArray(incomes) ? incomes : [];

  // format
  function formatCurrency(value: number | string) {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      maximumFractionDigits: 0,
    }).format(Number(value) || 0);
  }

  function handleDelete(id: number | string) {
    // Xoá bằng Inertia với type-safe route từ IncomeController
    const route = IncomeController.destroy(Number(id));
    Inertia.delete(route.url, { preserveState: false });
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
                  <th className="p-3 sticky top-0">Category</th>
                  <th className="p-3 sticky top-0">Description</th>
                  <th className="p-3 sticky top-0 text-right">Amount</th>
                  <th className="p-3 sticky top-0 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((i: Income, idx: number) => (
                  <IncomeTableRow
                    key={i.id}
                    item={i}
                    idx={idx}
                    onEdit={(item) => setEditing(item)}
                    onDelete={(id) => handleDelete(id)}
                    formatCurrency={formatCurrency}
                  />
                ))}
              </tbody>
            </table>
            {Array.isArray(filtered) && filtered.length === 0 && (
              <div className="p-6 text-center text-sm text-slate-500">You dont have any incomes :) .</div>
            )}
              {/* Pagination controls (server-driven) */}
              {isPag && (
                <Pagination paginator={raw as PaginatorShape} />
              )}
          </div>
        </div>
      )}
      {/* Update modal: open when editing is set */}
      <IncomeModal
        type="update"
        isOpen={Boolean(editing)}
        onClose={() => setEditing(null)}
        initialData={editing}
        onSuccess={() => { setEditing(null);}}
      />
    </>
  );
}