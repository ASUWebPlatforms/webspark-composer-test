<?php

namespace Drupal\asuaec_asulocal\Service;

use Drupal\Core\Url;
use Psr\Log\LoggerInterface;

class DegreesCardBuilder {

  public function __construct(
    protected AsuProgramsClient $client,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Build degree cards (render fragment).
   *
   * @param int|null $limit
   *   Limit number of items (NULL = all).
   * @param string|null $q
   *   Search query.
   * @param string|null $interest
   *   Interest area slug or title.   */
  public function buildCards(?int $limit = NULL, ?string $q = NULL, ?string $interest = NULL): array {
    try {
      $result = $this->client->listAllPrograms(1000, NULL);
      $programs = is_array($result['items'] ?? NULL) ? $result['items'] : [];
    }
    catch (\Throwable $e) {
      $this->logger->error('Failed to fetch programs: @m', ['@m' => $e->getMessage()]);
      return [
        '#markup' => t('Unable to load degrees at this time.'),
      ];
    }


    // -----------------------------
    // Normalize incoming filters
    // -----------------------------
    $q = is_string($q) ? trim($q) : '';
    $interest = is_string($interest) ? trim($interest) : '';

    $q_lc = mb_strtolower($q); // lower case. mb_ = multibyte
    $interest_lc = mb_strtolower($interest);

    // -----------------------------
    // Filter programs by search + interest
    // -----------------------------
    if ($q !== '' || $interest !== '') {
      $programs = array_values(array_filter($programs, function ($p) use ($q_lc, $interest_lc) {
        // ---- Search filter (title contains q) ----
        if ($q_lc !== '') {
          $title = mb_strtolower((string) ($p['title'] ?? ''));
          if ($title === '' || mb_strpos($title, $q_lc) === FALSE) {
            return FALSE;
          }
        }

        // ---- Interest filter (match interest_areas slug or title) ----
        if ($interest_lc !== '') {
          $areas = $p['interest_areas'] ?? [];
          if (!is_array($areas) || empty($areas)) {
            return FALSE;
          }

          $matched = FALSE;
          foreach ($areas as $a) {
            if (!is_array($a)) {
              continue;
            }
            $slug = mb_strtolower((string) ($a['slug'] ?? ''));
            $title = mb_strtolower((string) ($a['title'] ?? ''));

            // Allow matching either by slug ("business-degrees") or title ("Business")
            if ($slug === $interest_lc || $title === $interest_lc) {
              $matched = TRUE;
              break;
            }
          }

          if (!$matched) {
            return FALSE;
          }
        }

        return TRUE;
      }));
    }


    usort($programs, function ($a, $b) {
      return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
    });

    if ($limit !== NULL) {
      $programs = array_slice($programs, 0, $limit);
    }

    $items = [];
    // $i = 0;
    foreach ($programs as $p) {
      // if($i === 0) {
      //   $this->logger->error('p:<pre>' . print_r($p, true) . '</pre>');
      //         // Array
      //         // (
      //         //     [id] => dfc32d93-3f07-487f-afea-2e902effb4a4
      //         //     [title] => Accountancy (BS)
      //         //     [short_description] => 
      //         // Arizona State University’s Bachelor of Science in accountancy is ranked among the best programs in the nation by U.S. News & World Report and Public Accounting Report. As the highest-rated undergraduate accounting program offered online, you’ll benefit from ASU’s state-of-the-art curriculum and real-world environment.


      //         //     [degree_image] => https://cms.asuonline.asu.edu/sites/g/files/litvpz1971/files/program-images/AccountingHero.jpg
      //         //     [program_code] => UGBA
      //         //     [total_credit_hours] => 120
      //         //     [code] => UGBA-BAACCBS
      //         // )        
      // }

      $degree_code = $p['code'] ?? '';
      $plan_code = $p['plan_code'] ?? '';
      $slug_source = $degree_code ?: $plan_code ?: ($p['program_code'] ?? '');

      if (!$slug_source) {
        continue;
      }

      $slug = strtolower($slug_source);
      $button_url = Url::fromUserInput('/degrees/' . $slug)->toString();

      $items[] = [
        '#theme' => 'asuaec_asulocal_degree_card',
        '#title' => $p['title'] ?? 'Untitled',
        '#image_url' => !empty($p['degree_image']) ? $p['degree_image'] : NULL,
        '#button_url' => $button_url,
        '#button_text' => 'Learn more',
      ];      
      // $i++;
    }

    $cards = [];
    foreach ($items as $card) {
      $cards[] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['col-12', 'col-md-6', 'col-lg-4', 'mb-4'],
        ],
        'card' => $card,
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['container', 'asu-degree-cards']],
      'row' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['row']],
        'cards' => $cards,
      ],
      '#cache' => [
        'max-age' => 3600, // 1hr

        // Cache varies by filters
        // Keep a separate cached version for each unique q and interest query value
        'contexts' => [
          'url.query_args:q', // The rendered output depends on the value of ?q= in the URL
          'url.query_args:interest', // The rendered output also depends on the ?interest= query param
        ],
      ],
    ];

  }



  /**
   * Build interest area options from program list.
   *
   * @return array
   *   Options array: ['business-degrees' => 'Business', ...]
   */
  public function getInterestOptions(): array {
    try {
      $result = $this->client->listAllPrograms(1000, NULL);
      $programs = is_array($result['items'] ?? NULL) ? $result['items'] : [];
    }
    catch (\Throwable $e) {
      $this->logger->error('Failed to fetch programs for interest options: @m', ['@m' => $e->getMessage()]);
      return [];
    }

    $options = [];

    foreach ($programs as $p) {
      $areas = $p['interest_areas'] ?? [];
      if (!is_array($areas)) {
        continue;
      }

      foreach ($areas as $a) {
        if (!is_array($a)) {
          continue;
        }

        $slug = trim((string) ($a['slug'] ?? ''));
        $title = trim((string) ($a['title'] ?? ''));

        if ($slug === '' || $title === '') {
          continue;
        }

        // Unique by slug.
        $options[$slug] = $title;
      }
    }

    // Sort by label (title).
    asort($options, SORT_NATURAL | SORT_FLAG_CASE);

    return $options;
  } // END OF public function getInterestOptions()



  /**
   * Return the number of programs that match filters (q + interest).
   */
  public function getFilteredCount(?string $q = NULL, ?string $interest = NULL): int {
    try {
      $result = $this->client->listAllPrograms(1000, NULL);
      $programs = is_array($result['items'] ?? NULL) ? $result['items'] : [];
    }
    catch (\Throwable $e) {
      $this->logger->error('Failed to fetch programs for count: @m', ['@m' => $e->getMessage()]);
      return 0;
    }

    // Same normalization as buildCards().
    $q = is_string($q) ? trim($q) : '';
    $interest = is_string($interest) ? trim($interest) : '';

    $q_lc = mb_strtolower($q);
    $interest_lc = mb_strtolower($interest);

    if ($q !== '' || $interest !== '') {
      $programs = array_values(array_filter($programs, function ($p) use ($q_lc, $interest_lc) {
        if ($q_lc !== '') {
          $title = mb_strtolower((string) ($p['title'] ?? ''));
          if ($title === '' || mb_strpos($title, $q_lc) === FALSE) {
            return FALSE;
          }
        }

        if ($interest_lc !== '') {
          $areas = $p['interest_areas'] ?? [];
          if (!is_array($areas) || empty($areas)) {
            return FALSE;
          }

          foreach ($areas as $a) {
            if (!is_array($a)) {
              continue;
            }
            $slug = mb_strtolower((string) ($a['slug'] ?? ''));
            $title = mb_strtolower((string) ($a['title'] ?? ''));
            if ($slug === $interest_lc || $title === $interest_lc) {
              return TRUE;
            }
          }
          return FALSE;
        }

        return TRUE;
      }));
    }

    return count($programs);
  }



}