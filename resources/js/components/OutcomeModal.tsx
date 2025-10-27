import BaseModal, { BaseModalField } from './BaseModal';
import { usePage } from '@inertiajs/react';
import type { Jar } from '@/types';
import OutcomeController from '@/actions/App/Http/Controllers/OutcomeController';

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
    jar_id?: number | string | null;
  } | null;
  onSuccess?: () => void;
}

export default function OutcomeModal({ type, isOpen, onClose, initialData = null, onSuccess }: OutcomeModalProps) {
  const { props } = usePage<{ jars?: Jar[] }>();
  const jars = props?.jars ?? [];

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
      name: 'jar_id',
      label: 'Jar',
      type: 'select',
      required: true,
  options: jars.map((jar) => ({ label: jar.name|| jar.key || jar.id.toString(), value: jar.id })),
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

  // helper to format currency
  function formatCurrency(amount: number | null | undefined) {
    if (amount == null) return '-';
    try {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(amount));
    } catch (e) {
      console.error("something wrong when format the amount: ", e);
      return String(amount);
    }
  }

  // renderer to show extra content under a field
  function renderFieldExtra(fieldName: string, value: string, data: Record<string, string>) {
    if (fieldName !== 'jar_id') return null;
    const selectedId = Number(value || data['jar_id'] || initialData?.jar_id || '');
    const jar = jars.find((j) => Number(j.id) === selectedId) as Jar | undefined;
    if (!jar) return null;

    return (
      <div className="mt-2 text-sm text-gray-600">Balance: <span className="font-medium">{formatCurrency(jar.balance)}</span></div>
    );
  }

  return (
    <BaseModal
      type={type}
      isOpen={isOpen}
      onClose={onClose}
      title={type === 'add' ? 'Add Outcome' : 'Edit Outcome'}
      fields={outcomeFields}
      initialData={initialData}
      onSuccess={onSuccess}
      storeUrl={OutcomeController.store.url()}
      updateUrl={(id) => OutcomeController.update.url(id)}
      fieldExtra={renderFieldExtra}
    />
  );
}
