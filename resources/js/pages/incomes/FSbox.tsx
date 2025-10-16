import FillterBox from "../../components/FilterBox";
import SearchBox from "../../components/SearchBox";
import FillterOrder from "../../components/filter-order";
import AddBtn from "../../components/add-btn";
import { useState } from 'react';
import AddModel from './addModal';
import { router, usePage } from "@inertiajs/react";

export default function FSbox() {
    const [open, setOpen] = useState(false);

    // state for sort
    const { props } = usePage<{ filters?: Record<string, string | number | undefined> }>();
    const { filters } = props ?? {};

    const handleSortChange = (v: { by:string; dir:string }) => {
        // avoid issuing a new visit if sort hasn't changed (prevents redundant reloads)
        const currentBy = filters?.sort_by ?? 'date';
        const currentDir = filters?.sort_dir ?? 'desc';
        if (String(currentBy) === String(v.by) && String(currentDir) === String(v.dir)) return;

        router.get('/incomes', {
            ...filters,
            sort_by: v.by,
            sort_dir: v.dir,
            page: 1,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <div className="fsbox">
            <div className="fsbox-top">
                <FillterBox endpoint="/incomes" />
            </div>

            <div className="fsbox-row">
                <div className="fsbox-left">
                    <div className="fsbox-search"><SearchBox /></div>
                    <div className="fsbox-add"><AddBtn  title="Add new income" onClick={() => setOpen(true)} /></div>
                    <AddModel isOpen={open} onClose={() => setOpen(false)} />
                </div>

                <div className="fsbox-right">
                    <FillterOrder 
                    value={{
                        by: (String(filters?.sort_by ?? 'date') as 'date' | 'amount'),
                        dir: (String(filters?.sort_dir ?? 'desc') as 'asc' | 'desc')
                    }}
                    onChange={handleSortChange}
                    />
                </div>
            </div>
        </div>
    );
}