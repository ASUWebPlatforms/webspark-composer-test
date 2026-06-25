<?php

/**
 * @file
 * Theme and preprocess functions for blocks.
 */

 use Drupal\block_content\Entity\BlockContent;
 use Drupal\Core\Url;
 use Drupal\file\Entity\File;
 use Drupal\media\Entity\Media;
 use Drupal\crop\Entity\Crop;
 use Drupal\image\Entity\ImageStyle;

/**
 * Gets the video poster image if set.
 *
 * @param array $variables
 *    The block variables.
 *
 * @return string
 *    The Poster image URL, empty string if not found.
 */
function getAsuPosterImage(array $variables): string {
  if (!$variables['content']['#block_content'] instanceof BlockContent) {
    return '';
  }

  /** @var \Drupal\block_content\Entity\BlockContent $block */
  $block = $variables['content']['#block_content'];

  if (!$block->hasField('field_background_image')) {
    return '';
  }

  $poster_entity = $block->get('field_background_image')->referencedEntities();

  if (empty($poster_entity)) {
    return '';
  }

  $poster_media = reset($poster_entity);

  /** @var \Drupal\media\Entity\Media $poster_media */
  if (!($poster_media instanceof Media) || $poster_media->hasField('field_media_image') === FALSE) {
    return '';
  }

  $poster_image = $poster_media->get('field_media_image')->referencedEntities();
  if (empty($poster_image)) {
    return '';
  }

  /** @var \Drupal\file\Entity\File $poster_image */
  $poster_image = reset($poster_image);

  $poster_url = NULL;
  if (Crop::cropExists($poster_image->getFileUri(), 'mobile_video_poster')) {
    $style = ImageStyle::load('video_poster_image');
    if ($style) {
      $poster_url = $style->buildUrl($poster_image->getFileUri());
    }
  }
  else {
    // There is no crop - get image file URL.
    $poster_url = \Drupal::service('file_url_generator')->generateAbsoluteString($poster_image);
  }

  return $poster_url;
}

/**
 * Implements hook_preprocess_HOOK().
 * ASU Future Students
 */
function asu_edu_tucson_preprocess_block__inline_block__asu_edu_future_students(&$variables) {
    $variables['attributes']['class'][] = 'asu-edu-future-students';
}


/**
 * Implements hook_preprocess_HOOK().
 * ASU Overlay Parallax
 */
function asu_edu_tucson_preprocess_block__inline_block__asu_edu_overlay_parallax(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    // Get background image.
    $background_image_uri = '';
    if ($block->hasField('field_background_image') && !$block->get('field_background_image')->isEmpty()) {

      // Load the file.
      /** @var \Drupal\media\Entity\Media $file */
      $media = $block->field_background_image->entity;
      /** @var \Drupal\file\Entity\File $file */
      $file = $media->field_media_image->entity;

      $background_image_uri = $file->createFileUrl(FALSE);
    }

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    $card = current($cards);

    $card_content = [
      'media' => getCardImage($card),
      'heading' => $card->get('field_heading')->value,
      'body' => $card->get('field_body')->value,
      'ctas' => getCardCtas($card),
      'links' => getCardLinks($card),
    ];

    $variables['background'] = $background_image_uri;
    $variables['card'] = $card_content;
}

/**
 * Implements hook_preprocess_HOOK().
 * ASU Section with CTA Buttons
 */
function asu_edu_tucson_preprocess_block__inline_block__asu_edu_section_with_cta_buttons(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    $card = current($cards);

    $media_url = '';
    $media = getCardImage($card);

    if (!empty($media)) {
      // replace deprecated file_create_url($uri) for D10.x
      $media_url = \Drupal::service('file_url_generator')->generateAbsoluteString($media['#uri']);
    }

    $card_content = [
      'media_url' => $media_url,
      'media' => getCardImage($card),
      'heading' => $card->get('field_heading')->value,
      'body' => $card->get('field_body')->value,
      'ctas' => getCardCtas($card),
      'links' => getCardLinks($card),
    ];

    $variables['card'] = $card_content;
  }



  /**
   * Implements hook_preprocess_HOOK().
   * ASU Animated Content Buttons
   */
  function asu_edu_tucson_preprocess_block__inline_block__asu_edu_anim_content_buttons(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    foreach ($cards as $card) {
      $media_url = '';
      $media = getCardImage($card);

      if (!empty($media)) {
        // replace deprecated file_create_url($uri) for D10.x
        $media_url = \Drupal::service('file_url_generator')->generateAbsoluteString($media['#uri']);
      }

      $cards_content[] = [
        'media' => $media_url,
        'heading' => $card->get('field_heading')->value,
        'body' => $card->get('field_body')->value,
        'ctas' => getCardCta($card),
        'links' => getCardLinks($card),
        'tags' => getCardTags($card),
      ];
    }

    $variables['cards'] = $cards_content;
  }

  /**
   * Implements hook_preprocess_HOOK().
   * ASU Stories of Excellence
   */
  function asu_edu_tucson_preprocess_block__inline_block__asu_edu_stories_of_excellence(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    foreach ($cards as $card) {
      $media_url = '';
      $media = getCardImage($card);

      if (!empty($media)) {
        // replace deprecated file_create_url($uri) for D10.x
        $media_url = \Drupal::service('file_url_generator')->generateAbsoluteString($media['#uri']);
      }

      $ctas = getCardCta($card);

      if (!empty($ctas) && is_array($ctas)) {
        $first_cta = reset($ctas);
        $url = $first_cta['uri'];
      }

      $cards_content[] = [
        'media' => $media_url,
        'heading' => $card->get('field_heading')->value,
        'body' => $card->get('field_body')->value,
        'url' => $url,
        'ctas' => $ctas,
      ];
    }

    $variables['cards'] = $cards_content;
  }

  /**
   * Implements hook_preprocess_HOOK().
   * ASU For the Media
   */
  function asu_edu_tucson_preprocess_block__inline_block__asu_edu_for_the_media(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    foreach ($cards as $card) {
      $cards_content[] = [
        'heading' => $card->get('field_heading')->value,
        'icon_name' => $card->get('field_icon')->getValue()[0]['icon_name'],
        'body' => $card->field_formatted_text->value,
        'ctas' => getCardCta($card),
      ];
    }

    $variables['heading'] = $block->field_formatted_text->value;
    $variables['cards'] = $cards_content;
  }

  /**
 * Implements hook_preprocess_HOOK().
 * ASU Links only Cards
 */
