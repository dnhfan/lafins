import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import {TotalBalance, TotalIncome, TotalOutcome, JarDistributionPie, IncomeOutcomeBar, JarList, FillterBox} from './dashboard/DashboardComponent';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    const { props } = usePage();

    // Log controller-passed data to the browser console when navigating to Dashboard
    useEffect(() => {
        // Narrow log to relevant keys for readability, but also include full props
        if (typeof console.groupCollapsed === 'function') {
            console.groupCollapsed('Dashboard props');
        }
        console.log('full props:', props);
        console.log('summary:', props.summary);
        console.log('jars:', props.jars);
        console.log('jar_meta:', props.jar_meta);
        if (typeof console.groupEnd === 'function') {
            console.groupEnd();
        }
    }, [props]);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 min-h-screen flex-col gap-4 overflow-x-auto rounded-xl p-4">

                {/* fillter box */}
                <FillterBox className = "flex" />

                {/* Sumary */}
                <div id='sumary' className="relative z-20 flex-none overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">

                    <div className="grid auto-rows-auto gap-4 md:grid-cols-3 p-4">
                        <div className="card-default">
                            <TotalBalance />
                        </div>
                        <div className="card-default">
                            <TotalIncome />
                        </div>
                        <div className="card-default">
                            <TotalOutcome />
                        </div>
                    </div>

                </div>

                {/* chart: split into two columns (left: pie, right: income/outcome) */}
                <div id='chart' className="relative overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-[40vh] dark:border-sidebar-border">
                    <div className="p-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 h-full items-center">
                            <div className="flex items-center justify-center">
                                <div className="w-full max-w-2xl">
                                    <JarDistributionPie />
                                </div>
                            </div>
                            <div className="flex items-center justify-center">
                                <div className="w-full">
                                    <IncomeOutcomeBar />
                                </div>
                            </div>
                        </div>
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
