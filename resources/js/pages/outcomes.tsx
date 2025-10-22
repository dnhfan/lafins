import FSbox from "@/components/FSbox";
import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/react";
import AddOutcomeModal from "./outcomes/AddOutcomeModal";
import OutcomeTable from "./outcomes/OutcomeTable";
import type { Jar } from "@/types";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Outcome",
        href: "/outcomes"
    },
];

interface OutcomesPageProps {
    jars?: Jar[];
    [key: string]: unknown;
}

export default function Outcomes() {
    const { props } = usePage<OutcomesPageProps>();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Outcomes" />
            <main className="flex flex-1 flex-col gap-4 overflow-auto rounded-xl p-4">
                <FSbox 
                    endpoint="/outcomes"
                    addTitle="Add new outcome"
                    AddModalComponent={AddOutcomeModal}
                    sortFields={[
                        { label: 'Date', value: 'date' },
                        { label: 'Amount', value: 'amount' }
                    ]}
                    defaultSortBy="date"
                    defaultSortDir="desc"
            />
                <OutcomeTable />
            </main>
        </AppLayout>
    )
}