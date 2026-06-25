<?php

namespace Drupal\elevate_experience_import\EventSubscriber;

use Drupal\elevate_experience_import\ExperienceValueNormalizer as N;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\ParseEvent;
use Drupal\feeds\EventSubscriber\AfterParseBase;
use Drupal\feeds\Exception\SkipItemException;
use Drupal\feeds\Feeds\Item\ItemInterface;

/**
 * Cleans parsed rows for the two Experience CSV importers.
 *
 * Runs after the CSV parser. It drops section/header rows and rewrites the
 * messy classification columns into clean, pipe-delimited canonical values so
 * the Feeds mappings + Tamper "explode" can map them reliably.
 */
class ExperienceParseSubscriber extends AfterParseBase {

  /**
   * Feed type id for CSV A (general experiences, has a header row).
   */
  const FEED_GENERAL = 'experience_general';

  /**
   * Feed type id for CSV B (certificate courses, parsed without headers).
   */
  const FEED_CERTIFICATE = 'experience_certificate';

  /**
   * {@inheritdoc}
   *
   * Run after the parser (priority 0) but before the Feeds Tamper subscriber
   * (priority FeedsEvents::AFTER = -10000), so values are normalised to clean
   * pipe-delimited strings before Tamper "explode" splits them.
   */
  public static function getSubscribedEvents(): array {
    return [FeedsEvents::PARSE => [['afterParse', -5000]]];
  }

  /**
   * {@inheritdoc}
   */
  public function applies(ParseEvent $event) {
    return in_array($event->getFeed()->getType()->id(), [
      self::FEED_GENERAL,
      self::FEED_CERTIFICATE,
    ], TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterItem(ItemInterface $item, ParseEvent $event) {
    if ($event->getFeed()->getType()->id() === self::FEED_GENERAL) {
      $this->alterGeneralItem($item);
    }
    else {
      $this->alterCertificateItem($item);
    }
  }

  /**
   * Cleans a row from CSV A.
   *
   * Items are keyed by the feed type's custom-source machine names (see the
   * importer config): experience_name, link, reclassified_experience_type,
   * subitems, credits, description, location_modality,
   * career_interest_collection, college_department, contact_name, contact_email.
   */
  protected function alterGeneralItem(ItemInterface $item) {
    // Section-header rows only carry the first column ("Experience Name").
    $rest = [
      $item->get('link'),
      $item->get('reclassified_experience_type'),
      $item->get('subitems'),
      $item->get('credits'),
      $item->get('description'),
      $item->get('location_modality'),
      $item->get('career_interest_collection'),
      $item->get('college_department'),
      $item->get('contact_name'),
      $item->get('contact_email'),
    ];
    if ($this->allEmpty($rest)) {
      throw new SkipItemException();
    }

    $item->set('link', N::normalizeLink((string) $item->get('link')));
    $item->set('reclassified_experience_type', N::matchCanonical((string) $item->get('reclassified_experience_type'), N::EXPERIENCE_TYPE_ALIASES));
    $item->set('career_interest_collection', N::matchCanonical((string) $item->get('career_interest_collection'), N::COLLECTION_ALIASES));
    $item->set('location_modality', N::splitAndNormalize((string) $item->get('location_modality'), ',', N::LOCATION_ALIASES));
    $item->set('college_department', N::splitAndNormalize((string) $item->get('college_department'), ';', N::COLLEGE_ALIASES));
    $item->set('subitems', N::splitAndNormalize((string) $item->get('subitems'), ','));
  }

  /**
   * Cleans a row from CSV B (certificate courses).
   *
   * Parsed without headers; items are keyed by these custom-source machine
   * names mapped to column indices: course_title (0), subitems (1),
   * description (2), experience_type (3), credits (4), college (5), link (6).
   */
  protected function alterCertificateItem(ItemInterface $item) {
    $title = trim((string) $item->get('course_title'));

    // Skip the repeated header rows and the certificate title row.
    if (mb_strtolower($title) === 'course number & title') {
      throw new SkipItemException();
    }
    // Skip section / title rows: only the first column is populated.
    $rest = [
      $item->get('subitems'), $item->get('description'),
      $item->get('experience_type'), $item->get('credits'),
      $item->get('college'), $item->get('link'),
    ];
    if ($this->allEmpty($rest)) {
      throw new SkipItemException();
    }

    // Strip the "Learn more - " prefix and ensure the link has a scheme.
    $item->set('link', N::normalizeLink((string) $item->get('link')));

    $item->set('experience_type', N::matchCanonical((string) $item->get('experience_type'), N::EXPERIENCE_TYPE_ALIASES));
    $item->set('college', N::splitAndNormalize((string) $item->get('college'), ';', N::COLLEGE_ALIASES));
    $item->set('subitems', N::splitAndNormalize((string) $item->get('subitems'), ','));
  }

  /**
   * Returns TRUE when every value in the list is empty after trimming.
   */
  protected function allEmpty(array $values) {
    foreach ($values as $value) {
      if (trim((string) $value) !== '') {
        return FALSE;
      }
    }
    return TRUE;
  }

}
