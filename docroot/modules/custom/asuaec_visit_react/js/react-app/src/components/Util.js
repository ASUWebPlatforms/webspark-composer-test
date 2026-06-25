// Util.js

/**
 * Capture URL params and return it.
 * Use it to attach the URL params when going from Calendar page to the form page.
 */
export function buildCancelUrlParams() {
  // Get URL param - ?aid={attendee_id}&eventid={event_id}
  const urlParams = new URLSearchParams(window.location.search);
  let cancel_attendee_id = urlParams.get('c-aid');
  let cancel_eventid = urlParams.get('c-eid');
  let cancel_sid = urlParams.get('c-sid');
  let cancel_urlParam = "?c-aid=" + cancel_attendee_id + "&c-eid=" + cancel_eventid + "&c-sid=" + cancel_sid;

  return cancel_urlParam ? `?${urlParams}` : '';
}
