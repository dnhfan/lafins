import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from './ui/dialog';
import { Button } from './ui/button';

type ModalType = 'add' | 'update';

// interfaces of input field
export interface BaseModalField {
  name: string;
  label: string;
  type: 'text' | 'date' | 'number' | 'textarea' | 'select';
  placeholder?: string;
  options?: Array<{ label: string; value: string | number }>;
  required?: boolean;
}

// interfaces of props
interface BaseModalProps {
  type: ModalType;
  isOpen: boolean;
  onClose: () => void;
  title: string;
  fields: BaseModalField[];
  initialData?: Partial<Record<string, string | number | null | undefined>> | null;
  onSuccess?: () => void;
  storeUrl: string;
  updateUrl: (id: number) => string;
}

// main funct
export default function BaseModal({
  type,
  isOpen,
  onClose,
  title,
  fields,
  initialData = null,
  onSuccess,
  storeUrl,
  updateUrl,
}: BaseModalProps) {
  // Create initial form data from fields
  const initialFormData = fields.reduce((acc, field) => {
    acc[field.name] = '';
    return acc;
  }, {} as Record<string, string>);

  const { data, setData, post, put, processing, errors, reset } = useForm(initialFormData);

  // Update form when modal is open
  useEffect(() => {
    if (initialData) {
      fields.forEach((field) => {
        const value = initialData[field.name];
        setData(field.name as keyof typeof data, value != null ? String(value) : '');
      });
    } else if (type === 'add') {
      reset();
    }
  }, [initialData, type, isOpen, fields, reset, setData]);

  // Handle input change
  function handleChange(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) {
    const { name, value } = e.target;
    setData(name as keyof typeof data, value);
  }

  // Handle submit
  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    // Normalize number fields
    fields.forEach((field) => {
      if (field.type === 'number') {
        setData(field.name as keyof typeof data, String(Number(data[field.name as keyof typeof data]) || 0));
      }
    });

    if (type === 'add') {
      post(storeUrl, {
        preserveScroll: true,
        onSuccess: () => {
          onClose();
          onSuccess?.();
        },
      });
    } else {
      const id = initialData?.id;
      if (!id) return;
      put(updateUrl(Number(id)), {
        preserveScroll: true,
        onSuccess: () => {
          onClose();
          onSuccess?.();
        },
      });
    }
  }

  // render view of modal
  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-xl" aria-describedby={undefined}>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-4">
            {/* map -> foreach -> create input div */}
          {fields.map((field) => (
            <div key={field.name}>
              <label className="block text-sm font-medium mb-1">
                {field.label}
                {field.required && <span className="text-red-500 ml-1">*</span>}
              </label>

              {field.type === 'textarea' ? (
                <textarea
                  name={field.name}
                  value={data[field.name] || ''}
                  onChange={handleChange}
                  placeholder={field.placeholder}
                  className="w-full rounded-md border px-3 py-2 min-h-[80px]"
                  required={field.required}
                />
              ) : field.type === 'select' ? (
                <select
                  name={field.name}
                  value={data[field.name] || ''}
                  onChange={handleChange}
                  className="w-full rounded-md border px-3 py-2"
                  required={field.required}
                >
                  <option value="">Select {field.label}</option>
                  {field.options?.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
              ) : (
                <input
                  name={field.name}
                  type={field.type}
                  value={data[field.name] || ''}
                  onChange={handleChange}
                  placeholder={field.placeholder}
                  className="w-full rounded-md border px-3 py-2"
                  required={field.required}
                />
              )}

              {errors[field.name] && (
                <div className="text-red-500 text-sm mt-1">{String(errors[field.name])}</div>
              )}
            </div>
          ))}

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
