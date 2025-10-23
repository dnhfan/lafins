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
  options: jars.map((jar) => ({ label: jar.label || jar.key || jar.id.toString(), value: jar.id })),
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
    />
  );
}
