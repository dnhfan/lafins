
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
      className={`border-t ${idx % 2 === 0 ? 'bg-white dark:bg-slate-800' : 'bg-gray-50 dark:bg-slate-700'} hover:bg-gray-100 dark:hover:bg-slate-600`}
    >
      <td className="p-3 align-top">{item.date}</td>
      <td className="p-3 align-top">{item.source}</td>
      <td className="p-3 align-top truncate max-w-[28rem]">{item.description}</td>
      <td className="p-3 align-top text-right font-mono">{formatted}</td>
      <td className="p-3 align-top flex gap-2 justify-center">
        <button
          onClick={() => onEdit(item)}
          title="Edit"
          className="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-blue-50 text-blue-700 hover:bg-blue-100"
        >
          <i className="fa-solid fa-pencil"></i>
          <span className="hidden sm:inline">Edit</span>
        </button>
        <button
          onClick={() => onDelete(item.id)}
          title="Delete"
          className="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-red-50 text-red-700 hover:bg-red-100"
        >
          <i className="fa-solid fa-trash"></i>
          <span className="hidden sm:inline">Delete</span>
        </button>
      </td>
    </tr>
  );
}
