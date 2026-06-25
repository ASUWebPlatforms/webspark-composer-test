import React from 'react';

// Map internal keys to human-readable labels
const TOUR_LABELS = {
  inperson: 'In-person walking tour',
  'inperson-academic': 'In-person walking tour with academic fair',
  facility: 'Academic Facility tour',
  selfguided: 'Self-guided tour',
  barrett: 'Barrett Honors info session',
  generic: 'Campus experience',
  classroom: 'Classroom visit',
};

const TourChips = ({ selected = [], onRemove }) => {
  // Normalize: accept ['inperson', ...] OR [{key:'inperson', label:'...'}, ...]
  const items = (selected || [])
    .map((it) =>
      typeof it === 'string'
        ? { key: it, label: TOUR_LABELS[it] || it }
        : { key: it.key, label: it.label || TOUR_LABELS[it.key] || it.key },
    )
    .filter((it) => it?.key && it?.label);

  if (items.length === 0) return null;

  return (
    <div className="d-flex flex-wrap gap-2">
      {items.map(({ key, label }) => (
        <span key={key} className="chip chip--sm">
          <span className="chip__label">{label}</span>
          <button
            type="button"
            className="chip__close"
            aria-label={`Remove ${label}`}
            onClick={() => onRemove?.(key)}
            title="Remove"
          >
            ×
          </button>
        </span>
      ))}
    </div>
  );
};

export default TourChips;
