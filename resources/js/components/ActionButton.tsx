import React from 'react';

type Variant = 'primary' | 'danger' | 'neutral';

interface Props {
  variant?: Variant;
  onClick?: () => void;
  title?: string;
  children?: React.ReactNode;
  size?: 'sm' | 'md';
  className?: string;
}

export default function ActionButton({ variant = 'neutral', onClick, title, children, size = 'sm', className = '' }: Props) {
  const base = 'inline-flex items-center gap-2 rounded-md font-medium transition transform';
  const padding = size === 'sm' ? 'px-2 py-1 text-xs' : 'px-3 py-1.5 text-sm';

  const variantClass =
    variant === 'primary'
      ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'
      : variant === 'danger'
      ? 'bg-rose-100 text-rose-700 hover:bg-rose-200'
      : 'bg-gray-100 text-slate-700 hover:bg-gray-200';

  return (
    <button
      title={title}
      onClick={onClick}
      className={`${base} ${padding} ${variantClass} ${className} hover:scale-105`}
      type="button"
    >
      {children}
    </button>
  );
}
