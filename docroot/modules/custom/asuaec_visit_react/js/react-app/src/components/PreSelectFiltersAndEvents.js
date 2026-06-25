// PreSelectFiltersAndEvents.js
import { useEffect, useState } from 'react';

/**
 * Preselect filters and events based on sessionStorage.visitsRevamp.
 *
 * visitsRevamp example:
 * {
 *   "2026-01-12": {
 *     "Tempe": [
 *       { eventid: "786-1768233600", ... }
 *     ],
 *     "West campus": [
 *       { eventid: "790-1768237200", ... }
 *     ]
 *   },
 *   "2026-01-20": {
 *     "Tempe": [
 *       { eventid: "800-1768840000", ... }
 *     ]
 *   }
 * }
 *
 * NOTES:
 * - Support multiple campuses AND multiple dates.
 * - Preselect ALL matching events across ALL dates & campuses
 *   by scanning the full events array from CalendarPage.
 */

export function usePreselectFromVisitsRevamp({
  campusFilter,
  hasDateSelection,
  selectedItems,
  setCampusFilter,
  setSelectedDate,
  setHasDateSelection,
  setSelectedItems,
  setSelectedAreaOfInterest,
  allEvents, // full events array from CalendarPage
}) {
  // ---- 1) Read and parse visitsRevamp once on mount ----
  const [seed] = useState(() => {
    if (typeof window === 'undefined') return null;

    const raw = window.sessionStorage.getItem('visitsRevamp');
    if (!raw) return null;

    try {
      const data = JSON.parse(raw) || {};
      const dateKeys = Object.keys(data);
      if (!dateKeys.length) return null;

      // Sort dates and use the earliest as the "primary" date
      const sortedDateKeys = dateKeys.slice().sort(); // "2026-01-12", "2026-01-20", ...
      const primaryDate = sortedDateKeys[0];

      const primaryCampusObj = data[primaryDate];
      if (!primaryCampusObj || typeof primaryCampusObj !== 'object') return null;

      const primaryCampusKeys = Object.keys(primaryCampusObj);
      if (!primaryCampusKeys.length) return null;

      // If there is exactly ONE campus on the primary date,
      // remember its name for campusFilter. Otherwise leave blank.
      const campusKey =
        primaryCampusKeys.length === 1 ? primaryCampusKeys[0] : '';

      // Flatten ALL events from ALL dates + campuses
      const allSeedEvents = [];
      sortedDateKeys.forEach((dateKey) => {
        const campusObj = data[dateKey];
        if (!campusObj || typeof campusObj !== 'object') return;

        Object.keys(campusObj).forEach((campusName) => {
          const list = campusObj[campusName] || [];
          list.forEach((ev) => {
            allSeedEvents.push(ev);
          });
        });
      });

      if (!allSeedEvents.length) return null;

      return { date: primaryDate, campus: campusKey, events: allSeedEvents };
    } catch (e) {
      console.warn('[visit revamp] Failed to parse visitsRevamp from sessionStorage', e);
      return null;
    }
  });

  // ---- 2) Preselect campus + date + interest (only if calendar is currently "empty") ----
  useEffect(() => {
    if (!seed) return;

    // If the user already has selections (e.g., Back button restored state), do not override.
    const alreadyHasCampus = !!campusFilter;
    const alreadyHasDate = !!hasDateSelection;
    const alreadyHasChecks =
      selectedItems && Object.keys(selectedItems).length > 0;

    if (alreadyHasCampus || alreadyHasDate || alreadyHasChecks) {
      return;
    }

    const { date, campus, events } = seed;

    // campus
    if (campus && setCampusFilter) {
      // campus will only be non-empty if there was exactly ONE campus
      // on the primary date. For multiple campuses we leave campusFilter
      // alone so that the user can see all of them and choose.
      setCampusFilter(campus);
    }

    // date (JS Date object) — we use the earliest date as the primary date
    if (date && setSelectedDate && setHasDateSelection) {
      const [y, m, d] = date.split('-').map((n) => parseInt(n, 10));
      if (y && m && d) {
        const jsDate = new Date(y, m - 1, d);
        setSelectedDate(jsDate);
        setHasDateSelection(true);
      }
    }

    // interest — take from the first event (we assume a single interest per session)
    if (
      events &&
      events.length &&
      setSelectedAreaOfInterest &&
      events[0].interest
    ) {
      const interestId = events[0].interest;
      setSelectedAreaOfInterest(interestId);

      try {
        window.sessionStorage.setItem('interest', interestId);
      } catch (e) {
        // ignore storage errors
      }
    }
  }, [
    seed,
    campusFilter,
    hasDateSelection,
    selectedItems,
    setCampusFilter,
    setSelectedDate,
    setHasDateSelection,
    setSelectedAreaOfInterest,
  ]);

  // ---- 3) Auto-select the matching event checkboxes (for ALL matching events) ----
  useEffect(() => {
    if (!seed) return;
    if (!allEvents || !allEvents.length) return;
    if (!setSelectedItems) return;

    const { events: seedEvents } = seed;
    if (!seedEvents || !seedEvents.length) return;

    // visitsRevamp stores "eventid" like "786-1768233600"
    const seedEventIds = new Set(
      seedEvents
        .map((e) => e.eventid)
        .filter(Boolean)
    );

    if (!seedEventIds.size) return;

    setSelectedItems((prev) => {
      const next = { ...prev };

      // Scan the FULL events array so we keep selections
      // even if there are multiple campuses and/or multiple dates.
      allEvents.forEach((ev) => {
        // Calendar events have event_series_id + eventid_timestamp
        if (!ev.event_series_id || !ev.eventid_timestamp) return;

        const mainEventId = `${ev.event_series_id}-${ev.eventid_timestamp}`;
        if (seedEventIds.has(mainEventId)) {
          next[ev.uuid] = true;
        }
      });

      return next;
    });
  }, [seed, allEvents, setSelectedItems]);
}