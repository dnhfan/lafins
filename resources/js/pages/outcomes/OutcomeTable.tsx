import BaseResourceTable, { Column } from '@/components/BaseResourceTable';
import OutcomeModal from '@/components/OutcomeModal';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Simple paginator type
type Paginator<T> = { data: T[]; meta?: Record<string, unknown> };

function isPaginator<T>(v: unknown): v is Paginator<T> {
    return (
        typeof v === 'object' &&
        v !== null &&
        'data' in (v as object) &&
        Array.isArray((v as Paginator<unknown>).data)
    );
}

type PaginatorShape = {
    current_page?: number;
    last_page?: number;
    last?: number;
    total?: number;
    path?: string;
    meta?: { current_page?: number; last_page?: number };
};

interface Outcome {
    id: number | string;
    date?: string;
    category?: string;
    jar_id?: number | string | null;
    jar_label?: string;
    description?: string;
    amount?: number | string;
    formatted_amount?: string;
}

interface OutcomesPageProps {
    outcomes?: Outcome[] | Paginator<Outcome>;
    loading?: boolean;
    error?: string | null;
}

export default function OutcomeTable() {
    const { props } = usePage<OutcomesPageProps & Record<string, unknown>>();

    useEffect(() => {
        if (typeof console.groupCollapsed === 'function')
            console.groupCollapsed('Outcomes props');
        console.log('full props:', props);
        if (typeof console.groupEnd === 'function') console.groupEnd();
    }, [props]);

    const raw: unknown = props?.outcomes as unknown;
    const isPag = isPaginator<Outcome>(raw);
    const outcomes = isPag
        ? raw.data
        : Array.isArray(raw)
          ? (raw as Outcome[])
          : ([] as Outcome[]);

    const loading = props?.loading ?? false;
    const error = props?.error ?? null;

    const [editing, setEditing] = useState<Outcome | null>(null);

    const filtered: Outcome[] = Array.isArray(outcomes) ? outcomes : [];

    function formatCurrency(value: number | string) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            maximumFractionDigits: 0,
        }).format(Number(value) || 0);
    }

    function handleDelete(id: number | string) {
        router.delete(`/outcomes/${id}`, { preserveState: false });
    }

    const columns: Column<Outcome>[] = [
        { key: 'date', header: 'Date' },
        { key: 'category', header: 'Category' },
        {
            key: 'jar_label',
            header: 'Jar',
            render: (item) => item.jar_label || 'None',
        },
        {
            key: 'description',
            header: 'Description',
            className: 'truncate max-w-[28rem]',
        },
        {
            key: 'amount',
            header: 'Amount',
            align: 'right',
            render: (item) =>
                item.formatted_amount ?? formatCurrency(item.amount ?? 0),
        },
    ];

    return (
        <>
            {loading ? (
                <p className="py-6 text-center">Loading...</p>
            ) : error ? (
                <p className="py-6 text-center text-red-500">
                    Lỗi: {String(error)}
                </p>
            ) : (
                <BaseResourceTable<Outcome>
                    data={filtered}
                    columns={columns}
                    onEdit={(item) => setEditing(item)}
                    onDelete={(id) => handleDelete(id)}
                    emptyMessage={'You dont have any outcomes :) .'}
                    paginator={isPag ? (raw as PaginatorShape) : undefined}
                />
            )}

            <OutcomeModal
                type="update"
                isOpen={Boolean(editing)}
                onClose={() => setEditing(null)}
                initialData={editing}
                onSuccess={() => setEditing(null)}
            />
        </>
    );
}
