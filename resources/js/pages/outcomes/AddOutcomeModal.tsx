import OutcomeModal from '../../components/OutcomeModal';

export default function AddOutcomeModal(props: { isOpen: boolean; onClose: () => void; onSuccess?: () => void }) {
  return <OutcomeModal type="add" isOpen={props.isOpen} onClose={props.onClose} onSuccess={props.onSuccess} />;
}
