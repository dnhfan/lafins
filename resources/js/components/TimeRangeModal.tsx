import React, { useState } from 'react';
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from '@/components/ui/dialog';

// make Date type -> input string (local date, avoids UTC shifts)
function pad(n: number) {
  return n < 10 ? `0${n}` : String(n);
}

function toInputDate(d: Date) {
  // Use local date parts so the formatted string matches the user's local day
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

// Parse an input (YYYY-MM-DD) into a local Date object
function parseInputDate(s: string) {
  const [y, m, day] = s.split('-').map((v) => Number(v));
  return new Date(y, m - 1, day);
}

// props of model
interface TimeRangeModalProps {
  onApply?: (start: string, end: string) => void;
  onCancel?: () => void; // called when dialog closed without apply
  triggerLabel?: string;
  children?: React.ReactNode; // optional custom trigger
}

// main component
export default function TimeRangeModal({ onApply, onCancel, triggerLabel = 'Options', children }: TimeRangeModalProps) {
  const [start, setStart] = useState<string>(() => toInputDate(new Date()));
  const [end, setEnd] = useState<string>(() => toInputDate(new Date()));
  const didApplyRef = React.useRef(false);

  function apply() {
    // simple validation: ensure end is not in the future and start <= end
    const today = toInputDate(new Date());
    let s = start;
    let e = end;

    if (e > today) {
      e = today;
      setEnd(e);
    }

    const sd = parseInputDate(s);
    const ed = parseInputDate(e);

    if (sd > ed) {
      // if start is after end, clamp start to end
      s = e;
      setStart(s);
    }

    // mark that apply was used so onOpenChange knows it was a successful apply
    didApplyRef.current = true;
    onApply?.(s, e);
  }

  function applyPreset(days: number) {
    const now = new Date();
    const from = new Date(now);
    from.setDate(now.getDate() - (days - 1));
    setStart(toInputDate(from));
    setEnd(toInputDate(now));
  }

  return (
    <Dialog
      onOpenChange={(open: boolean) => {
        if (!open) {
          // dialog closed; if apply was not used call onCancel
          if (!didApplyRef.current) {
            onCancel?.();
          }
          // reset flag for next open
          didApplyRef.current = false;
        } else {
          // when opening, ensure apply flag cleared
          didApplyRef.current = false;
        }
      }}
    >
      {/* If children provided, use it as the trigger (asChild). Otherwise render the default trigger button. */}
      {children ? (
        <DialogTrigger asChild>{children}</DialogTrigger>
      ) : (
        <DialogTrigger asChild>
          <button type="button" className="flex-1 text-sm py-2 px-3 border border-sidebar-border/50 rounded-md bg-transparent hover:bg-muted transition-colors">
            {triggerLabel}
          </button>
        </DialogTrigger>
      )}
      <DialogContent className="sm:max-w-md">
        <DialogHeader className="text-center">
          <DialogTitle>Chọn khoảng thời gian</DialogTitle>
          <DialogDescription>Chọn ngày bắt đầu và kết thúc hoặc dùng các lựa chọn nhanh.</DialogDescription>
        </DialogHeader>

        <div className="grid gap-3 py-2">
          <div className="grid grid-cols-2 gap-2">
            <label className="flex flex-col">
              <span className="text-sm text-muted-foreground">Bắt đầu</span>
              <input type="date" value={start} onChange={(e) => setStart(e.target.value)} className="mt-1 rounded-md border px-2 py-1" />
            </label>
            <label className="flex flex-col">
              <span className="text-sm text-muted-foreground">Kết thúc</span>
              <input type="date" value={end} onChange={(e) => setEnd(e.target.value)} className="mt-1 rounded-md border px-2 py-1" />
            </label>
          </div>

          <div className="flex gap-2 flex-wrap">
            <button type="button" onClick={() => applyPreset(7)} className="text-sm px-2 py-1 rounded-md border bg-transparent hover:bg-muted">7 ngày</button>
            <button type="button" onClick={() => applyPreset(30)} className="text-sm px-2 py-1 rounded-md border bg-transparent hover:bg-muted">30 ngày</button>
            <button type="button" onClick={() => applyPreset(90)} className="text-sm px-2 py-1 rounded-md border bg-transparent hover:bg-muted">90 ngày</button>
            <button type="button" onClick={() => { const d = new Date(); setStart(toInputDate(new Date(d.getFullYear(), d.getMonth(), 1))); setEnd(toInputDate(new Date())); }} className="text-sm px-2 py-1 rounded-md border bg-transparent hover:bg-muted">Tháng này</button>
          </div>
        </div>

        <DialogFooter className="gap-2">
          <DialogClose asChild>
            <button type="button" className="px-3 py-2 rounded-md border">Hủy</button>
          </DialogClose>
          <DialogClose asChild>
            <button type="button" onClick={apply} className="px-3 py-2 rounded-md bg-primary text-primary-foreground">Áp dụng</button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
