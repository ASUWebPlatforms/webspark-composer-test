<?php

namespace Drupal\asu_tuition\Service;

use Drupal\Core\Render\Markup;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionBreakdownTableJson {

  /**
   *
   */
  public function getBreakdownTableJson($result) {
    $output = '';
    // ksm($result);
    // $credit_hr = $result->values->credit_hr;
    // ksm($credit_hr);
    // ksm($result->breakdown);
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
      /*$year = $result->acad_years['acad_year'];
      $result_data_header = '<div class="col-12"><div class="card card-foldable">' ;
      $term_title = ucfirst($term)." ".$year;
      $term_id = ucfirst($term)."-".$year;
      $header_title = '<div class="card-header"><h4><a aria-controls="cardBody" aria-expanded="false" class="collapsed" data-ga="This card unfolds" data-ga-event="collapse" data-ga-name="onclick" data-ga-region="main content" data-ga-section="default" data-ga-type="click" data-target="#'.$term_id.'" data-toggle="collapse" href="#'.$term_id.'" id="card" role="button">'.$term_title.'<span class="fas fa-chevron-up" /></a></h4></div><div aria-labelledby="card" class="collapse card-body" data-parent="" id="cardBody">';*/

      $header = [
        'fee' => [
          'data' => t('Tuition/Fee Description'),
          'data-class' => 'expand',
          'class' => ['fee-description'],
        ],
      ];

      // $header[$max_credits]['data-hide'] = '';
      // $header[$credit_hr] = $credit_hr;
      // Build table out.
      $table = [
        'header' => $header,
        'rows' => [],
        'attributes' => [
          'class' => ['tuition-breakdown', 'table', 'table-striped', 'collapse', 'card-body', 'credits-' . $max_credits, $term],
      // 'id' => array($term_id)
        ],
        'caption' => '',
      ];
      // ksm($breakdown);
      // ksm($result->notes);
      $j = 1;
      foreach ($credits as $value => $credit) {
        $header[$value] = ['data' => \Drupal::translation()->formatPlural($value, '1 hour', '%credit hours', ['%credit' => $credit]), 'class' => ['fee-amount', 'text-right', 'credithr' . $j, 'mobile-hide'], 'data-hide' => 'phone,tablet'];
        $j++;
      }
      $header[$max_credits]['data-hide'] = '';
      // Build table out.
      $table = [
        'header' => $header,
        'rows' => [],
        'attributes' => [
          'class' => ['tuition-breakdown', 'table', 'table-striped', 'credits-' . $max_credits, $term],
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
      
      foreach ($breakdown as $key => $row) {
        /* if (empty($row['descr'])) {
        continue;
        }
         */
        $descr = $row['descr'];
        $items = [['data' => $descr . (array_key_exists($key, $result->notes) ? t(' (see notes below)') : '')]];

        for ($i = 1; $i <= $max_credits; $i++) {
          $items[] = ['data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($row[$i], FALSE), 'class' => ['dollar-amount', 'credithr' . $i]];
        }

        $table['rows'][] = ['data' => $items, 'class' => [$key]];
      }

      if ($term == 'fall') {
        $table['caption'] = 'Fall ' . substr($result->acad_year->descr, 0, 4) . '<span class="mobile-hide"> breakdown by credit hour</span>';
      }
      elseif ($term == 'spring') {
        $table['caption'] = 'Spring ' . substr($result->acad_year->descr, -4) . '<span class="mobile-hide"> breakdown by credit hour</span>';
      }
      else {
        $table['caption'] = 'Summer ' . substr($result->acad_year->descr, -4) . '<span class="mobile-hide"> breakdown by credit hour</span>';
      }

      $build['table'] = [
        '#type' => 'table',
        '#header' => $table['header'],
        '#rows' => $table['rows'],
        '#caption' => Markup::create($table['caption']),
        '#empty' => t('No content has been found.'),
        '#attributes' => $table['attributes'],
      ];

      // $data = render($build)->__toString();
      $data = Markup::create(\Drupal::service('renderer')->render($build))->__toString();
      // Remove theme debug comments if any.
      $output = preg_replace('/<!--(.|\s)*?-->/', '', $data);
      return $output;

    }

  }

}
