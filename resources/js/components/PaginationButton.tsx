import React from 'react';

interface Props extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  active?: boolean;
}

export default function PaginationButton({ active, className = '', children, ...rest }: Props) {
  const base = 'px-3 py-1 rounded text-sm border';
  const activeCls = active ? 'bg-indigo-600 text-white border-transparent' : 'bg-white text-slate-700';
  const disabledCls = rest.disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100';

  return (
    <button
      {...rest}
      className={`${base} ${activeCls} ${disabledCls} ${className}`}
    >
      {children}
    </button>
  );
}
