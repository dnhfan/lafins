
import ActionButton from '@/components/ActionButton';

interface Income {
  id: number | string;
  date?: string;
  source?: string;
  description?: string;
  amount?: number | string;
  formatted_amount?: string;
}

interface Props {
  item: Income;
  idx: number;
  onEdit: (item: Income) => void;
  onDelete: (id: number | string) => void;
  formatCurrency: (v: number | string) => string;
}

export default function IncomeTableRow({ item, idx, onEdit, onDelete, formatCurrency }: Props) {
  const formatted = item.formatted_amount ?? formatCurrency(item.amount ?? 0);

  return (
    <tr
      key={item.id}
      className={`border-t ${idx % 2 === 0 ? 'bg-white dark:bg-black' : 'bg-gray-50 dark:bg-slate-800'} hover:bg-gray-100 dark:hover:bg-slate-700`}
    >
      <td className="p-3 align-top text-slate-900 dark:text-slate-200">{item.date}</td>
      <td className="p-3 align-top text-slate-900 dark:text-slate-200">{item.source}</td>
      <td className="p-3 align-top truncate max-w-[28rem] text-slate-900 dark:text-slate-200">{item.description}</td>
      <td className="p-3 align-top text-right font-mono text-slate-900 dark:text-slate-200">{formatted}</td>
      <td className="p-3 align-top flex gap-2 justify-center">
        <>
          <ActionButton variant="primary" title="Edit" onClick={() => onEdit(item)}>
            <i className="fa-solid fa-pencil " />
            <span className="hidden sm:inline">Edit</span>
          </ActionButton>
          <ActionButton variant="danger" title="Delete" onClick={() => onDelete(item.id)}>
            <i className="fa-solid fa-trash" />
            <span className="hidden sm:inline">Delete</span>
          </ActionButton>
        </>
      </td>
    </tr>
  );
}
