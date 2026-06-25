import React, { useEffect, useMemo } from 'react';
import Tooltip from './Tooltip';

/**
 * Renders a single table row with the left label + tooltip and
 * input fields for each school column.
 */
export default function CostRow({
  row,
  columns,
  rowValues = {},
  onChange,
  rowIndex,
  errors = {},
  validation,
}) {
  return (
    <tr
      key={row.id}
      className={`webform-table-row ${rowIndex % 2 === 0 ? 'even' : 'odd'}`}
    >
      {columns.map((col) => {
        if (col.key === 'your_costs') {
          return (
            <td key={col.key}>
              <div className="label-cell">
                <span className="row-label">{row.label}</span>{' '}
                <Tooltip
                  id={`tooltip-${row.id}`}
                  label={row.label}
                  content={row.help}
                />
              </div>
            </td>
          );
        }

        const hasRowErrors = useMemo(
          () =>
            Object.values(errors).some((err) => Boolean(err && err.length > 0)),
          [errors],
        );
        // notify parent once when hasLabelErrors changes
        useEffect(() => {
          if (typeof validation === 'function') {
            validation(hasRowErrors);
          }
        }, [hasRowErrors, validation]);

        const value = rowValues ? (rowValues[col.key] ?? '') : '';
        const errorKey = `${row.id}-${col.key}`;
        const fieldError = errors[errorKey];

        const inputName = `estimated_annual_costs_${row.id}_${col.key}`;
        //console.log(col.key);
        const schoolName =
          col.key === 'asu'
            ? 'Arizona State Univeristy'
            : col.key === 'school2'
              ? col.label?.trim() || 'School 2'
              : col.key === 'school3'
                ? col.label?.trim() || 'School 3'
                : '';
        //const inputName = `estimated_annual_costs_${col.key}`;
        return (
          <td key={col.key} data-label={schoolName}>
            <input
              type="text"
              name={inputName}
              value={value ?? ''}
              //onChange={(e) => onChange(col.key, e.target.value)}
              onChange={(e) => onChange(row.id, col.key, e.target.value)}
              className={`form-textfield form-control ${fieldError ? 'input-error' : ''}`}
              aria-label={`${row.label}, ${col.label}`}
            />
            {fieldError && (
              <div className="error-text" role="alert">
                {fieldError}
              </div>
            )}
          </td>
        );
      })}
    </tr>
  );
}
