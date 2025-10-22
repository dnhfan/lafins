import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Outcome",
        href: "/outcomes"
    },
];



export default function Outcomes() {

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Outcomes" />
            <main className="flex flex-1 flex-col gap-4 overflow-auto rounded-xl p-4">
                
            </main>
        </AppLayout>
    )
}