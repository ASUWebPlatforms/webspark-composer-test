<?php

namespace Drupal\asu_cost_comparison_tool\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @file
 * Contains \Drupal\asu_cost_comparison_tool\Plugin\Block\cost_calculator_block.
 */

/**
 * Provides a Cost Calculator block.
 *
 * @Block(
 *   id = "cost_calculator_block",
 *   admin_label = @Translation("ASU React Cost Calculator block"),
 *   category = @Translation("ASU React Cost Calculator block"),
 * )
 */
class cost_calculator_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $defaultValues = \Drupal::config('asu_cost_comparison_tool.settings');
    // dpm($defaultValues->get('tuition_az'),'Default Values');.
    $defaultValuesfromAdminForm = [
      'defaultTuitionAz' => $defaultValues->get('tuition_az'),
      'defaultTuitionNonAz' => $defaultValues->get('tuition_non_az'),
      'defaultTuitionIntl' => $defaultValues->get('tuition_intl'),
      'defaultOnCampusHousing' => $defaultValues->get('oncampus_living'),
      'defaultOffCampusLiving' => $defaultValues->get('off_campus_living'),
      'defaultWithParentsMealPlans' => $defaultValues->get('with_parents_living'),
      'defaultBooksAndSupplies' => $defaultValues->get('books_and_supplies'),
      'defaultTransportation' => $defaultValues->get('transportation'),
      'CustomText' => $defaultValues->get('CustomText'),
      'CustomText2' => $defaultValues->get('CustomText2'),
      'CustomText3' => $defaultValues->get('CustomText3'),
      'tuitionToolTip' => $defaultValues->get('tuition_fees_tooltip'),
      'booksToolTip' => $defaultValues->get('books_supplies_tooltip'),
      'housingToolTip' => $defaultValues->get('housing_meals_tooltip'),
      'transportationToolTip' => $defaultValues->get('transportation_tooltip'),
      'subsidiesLoansToolTip' => $defaultValues->get('subsidies_loans_tooltip'),
      'unsubsidizedLoansToolTip' => $defaultValues->get('unsubsidized_loans_tooltip'),
      'parentPlusLoansToolTip' => $defaultValues->get('parent_plus_loans_tooltip'),
      'scholarToolTip' => $defaultValues->get('scholar_tooltip'),
      'grantToolTip' => $defaultValues->get('grant_tooltip'),
      'cost_webform_id' => $defaultValues->get('cost_webform_id'),
      'cas_login_url' => $defaultValues->get('cas_login_url'),
    ];
    // dpm($defaultValuesfromAdminForm,'Default Values Array to React');.
    $build['react_cost_comparison_block'] = [
      '#markup' => '<div id="react-cost-comparison"></div>',
      '#attached' => [
        'library' => [
          'asu_cost_comparison_tool/react_cost_tool',
        ],
        'drupalSettings' => [
          'asu_cost_comparison_tool' => $defaultValuesfromAdminForm,
        ],
      ],
    ];

    return $build;
  }

}
