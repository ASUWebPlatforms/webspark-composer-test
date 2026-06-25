import React, { useState, useEffect, useMemo, useRef } from 'react';
import Calendar from 'react-calendar';
import './CalendarPage.css';
import InterestDropdown from './InterestDropdown'; // Import the new dropdown component
import { interestLabels } from './InterestDropdown';
import CampusOptions from './CampusOptions';
import YouHaveSelected from './YouHaveSelected';
import TourOptions from './TourOptions'; // Tours filter
import VisitBucketTaxDescription from './VisitBucketTaxDescription';
import { DateTime } from 'luxon';
import { shouldShowAdditionalTour } from './AdditionalTour';
import { applyCapacityWithBatchCounts } from './Capacity';
import { checkCapacityForBarrettUnderExpASU } from './CheckCapacityForBarrettUnderExpASU';
import { checkCapacityForAddTour } from './CheckCapacityForAddTour';
import GoldFilterIndicator from './GoldFilterIndicator';
import ColoredDots from './ColoredDots';
import { populateFromCancelForm } from './ComingFromCancelForm';
import { buildCancelUrlParams } from './Util.js'; // Get current URL params when coming from Cancel form
import WaitCursor from './WaitCursor';
import {
  canToggleEventSelection,
  getBarrettEventIdFromEvent,
  removeMatchingTopLevelBarrettSelection,
} from './CheckTimeLocationCondition';
import BarrettDescription from './BarrettDescription';
import { usePreselectFromVisitsRevamp } from './PreSelectFiltersAndEvents';
import { useAutoScrollToCalendarWhenFiltersComplete } from './FiltersComplete';
import EventDescription from './EventDescription';

const userRoles = window.drupalSettings?.asuaec_visit_react?.userRoles || [];
// console.log("userRoles: ", userRoles);
// isPrivileged - decide whether the viewer is privileged (admins/site builders can see test content or edit links)
const isPrivileged =
  userRoles.includes('administrator') || userRoles.includes('site_builder');

const maxMonthYear = window?.drupalSettings?.visitRevamp?.maxMonthYear || null;
// const maxMonthYear = window?.drupalSettings?.visitRevamp?.maxMonthYear || "12/2025"; // TODO: If not set, show next 4 months

// Added on 3/11/2026.
const barrettDescriptions =
  window?.drupalSettings?.visitRevamp?.barrettDescriptions || {};

// Added on 5/12/2026.
// These are the settings for interests, campuses, and presets (which control allowed filters and default campus for each calendar page).
const calendarInterests =
  window?.drupalSettings?.visitRevamp?.calendarInterests || {};
const calendarCampuses =
  window?.drupalSettings?.visitRevamp?.calendarCampuses || {};
const calendarPresets =
  window?.drupalSettings?.visitRevamp?.calendarPresets || {};

// Look at path to determine which preset applies (if any), and pull the config for allowed interests/campuses and default campus for that preset.
const getRouteKey = () => {
  const path = String(window.location.pathname || '').replace(/^\/+|\/+$/g, '');
  return path || 'schedule';
};

const currentRouteKey = getRouteKey();
const currentPreset = calendarPresets[currentRouteKey] || {};

const allowedInterestsConfig = currentPreset.allowed_interests ?? 'all';
const allowedCampusesConfig = currentPreset.allowed_campuses ?? 'all';
const defaultCampusConfig = currentPreset.default_campus || '';
const lockCampusConfig = currentPreset.lock_campus === true;

const allowedInterestsByLevel = (() => {
  if (allowedInterestsConfig === 'all') {
    return {
      grad: Object.keys(calendarInterests.grad || {}),
      ugrad: Object.keys(calendarInterests.ugrad || {}),
    };
  }
  if (typeof allowedInterestsConfig === 'object') {
    return {
      grad: allowedInterestsConfig.grad || [],
      ugrad: allowedInterestsConfig.ugrad || [],
    };
  }
  return {
    grad: [],
    ugrad: [],
  };
})();

const getInterestLabel = (interestKey) =>
  calendarInterests.grad?.[interestKey] ||
  calendarInterests.ugrad?.[interestKey] ||
  String(interestKey);

const normalizeCampusName = (label) => {
  if (!label) return '';
  const normalized = String(label)
    .replace(/\./g, '')
    .replace(/\s+campus$/i, '')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();

  // Treat West Valley campus and West as equivalent.
  if (normalized === 'west') return 'west valley';
  return normalized;
};

// Helper: builds the custom month-scoped instances endpoint
function buildInstancesMonthUrl(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const base = '/visit-revamp-api/filtered-eventinstances-month';
  return `${base}/${year}/${month}`;
}

// Helper: attach student types (visitor type) to each event
// Return instance with studentType: [...]
function attachStudentTypes(eventsWithCampus, eventSeriesData) {
  return eventsWithCampus.map((event) => {
    const series = eventSeriesData.find(
      (series) =>
        String(series?.top_level_id) === String(event.event_series_id),
    );

    if (!series) {
      console.warn(
        `No series found for event_series_id: ${event.event_series_id}`,
      );
      return { ...event, studentType: [] };
    }

    // Extract attributes correctly no matter the format
    let attributes = series?.data?.[0]?.attributes || {};
    if (Array.isArray(series.data)) {
      attributes = series.data[0]?.attributes || {};
    } else if (series.data?.attributes) {
      attributes = series.data.attributes;
    }

    let studentType = attributes?.field_visitor_type || [];
    if (!Array.isArray(studentType)) {
      studentType = [studentType];
    }

    return { ...event, studentType };
  });
}

// For ASU in LA page, we want to show a custom message with a markdown link when user selects "Graduate student". This is the default message if not set in Drupal. (6/20/2026)
const defaultGraduatePopupMessage =
  'Individuals considering a [graduate program](https://california.asu.edu/degree-programs#webspark-anchor-link--148) are encouraged to reach out to their academic department to schedule an in-person visit.';

// Helper: render a message with a single markdown link in the format of [link text](url). If the message doesn't match this format, render as plain text. (6/20/2026)
const renderMessageWithMarkdownLink = (message) => {
  const match = String(message || '').match(/\[([^\]]+)\]\(([^)]+)\)/);

  if (!match) {
    return message;
  }

  const [fullMatch, linkText, href] = match;
  const [before, after] = message.split(fullMatch);

  return (
    <>
      {before}
      <a href={href}>{linkText}</a>
      {after}
    </>
  );
};

