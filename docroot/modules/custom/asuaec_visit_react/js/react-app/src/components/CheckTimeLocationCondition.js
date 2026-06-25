// CheckTimeLocationCondition.js

// Helper to normalize an event's start time into Unix timestamp in seconds
function getStartSeconds(ev) {
  if (ev?.dateLuxon) {
    return Math.floor(ev.dateLuxon.toSeconds());
  }
  if (ev?.date instanceof Date) {
    return Math.floor(ev.date.getTime() / 1000);
  }
  return null;
}

// (1/28/2026)
// Get Barrett Event ID from Event
export function getBarrettEventIdFromEvent(ev) {
  if (!ev) return null;

  // 1) If there is already eventid
  if (ev.eventid) return String(ev.eventid);

  // 2) If this is a top-level event (might have these directly)
  const directSeriesId =
    ev.event_series_id ?? ev.eventseriesId ?? ev.drupal_internal__id;
  const directTs =
    ev.eventid_timestamp ?? ev.eventidTimestamp;

  if (directSeriesId && directTs != null) {
    return `${String(directSeriesId)}-${String(directTs)}`;
  }

  // 3) If this is a parent event that contains barrett_tours[]
  const bt = ev?.barrett_tours?.[0];
  if (bt?.eventid) return String(bt.eventid);

  const seriesId =
    bt?.event_series_id ?? bt?.eventseriesId ?? directSeriesId;
  const ts =
    bt?.eventid_timestamp ?? bt?.eventidTimestamp ?? directTs;

  if (!seriesId || ts == null) return null;
  return `${String(seriesId)}-${String(ts)}`;
}

// Collect keys for Barrett tours selected under Experience ASU events
export function getSelectedBarrettUnderExpasuKeys(allEvents, selectedItems, selectedStudentType) {
  const keys = new Set();

  allEvents.forEach(parent => {
    if (!selectedItems[parent.uuid]) return; // parent event not selected

    // normalize + filter the same way your UI does
    const barrettArray = Array.isArray(parent.barrett_tours)
      ? parent.barrett_tours
      : (parent.barrett_tours ? [parent.barrett_tours] : []);

    const filtered = barrettArray.filter(tour => {
      const visitorTypes = tour.visitorTypes || [];
      return !selectedStudentType || visitorTypes.includes(selectedStudentType);
    });

    filtered.forEach((tour, index) => {
      const id = `${parent.uuid}_barrett_${index}`;
      if (!selectedItems[id]) return;

      const key = getBarrettEventIdFromEvent(tour);

      if (key) keys.add(key);
    });
  });

  return keys;
}

// Remove a selected TOP-LEVEL Barrett event that matches the given Barrett Event ID key.
// selectedItems is an object map: { [id]: boolean }
export function removeMatchingTopLevelBarrettSelection(allEvents, selectedItems, barrettEventKey) {
  if (!barrettEventKey) return selectedItems;

  const next = { ...selectedItems };

  allEvents.forEach(ev => {
    // Only consider selected top-level events.
    if (!next[ev.uuid]) return;

    // Only top-level Barrett (adjust if your type naming differs)
    if (ev.type !== 'barrett') return;

    const key = getBarrettEventIdFromEvent(ev);
    if (key && key === barrettEventKey) {
      // Uncheck it
      delete next[ev.uuid];
    }
  });

  return next;
}


/**
 * Decide whether the user is allowed to select this event.
 *
 * @param {Object} eventToToggle - the event object for the clicked checkbox
 * @param {Object[]} allEvents   - full events array from CalendarPage
 * @param {Object} selectedItems - current selectedItems map { [uuid]: boolean }
 * @param {boolean} willSelect   - true if we are about to SELECT (turn on), false if unselecting
 *
 * @returns {{ allowed: boolean, reason?: string, conflicts?: Object[] }}
 */
export function canToggleEventSelection(eventToToggle, allEvents, selectedItems, willSelect, selectedStudentType) {
  // If we are unselecting, always allow.
  if (!willSelect || !eventToToggle) {
    return { allowed: true };
  }

  // (1/28/2026)
  // Rule 4: if user tries to select a top-level Barrett event that is already selected under Experience ASU, block it.
  const eventKey = getBarrettEventIdFromEvent(eventToToggle);
  if (eventKey && eventToToggle.type === 'barrett') {
    const selectedUnderExp = getSelectedBarrettUnderExpasuKeys(allEvents, selectedItems, selectedStudentType);
    if (selectedUnderExp.has(eventKey)) {
      return { allowed: false, reason: 'duplicate-barrett-under-expasu' };
    }
  }


  const startSeconds = getStartSeconds(eventToToggle);
  if (startSeconds == null) {
    return { allowed: true };
  }

  const sameStartConflicts = [];
  const aFewHourCampusConflicts = [];
  const losanCampusConflicts = [];

  const aFewHoursInSeconds = 3 * 60 * 60; // Set it to 3 hours (12/23/2025)

  // Look for conflicting events that are already selected
  allEvents.forEach(ev => {
    if (!selectedItems[ev.uuid]) return; // only currently selected
    if (ev.uuid === eventToToggle.uuid) return; // skip itself

    const otherStart = getStartSeconds(ev);
    if (otherStart == null) return;

    // --- Rule 1: same start time (date + time) -> conflict ---
    if (otherStart === startSeconds) {
      sameStartConflicts.push(ev);
      return;
    }

    // ---- Rule 2: different campus AND within 3 hours -> conflict ----
    const diff = Math.abs(otherStart - startSeconds);
    if (diff < aFewHoursInSeconds && ev.campus !== eventToToggle.campus) {
      aFewHourCampusConflicts.push(ev);
      return;
    }

    // ---- Rule 3: If user selected LA event, they cannot select events in Arizona on the same day.
    const LA_CAMPUS = 'ASU California Center in downtown LA';

    function toISODateFromSeconds(sec) {
      return new Date(sec * 1000).toISOString().slice(0, 10); // YYYY-MM-DD
    }

    const eventDate = toISODateFromSeconds(startSeconds);
    const otherEventDate = toISODateFromSeconds(otherStart);

    if (eventDate === otherEventDate) {

      // Selecting LA but already have AZ
      if (eventToToggle.campus === LA_CAMPUS && ev.campus !== LA_CAMPUS) {
        losanCampusConflicts.push(ev);
        return;
      }

      // Selecting AZ but already have LA
      if (eventToToggle.campus !== LA_CAMPUS && ev.campus === LA_CAMPUS) {
        losanCampusConflicts.push(ev);
        return;
      }
    }

  });

  if (sameStartConflicts.length > 0) {
    return {
      allowed: false,
      reason: 'same-start',
      conflicts: sameStartConflicts,
    };
  }

  if (aFewHourCampusConflicts.length > 0) {
    return {
      allowed: false,
      reason: 'a-few-hour-campus',
      conflicts: aFewHourCampusConflicts,
    };
  }

  if (losanCampusConflicts.length > 0) {
    return {
      allowed: false,
      reason: 'losan-campus',
      conflicts: losanCampusConflicts,
    };
  }

  return { allowed: true };
}