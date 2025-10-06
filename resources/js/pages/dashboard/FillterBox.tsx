import { useState } from 'react';
import TimeRangeModal from '@/components/TimeRangeModal';

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
    const [selected, setSelected] = useState<string>('Day');

    return (
        <div className={`${className ?? ''} w-full flex gap-2`}>
            <Fillter name="Day" selected={selected === 'Day'} onClick={() => setSelected('Day')} />
            <Fillter name="Month" selected={selected === 'Month'} onClick={() => setSelected('Month')} />
            <Fillter name="Year" selected={selected === 'Year'} onClick={() => setSelected('Year')} />
            <TimeRangeModal
                onApply={(s, e) => {
                    console.log('Applied range', s, e);
                    setSelected('Options');
                }}
                onCancel={() => setSelected('Day')}
            >
                <Fillter name="Options" selected={selected === 'Options'} onClick={() => setSelected('Options')} />
            </TimeRangeModal>
        </div>
    );
}
