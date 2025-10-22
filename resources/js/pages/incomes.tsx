import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/react";
import FSbox from "./incomes/FSbox";
import IncomesTable from "./incomes/IncomesTable";
import SuccessDialog from "@/components/success-dialog";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Income",
        href: "/incomes"
    },
];

export default function Incomes() {
    const { props } = usePage<{ flash?: { success?: string | null; error?: string | null; status?: string | null } } & Record<string, unknown>>();
    const successMessage = props?.flash?.success ?? undefined;

    return (
        <>
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Incomes" />
                <main className="flex flex-1 flex-col gap-4 overflow-auto rounded-xl p-4">
                    <FSbox />
                    <IncomesTable />
                </main>
                <SuccessDialog message={successMessage ?? undefined} />
                
            </AppLayout> 
        </>
    )
}