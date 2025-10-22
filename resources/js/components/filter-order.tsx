import React, { useEffect, useRef, useState } from 'react'

export type SortBy = 'amount' | 'date'
export type SortDir = 'asc' | 'desc'

export interface FilterOrderValue {
	by: SortBy
	dir: SortDir
}

export interface FilterOrderProps {
	/** Initial value (optional) */
	value?: FilterOrderValue
	/** Called when selection changes */
	onChange?: (v: FilterOrderValue) => void
	/** Optional class name for wrapper */
	className?: string
	/** Sort fields: array of {label, value} */
	fields?: Array<{ label: string; value: string }>
}

/**
 * Component: FilterOrder
 * - two controls:
 *   1) sort by: amount / time
 *   2) sort direction: low -> high (asc) / high -> low (desc)
 */
export default function FilterOrder({ value, onChange, className, fields }: FilterOrderProps) {
	const defaultFields = [
		{ label: 'Date', value: 'date' },
		{ label: 'Amount', value: 'amount' },
	];
	const sortFields = fields && fields.length > 0 ? fields : defaultFields;
	const [by, setBy] = useState<SortBy | string>(value?.by ?? sortFields[0].value);
	const [dir, setDir] = useState<SortDir>(value?.dir ?? 'desc');
	// track first render to avoid emitting onChange on mount
	const mountedRef = useRef<boolean>(false);

	useEffect(() => {
		// keep external value in sync when prop changes / take props from server
		if (value) {
			if (value.by !== by) setBy(value.by)
			if (value.dir !== dir) setDir(value.dir)
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [value])

	useEffect(() => {
		// avoid calling onChange on initial mount — only notify when user actually changes selection
		if (!mountedRef.current) {
			mountedRef.current = true;
			return;
		}
	onChange?.({ by: by as SortBy, dir })
	}, [by, dir, onChange])

		return (
			<div className={`filter-order ${className ?? ''}`}>
				<div className="filter-row">
					<label className="filter-control" htmlFor="filter-by">
						<span className="filter-label"><i className='fa-solid fa-table-list filter-icon' aria-hidden /> Sort by</span>
											<select
												id="filter-by"
												className="filter-select"
												value={by}
												onChange={(e) => setBy(e.target.value)}
												aria-label="Sort by"
											>
												{sortFields.map(f => (
													<option key={f.value} value={f.value}>{f.label}</option>
												))}
											</select>
					</label>

					<label className="filter-control" htmlFor="filter-dir">
						<span className="filter-label"><i className='fa-solid fa-sort filter-icon' aria-hidden /> Direction</span>
						<select
							id="filter-dir"
							className="filter-select"
							value={dir}
							onChange={(e) => setDir(e.target.value as SortDir)}
							aria-label="Sort direction"
						>
							<option value="asc">Low → High</option>
							<option value="desc">High → Low</option>
						</select>
					</label>
				</div>
			</div>
		)
}
