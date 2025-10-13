import IncomeModal from '../../components/IncomeModal';

export default function UpdateModal(props: { isOpen: boolean; onClose: () => void; onSuccess?: () => void }) {

    return (
        <IncomeModal type="update" isOpen={props.isOpen} onClose={props.onClose} onSuccess={props.onSuccess} />
    )
}