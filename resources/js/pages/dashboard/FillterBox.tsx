import { useState, useEffect } from 'react';
import TimeRangeModal from '@/components/TimeRangeModal';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/react';

function Fillter({ name, selected, onClick }: { name: string; selected?: boolean; onClick?: () => void }) {
    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={selected}
            className={`flex-1 text-sm py-2 px-3 border rounded-md transition-colors ${selected ? 'bg-primary text-primary-foreground shadow-xs border-transparent' : 'border-sidebar-border/50 bg-transparent hover:bg-muted'}`}
        >
            {name}
        </button>
    );
}

interface FillterBoxProps {
    className?: string;
}

export default function FillterBox({ className }: FillterBoxProps) {
    const { props } = usePage();
    const filters = props?.filters ?? {} as Record<string, string | undefined>;

    function detectSelectedFromFilters(filters: { start?: string; end?: string; range?: string } | Record<string, string | undefined>) {
        // If server gives a canonical range, trust it directly
        if (filters.range) {
            if (filters.range === 'day') return 'Day';
            if (filters.range === 'month') return 'Month';
            if (filters.range === 'year') return 'Year';
            return 'Options';
        }

        // fallback: infer from start/end
        if (!filters.start || !filters.end) return 'Day';
        const start = filters.start;
        const end = filters.end;
        const today = new Date().toISOString().slice(0, 10);
        if (start === today && end === today) return 'Day';
        // month
        const now = new Date();
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
        const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
        if (start === monthStart && end === monthEnd) return 'Month';
        // year
        const yearStart = new Date(now.getFullYear(), 0, 1).toISOString().slice(0, 10);
        const yearEnd = new Date(now.getFullYear(), 11, 31).toISOString().slice(0, 10);
        if (start === yearStart && end === yearEnd) return 'Year';

        return 'Options';
    }

    const [selected, setSelected] = useState<string>(() => detectSelectedFromFilters(filters));
    // Keep previous selected filter so we can restore if user cancels/outside-clicks the options modal
    const [prevSelected, setPrevSelected] = useState<string | null>(null);

    // update when server props change (e.g., after an Inertia visit)
    useEffect(() => {
        setSelected(detectSelectedFromFilters(props?.filters ?? {}));
    }, [props?.filters]);



    function applyRange(start: string, end: string, name: string) {
        Inertia.get('/dashboard', { start, end }, { preserveState: false, replace: false });
        setSelected(name);
    }

    function applyDay() {
        Inertia.get('/dashboard', { range: 'day' }, { preserveState: false, replace: false });
        setSelected('Day');
    }

    function applyMonth() {
        Inertia.get('/dashboard', { range: 'month' }, { preserveState: false, replace: false });
        setSelected('Month');
    }

    function applyYear() {
        Inertia.get('/dashboard', { range: 'year' }, { preserveState: false, replace: false });
        setSelected('Year');
    }

    return (
        <div className={`${className ?? ''} w-full flex gap-2`}>
            <Fillter name="Day" selected={selected === 'Day'} onClick={applyDay} />
            <Fillter name="Month" selected={selected === 'Month'} onClick={applyMonth} />
            <Fillter name="Year" selected={selected === 'Year'} onClick={applyYear} />
            <TimeRangeModal
                onApply={(s, e) => {
                    applyRange(s, e, 'Options');
                    // clear previous selection once applied
                    setPrevSelected(null);
                }}
                onCancel={() => {
                    // restore previous selection (or fallback to server-determined)
                    const restore = prevSelected ?? detectSelectedFromFilters(props?.filters ?? {});
                    setSelected(restore);
                    setPrevSelected(null);
                }}
            >
                <Fillter
                    name="Options"
                    selected={selected === 'Options'}
                    onClick={() => {
                        // open options modal and remember what was selected before
                        setPrevSelected(selected);
                        setSelected('Options');
                    }}
                />
            </TimeRangeModal>
        </div>
    );
}
