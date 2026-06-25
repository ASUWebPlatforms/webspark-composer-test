// CheckCapacityForAddTour.js
export async function checkCapacityForAddTour(events) {
  if (!Array.isArray(events)) return [];

  const updatedEvents = await Promise.all(
    events.map(async (event) => {
      // Normalize null/undefined items so callers can safely filter/use them
      if (!event) return { isFull: false, registeredCount: 0 };

      const hasRequired =
        !!event?.addtour_eventid_timestamp &&
        !!event?.parent_event_series_id &&
        !!event?.id;

      if (!hasRequired) {
        return { ...event, isFull: false, registeredCount: 0 };
      }

      // Build the additional tour-specific event ID
      const eventId = `${event.parent_event_series_id}-${event.id}-${event.addtour_eventid_timestamp}`;

      try {
        // Fetch resolved capacity and current registrations in parallel
        const capUrl = `/visit-revamp-api/addtour-resolved-capacity/${encodeURIComponent(eventId)}`;
        const regUrl = `/visit-revamp-api/student-registrations-count-addtour?id=${encodeURIComponent(eventId)}`;

        const [capRes, regRes] = await Promise.all([
          fetch(capUrl, { credentials: 'same-origin' }),
          fetch(regUrl, { credentials: 'same-origin' }),
        ]);

        // Handle capacity response
        let capJson = {};
        if (capRes.ok) {
          try {
            capJson = await capRes.json();
          } catch (e) {
            console.error(`Invalid JSON from resolved capacity for ${eventId}:`, e);
          }
        } else {
          console.error(`Resolved capacity request failed (${capRes.status}) for ${eventId}`);
        }

        // capacity precedence: resolved_capacity → base_capacity → null
        const resolvedCap =
          Number.isFinite(capJson?.resolved_capacity)
            ? capJson.resolved_capacity
            : (Number.isFinite(capJson?.base_capacity) ? capJson.base_capacity : null);

        const capacitySource = capJson?.source || (Number.isFinite(resolvedCap) ? 'base' : 'unknown');

        // Handle registrations response
        let regJson = {};
        if (regRes.ok) {
          try {
            regJson = await regRes.json();
          } catch (e) {
            console.error(`Invalid JSON from registrations for ${eventId}:`, e);
          }
        } else {
          console.error(`Registrations request failed (${regRes.status}) for ${eventId}`);
        }

        const registered = Number.isFinite(regJson?.count) ? regJson.count : 0;

        // Determine fullness only when we have a numeric capacity
        const hasNumericCapacity = Number.isFinite(resolvedCap);
        const isFull = hasNumericCapacity ? registered >= resolvedCap : false;

        return {
          ...event,
          capacity: hasNumericCapacity ? resolvedCap : null,
          capacitySource,
          registeredCount: registered,
          isFull,
        };
      } catch (error) {
        console.error(`Error checking addtour capacity/regs for ${eventId}:`, error);
        return {
          ...event,
          capacity: null,
          capacitySource: 'error',
          registeredCount: 0,
          isFull: false,
        };
      }
    })
  );

  return updatedEvents;
}
