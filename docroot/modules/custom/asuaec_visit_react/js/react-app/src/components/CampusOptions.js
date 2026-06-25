// Convert campus keys like losan, tempe, west into display labels.
// Support default_campus.
// Support lock_campus.
// Filter campuses when allowed_campuses is not "all".
// The available campus options still come from this endpoint: /admin/asuaec_json/json/campus/${level}/${selectedAreaOfInterest}.

import React, { useEffect, useLayoutEffect, useRef, useState } from 'react';
import CampusDescription from './CampusDescription';

function CampusOptions({
  selectedStudentType,
  selectedAreaOfInterest,
  onCampusChange,
  onReady,
  selectedCampus: selectedCampusFromParent, // value from parent
  // 5/12/2026 - added master/allowed campuses and default/lock config for preset support
  masterCampuses = {},
  allowedCampuses = 'all',
  defaultCampus = '',
  lockCampus = false,
}) {
  const containerRef = useRef(null);

  const [options, setOptions] = useState([]);
  const [selectedCampus, setSelectedCampus] = useState('');

  // Added on 5/12/2026 - handle default campus selection and lock config for presets
  const CAMPUS_KEY_BY_LABEL = {
    'asu california center in downtown la': 'losan',
    'asu california center in downtown l.a.': 'losan',
    'downtown phoenix': 'downtown_phx',
    polytechnic: 'poly',
    tempe: 'tempe',
    west: 'west',
    'west valley': 'west',
  };

  useEffect(() => {
    if (lockCampus && defaultCampus) {
      const displayName = masterCampuses[defaultCampus] || '';
      if (displayName) {
        setSelectedCampus(displayName);
        onCampusChange?.(displayName);
      }
    }
  }, [lockCampus, defaultCampus, masterCampuses, onCampusChange]);

  // Keep local radio selection in sync with parent (campusFilter)
  useEffect(() => {
    if (selectedCampusFromParent) {
      setSelectedCampus(selectedCampusFromParent);
    } else {
      // If parent clears campusFilter, clear the radios too
      setSelectedCampus('');
    }
  }, [selectedCampusFromParent]);

  // Tell parent only when radios are actually mounted
  useLayoutEffect(() => {
    if (containerRef.current) {
      const radios = containerRef.current.querySelectorAll(
        'input[type="radio"]',
      );
      if (radios.length > 0) {
        const id = requestAnimationFrame(() => {
          onReady && onReady();
        });
        return () => cancelAnimationFrame(id);
      }
    }
  }, [options, onReady]);

  const isUndergrad =
    selectedStudentType &&
    selectedStudentType !== 'considering graduate school' &&
    selectedStudentType !== 'a high school counselor';

  const CAMPUS_TID_BY_LABEL = {
    'ASU California Center in downtown LA': '74',
    'ASU California Center in downtown L.A.': '74', // variant from API
    'Downtown Phoenix': '18',
    Polytechnic: '19',
    Tempe: '20',
    West: '21',
    'West Valley': '21', // if this shows as a label anywhere
  };

  const normalizeCampusLabel = (label) => {
    if (!label) return '';
    let s = String(label).trim();

    // Many API values include "xxxx campus" in the value or HTML. Remove it for lookup.
    // e.g., "Tempe campus" -> "Tempe"
    s = s.replace(/\s+campus$/i, '');

    // HTML may contain periods like "L.A." vs "LA". Support both via map entries above.
    // Also collapse multiple spaces.
    s = s.replace(/\s+/g, ' ').trim();

    return s;
  };

  useEffect(() => {
    const fetchCampuses = async () => {
      if (selectedStudentType === 'Other') {
        setOptions([
          {
            id: 'asu-la',
            name: 'ASU California Center in downtown LA',
            tid: '74',
          },
          { id: 'downtown-phx', name: 'Downtown Phoenix', tid: '18' },
          { id: 'poly', name: 'Polytechnic', tid: '19' },
          { id: 'tempe', name: 'Tempe', tid: '20' },
          { id: 'west-valley', name: 'West', tid: '21' },
        ]);
        return;
      }

      // console.log("selectedStudentType:", selectedStudentType);
      // console.log("selectedAreaOfInterest:", selectedAreaOfInterest);

      if (!selectedStudentType || !selectedAreaOfInterest) {
        setOptions([]);
        return;
      }

      try {
        const level =
          selectedStudentType === 'Graduate student' ? 'grad' : 'ugrad';
        //console.log("level:", level);
        //console.log("selectedAreaOfInterest:", selectedAreaOfInterest);
        const response = await fetch(
          `/admin/asuaec_json/json/campus/${level}/${selectedAreaOfInterest}`,
        ); // For example: https://visit-asu-csdev60.ddev.site/admin/asuaec_json/json/campus/grad/arts
        const data = await response.json();

        // Changed on 5/12/2026 to support master/allowed campus config and more robust label normalization for TID lookup
        const allowedCampusKeys =
          allowedCampuses === 'all'
            ? Object.keys(masterCampuses)
            : Array.isArray(allowedCampuses)
              ? allowedCampuses
              : [];

        const optionsArray = Object.keys(data)
          .map((rawKey) => {
            const canonicalLabel = normalizeCampusLabel(rawKey);
            const canonicalLabelKey = canonicalLabel.toLowerCase();
            const campusKey = CAMPUS_KEY_BY_LABEL[canonicalLabelKey] || null;

            const displayName = masterCampuses[campusKey] || rawKey;
            const id = displayName
              .toLowerCase()
              .replace(/\s+/g, '-')
              .replace(/[^a-z0-9-_]/g, '');

            const isAllowedCampus =
              allowedCampuses === 'all' ||
              (campusKey && allowedCampusKeys.includes(campusKey));

            if (!isAllowedCampus) {
              return null;
            }

            return {
              id,
              name: displayName,
              tid:
                CAMPUS_TID_BY_LABEL[canonicalLabel] ??
                CAMPUS_TID_BY_LABEL[canonicalLabel.toLowerCase()] ??
                null,
              key: campusKey || displayName,
            };
          })
          .filter(Boolean);

        setOptions(optionsArray);
      } catch (err) {
        console.error('Error fetching campuses:', err);
        setOptions([]); // fail-safe
      }
    };

    fetchCampuses();
  }, [selectedStudentType, selectedAreaOfInterest]);

  const handleCampusChange = (campus) => {
    setSelectedCampus(campus);
    // Optionally notify CalendarPage.js via prop if needed
    if (onCampusChange) {
      onCampusChange(campus);
    }
  };

  if (options.length === 0) return null;

  return (
    <div ref={containerRef}>
      <fieldset className="mb-2">
        {options.map((option) => {
          const safeId = `campus-${(option.id || option.name)
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-_]/g, '')}`;
          const displayName =
            option.name === 'West'
              ? 'West Valley campus'
              : option.name.toLowerCase().endsWith('campus')
                ? option.name
                : option.name + ' campus';
          return (
            <div key={option.id} className="form-check">
              <input
                className="form-check-input"
                type="radio"
                name="campus"
                id={safeId}
                value={option.name}
                checked={selectedCampus === option.name}
                onChange={(e) => handleCampusChange(e.target.value)}
                // Added on 5/12/2026 to disable campus selection if lockCampus is true from preset config
                disabled={lockCampus}
              />
              <label className="form-check-label" htmlFor={safeId}>
                {displayName}
                <CampusDescription
                  enabled={isUndergrad && !!option.tid}
                  campusTid={option.tid} // pass TID from options
                  interestTid={selectedAreaOfInterest} // visit bucket TID
                />
              </label>
            </div>
          );
        })}
      </fieldset>
    </div>
  );
}

export default CampusOptions;
