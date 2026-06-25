import React from 'react';

/**
 * Renders Event Series field_event_description_html.
 * (Sanitized on the API side)
 */
export default function EventDescription({ html }) {
  if (!html) return null;

  return (
    <div
      className="event-description mt-2"
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
}