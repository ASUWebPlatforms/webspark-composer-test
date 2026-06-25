<?php

namespace Drupal\elevate_experience_import;

/**
 * Canonical taxonomy values and helpers for cleaning the Experience CSV data.
 *
 * The source CSVs encode multi-value classification columns as inconsistent
 * "CSV-within-CSV" strings (mixed quoting, comma-containing values) and contain
 * several typos / spelling variants. Splitting such a cell naively corrupts the
 * values, so instead we match each cell against the known canonical term names
 * and emit a clean, pipe-delimited list that the Feeds Tamper "explode" plugin
 * can split reliably.
 *
 * The pipe character "|" is used as the single, unambiguous internal delimiter
 * for every multi-value target so all Feeds mappings share one Tamper rule.
 */
class ExperienceValueNormalizer {

  /**
   * The internal delimiter used between cleaned multi-values.
   */
  const DELIMITER = '|';

  /**
   * Alias (lower-case) => canonical term name for the experience_type vocab.
   *
   * Covers both CSVs: CSV A "Reclassified Experience Type" and CSV B
   * "Experience Type" (which includes the typo "Interships" and the variant
   * "Projects & Research").
   */
  const EXPERIENCE_TYPE_ALIASES = [
    'jobs, work & career development' => 'Jobs, Work & Career Development',
    'jobs, work and career development' => 'Jobs, Work & Career Development',
    'internships and field experience' => 'Internships & Field Experience',
    'internships & field experience' => 'Internships & Field Experience',
    'interships' => 'Internships & Field Experience',
    'internships' => 'Internships & Field Experience',
    'projects & research' => 'Research',
    'projects and research' => 'Research',
    'research' => 'Research',
    'community, service and leadership' => 'Community, Service & Leadership',
    'community, service & leadership' => 'Community, Service & Leadership',
    'global experiences' => 'Global Experiences',
  ];

  /**
   * Alias (lower-case) => canonical term name for the collection vocab.
   */
  const COLLECTION_ALIASES = [
    'arts, design & performance' => 'Arts, Design & Performance',
    'art, design & performance' => 'Arts, Design & Performance',
    'business' => 'Business',
    'communication & media' => 'Communication & Media',
    'education' => 'Education',
    'entrepreneurship' => 'Entrepreneurship',
    'health & wellness' => 'Health & Wellness',
    'public, social & human services' => 'Public, Social & Human Services',
    'sustainability, environmental & natural resources' => 'Sustainability, Environmental & Natural Resources',
    'sustainabilty, environment & natural resources' => 'Sustainability, Environmental & Natural Resources',
    'science, technology, engineering & math' => 'Science, Technology, Engineering & Math',
  ];

  /**
   * Alias (lower-case) => canonical term name for the location vocab.
   *
   * Unknown location values (e.g. "California", "Washington D.C.") are left
   * untouched and auto-created by the Feeds mapping.
   */
  const LOCATION_ALIASES = [
    'anywhere' => 'Anywhere',
    'arizona-based' => 'Arizona-based',
    'international' => 'International',
    'virtual' => 'Virtual',
    'resource' => 'Resource',
    'n/a (resource)' => 'Resource',
  ];

  /**
   * Alias (lower-case) => canonical name for college values (variants/typos).
   *
   * The college vocabulary is open (terms are auto-created), so only known
   * spelling errors are corrected here; everything else passes through as-is.
   */
  const COLLEGE_ALIASES = [
    'w,p. carey school of business' => 'W. P. Carey School of Business',
  ];

  /**
   * The program term name for certificate-CSV courses.
   */
  const CERTIFICATE_PROGRAM = 'Work+Life Design Certificate';

  /**
   * Determines whether a free-text credits value indicates a credit-bearing item.
   *
   * @param string $credits
   *   The raw "Credits" value.
   *
   * @return bool
   *   TRUE when the value represents earned credit.
   */
  public static function indicatesCredit($credits) {
    $credits = trim((string) $credits);
    if ($credits === '') {
      return FALSE;
    }
    return !in_array(mb_strtolower($credits), ['0', 'n/a', 'na', 'none', 'no credit'], TRUE);
  }

  /**
   * Normalises a link value for the link field.
   *
   * Strips a leading "Learn more - " prefix (present in CSV B) and prepends
   * "https://" when the value has no scheme and is not a root-relative path,
   * so the link field's URI validation passes.
   *
   * @param string $url
   *   The raw link value.
   *
   * @return string
   *   The cleaned URL (may be empty).
   */
  public static function normalizeLink($url) {
    $url = trim((string) $url);
    $url = preg_replace('/^\s*learn more\s*-\s*/i', '', $url);
    $url = trim($url);
    if ($url === '') {
      return '';
    }
    // Already an absolute URL, internal path, or mailto/tel link.
    if (preg_match('#^(https?://|/|mailto:|tel:|internal:|entity:)#i', $url)) {
      return $url;
    }
    return 'https://' . $url;
  }

  /**
   * Extracts canonical term names found in a messy multi-value cell.
   *
   * Matches the longest aliases first and removes each matched span so that an
   * alias which is a substring of another is not double counted.
   *
   * @param string $cell
   *   The raw cell value.
   * @param array $aliases
   *   Alias (lower-case) => canonical name map.
   *
   * @return string
   *   Pipe-delimited list of canonical names (may be empty).
   */
  public static function matchCanonical($cell, array $aliases) {
    $haystack = mb_strtolower($cell);
    // Match longer aliases before shorter ones.
    $keys = array_keys($aliases);
    usort($keys, function ($a, $b) {
      return mb_strlen($b) - mb_strlen($a);
    });

    $found = [];
    foreach ($keys as $alias) {
      $pos = mb_strpos($haystack, $alias);
      if ($pos !== FALSE) {
        $canonical = $aliases[$alias];
        $found[$canonical] = $canonical;
        // Blank out the matched span to avoid re-matching substrings.
        $haystack = mb_substr($haystack, 0, $pos)
          . str_repeat(' ', mb_strlen($alias))
          . mb_substr($haystack, $pos + mb_strlen($alias));
      }
    }

    return implode(self::DELIMITER, array_values($found));
  }

  /**
   * Splits a simple delimited cell, applies an alias map, and re-joins it.
   *
   * Used for columns with a reliable separator (college uses ";", subitems
   * and location use ","). Unknown values are kept verbatim.
   *
   * @param string $cell
   *   The raw cell value.
   * @param string $separator
   *   The separator to split on.
   * @param array $aliases
   *   Optional alias (lower-case) => canonical name map.
   *
   * @return string
   *   Pipe-delimited, trimmed, de-duplicated list of values.
   */
  public static function splitAndNormalize($cell, $separator, array $aliases = []) {
    $values = [];
    foreach (explode($separator, (string) $cell) as $raw) {
      $value = trim($raw, " \t\n\r\0\x0B\"'");
      if ($value === '') {
        continue;
      }
      $key = mb_strtolower($value);
      if (isset($aliases[$key])) {
        $value = $aliases[$key];
      }
      $values[$value] = $value;
    }
    return implode(self::DELIMITER, array_values($values));
  }

}
