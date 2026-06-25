// Capacity.js
// This module provides a function to check event capacity status for top-level events (Exp ASU events, Barrett solo events)

// export async function checkEventCapacities(events) {
//   const updatedEvents = await Promise.all(
//     events.map(async (event) => {
//       if (!event.eventid_timestamp || !event.event_series_id) return event;

//       const eventId = `${event.event_series_id}-${event.eventid_timestamp}`;
//       // console.log("eventId: ", eventId);
//       // console.log("event: ", event);
//       const overwrite_capacity = event.overwrite_capacity || '';
//       // console.log("overwrite_capacity: ", overwrite_capacity);

//       // Normalize overwrite_capacity flag (handles true/false, 1/0, "1"/"0", "on")
//       const overwriteCapacity =
//         overwrite_capacity === true ||
//         overwrite_capacity === 1 ||
//         overwrite_capacity === "1" ||
//         String(overwrite_capacity || "").toLowerCase() === "on";

//       // Series-level capacity from MonthMetadata (CalendarPage.js sets this)
//       const rawSeriesCapacity = event.capacity_series ?? null;

//       // Instance-level capacity from the eventinstance payload (field_capacity_event_instance)
//       const rawInstanceCapacity = event.capacity_instance ?? null;

//       // Helper to normalize to integer or null
//       const toIntOrNull = (value) => {
//         if (value === null || value === undefined || value === "") return null;
//         const n = parseInt(value, 10);
//         return Number.isNaN(n) ? null : n;
//       };

//       // Use series capacity (from MonthMetadata) + instance capacity (from Field Inheritance)
//       const seriesCapacity = toIntOrNull(rawSeriesCapacity);  // from Event Series
//       const instanceCapacity = toIntOrNull(rawInstanceCapacity); // from Event Instance

//       // let capacity = '';
//       // Pick final capacity based on overwrite flag
//       let finalCapacity = null;

//       if (overwriteCapacity) {
//         // Overwrite flag ON → prefer instance capacity, fall back to series if needed
//         finalCapacity = instanceCapacity ?? seriesCapacity;
//       } else {
//         // Overwrite flag OFF → use series capacity (Field Inheritance fallback),
//         // but if for some reason series is missing, fall back to instance.
//         finalCapacity = seriesCapacity ?? instanceCapacity;
//       }

//       // If we still don't know capacity, don't mark the event as full.
//       if (finalCapacity === null) {
//         return {
//           ...event,
//           isFull: false,
//           registeredCount: 0,
//         };
//       }


//       try {
//         const response = await fetch(`/visit-revamp-api/student-registrations-count?id=${eventId}`);
//         const data = await response.json();
//         // const registered = data.count || 0;
//         const registered = toIntOrNull(data.count) ?? 0;

//         return {
//           ...event,
//           isFull: registered >= finalCapacity,
//           registeredCount: registered,
//           finalCapacity,
//         };
//       } catch (error) {
//         console.error(`Error checking capacity for event ${eventId}:`, error);
//         return {
//           ...event,
//           isFull: false,
//           registeredCount: 0,
//         };
//       }
//     })
//   );

//   return updatedEvents;
// }
export async function checkEventCapacities(events, { signal } = {}) {
  return applyCapacityWithBatchCounts(events, { signal });
}

// (1/20/2026)
// Batch version
function toIntOrNull(value) {
  if (value === null || value === undefined || value === "") return null;
  const n = parseInt(value, 10);
  return Number.isNaN(n) ? null : n;
}

function normalizeOverwriteFlag(overwrite_capacity) {
  return (
    overwrite_capacity === true ||
    overwrite_capacity === 1 ||
    overwrite_capacity === "1" ||
    String(overwrite_capacity || "").toLowerCase() === "on"
  );
}

function computeFinalCapacity(event) {
  const overwriteCapacity = normalizeOverwriteFlag(event.overwrite_capacity || "");

  const seriesCapacity = toIntOrNull(event.capacity_series ?? null);
  const instanceCapacity = toIntOrNull(event.capacity_instance ?? null);

  if (overwriteCapacity) return instanceCapacity ?? seriesCapacity;
  return seriesCapacity ?? instanceCapacity;
}

/**
 * Apply batch registration counts to events and mark isFull.
 * countsMap can be { [id]: count } or Map(id -> count)
 */
export function applyCountsToCapacity(events, countsMap) {
  const getCount = (id) => {
    if (!id) return 0;

    if (countsMap && typeof countsMap.get === "function") {
      return toIntOrNull(countsMap.get(id)) ?? 0;
    }
    return toIntOrNull(countsMap?.[id]) ?? 0;
  };

  return (events || []).map((event) => {
    const eventId =
      event.eventid ||
      (event.event_series_id && event.eventid_timestamp
        ? `${event.event_series_id}-${event.eventid_timestamp}`
        : null);

    const finalCapacity = computeFinalCapacity(event);
    const registered = getCount(eventId);

    const isFull = finalCapacity === null ? false : registered >= finalCapacity;

    return {
      ...event,
      eventid: eventId ?? event.eventid,
      registeredCount: registered,
      finalCapacity,
      isFull,
    };
  });
}

async function fetchStudentRegistrationsCountBatch(ids, { signal } = {}) {
  if (!Array.isArray(ids) || ids.length === 0) return {};

  const res = await fetch("/visit-revamp-api/student-registrations-count-batch", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    body: JSON.stringify({ ids }),
    signal,
  });

  if (!res.ok) {
    const txt = await res.text().catch(() => "");
    throw new Error(`Batch counts failed: ${res.status} ${txt}`);
  }

  const data = await res.json();
  return data.counts || data || {};
}

export async function applyCapacityWithBatchCounts(events, { signal } = {}) {
  if (!Array.isArray(events) || events.length === 0) return events;

  const ids = Array.from(
    new Set(
      events
        .map((e) =>
          e.eventid ||
          (e.event_series_id && e.eventid_timestamp
            ? `${e.event_series_id}-${e.eventid_timestamp}`
            : null)
        )
        .filter(Boolean)
    )
  );

  if (ids.length === 0) return events;

  const countsMap = await fetchStudentRegistrationsCountBatch(ids, { signal });
  return applyCountsToCapacity(events, countsMap);
}