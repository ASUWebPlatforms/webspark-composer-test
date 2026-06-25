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
 *   id = "loan_proration_block",
 *   admin_label = @Translation("ASU Loans proration block"),
 *   category = @Translation("ASU loans proration block"),
 * )
 */
class loan_proration_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $defaultValues = \Drupal::config('asu_cost_comparison_tool.loan_proration_settings');
    // dpm($defaultValues->get('tuition_az'),'Default Values');.
    $loanProrationAdminFormFields = [
      'fieldLabels' => $defaultValues->get('field_labels'),
      'studentType' => $defaultValues->get('student_type'),
      'loanResidency' => $defaultValues->get('loan_residency'),
      'loanDependency' => $defaultValues->get('loan_dependency'),
      'creditsCompleted' => $defaultValues->get('credits_completed'),
      'semester' => $defaultValues->get('semester'),
      'creditsHelpTextUndergrad' => $defaultValues->get('credits_help_text_undergrad'),
      'creditsHelpTextGraduate' => $defaultValues->get('credits_help_text_graduate'),
      'importantNotes' => $defaultValues->get('importantNotes'),
      'undergradDependentMaxOne' => $defaultValues->get('undergradDepOne'),
      'undergradDependentMaxTwo' => $defaultValues->get('undergradDepTwo'),
      'undegradDependentMaxThree' => $defaultValues->get('undergradDepThreePlus'),
      'undergradIndependentMaxOne' => $defaultValues->get('undergradIndependentOne'),
      'undergradIndependentMaxTwo' => $defaultValues->get('undergradIndependentTwo'),
      'undergradIndependentMaxThree' => $defaultValues->get('undergradIndependentThreePlus'),
      'graduateMax' => $defaultValues->get('graduate'),
      'campusList' => $defaultValues->get('campus_list'),
      'creditSemesterLimit' => $defaultValues->get('credit_semester_limit'),
      'submitButtonText' => $defaultValues->get('submit_button_text'),
      'undergradMaxCreditLimit' => $defaultValues->get('undergrad_max_full_credit_limit'),
      'graduateMaxCreditLimit' => $defaultValues->get('graduate_max_full_credit_limit'),
      'undergradLeastCreditLimit' => $defaultValues->get('undergrad_least_full_credit_limit'),
      'graduateLeastCreditLimit' => $defaultValues->get('graduate_least_full_credit_limit'),
      'currentAcadYear' => $defaultValues->get('current_acad_year'),
      'resultsDisclaimer' => $defaultValues->get('results_disclaimer'),
      'resultsAbout' => $defaultValues->get('results_about'),
    ];
    
    // dpm($defaultValuesfromAdminForm,'Default Values Array to React');.
    $build['react_loan_proration_block'] = [
      '#markup' => '<div id="loan-proration-block"></div>',
      '#attached' => [
         'library' => [
          'asu_cost_comparison_tool/loan_proration_tool',
        ], 
        'drupalSettings' => [
          'asu_loan_proration_tool' => $loanProrationAdminFormFields,
        ],
      ],
    ];

    return $build;
  }

}
