import React, { useState, useEffect, useRef, useMemo } from 'react'; // CHANGED: added useMemo
import TourChips from './TourChips';

function GoldFilterIndicator({ appliedFilters, clearAllFilters }) {
  const [open, setOpen] = useState(false);
  const popoverRef = useRef(null);

  // Compute a filter count
  const totalCount = useMemo(() => {
    return (appliedFilters || []).reduce((sum, item) => {
      if (item?.type === 'tours') {
        const arr = Array.isArray(item.selected) ? item.selected : [];
        return sum + arr.length; // each selected tour = 1 filter
      }
      return sum + 1;
    }, 0);
  }, [appliedFilters]);

  // Close popover when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (popoverRef.current && !popoverRef.current.contains(event.target)) {
        setOpen(false);
      }
    }
    if (open) {
      document.addEventListener('mousedown', handleClickOutside);
    } else {
      document.removeEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [open]);

  return (
    <div className="filters-bar">
      {/* Gold pill + popover */}
      <div className="position-relative filters-anchor" ref={popoverRef}>
        <button
          type="button"
          className="btn btn-gold rounded-pill d-inline-flex align-items-center"
          onClick={() => setOpen(v => !v)}
          aria-expanded={open}
          aria-controls="filters-popover"
        >
          {/* Use totalCount and “selected” */}
          {totalCount} {totalCount === 1 ? 'filter' : 'filters'} selected
          <i className={`ms-2 fas ${open ? 'fa-chevron-up' : 'fa-chevron-down'}`}/>
        </button>

        {open && (
          <div id="filters-popover" className="filters-popover shadow">
            {totalCount === 0 ? ( /* use totalCount */
              <div className="p-2 text-muted">No filters selected yet.</div>
            ) : (
              <ul className="chip-list" role="list">
                {appliedFilters.map(item => {
                  // Render Tours
                  if (item.type === 'tours') {
                    return (
                      <li key="tours" className="chip-group" aria-label="Tours selected">
                        <TourChips
                          selected={item.selected /* ['inperson', ...] or [{key,label}] */}
                          onRemove={(k) => item.onClearOne?.(k)} /* remove individual tour */
                        />
                      </li>
                    );
                  }

                  // Default single-chip rendering for all other filters
                  return (
                    <li key={item.key} className="chip" aria-label={item.label}>
                      <span className="chip__label">{item.label}</span>
                      <button
                        type="button"
                        className="chip__close"
                        aria-label={`Remove ${item.label}`}
                        onClick={() => {
                          item.onClear();
                          setOpen(false);
                        }}
                        title="Remove"
                      >
                        ×
                      </button>
                    </li>
                  );
                })}
              </ul>
            )}
          </div>
        )}
      </div>

      {/* Clear all aligned to the right */}
      <button
        type="button"
        className="btn btn-outline-secondary btn-sm fixed-outline clear-inline"
        onClick={() => { clearAllFilters(); setOpen(false); }}
        disabled={totalCount === 0}
      >
        Clear all
      </button>

    </div>
  );
}

export default GoldFilterIndicator;
