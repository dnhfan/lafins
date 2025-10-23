import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import JarList from './jarconfigs/jar-list';
import ConfigHeader from "./jarconfigs/jarconfig-head";
import { usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
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
    const { props } = usePage<{ jars?: any[] } & Record<string, unknown>>();
    const serverJars = props?.jars ?? [];

    // local editable state for percentages: { id: percent }
    const [percentages, setPercentages] = useState<Record<string | number, number>>(() => {
        const map: Record<string | number, number> = {};
        serverJars.forEach((j: any) => { map[j.id] = Number(j.percentage ?? 0); });
        return map;
    });

    // reflect server changes if props change
    useEffect(() => {
        const map: Record<string | number, number> = {};
        serverJars.forEach((j: any) => { map[j.id] = Number(j.percentage ?? 0); });
        setPercentages(map);
    }, [serverJars]);

    function handlePercentChange(id: number | string, percent: number) {
        setPercentages((s) => ({ ...s, [id]: percent }));
    }

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

        Inertia.post(
            JarsController.bulkUpdate.url(),
            { percentages: payload } as any,
            {
                preserveState: true,
                onSuccess: () => setAlert({ message: 'Jar percentages saved successfully.', variant: 'success' }),
                onError: (err) => setAlert({ message: 'Failed to save jar percentages.', variant: 'error' }),
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
            </main>
        </AppLayout>
        
    )
}