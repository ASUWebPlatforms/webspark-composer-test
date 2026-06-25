// Mapping of Visit Bucket (taxonomy term ID) to associated college codes
const collegeMapByBucket = {
  25: ['UGLA', 'UGPP', 'UGGF', 'UGAS', 'UGHI'],
  26: ['UGLA', 'UGHI', 'UGES'],
  27: ['UGBA', 'UGTB', 'UGES', 'UGLA', 'UGNH', 'UGPP', 'UGNU', 'UGCS'],
  28: ['UGLA', 'UGBA', 'UGTB', 'UGAS', 'UGLS', 'UGCS', 'UGHI'],
  29: ['UGGF', 'UGNH', 'UGLS', 'UGES', 'UGAS', 'UGLA', 'UGBA'],
  30: ['UGLA', 'UGPP', 'UGAS'],
  31: ['UGLA', 'UGES'],
  32: ['UGTE', 'UGLA', 'UGAS'],
  33: ['UGES'], // Engineering
  34: ['UGUC', 'UGAS', 'UGBA', 'UGTE'],
  72: ['UGHI'],
  73: ['UGHI', 'UGCS', 'UGLA'],
  35: ['UGHI', 'UGAS'],
  76: ['UGTB'],
  36: ['UGLS', 'UGNU', 'UGNH', 'UGES', 'UGPP', 'UGAS', 'UGLA'],
  37: ['UGLS', 'UGAS', 'UGLA', 'UGGF'],
  38: ['UGCS', 'UGNH', 'UGPP'],
  39: ['UGNU'],
  40: ['UGBA', 'UGHI', 'UGTE', 'UGES', 'UGGF', 'UGNH', 'UGLS', 'UGAS', 'UGCS', 'UGLA', 'UGNU', 'UGPP', 'UGTB'],
  41: ['UGHI', 'UGCS', 'UGLA'],
  42: ['UGAS', 'UGLA', 'UGPP', 'UGES'],
  43: ['UGGF', 'UGLS', 'UGAS', 'UGLA', 'UGPP', 'UGBA'],
  44: ['UGLS', 'UGLA', 'UGES', 'UGNH', 'UGAS'],
  45: ['UGBA', 'UGPP', 'UGCS', 'UGNH'],
  46: ['UGGF', 'UGBA', 'UGES', 'UGPP', 'UGAS', 'UGLS', 'UGLA'],
};

/**
 * Determine whether an additional tour should be displayed for the selected Visit Bucket.
 * @param {number|string} visitBucketTid - The selected taxonomy term ID for Visit Bucket.
 * @param {string} tourCollegeCode - The `field_college` from the additional tour (e.g., 'UGES').
 * @returns {boolean}
 */
export function shouldShowAdditionalTour(visitBucketTid, tourCollegeCode) {
  const allowedColleges = collegeMapByBucket[visitBucketTid];
  return allowedColleges?.includes(tourCollegeCode) ?? false;
}
