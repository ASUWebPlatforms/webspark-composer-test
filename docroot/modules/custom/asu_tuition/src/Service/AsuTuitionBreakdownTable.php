<?php

namespace Drupal\asu_tuition\Service;

use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionBreakdownTable {

  /**
   *
   */
  public function getBreakdownTable($result) {
    $output = '';

    $credit_hr = $result->values->credit_hr;
    // ksm($result);
    $result_data_header = '<div class="col-12"><div class="accordion" id="accordionTuition">';
    $allSemTotals = [];
    $semtotal = [];
    foreach ($result->breakdown as $term => $breakdown) {

      if ($term === 'summer' && !\Drupal::service('includeSummer')->includeSummer($result)) {
        continue;
      }

      // Build credits row.
      $credits = [];
      $max_credits = ($term !== 'summer') ? $result->max_credits : 18;
      for ($i = 1; $i <= $max_credits; $i++) {
        $credits[$i] = $i;
      }

      // Never display a '+' on last credit column when max_credits = 18.
      // $programs_without_limit = array('LPN002', 'LPR002', 'LPN003', 'LPR003', 'LPN004', 'LPR004', 'LPN005', 'LPR005', 'GP0001', 'GP0049');.
      if ($max_credits < 18) {
        $credits[$max_credits] .= '+';
      }
      $year = $result->acad_years['acad_year'];

      if (ucfirst($term) == "Fall") {
        $term_title = ucfirst($term) . " " . substr($result->acad_years['descr'], 0, 4);
        $term_id = ucfirst($term) . "-" . substr($result->acad_years['descr'], 0, 4);
      }
      if (ucfirst($term) == "Spring") {
        $term_title = ucfirst($term) . " " . substr($result->acad_years['descr'], 5, 4);
        $descr = $result->acad_years['descr'] ?? '';
        $term_id = ucfirst($term) . "-" . substr($descr, 5, 4);
        // $term_id = ucfirst($term)."-".substr($result->acad_years['descr'], 5, 4);
      }
      if (ucfirst($term) == "Summer") {
        $term_title = ucfirst($term) . " " . substr($result->acad_years['descr'], 5, 4);
        $term_id = ucfirst($term) . "-" . substr($result->acad_years['descr'], 5, 4);
      }

      // $term_title = ucfirst($term)." ".$year;
      // $term_id = ucfirst($term)."-".$year;
      $header = [
        'fee' => [
      // 'data' => \Drupal\Core\Render\Markup::create('<div data-toggle="collapse" data-target="#accordion">'.$term_title.'</div'),
          'data' => $term_title,
          'data-class' => 'expand',
          'class' => ['fee-description'],

        ],
      ];

      // Build table out.
      $table = [
        'header' => $header,
        'rows' => [],
        'attributes' => [
          'class' => ['tuition-breakdown', 'table', 'table-striped', 'credits-' . $max_credits, $term],
          'id' => $term_id,
        ],
        'caption' => '',
      ];

      //code to remove element with empty descr from the breakdown
      $newbreakdown = array_filter($breakdown, function ($item) {
        if (!isset($item['descr'])) {
          return false;
        }
      
        // If it's a TranslatableMarkup, check untranslated string to avoid attaching translator.
        if ($item['descr'] instanceof TranslatableMarkup) {
          return trim($item['descr']->getUntranslatedString()) !== '';
        }
      
        // Plain string handling
        return is_string($item['descr']) && trim($item['descr']) !== '';
      });
      //dpm($breakdown, 'Original Breakdown');
      foreach ($breakdown as $key => $row) {
        $descr = $row['descr'];
        $items = [['data' => $descr . (array_key_exists($key, $result->notes) ? t(' (see notes below)') : '')]];
        $items[] = ['data' => number_format($row[$credit_hr]), 'class' => ['dollar-amount', 'credithr' . $credit_hr]];
        $raw_items = ['data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($row[$credit_hr], FALSE), 'class' => ['dollar-amount', 'credithr' . $credit_hr]];
        $table['rows'][] = ['data' => $items, 'class' => [$key, 'tuition-tr']];
      }
      
      //code to get separate total fee for each semester
      if (!empty($table['rows'])) {
        foreach ($table['rows'] as $row) {
          $label = $row['data'][0]['data'] ?? '';

          if ($label === 'Total Tuition & Fees') {
            $semtotal[$term_title] = $row['data'][1]['data'] ?? null;
            break;
          }
        }
      }
     
      $allSemTotals[$term_title] = $semtotal[$term_title] ?? null;
      
      ///// end of code to get separate total fee for each semester
      
      $total[] = $raw_items['data'];
      $price_in_header = $items[1]['data'];
      $header1 = '<div class="accordion-item  mt-3"><div class="accordion-header"><h4><button><a aria-controls="' . $term_id . '" aria-expanded="false" class="collapsed tuition-result-a" data-ga="' . $term_id . '" data-bs-toggle="collapse" href="#' . $term_id . '" id="' . $term . 'cardOne" role="button"><span class="tuition-icon" aria-hidden="true"><i class="fas fa-plus-circle"></i> ' . $term_title . '</span><span class="header-price">$' . $price_in_header . '</span></a></button></h4></div><div id="' . $term_id . '" class="collapse" aria-labelledby="' . $term . 'cardOne" data-bs-parent="#accordionTuition" style="">';
      $result_inner_bottom = '</div></div><p>&nbsp;</p>';
      $build[$term] = [
        '#prefix' => $header1,
        '#type' => 'table',
      // '#header' =>  $table['header'],
        '#rows' => $table['rows'],
        '#caption' => Markup::create($table['caption']),
        '#empty' => t('No content has been found.'),
        '#attributes' => $table['attributes'],
        '#suffix' => $result_inner_bottom,
      ];

    }

    
    $total_value = number_format(array_sum($total));

    $result_bottom = "</div><div class='tuition-total-academic'><div class='table table-striped tuition-total-result-table'>Academic Year total</div><div class='dollar-amount header-price-bottom'>$" . $total_value . "</div></div>";
    $results_all_link = "<div class='tuition_all_link'><button class='hide-show-link'>Show all</button></div></div>";
    $renderBuild = \Drupal::service('renderer')->render($build);
    return [
      '#type' => '#markup',
      '#markup' => $result_data_header . $renderBuild . $result_bottom . $results_all_link,
      'total_value' => $total_value,
      'all_sem_totals' => $allSemTotals,
    ];

  }

}
