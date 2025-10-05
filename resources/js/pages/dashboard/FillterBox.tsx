function Fillter({ name }: { name: string }) {
    return (
        <button
            type="button"
            className="flex-1 text-sm py-2 px-3 border border-sidebar-border/50 rounded-md bg-transparent hover:bg-muted transition-colors"
        >
            {name}
        </button>
    );
}

interface FillterBoxProps {
    className?: string;
}

export default function FillterBox({ className }: FillterBoxProps) {
    return (
        <div className={`${className ?? ''} w-full flex gap-2`}>
            <Fillter name="Day" />
            <Fillter name="Month" />
            <Fillter name="Year" />
            <Fillter name="Options" />
        </div>
    );
}
