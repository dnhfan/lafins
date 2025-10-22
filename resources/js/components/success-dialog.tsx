import { useEffect, useState } from 'react';
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
  title?: string;
  confirmText?: string;
  onClose?: () => void;
};

// SuccessDialog: show a modal when message is provided, allows user to confirm/close.
export default function SuccessDialog({
  message,
  title = 'Success',
  confirmText = 'Confirm',
  onClose,
}: Props) {
  const [open, setOpen] = useState<boolean>(Boolean(message));

  useEffect(() => {
    setOpen(Boolean(message));
  }, [message]);

  const handleClose = () => {
    setOpen(false);
    onClose?.();
  };

  if (!message) return null;

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{message}</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button onClick={handleClose}>{confirmText}</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
