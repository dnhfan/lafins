import { useState, useEffect } from 'react';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/react';

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
  const { props } = usePage();
  const serverErrors = (props as any).errors ?? {};

  const [form, setForm] = useState({ date: '', source: '', description: '', amount: '' });
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (initialData) {
      setForm({
        date: initialData.date ?? '',
        source: initialData.source ?? '',
        description: initialData.description ?? '',
        amount: initialData.amount != null ? String(initialData.amount) : '',
      });
    } else if (type === 'add') {
      setForm({ date: '', source: '', description: '', amount: '' });
    }
  }, [initialData, type, isOpen]);

  function change(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    try {
      if (type === 'add') {
        Inertia.post('/incomes', {
          date: form.date,
          source: form.source,
          description: form.description,
          amount: Number(form.amount) || 0,
        }, {
          onSuccess: () => {
            setSubmitting(false);
            onClose();
            onSuccess?.();
          },
          onError: () => setSubmitting(false),
        });
      } else {
        // update
        const id = initialData?.id;
        if (!id) {
          setSubmitting(false);
          return;
        }
        Inertia.put(`/incomes/${id}`, {
          date: form.date,
          source: form.source,
          description: form.description,
          amount: Number(form.amount) || 0,
        }, {
          onSuccess: () => {
            setSubmitting(false);
            onClose();
            onSuccess?.();
          },
          onError: () => setSubmitting(false),
        });
      }
    } catch (err) {
      setSubmitting(false);
    }
  }

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="fixed inset-0 bg-black/40" onClick={onClose} />
      <div className="bg-white dark:bg-slate-800 rounded-lg shadow-lg w-full max-w-xl mx-4 z-10">
        <div className="p-4 border-b dark:border-slate-700 flex items-center justify-between">
          <h3 className="text-lg font-medium">{type === 'add' ? 'Add Income' : 'Edit Income'}</h3>
          <button onClick={onClose} className="text-slate-500 hover:text-slate-700">×</button>
        </div>
        <form onSubmit={handleSubmit} className="p-4 space-y-3">
          <div>
            <label className="block text-sm text-slate-600">Date</label>
            <input name="date" type="date" value={form.date} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {serverErrors.date && <div className="text-red-500 text-sm">{String(serverErrors.date)}</div>}
          </div>

          <div>
            <label className="block text-sm text-slate-600">Category / Source</label>
            <input name="source" value={form.source} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {serverErrors.source && <div className="text-red-500 text-sm">{String(serverErrors.source)}</div>}
          </div>

          <div>
            <label className="block text-sm text-slate-600">Description</label>
            <textarea name="description" value={form.description} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {serverErrors.description && <div className="text-red-500 text-sm">{String(serverErrors.description)}</div>}
          </div>

          <div>
            <label className="block text-sm text-slate-600">Amount</label>
            <input name="amount" type="number" value={form.amount} onChange={change} className="mt-1 w-full rounded border px-2 py-1" />
            {serverErrors.amount && <div className="text-red-500 text-sm">{String(serverErrors.amount)}</div>}
          </div>

          <div className="flex justify-end gap-2">
            <button type="button" onClick={onClose} className="px-3 py-1 rounded border">Cancel</button>
            <button type="submit" disabled={submitting} className="px-3 py-1 rounded bg-blue-600 text-white">{submitting ? 'Saving...' : 'Save'}</button>
          </div>
        </form>
      </div>
    </div>
  );
}
