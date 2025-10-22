import React, { ReactNode } from 'react';
import Pagination from './Pagination';
import BaseResourceRow from './BaseResourceRow';

export type IdLike = number | string;

export type ColumnAlign = 'left' | 'right' | 'center';

export type Column<T extends { id: IdLike }> = {
  key: Extract<keyof T, string>;
  header: string;
  className?: string;
  align?: ColumnAlign;
  render?: (item: T, index: number) => ReactNode;
};

export interface PaginatorLike {
  current_page?: number;
  last_page?: number;
  last?: number;
  total?: number;
  path?: string;
  meta?: { current_page?: number; last_page?: number };
}

export interface BaseResourceTableProps<T extends { id: IdLike }> {
  data: T[];
  columns: Column<T>[];
  getRowKey?: (item: T, index: number) => IdLike;
  actions?: (item: T) => ReactNode;
  onEdit?: (item: T) => void;
  onDelete?: (id: IdLike) => void;
  emptyMessage?: string;
  paginator?: PaginatorLike; // passed through to Pagination component
  className?: string;
  headerSticky?: boolean;
}

export default function BaseResourceTable<T extends { id: IdLike }>(props: BaseResourceTableProps<T>) {
  const {
    data,
    columns,
    getRowKey,
    actions,
    onEdit,
    onDelete,
    emptyMessage = 'No data',
    paginator,
    className,
    headerSticky = true,
  } = props;

  const hasDefaultActions = !actions && (onEdit || onDelete);

  return (
    <div className="overflow-x-auto">
      <div className={`bg-white dark:bg-slate-800 border rounded-lg shadow-sm overflow-hidden ${className ?? ''}`}>
        <table className="min-w-full text-sm">
            
            {/* table head */}
          <thead className={`bg-gray-50 dark:bg-slate-700 ${headerSticky ? '' : ''}`}>
            <tr className="text-left text-xs uppercase text-slate-500">
                {/* foreach cl -> create coll */}
              {columns.map((col, i) => (
                <th
                  key={String(col.key) + i}
                  className={`p-3 ${col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : ''} sticky top-0 ${col.className ?? ''}`}
                >
                  {col.header}
                </th>
              ))}
              {(actions || hasDefaultActions) && (
                <th className="p-3 sticky top-0 text-center">Actions</th>
              )}
            </tr>
          </thead>

          {/* tabe body */}
          <tbody>
            {data.map((item, idx) => (
              <BaseResourceRow
                key={String(getRowKey ? getRowKey(item, idx) : item.id)}
                item={item}
                idx={idx}
                columns={columns}
                actions={actions}
                onEdit={onEdit}
                onDelete={onDelete}
              />
            ))}
          </tbody>
        </table>
        {Array.isArray(data) && data.length === 0 && (
          <div className="p-6 text-center text-sm text-slate-500">{emptyMessage}</div>
        )}
        {paginator && (
          <Pagination paginator={paginator} />
        )}
      </div>
    </div>
  );
}
