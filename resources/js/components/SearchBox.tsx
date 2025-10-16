
import React, { useRef, useState } from "react";
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/react';

export default function SearchBox() {
    // state for open animation
    const [open, setOpen] = useState(false);

   
    const inputRef = useRef<HTMLInputElement | null>(null);
    const btnRef = useRef<HTMLButtonElement | null>(null);

    // state for search
    // mirror open state in a ref so rapid clicks can read current value synchronously
    const openRef = useRef<boolean>(false);
    const { props } = usePage<{ filters?: Record<string, string | number | undefined> }>();
    // loading indicator while Inertia request is in progress
    const [loading, setLoading] = useState(false);
    
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
        openRef.current = true;
        // focus the input shortly after opening so keyboard users can type
        setTimeout(() => inputRef.current?.focus(), 50);
    }

    function handleBlur() {
        // collapse when input loses focus
        setOpen(false);
        openRef.current = false;
    }

    function handleFocus() {
        // open when the input receives focus (keyboard users/tabbing)
        setOpen(true);
        openRef.current = true;
    }

    // handle when press key
    function handleKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
        // escape 
        if (e.key === "Escape") {
            setOpen(false);
            openRef.current = false;
            inputRef.current?.blur();
        }

        if (e.key === 'Enter') {
            // submit search to server
            const val = inputRef.current?.value ?? '';
            const page = 1; // reset to first page on new search

            // preserve other filters if present (read from top-level props)
            const filters = props?.filters ?? {};
            const data = { ...filters, search: val, page };
            Inertia.get(window.location.pathname, data, {
                preserveState: false,
                preserveScroll: true,
                // show spinner while request is in flight
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
            });
        }
    }

    function submitSearch() {
        const val = inputRef.current?.value ?? '';
        const page = 1;
    const filters = props?.filters ?? {};
        const data = { ...filters, search: val, page };
        Inertia.get(window.location.pathname, data, {
            preserveState: false,
            preserveScroll: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    }

    // clear search from URL (and input) and reload preserving other filters
    function clearSearch() {
    const filters = props?.filters ? { ...props.filters } : {} as Record<string, string | number | undefined>;
        if (filters.search) delete filters.search;
        const data = { ...filters, page: 1 };

        // clear input visually
        if (inputRef.current) inputRef.current.value = '';
        // close input
        setOpen(false);
        openRef.current = false;

        Inertia.get(window.location.pathname, data, {
            preserveState: false,
            preserveScroll: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    }

    return (
        <div className={`search-box ${open ? "open" : ""}`}>
            <button
                ref={btnRef}
                type="button"
                className="search-icon"
                aria-label="Open search"
                aria-expanded={open}
                onClick={() => {
                    animateClick();
                    // use openRef to decide immediately whether to open or submit (avoids waiting for state update)
                    if (!openRef.current) {
                        handleIconClick();
                    } else {
                        submitSearch();
                    }
                }}
                onMouseDown={(e) => {
                    // Prevent the input from blurring before the click event fires.
                    // Without this, clicking the icon while input is focused causes onBlur to run
                    // and set openRef.current = false before onClick runs.
                    e.preventDefault();
                    animateClick();
                }}
            >
                {loading ? (
                    <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden>
                        <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" opacity="0.25"></circle>
                        <path d="M4 12a8 8 0 018-8" stroke="currentColor" strokeWidth="4" strokeLinecap="round"></path>
                    </svg>
                ) : (
                    <i className="fa-solid fa-search" aria-hidden />
                )}
            </button>

            <input
                ref={inputRef}
                className="search-input"
                type="text"
                name="search"
                id="search"
                placeholder="Search"
                onBlur={handleBlur}
                onFocus={handleFocus}
                onKeyDown={handleKeyDown}
            />
            { open && props && props.filters && props.filters.search ? (
                <button
                    type="button"
                    className="search-clear ml-2 text-sm text-muted-foreground"
                    aria-label="Clear search"
                    onClick={clearSearch}
                    onMouseDown={(e) => { e.preventDefault(); }}
                    tabIndex={0}
                    role="button"
                >
                    <i className="fa-solid fa-circle-xmark"></i>
                </button>
            ) : null}
        </div>
    );
}