function CalendarPage() {
  const fetchVersionRef = useRef(0); // Increments every fetch. If an older fetch finishes late, ignore it.

  // Auto-scroll to calendar when filters are complete
  const calendarWrapperRef = useRef(null);
  const didAutoScrollRef = useRef(false);

  // ============================
  // Performance: Month cache + prefetch - Added on 12/22/2025
  // ============================
  const monthCacheRef = useRef({}); // { [YYYY-MM]: { events, campuses, availableTourFilters } }
  const monthInFlightRef = useRef({}); // { [YYYY-MM]: Promise }
  const monthAbortRef = useRef({}); // { [YYYY-MM]: AbortController }

  // Cache these across months
  const seriesMetaCacheRef = useRef({}); // { [seriesId|type]: normalizedSeriesMeta }
  const additionalTourCacheRef = useRef({}); // { [tourUuid]: attributes }
  const barrettTourCacheRef = useRef({}); // { [tourUuid]: data }

  const monthKey = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    return `${y}-${m}`;
  };

  const firstOfMonth = (d) => new Date(d.getFullYear(), d.getMonth(), 1);

  const addMonths = (d, delta) =>
    new Date(d.getFullYear(), d.getMonth() + delta, 1);

  // Simple concurrency limiter (prevents 100+ simultaneous fetches)
  async function runBatched(items, batchSize, worker) {
    const results = [];
    for (let i = 0; i < items.length; i += batchSize) {
      const slice = items.slice(i, i + batchSize);
      const chunk = await Promise.all(slice.map(worker));
      results.push(...chunk);
    }
    return results;
  }

  const [loading, setLoading] = useState(false); // used for wait cursor / loading indicator.
  const [campusUiReady, setCampusUiReady] = useState(false);
  const [campusLoading, setCampusLoading] = useState(false);

  const [events, setEvents] = useState([]);
  //const [selectedDate, setSelectedDate] = useState(new Date());
  const [selectedDate, setSelectedDate] = useState(null);
  const [hasDateSelection, setHasDateSelection] = useState(false);
  const [campusFilter, setCampusFilter] = useState('');
  const [campusResetToken, setCampusResetToken] = useState(0); // force CampusOptions remount
  const [campuses, setCampuses] = useState([]);

  useEffect(() => {
    if (lockCampusConfig && defaultCampusConfig && selectedAreaOfInterest) {
      // Convert preset key to display label for filtering, strip " campus" suffix
      let displayLabel =
        calendarCampuses[defaultCampusConfig] || defaultCampusConfig;
      displayLabel = displayLabel.replace(/\s+campus$/i, '');
      setCampusFilter(displayLabel);
      setShowTours(true);
    }
  }, [lockCampusConfig, defaultCampusConfig, selectedAreaOfInterest]);

  const isAsuInLaPage = currentRouteKey === 'asuinla'; // Added on 5/29/2026.

  const studentTypeOptions = [
    { key: 'High school freshman', value: 'a high school freshman' },
    { key: 'High school sophomore', value: 'a high school sophomore' },
    { key: 'High school junior', value: 'a high school junior' },
    { key: 'High school senior', value: 'a high school senior' },
    {
      key: 'College transfer',
      value: 'in college and thinking of transferring to ASU',
    },
    {
      key: 'Graduate student',
      value: isAsuInLaPage
        ? 'considering graduate school'
        : 'considering graduate school (Masters, PhD, EdD, DNP, etc.)',
    },
    {
      key: 'Other',
      value: 'a high school counselor, teacher, or community leader',
    },
  ];

  const [selectedStudentType, setSelectedStudentType] = useState(''); // Single selection
  // Track whether the user has actively chosen a person type/student type
  const [personTouched, setPersonTouched] = useState(false);
  // NOTE: keep selectedStudentType default as "" (that already acts like “All” internally)
  // For ASU in LA page, we want the "Graduate student" option to open a popup window. So we track that separately with showGraduatePopup. (6/20/2026)
  const [showGraduatePopup, setShowGraduatePopup] = useState(false);

  const [selectedAreaOfInterest, setSelectedAreaOfInterest] = useState('');
  // A token we bump to re-mount InterestDropdown (clears previous selection visually)
  const [interestResetToken, setInterestResetToken] = useState(0);

  const [selectedItems, setSelectedItems] = useState({}); // checkboxes (main events + nested tours)

  // Month control
  const [visibleMonth, setVisibleMonth] = useState(new Date()); // used by Calendar. Controls which month the calendar shows.
  const [selectedMonth, setSelectedMonth] = useState(null); // which radio button user selected (drives fetch).
  const [monthOptions, setMonthOptions] = useState([]); // list of months to render as radios.
  const [currentMonth, setCurrentMonth] = useState(DateTime.now()); //  Luxon version; mostly for internal alignment.

  // Tours filter / Tour filter
  const [availableTourFilters, setAvailableTourFilters] = useState([]); // computed from events in month
  const [selectedTourFilters, setSelectedTourFilters] = useState([]); // user picks

  // Guided accordion open states
  const [isMonthOpen, setIsMonthOpen] = useState(true);
  const [isPersonOpen, setIsPersonOpen] = useState(false);
  const [isInterestOpen, setIsInterestOpen] = useState(false); // Always open
  const [isLocationOpen, setIsLocationOpen] = useState(false);

  // Show Tours after Location chosen
  const [showTours, setShowTours] = useState(false);

  const handleInterestChange = (interest) => {
    setSelectedAreaOfInterest(interest);
  };

  const handleCheckboxChange = (id) => {
    // Changed on 12/10/2025.
    // If this is a top-level event checkbox, id will just be the event.uuid.
    // Nested options use patterns like `${event.uuid}_additional_${i}` or `_barrett_`.
    const isMainEvent = !id.includes('_');

    // (1/28/2026)
    // If user selects a Barrett tour under Experience ASU,
    // and they already selected the same TOP-LEVEL Barrett event,
    // uncheck the top-level Barrett with the same Barrett Event ID.
    const isNestedBarrett = id.includes('_barrett_');
    if (isNestedBarrett) {
      const currentlySelected = !!selectedItems[id];
      const willSelect = !currentlySelected;

      if (willSelect) {
        // id format: `${parent.uuid}_barrett_${index}`
        const [parentUuid, indexStr] = id.split('_barrett_');
        const index = parseInt(indexStr, 10);

        const parentEvent = events.find((e) => e.uuid === parentUuid);
        const tour = parentEvent?.barrett_tours?.[index];

        const barrettKey = getBarrettEventIdFromEvent(tour);
        if (barrettKey) {
          setSelectedItems((prev) => {
            const next = removeMatchingTopLevelBarrettSelection(
              events,
              prev,
              barrettKey,
            );
            return {
              ...next,
              [id]: true, // select this nested barrett
            };
          });
          return; // IMPORTANT: stop here so we don't run the default toggle below
        }
      }
    }

    if (isMainEvent) {
      const event = events.find((e) => e.uuid === id);
      if (event) {
        const currentlySelected = !!selectedItems[id];
        const willSelect = !currentlySelected;

        // Only run the rule when turning ON the selection
        if (willSelect) {
          const { allowed, reason, conflicts } = canToggleEventSelection(
            event,
            events,
            selectedItems,
            willSelect,
            selectedStudentType,
          );

          if (!allowed) {
            // Browser alert
            if (reason === 'same-start') {
              alert(
                'You already selected another event that starts at the same time. ' +
                  'Please remove that event before selecting this one.',
              );
            } else if (reason === 'a-few-hour-campus') {
              alert(
                'You already selected another event at a different campus that starts within a few hours. ' +
                  'Please remove that event before selecting this one.',
              );
            } else if (reason === 'losan-campus') {
              alert(
                'You are trying to schedule campus tours in different states on the same day. ' +
                  'Please remove that event before selecting this one.',
              );
            } else if (reason === 'duplicate-barrett-under-expasu') {
              alert(
                'You already selected the same Barrett tour with Experience ASU.',
              );
            } else {
              alert('You already have another conflicting event selected.');
            }

            // Do NOT toggle this checkbox
            return;
          }
        }
      }
    }

    // Default behavior: toggle the checkbox
    setSelectedItems((prev) => ({
      ...prev,
      [id]: !prev[id],
    }));
  };

  // Remove an entire event selection (and its nested tours) -- Used with "Remove" button in "You have selected" section. Added on 12/10/2025.
  const handleRemoveSelection = (eventUuid) => {
    setSelectedItems((prev) => {
      const next = { ...prev };

      // Turn off the main event checkbox
      if (next[eventUuid]) {
        next[eventUuid] = false;
      }

      // Turn off any nested additional/barrett selections for this event
      Object.keys(next).forEach((key) => {
        if (key.startsWith(`${eventUuid}_`)) {
          next[key] = false;
        }
      });

      return next;
    });
  };

  // Tours filter / Tour filter
  const handleTourFilterChange = (filters) => {
    setSelectedTourFilters(filters);
  };

  // // Person type selection + advance
  // const selectPersonType = (key) => {
  //   setSelectedStudentType(key); // internal value (“All” is "")
  //   setPersonTouched(true); // mark user has picked
  //   sessionStorage.setItem("persontype", key);
  //   setIsPersonOpen(false);
  //   setIsInterestOpen(true);

  //   // Clear any previous interest & downstream selections
  //   sessionStorage.removeItem('interest'); // drop persisted interest
  //   setSelectedAreaOfInterest(''); // reset interest state
  //   setCampusFilter(''); // location depends on interest
  //   setSelectedTourFilters([]); // tours depend on interest/location
  //   setShowTours(false);
  //   setSelectedDate(null);
  //   setHasDateSelection(false);

  //   // Force InterestDropdown to remount so UI shows cleared state
  //   setInterestResetToken((t) => t + 1);
  // };

  // Person type selection + advance
  const selectPersonType = (key) => {
    // Special case for ASU in LA page: if user selects "Graduate student", show the popup and do NOT advance to interest selection.
    if (currentPreset.graduate_popup === true && key === 'Graduate student') {
      setShowGraduatePopup(true);

      setSelectedStudentType('');
      setPersonTouched(false);
      sessionStorage.removeItem('persontype');
      sessionStorage.removeItem('interest');

      setSelectedAreaOfInterest('');
      setCampusFilter('');
      setSelectedTourFilters([]);
      setShowTours(false);
      setSelectedItems({});
      setSelectedDate(null);
      setHasDateSelection(false);

      setCampusResetToken((t) => t + 1);
      setInterestResetToken((t) => t + 1);

      setIsPersonOpen(true);
      setIsInterestOpen(false);
      setIsLocationOpen(false);
      return;
    }

    const isOtherType =
      key === 'Other' ||
      key === 'a high school counselor' ||
      key === 'a high school counselor, teacher, or community leader';

    setSelectedStudentType(key); // internal value
    setPersonTouched(true);
    sessionStorage.setItem('persontype', key);

    // Clear any previous interest & downstream selections
    sessionStorage.removeItem('interest');
    setSelectedAreaOfInterest('');
    setCampusFilter('');
    setSelectedTourFilters([]);
    setShowTours(false);
    setSelectedDate(null);
    setHasDateSelection(false);

    // Force InterestDropdown to remount so UI shows cleared state
    setInterestResetToken((t) => t + 1);

    // Advance accordions
    setIsPersonOpen(false);

    if (isOtherType) {
      // Skip interest and go straight to campus
      setIsInterestOpen(false);
      setIsLocationOpen(true);
      return;
    }

    // Default behavior (non-Other)
    setIsInterestOpen(true);
    setIsLocationOpen(false);
  };

  // Interest selection + advance
  const handleInterestChangeAndAdvance = (value) => {
    handleInterestChange(value);
    setIsInterestOpen(false);
    setIsLocationOpen(true);
  };

  // Location selection + reveal Tours
  const handleCampusChangeAndShowTours = (campus) => {
    setCampusFilter(campus);
    setIsLocationOpen(true);
    setShowTours(true); // reveal Tours
  };

  // ============================
  // Default to current month unless URL has ?month=YYYY-M
  // (Do NOT call setMonthBoth here because it's declared later)
  // ============================
  useEffect(() => {
    let first = null;

    try {
      const url = new URL(window.location.href);
      const q = url.searchParams.get('month'); // e.g., "2025-8"
      if (q) {
        const [y, m] = q.split('-').map((n) => parseInt(n, 10));
        if (y && m) first = new Date(y, m - 1, 1);
      }
    } catch (_) {
      // ignore
    }

    if (!first) {
      const now = new Date();
      first = new Date(now.getFullYear(), now.getMonth(), 1);
    }

    setSelectedMonth(first);
    setVisibleMonth(first);
    setHasDateSelection(false);
  }, []);

  // Build month options on mount
  useEffect(() => {
    if (maxMonthYear) {
      const [maxMM, maxYYYY] = maxMonthYear.split('/').map(Number);
      const today = new Date();
      const months = [];
      let dateCursor = new Date(today.getFullYear(), today.getMonth(), 1);
      const maxDate = new Date(maxYYYY, maxMM - 1, 1);
      while (dateCursor <= maxDate) {
        months.push(new Date(dateCursor));
        dateCursor.setMonth(dateCursor.getMonth() + 1);
      }
      setMonthOptions(months);
      // setSelectedMonth(months[0]);
      // setSelectedDate(months[0]);
    }
  }, [maxMonthYear]);

  // Month handler
  // Update radio handler to use the helper
  const handleMonthChange = (e) => {
    const [year, month] = e.target.value.split('-').map(Number);
    const newDate = new Date(year, month - 1, 1);

    setMonthBoth(newDate); // updates BOTH selectedMonth + visibleMonth
    setHasDateSelection(false); // clears selected day
  };

  // Wrap existing handleMonthChange
  const handleMonthChangeAndAdvance = (e) => {
    handleMonthChange(e);
    setIsMonthOpen(false);
    setIsPersonOpen(true);
  };

  // Update both selectedMonth and visibleMonth
  const setMonthBoth = (date) => {
    const firstOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);

    setSelectedMonth((prev) => {
      if (
        prev &&
        prev.getFullYear() === firstOfMonth.getFullYear() &&
        prev.getMonth() === firstOfMonth.getMonth()
      )
        return prev;
      return firstOfMonth;
    });

    setVisibleMonth(firstOfMonth); // controls <Calendar activeStartDate>
    setHasDateSelection(false);
  };

  // Arrow handlers
  const handlePrevMonth = () => {
    setMonthBoth(
      new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1),
    );
  };
  const handleNextMonth = () => {
    setMonthBoth(
      new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1),
    );
  };

  // handleCalendarNavigation is passed to onActiveStartDateChange.
  function handleCalendarNavigation({ activeStartDate, view }) {
    // react-calendar fires for year/decade views too; ignore those
    if (view !== 'month' || !activeStartDate) return;

    // Keep everything in sync + normalize to first day of month
    setMonthBoth(activeStartDate);

    // Optional: keep currentMonth aligned (if you still need it elsewhere)
    setCurrentMonth(
      DateTime.fromJSDate(activeStartDate).setZone('America/Phoenix'),
    ); // align Luxon currentMonth.
  }

  // Populate person type & interest from sessionStorage on first load
  useEffect(() => {
    const savedPerson = sessionStorage.getItem('persontype') || '';
    const savedInterest = sessionStorage.getItem('interest') || '';

    if (savedPerson) {
      setSelectedStudentType(savedPerson);
      setPersonTouched(true); // show the radio as selected
      setIsPersonOpen(false);
      setIsInterestOpen(true);
    }

    if (savedInterest && savedPerson) {
      setSelectedAreaOfInterest(savedInterest);
      setIsInterestOpen(false);
      setIsLocationOpen(true);
    }
  }, []);

  // ============================
  // Performance: month-scoped loader with cache + prefetch - Added on 12/22/2025.
  // ============================
  async function buildMonthData(targetMonth, { signal } = {}) {
    // --- Step A: fetch instances for month ---
    const year = targetMonth.getFullYear();
    const month = String(targetMonth.getMonth() + 1).padStart(2, '0');
    const url = `/visit-revamp-api/filtered-eventinstances-month/${year}/${month}`;

    // Handle non-OK responses safely (prevents hard crash / silent failure)
    const visitEventsResponse = await fetch(url, { signal });
    if (!visitEventsResponse.ok) {
      console.warn('[calendar] instances endpoint not OK', {
        url,
        status: visitEventsResponse.status,
      });
      return { events: [], campuses: [], availableTourFilters: [] };
    }

    const visitEvents = await visitEventsResponse.json();

    let formattedEvents = (visitEvents?.data || []).map((event) => {
      const azStart = DateTime.fromISO(event.start, {
        zone: 'America/Phoenix',
      });
      const azEnd = event.end
        ? DateTime.fromISO(event.end, { zone: 'America/Phoenix' })
        : null;

      const eventSeriesId = event.eventseries_id;

      return {
        id: eventSeriesId,
        uuid: event.uuid,
        title: event.title,
        date: new Date(
          azStart.year,
          azStart.month - 1,
          azStart.day,
          azStart.hour,
          azStart.minute,
        ),
        end_date: azEnd
          ? new Date(
              azEnd.year,
              azEnd.month - 1,
              azEnd.day,
              azEnd.hour,
              azEnd.minute,
            )
          : null,
        event_series_id: eventSeriesId,
        type: event.type,
        drupal_internal_id: event.drupal_internal_id,
        dateLuxon: azStart,
        endDateLuxon: azEnd,

        overwrite_capacity: event.overwrite_capacity,
        capacity_instance: event.capacity_instance,
        overwrite_barrett_tour: event.overwrite_barrett_tour,
        barrett_tours_instance: event.barrett_tours_instance,
        overwrite_conf_letter: event.overwrite_conf_letter,
        conf_letter_instance: event.conf_letter_instance,

        overwrite_legend_toggle: event.overwrite_legend_toggle,
        legend_toggle_instance: event.legend_toggle_instance,
      };
    });

    // Build (seriesId|ISODate) -> instance lookup
    // Build a fast lookup: (seriesId, ISO date) -> eventinstance
    const instanceIndex = {};
    formattedEvents.forEach((ev) => {
      if (!ev.event_series_id || !ev.dateLuxon) return;
      const key = `${String(ev.event_series_id)}|${ev.dateLuxon.toISODate()}`;
      instanceIndex[key] = ev;
    });

    const getInstanceForSeriesOnDate = (seriesId, dateLuxon) => {
      if (!seriesId || !dateLuxon) return null;
      const key = `${String(seriesId)}|${dateLuxon.toISODate()}`;
      return instanceIndex[key] || null;
    };

    // NEW: Safety wrapper. If ANYTHING below throws, we fall back to a simple result.
    try {
      // ========================================
      // NEW: Fetch all month metadata in ONE call
      // ========================================
      const metaUrl = `/visit-revamp-api/month-metadata/${year}/${month}`;
      let monthMeta = null;

      try {
        const metaResp = await fetch(metaUrl, { signal });
        if (metaResp.ok) {
          monthMeta = await metaResp.json();
        } else {
          console.warn('[calendar] month metadata endpoint not OK', {
            metaUrl,
            status: metaResp.status,
          });
        }
      } catch (e) {
        if (signal?.aborted) throw e;
        console.warn('[calendar] month metadata fetch failed', e);
      }

      const seriesMetaFromApi = monthMeta?.series || {};
      const additionalToursFromApi = monthMeta?.additional_tours || {};
      const barrettToursFromApi = monthMeta?.barrett_tours || {};

      // --- Step B: fetch series metadata (NOW using month metadata + cache) ---
      const eventSeriesIds = Array.from(
        new Set(formattedEvents.map((e) => e.event_series_id).filter(Boolean)),
      );
      const excludedSeriesIds = new Set();
      const additionalToursBySeries = {};
      const barrettToursBySeries = {};

      const seriesMetaById = {}; // month-local, but source data comes from seriesMetaCacheRef

      // Instead of runBatched() per-series HTTP calls, we walk the series IDs
      // and hydrate from monthMeta (with cache + exclusion logic).
      for (const id of eventSeriesIds) {
        try {
          const idStr = String(id);

          // -----------------------------
          // NEW: Pull series from monthMeta
          // -----------------------------
          let seriesItem = seriesMetaFromApi[idStr] || seriesMetaFromApi[id];

          // LEGACY / fallback: if monthMeta didn't have it, try existing cache
          if (!seriesItem) {
            // Try cache first
            const eventType = formattedEvents.find(
              (e) => e.event_series_id === id,
            )?.type;
            const typeParam =
              eventType === 'barrett' ? 'barrett' : 'visit_event';
            const cacheKey = `${idStr}|${typeParam}`;
            const cached = seriesMetaCacheRef.current[cacheKey];

            if (!cached || !cached.data || cached.data.length === 0) {
              // IMPORTANT:
              // Do NOT exclude the series if we can't find metadata.
              // Just log a warning and keep the events; they'll show with
              // default campus = "Unknown" and no tours.
              console.warn(
                '[calendar] no series metadata found for series',
                idStr,
                '- leaving events visible without metadata',
              );
              continue;
            }

            seriesMetaById[idStr] = cached;

            // Extract relationships from cached data (for additional / Barrett)
            const series0 = cached.data[0];
            const rels = series0?.relationships || {};
            const additionalTours = rels.field_additional_tours || [];
            const barrettTours = rels.field_barrett_tours || [];

            additionalToursBySeries[id] = additionalTours.map((t) => ({
              uuid: t.uuid,
              paragraphId: t.paragraphId,
            }));

            barrettToursBySeries[id] = barrettTours.map((t) => ({
              uuid: t.uuid,
              eventseriesId: t.eventseriesId || t.drupal_internal__id,
              title: t.title,
            }));

            // We still need to apply exclusion + display_title/eventid_timestamp below,
            // so treat series0 as the seriesItem.
            seriesItem = series0;
          } else {
            // We have it from monthMeta – normalize into the same cache shape: { top_level_id, data: [seriesItem] }
            const eventType = formattedEvents.find(
              (e) => e.event_series_id === id,
            )?.type;
            const typeParam =
              eventType === 'barrett' ? 'barrett' : 'visit_event';
            const cacheKey = `${idStr}|${typeParam}`;

            let normalized = seriesMetaCacheRef.current[cacheKey];
            if (!normalized) {
              const arr = [seriesItem];
              normalized = { top_level_id: idStr, data: arr };
              seriesMetaCacheRef.current[cacheKey] = normalized;
            }
            seriesMetaById[idStr] = normalized;

            // Relationships from monthMeta
            const rels = seriesItem.relationships || {};
            const additionalTours = rels.field_additional_tours || [];
            const barrettTours = rels.field_barrett_tours || [];

            additionalToursBySeries[id] = additionalTours.map((t) => ({
              uuid: t.uuid,
              paragraphId: t.paragraphId,
            }));

            barrettToursBySeries[id] = barrettTours.map((t) => ({
              uuid: t.uuid,
              eventseriesId: t.eventseriesId || t.drupal_internal__id,
              title: t.title,
            }));
          }

          // --- Exclusion + display_title/eventid_timestamp ---
          const attrs = seriesItem?.attributes || {};
          const eventType = formattedEvents.find(
            (e) => e.event_series_id === id,
          )?.type;

          // Skip Test mode event
          if (attrs.field_test_mode === true && !isPrivileged) {
            excludedSeriesIds.add(idStr);
            continue;
          }

          // field_privacy - Skip "Private" or "Registration closed" events
          const privacy = attrs.field_privacy;
          // console.log("privacy:", privacy);
          if (privacy === 'Private' || privacy === 'Registration closed') {
            excludedSeriesIds.add(idStr);
            continue;
          }

          // Skip Barrett series marked only for under Experience ASU
          if (
            eventType === 'barrett' &&
            attrs.field_show_only_under_expasu === true
          ) {
            excludedSeriesIds.add(idStr);
            continue;
          }

          // Attach display_title/capacity/privacy/publish_date + eventid_timestamp
          formattedEvents = formattedEvents.map((event) => {
            if (event.event_series_id !== id) return event;

            // Use dateLuxon -- it preserves AZ time
            const baseDateLuxon = event.dateLuxon
              ? event.dateLuxon.startOf('day') // Midnight in AZ
              : DateTime.fromJSDate(event.date, {
                  zone: 'America/Phoenix',
                }).startOf('day');

            // let startTimeInSeconds = 0;
            // // Use field_start_time_for_eventid from Event Series
            // if (typeof attrs.field_start_time_for_eventid === 'number') {
            //   startTimeInSeconds = attrs.field_start_time_for_eventid;
            // } else if (event.dateLuxon) {
            //   // Fallback — use the actual instance start time
            //   startTimeInSeconds = event.dateLuxon.diff(event.dateLuxon.startOf('day'), 'seconds').seconds;
            // }
            // console.log("event:", event);
            // console.log("attrs:" , attrs);

            // Use field_start_time_for_eventid from Event Series (Bug fix on 2/18/2026)
            // HARD REQUIREMENT: field_start_time_for_eventid must exist and be numeric (string or number).
            const rawStartTimeForEventId = attrs?.field_start_time_for_eventid;
            // console.log("rawStartTimeForEventId:", rawStartTimeForEventId);
            if (
              rawStartTimeForEventId === null ||
              rawStartTimeForEventId === undefined ||
              rawStartTimeForEventId === ''
            ) {
              throw new Error(
                `[VisitRevamp] Missing field_start_time_for_eventid for eventseries ${attrs?.drupal_internal__id || attrs?.id || 'UNKNOWN'}`,
              );
            }

            const startTimeInSeconds = Number(rawStartTimeForEventId);
            if (!Number.isFinite(startTimeInSeconds)) {
              throw new Error(
                `[VisitRevamp] Invalid field_start_time_for_eventid="${rawStartTimeForEventId}" for eventseries ${attrs?.drupal_internal__id || attrs?.id || 'UNKNOWN'}`,
              );
            }

            // Compute final timestamp (ALWAYS AZ time)
            const startTimestamp =
              Math.floor(
                (event.dateLuxon
                  ? event.dateLuxon.startOf('day')
                  : DateTime.fromJSDate(event.date, {
                      zone: 'America/Phoenix',
                    }).startOf('day')
                ).toSeconds(),
              ) + Math.floor(startTimeInSeconds);

            return {
              ...event,
              display_title: attrs.field_display_title || event.title,
              capacity_series: attrs.field_capacity || '', // Changed on 12/29/2025
              privacy: attrs.field_privacy || null,
              publish_date: attrs.field_publish_date || null,
              event_description_html:
                attrs.field_event_description_html || null, // Added on 2/2/2026
              eventid_timestamp: startTimestamp,
            };
          });
        } catch (e) {
          if (signal?.aborted) throw e;
          console.error(`Error processing event series ${id}:`, e);
        }
      }

      // Remove excluded series
      // After all series checks finished, remove excluded ones all at once
      formattedEvents = formattedEvents.filter(
        (ev) => !excludedSeriesIds.has(String(ev.event_series_id)),
      );

      // --- Step C: fetch Additional tour details (NOW from monthMeta, fallback to per-tour) ---
      // Stores attributes by uuid
      const allTourIds = Object.values(additionalToursBySeries)
        .flat()
        .map((t) => t.uuid);

      // await runBatched(allTourIds, 15, async (tourId) => {
      //   if (additionalTourCacheRef.current[tourId]) return;
      //   const resp = await fetch(`/visit-revamp-api/additional-tour/${tourId}`, { signal });
      //   const tourData = await resp.json();
      //   if (tourData?.data?.attributes) {
      //     additionalTourCacheRef.current[tourId] = tourData.data.attributes;
      //   }
      // });

      for (const tourId of allTourIds) {
        if (additionalTourCacheRef.current[tourId]) continue;

        // NEW: Try monthMeta.additional_tours first
        const fromMeta = additionalToursFromApi?.[tourId];
        if (fromMeta) {
          additionalTourCacheRef.current[tourId] = fromMeta;
          continue;
        }

        // LEGACY / fallback: hit per-tour endpoint
        try {
          const resp = await fetch(
            `/visit-revamp-api/additional-tour/${tourId}`,
            { signal },
          );
          if (!resp.ok) continue;
          const tourData = await resp.json();
          if (tourData?.data?.attributes) {
            additionalTourCacheRef.current[tourId] = tourData.data.attributes;
          }
        } catch (e) {
          if (signal?.aborted) throw e;
          console.error('Error fetching additional tour', tourId, e);
        }
      }

      // --- Step D: fetch Barrett tour details (NOW from monthMeta, fallback to per-tour) ---
      // Fetch Barrett under Exp ASU Details. Stores full data by uuid.
      const allBarrettTourIds = Object.values(barrettToursBySeries)
        .flat()
        .map((t) => t.uuid);

      for (const tourUuid of allBarrettTourIds) {
        if (barrettTourCacheRef.current[tourUuid]) continue;

        // NEW: Try monthMeta.barrett_tours first
        const fromMeta = barrettToursFromApi?.[tourUuid];

        if (fromMeta) {
          barrettTourCacheRef.current[tourUuid] = {
            id: fromMeta.uuid || fromMeta.drupal_internal__id || tourUuid,
            type: `eventseries--${fromMeta.type || 'barrett'}`,
            attributes: {
              ...fromMeta,
              drupal_internal__id: fromMeta.drupal_internal__id,
              title: fromMeta.title,
            },
          };
          continue;
        }

        // LEGACY / fallback: hit per-tour endpoint
        try {
          const resp = await fetch(
            `/visit-revamp-api/barrett-tour/${tourUuid}`,
            { signal },
          );
          if (!resp.ok) continue;
          const tourData = await resp.json();
          if (tourData?.data) {
            barrettTourCacheRef.current[tourUuid] = tourData.data;
          }
        } catch (e) {
          if (signal?.aborted) throw e;
          console.error('Error fetching Barrett tour', tourUuid, e);
        }
      }

      // Convert time string (such as 11:15 am) into timestamp which is the seconds since midnight
      const convertTimeToSecondsSinceMidnight = (timeStr) => {
        if (!timeStr || timeStr.trim().toUpperCase() === 'N/A') return 0;
        const trimmed = timeStr.trim().toUpperCase();
        const [time, modifier] = trimmed.split(' ');
        if (!time || !modifier) return 0;
        let [hours, minutes] = time.split(':').map(Number);
        if (modifier === 'PM' && hours !== 12) hours += 12;
        if (modifier === 'AM' && hours === 12) hours = 0;
        return hours * 3600 + minutes * 60;
      };

      // --- Step E: Attach Additional tour and Barrett under Exp ASU to Each Event ---
      formattedEvents = await Promise.all(
        formattedEvents.map(async (event) => {
          try {
            const additionalTourIds =
              additionalToursBySeries[event.event_series_id] || [];
            const barrettTourIds =
              barrettToursBySeries[event.event_series_id] || [];

            // ============================
            // 1) Additional tours
            // ============================
            const additionalTours = additionalTourIds
              .map((tourInfo) => {
                const tour = additionalTourCacheRef.current[tourInfo.uuid];
                if (!tour || !tour.field_time_range) return null;

                // Normalize field_time_range to an ARRAY of { from, to }
                const ranges = Array.isArray(tour.field_time_range)
                  ? tour.field_time_range
                  : tour.field_time_range
                    ? [tour.field_time_range]
                    : [];

                if (!ranges.length) return null;

                const firstRange = ranges[0];
                const from = firstRange?.from ?? null;
                const to = firstRange?.to ?? null;
                if (from == null || to == null) return null;

                // Compute real start/end timestamps by adding offsets to event’s midnight.
                // For time_unix, use Luxon base date in AZ time
                const baseDateLuxon = event.dateLuxon
                  ? event.dateLuxon.startOf('day')
                  : DateTime.fromJSDate(event.date, {
                      zone: 'America/Phoenix',
                    }).startOf('day');

                // Add seconds offset from the tour
                const startTime = Math.floor(baseDateLuxon.toSeconds()) + from;
                const endTime = Math.floor(baseDateLuxon.toSeconds()) + to;

                // For time
                const startLuxon = DateTime.fromSeconds(startTime, {
                  zone: 'America/Phoenix',
                });
                const endLuxon = DateTime.fromSeconds(endTime, {
                  zone: 'America/Phoenix',
                });
                const time = `${startLuxon.toFormat('h:mm a').toLowerCase()} - ${endLuxon.toFormat('h:mm a').toLowerCase()}`;

                // NEW: eventid_timestamp for additional tours
                const offsetForEventId =
                  tour.field_start_time_for_addtourid != null
                    ? tour.field_start_time_for_addtourid
                    : from; // fallback

                // Calculate addtour_eventid_timestamp based on tour.field_start_time_for_addtourid
                const addtour_eventid_timestamp =
                  Math.floor(baseDateLuxon.toSeconds()) + offsetForEventId;

                return {
                  id: tourInfo.paragraphId,
                  uuid: tourInfo.uuid,
                  title: tour.field_addtour_name,
                  display_title: tour.field_addtour_name,
                  time,
                  time_unix: [startTime, endTime],
                  time_range: ranges, // NEW: normalized array
                  addtour_eventid_timestamp: addtour_eventid_timestamp,
                  college: tour.field_college,
                  college_code: tour.field_college, // used by shouldShowAdditionalTour
                  parent_event_series_id: event.event_series_id,
                  addtour_eventid: `${event.event_series_id}-${tourInfo.paragraphId}-${addtour_eventid_timestamp}`,
                  need_radio_button: !!tour.field_need_radio_button,
                };
              })
              .filter(Boolean);

            // // Capacity check
            // const filteredAddTours = await checkCapacityForAddTour(additionalTours);
            // // If isFull is true, don't include it.
            // const additionalToursFinal = filteredAddTours.filter(t => !t.isFull);
            // NEW: run capacity check for additional tours
            const additionalToursWithCap =
              await checkCapacityForAddTour(additionalTours);
            const additionalToursFinal = additionalToursWithCap.filter(
              (t) => !t.isFull,
            );

            // ============================
            // 2) Barrett tours under Exp ASU
            // ============================
            const barrettToursUnderExpAsu = barrettTourIds
              .map((t) => {
                const tour = barrettTourCacheRef.current[t.uuid];
                if (!tour || !tour.attributes) return null;

                // If it is Test mode on, don't show Barrett under Exp ASU.
                if (tour.attributes.field_test_mode === true && !isPrivileged)
                  return null;

                const tourAttr = tour.attributes;

                // Base visit date = same calendar day as the parent Exp ASU event (AZ time)
                const baseVisitDateLuxon = event.dateLuxon
                  ? event.dateLuxon.setZone('America/Phoenix').startOf('day')
                  : DateTime.fromJSDate(event.date, {
                      zone: 'America/Phoenix',
                    }).startOf('day');

                // Find the Barrett event instance for this series on that day
                const parentSeriesId = String(tourAttr.drupal_internal__id);
                const matchingInstance = getInstanceForSeriesOnDate(
                  parentSeriesId,
                  baseVisitDateLuxon,
                );
                if (!matchingInstance) {
                  // console.log(
                  //   '[calendar] No matching Barrett instance for',
                  //   parentSeriesId,
                  //   baseVisitDateLuxon.toISODate()
                  // );
                  return null;
                }

                // Use the instance start/end as the single source of truth
                const startLuxon = matchingInstance.dateLuxon
                  ? matchingInstance.dateLuxon.setZone('America/Phoenix')
                  : baseVisitDateLuxon.plus({
                      seconds: tourAttr.field_start_time_for_eventid || 0,
                    });

                const endLuxon = matchingInstance.endDateLuxon
                  ? matchingInstance.endDateLuxon.setZone('America/Phoenix')
                  : startLuxon.plus({ minutes: 60 }); // fallback 1-hour duration

                const startUnix = Math.floor(startLuxon.toSeconds());
                const endUnix = Math.floor(endLuxon.toSeconds());

                // Time string now from instance
                const time = `${startLuxon.toFormat('h:mm a').toLowerCase()} - ${endLuxon.toFormat('h:mm a').toLowerCase()}`;

                const privacy2 = tourAttr.field_privacy;
                if (
                  privacy2 === 'Private' ||
                  privacy2 === 'Registration closed'
                )
                  return null;

                // Capacity comes from the matching instance
                const capacity_instance =
                  matchingInstance?.capacity_instance ?? null;
                const overwrite_capacity =
                  matchingInstance?.overwrite_capacity ?? '0';

                // const eventidTimestamp = Math.floor(baseDateLuxon.toSeconds()) + tourAttr.field_start_time_for_eventid;

                // eventid_timestamp – always based on field_start_time_for_eventid.
                // We do NOT derive it from the actual event start time, so that Event IDs
                // remain stable even if the event time changes.
                const baseMidnight = baseVisitDateLuxon;

                let offsetForEventId = 0;
                if (
                  tourAttr.field_start_time_for_eventid !== null &&
                  tourAttr.field_start_time_for_eventid !== undefined
                ) {
                  // Make sure to be number in case it comes through as a string.
                  const parsed = Number(tourAttr.field_start_time_for_eventid);
                  if (!Number.isNaN(parsed)) {
                    offsetForEventId = parsed;
                  }
                }

                const eventidTimestamp =
                  Math.floor(baseMidnight.toSeconds()) + offsetForEventId;

                // // DEBUG Barrett under Experience ASU eventid
                // if(tourAttr.drupal_internal__id == '711') {
                //   console.log("BARRETT UNDER EXP ASU EVENT ID", {
                //     parentExpASU: event.event_series_id,
                //     barrettSeriesId: tourAttr.drupal_internal__id,
                //     title: tourAttr.title,
                //     visitDate: startLuxon.toFormat('yyyy-MM-dd'),
                //     field_start_time_for_eventid: tourAttr.field_start_time_for_eventid,
                //     calculated_eventid_timestamp: eventidTimestamp,
                //     final_eventid: `${tourAttr.drupal_internal__id}-${eventidTimestamp}`,
                //     capacity_series: tourAttr.field_capacity,
                //     overwrite_capacity: overwrite_capacity,
                //     capacity_instance: capacity_instance,
                //   });
                // }

                return {
                  uuid: tour.id,
                  title: tourAttr.title,
                  time, // from instance
                  eventType: tourAttr.field_evtype || null,
                  visitorTypes: Array.isArray(tourAttr.field_visitor_type)
                    ? tourAttr.field_visitor_type
                    : tourAttr.field_visitor_type
                      ? [tourAttr.field_visitor_type]
                      : [],
                  event_series_id: tourAttr.drupal_internal__id,
                  time_unix: [startUnix, endUnix],
                  eventid_timestamp: eventidTimestamp,
                  display_title: tourAttr.field_display_title || tourAttr.title,
                  capacity_series: tourAttr.field_capacity,
                  capacity_instance: capacity_instance, // Changed on 12/29/2025
                  overwrite_capacity: overwrite_capacity,
                  visitDate: startLuxon.toFormat('yyyy-MM-dd'), // from instance start
                  eventid: `${tourAttr.drupal_internal__id}-${eventidTimestamp}`,
                };
              })
              .filter(Boolean);

            const filteredBarrettTours =
              await checkCapacityForBarrettUnderExpASU(barrettToursUnderExpAsu);
            const barrettToursFinal = filteredBarrettTours.filter(
              (t) => !t.isFull,
            );

            return {
              ...event,
              additional_tours: additionalToursFinal,
              barrett_tours: barrettToursFinal,
            };
          } catch (e) {
            // NEW: if enrichment fails for this ONE event, don't kill the whole month
            if (signal?.aborted) throw e;
            console.error('[calendar] failed to enrich event', {
              event_series_id: event.event_series_id,
              uuid: event.uuid,
              error: e,
            });
            return event; // return the bare event so it still shows in calendar
          }
        }),
      );

      // --- Step F: campus + legend + tour filters + student types + capacity ---
      const eventSeriesData = Object.values(seriesMetaById);

      const eventsWithCampus = formattedEvents.map((event) => {
        const series = eventSeriesData.find(
          (s) => s?.top_level_id === String(event.event_series_id),
        );
        const attributes = series?.data?.[0]?.attributes || {};

        // NEW: series-level capacity from MonthMetadata
        const seriesCapacity = attributes.field_capacity ?? null;

        let campus = (attributes.field_campus || 'Unknown').replace(/\./g, '');

        const overwriteLegend =
          event.overwrite_legend_toggle === true ||
          event.overwrite_legend_toggle === 1 ||
          event.overwrite_legend_toggle === '1' ||
          String(event.overwrite_legend_toggle || '').toLowerCase() === 'on';

        let legend = [];
        if (overwriteLegend && event.legend_toggle_instance) {
          legend = Array.isArray(event.legend_toggle_instance)
            ? event.legend_toggle_instance
            : [event.legend_toggle_instance];
        } else {
          legend = attributes.field_legend_toggle
            ? Array.isArray(attributes.field_legend_toggle)
              ? attributes.field_legend_toggle
              : [attributes.field_legend_toggle]
            : [];
        }

        const event_type = attributes.field_evtype ?? '';

        return {
          ...event,
          campus,
          legend,
          // Keep both instance + series capacity raw, let Capacity.js decide:
          capacity_series: seriesCapacity, // from Event Series
          // capacity_instance & overwrite_capacity were already added in formattedEvents
          event_type,
        };
      });

      // Extract unique tour legend values for Tours filter options
      const tourLegendValues = new Set();
      eventsWithCampus.forEach((ev) => {
        const legend = ev.legend || [];
        if (Array.isArray(legend)) {
          legend.forEach((item) => {
            if (typeof item === 'string') tourLegendValues.add(item);
            else if (item?.value) tourLegendValues.add(item.value);
          });
        } else if (typeof legend === 'string') {
          tourLegendValues.add(legend);
        } else if (legend?.value) {
          tourLegendValues.add(legend.value);
        }
      });
      tourLegendValues.delete('facility');

      // (1/20/2026)
      const eventsWithStudentType = attachStudentTypes(
        eventsWithCampus,
        eventSeriesData,
      );

      // Use Capacity.js batch
      let eventsWithCapacity = await applyCapacityWithBatchCounts(
        eventsWithStudentType,
        { signal },
      );

      // Don't show "full" events
      eventsWithCapacity = eventsWithCapacity.filter((ev) => !ev.isFull);

      const uniqueCampuses = Array.from(
        new Set(eventsWithCapacity.map((e) => e.campus)),
      );

      return {
        events: eventsWithCapacity,
        campuses: uniqueCampuses,
        availableTourFilters: [...tourLegendValues],
      };
    } catch (e) {
      // NEW: Fallback so the calendar NEVER goes completely blank
      if (signal?.aborted) throw e;
      console.error(
        '[calendar] buildMonthData failed, falling back to basic events only',
        e,
      );

      const fallbackEvents = formattedEvents.map((ev) => ({
        ...ev,
        campus: 'Unknown',
        legend: [],
        isFull: false,
        additional_tours: [],
        barrett_tours: [],
      }));
      const fallbackCampuses = Array.from(
        new Set(fallbackEvents.map((e) => e.campus)),
      );

      return {
        events: fallbackEvents,
        campuses: fallbackCampuses,
        availableTourFilters: [],
      };
    }
  }

  async function loadMonthEvents(
    targetMonth,
    { setAsCurrent = true, showSpinner = true } = {},
  ) {
    const m = firstOfMonth(targetMonth);
    const key = monthKey(m);

    // Serve from cache instantly
    const cached = monthCacheRef.current[key];
    if (cached) {
      if (setAsCurrent) {
        setEvents(cached.events);
        setCampuses(cached.campuses);
        setAvailableTourFilters(cached.availableTourFilters);
      }
      return;
    }

    // Deduplicate in-flight
    if (monthInFlightRef.current[key]) {
      const data = await monthInFlightRef.current[key];
      if (setAsCurrent && data) {
        setEvents(data.events);
        setCampuses(data.campuses);
        setAvailableTourFilters(data.availableTourFilters);
      }
      return;
    }

    // Only abort other requests when THIS call is the "current UI month".
    // Prefetch must NOT abort the in-flight current month request.
    if (setAsCurrent) {
      try {
        Object.entries(monthAbortRef.current).forEach(([k, controller]) => {
          if (k !== key) controller.abort();
        });
      } catch (_) {}
    }

    const controller = new AbortController();
    monthAbortRef.current[key] = controller;

    // Version bump only for current (UI) month; prefetch shares current version
    const myVersion = setAsCurrent
      ? ++fetchVersionRef.current
      : fetchVersionRef.current;

    if (showSpinner && setAsCurrent) {
      setLoading(true);
      document.body.classList.add('wait-cursor');
    }

    const promise = (async () => {
      try {
        const data = await buildMonthData(m, { signal: controller.signal });

        // Stale guard only applies to current UI month
        if (setAsCurrent && myVersion !== fetchVersionRef.current) return null;

        monthCacheRef.current[key] = data;
        return data;
      } catch (e) {
        if (controller.signal.aborted) return null;
        console.error('[calendar] month load failed', key, e);
        return null;
      } finally {
        delete monthInFlightRef.current[key];
        if (monthAbortRef.current[key] === controller)
          delete monthAbortRef.current[key];

        if (showSpinner && setAsCurrent) {
          document.body.classList.remove('wait-cursor');
          setLoading(false);
        }
      }
    })();

    monthInFlightRef.current[key] = promise;

    const data = await promise;
    if (data && setAsCurrent) {
      setEvents(data.events);
      setCampuses(data.campuses);
      setAvailableTourFilters(data.availableTourFilters);
    }
  }

  // ============================
  // NEW: Load selected month, prefetch neighbors
  // ============================
  useEffect(() => {
    if (!visibleMonth) return;

    let cancelled = false;

    const monthToLoad = selectedMonth
      ? firstOfMonth(selectedMonth)
      : firstOfMonth(visibleMonth);

    (async () => {
      // Turn on global loading while we load this month
      setLoading(true);

      try {
        // 1) Load the current/selected month first. Mark it as current.
        await loadMonthEvents(monthToLoad, {
          setAsCurrent: true,
          showSpinner: false, // Moved spinner control to this effect
        });

        // 2) Prefetch previous + next (no spinner, don’t overwrite UI)
        await Promise.all([
          loadMonthEvents(addMonths(monthToLoad, -1), {
            setAsCurrent: false,
            showSpinner: false,
          }),
          loadMonthEvents(addMonths(monthToLoad, 1), {
            setAsCurrent: false,
            showSpinner: false,
          }),
        ]);
      } catch (error) {
        if (!cancelled) {
          console.error('[calendar] Month load failed:', error);
        }
      } finally {
        if (!cancelled) {
          // Always turn the spinner off once this cycle is done
          setLoading(false);
        }
      }
    })();

    // Cleanup if the user navigates again before this finishes
    return () => {
      cancelled = true;
    };
  }, [selectedMonth, visibleMonth]);

  // Filter engine for the calendar cells and day list.
  // 1.	remove main events where isFull === true
  // 2.	match calendar day
  // 3.	apply campusFilter
  // 4.	apply selectedStudentType
  // 5.	apply selectedTourFilters (legend tags)
  // Anything failing any filter is excluded.
  const getEventsForDate = (date) => {
    // Debug to see how many events are marked full
    // console.log('[calendar] getEventsForDate raw', {
    //   date,
    //   total: events.length,
    //   full: events.filter(e => e.isFull === true).length,
    // });

    return events
      .filter((event) => event.isFull !== true) // EXCLUDE full events
      .filter((event) => {
        return event.date.toDateString() === date.toDateString();
      })

      .filter((event) => {
        if (!campusFilter) return true;
        const evCampus = normalizeCampusName(event?.campus);
        const cfCampus = normalizeCampusName(campusFilter);
        return evCampus === cfCampus;
      })

      .filter((event) => {
        if (!selectedStudentType) return true;
        if (!event.studentType || event.studentType.length === 0) return false;

        // Normalize both sides before comparing
        const normalize = (str) => str?.toLowerCase().trim();

        return event.studentType.some(
          (type) => normalize(type) === normalize(selectedStudentType),
        );
      })

      .filter((event) => {
        // Tour filter logic
        if (selectedTourFilters.length === 0) return true;
        const legendField = event.legend || [];
        const normalizedLegend = Array.isArray(legendField)
          ? legendField.map((l) => (typeof l === 'string' ? l : l?.value))
          : [legendField?.value || legendField];
        return normalizedLegend.some((tag) =>
          selectedTourFilters.includes(tag),
        );
      });
  };

  // Events for the currently selected day (used in UI and preselect hook)
  const dayEvents = useMemo(
    () => (selectedDate ? getEventsForDate(selectedDate) : []),
    [
      selectedDate,
      events,
      campusFilter,
      selectedStudentType,
      selectedTourFilters,
    ],
  );

  usePreselectFromVisitsRevamp({
    campusFilter,
    hasDateSelection,
    selectedItems,
    setCampusFilter,
    setSelectedDate,
    setHasDateSelection,
    setSelectedItems,
    setSelectedAreaOfInterest,
    allEvents: events,
  });

  // For JS Session Storage - visitsRevamp
  const buildStructuredEventData = () => {
    const structured = {};

    const phoenixFormatter = new Intl.DateTimeFormat('en-CA', {
      timeZone: 'America/Phoenix',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    });

    events.forEach((event) => {
      // Only proceed if the main event is selected
      if (!selectedItems[event.uuid]) return;

      // Use Phoenix local time to get consistent YYYY-MM-DD
      const eventDate = event.dateLuxon.toFormat('yyyy-MM-dd');

      const eventIdTimestamp =
        event.eventid_timestamp || Math.floor(event.date.getTime() / 1000);

      const startTimestamp = Math.floor(event.dateLuxon.toSeconds());
      const endTimestamp = event.endDateLuxon
        ? Math.floor(event.endDateLuxon.toSeconds())
        : startTimestamp + 2 * 60 * 60;

      const from = event.date
        .toLocaleTimeString([], {
          hour: 'numeric',
          minute: '2-digit',
          hour12: true,
        })
        .toLowerCase();

      const to = new Date(event.end_date.getTime())
        .toLocaleTimeString([], {
          hour: 'numeric',
          minute: '2-digit',
          hour12: true,
        })
        .toLowerCase();

      // Build Additional Tour strings only if selected
      const addTours = event.additional_tours
        .map((tour, index) => {
          const key = `${event.uuid}_additional_${index}`;
          if (!selectedItems[key]) return null;
          const [fromUnix, toUnix] = tour.time_unix || [
            startTimestamp + 2 * 60 * 60,
            startTimestamp + 3 * 60 * 60,
          ];

          return `${tour.addtour_eventid}|${fromUnix}|${toUnix}|${tour.title}`;
        })
        .filter(Boolean);

      // Build Barrett Tour under Exp ASU strings only if selected
      const barrettTours = event.barrett_tours
        .map((tour, index) => {
          const key = `${event.uuid}_barrett_${index}`;
          if (!selectedItems[key]) return null;
          const [fromUnix, toUnix] = tour.time_unix || [
            startTimestamp + 3 * 60 * 60,
            startTimestamp + 4 * 60 * 60,
          ];

          return `${tour.eventid}|${fromUnix}|${toUnix}|${tour.display_title}`;
        })
        .filter(Boolean);

      if (!structured[eventDate]) structured[eventDate] = {};
      if (!structured[eventDate][event.campus])
        structured[eventDate][event.campus] = [];

      structured[eventDate][event.campus].push({
        tourtype: 'Regular',
        campus: event.campus,
        eventid: `${event.event_series_id}-${eventIdTimestamp}`,
        eventseriesid: event.event_series_id,
        eventtype: event.event_type || 'Experience ASU',
        vdate: eventDate,
        timestamp: `${startTimestamp}`,
        timestamp2: `${endTimestamp}`,
        from,
        to,
        interest: selectedAreaOfInterest || '',
        addtour: addTours,
        addtour_barrett: barrettTours,
        eventdisplaytitle: event.display_title || event.title,
      });
    });

    return structured;
  };

  const tourLabelMap = {
    inperson: 'In-person walking tour',
    'inperson-academic': 'In-person walking tour with academic fair',
    barrett: 'Barrett tour',
    facility: 'Academic Facility tour',
    selfguided: 'Self-guided tour',
    general: 'Signature event',
  };

  const appliedFilters = useMemo(() => {
    const items = [];

    // Month
    if (selectedMonth) {
      items.push({
        key: 'month',
        label: selectedMonth.toLocaleString('default', {
          month: 'long',
          year: 'numeric',
        }),
        onClear: () => {
          setSelectedMonth(null);
          setIsMonthOpen?.(true);
        },
      });
    }

    // I am a...
    if (selectedStudentType) {
      const match = studentTypeOptions?.find(
        (o) => o.key === selectedStudentType,
      );
      items.push({
        key: 'person',
        label: match?.value || selectedStudentType,
        onClear: () => {
          setSelectedStudentType('');
          // downstream resets
          setSelectedAreaOfInterest('');
          setCampusFilter('');
          setSelectedTourFilters([]);
          setShowTours?.(false);
          setIsPersonOpen?.(true);
          setIsInterestOpen?.(false);
          setIsLocationOpen?.(false);
        },
      });
    }

    // I want to study.../Select your interest
    if (selectedAreaOfInterest) {
      // const label = interestLabels[selectedAreaOfInterest] || String(selectedAreaOfInterest);
      // NEW: use getInterestLabel util which has more robust mapping + fallback (5/12/2026)
      const label = getInterestLabel(selectedAreaOfInterest);
      items.push({
        key: 'interest',
        label,
        onClear: () => {
          setSelectedAreaOfInterest('');
          setCampusFilter('');
          setSelectedTourFilters([]);
          setShowTours?.(false);
          setIsInterestOpen?.(true);
          setIsLocationOpen?.(false);
        },
      });
    }

    // Location
    if (campusFilter) {
      items.push({
        key: 'location',
        label: campusFilter === 'West' ? 'West Valley' : campusFilter,
        onClear: () => {
          setCampusFilter('');
          setSelectedTourFilters([]);
          setShowTours?.(false);
          setIsLocationOpen?.(true);
          setCampusResetToken((t) => t + 1); // clears radio selection
        },
      });
    }

    // Tours
    if (Array.isArray(selectedTourFilters) && selectedTourFilters.length > 0) {
      items.push({
        key: 'tours',
        type: 'tours',
        selected: selectedTourFilters.map((k) => ({
          key: k,
          label: tourLabelMap[k] || k,
        })),
        onClear: () => setSelectedTourFilters([]),
        onClearOne: (k) =>
          setSelectedTourFilters((prev) => prev.filter((t) => t !== k)),
      });
    }
    return items;
  }, [
    selectedMonth,
    selectedStudentType,
    selectedAreaOfInterest,
    campusFilter,
    selectedTourFilters,
  ]);

  // Build tour filter options ONLY from events relevant to current selections (1/20/2026)
  const relevantTourFilters = useMemo(() => {
    let pool = Array.isArray(events) ? events.slice() : [];

    // Exclude full events
    pool = pool.filter((ev) => ev?.isFull !== true);

    // Match selected campus
    if (campusFilter) {
      pool = pool.filter((ev) => {
        const evCampus = normalizeCampusName(ev?.campus);
        const cfCampus = normalizeCampusName(campusFilter);
        return evCampus === cfCampus;
      });
    }

    // Match selected person type
    if (selectedStudentType) {
      const normalize = (s) =>
        String(s ?? '')
          .toLowerCase()
          .trim();
      const selectedNorm = normalize(selectedStudentType);

      pool = pool.filter((ev) => {
        const types = Array.isArray(ev?.studentType) ? ev.studentType : [];
        return types.some((t) => normalize(t) === selectedNorm);
      });
    }

    // Extract legend tags from this pool
    const set = new Set();
    pool.forEach((ev) => {
      const legend = ev?.legend;

      if (Array.isArray(legend)) {
        legend.forEach((item) => {
          if (typeof item === 'string') set.add(item);
          else if (item?.value) set.add(item.value);
        });
      } else if (typeof legend === 'string') {
        set.add(legend);
      } else if (legend?.value) {
        set.add(legend.value);
      }
    });

    // Remove facility only when there are other tour filters available.
    const allFilters = Array.from(set);
    const hasNonFacility = allFilters.some((filter) => filter !== 'facility');
    return hasNonFacility
      ? allFilters.filter((filter) => filter !== 'facility')
      : allFilters;
  }, [events, campusFilter, selectedStudentType]);

  useEffect(() => {
    setSelectedTourFilters((prev) =>
      (prev || []).filter((f) => relevantTourFilters.includes(f)),
    );
  }, [relevantTourFilters]);

  const clearAllFilters = () => {
    setSelectedMonth(null);
    setSelectedStudentType('');
    setSelectedAreaOfInterest('');
    setCampusFilter('');
    setSelectedTourFilters([]);
    setShowTours?.(false);
    setCampusResetToken((t) => t + 1);

    setSelectedItems({});
    setSelectedDate(null);
    setHasDateSelection(false);
    setPersonTouched(false);
    sessionStorage.removeItem('interest'); // Clear persisted interest
    setInterestResetToken((t) => t + 1); // Ensure dropdown re-mounts

    // Reset accordion guidance
    setIsMonthOpen?.(true);
    setIsPersonOpen?.(false);
    setIsInterestOpen?.(false);
    setIsLocationOpen?.(false);
  };

  useEffect(() => {
    populateFromCancelForm(
      setSelectedStudentType,
      setSelectedAreaOfInterest,
      setPersonTouched,
    );
  }, []);

  // ============================
  // DEBUG/SAFETY: detect events missing campus
  // ============================
  useEffect(() => {
    if (!events || events.length === 0) return;

    const bad = events.filter((e) => !e?.campus);
    if (bad.length) {
      console.warn(
        '[calendar] events missing campus',
        bad.map((e) => ({
          uuid: e?.uuid,
          event_series_id: e?.event_series_id,
          title: e?.display_title || e?.title,
        })),
      );
    }
  }, [events]);

  useEffect(() => {
    // any change to the driving inputs means the UI is not ready yet
    setCampusUiReady(false);
  }, [campuses, selectedStudentType, selectedAreaOfInterest]);

  const handleMobileApply = () => {
    // Just close the off-canvas; filters are already applied reactively
    const closeBtn = document.getElementById('closeFilterBtn');
    if (closeBtn) {
      closeBtn.click();
    }
  };

  // NOT USED
  // function loadInstancesForMonth(dt) {
  //   fetchInstancesForMonth(dt);
  // }

  useEffect(() => {
    document.body.classList.toggle('wait-cursor', loading);
    return () => document.body.classList.remove('wait-cursor');
  }, [loading]);

  // Generic info tooltip (hover + keyboard focus)
  const InfoTooltip = ({ children, ariaLabel = 'More info' }) => (
    <span className="vr-tooltip">
      <span
        className="vr-tooltip__icon"
        tabIndex={0}
        role="button"
        aria-label={ariaLabel}
      >
        i
      </span>

      <span className="vr-tooltip__box" role="tooltip">
        {children}
      </span>
    </span>
  );

  // Show campus if we have interest OR if person type is "Other"
  const isOtherPersonType = selectedStudentType === 'Other';
  const canShowCampusOptions =
    !!selectedAreaOfInterest || (personTouched && isOtherPersonType);

  useAutoScrollToCalendarWhenFiltersComplete({
    selectedMonth,
    personTouched,
    selectedStudentType,
    selectedAreaOfInterest,
    campusFilter,
    selectedTourFilters,
    calendarWrapperRef,
    didAutoScrollRef,
  });

  // Added on 3/11/2026.
  const getBarrettDescription = ({ campus, nested = false }) => {
    const campusMap = {
      Tempe: 'tempe',
      'Downtown Phoenix': 'dpc',
      West: 'west',
      'West Valley': 'west',
      Polytechnic: 'poly',
    };

    const campusKey = campusMap[(campus || '').trim()];
    if (!campusKey) return '';

    const key = nested
      ? `barrett_nested_${campusKey}`
      : `barrett_top_${campusKey}`;

    return barrettDescriptions[key] || '';
  };

  // JSX
  return (
    <div className="calendar-container">
      {showGraduatePopup && (
        <div className="graduate-popup-backdrop">
          <div
            className="graduate-popup-dialog"
            role="dialog"
            aria-modal="true"
            aria-label="Graduate program visit information"
          >
            <p className="graduate-popup-message">
              {renderMessageWithMarkdownLink(
                currentPreset.graduate_popup_message ||
                  defaultGraduatePopupMessage,
              )}
            </p>

            <button
              type="button"
              className="graduate-popup-ok"
              onClick={() => setShowGraduatePopup(false)}
            >
              OK
            </button>
          </div>
        </div>
      )}

      <aside
        id="mobileFilter"
        className="offcanvas-calendar"
        aria-hidden="true"
        tabIndex={-1}
      >
        <div className="offcanvas-header">
          <h2 className="mb-0">Filters</h2>
          <button
            id="closeFilterBtn"
            className="btn btn-link"
            aria-label="Close filters"
          >
            ✕
          </button>
        </div>

        {/* MOBILE apply / clear row (hidden on desktop via CSS) */}
        <div className="mobile-apply-bar">
          <button
            type="button"
            className="btn btn-gold rounded-pill me-3"
            onClick={handleMobileApply}
          >
            Apply
          </button>
          <button
            type="button"
            className="btn btn-outline-secondary btn-sm fixed-outline clear-inline"
            onClick={clearAllFilters}
          >
            Clear all
          </button>
        </div>

        <div className="left-col">
          {/* Filters */}
          <fieldset className="mb-2 mt-2" id="gold-filter-indicator">
            <div className="text-muted mb-2">
              This gold button will show the number and type of filters you have
              selected
            </div>
            <GoldFilterIndicator
              appliedFilters={appliedFilters}
              clearAllFilters={clearAllFilters}
            />
          </fieldset>

          {/* Month dropdown list */}
          <details open={isMonthOpen} className="mb-2 filter-accordion">
            <summary className="acc-summary mb-2 fw-semibold">
              <span>1. I want to visit in...</span>
              <span className="acc-icon" aria-hidden="true">
                <i className="fas fa-plus"></i>
                <i className="fas fa-minus"></i>
              </span>
            </summary>
            <div>
              <fieldset className="mb-2">
                <div>
                  {monthOptions.map((date) => {
                    const value = `${date.getFullYear()}-${date.getMonth() + 1}`;
                    const isChecked = selectedMonth
                      ? `${selectedMonth.getFullYear()}-${selectedMonth.getMonth() + 1}` ===
                        value
                      : false;
                    const id = `month-radio-${date.toISOString()}`;
                    return (
                      <div key={date.toISOString()} className="form-check">
                        <input
                          className="form-check-input"
                          type="radio"
                          name="month-select"
                          id={id}
                          value={value}
                          checked={isChecked}
                          onChange={handleMonthChangeAndAdvance}
                        />
                        <label className="form-check-label" htmlFor={id}>
                          {date.toLocaleString('default', {
                            month: 'long',
                            year: 'numeric',
                          })}
                        </label>
                      </div>
                    );
                  })}
                </div>
              </fieldset>
            </div>
          </details>

          {/* Person type */}
          {selectedMonth && (
            <details open={isPersonOpen} className="mb-2 filter-accordion">
              <summary className="acc-summary mb-2 fw-semibold">
                <span>2. I am a...</span>
                <span className="acc-icon" aria-hidden="true">
                  <i className="fas fa-plus"></i>
                  <i className="fas fa-minus"></i>
                </span>
              </summary>
              <div>
                <fieldset className="mb-2">
                  <div>
                    {studentTypeOptions.map((option, index) => {
                      const safeKey = option.key
                        ? option.key.replace(/\s+/g, '-').toLowerCase()
                        : index;
                      const id = `studentType-${safeKey}`;
                      // SHOW NO SELECTION until user picks one (personTouched === false)
                      const checked = personTouched
                        ? selectedStudentType === option.key
                        : false;
                      return (
                        <div key={id} className="form-check">
                          <input
                            className="form-check-input"
                            type="radio"
                            name="studentType"
                            id={id}
                            value={option.key}
                            checked={checked}
                            onChange={() => {
                              selectPersonType(option.key);
                            }}
                            required
                          />
                          <label className="form-check-label" htmlFor={id}>
                            {option.value}
                          </label>
                        </div>
                      );
                    })}
                  </div>
                </fieldset>
              </div>
            </details>
          )}

          {/* Interest Dropdown Component */}
          {personTouched && (
            <details open className="mb-2 filter-accordion">
              <summary className="acc-summary mb-2 fw-semibold">
                <span>3. Select your interest.</span>
                <span className="acc-icon" aria-hidden="true">
                  <i className="fas fa-plus"></i>
                  <i className="fas fa-minus"></i>
                </span>
              </summary>
              {selectedStudentType !== 'Other' && (
                <InterestDropdown
                  key={interestResetToken} // Remounts when token changes
                  selectedStudentType={selectedStudentType}
                  onSelectInterest={handleInterestChangeAndAdvance}
                  // Added on 5/12/2026: pass master list + allowed list for dynamic options
                  masterInterests={calendarInterests}
                  allowedInterests={allowedInterestsByLevel}
                />
              )}
            </details>
          )}

          {/* Campus options component */}
          {/* {selectedAreaOfInterest && ( */}
          {canShowCampusOptions && (
            <section className="mb-2">
              <div className="mb-2 fw-semibold">
                4. Please select which campus location you would like to visit.
              </div>
              {!campusUiReady && (
                <p
                  className="text-muted fst-italic"
                  style={{ marginBottom: 8 }}
                >
                  Retrieving information...
                </p>
              )}

              <div style={{ visibility: campusUiReady ? 'visible' : 'hidden' }}>
                <CampusOptions
                  // remount when token changes
                  key={campusResetToken}
                  selectedStudentType={selectedStudentType}
                  selectedAreaOfInterest={selectedAreaOfInterest}
                  onCampusChange={handleCampusChangeAndShowTours}
                  onReady={() => setCampusUiReady(true)}
                  // keep radios in sync with event details
                  selectedCampus={campusFilter}
                  // Added on 5/12/2026
                  masterCampuses={calendarCampuses}
                  allowedCampuses={allowedCampusesConfig}
                  defaultCampus={defaultCampusConfig}
                  lockCampus={lockCampusConfig}
                />
              </div>
            </section>
          )}

          {/* Tours filter checkboxes - Used to be called "Legend toggle" */}
          {showTours && (
            <section className="mb-2">
              <div className="mb-2 fw-semibold">
                5. Select the type of tour you would like.
              </div>

              <TourOptions
                availableTourFilters={relevantTourFilters}
                selectedTourFilters={selectedTourFilters}
                onFilterChange={handleTourFilterChange}
              />
            </section>
          )}
        </div>
        {/* END OF left-col */}
      </aside>

      <div id="offcanvasBackdrop" className="offcanvas-backdrop" hidden />

      <div className="right-col">
        {/* Wait cursor + small message while fetching */}
        <WaitCursor active={loading} message="Loading events..." />
        <div className="wrap w-100 d-flex justify-content-end">
          <button
            id="openFilterBtn"
            className="btn btn-dark mb-3"
            aria-controls="mobileFilter"
            aria-expanded="false"
          >
            Schedule a tour &gt;
          </button>
        </div>
        {/* Calendar */}
        <div className="calendar-wrapper" ref={calendarWrapperRef}>
          {/* Month Year */}
          <h2>
            <span className="highlight-black">
              {visibleMonth.toLocaleString('default', {
                month: 'long',
                year: 'numeric',
              })}
            </span>
          </h2>

          <Calendar
            value={selectedDate}
            locale="en-US"
            activeStartDate={visibleMonth}
            onChange={(date) => {
              const hasEvents = getEventsForDate(date).length > 0;
              if (!hasEvents) return;
              setSelectedDate(date);
              setHasDateSelection(true);
            }}
            onActiveStartDateChange={handleCalendarNavigation}
            tileContent={() => null}
            tileDisabled={({ date, view }) => {
              if (view !== 'month') return false;
              return getEventsForDate(date).length === 0;
            }}
            tileClassName={({ date }) => {
              const isSameDay = (d1, d2) =>
                d1.getFullYear() === d2.getFullYear() &&
                d1.getMonth() === d2.getMonth() &&
                d1.getDate() === d2.getDate();

              const dayEvents = getEventsForDate(date);
              let classes = [];

              if (dayEvents.length <= 0) {
                classes.push('grayed-out-date'); // Gray if no events
              }

              if (
                hasDateSelection &&
                selectedDate &&
                isSameDay(date, selectedDate)
              ) {
                classes.push('selected-date'); // Highlight selected date
              }

              return classes.join(' ');
            }}
            next2Label={null} // Removed > button
            prev2Label={null} // Removed < button
          />

          {/* Legend */}
          {/* <div className="calendar-legend" aria-label="Calendar legend">
            <div className="legend-item">
              <span className="legend-swatch legend-swatch--available" aria-hidden="true"></span>
              <span className="legend-label">Events available</span>
            </div>

            <div className="legend-item">
              <span className="legend-swatch legend-swatch--selected" aria-hidden="true"></span>
              <span className="legend-label">Selected</span>
            </div>
          </div> */}

          {/* Manual arrow navigation moved below the calendar */}
          <div className="calendar-nav-buttons">
            <button onClick={handlePrevMonth}>&lt;</button>
            <button onClick={handleNextMonth}>&gt;</button>
          </div>

          {/* Overlay appears until Location is selected */}
          {!campusFilter && (
            <div className="calendar-overlay gray-2-bg">
              <div className="overlay-content">
                <hr className="gold-line" />
                <p>
                  To access the calendar, simply input a few pieces of
                  information on the left.
                </p>
                <ol>
                  <li>
                    <span className="highlight">I want to visit in...</span>
                  </li>
                  <li>
                    <span className="highlight">I am a...</span>
                  </li>
                  <li>
                    <span className="highlight">Select your interest.</span>
                  </li>
                </ol>
              </div>
            </div>
          )}
        </div>{' '}
        {/* END OF <div className="calendar-wrapper"> */}
        {/* Visit bucket taxonomy description */}
        <VisitBucketTaxDescription
          selectedAreaOfInterest={selectedAreaOfInterest}
          isUndergrad={
            selectedStudentType !== 'considering graduate school' &&
            selectedStudentType !== 'a high school counselor'
          }
        />
        {/* Event details */}
        {hasDateSelection && (
          <div className="event-list">
            <h3>
              Events on{' '}
              {selectedDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: '2-digit',
              })}
            </h3>

            {(() => {
              // const dayEvents = getEventsForDate(selectedDate);

              // if (dayEvents.length === 0) {
              if (!dayEvents || dayEvents.length === 0) {
                return (
                  <p className="no-events-msg alert alert-warning">
                    There are no events on the selected date with the current
                    filters. Please adjust the filters and try again.
                  </p>
                );
              }

              return (
                <ul>
                  {dayEvents.map((event) => {
                    const isComingSoon = event.privacy === 'Coming soon';
                    let publishDateLabel = '';
                    if (isComingSoon && event.publish_date) {
                      const pd = DateTime.fromISO(event.publish_date, {
                        zone: 'America/Phoenix',
                      });
                      publishDateLabel = pd.isValid
                        ? pd.toLocaleString({ month: 'long', day: 'numeric' }) // e.g. "November 27"
                        : event.publish_date;
                    }

                    return (
                      <li key={event.uuid}>
                        <label>
                          {/* Coming soon banner */}
                          {isComingSoon && (
                            <div className="coming-soon-banner">
                              {publishDateLabel
                                ? `Coming soon - registration opens ${publishDateLabel}`
                                : 'Coming soon'}
                            </div>
                          )}
                          <h3 className={isComingSoon ? 'mt-0' : ''}>
                            {/* Only show checkbox if NOT coming soon */}
                            {!isComingSoon && (
                              <input
                                type="checkbox"
                                className="form-check-input"
                                checked={!!selectedItems[event.uuid]}
                                onChange={() =>
                                  handleCheckboxChange(event.uuid)
                                }
                              />
                            )}{' '}
                            {event.display_title}
                          </h3>
                          {event.campus === 'West'
                            ? 'West Valley'
                            : event.campus}{' '}
                          campus
                          <br />
                          {/* Hide time for self-guided tours */}
                          {Array.isArray(event.legend) &&
                          event.legend.includes('selfguided') ? null : (
                            <>
                              From{' '}
                              {event.date
                                .toLocaleTimeString([], {
                                  hour: 'numeric',
                                  minute: '2-digit',
                                  hour12: true,
                                })
                                .toLowerCase()}
                              {' to '}
                              {(event.end_date
                                ? new Date(event.end_date).toLocaleTimeString(
                                    [],
                                    {
                                      hour: 'numeric',
                                      minute: '2-digit',
                                      hour12: true,
                                    },
                                  )
                                : new Date(
                                    event.date.getTime() + 2 * 60 * 60 * 1000,
                                  ).toLocaleTimeString([], {
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true,
                                  })
                              ).toLowerCase()}
                              {/* Info popup for top-level Barrett tour event. Changed on 3/11/2026. */}
                              {event.type === 'barrett' && (
                                <span style={{ margin: '6px 0' }}>
                                  <BarrettDescription
                                    enabled={true}
                                    description={getBarrettDescription({
                                      campus: event.campus,
                                      nested: false,
                                    })}
                                    ariaLabel="More info about Barrett tour"
                                  />
                                </span>
                              )}
                              <br />
                            </>
                          )}
                          <ColoredDots event={event} />
                        </label>

                        {/* Only show edit links if user is privileged */}
                        {isPrivileged && (
                          <>
                            <br />
                            <a
                              href={`/events/series/${event.event_series_id}/edit`}
                            >
                              Edit Event Series
                            </a>
                            <br />
                            <a
                              href={`/events/${event.drupal_internal_id}/edit`}
                            >
                              Edit Event Instance
                            </a>
                          </>
                        )}

                        <EventDescription
                          html={
                            selectedItems[event.uuid]
                              ? event.event_description_html
                              : null
                          }
                        />

                        <div className="nested-events">
                          {/* Additional Tours */}
                          {selectedItems[event.uuid] &&
                            (() => {
                              //const filteredTours = event.additional_tours.filter(tour => {
                              // if (!selectedAreaOfInterest) return true;
                              // console.log("tour college:", tour.college);
                              // console.log("selectedAreaOfInterest:", Number(selectedAreaOfInterest));
                              // return shouldShowAdditionalTour(Number(selectedAreaOfInterest), tour.college);

                              // Guard against undefined / non-array additional_tours
                              const toursArray = Array.isArray(
                                event.additional_tours,
                              )
                                ? event.additional_tours
                                : event.additional_tours
                                  ? [event.additional_tours]
                                  : [];

                              const filteredTours = toursArray.filter(
                                (tour) => {
                                  if (!selectedAreaOfInterest) return true;

                                  return shouldShowAdditionalTour(
                                    Number(selectedAreaOfInterest),
                                    tour.college,
                                  );
                                },
                              );

                              return filteredTours.length > 0 ? (
                                <div>
                                  {/* <strong style={{color: selectedItems[event.uuid] ? 'inherit' : 'gray'}}>
                                  Additional Tours:
                                </strong> */}
                                  <ul>
                                    {filteredTours.map((tour, index) => {
                                      const needRB =
                                        tour.need_radio_button === true ||
                                        tour.need_radio_button === 'true';
                                      return (
                                        <li key={index}>
                                          <label
                                            style={{
                                              opacity: selectedItems[event.uuid]
                                                ? 1
                                                : 0.5,
                                            }}
                                          >
                                            {needRB && (
                                              <input
                                                type="checkbox"
                                                className="form-check-input"
                                                checked={
                                                  !!selectedItems[
                                                    `${event.uuid}_additional_${index}`
                                                  ]
                                                }
                                                onChange={() =>
                                                  handleCheckboxChange(
                                                    `${event.uuid}_additional_${index}`,
                                                  )
                                                }
                                                disabled={
                                                  !selectedItems[event.uuid]
                                                }
                                              />
                                            )}{' '}
                                            {tour.title} ({tour.time})
                                          </label>
                                        </li>
                                      );
                                    })}
                                  </ul>
                                </div>
                              ) : null;
                            })()}

                          {/* Barrett Tours under Exp ASU with filter and checkbox */}
                          {selectedItems[event.uuid] &&
                            (() => {
                              // Guard barrett_tours to avoid a similar error
                              const barrettArray = Array.isArray(
                                event.barrett_tours,
                              )
                                ? event.barrett_tours
                                : event.barrett_tours
                                  ? [event.barrett_tours]
                                  : [];

                              const filteredBarrettTours = barrettArray.filter(
                                (tour) => {
                                  const visitorTypes = tour.visitorTypes || [];
                                  return (
                                    !selectedStudentType ||
                                    visitorTypes.includes(selectedStudentType)
                                  );
                                },
                              );

                              return filteredBarrettTours.length > 0 ? (
                                <div>
                                  {/* <strong style={{color: selectedItems[event.uuid] ? 'inherit' : 'gray'}}>
                                  Barrett Tours:
                                </strong> */}
                                  <ul>
                                    {filteredBarrettTours.map((tour, index) => (
                                      <li key={index}>
                                        <label
                                          style={{
                                            opacity: selectedItems[event.uuid]
                                              ? 1
                                              : 0.5,
                                          }}
                                        >
                                          <input
                                            type="checkbox"
                                            className="form-check-input"
                                            checked={
                                              !!selectedItems[
                                                `${event.uuid}_barrett_${index}`
                                              ]
                                            }
                                            onChange={() =>
                                              handleCheckboxChange(
                                                `${event.uuid}_barrett_${index}`,
                                              )
                                            }
                                            disabled={
                                              !selectedItems[event.uuid]
                                            }
                                          />{' '}
                                          {tour.display_title} ({tour.time}){' '}
                                          {/* Show i icon only for Barrett tour. Changed on 3/11/2026. */}
                                          {tour.eventType ===
                                            'Barrett tour' && (
                                            <BarrettDescription
                                              enabled={true}
                                              description={getBarrettDescription(
                                                {
                                                  campus: event.campus,
                                                  nested: true,
                                                },
                                              )}
                                              ariaLabel="More info about Barrett tour"
                                            />
                                          )}
                                        </label>
                                      </li>
                                    ))}
                                  </ul>
                                </div>
                              ) : null;
                            })()}
                        </div>
                      </li>
                    );
                    {
                      /* END OF return */
                    }
                  })}
                </ul>
              );
            })()}
          </div>
        )}
        {/* You have selected block */}
        <YouHaveSelected
          events={events}
          selectedItems={selectedItems}
          onRemove={handleRemoveSelection}
        />
        {/* Continue button */}
        {Object.values(selectedItems).some(Boolean) && (
          <div className="continue-wrap">
            <p className="continue-message">
              You may return to the filter to select another campus and date to
              add a visit. If you are ready to confirm this visit, press{' '}
              <strong>Continue</strong>.
            </p>

            <button
              onClick={() => {
                const structuredData = buildStructuredEventData();
                sessionStorage.setItem(
                  'visitsRevamp',
                  JSON.stringify(structuredData),
                );
                const urlParams = buildCancelUrlParams(); // Get URL params
                // window.location.href = "/registration-form-v2" + urlParams; {/* Redirect to the Visit Revamp form */}
                // Choose webform based on person type
                const isOtherPersonType = selectedStudentType === 'Other';
                const targetFormPath = isOtherPersonType
                  ? '/registration-form-0-v2?ptype=Other'
                  : '/registration-form-v2';

                // Add conf_email_nid value from preset. Changed on 6/1/2026.
                const redirectParams = [];

                const cancelParams = String(urlParams || '').replace(/^\?/, '');
                if (cancelParams) {
                  redirectParams.push(cancelParams);
                }

                const confirmationEmailNid = currentPreset.conf_email_nid || '';
                if (confirmationEmailNid) {
                  redirectParams.push(
                    `conf-email-nid=${encodeURIComponent(confirmationEmailNid)}`,
                  );
                }

                // If target already has "?", append urlParams with "&" (not another "?")
                const joinedParams = redirectParams.length
                  ? `${targetFormPath.includes('?') ? '&' : '?'}${redirectParams.join('&')}`
                  : '';

                window.location.href = targetFormPath + joinedParams;
              }}
              className="btn btn-gold"
            >
              Continue
            </button>
          </div>
        )}
      </div>
      {/* END OF right-col */}
    </div>
  );
}

export default CalendarPage;
