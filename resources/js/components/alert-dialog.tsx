import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

type Props = {
  message?: string | null;
  variant?: 'success' | 'error';
  /** When true, ignore Inertia page flash props and only show explicit message */
  suppressFlash?: boolean;
  title?: string;
  confirmText?: string;
  onClose?: () => void;
};

// Shared flash dialog that shows success or error messages.
// Default export kept as `SuccessDialog` for backward compatibility.
export default function SuccessDialog({
  message: explicitMessage,
  variant: explicitVariant,
  suppressFlash = false,
  title,
  confirmText = 'OK',
  onClose,
}: Props) {
  const { props } = usePage<{ flash?: { success?: string | null; error?: string | null } } & Record<string, unknown>>();

  const flashSuccess = props?.flash?.success ?? null;
  const flashError = props?.flash?.error ?? null;

  const message = explicitMessage ?? (suppressFlash ? null : (flashError ?? flashSuccess)) ?? null;
  const variant: 'success' | 'error' = explicitVariant ?? (suppressFlash ? 'success' : (flashError ? 'error' : 'success'));

  const [open, setOpen] = useState<boolean>(Boolean(message));

  useEffect(() => {
    setOpen(Boolean(message));
  }, [message]);

  const handleClose = () => {
    setOpen(false);
    onClose?.();
  };

  if (!message) return null;

  const resolvedTitle = title ?? (variant === 'error' ? 'Error' : 'Success');

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className={variant === 'error' ? 'text-red-700' : undefined}>{resolvedTitle}</DialogTitle>
          <DialogDescription className={variant === 'error' ? 'text-red-600' : undefined}>
            {message}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button onClick={handleClose} className={variant === 'error' ? 'bg-red-600 hover:bg-red-700' : undefined}>
            {confirmText}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
