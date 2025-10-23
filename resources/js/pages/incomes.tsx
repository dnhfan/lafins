import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/react";
import FSbox from "../components/FSbox";
import AddIncomeModal from "./incomes/addModal";
import IncomesTable from "./incomes/IncomesTable";
import SuccessDialog from "@/components/alert-dialog";

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
                    <FSbox
                        endpoint="/incomes"
                        addTitle="Add new income"
                        AddModalComponent={AddIncomeModal}
                        sortFields={[
                            { label: 'Date', value: 'date' },
                            { label: 'Amount', value: 'amount' }
                        ]}
                        defaultSortBy="date"
                        defaultSortDir="desc"
                    />
                    <IncomesTable />
                </main>
                <SuccessDialog message={successMessage ?? undefined} />
                
            </AppLayout> 
        </>
    )
}