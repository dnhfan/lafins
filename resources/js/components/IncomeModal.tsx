import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import IncomeController from '../actions/App/Http/Controllers/IncomeController';

type ModalType = 'add' | 'update';

interface IncomeModalProps {
  type: ModalType;
  isOpen: boolean;
  onClose: () => void;
  // initial data for update; for add it can be undefined
  initialData?: {
    id?: number | string;
    date?: string;
    source?: string;
    description?: string;
    amount?: number | string;
  } | null;
  onSuccess?: () => void;
}

export default function IncomeModal({ type, isOpen, onClose, initialData = null, onSuccess }: IncomeModalProps) {
  // Manage form using Inertia useForm (processing + errors built-in)
  // 1. useForm
  const { data, setData, post, put, processing, errors, reset } = useForm({
    date: '',
    source: '',
    description: '',
    amount: '',
  });

  // 2. handle update form
  // update form when modal is open
  useEffect(() => {
    // if have init data (type = update)
    if (initialData) {
      setData('date', initialData.date ?? '');
      setData('source', initialData.source ?? '');
      setData('description', initialData.description ?? '');
      setData('amount', initialData.amount != null ? String(initialData.amount) : '');
    }
    // if type = add -> empty form
    else if (type === 'add') {
      reset();
    }
  }, [initialData, type, isOpen, setData, reset]);
  
  // hande state when data in UI is changed
  function change(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) {
    const { name, value } = e.target;
    setData(name as keyof typeof data, value);
  }

  // handle submit
  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    // normalize amount to number string
    setData('amount', String(Number(data.amount) || 0));

    // type = add -> post 
    if (type === 'add') {
      post(IncomeController.store.url(), {
        preserveState: false,
        onSuccess: () => {
          onClose();
          onSuccess?.();
        },
      });
      // type = 'update' -> put
    } else {
      const id = initialData?.id;
      if (!id) return;
      put(`/incomes/${id}`, {
        preserveState: false,
        onSuccess: () => {
          onClose();
          onSuccess?.();
        },
      });
    }
  }

  // Keep the modal mounted to allow simple CSS transitions on open/close
  return (
    <div className={`fixed inset-0 z-50 flex items-center justify-center transition-opacity ${isOpen ? 'pointer-events-auto' : 'pointer-events-none'}`} aria-hidden={!isOpen}>
       {/* wrapper   */}
      <div
        className={`fixed inset-0 bg-black/40 transition-opacity duration-200 ${isOpen ? 'opacity-100' : 'opacity-0'}`}
        onClick={onClose}
      />

      {/* Main modal   */}
      <div className={`bg-white dark:bg-slate-800 rounded-lg shadow-lg w-full max-w-xl mx-4 z-10 transform transition-all duration-200 ${isOpen ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-2 scale-95'}`}>
        {/* header */}
        <div className="p-4 border-b dark:border-slate-700 flex items-center justify-between">
          <h3 className="text-lg font-medium">{type === 'add' ? 'Add Income' : 'Edit Income'}</h3>
          <button onClick={onClose} className="text-slate-500 hover:text-slate-700">×</button>
        </div>
        {/* form */}
        <form onSubmit={handleSubmit} className="p-4 space-y-3">
          <div>
            <label className="block text-sm text-slate-600">Date</label>
            <input name="date" type="date" value={data.date} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {errors.date && <div className="text-red-500 text-sm">{String(errors.date)}</div>}
          </div>

          <div>
            <label className="block text-sm text-slate-600">Category / Source</label>
            <input name="source" value={data.source} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {errors.source && <div className="text-red-500 text-sm">{String(errors.source)}</div>}
          </div>

          <div>
            <label className="block text-sm text-slate-600">Description</label>
            <textarea name="description" value={data.description} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {errors.description && <div className="text-red-500 text-sm">{String(errors.description)}</div>}
          </div>

          <div>
            <label className="block text-sm text-slate-600">Amount</label>
            <input name="amount" type="number" value={data.amount} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {errors.amount && <div className="text-red-500 text-sm">{String(errors.amount)}</div>}
          </div>

          <div className="flex justify-end gap-2">
            <button type="button" onClick={onClose} className="px-3 py-1 rounded border">Cancel</button>
            <button type="submit" disabled={processing} className="px-3 py-1 rounded bg-blue-600 text-white">{processing ? 'Saving...' : 'Save'}</button>
          </div>
        </form>

      </div>
    </div>
  );
}
