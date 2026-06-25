import React from 'react';

/**
 * Tooltip is CSS-only: shows on hover or focus.
 * Usage: <Tooltip id="tooltip-01" label="Tuition" content="..." />
 */
export default function Tooltip({ id, label, content }) {
  return (
    <div className="uds-tooltip-container" aria-hidden="false">
      <button
        type="button"
        className="uds-tooltip uds-tooltip-black no-print"
        aria-describedby={id}
        aria-label={`${label} info`}
      >
        <span className="fa-stack" aria-hidden="true">
          <i className="fas fa-circle fa-stack-2x" />
          <i className="fas fa-info fa-stack-1x" />
        </span>
        <span className="visually-hidden">More info</span>
      </button>

      <div id={id} role="dialog" className="uds-tooltip-description">
        <span className='uds-tooltip-heading'><strong>{label}</strong></span>
        <div className='formatted-text' dangerouslySetInnerHTML={{ __html: content }}/>
      </div>
    </div>
  );
}
