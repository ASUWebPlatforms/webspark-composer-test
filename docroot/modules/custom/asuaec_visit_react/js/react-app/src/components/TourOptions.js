import React from 'react';
import ToursToolTip from './ToursToolTip';

function TourOptions({
  availableTourFilters,
  selectedTourFilters,
  onFilterChange,
}) {
  const handleCheckboxChange = (id) => {
    if (selectedTourFilters.includes(id)) {
      onFilterChange(selectedTourFilters.filter((f) => f !== id));
    } else {
      onFilterChange([...selectedTourFilters, id]);
    }
  };

  const labelMap = {
    inperson: 'In-person walking tour without academic fair',
    selfguided: 'Self-guided tour',
    'inperson-academic': 'In-person walking tour with academic fair',
    barrett: 'Barrett, The Honors College',
    facility: 'Academic facility tour',
    generic: 'Signature event',
    classroom: 'Classroom visit',
  };

  if (!availableTourFilters || availableTourFilters.length === 0) {
    return null;
  }

  const sortedTourFilters = [...availableTourFilters].sort((a, b) =>
    (labelMap[a] || a).localeCompare(labelMap[b] || b),
  );

  return (
    <div className="tour-options">
      <fieldset className="mb-2">
        {sortedTourFilters.map((id) => {
          const safeId = `tourfilter-${String(id)
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-_]/g, '')}`;
          const labelText = labelMap[id] || id;

          return (
            <div
              key={id}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.5em',
                marginBottom: '0.5em',
              }}
            >
              <div className="form-check" style={{ marginBottom: 0 }}>
                <input
                  className="form-check-input"
                  type="checkbox"
                  id={safeId}
                  checked={selectedTourFilters.includes(id)}
                  onChange={() => handleCheckboxChange(id)}
                />
                <label className="form-check-label" htmlFor={safeId}>
                  {labelText}
                </label>
              </div>
              <ToursToolTip label={labelText} /> {/* Add Tool tip */}
            </div>
          );
        })}
      </fieldset>
    </div>
  );
}

export default TourOptions;
