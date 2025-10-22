import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';
import IncomeModal from '../../components/IncomeModal';
import IncomeController from '@/actions/App/Http/Controllers/IncomeController';
import BaseResourceTable, { Column } from '@/components/BaseResourceTable';


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

  const columns: Column<Income>[] = [
    { key: 'date', header: 'Date' },
    { key: 'source', header: 'Category' },
    { key: 'description', header: 'Description', className: 'truncate max-w-[28rem]' },
    {
      key: 'amount',
      header: 'Amount',
      align: 'right',
      render: (item) => item.formatted_amount ?? formatCurrency(item.amount ?? 0),
    },
  ];

  return (
    <>
      {loading ? (
        <p className="text-center py-6">Loading...</p>
      ) : error ? (
        <p className="text-center text-red-500 py-6">Lỗi: {String(error)}</p>
      ) : (
        <BaseResourceTable<Income>
          data={filtered}
          columns={columns}
          onEdit={(item) => setEditing(item)}
          onDelete={(id) => handleDelete(id)}
          emptyMessage={'You dont have any incomes :) .'}
          paginator={isPag ? (raw as PaginatorShape) : undefined}
        />
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