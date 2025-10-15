import React from 'react';

interface Props extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  active?: boolean;
}

export default function PaginationButton({ active, className = '', children, ...rest }: Props) {
  // Base styles + animations: smooth transform + shadow on active
  const base = 'px-3 py-1 rounded text-sm border transition-transform duration-200 ease-in-out';
  const activeCls = active
    ? 'bg-indigo-600 text-white border-transparent shadow-md transform scale-105'
    : 'bg-white text-slate-700';
  const disabledCls = rest.disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100 hover:scale-[1.3]';

  // Focus ring for accessibility and subtle press effect
  const focusCls = 'focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1';

  return (
    <button
      {...rest}
      className={`${base} ${activeCls} ${disabledCls} ${focusCls} ${className}`}
    >
      {children}
    </button>
  );
}
