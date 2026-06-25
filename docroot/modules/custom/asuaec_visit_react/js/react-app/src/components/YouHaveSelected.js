import React from 'react';

// Added: formatters for Phoenix timezone
const phoenixFormatter = new Intl.DateTimeFormat('en-US', {
  // timeZone: 'America/Phoenix',
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: '2-digit'
});

const phoenixDateOnly = new Intl.DateTimeFormat('en-CA', {
  // timeZone: 'America/Phoenix',
  year: 'numeric',
  month: '2-digit',
  day: '2-digit'
});

function YouHaveSelected({ events, selectedItems, onRemove }) {
  const grouped = {};

  events.forEach(event => {
    // Only include events where the top-level event checkbox is selected
    if (!selectedItems[event.uuid]) return;

    // const dateStr = event.date.toISOString().split('T')[0]; // Format: YYYY-MM-DD
    const dateStr = phoenixDateOnly.format(event.date); // Format: YYYY-MM-DD in Phoenix time

    if (!grouped[dateStr]) grouped[dateStr] = {};
    if (!grouped[dateStr][event.campus]) grouped[dateStr][event.campus] = [];

    const selectedAdditional = event.additional_tours?.filter((tour, i) =>
      selectedItems[`${event.uuid}_additional_${i}`]
    );

    const selectedBarrett = event.barrett_tours?.filter((tour, i) =>
      selectedItems[`${event.uuid}_barrett_${i}`]
    );

    grouped[dateStr][event.campus].push({
      uuid: event.uuid, // <-- keep uuid so we can remove it
      type: "Regular",
      title: event.title,
      time: event.date,
      event_type: event.event_type,
      barrett: selectedBarrett,
      additional: selectedAdditional,
      display_title: event.display_title
    });
  });

  const sortedDates = Object.keys(grouped).sort(
    (a, b) => new Date(a) - new Date(b)
  );

  if (sortedDates.length === 0) return null;

  return (
    <div className="you-have-selected">
      <h4>You have selected the following:</h4>
      {sortedDates.map(date => (
        <div key={date}>
          {/*<strong>{new Date(`${date}T12:00:00`).toLocaleDateString('en-US', { // Use noon to avoid timezone offset*/}
          {/*  weekday: 'long',*/}
          {/*  year: 'numeric',*/}
          {/*  month: 'long',*/}
          {/*  day: '2-digit'*/}
          {/*})}</strong>*/}
          <strong>{phoenixFormatter.format(new Date(`${date}T00:00:00`))}</strong>
          {Object.entries(grouped[date]).map(([campus, eventList]) => (
            <div key={campus} style={{paddingLeft: '1em'}}>
              <div>{campus === 'West' ? 'West Valley' : campus} campus</div>
              <ul>
                {eventList
                  .sort((a, b) => a.time - b.time)
                  .map((evt, i) => (
                    <li key={i}>
                      {evt.display_title}{" "}
                      {/* Show time only if NOT Self-guided campus Tour */}
                      {evt.event_type !== 'Self-guided campus Tour' && (
                        <>
                          {" - "}
                          {evt.time.toLocaleTimeString([], {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                          }).toLowerCase()}
                        </>
                      )}

                      {/* "Remove" button */}
                      {typeof onRemove === 'function' && (
                        <button
                          type="button"
                          className="btn btn-link btn-gray btn-md ms-2"
                          onClick={() => onRemove(evt.uuid)}
                        >
                          Remove
                        </button>
                      )}

                      {/* Nested Barrett tours (optional sessions) */}
                      {evt.barrett?.length > 0 && (
                        <ul>
                          {evt.barrett.map((tour, j) => (
                            <li key={j}>
                              Optional session — {tour.display_title} ({tour.time})
                            </li>
                          ))}
                        </ul>
                      )}

                      {/* Nested Additional tours (optional sessions) */}
                      {evt.additional?.length > 0 && (
                        <ul>
                          {evt.additional.map((tour, j) => (
                            <li key={j}>
                              Optional session — {tour.title} ({tour.time})
                            </li>
                          ))}
                        </ul>
                      )}
                    </li>
                  ))}
              </ul>
            </div>
          ))}
        </div>
      ))}
    </div>
  );
}

export default YouHaveSelected;
