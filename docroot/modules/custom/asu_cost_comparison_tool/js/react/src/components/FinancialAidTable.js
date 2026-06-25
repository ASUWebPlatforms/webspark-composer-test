// src/components/AidTable.jsx
import React, {
  useEffect,
  useMemo,
  useState,
  useRef,
  useCallback,
} from 'react';
import Tooltip from './Tooltip';
import '../styles.css';

export default function AidTable({
  totalCosts = {},
  showTotals,
  aidValues = {},
  onAidChange,
  LocalStorageKey,
  school2,
  school3,
  school2Name,
  school3Name,
  labels = {},
  onValidationChange,
  onHydrated,
  reset,
}) {
  // helper to create a new empty scholarship/grant row
  const createEmptyRow = useCallback(
    () => ({
      id:
        typeof crypto !== 'undefined' && crypto.randomUUID
          ? crypto.randomUUID()
          : `${Date.now()}-${Math.random()}`,
      asu: '',
      school2: '',
      school3: '',
    }),
    [],
  );

  // Read drupalSettings if available; fallback maps used otherwise
  const formApiValues =
    typeof drupalSettings !== 'undefined' &&
    drupalSettings.asu_cost_comparison_tool
      ? drupalSettings.asu_cost_comparison_tool
      : null;

  const CustomText = formApiValues ? formApiValues.CustomText : '';
  const CustomText2 = formApiValues ? formApiValues.CustomText2 : '';
  const CustomText3 = formApiValues ? formApiValues.CustomText3 : '';

  const customTextBySchool = useMemo(
    () => ({
      within: CustomText,
      asuMore: CustomText2,
      asu: CustomText3,
    }),
    [CustomText, CustomText2, CustomText3],
  );

  // ----- component state -----
  const [loansRow, setLoansRow] = useState({
    subloansAsu: '',
    subloansSchool2: '',
    subloansSchool3: '',
    subloansHelp:
      (formApiValues && formApiValues.subsidiesLoansToolTip) ||
      'Subsidized loans',

    unsubloansAsu: '',
    unsubloansSchool2: '',
    unsubloansSchool3: '',
    unsubloansHelp:
      (formApiValues && formApiValues.unsubsidizedLoansToolTip) ||
      'Unsubsidized loans',

    pplusloansAsu: '',
    pplusloansSchool2: '',
    pplusloansSchool3: '',
    pplusloansHelp:
      (formApiValues && formApiValues.parentPlusLoansToolTip) ||
      'Parent PLUS loans',
  });

  const [extra, setExtra] = useState({
    booksAsu: '',
    booksSchool2: '',
    booksSchool3: '',
    booksHelp:
      (formApiValues && formApiValues.booksToolTip) || 'Books and supplies',

    transportAsu: '',
    transportSchool2: '',
    transportSchool3: '',
    transportHelp: 'Transportation',

    otherAsu: '',
    otherSchool2: '',
    otherSchool3: '',
    otherHelp: 'Other expenses',
  });

  const [scholarships, setScholarships] = useState([createEmptyRow()]);
  const [grants, setGrants] = useState([createEmptyRow()]);

  const scholarGrantToolTips = {
    Scholarships: formApiValues.scholarToolTip,
    Grants: formApiValues.grantToolTip,
  };

  // errors local to AidTable: keyed strings -> message
  const [errors, setErrors] = useState({});

  const placeHolder = 'Add yearly amount';

  const hasErrors = useMemo(() => Object.keys(errors).length > 0, [errors]);

  //reset values if reset button is clicked
  useEffect(() => {
    if (!reset) return;
    // Reset internal aid fields
    setScholarships([createEmptyRow()]);
    setGrants([createEmptyRow()]);
    setLoansRow({
      subloansAsu: '',
      subloansSchool2: '',
      subloansSchool3: '',
      subloansHelp: formApiValues?.subsidiesLoansToolTip || 'Subsidized loans',
      unsubloansAsu: '',
      unsubloansSchool2: '',
      unsubloansSchool3: '',
      unsubloansHelp:
        formApiValues?.unsubsidizedLoansToolTip || 'Unsubsidized loans',
      pplusloansAsu: '',
      pplusloansSchool2: '',
      pplusloansSchool3: '',
      pplusloansHelp:
        formApiValues?.parentPlusLoansToolTip || 'Parent PLUS loans',
    });
    // clear local errors if you keep them here
    setErrors({});
  }, [reset]);

  const didHydrateRef = useRef(false);

  //function to reload saved scholarships, grants, loans from aidValues prop on first load
  useEffect(() => {
    if (!aidValues || typeof aidValues !== 'object') return;
    if (reset) return;
    // don't overwrite user's editing after first hydration
    if (didHydrateRef.current) return;

    const hasScholarships =
      Array.isArray(aidValues.scholarships) &&
      aidValues.scholarships.length > 0;
    const hasGrants =
      Array.isArray(aidValues.grants) && aidValues.grants.length > 0;
    const hasLoans =
      aidValues.loansRow && Object.keys(aidValues.loansRow).length > 0;
    if (typeof onHydrated === 'function') onHydrated();
    if (!hasScholarships && !hasGrants && !hasLoans) return;

    if (hasScholarships) {
      setScholarships(
        aidValues.scholarships.map((r) => ({
          id: r.id ?? Date.now() + Math.random(),
          asu: r.asu ?? '',
          school2: r.school2 ?? '',
          school3: r.school3 ?? '',
        })),
      );
    }

    if (hasGrants) {
      setGrants(
        aidValues.grants.map((r) => ({
          id: r.id ?? Date.now() + Math.random(),
          asu: r.asu ?? '',
          school2: r.school2 ?? '',
          school3: r.school3 ?? '',
        })),
      );
    }

    if (aidValues.loansRow) {
      setLoansRow((prev) => ({ ...prev, ...aidValues.loansRow }));
    }

    // mark hydrated so we don't clobber user edits
    didHydrateRef.current = true;

    // notify parent (App.js) that hydration finished
    if (typeof onHydrated === 'function') onHydrated();
  }, [aidValues, onHydrated]);

  // ---------- validation helpers ----------
  const isNumeric = useCallback((val) => {
    if (val === '' || val === null || typeof val === 'undefined') return true;
    const cleaned = String(val).replace(/[\s,$]/g, '');
    return /^[0-9]+(?:\.[0-9]{0,2})?$/.test(cleaned);
  }, []);

  const fieldKey = useCallback(({ section, rowId, field }) => {
    if (section === 'scholarships' || section === 'grants') {
      return `${section}-${rowId}-${field}`; // e.g. scholarships-163423-asu
    }
    // extras/loans use named fields
    return `${section}-${field}`; // e.g. extra-booksAsu or loans-subloansSchool2
  }, []);

  const setFieldError = useCallback((key, message) => {
    setErrors((prev) => {
      const exists = prev[key];
      if (!message) {
        if (!exists) return prev; // nothing to do
        const next = { ...prev };
        delete next[key];
        return next;
      }
      if (exists === message) return prev; // no change
      return { ...prev, [key]: message };
    });
  }, []);

  // ---------- update helpers that validate on-change ----------
  // For scholarships / grants rows
  const updateRowField = useCallback(
    (rows, setRows, index, field, value, sectionName) => {
      const rowId = rows[index]?.id;
      const key = fieldKey({ section: sectionName, rowId, field });

      if (!isNumeric(value)) {
        setFieldError(
          key,
          'Only numeric values allowed (e.g. 12345 or 12345.67)',
        );
      } else {
        setFieldError(key, null);
      }

      setRows((prevRows) => {
        // avoid replacing array if value unchanged
        const currentValue = prevRows[index]?.[field] ?? '';
        if (currentValue === value) return prevRows;
        const updated = [...prevRows];
        updated[index] = { ...updated[index], [field]: value };
        return updated;
      });
    },
    [fieldKey, isNumeric, setFieldError],
  );

  // For loans rows
  const setLoanField = useCallback(
    (base, school, value) => {
      const fieldName =
        base +
        (school === 'asu'
          ? 'Asu'
          : school === 'school2'
            ? 'School2'
            : 'School3');
      const key = fieldKey({ section: 'loans', field: fieldName });

      if (!isNumeric(value)) {
        setFieldError(key, 'Only numeric values allowed');
      } else {
        setFieldError(key, null);
      }

      setLoansRow((prev) => {
        if (prev[fieldName] === value) return prev;
        return { ...prev, [fieldName]: value };
      });
    },
    [fieldKey, isNumeric, setFieldError],
  );

  // ---------- helpers to add/remove rows ----------
  const addRow = useCallback(
    (rows, setRows) => {
      setRows((prev) => [...prev, createEmptyRow()]);
    },
    [createEmptyRow],
  );

  const removeRow = useCallback(
    (rows, setRows, index) => {
      // also clear any errors associated with that row
      const rowId = rows[index]?.id;
      setErrors((prevErrors) => {
        const nextErrors = { ...prevErrors };
        ['asu', 'school2', 'school3'].forEach((f) => {
          const k = fieldKey({
            section: rows === scholarships ? 'Scholarships' : 'Grants',
            rowId,
            field: f,
          });
          if (nextErrors[k]) delete nextErrors[k];
        });
        // if unchanged, return prevErrors (avoid re-render)
        const same =
          Object.keys(nextErrors).length === Object.keys(prevErrors).length &&
          Object.keys(nextErrors).every((k) => prevErrors[k] === nextErrors[k]);
        return same ? prevErrors : nextErrors;
      });
      setRows((prev) => prev.filter((_, i) => i !== index));
    },
    [fieldKey, scholarships],
  );

  // ---------- calculation helpers ----------
  const parseNumeric = useCallback(
    (v) => parseFloat(String(v || '').replace(/[\s,$]/g, '')) || 0,
    [],
  );

  const calcTotalFor = useCallback(
    (fieldKeyName) => {
      const sumFromRows = [...scholarships, ...grants].reduce((acc, row) => {
        return acc + parseNumeric(row[fieldKeyName]);
      }, 0);

      const extraFieldsMap = {
        asu: ['booksAsu', 'transportAsu', 'otherAsu'],
        school2: ['booksSchool2', 'transportSchool2', 'otherSchool2'],
        school3: ['booksSchool3', 'transportSchool3', 'otherSchool3'],
      };

      const extraSum = (extraFieldsMap[fieldKeyName] || []).reduce(
        (acc, key) => {
          return acc + parseNumeric(extra[key]);
        },
        0,
      );

      return sumFromRows + extraSum;
    },
    [scholarships, grants, extra, parseNumeric],
  );

  const loanCalcTotal = useCallback(
    (loanFieldKey) => {
      const loansTotalFields = {
        asu: ['subloansAsu', 'unsubloansAsu', 'pplusloansAsu'],
        school2: ['subloansSchool2', 'unsubloansSchool2', 'pplusloansSchool2'],
        school3: ['subloansSchool3', 'unsubloansSchool3', 'pplusloansSchool3'],
      };

      return (loansTotalFields[loanFieldKey] || []).reduce((acc, key) => {
        return acc + parseNumeric(loansRow[key]);
      }, 0);
    },
    [loansRow, parseNumeric],
  );

  const formatMoney = useCallback((value) => {
    if (value === '' || value === null || typeof value === 'undefined')
      return '';
    const num = Number(value);
    if (Number.isNaN(num)) return '';
    return num.toLocaleString('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
    });
  }, []);

  // computed totals (memoized)
  const totals = useMemo(
    () => ({
      asu: calcTotalFor('asu'),
      school2: calcTotalFor('school2'),
      school3: calcTotalFor('school3'),
    }),
    [calcTotalFor],
  );

  const loanTotals = useMemo(
    () => ({
      asu: loanCalcTotal('asu'),
      school2: loanCalcTotal('school2'),
      school3: loanCalcTotal('school3'),
    }),
    [loanCalcTotal],
  );

  const aidsTotals = useMemo(
    () => ({
      asu: totals.asu,
      school2: totals.school2,
      school3: totals.school3,
    }),
    [totals],
  );

  const netPrices = useMemo(
    () => ({
      asu: (totalCosts.asu || 0) - totals.asu,
      school2: (totalCosts.school2 || 0) - totals.school2,
      school3: (totalCosts.school3 || 0) - totals.school3,
    }),
    [totalCosts, totals],
  );

  const remainingCosts = useMemo(
    () => ({
      asu: netPrices.asu - loanTotals.asu,
      school2: netPrices.school2 - loanTotals.school2,
      school3: netPrices.school3 - loanTotals.school3,
    }),
    [netPrices, loanTotals],
  );

  const highestPriceSchool = useMemo(() => {
    if (!netPrices) return null;

    const asu = Number(netPrices.asu ?? 0);
    const s2 = Number(netPrices.school2 ?? 0);
    const s3 = Number(netPrices.school3 ?? 0);
    const threshold = 1500;

    const others = [
      { key: 'asuMore', val: s2 },
      { key: 'asu', val: s3 },
    ].filter((o) => o.val > 0);

    if (others.length === 0) return 'asu';

    const comparisons = others.map((o) => {
      const diff = o.val - asu;
      if (diff > threshold) return 'asu';
      if (-diff > threshold) return 'asuMore';
      return 'within';
    });

    const allOthersMore = comparisons.every((c) => c === 'asu');
    const anyAsuMore = comparisons.some((c) => c === 'asuMore');
    const allWithin = comparisons.every((c) => c === 'within');

    if (allOthersMore) return 'asu';
    if (anyAsuMore) return 'asuMore';
    if (allWithin) return 'within';

    // Mixed case:
    // one school within threshold, another slightly higher/lower,
    // but no one exceeds the threshold decisively
    return 'asu';
  }, [netPrices]);
  //console.log(highestPriceSchool,'highestPriceSchool');
  const highestPriceSchoolText = highestPriceSchool
    ? customTextBySchool[highestPriceSchool]
    : '';

  // notify parent on changes but only when payload actually changes
  const lastPayloadJson = useRef(null);
  const aidPayload = useMemo(
    () => ({
      scholarships,
      grants,
      aidsTotals,
      loansRow,
      totals,
      loanTotals,
      netPrices,
      remainingCosts,
      highestPriceSchool,
    }),
    [
      scholarships,
      grants,
      aidsTotals,
      loansRow,
      totals,
      loanTotals,
      netPrices,
      remainingCosts,
      highestPriceSchool,
    ],
  );

  useEffect(() => {
    if (typeof onAidChange !== 'function') return;
    // cheap stringify diff (payloads are small)
    try {
      const json = JSON.stringify(aidPayload);
      if (lastPayloadJson.current !== json) {
        lastPayloadJson.current = json;
        onAidChange(aidPayload);
      }
    } catch (e) {
      // fallback: always call if stringify fails
      onAidChange(aidPayload);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [aidPayload, onAidChange]);

  useEffect(() => {
    if (typeof onValidationChange === 'function') {
      onValidationChange(hasErrors);
    }
  }, [hasErrors, onValidationChange]);

  // ---------- render helpers ----------
  const renderSectionRows = useCallback(
    (title, rows, setRows, sectionName) => (
      <>
        {rows.map((row, index) => {
          const asuKey = fieldKey({
            section: sectionName,
            rowId: row.id,
            field: 'asu',
          });
          const school2Key = fieldKey({
            section: sectionName,
            rowId: row.id,
            field: 'school2',
          });
          const school3Key = fieldKey({
            section: sectionName,
            rowId: row.id,
            field: 'school3',
          });

          // stable labels (fallback to labels prop if school2Name / school3Name are missing)
          const rowLabel = title.slice(0, -1); // "Scholarships" -> "Scholarship"
          const s1Label = 'Arizona State University'; // keep static or get from props if you have it
          const s2Label = school2Name || labels.school2 || 'School 2';
          const s3Label = school3Name || labels.school3 || 'School 3';
          return (
            <tr
              key={row.id}
              className={`webform-table-row ${index % 2 === 0 ? 'even' : 'odd'}`}
            >
              <td data-label={rowLabel} role="cell">
                <div className="label-cell">
                  <span className="row-label">
                    <button
                      type="button"
                      className="scholar-grant-btn-add"
                      onClick={() =>
                        index === 0
                          ? addRow(rows, setRows)
                          : removeRow(rows, setRows, index)
                      }
                      aria-label={
                        index === 0
                          ? `Add ${title.slice(0, -1)}`
                          : `Remove ${title.slice(0, -1)}`
                      }
                    >
                      {title.slice(0, -1)}{' '}
                      <span
                        aria-hidden="true"
                        style={{
                          fontSize: '1.4rem',
                          fontWeight: 'bold',
                          lineHeight: 1,
                        }}
                      >
                        {index === 0 ? '+' : '−'}
                      </span>
                    </button>
                    <Tooltip
                      id={`tooltip-${title}`}
                      label={rowLabel}
                      content={scholarGrantToolTips[title] || ''}
                    />
                  </span>
                </div>
              </td>

              <td data-label={`${s1Label} - ${title}`} role="cell">
                <input
                  type="text"
                  id={`${row.id}-asu`}
                  name={`name-${row.id}-asu`}
                  inputMode="numeric"
                  className={`form-textfield form-control ${errors[asuKey] ? 'input-error' : ''}`}
                  placeholder={placeHolder}
                  aria-label={`${rowLabel} for ${s1Label}`}
                  value={row.asu}
                  onChange={(e) =>
                    updateRowField(
                      rows,
                      setRows,
                      index,
                      'asu',
                      e.target.value,
                      sectionName,
                    )
                  }
                />
                {errors[asuKey] && (
                  <div className="error" role="alert">
                    {errors[asuKey]}
                  </div>
                )}
              </td>

              <td data-label={`${s2Label} - ${title}`} role="cell">
                <input
                  type="text"
                  inputMode="numeric"
                  id={`${row.id}-school2`}
                  name={`name-${row.id}-school2`}
                  className={`form-textfield form-control ${errors[school2Key] ? 'input-error' : ''}`}
                  placeholder={placeHolder}
                  value={row.school2}
                  aria-label={`${rowLabel} for ${s2Label}`}
                  onChange={(e) =>
                    updateRowField(
                      rows,
                      setRows,
                      index,
                      'school2',
                      e.target.value,
                      sectionName,
                    )
                  }
                />
                {errors[school2Key] && (
                  <div className="error" role="alert">
                    {errors[school2Key]}
                  </div>
                )}
              </td>

              <td data-label={`${s3Label} - ${title}`} role="cell">
                <input
                  type="text"
                  inputMode="numeric"
                  id={`${row.id}-school3`}
                  name={`name-${row.id}-school3`}
                  className={`form-textfield form-control ${errors[school3Key] ? 'input-error' : ''}`}
                  placeholder={placeHolder}
                  value={row.school3}
                  aria-label={`${rowLabel} for ${s3Label}`}
                  onChange={(e) =>
                    updateRowField(
                      rows,
                      setRows,
                      index,
                      'school3',
                      e.target.value,
                      sectionName,
                    )
                  }
                />
                {errors[school3Key] && (
                  <div className="error" role="alert">
                    {errors[school3Key]}
                  </div>
                )}
              </td>
            </tr>
          );
        })}
      </>
    ),
    [addRow, removeRow, updateRowField, errors, fieldKey],
  );

  const renderLoanRow = useCallback(
    (base, label) => {
      const asuKey = fieldKey({ section: 'loans', field: `${base}Asu` });
      const s2Key = fieldKey({ section: 'loans', field: `${base}School2` });
      const s3Key = fieldKey({ section: 'loans', field: `${base}School3` });

      return (
        <tr key={base}>
          <td data-label={label} role="cell">
            <div className="label-cell">
              <span className="row-label">{label}</span>
              <Tooltip
                id={`tooltip-${base}`}
                label={label}
                content={loansRow[`${base}Help`] || ''}
              />
            </div>
          </td>

          <td data-label={`Arizona State University - ${label}`} role="cell">
            <input
              type="text"
              id={`${base}-asu`}
              name={`name-${base}-asu`}
              className={`form-textfield form-control ${errors[asuKey] ? 'input-error' : ''}`}
              placeholder={placeHolder}
              value={loansRow[`${base}Asu`]}
              aria-label={`${label} for Arizona State University`}
              onChange={(e) => setLoanField(base, 'asu', e.target.value)}
            />
            {errors[asuKey] && (
              <div className="error" role="alert">
                {errors[asuKey]}
              </div>
            )}
          </td>

          <td data-label={`${school2Name} - ${label}`} role="cell">
            <input
              type="text"
              id={`${base}-school2`}
              name={`name-${base}-school2`}
              className={`form-textfield form-control ${errors[s2Key] ? 'input-error' : ''}`}
              placeholder={placeHolder}
              value={loansRow[`${base}School2`]}
              aria-label={`${label} for ${school2Name || 'School 2'}`}
              onChange={(e) => setLoanField(base, 'school2', e.target.value)}
            />
            {errors[s2Key] && (
              <div className="error" role="alert">
                {errors[s2Key]}
              </div>
            )}
          </td>

          <td data-label={`${school3Name} - ${label}`} role="cell">
            <input
              type="text"
              id={`${base}-school3`}
              name={`name-${base}-school3`}
              className={`form-textfield form-control ${errors[s3Key] ? 'input-error' : ''}`}
              placeholder={placeHolder}
              value={loansRow[`${base}School3`]}
              aria-label={`${label} for ${school3Name || 'School 3'}`}
              onChange={(e) => setLoanField(base, 'school3', e.target.value)}
            />
            {errors[s3Key] && (
              <div className="error" role="alert">
                {errors[s3Key]}
              </div>
            )}
          </td>
        </tr>
      );
    },
    [errors, loansRow, fieldKey, setLoanField],
  );

  // ---------- component render ----------
  return (
    <div className="table-wrapper financialTableDiv pt-4">
      <table className="table-responsive uds-table table responsive-enabled react-table aid-table">
        <thead>
          <tr className="blackTableHeader">
            <th scope="colgroup" colSpan="4" class="financeHeaderText">
              Financial aid offer
            </th>
          </tr>

          <tr
            className="gold-table-header"
            style={{ backgroundColor: '#FFD700' }}
          >
            <th scope="col">Gift aid</th>
            <th scope="col">Arizona State University</th>
            <th scope="col">
              <span className="visually-hidden">
                {school2Name ? `${school2Name}` : 'School 2 name'}
              </span>
              {school2Name}
            </th>
            <th scope="col">
              <span className="visually-hidden">
                {school3Name ? `${school3Name}` : 'School 3 name'}
              </span>
              {school3Name}
            </th>
            {/*  <th scope="col">{school2Name}</th>
            <th scope="col">{school3Name}</th> */}
          </tr>
        </thead>

        <tbody>
          {renderSectionRows(
            'Scholarships',
            scholarships,
            setScholarships,
            'Scholarships',
          )}
          {renderSectionRows('Grants', grants, setGrants, 'Grants')}
        </tbody>
      </table>

      {/* Loans table */}
      <table className="table-responsive uds-table loans-table responsive-enabled react-table table ">
        <thead
          className="gold-table-header"
          style={{ backgroundColor: '#FFD700' }}
        >
          <tr>
            <th scope="col">Loans</th>
            <th scope="col">Arizona State University</th>
            <th scope="col">
              <span className="visually-hidden">
                {school2Name ? `${school2Name}` : 'School 2 name'}
              </span>
              {school2Name}
            </th>
            <th scope="col">
              <span className="visually-hidden">
                {school3Name ? `${school3Name}` : 'School 3 name'}
              </span>
              {school3Name}
            </th>
          </tr>
        </thead>

        <tbody>
          {renderLoanRow('subloans', 'Subsidized loan')}
          {renderLoanRow('unsubloans', 'Unsubsidized loan')}
          {renderLoanRow('pplusloans', 'Parent PLUS loan')}
        </tbody>
      </table>

      {/* Totals table (unchanged layout) */}
      <table className="table-responsive uds-table total-calc-tables table responsive-enabled react-table mt-3">
        <thead className="hide-header">
          <tr>
            <th scope="col" span class="visually-hidden">
              Your loans
            </th>
            <th scope="col">
              <span class="visually-hidden">Arizona State University</span>
            </th>
            <th scope="col">
              <span className="visually-hidden">
                {school2Name ? `${school2Name}` : 'School 2 name'}
              </span>
              {school2Name}
            </th>
            <th scope="col">
              <span className="visually-hidden">
                {school3Name ? `${school3Name}` : 'School 3 name'}
              </span>
              {school3Name}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr
            className="total-row annual-costs highlight-black"
            style={{ backgroundColor: '#000' }}
          >
            <td className="label-td" data-label="Total annual costs">
              <div className="label-cell">
                <div className="total_headText">
                  <span className="row-label">Total annual costs</span>
                </div>
              </div>
            </td>
            <td data-label={`Arizona State University - Total annual costs`}>
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control annual-form-control total-input"
                  value={showTotals ? formatMoney(totalCosts.asu) : ''}
                  readOnly
                  tabIndex={showTotals ? 0 : -1}
                  aria-label={`Total annual costs for Arizona State University`}
                />
              </div>
            </td>
            <td
              data-label={
                school2Name
                  ? `${school2Name} - Total annual costs`
                  : 'School 2 - Total annual costs'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control annual-form-control total-input"
                  value={showTotals ? formatMoney(totalCosts.school2) : ''}
                  readOnly
                  tabIndex={showTotals && school2 ? 0 : -1}
                  aria-label={`Total annual costs for ${school2Name || 'School 2'}`}
                />
              </div>
            </td>
            <td
              data-label={
                school3Name
                  ? `${school3Name} - Total annual costs`
                  : 'School 3 - Total annual costs'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control annual-form-control total-input"
                  value={showTotals ? formatMoney(totalCosts.school3) : ''}
                  readOnly
                  tabIndex={showTotals && school3 ? 0 : -1}
                  aria-label={`Total annual costs for ${school3Name || 'School 3'}`}
                />
              </div>
            </td>
          </tr>

          <tr className="total-row gift-aid-row gray-row uds-tooltip-bg-gray">
            <td className="label-td" data-label="Total gift aid">
              <div className="label-cell">
                <div className="total_headText">
                  <span className="row-label">Total gift aid</span>
                </div>
              </div>
            </td>
            <td data-label={`Arizona State University - Total gift aid`}>
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control gray-form-control total-input"
                  value={showTotals ? formatMoney(totals.asu) : ''}
                  readOnly
                  tabIndex={showTotals ? 0 : -1}
                  aria-label={`Total gift aid for Arizona State University`}
                />
              </div>
            </td>
            <td
              data-label={
                school2Name
                  ? `${school2Name} - Total gift aid`
                  : 'School 2 - Total gift aid'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control gray-form-control total-input"
                  value={showTotals ? formatMoney(totals.school2) : ''}
                  readOnly
                  tabIndex={showTotals && school2 ? 0 : -1}
                  aria-label={`Total gift aid for ${school2Name || 'School 2'}`}
                />
              </div>
            </td>
            <td
              data-label={
                school3Name
                  ? `${school3Name} - Total gift aid`
                  : 'School 3 - Total gift aid'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control gray-form-control total-input"
                  value={showTotals ? formatMoney(totals.school3) : ''}
                  readOnly
                  tabIndex={showTotals && school3 ? 0 : -1}
                  aria-label={`Total gift aid for ${school3Name || 'School 3'}`}
                />
              </div>
            </td>
          </tr>

          <tr className="total-row net-total maroon-row">
            <td className="label-td" data-label="Net price (cost after aid)">
              <div className="label-cell">
                <div className="total_headText">
                  <span className="row-label">Net price (cost after aid)</span>
                </div>
              </div>
            </td>

            <td data-label={`Arizona State University - Net price`}>
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control maroon-form-control"
                  value={showTotals ? formatMoney(netPrices.asu) : ''}
                  readOnly
                  tabIndex={showTotals ? 0 : -1}
                  aria-label={`Net price (cost after aid) for Arizona State University`}
                />
              </div>
            </td>
            <td
              data-label={
                school2Name
                  ? `${school2Name} - Net price`
                  : 'School 2 - Net price'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control maroon-form-control"
                  value={showTotals ? formatMoney(netPrices.school2) : ''}
                  readOnly
                  tabIndex={showTotals && school2 ? 0 : -1}
                  aria-label={`Net price (cost after aid) for ${school2Name || 'School 2'}`}
                />
              </div>
            </td>
            <td
              data-label={
                school3Name
                  ? `${school3Name} - Net price`
                  : 'School 3 - Net price'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control maroon-form-control"
                  value={showTotals ? formatMoney(netPrices.school3) : ''}
                  readOnly
                  tabIndex={showTotals && school3 ? 0 : -1}
                  aria-label={`Net price (cost after aid) for ${school3Name || 'School 3'}`}
                />
              </div>
            </td>
          </tr>

          <tr className="total-row loans-total gray-row uds-tooltip-bg-gray">
            <td className="label-td">
              <div className="label-cell" data-label="Total loans offered">
                <div className="total_headText">
                  <span className="row-label">Total loans offered</span>
                </div>
              </div>
            </td>
            <td data-label={`Arizona State University - Total loans offered`}>
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control gray-form-control"
                  value={showTotals ? formatMoney(loanTotals.asu) : ''}
                  readOnly
                  tabIndex={showTotals ? 0 : -1}
                  aria-label={`Total loans offered for Arizona State University`}
                />
              </div>
            </td>
            <td
              data-label={
                school2Name
                  ? `${school2Name} - Total loans offered`
                  : 'School 2 - Total loans offered'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control gray-form-control"
                  value={showTotals ? formatMoney(loanTotals.school2) : ''}
                  readOnly
                  tabIndex={showTotals && school2 ? 0 : -1}
                  aria-label={`Total loans offered for ${school2Name || 'School 2'}`}
                />
              </div>
            </td>
            <td
              data-label={
                school3Name
                  ? `${school3Name} - Total loans offered`
                  : 'School 3 - Total loans offered'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control gray-form-control"
                  value={showTotals ? formatMoney(loanTotals.school3) : ''}
                  readOnly
                  tabIndex={showTotals && school3 ? 0 : -1}
                  aria-label={`Total loans offered for ${school3Name || 'School 3'}`}
                />
              </div>
            </td>
          </tr>

          <tr className="total-row remaining-total highlight-black">
            <td className="label-td" data-label="Remaining Costs">
              <div className="total_headText">
                <div className="label-cell">
                  <span className="row-label">Remaining Costs</span>
                </div>
              </div>
            </td>
            <td data-label={`Arizona State University - Remaining Costs`}>
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control annual-form-control"
                  value={showTotals ? formatMoney(remainingCosts.asu) : ''}
                  readOnly
                  tabIndex={showTotals ? 0 : -1}
                  aria-label={`Remaining Costs for Arizona State University`}
                />
              </div>
            </td>
            <td
              data-label={
                school2Name
                  ? `${school2Name} - Remaining Costs`
                  : 'School 2 - Remaining Costs'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control annual-form-control"
                  value={showTotals ? formatMoney(remainingCosts.school2) : ''}
                  readOnly
                  tabIndex={showTotals && school2 ? 0 : -1}
                  aria-label={`Remaining Costs for ${school2Name || 'School 2'}`}
                />
              </div>
            </td>
            <td
              data-label={
                school3Name
                  ? `${school3Name} - Remaining Costs`
                  : 'School 3 - Remaining Costs'
              }
            >
              <div className="total_headText">
                <input
                  type="text"
                  className="form-textfield form-control total-form-control annual-form-control"
                  value={showTotals ? formatMoney(remainingCosts.school3) : ''}
                  readOnly
                  tabIndex={showTotals && school3 ? 0 : -1}
                  aria-label={`Remaining Costs for ${school3Name || 'School 3'}`}
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div>
        {showTotals ? (
          <>
            <p>
              <div
                className="pt-5 custom-bottom-text"
                style={{ backgroundColor: '#FFfff' }}
                dangerouslySetInnerHTML={{ __html: highestPriceSchoolText }}
              />
            </p>
          </>
        ) : null}
      </div>
    </div>
  );
}
