import IncomeController from '../actions/App/Http/Controllers/IncomeController';
import BaseModal, { BaseModalField } from './BaseModal';

type ModalType = 'add' | 'update';

interface IncomeModalProps {
  type: ModalType;
  isOpen: boolean;
  onClose: () => void;
  initialData?: {
    id?: number | string;
    date?: string;
    source?: string;
    description?: string;
    amount?: number | string;
  } | null;
  onSuccess?: () => void;
}

const incomeFields: BaseModalField[] = [
  {
    name: 'date',
    label: 'Date',
    type: 'date',
    required: true,
  },
  {
    name: 'source',
    label: 'Category / Source',
    type: 'text',
    required: true,
  },
  {
    name: 'description',
    label: 'Description',
    type: 'textarea',
  },
  {
    name: 'amount',
    label: 'Amount',
    type: 'number',
    required: true,
  },
];

export default function IncomeModal({ type, isOpen, onClose, initialData = null, onSuccess }: IncomeModalProps) {
  return (
    <BaseModal
      type={type}
      isOpen={isOpen}
      onClose={onClose}
      title={type === 'add' ? 'Add Income' : 'Edit Income'}
      fields={incomeFields}
      initialData={initialData}
      onSuccess={onSuccess}
      storeUrl={IncomeController.store.url()}
      updateUrl={(id) => IncomeController.update.url(id)}
    />
  );
}
