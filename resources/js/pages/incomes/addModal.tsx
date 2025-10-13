
import IncomeModal from '../../components/IncomeModal';

export default function AddModel(props: { isOpen: boolean; onClose: () => void; onSuccess?: () => void }) {
  return <IncomeModal type="add" isOpen={props.isOpen} onClose={props.onClose} onSuccess={props.onSuccess} />;
}