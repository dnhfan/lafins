import BaseModal, { BaseModalField } from './BaseModal';

type ModalType = 'add' | 'update';

interface OutcomeModalProps {
  type: ModalType;
  isOpen: boolean;
  onClose: () => void;
  initialData?: {
    id?: number | string;
    date?: string;
    category?: string;
    description?: string;
    amount?: number | string;
  } | null;
  onSuccess?: () => void;
}

const outcomeFields: BaseModalField[] = [
  {
    name: 'date',
    label: 'Date',
    type: 'date',
    required: true,
  },
  {
    name: 'category',
    label: 'Category',
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

export default function OutcomeModal({ type, isOpen, onClose, initialData = null, onSuccess }: OutcomeModalProps) {
  return (
    <BaseModal
      type={type}
      isOpen={isOpen}
      onClose={onClose}
      title={type === 'add' ? 'Add Outcome' : 'Edit Outcome'}
      fields={outcomeFields}
      initialData={initialData}
      onSuccess={onSuccess}
      storeUrl="/outcomes"
      updateUrl={(id: number) => `/outcomes/${id}`}
    />
  );
}
