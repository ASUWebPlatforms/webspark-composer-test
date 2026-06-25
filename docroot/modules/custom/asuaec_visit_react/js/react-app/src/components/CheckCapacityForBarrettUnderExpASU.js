// CheckCapacityForBarrettUnderExpASU.js
import { checkEventCapacities } from './Capacity';

export async function checkCapacityForBarrettUnderExpASU(tourEvents) {

  // Assume each `tourEvent` has event_series_id and eventid_timestamp
  const updatedTours = await checkEventCapacities(tourEvents);
  return updatedTours;
}
