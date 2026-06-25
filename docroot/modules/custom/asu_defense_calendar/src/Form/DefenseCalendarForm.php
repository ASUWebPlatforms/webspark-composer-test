<?php

namespace Drupal\asu_defense_calendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\simple_sitemap\Logger;
use DrupalCodeGenerator\Command\Service\Logger as ServiceLogger;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

class DefenseCalendarForm extends FormBase {

  use StringTranslationTrait;

  protected $database;

  public function __construct($database, TranslationInterface $string_translation) {
    $this->database = $database;
    $this->stringTranslation = $string_translation;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('string_translation'),
    );
  }

  public function getFormId() {
    return 'asu_defense_calendar_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes']['class'][] = 'container';

    // Container for inline display
    $form['search_container'] = [
        '#type' => 'container',
        '#attributes' => ['style' => 'display: flex; flex-wrap: wrap;', 'class' => 'mt-3'],
    ];

    $form['search_container']['search_text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Keywords'),
        '#wrapper_attributes' => ['style' => 'flex-grow: 9;'], // Adjust as needed for styling
    ];

    $form['search_container']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Search'),
        '#ajax' => [
            'callback' => '::searchAjax',
            'wrapper' => 'search-results',
        ],
        '#attributes' => ['style' => 'flex-grow: 2; max-height: 3rem; margin-top: 2rem; margin-left: 1rem;'], // Adjust as needed for styling
    ];

    $form['results'] = [
      '#type' => 'markup',
      '#markup' => '<section id="search-results">'.$this->searchOutput().'</section>',
    ];

    return $form;
  }

  private function searchOutput(string $term = ''){
    // Database query
    $query = $this->database->select('announcements', 'a');
    $query->fields('a', ['title', 'virtual_audiencelink', 'virtual_meetinglink', 'fname', 'lname', 'datetime', 'defense_type', 'descr', 'degree', 'room', 'bldg_location', 'building', 'defense_type']);
    // Creating an OR condition group
    $orGroup = $query->orConditionGroup()
    ->condition('lname', '%' . $this->database->escapeLike($term) . '%', 'LIKE')
    ->condition('fname', '%' . $this->database->escapeLike($term) . '%', 'LIKE')
    ->condition('building', '%' . $this->database->escapeLike($term) . '%', 'LIKE')
    ->condition('bldg_location', '%' . $this->database->escapeLike($term) . '%', 'LIKE')
    ->condition('title', '%' . $this->database->escapeLike($term) . '%', 'LIKE')
    ->condition('descr', '%' . $this->database->escapeLike($term) . '%', 'LIKE');

    $query->condition($orGroup);
    $query->addExpression('WEEK(datetime)', 'week_number');
    $query->condition('datetime', date('Y-m-d'), '>=');
    $query->orderBy('datetime');
    $query->range(0, 400); // limit increased from 20 to 100 then 300 then 400// Need pagination to implement
    $result = $query->execute();
    
    $groupedResults = [];
    

    // We will implement the pagination soon
    // Grouping results by week number
    foreach ($result as $row) {

      $vmlink = '';
      $valink = '';
      $location = '';
        
      $weekNumber = $row->week_number + 1;
      if (!isset($groupedResults[$weekNumber])) {
          $groupedResults[$weekNumber] = [];
      }

      if (!empty(trim($row->virtual_meetinglink))) {
        $vmlink = "Virtual meeting link: <a href=\"{$row->virtual_meetinglink}\">{$row->virtual_meetinglink}</a>";
      }
      if (!empty(trim($row->virtual_audiencelink))) {
        $valink = "Virtual audience link: <a href=\"{$row->virtual_audiencelink}\">{$row->virtual_audiencelink}</a>";
      }

      if(!empty(trim($row->room))){
        $building = $row->building ?? $row->building;
        $bldg_location = $row->bldg_location ?? $row->bldg_location;
        $location = implode(' ', [$building, $bldg_location, $row->room]);
      }

      $groupedResults[$weekNumber][] = "<article>
                                          <header class=\"mt-4\">
                                            <p><strong><time class=\"font-weight-bold\" datetime='{$row->datetime}'>" . date("l, F j", strtotime($row->datetime)) . "</time></strong></p>
                                            <p class=\"p-0 m-0\"><strong><time class=\"text-maroon-emphasis\" datetime='{$row->datetime}'>" . date("h:i a", strtotime($row->datetime)) . " </time></strong>
                                            <span class=\"badge bg-gold p2\">{$this->mappingType($row->defense_type)}</span></p>
                                            <p class=\"p-0 m-0\"><strong>{$row->title}</strong></p>
                                            <p class=\"p-0 m-0\">{$row->fname} {$row->lname}</p>
                                            <p class=\"p-0 m-0\">{$row->descr} ({$row->degree})</p>
                                          </header>
                                          <footer>                                            
                                            <p class=\"p-0 m-0\">{$location}</p>
                                          </footer>
                                        </article>";
                                        /*
                                        # hiding zoom link from above <footer> on April 1, 2025
                                        <p class=\"p-0 m-0\">{$vmlink}</p>
                                        <p class=\"p-0 m-0\">{$valink}</p>
                                        */
    }

    // Construct HTML with sections // Year should be dynamic not hardcoded
    $results = [];
    foreach ($groupedResults as $weekNumber => $articles) {
        $weekArticles = implode('', $articles);
        $results[] = "<section class=\"pb-3\">
                        <h3>Week {$this->getWeekDateRange($weekNumber, date("Y"))}</h3>
                        {$weekArticles}
                      </section>";
    }
    

    if(empty($results)){
      $no_results = $this->t('No results found');
      return "<section class=\"pb-3\">
        <h2>{$no_results}</h2>
      </section>";
    }

    return implode('', $results);
  }

  public function searchAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $searchTerm = $form_state->getValue('search_text');
    $response->addCommand(new HtmlCommand('#search-results', $this->searchOutput($searchTerm)));
    return $response;
  }

  private function getWeekDateRange($week, $year) {
    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate($year, $week);
    
    $endOfWeek = clone $startOfWeek;
    $endOfWeek->modify('+6 days');

    // Formatting start and end dates
    $format = 'F d, Y'; // Full month name, day, year
    $startDate = $startOfWeek->format($format);
    $endDate = $endOfWeek->format($format);

    // Check if the month or year changes within the week
    if ($startOfWeek->format('Y-m') === $endOfWeek->format('Y-m')) {
        // Same month and year
        if ($startOfWeek->format('Y') === $endOfWeek->format('Y')) {
            // Same year
            return $startOfWeek->format('F d') . ' - ' . $endOfWeek->format('d, Y');
        } else {
            // Different year
            return $startDate . ' - ' . $endDate;
        }
    } else {
        // Different month or year
        return $startDate . ' - ' . $endDate;
    }
}

  private function mappingType($type) {
    $map = [
      'ONLN' => 'Online',
      'PERS' => 'In-Person',
      'SYNC' => 'Hybrid'
    ];
    return $map[$type];
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form submission logic is handled in AJAX
  }
}
