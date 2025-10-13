import FillterBox from "../../components/FilterBox";
import SearchBox from "../../components/SearchBox";
import FillterOrder from "../../components/filter-order";
import AddBtn from "../../components/add-btn";
import { useState } from 'react';
import AddModel from './addModal';

export default function FSbox() {
    const [open, setOpen] = useState(false);
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
                    <FillterOrder />
                </div>
            </div>
        </div>
    );
}