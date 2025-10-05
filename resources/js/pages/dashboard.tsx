import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

import {TotalBalance, TotalIncome, TotalOutcome, JarDistributionPie, IncomeOutcomeBar, JarList} from './dashboard/DashboardComponent';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 min-h-screen flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Sumary */}
                <div id='sumary' className="relative flex-[2] min-h-[30vh] overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <div className="grid auto-rows-min gap-4 md:grid-cols-3 p-4 h-full">
                        <div className="relative h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <TotalBalance className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="relative h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <TotalIncome className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="relative h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <TotalOutcome className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                    </div>
                </div>

                {/* chart */}
                <div id='chart' className="relative flex-[4] overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <div className="absolute inset-0 size-full">
                        <JarDistributionPie />
                    </div>
                </div>

                {/* Jar list */}
                <div id='jarlist' className="relative flex-[2] min-h-[30vh] overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <div className="absolute inset-0 size-full overflow-auto p-4">
                        <JarList className={"w-full h-full"} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
