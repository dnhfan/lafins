
import React, { useRef, useState } from "react";

export default function SearchBox() {
    const [open, setOpen] = useState(false);
    const inputRef = useRef<HTMLInputElement | null>(null);
    const btnRef = useRef<HTMLButtonElement | null>(null);

    function animateClick() {
        const btn = btnRef.current;
        if (!btn) return;
        btn.classList.add("clicked");
        window.setTimeout(() => btn.classList.remove("clicked"), 420);
    }

    function handleIconClick() {
        animateClick();
        setOpen(true);
        // focus the input shortly after opening so keyboard users can type
        setTimeout(() => inputRef.current?.focus(), 50);
    }

    function handleBlur() {
        // collapse when input loses focus
        setOpen(false);
    }

    function handleKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
        if (e.key === "Escape") {
            setOpen(false);
            inputRef.current?.blur();
        }
    }

    return (
        <div className={`search-box ${open ? "open" : ""}`}>
            <button
                ref={btnRef}
                type="button"
                className="search-icon"
                aria-label="Open search"
                aria-expanded={open}
                onClick={handleIconClick}
                onMouseDown={animateClick}
            >
                <i className="fa-solid fa-search" aria-hidden />
            </button>

            <input
                ref={inputRef}
                className="search-input"
                type="text"
                name="search"
                id="search"
                placeholder="Search"
                onBlur={handleBlur}
                onKeyDown={handleKeyDown}
            />
        </div>
    );
}