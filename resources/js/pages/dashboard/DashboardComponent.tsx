// Barrel file: re-export all dashboard components from a single entry point.
// This file imports the components (written as .jsx files) and exports them
// as named exports so other parts of the app can do:
// import { TotalIncome, JarList } from './pages/dashboard/DashboardComponent'

import TotalOutcome from './TotalOutcome';
import TotalIncome from './TotalIncome';
import TotalBalance from './TotalBalance';
import JarList from './JarList';
import JarDistributionPie from './JarDistributionPie';
import IncomeOutcomeBar from './IncomeOutcomeBar';
import FillterBox from '../../components/FilterBox';

export {
	TotalOutcome,
	TotalIncome,
	TotalBalance,
	JarList,
	JarDistributionPie,
	IncomeOutcomeBar,
	FillterBox
};

// Default grouped export (optional convenience)
export default {
	TotalOutcome,
	TotalIncome,
	TotalBalance,
	JarList,
	JarDistributionPie,
	IncomeOutcomeBar,
	FillterBox
};
