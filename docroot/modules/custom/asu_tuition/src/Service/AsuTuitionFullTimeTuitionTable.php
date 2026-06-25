<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionFullTimeTuitionTable {

  /**
   *
   */
  public function getFullTimeTuitionTable($result) {
    // ksm($result);
    $table = [
      'header' => [
          ['data' => t('Tuition/Fee Description'), 'class' => ['fee-description'], 'data-class' => 'expand'],
          ['data' => t('Fall ' . substr($result->acad_years['descr'], 0, 4)), 'class' => ['fee-amount', 'text-right', 'mobile-hide'], 'data-hide' => 'phone,tablet'],
          ['data' => t('Spring ' . substr($result->acad_years['descr'], 5, 4)), 'class' => ['fee-amount', 'text-right', 'mobile-hide'], 'data-hide' => 'phone,tablet'],
          ['data' => t('Academic Year Total'), 'class' => ['fee-amount', 'text-right'], 'data-hide' => ''],
      ],
      'rows' => [],
      'attributes' => [
        'id' => 'acad-year-table',
        'class' => ['table', 'table-striped'],
      ],
    ];

    if (!\Drupal::service('includeSummer')->includeSummer($result)) {
      foreach ($result->tuition_fees as $key => $value) {
        if ($key == 'total') {
          $table['rows'][] = [
            'data' => [
              $value['descr'] . (array_key_exists($key, $result->notes) ? t(' (see notes below)') : ''),
          /* array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['fall'], FALSE), 'class' => array('dollar-amount', 'mobile-hide')),
           array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['spring'], FALSE), 'class' => array('dollar-amount', 'mobile-hide')),
           array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['total'], FALSE), 'class' => array('dollar-amount')),*/
           ['data' => number_format($value['fall']), 'class' => ['dollar-amount', 'mobile-hide']],
           ['data' => number_format($value['spring']), 'class' => ['dollar-amount', 'mobile-hide']],
           ['data' => number_format($value['total']), 'class' => ['dollar-amount']],
            ],
            'class' => [$key],
          ];
        }
      }
    }
    else {
      foreach ($result->tuition_fees as $key => $value) {
        if ($key == 'total') {
          $table['rows'][] = [
            'data' => [
              $value['descr'] . (array_key_exists($key, $result->notes) ? t(' (see notes below)') : ''),
          // array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['fall'], FALSE), 'class' => array('dollar-amount', 'mobile-hide')),
          // array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['spring'], FALSE), 'class' => array('dollar-amount', 'mobile-hide')),
          // array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['summer'], FALSE), 'class' => array('dollar-amount', 'mobile-hide')),
          // array('data' => \Drupal::service('tuitionMoneyFormat')->tuitionMoneyFormat($value['total'], FALSE), 'class' => array('dollar-amount')),.
          ['data' => number_format($value['total']), 'class' => ['dollar-amount']],
            ],
            'class' => [$key],
          ];
        }
      }
    }

    $build['table'] = [
      '#type' => 'table',
      '#header' => $table['header'],
      '#rows' => $table['rows'],
      '#empty' => t('No content has been found.'),
      '#attributes' => $table['attributes'],
    ];

    return [
      '#type' => '#markup',
      '#markup' => \Drupal::service('renderer')->render($build),
    ];
    // Return theme('table', $table);
    //  return table();
  }

}
