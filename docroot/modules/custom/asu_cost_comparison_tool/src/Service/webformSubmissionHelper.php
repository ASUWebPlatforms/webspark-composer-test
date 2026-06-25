<?php

namespace Drupal\asu_cost_comparison_tool\Service;

use Drupal\webform\Entity\WebformSubmission;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class webformSubmissionHelper {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Hlper functions.
   */
  private function isNumericOrEmpty($value): bool {
    // Null is allowed.
    if ($value === NULL) {
      return TRUE;
    }
  
    // If it's a string, trim whitespace and treat empty string as allowed.
    if (is_string($value)) {
      $trimmed = trim($value);
      if ($trimmed === '') {
        return TRUE;
      }
      // Use trimmed value for numeric check.
      return is_numeric($trimmed);
    }
  
    // Empty string (non-string) — treat as allowed.
    if ($value === '') {
      return TRUE;
    }
  
    // For numeric types (int/float) accept.
    return is_numeric($value);
  }
  

  /**
   *
   */
  private function isPlainText($value): bool {
    return is_string($value) && trim($value) !== '';
  }

  /**
   * Validate numeric values ONLY for costs and aid (scholarships & grants).
   *
   * @param array $data
   *   The decoded JSON under "data".
   *
   * @return array
   *   List of errors. Empty array = valid.
   */
  private function validateNumericCostsAndAid(array $data): array {
    $errors = [];

    // Helper.
    $isNumericOrEmpty = function ($v) {
      return is_numeric($v);
    };

    /* ---------- COSTS ---------- */
    if (!empty($data['costs']) && is_array($data['costs'])) {
      foreach ($data['costs'] as $rowId => $row) {
        if (!is_array($row)) {
          continue;
        }
        if ($rowId = '01') {
          $costLabel = 'Tuition & fees';
        }
        if ($rowId = '02') {
          $costLabel = 'Books & supplies';
        }
        if ($rowId = '03') {
          $costLabel = 'Housing';
        }
        if ($rowId = '04') {
          $costLabel = 'Transportation';
        }
        foreach (['asu', 'school2', 'school3'] as $col) {
          $val = $row[$col] ?? '';
          if(!empty($val)){
            if (!$isNumericOrEmpty($val)) {
              $errors[] = "Invalid numeric value at " . $data['labels'][$col] . " " . $costLabel . " cost";
            }
            else{
              $errors[] = '';
            }
          }
        }
      }
    }

    /* ---------- AID: SCHOLARSHIPS ---------- */
    if (!empty($data['aid']['scholarships']) && is_array($data['aid']['scholarships'])) {
      foreach ($data['aid']['scholarships'] as $i => $sch) {
        if (!is_array($sch)) {
          continue;
        }

        foreach (['asu', 'school2', 'school3'] as $col) {
          $val = $sch[$col] ?? '';
          if (!$isNumericOrEmpty($val)) {
            $errors[] = "Invalid numeric value at " . $data['labels'][$col] . " scholarship cost";
          }
        }
      }
    }

    /* ---------- AID: GRANTS ---------- */
    if (!empty($data['aid']['grants']) && is_array($data['aid']['grants'])) {
      foreach ($data['aid']['grants'] as $i => $grant) {
        if (!is_array($grant)) {
          continue;
        }

        foreach (['asu', 'school2', 'school3'] as $col) {
          $val = $grant[$col] ?? '';
          if (!$isNumericOrEmpty($val)) {
            $errors[] = "Invalid numeric value at " . $data['labels'][$col] . "grant cost";
          }
        }
      }
    }

    return $errors;
  }

  /**
   *
   */
  public function createSubmission($webform_id, array $data, $uid = 0) {
    \Drupal::logger('asu_cost_comparison_tool')->notice('Creating submission for webform ID: @webform_id with data: @data', [
      '@webform_id' => $webform_id,
      '@data' => print_r($data, TRUE),
    ]);

     $encoded = json_encode($data);

   /* $errors = $this->validateNumericCostsAndAid($data);
    dpm($errors);

    if (!empty($errors)) {
      return new JsonResponse([
        'status' => 'error',
        'errors' => $errors,
      ], 400);
    } */
   //else {
      // dpm($data['aid']['scholarships'],'scholar');.
      if (!empty($data['aid']['scholarships'])) {
        foreach ($data['aid']['scholarships'] as $key => $value) {
          $scholar_array = $data['aid']['scholarships'];
        }
      }

      if (is_array($data['campus'])) {
        $campus = implode("", $data['campus']);
      }
      else{
        $campus = $data['campus'];
      }

      if (is_array($data['resident'])) {
        $resident = implode("", $data['resident']);
      }
      else{
        $resident = $data['resident'];
      }

      //dpm($data);
      $submission = WebformSubmission::create([
        'webform_id' => $webform_id,
        'uid' => $uid,
        'data' => [
          'payload' => $encoded,
          'asu' => $data['costs']['01']['asu'] ?? '',
          'school_2' => $data['costs']['01']['school2'] ?? '',
          'school_3' => $data['costs']['01']['school3'] ?? '',
          'asu_books' => $data['costs']['02']['asu'] ?? '',
          'school_2_books' => $data['costs']['02']['school2'] ?? '',
          'school_3_books' => $data['costs']['02']['school3'] ?? '',
          'asu_housing' => $data['costs']['03']['asu'] ?? '',
          'school_2_housing' => $data['costs']['03']['school2'] ?? '',
          'school_3_housing' => $data['costs']['03']['school3'] ?? '',
          'asu_transportation' => $data['costs']['04']['asu'] ?? '',
          'school_2_transportation' => $data['costs']['04']['school2'] ?? '',
          'school_3_transportation' => $data['costs']['04']['school3'] ?? '',
          'residency' => $resident,
          'campus' => $campus,

        // Scholarships.
          'asu_scholarship_1' => $data['aid']['scholarships'][0]['asu'] ?? '',
          'school_2_scholarship_1' => $data['aid']['scholarships'][0]['school2'] ?? '',
          'school_3_scholarship_1' => $data['aid']['scholarships'][0]['school3'] ?? '',
          'asu_scholarship_2' => $data['aid']['scholarships'][1]['asu'] ?? '',
          'school_2_scholarship_2' => $data['aid']['scholarships'][1]['school2'] ?? '',
          'school_3_scholarship_2' => $data['aid']['scholarships'][1]['school3'] ?? '',
          'asu_scholarship_3' => $data['aid']['scholarships'][3]['asu'] ?? '',
          'school_2_scholarship_3' => $data['aid']['scholarships'][3]['school2'] ?? '',
          'school_3_scholarship_3' => $data['aid']['scholarships'][3]['school3'] ?? '',

        // Grants.
          'asu_grant_1' => $data['aid']['grants'][0]['asu'] ?? '',
          'school_2_grant_1' => $data['aid']['grants'][0]['school2'] ?? '',
          'school_3_grant_1' => $data['aid']['grants'][0]['school3'] ?? '',
          'asu_grant_2' => $data['aid']['grants'][1]['asu'] ?? '',
          'school_2_grant_2' => $data['aid']['grants'][1]['school2'] ?? '',
          'school_3_grant_2' => $data['aid']['grants'][1]['school3'] ?? '',
          'asu_grant_3' => $data['aid']['grants'][3]['asu'] ?? '',
          'school_2_grant_3' => $data['aid']['grants'][3]['school2'] ?? '',
          'school_3_grant_3' => $data['aid']['grants'][3]['school3'] ?? '',

        // Loams.
          'asu_subsidized_loan' => $data['aid']['loansRow']['subloansAsu'] ?? $data['aid']['loansRow']['subloansAsu'],
          'school_2_subsidized_loan' => $data['aid']['loansRow']['subloansSchool2'] ?? $data['aid']['loansRow']['subloansSchool2'],
          'school_3_subsidized_loan' => $data['aid']['loansRow']['subloansSchool3'] ?? $data['aid']['loansRow']['subloansSchool3'],

          'asu_unsubsidized_loan' => $data['aid']['loansRow']['unsubloansAsu'] ?? $data['aid']['loansRow']['unsubloansAsu'],
          'school_2_unsubsidized_loan' => $data['aid']['loansRow']['unsubloansSchool2'] ?? $data['aid']['loansRow']['unsubloansSchool2'],
          'school_3_unsubsidized_loan' => $data['aid']['loansRow']['unsubloansSchool3'] ?? $data['aid']['loansRow']['unsubloansSchool3'],

          'asu_parent_plus_loan' => $data['aid']['loansRow']['pplusloansAsu'] ?? $data['aid']['loansRow']['pplusloansAsu'],
          'school_2_parent_plus_loan' => $data['aid']['loansRow']['pplusloansSchool2'] ?? $data['aid']['loansRow']['pplusloansSchool2'],
          'school_3_parent_plus_loan' => $data['aid']['loansRow']['pplusloansSchool3'] ?? $data['aid']['loansRow']['pplusloansSchool3'],

        // Totals.
          'asu_total_annual_cost' => $data['aid']['totals']['asu'] ?? $data['aid']['totals']['asu'],
          'school_2_total_annual_cost' => $data['aid']['totals']['school2'] ?? $data['aid']['totals']['school2'],
          'school_3_total_annual_cost' => $data['aid']['totals']['school3'] ?? $data['aid']['totals']['school3'],

        // Net price.
          'asu_net_price_total' => $data['aid']['netPrices']['asu'] ?? $data['aid']['netPrices']['asu'],
          'school_2_net_price_total' => $data['aid']['netPrices']['school2'] ?? $data['aid']['netPrices']['school2'],
          'school_3_net_price_total' => $data['aid']['netPrices']['school3'] ?? $data['aid']['netPrices']['school3'],

        // Loan totals.
          'asu_total_loans_offered' => $data['aid']['loanTotals']['asu'] ?? $data['aid']['loanTotals']['asu'],
          'school_2_total_loans_offered' => $data['aid']['loanTotals']['school2'] ?? $data['aid']['loanTotals']['school2'],
          'school_3_total_loans_offered' => $data['aid']['loanTotals']['school3'] ?? $data['aid']['loanTotals']['school3'],

        // Remaining totals.
          'asu_remaining_costs_total' => $data['aid']['remainingCosts']['asu'] ?? $data['aid']['remainingCosts']['asu'],
          'school_2_reminaing_costs_total' => $data['aid']['remainingCosts']['school2'] ?? $data['aid']['remainingCosts']['school2'],
          'school_3_reminaing_costs_total' => $data['aid']['remainingCosts']['school3'] ?? $data['aid']['remainingCosts']['school3'],

          'school_2_name' => $data['labels']['school2'] ?? $data['labels']['school2'],
          'school_3_name' => $data['labels']['school3'] ?? $data['labels']['school3'],
        ],
      ]);
      $submission->save();

      return $submission;
     /*  return new JsonResponse([
        'status' => 'success',
        'sid' => $submission->id(),
        // getData() returns the array you saved under 'data'
        'data' => $submission->getData(),
      ], 201); */
    }
 // }

}
