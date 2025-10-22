import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import IncomeController from '../actions/App/Http/Controllers/IncomeController';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from './ui/dialog';
import { Button } from './ui/button';

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
        preserveScroll: true,
        onSuccess: () => {
          onClose();
          onSuccess?.();
        },
      });
      // type = 'update' -> put
    } else {
      const id = initialData?.id;
      if (!id) return;
      put(IncomeController.update.url(Number(id)), {
        preserveScroll: true,
        onSuccess: () => {
          onClose();
          onSuccess?.();
        },
      });
    }
  }

  // Keep the modal mounted to allow simple CSS transitions on open/close
  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>{type === 'add' ? 'Add Income' : 'Edit Income'}</DialogTitle>
        </DialogHeader>
        
        {/* form */}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">Date</label>
            <input name="date" type="date" value={data.date} onChange={change} className="w-full rounded-md border px-3 py-2" />
            {errors.date && <div className="text-red-500 text-sm mt-1">{String(errors.date)}</div>}
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Category / Source</label>
            <input name="source" value={data.source} onChange={change} className="w-full rounded-md border px-3 py-2" />
            {errors.source && <div className="text-red-500 text-sm mt-1">{String(errors.source)}</div>}
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" value={data.description} onChange={change} className="w-full rounded-md border px-3 py-2 min-h-[80px]" />
            {errors.description && <div className="text-red-500 text-sm mt-1">{String(errors.description)}</div>}
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Amount</label>
            <input name="amount" type="number" value={data.amount} onChange={change} className="w-full rounded-md border px-3 py-2" />
            {errors.amount && <div className="text-red-500 text-sm mt-1">{String(errors.amount)}</div>}
          </div>

          <DialogFooter className="gap-2">
            <Button type="button" variant="outline" onClick={onClose}>
              Cancel
            </Button>
            <Button type="submit" disabled={processing}>
              {processing ? 'Saving...' : 'Save'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
