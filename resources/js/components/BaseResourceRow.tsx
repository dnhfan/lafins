import React, { ReactNode } from 'react'
import ActionButton from '@/components/ActionButton'
import DeleteRow from '@/components/delete-row'
import type { Column, IdLike } from './BaseResourceTable'

export interface BaseResourceRowProps<T extends { id: IdLike }> {
  item: T
  idx: number
  columns: Column<T>[]
  actions?: (item: T) => ReactNode
  onEdit?: (item: T) => void
  onDelete?: (id: IdLike) => void
}

export default function BaseResourceRow<T extends { id: IdLike }>({ item, idx, columns, actions, onEdit, onDelete }: BaseResourceRowProps<T>) {
  const hasDefaultActions = !actions && (onEdit || onDelete)

  return (
    <tr
      className={`border-t ${idx % 2 === 0 ? 'bg-white dark:bg-black' : 'bg-gray-50 dark:bg-slate-800'} hover:bg-gray-100 dark:hover:bg-slate-700`}
    >
      {columns.map((col, ci) => (
        <td
          key={String(col.key) + ci}
          className={`p-3 align-top ${col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : ''} ${col.className ?? ''}`}
        >
          {col.render
            ? col.render(item, idx)
            : (() => {
                const key = col.key as string
                const rec = item as unknown as Record<string, unknown>
                const value = rec[key]
                return value != null && value !== '' ? String(value) : ''
              })()}
        </td>
      ))}
      {(actions || hasDefaultActions) && (
        <td className="p-3 align-top flex gap-2 justify-center">
          {actions ? (
            actions(item)
          ) : (
            <>
              {onEdit && (
                <ActionButton variant="primary" title="Edit" onClick={() => onEdit(item)}>
                  <i className="fa-solid fa-pencil " />
                  <span className="hidden sm:inline">Edit</span>
                </ActionButton>
              )}
              {onDelete && <DeleteRow onConfirm={() => onDelete(item.id)} />}
            </>
          )}
        </td>
      )}
    </tr>
  )
}
