
import React from 'react'

export interface AddBtnProps {
    onClick?: () => void
    className?: string
    title?: string
    /** Show icon only (hide text) */
    iconOnly?: boolean
}

export default function AddBtn({ onClick, className, title, iconOnly = false }: AddBtnProps) {
    const btnRef = React.useRef<HTMLButtonElement | null>(null);

    function animateClick() {
        const el = btnRef.current;
        if (!el) return;
        el.classList.add('clicked');
        window.setTimeout(() => el.classList.remove('clicked'), 420);
    }

            return (
            <button
                ref={btnRef}
                type="button"
                className={`add-btn ${!iconOnly ? 'icon-only' : ''} ${className ?? ''}`}
                onClick={onClick}
                onMouseDown={animateClick}
                aria-label={title ?? 'Add'}
                title={title ?? 'Add'}
            >
                <span className="add-icon"><i className="fa-solid fa-plus" aria-hidden /></span>
                <span className="add-text">Add</span>
            </button>
        )
}