// FiltersComplete.js
import { useEffect, useMemo } from "react";

/**
 * Keep the "Other flow" person-type logic in one place.
 * Adjust labels here if the radio labels change.
 */
export function isOtherPersonType(selectedStudentType) {
  return (
    selectedStudentType === "Other"
  );
}

/**
 * areFiltersComplete function: returns true/false
 */
export function areFiltersComplete({
  selectedMonth,
  personTouched,
  selectedStudentType,
  selectedAreaOfInterest,
  campusFilter,
  selectedTourFilters,
}) {
  const otherFlow = isOtherPersonType(selectedStudentType);

  return (
    !!selectedMonth &&
    !!personTouched &&
    !!selectedStudentType &&
    (otherFlow ? true : !!selectedAreaOfInterest) &&
    !!campusFilter &&
    Array.isArray(selectedTourFilters) &&
    selectedTourFilters.length > 0
  );
}

function getASUHeaderOffset() {
  // ASU sticky header
  const header =
    document.querySelector('.header-main') ||
    document.querySelector('#ws2HeaderContainer');

  if (!header) return 0;

  const rect = header.getBoundingClientRect();
  return rect.height || 0;
}

/**
 * Hook that:
 *  - computes filtersComplete
 *  - scrolls once when filters become complete
 *  - resets "did scroll" when filters become incomplete again
 */
export function useAutoScrollToCalendarWhenFiltersComplete({
  selectedMonth,
  personTouched,
  selectedStudentType,
  selectedAreaOfInterest,
  campusFilter,
  selectedTourFilters,
  calendarWrapperRef,
  didAutoScrollRef,
  enabled = true,
}) {
  const filtersComplete = useMemo(() => {
    return areFiltersComplete({
      selectedMonth,
      personTouched,
      selectedStudentType,
      selectedAreaOfInterest,
      campusFilter,
      selectedTourFilters,
    });
  }, [
    selectedMonth,
    personTouched,
    selectedStudentType,
    selectedAreaOfInterest,
    campusFilter,
    selectedTourFilters,
  ]);

  useEffect(() => {
    if (!enabled) return;

    if (filtersComplete && !didAutoScrollRef.current) {
      window.requestAnimationFrame(() => {
        if (!calendarWrapperRef.current) return;

        const headerOffset = getASUHeaderOffset();
        const calendarTop =
          calendarWrapperRef.current.getBoundingClientRect().top +
          window.pageYOffset;

        window.scrollTo({
          top: calendarTop - headerOffset - 16,
          behavior: "smooth",
        });
      });

      didAutoScrollRef.current = true;
    }

    if (!filtersComplete) {
      didAutoScrollRef.current = false;
    }
  }, [enabled, filtersComplete, calendarWrapperRef, didAutoScrollRef]);

  return { filtersComplete };
}