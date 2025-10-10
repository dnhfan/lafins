import FillterBox from "../../components/FilterBox";
import SearchBox from "../../components/SearchBox";
import FillterOrder from "../../components/filter-order";
import AddBtn from "../../components/add-btn";

export default function FSbox() {
    return (
        <div className="fsbox">
            <div className="fsbox-top">
                <FillterBox endpoint="/incomes" />
            </div>

            <div className="fsbox-row">
                <div className="fsbox-left">
                    <div className="fsbox-search"><SearchBox /></div>
                    <div className="fsbox-add"><AddBtn iconOnly title="Add new income" /></div>
                </div>

                <div className="fsbox-right">
                    <FillterOrder />
                </div>
            </div>
        </div>
    );
}