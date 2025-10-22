import { useState } from 'react';
import ActionButton from '@/components/ActionButton';
import { Button } from '@/components/ui/button';
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	DialogTrigger,
	DialogClose,
} from '@/components/ui/dialog';

type Props = {
	onConfirm: () => void;
	title?: string;
	description?: string;
	confirmText?: string;
	cancelText?: string;
	disabled?: boolean;
	className?: string;
};

// A reusable delete confirmation component
// Renders a Delete button; when clicked, opens a confirmation dialog.
// On confirm, calls onConfirm() provided by parent.
export default function DeleteRow({
	onConfirm,
	title = 'Delete this item?',
	description = 'This action cannot be undone. Are you sure you want to delete?',
	confirmText = 'Delete',
	cancelText = 'Cancel',
	disabled = false,
	className = '',
}: Props) {
	const [open, setOpen] = useState(false);
	const [processing, setProcessing] = useState(false);

	const handleConfirm = async () => {
		try {
			setProcessing(true);
			await Promise.resolve(onConfirm());
			setOpen(false);
		} finally {
			setProcessing(false);
		}
	};

	return (
		<Dialog open={open} onOpenChange={setOpen}>
			<DialogTrigger asChild>
				<span>
					<ActionButton
						variant="danger"
						title="Delete"
						className={className}
						onClick={() => setOpen(true)}
					>
						<i className="fa-solid fa-trash" />
						<span className="hidden sm:inline">Delete</span>
					</ActionButton>
				</span>
			</DialogTrigger>

			<DialogContent>
				<DialogHeader>
					<DialogTitle>{title}</DialogTitle>
					<DialogDescription>{description}</DialogDescription>
				</DialogHeader>

				<DialogFooter className="gap-2">
					<DialogClose asChild>
						<Button variant="secondary" disabled={processing}>
							{cancelText}
						</Button>
					</DialogClose>
					<Button
						variant="destructive"
						onClick={handleConfirm}
						disabled={processing || disabled}
					>
						{processing ? 'Deleting…' : confirmText}
					</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}

