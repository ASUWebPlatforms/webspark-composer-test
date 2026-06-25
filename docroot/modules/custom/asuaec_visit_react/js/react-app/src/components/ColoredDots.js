// src/components/ColoredDots.js
import React from 'react';

const ColoredDots = ({ event }) => {
  // Normalize legend toggles (Drupal sends as array of values or objects)
  const legendToggles = Array.isArray(event.legend)
    ? event.legend.map((item) =>
        typeof item === 'string'
          ? item.toLowerCase()
          : String(item.value).toLowerCase(),
      )
    : [];

  const hasInperson = legendToggles.includes('inperson');
  const hasInpersonAcademic = legendToggles.includes('inperson-academic');
  const hasFacility = legendToggles.includes('facility');
  const hasBarrett = legendToggles.includes('barrett');
  const hasSelfguided = legendToggles.includes('selfguided');
  const hasSignature = legendToggles.includes('generic');
  const hasClassroom = legendToggles.includes('classroom');

  return (
    <span className="event-dots" aria-label="event indicators">
      {hasInperson && (
        <span
          className="dot dot--inperson"
          title="In-person walking tour without academic fair"
          aria-label="In-person walking tour without academic fair"
        />
      )}
      {hasInpersonAcademic && (
        <span
          className="dot dot--inperson-academic"
          title="In-person walking tour with academic fair"
          aria-label="In-person walking tour with academic fair"
        />
      )}
      {hasFacility && (
        <span
          className="dot dot--facility"
          title="Academic facility tour"
          aria-label="Academic facility tour"
        />
      )}
      {hasBarrett && (
        <span
          className="dot dot--barrett"
          title="Barrett, The Honors College information session and tour"
          aria-label="Barrett, The Honors College information session and tour"
        />
      )}
      {hasSelfguided && (
        <span
          className="dot dot--selfguided"
          title="Self-guided tour"
          aria-label="Self-guided tour"
        />
      )}
      {hasSignature && (
        <span
          className="dot dot--signature"
          title="Signature event"
          aria-label="Signature event"
        />
      )}
      {hasClassroom && (
        <span
          className="dot dot--classroom"
          title="Classroom visit"
          aria-label="Classroom visit"
        />
      )}
    </span>
  );
};

export default ColoredDots;
