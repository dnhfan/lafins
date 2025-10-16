
import React, { useRef, useState } from "react";
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/react';

export default function SearchBox() {
    // state for open animation
    const [open, setOpen] = useState(false);
    const inputRef = useRef<HTMLInputElement | null>(null);
    const btnRef = useRef<HTMLButtonElement | null>(null);

    // state for search
    const { props } = usePage();
    
    // funtion for animate
    function animateClick() {
        const btn = btnRef.current;
        if (!btn) return;
        btn.classList.add("clicked");
        window.setTimeout(() => btn.classList.remove("clicked"), 420);
    }

    // trigger function when click
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

    // handle when press key
    function handleKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
        // escape 
        if (e.key === "Escape") {
            setOpen(false);
            inputRef.current?.blur();
        }

        if (e.key === 'Enter') {
            // submit search to server
            const val = inputRef.current?.value ?? '';
            const page = 1; // reset to first page on new search

            // preserve other filters if present (read from top-level props)
            const filters = (props && props.filters) ? props.filters : {};
            const data = { ...filters, search: val, page };
            Inertia.get(window.location.pathname, data, { preserveState: false, preserveScroll: true });
        }
    }

    function submitSearch() {
        const val = inputRef.current?.value ?? '';
        const page = 1;
        const filters = (props && props.filters) ? props.filters : {};
        const data = { ...filters, search: val, page };
        Inertia.get(window.location.pathname, data, { preserveState: false, preserveScroll: true });
    }

    return (
        <div className={`search-box ${open ? "open" : ""}`}>
            <button
                ref={btnRef}
                type="button"
                className="search-icon"
                aria-label="Open search"
                aria-expanded={open}
                onClick={() => { animateClick(); if (!open) { handleIconClick() } else { submitSearch() } }}
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