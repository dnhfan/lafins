
import React from 'react';

interface ConfigHeaderProps {
    title?: string;
    subtitle?: string;
    totalPercent?: number; // sum of jar percentages
    onSave?: () => void;
    onReset?: () => void;
}

export default function ConfigHeader({
    title = 'Jar Configs',
    subtitle = 'Configure percentages for each jar',
    totalPercent = 0,
    onSave,
    onReset,
}: ConfigHeaderProps) {
    const percentOk = Math.abs(totalPercent - 100) < 0.1;

    return (
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div className="flex items-start gap-3">
                <div className="flex-shrink-0 w-12 h-12 rounded-lg bg-primary/10 dark:bg-primary/20 flex items-center justify-center">
                    <i className="fa-solid fa-screwdriver-wrench text-primary" aria-hidden />
                </div>
                <div>
                    <h1 className="text-lg font-semibold text-slate-800 dark:text-white">{title}</h1>
                    <p className="text-sm text-slate-500 dark:text-slate-400">{subtitle}</p>
                </div>
            </div>

            <div className="flex items-center gap-3">
                <div className={`px-3 py-2 rounded-md border ${percentOk ? 'border-green-500 bg-green-50 text-green-700' : 'border-amber-400 bg-amber-50 text-amber-700'}`}>
                    <div className="text-sm">Total</div>
                    <div className="text-sm font-medium">{Math.round(totalPercent)}%</div>
                </div>

                <div className="flex gap-2">
                    <button onClick={onReset} className="px-3 py-2 rounded-md bg-transparent border text-sm text-slate-700 dark:text-white border-gray-200 hover:bg-gray-50">Reset</button>
                    <button onClick={onSave} className="px-3 py-2 rounded-md bg-primary text-white text-sm hover:bg-primary-600">Save</button>
                </div>
            </div>
        </div>
    );
}