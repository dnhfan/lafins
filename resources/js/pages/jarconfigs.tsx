import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import JarList from './jarconfigs/jar-list';
import ConfigHeader from "./jarconfigs/jarconfig-head";
import { usePage } from '@inertiajs/react';
import { useState, useEffect, useMemo } from 'react';
import type { Jar } from '@/types';
import JarsController from '@/actions/App/Http/Controllers/JarsController';
import { Inertia } from '@inertiajs/inertia';
import DeleteAllDataBox from './jarconfigs/del-data';
import SuccessDialog from '@/components/success-dialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Jarsconfig",
        href: "/jarsconfig"
    },
];

export default function Jarsconfig() {
    const { props } = usePage<{ jars?: Jar[] } & Record<string, unknown>>();
    const serverJars = useMemo(() => props?.jars ?? [], [props?.jars]);

    // local editable state for percentages: { id: percent }
    const [percentages, setPercentages] = useState<Record<string | number, number>>(() => {
        const map: Record<string | number, number> = {};
        (serverJars as Jar[]).forEach((j) => { map[j.id] = Number(j.percentage ?? 0); });
        return map;
    });

    // reflect server changes if props change
    useEffect(() => {
        const map: Record<string | number, number> = {};
        (serverJars as Jar[]).forEach((j) => { map[j.id] = Number(j.percentage ?? 0); });
        setPercentages(map);
    }, [serverJars]);

    function handlePercentChange(id: number | string, percent: number) {
        setPercentages((s) => ({ ...s, [id]: percent }));
    }

    // Local alert state used to drive the shared alert dialog
    const [alert, setAlert] = useState<{ message: string; variant: 'success' | 'error' } | null>(null);
    function clearAlert() { setAlert(null); }

    async function handleSave() {
        // call bulkUpdate
        // convert keys to strings and ensure values are numbers
        const payload: Record<string, number> = {};
        Object.entries(percentages).forEach(([k, v]) => (payload[String(k)] = Number(v || 0)));

        // Validate total equals 100 (allow small epsilon)
        const total = Object.values(payload).reduce((a, b) => a + Number(b || 0), 0);
        const epsilon = 0.01;
        if (Math.abs(total - 100) > epsilon) {
            // show fail alert and prevent save
            setAlert({ message: `Total percentage must equal 100%. Current total is ${total.toFixed(2)}%.`, variant: 'error' });
            return;
        }
        const form = new FormData();
        Object.entries(payload).forEach(([k, v]) => form.append(`percentages[${k}]`, String(v)));

        Inertia.post(
            JarsController.bulkUpdate.url(),
            form,
            {
                preserveState: true,
                onSuccess: () => setAlert({ message: 'Jar percentages saved successfully.', variant: 'success' }),
                onError: () => setAlert({ message: 'Failed to save jar percentages.', variant: 'error' }),
            }
        );
    }

    async function handleReset() {
        Inertia.post(JarsController.reset.url(), {}, { preserveState: true });
    }

    const totalPercent = Object.values(percentages).reduce((a, b) => a + (Number(b) || 0), 0);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Jarconfigs" />
            <main className="flex flex-1 flex-col gap-4 overflow-auto rounded-xl p-4">
                <ConfigHeader onSave={handleSave} onReset={handleReset} totalPercent={totalPercent} />
                
                <JarList className="w-full" onPercentChange={handlePercentChange} />
                <DeleteAllDataBox />
                <SuccessDialog message={alert?.message ?? undefined} variant={alert?.variant} onClose={clearAlert} suppressFlash={!!alert} />
            </main>
        </AppLayout>
        
    )
}