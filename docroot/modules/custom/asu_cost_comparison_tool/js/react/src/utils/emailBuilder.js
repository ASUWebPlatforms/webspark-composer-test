// src/utils/emailBuilder.js
// Lightweight email HTML builder. Exported functions:
// - buildEmailHtml(payload) -> string (HTML body)
// - formatCurrencyRaw(n) -> string
// - escapeHtml(s) -> string

export function escapeHtml(s) {
  if (s === null || s === undefined) return '';
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

export function formatCurrencyRaw(n) {
  if (n === null || n === undefined || n === '') return '';
  const num = Number(String(n).replace(/[\s,$]/g, ''));
  if (Number.isNaN(num)) return escapeHtml(String(n));
  return num.toLocaleString(undefined, { style: 'currency', currency: 'USD', minimumFractionDigits: 0 });
}

/**
 * buildEmailHtml(payload)
 * payload can be:
 *  - { data: { costs, aid, totals, labels, resident, campus, timestamp } }
 *  - or the raw data object directly (backwards-compatible)
 */
export function buildEmailHtml(payloadOrData) {
  const wrapper = payloadOrData && payloadOrData.data ? payloadOrData.data : payloadOrData || {};
  const costs = wrapper.costs || {};
  const aid = wrapper.aid || {};
  const totals = wrapper.totals || {};
  const labels = wrapper.labels || {};
  const resident = wrapper.resident || '';
  const campus = wrapper.campus || '';
  const ts = wrapper.timestamp || new Date().toISOString();

  // row order / labels — mirror your INITIAL_ROWS or pass rows in payload
  const rowsOrder = [
    { id: '01', label: 'Tuition & fees' },
    { id: '02', label: 'Books & supplies' },
    { id: '03', label: 'Housing' },
    { id: '04', label: 'Transportation' },
  ];

  const headerAsu = escapeHtml(labels.asu || 'Arizona State University');
  const headerS2 = escapeHtml(labels.school2 || 'School 2');
  const headerS3 = escapeHtml(labels.school3 || 'School 3');

  let costsRowsHtml = rowsOrder.map(r => {
    const v = costs[r.id] || {};
    const asu = formatCurrencyRaw(v.asu);
    const s2 = formatCurrencyRaw(v.school2);
    const s3 = formatCurrencyRaw(v.school3);
    return `<tr>
      <td style="padding:6px;border:1px solid #ddd">${escapeHtml(r.label)}</td>
      <td style="padding:6px;border:1px solid #ddd;text-align:right">${asu}</td>
      <td style="padding:6px;border:1px solid #ddd;text-align:right">${s2}</td>
      <td style="padding:6px;border:1px solid #ddd;text-align:right">${s3}</td>
    </tr>`;
  }).join('');

  costsRowsHtml += `<tr style="font-weight:700;background:#f7f7f7">
    <td style="padding:6px;border:1px solid #ddd">Total</td>
    <td style="padding:6px;border:1px solid #ddd;text-align:right">${formatCurrencyRaw(totals.asu)}</td>
    <td style="padding:6px;border:1px solid #ddd;text-align:right">${formatCurrencyRaw(totals.school2)}</td>
    <td style="padding:6px;border:1px solid #ddd;text-align:right">${formatCurrencyRaw(totals.school3)}</td>
  </tr>`;

  const costsTable = `
    <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:700px;margin-bottom:12px">
      <thead>
        <tr style="background:#222;color:#fff">
          <th style="padding:8px;border:1px solid #222;text-align:left">Item</th>
          <th style="padding:8px;border:1px solid #222;text-align:right">${headerAsu}</th>
          <th style="padding:8px;border:1px solid #222;text-align:right">${headerS2}</th>
          <th style="padding:8px;border:1px solid #222;text-align:right">${headerS3}</th>
        </tr>
      </thead>
      <tbody>
        ${costsRowsHtml}
      </tbody>
    </table>
  `;

  const renderAidList = (list = [], title) => {
    if (!Array.isArray(list) || list.length === 0) return `<p><strong>${escapeHtml(title)}:</strong> None</p>`;
    const rows = list.map(r => {
      const label = escapeHtml(r.name || title);
      const asu = formatCurrencyRaw(r.asu);
      const s2 = formatCurrencyRaw(r.school2);
      const s3 = formatCurrencyRaw(r.school3);
      return `<tr>
        <td style="padding:6px;border:1px solid #eee">${label}</td>
        <td style="padding:6px;border:1px solid #eee;text-align:right">${asu}</td>
        <td style="padding:6px;border:1px solid #eee;text-align:right">${s2}</td>
        <td style="padding:6px;border:1px solid #eee;text-align:right">${s3}</td>
      </tr>`;
    }).join('');
    return `<div style="margin-top:8px">
      <div style="font-weight:700;margin-bottom:6px">${escapeHtml(title)}</div>
      <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:700px;margin-bottom:12px">
        <thead>
          <tr style="background:#efefef">
            <th style="padding:6px;border:1px solid #ddd;text-align:left">Source</th>
            <th style="padding:6px;border:1px solid #ddd;text-align:right">${headerAsu}</th>
            <th style="padding:6px;border:1px solid #ddd;text-align:right">${headerS2}</th>
            <th style="padding:6px;border:1px solid #ddd;text-align:right">${headerS3}</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>`;
  };

  const scholarshipsHtml = renderAidList(aid.scholarships || [], 'Scholarships');
  const grantsHtml = renderAidList(aid.grants || [], 'Grants');

  const loanTotals = aid.loanTotals || {
    asu: (aid.loansRow && (Number(aid.loansRow.subloansAsu||0) + Number(aid.loansRow.unsubloansAsu||0) + Number(aid.loansRow.pplusloansAsu||0))) || 0,
    school2: (aid.loansRow && (Number(aid.loansRow.subloansSchool2||0) + Number(aid.loansRow.unsubloansSchool2||0) + Number(aid.loansRow.pplusloansSchool2||0))) || 0,
    school3: (aid.loansRow && (Number(aid.loansRow.subloansSchool3||0) + Number(aid.loansRow.unsubloansSchool3||0) + Number(aid.loansRow.pplusloansSchool3||0))) || 0,
  };

  const loansHtml = `
    <div style="margin-top:8px">
      <div style="font-weight:700;margin-bottom:6px">Loans</div>
      <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:700px;margin-bottom:12px">
        <thead>
          <tr style="background:#efefef">
            <th style="padding:6px;border:1px solid #ddd;text-align:left">Type</th>
            <th style="padding:6px;border:1px solid #ddd;text-align:right">${headerAsu}</th>
            <th style="padding:6px;border:1px solid #ddd;text-align:right">${headerS2}</th>
            <th style="padding:6px;border:1px solid #ddd;text-align:right">${headerS3}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:6px;border:1px solid #eee">Total loans offered</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(loanTotals.asu)}</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(loanTotals.school2)}</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(loanTotals.school3)}</td>
          </tr>
        </tbody>
      </table>
    </div>
  `;

  const netPrices = wrapper.aid.netPrices || {};
  const remaining = wrapper.aid.remainingCosts || {};
  const annualTotal = wrapper.totals || {}
  const totalsSummaryHtml = `
    <div style="margin-top:8px">
      <div style="font-weight:700;margin-bottom:6px">Summary</div>
      <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:700px;margin-bottom:12px">
        <tbody>
          <tr>
            <td style="padding:6px;border:1px solid #eee">Total annual cost (ASU)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(annualTotal.asu ?? '')}</td>
            <td style="padding:6px;border:1px solid #eee">Net price (ASU)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(netPrices.asu ?? '')}</td>
            <td style="padding:6px;border:1px solid #eee">Remaining (ASU)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(remaining.asu ?? '')}</td>
          </tr>
          <tr>
           <td style="padding:6px;border:1px solid #eee">Total annual cost (ASU)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(annualTotal.school2 ?? '')}</td>
            <td style="padding:6px;border:1px solid #eee">Net price (School 2)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(netPrices.school2 ?? '')}</td>
            <td style="padding:6px;border:1px solid #eee">Remaining (School 2)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(remaining.school2 ?? '')}</td>
          </tr>
          <tr>
            <td style="padding:6px;border:1px solid #eee">Total annual cost (ASU)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(annualTotal.school3 ?? '')}</td> 
            <td style="padding:6px;border:1px solid #eee">Net price (School 3)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(netPrices.school3 ?? '')}</td>
            <td style="padding:6px;border:1px solid #eee">Remaining (School 3)</td>
            <td style="padding:6px;border:1px solid #eee;text-align:right">${formatCurrencyRaw(remaining.school3 ?? '')}</td>
          </tr>
        </tbody>
      </table>
    </div>
  `;

  const metaHtml = `
    <div style="margin-top:8px;font-size:13px;color:#555">
      <div><strong>Residency:</strong> ${escapeHtml(resident)}</div>
      <div><strong>Campus:</strong> ${escapeHtml(campus)}</div>
      <div><strong>Snapshot:</strong> ${escapeHtml(ts)}</div>
    </div>
  `;

  const bodyHtml = `
    <div>
      <h2>Cost Comparison Snapshot</h2>
      ${costsTable}
      ${scholarshipsHtml}
      ${grantsHtml}
      ${loansHtml}
      ${totalsSummaryHtml}
      ${metaHtml}
    </div>
  `;

  return bodyHtml;
}
