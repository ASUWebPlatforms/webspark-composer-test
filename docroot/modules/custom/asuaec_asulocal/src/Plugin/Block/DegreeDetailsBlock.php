<?php

namespace Drupal\asuaec_asulocal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\asuaec_asulocal\Service\DegreeResolver;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/**
 * Degree details block (body fields).
 *
 * @Block(
 *   id = "asuaec_asulocal_degree_details",
 *   admin_label = @Translation("ASU Local Degree Details"),
 * )
 */
class DegreeDetailsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected DegreeResolver $degreeResolver,
    protected RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('asuaec_asulocal.degree_resolver'),
      $container->get('current_route_match'),
    );
  }

  public function build(): array {
    $code = (string) $this->routeMatch->getParameter('code');
    if ($code === '') {
      return ['#markup' => $this->t('Missing degree code.')];
    }

    $degree = $this->degreeResolver->getDegreeByPlanCode($code);
    if (!$degree) {
      return ['#markup' => $this->t('Degree not found.')];
    }

    // \Drupal::logger('cstest')->notice('degree:<pre>' . print_r($degree, true) . '</pre>');

    $data = $degree;

    // -----------------------------
    // Canonical/meta. Added on 3/11/2026.
    // -----------------------------
    $meta_description = trim(strip_tags(Html::decodeEntities((string) ($data['short_description'] ?? ''))));
    $detail_page = trim((string) ($data['detail_page'] ?? ''));
    $canonical_url = $detail_page ? 'https://asuonline.asu.edu' . $detail_page : '';


    // -----------------------------
    // Courses
    // -----------------------------
    $course_items_raw = $data['curriculum_course']['course_items'] ?? '';
    $curriculum_courses = $this->parseCourseItems($course_items_raw);


    // -----------------------------
    // Career items
    // -----------------------------
    // Parse career_items (bamm_program)
    $career_items_raw = $data['bamm_program']['career_items'] ?? '';
    $career_items = $this->parseCareerItems($career_items_raw);

    // Helpful derived values.
    $plan_code = $data['plan_code'] ?? '';
    $program_code = $data['program_code'] ?? '';
    $degree_code = $data['code'] ?? '';
    $detail_page = $data['detail_page'] ?? '';

    // html_head. Added on 3/11/2026.
    $html_head = [];

    if (!empty($meta_description)) {
      $html_head[] = [
        [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'description',
            'content' => $meta_description,
          ],
        ],
        'asuaec_asulocal_meta_description',
      ];
    }

    if (!empty($canonical_url)) {
      $html_head[] = [
        [
          '#tag' => 'link',
          '#attributes' => [
            'rel' => 'canonical',
            'href' => $canonical_url,
          ],
        ],
        'asuaec_asulocal_canonical',
      ];
    }

    return [
      '#theme' => 'asuaec_asulocal_degree_details',

      // Raw full payload to reference arbitrary fields in Twig.
      // In Twig: {{ degree.title }}, {{ degree.total_credit_hours }}, etc.
      '#degree' => $data,

      // Also pass commonly-used fields as top-level Twig vars for convenience.
      '#id' => $data['id'] ?? '',
      '#title' => $data['title'] ?? 'Untitled',
      '#short_description' => $data['short_description'] ?? '',
      '#degree_image' => $data['degree_image'] ?? '',
      '#detail_page' => $detail_page,

      '#code' => $degree_code,
      '#plan_code' => $plan_code,
      '#program_code' => $program_code,
      '#sub_plan_code' => $data['sub_plan_code'] ?? '',
      '#concentration_code' => $data['concentration_code'] ?? '',

      '#next_start_date' => $data['next_start_date'] ?? '',
      '#weeks_per_class' => $data['weeks_per_class'] ?? '',
      '#total_classes' => $data['total_classes'] ?? '',
      '#total_credit_hours' => $data['total_credit_hours'] ?? '',

      '#featured_courses_title' => $data['featured_courses_title'] ?? '',
      '#featured_courses_description' => $data['featured_courses_description'] ?? '',

      '#related_careers_title' => $data['related_careers_title'] ?? '',
      '#related_careers_description' => $data['related_careers_description'] ?? '',
      '#related_careers_image' => $data['related_careers_image'] ?? '',

      // /degrees/<degree-code-lowercase>
      '#local_degree_url' => '/degrees/' . strtolower($degree_code ?: $plan_code ?: $code),

      '#curriculum_courses' => $curriculum_courses,
      '#career_items' => $career_items,

      // IMPORTANT: JS uses this.
      '#attached' => [
        'library' => [
          'asuaec_asulocal/degree_courses',
        ],
        'drupalSettings' => [
          'asuaec_asulocal' => [
            'curriculumCourses' => $curriculum_courses,
            'careerItems' => $career_items,
          ],
        ],
        'html_head' => $html_head,
      ],

      '#cache' => [
        'max-age' => 3600,
        'contexts' => ['url.path'],
      ],
    ];
  }


  /**
   * Convert the weird course_items string into an array of course arrays.
   *
   * Input example:
   * [@"subject":"CIS","catalogNumber":"105","title":"...","description":"...","courseSearchUrl":"... ",@"subject":"ECN", ...]
   */
  protected function parseCourseItems(mixed $raw): array {
    if (empty($raw)) {
      return [];
    }

    // If API ever starts returning a real array, just return it.
    if (is_array($raw)) {
      return $raw;
    }

    if (!is_string($raw)) {
      return [];
    }

    $raw = trim($raw);

    // Normalize: remove the @ prefix so keys become normal JSON-ish keys.
    // Turns @"subject" into "subject".
    $normalized = str_replace('@"', '"', $raw);

    // Strip outer brackets if present.
    $normalized = trim($normalized);
    $normalized = ltrim($normalized, '[');
    $normalized = rtrim($normalized, ']');

    // Find each item boundary: "subject":"..."
    if (!preg_match_all('/"subject"\s*:\s*"/m', $normalized, $m, PREG_OFFSET_CAPTURE)) {
      return [];
    }

    $offsets = array_map(fn($x) => $x[1], $m[0]);
    $offsets[] = strlen($normalized); // sentinel end

    $items = [];

    for ($i = 0; $i < count($offsets) - 1; $i++) {
      $start = $offsets[$i];
      $end = $offsets[$i + 1];
      $chunk = substr($normalized, $start, $end - $start);

      $chunk = trim($chunk);
      $chunk = ltrim($chunk, ",");
      $chunk = rtrim($chunk, ",");


      // Pull all "key":"value" pairs inside this chunk.
      if (!preg_match_all('/"([^"]+)"\s*:\s*"([^"]*)"/m', $chunk, $pairs, PREG_SET_ORDER)) {
        continue;
      }

      $course = [];
      foreach ($pairs as $p) {
        $key = $p[1];
        $val = $p[2];

        // Normalize keys
        if ($key === 'catalogNumber') {
          $key = 'catalog_number';
        }
        elseif ($key === 'courseSearchUrl') {
          $key = 'course_search_url';
        }

        $course[$key] = $val;
      }

      // Keep items that at least have subject + catalog number or a title.
      if (!empty($course['subject']) && (!empty($course['catalog_number']) || !empty($course['title']))) {
        $items[] = $course;
      }
    }

    return $items;
  }


  /**
   * Convert the weird career_items string into an array.
   *
   * Input example:
   * [@"salary":"$81,680","title":"Accountants and Auditors","growth":"4.6","alternateTitle":"Accountant/Auditor", ...]
   */
  protected function parseCareerItems(mixed $raw): array {
    if (empty($raw)) {
      return [];
    }
    if (is_array($raw)) {
      return $raw;
    }
    if (!is_string($raw)) {
      return [];
    }

    $raw = trim($raw);

    // Normalize @"key" -> "key"
    $normalized = str_replace('@"', '"', $raw);
    $normalized = trim($normalized);
    $normalized = ltrim($normalized, '[');
    $normalized = rtrim($normalized, ']');

    // ================================
    // Split items by "salary" (NOT by "title")
    // to avoid the 1-row shift.
    // ================================
    if (!preg_match_all('/"salary"\s*:\s*"\$?/m', $normalized, $m, PREG_OFFSET_CAPTURE)) {
      // Fallback: if there is only one item and it doesn't match the above,
      // try a looser check:
      if (!preg_match('/"salary"\s*:\s*"/m', $normalized)) {
        return [];
      }
      $offsets = [0, strlen($normalized)];
    }
    else {
      $offsets = array_map(fn($x) => $x[1], $m[0]);
      // Ensure first chunk includes beginning.
      if (empty($offsets) || $offsets[0] !== 0) {
        array_unshift($offsets, 0);
      }
      $offsets[] = strlen($normalized);
    }

    $items = [];

    for ($i = 0; $i < count($offsets) - 1; $i++) {
      $start = $offsets[$i];
      $end = $offsets[$i + 1];
      $chunk = substr($normalized, $start, $end - $start);

      $chunk = trim($chunk);
      $chunk = ltrim($chunk, ",");
      $chunk = rtrim($chunk, ",");

      if (!preg_match_all('/"([^"]+)"\s*:\s*"([^"]*)"/m', $chunk, $pairs, PREG_SET_ORDER)) {
        continue;
      }

      $career = [];
      foreach ($pairs as $p) {
        $key = $p[1];
        $val = $p[2];

        if ($key === 'alternateTitle') {
          $key = 'alternate_title';
        }

        $career[$key] = $val;
      }

      // ================================
      // Require title AND salary when possible
      // (title-only can hide bugs; but keep a fallback)
      // ================================
      if (!empty($career['title']) || !empty($career['salary'])) {
        $items[] = $career;
      }
    }

    return $items;
  }

}