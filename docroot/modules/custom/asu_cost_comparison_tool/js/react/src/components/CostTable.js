import React, { useEffect, useMemo, useRef, useState, useCallback } from 'react';
//import CostRow from './CostRow';
const CostRow =  React.lazy(() => import('./CostRow'));

/**
 * CostTable - renders rows and a footer with Totals and Count per column.
 *
 * props:
 *  - rows: array of row definitions
 *  - columns: array of column defs (first column is 'your_costs' label column)
 *  - values: object mapping rowId -> { colKey: value, ... }
 *  - onChange: function(rowId, colKey, newValue)
 *  - setTotalCosts: optional function(totals) called when totals change
 */

export default function CostTable({ rows = [], columns = [], values = {}, onChange, onLabelChange, setTotalCosts, errors, onValidationChange, onLabelValidationChange, }) {
  // ensure numericColumns is stable enough
  const numericColumns = useMemo(() => columns.filter(col => col.key !== 'your_costs'), [columns]);
  
  // compute totals *only when rows, numericColumns or values change*
  const totals = useMemo(() => {
    const t = {};
    numericColumns.forEach(col => {
      t[col.key] = 0;
    });

    rows.forEach(row => {
      const rowVals = values[row.id] || {};
      numericColumns.forEach(col => {
        const raw = rowVals[col.key];
        const normalized = typeof raw === 'string' ? raw.replace(/[\s,$]/g, '') : raw;
        const num = parseFloat(normalized);
        if (!Number.isNaN(num)) {
          t[col.key] += num;
        }
      });
    });

    return t;
  }, [rows, numericColumns, values]);
   
  // only call setTotalCosts when totals truly change (avoid calling it every render)
  const prevTotalsRef = useRef(null);
  useEffect(() => {
    if (typeof setTotalCosts !== 'function') return;

    // fast deep-compare via JSON; fine for small totals object
    const prev = prevTotalsRef.current;
    const curStr = JSON.stringify(totals);
    const prevStr = prev ? JSON.stringify(prev) : null;
   
    if (curStr !== prevStr) {
      // totals changed — call setter and update ref
      setTotalCosts(totals);
      prevTotalsRef.current = totals;
    }
    // otherwise do nothing (prevents parent re-render storms)
  }, [totals, setTotalCosts]);
  
  const [labelErrors, setLabelErrors] = useState({});
  //const hasErrors = useMemo(() => Object.keys(labelErrors).length > 0, [errors]);
  const validateLabel = (key, value) => {
    // empty label is allowed -> no error
    if (value.trim() === '') return '';
    if (!/^[a-zA-Z0-9 .'-]+$/.test(value)) {
      return 'Only letters, numbers, spaces, . and - allowed';
    }
    return '';
  };
  
  const hasLabelErrors = useMemo(
    () => Object.values(labelErrors).some(err => Boolean(err && err.length > 0)),
    [labelErrors]
  );
  
  // notify parent once when hasLabelErrors changes
  useEffect(() => {
    
    if (typeof onLabelValidationChange === 'function') {
      onLabelValidationChange(hasLabelErrors);
    }
  }, [hasLabelErrors, onLabelValidationChange]);

  return (
    <div className="table-wrapper costTableDiv pt-4">
      <table className="table-responsive uds-table responsive-enabled table react-table" aria-label="Estimated annual costs">
         <thead className="gold-table-header custom-entry-header">
         
          <tr>
          {columns.map((col) => (
            <th scope="col" key={col.key} >
               <span className="sr-only">School names</span>
              {col.editable ? (
                // editable label: input that updates parent via onLabelChange
                <>
                <input
                  type="text"
                  value={col.label}
                  //onChange={(e) => onLabelChange(col.key, e.target.value)}
                  onChange={(e) => {
                    const val = e.target.value;
                    onLabelChange(col.key, val);
                    setLabelErrors(prev => ({
                      ...prev,
                      [col.key]: validateLabel(col.key, val),
                    }));
                  }}
                  placeholder="Add School name"
                  className={labelErrors[col.key] ? 'error-text' : 'customLabels'}
                  aria-label="School name column header"
                />
                 {labelErrors[col.key] && (
                  <div className="error-text">
                    {labelErrors[col.key]}
                  </div>
                )}
                </>
              ) : (
                col.label
              )}
            </th>
          ))}
        </tr>
        </thead> 
        <tbody>
          {rows.map((row, idx) => (
            <CostRow
              key={row.id}
              errors={errors}
              row={row}
              columns={columns}
              rowValues={values[row.id]}
              onChange={onChange}
              rowIndex={idx}
              validation={onValidationChange}
            />
          ))}
        </tbody>
      </table>
    </div>
  );
}
