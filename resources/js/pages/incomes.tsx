import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import FSbox from "./incomes/FSbox";
import IncomesTable from "./incomes/IncomesTable";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Income",
        href: "/incomes"
    },
];

export default function Incomes() {

    return (
        <>
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Incomes" />
                <main className="flex flex-1 flex-col gap-4 overflow-auto rounded-xl p-4">
                    <FSbox />
                    <IncomesTable />
                </main>
                
            </AppLayout> 
        </>
    )
}