function asu_edu_tucson_preprocess_block__inline_block__asu_edu_links_only_cards(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    foreach ($cards as $card) {
      if ($card->bundle() !== 'card_links') {
        continue;
      }

      $buf = [];
      $links = $card->get('field_simple_links')->getValue();
      if (!empty($links)) {
        foreach ($links as $l) {
          $buf[] = [
            'title' => $l['title'],
            'uri' => $l['uri'],
          ];
        }
      }

      $cards_content[] = [
        'heading' => $card->get('field_heading')->value,
        'links' => $buf,
      ];
    }

    $variables['cards'] = $cards_content;

    $cards_count = count($cards);

    // Set three columns layout by default.
    $divider = 4;

    if (in_array($cards_count, range(1, 4))) {
      $divider = intval(12 / $cards_count);
    }

    $variables['heading'] = $block->field_heading->value;

    $variables['divider'] = $divider;
  }

  /**
   * Implements hook_preprocess_HOOK().
   * Carousel Image
   */
  function asu_edu_tucson_preprocess_block__inline_block__carousel_image(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_carousel_card')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    foreach ($cards as $card) {
      $media_url = '';
      $media = getCardImage($card);

      if (!empty($media)) {
        // replace deprecated file_create_url($uri) for D10.x
        $media_url = \Drupal::service('file_url_generator')->generateAbsoluteString($media['#uri']);
      }

      $cards_content[] = [
        'media' => $media_url,
      ];
    }

    $variables['cards'] = $cards_content;
  }

  /**
   * Implements hook_preprocess_HOOK().
   * ASU Image Carousel Buttons
   */
  function asu_edu_tucson_preprocess_block__inline_block__asu_edu_image_carousel_buttons(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $cards = $block->get('field_cards')->referencedEntities();

    if (empty($cards)) {
      return;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $card */
    foreach ($cards as $card) {
      $media_url = '';
      $media = getCardImage($card);

      if (!empty($media)) {
        // replace deprecated file_create_url($uri) for D10.x
        $media_url = \Drupal::service('file_url_generator')->generateAbsoluteString($media['#uri']);
      }

      $cards_content[] = [
        'heading' => $card->get('field_heading')->value,
        // 'links' => getCardLinks($card),
        'ctas' => getCardCta($card),
        'media' => $media_url,
      ];
    }

    $variables['cards'] = $cards_content;

    $variables['heading'] = $block->field_heading->value;
  }

  /**
   * Implements hook_preprocess_HOOK().
   * ASU COntent Image Overlap
   */
  function asu_edu_tucson_preprocess_block__inline_block__asu_edu_content_image_overlap(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $variables['cards'] = $cards_content;

    $variables['heading'] = $block->field_heading->value;

    $variables['body'] = $block->field_formatted_text->value;

    /** @var \Drupal\paragraphs\Entity\Paragraph $ctas */
    $ctas = $block->field_cta->referencedEntities()[0];

    if ($ctas->hasField('field_cta_link') ) {
      $cta_value = $ctas->get('field_cta_link')->getValue();
      if (!empty($cta_value)) {
        $variables['ctas'] = $cta_value;
      }
    }

    $media = $block->field_media->referencedEntities();
    if (!empty($media)) {
      /** @var \Drupal\media\Entity\Media $image*/
      $image = current($media);

      /** @var \Drupal\file\Entity\File $file */
      $file = $image->field_media_image->entity;

      // convert file URL to string
      $uri = $file->getFileUri();

      if ($file) {
        // replace deprecated file_create_url($uri) for D10.x
        $variables['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
      }
    }
  }

  /**
 * Implements hook_preprocess_HOOK().
 * ASU Sidebar Naviation
 */
function asu_edu_tucson_preprocess_block__inline_block__asu_edu_sidebar_navigation(&$variables) {
    if (!$variables['content']['#block_content'] instanceof BlockContent) {
      return;
    }

    $cards_content = [];

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $variables['content']['#block_content'];

    $links = $block->get('field_simple_links')->getValue();

    $nav_links = [];

    foreach ($links as $l) {
      $nav_links[] = [
        'url' => Url::fromUri($l['uri'])->toString(),
        'title' => $l['title'],
      ];
    }

    $variables['nav_links'] = $nav_links;

  }

/**
 * Implements hook_preprocess_HOOK().
 * Grid Links
 */
function asu_edu_tucson_preprocess_block__inline_block__grid_links(&$variables) {
    $request = \Drupal::request();
    if ($request->getPathInfo() === '/academics/faculty-excellence') {
      $variables['attributes']['class'][] = 'asu-edu-grid-links-heading';
      $variables['#attached']['library'][] =  'asu_edu_tucson_components/asu_edu_tucson_components';
    }
  }
