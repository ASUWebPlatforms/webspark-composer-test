<?php

namespace Drupal\asu_cost_comparison_tool\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * @WebformHandler(
 *   id = "email_cost_comparison_handler",
 *   label = @Translation("Email Cost Comparison handler"),
 *   category = @Translation("Custom"),
 *   description = @Translation("Sends a cost comparison tool confirmation email after email field is updated."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED
 * )
 */
class EmailCostComparisonHandler extends WebformHandlerBase {

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $data = $webform_submission->getData();
    // dpm($data);
    $payload = json_decode($data['payload'], TRUE);
   
    //$email_content = $payload['email_html'] ?? [];
    $email_content_data = $this->build_cost_email_param($payload);
    $email_content = $email_content_data['body_html'];
    $plain = $email_content_data['body_plain'];
   // dpm($email_content_data['body_html']);
    //$email_content = 'Hello';
     \Drupal::logger('asu_cost_comparison_tool_email')->notice(
    'Data: <pre>@data</pre>',
    ['@data' => print_r($email_content, TRUE)]
    ); 

    $config = \Drupal::config('asu_cost_comparison_tool.settings');
    $from_email = $config->get('email_from');
    $reply_to_email = $config->get('email_reply_to');

    // Make sure email field exists.
    if (!empty($data['email'])) {
      $email = $data['email'];
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'asu_cost_comparison_tool';
      $key = 'cost_tool_confirmation';
      $to = $email;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $params = [
        'subject' => 'ASU cost comparison confirmation',
        'body_html' => $email_content,
        'body_plain' => $plain,
        'is_html' => TRUE,
        'reply-to' => $reply_to_email,
      ];
      
      $send = TRUE;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if (!empty($result) && !empty($result['result'])) {
        \Drupal::logger('asu_cost_comparison_tool_email')->notice('Sent ASU cost comparison confirmation to %email for submission %sid', ['%email' => $to, '%sid' => $webform_submission->id()]);
      }
      else {
        \Drupal::logger('asu_cost_comparison_tool_email')->error('Failed to send ASU Cost Comparison Tool confirmation to %email for submission %sid; result: @r', ['%email' => $to, '%sid' => $webform_submission->id(), '@r' => print_r($result, TRUE)]);
      }

    }
  }

  /**
 * Build stacked (div) email params from payload, removing empty values.
 *
 * @param array $payload
 *   Decoded payload array.
 *
 * @return array
 */
public function build_cost_email_param(array $payload): array {
  // Helper: treat '' and null and empty arrays as empty. Keep numeric 0.
  $is_empty_value = function ($v) {
    if ($v === '' || $v === null) {
      return true;
    }
    if (is_array($v)) {
      return count($v) === 0;
    }
    return false;
  };

  // Remove empty values (but keep zeros).
  $clean = function ($data) use (&$clean, $is_empty_value) {
    if (!is_array($data)) {
      return $data;
    }
    $out = [];
    foreach ($data as $k => $v) {
      if (is_array($v)) {
        $cv = $clean($v);
        if (!$is_empty_value($cv)) {
          $out[$k] = $cv;
        }
      } else {
        // keep numeric 0, non-empty strings, everything else except ''/null
        if (!$is_empty_value($v) || $v === 0 || $v === '0') {
          $out[$k] = $v;
        }
      }
    }
    return $out;
  };

  $payload = $clean($payload);

  // Safe escape and money formatting helpers
  $esc = function ($s) {
    return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  };

  $fmt_money = function ($v) {
    if ($v === '' || $v === null || $v === false) return '';
    // keep numeric zeros, strip non numeric chars
    $clean = preg_replace('/[^\d\.\-]/', '', (string) $v);
    if ($clean === '' || !is_numeric($clean)) return '';
    return '$' . number_format((float) $clean, 0, '.', ',');
  };

  // Labels and school names with fallbacks
  $labels = $payload['labels'] ?? [];
  $label_asu = $labels['asu'] ?? 'Arizona State University';
  $label_s2  = $labels['school2'] ?? 'School 2';
  $label_s3  = $labels['school3'] ?? 'School 3';

  // Cost name mapping
  $cost_names = [
    '01' => 'Tuition & fees',
    '02' => 'Books & supplies',
    '03' => 'Housing',
    '04' => 'Transportation',
  ];

  $costs = $payload['costs'] ?? [];
  $aid = $payload['aid'] ?? [];
  $totals = $payload['totals'] ?? [];
  $timestamp = $payload['timestamp'] ?? time();

  if (!is_numeric($timestamp)) {
    $timestamp = strtotime($timestamp);
  }
  $date = \Drupal::service('date.formatter')->format((int) $timestamp, 'custom', 'F j, Y g:i A');

  $resident_code = $payload['resident'] ?? 'AZ';

  $resident_map = [
    'AZ' => 'Arizona resident',
    'non-az' => 'Non resident',
    'intl' => 'International student',
  ];

  $resident = $resident_map[$resident_code] ?? 'Arizona resident';

  $campus_code = $payload['campus'] ?? 'oncampus';

  $campus_map = [
    'oncampus' => 'On campus',
    'with_parents' => 'With my parents',
    'offcampus' => 'Off campus',
  ];

  $campus = $campus_map[$campus_code] ?? 'On campus';

  // Begin HTML wrapper
  $html = '<div style="font-family:Arial,Helvetica,sans-serif;color:#222;line-height:1.4;">';
  $html .= '<h2 style="margin:0 0 12px 0;font-size:18px;">ASU cost Comparison Summary</h2>';

  // Reusable small table row generator (email safe)
  $row_table = function ($label, $value) use ($esc) {
    $label_html = $esc($label);
    $value_html = $value !== '' ? $value : '&nbsp;';
    $r  = '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;border-top:1px solid #f2f2f2;margin:0;">';
    $r .= '<tr>';
    $r .= '<td style="padding:8px 0;font-size:14px;vertical-align:top;text-align:left;">' . $label_html . '</td>';
    $r .= '<td style="padding:8px 0;font-size:15px;font-weight:600;vertical-align:top;text-align:right;width:1px;white-space:nowrap;">' . $value_html . '</td>';
    $r .= '</tr></table>';
    return $r;
  };

  // Costs section: each cost item -> stacked card
  if (!empty($costs)) {
    $html .= '<div style="margin-bottom:12px;">';
    foreach ($cost_names as $id => $label) {
      if (empty($costs[$id])) continue;
      $row = $costs[$id];

      // If entire row empty, skip
      if (!is_array($row) || (empty($row['asu']) && empty($row['school2']) && empty($row['school3']))) {
        continue;
      }

      $html .= '<div style="border:1px solid #e0e0e0;margin-bottom:8px;padding:10px;background:#fff;">';
      $html .= '<div style="font-weight:600;margin-bottom:8px;">' . $esc($label) . '</div>';

      $schools = [
        ['key' => 'asu',     'label' => $label_asu, 'value' => $row['asu'] ?? null],
        ['key' => 'school2', 'label' => $label_s2,  'value' => $row['school2'] ?? null],
        ['key' => 'school3', 'label' => $label_s3,  'value' => $row['school3'] ?? null],
      ];

      foreach ($schools as $s) {
        // show if value exists (including numeric 0) OR label is not empty
        if ($s['value'] !== null || trim((string)$s['label']) !== '') {
          $val = $fmt_money($s['value']);
          $html .= $row_table($s['label'], $val);
        }
      }

      $html .= '</div>'; // card end
    }
    $html .= '</div>';
  }

  // Scholarships section (stacked list)
  $html .= '<div style="margin-bottom:12px;">';
  $html .= '<div style="font-weight:700;margin-bottom:8px;">Scholarships</div>';
  if (!empty($aid['scholarships']) && is_array($aid['scholarships'])) {
    $found = false;
    foreach ($aid['scholarships'] as $idx => $row) {
      if (!is_array($row)) continue;
      $hasAny = false;
      foreach (['asu','school2','school3'] as $k) {
        if (isset($row[$k]) && $row[$k] !== '') { $hasAny = true; break; }
      }
      if (!$hasAny) continue;
      $found = true;

      $html .= '<div style="border:1px solid #eaeaea;padding:8px;margin-bottom:6px;">';
      $html .= '<div style="font-weight:600;margin-bottom:6px;">Scholarship ' . ($idx + 1) . '</div>';
      if (isset($row['asu']))    $html .= $row_table($label_asu, $fmt_money($row['asu']));
      if (isset($row['school2'])) $html .= $row_table($label_s2, $fmt_money($row['school2']));
      if (isset($row['school3'])) $html .= $row_table($label_s3, $fmt_money($row['school3']));
      $html .= '</div>';
    }
    if (!$found) {
      $html .= '<div style="color:#666;font-size:14px;">No scholarships entered</div>';
    }
} else {
    $html .= '<div style="color:#666;font-size:14px;">No scholarships entered</div>';
}
  $html .= '</div>';

  // Grants section
  $html .= '<div style="margin-bottom:12px;">';
  $html .= '<div style="font-weight:700;margin-bottom:8px;">Grants</div>';
  if (!empty($aid['grants']) && is_array($aid['grants'])) {
    $found = false;
    foreach ($aid['grants'] as $idx => $row) {
      if (!is_array($row)) continue;
      $hasAny = false;
      foreach (['asu','school2','school3'] as $k) {
        if (isset($row[$k]) && $row[$k] !== '') { $hasAny = true; break; }
      }
      if (!$hasAny) continue;
      $found = true;

      $html .= '<div style="border:1px solid #eaeaea;padding:8px;margin-bottom:6px;">';
      $html .= '<div style="font-weight:600;margin-bottom:6px;">Grant ' . ($idx + 1) . '</div>';
      if (isset($row['asu']))    $html .= $row_table($label_asu, $fmt_money($row['asu']));
      if (isset($row['school2'])) $html .= $row_table($label_s2, $fmt_money($row['school2']));
      if (isset($row['school3'])) $html .= $row_table($label_s3, $fmt_money($row['school3']));
      $html .= '</div>';
    }
    if (!$found) {
      $html .= '<div style="color:#666;font-size:14px;">No grants entered</div>';
    }
  } else {
    $html .= '<div style="color:#666;font-size:14px;">No grants entered</div>';
  }
  $html .= '</div>';

  // Loans summary (if present)
  if (!empty($aid['loanTotals']) || !empty($aid['loansRow'])) {
    $html .= '<div style="margin-bottom:12px;">';
    $html .= '<div style="font-weight:700;margin-bottom:8px;">Loans</div>';

    if (!empty($aid['loanTotals'])) {
      $lt = $aid['loanTotals'];
      $html .= '<div style="border:1px solid #eaeaea;padding:8px;margin-bottom:6px;">';
      $html .= $row_table($label_asu . ' Total loans', $fmt_money($lt['asu'] ?? ''));
      $html .= $row_table($label_s2  . ' Total loans', $fmt_money($lt['school2'] ?? ''));
      $html .= $row_table($label_s3  . ' Total loans', $fmt_money($lt['school3'] ?? ''));
      $html .= '</div>';
    }

    $html .= '</div>';
  }

  // Summary (totals/net/remaining)
  $html .= '<div style="margin-bottom:12px;">';
  $html .= '<div style="font-weight:700;margin-bottom:8px;">Summary</div>';
  $html .= '<div style="border:1px solid #eaeaea;padding:8px;">';
  if (!empty($totals)) {
    $html .= $row_table('Total annual cost (' . $label_asu . ')', $fmt_money($totals['asu'] ?? ''));
    $html .= $row_table('Total annual cost (' . $label_s2  . ')', $fmt_money($totals['school2'] ?? ''));
    $html .= $row_table('Total annual cost (' . $label_s3  . ')', $fmt_money($totals['school3'] ?? ''));
  }

  if (!empty($aid['netPrices'])) {
    $html .= '<div style="margin-top:6px;">';
    $html .= $row_table('Net price (' . $label_asu . ')', $fmt_money($aid['netPrices']['asu'] ?? ''));
    $html .= $row_table('Net price (' . $label_s2  . ')', $fmt_money($aid['netPrices']['school2'] ?? ''));
    $html .= $row_table('Net price (' . $label_s3  . ')', $fmt_money($aid['netPrices']['school3'] ?? ''));
    $html .= '</div>';
  }

  if (!empty($aid['remainingCosts'])) {
    $html .= '<div style="margin-top:6px;">';
    $html .= $row_table('Remaining (' . $label_asu . ')', $fmt_money($aid['remainingCosts']['asu'] ?? ''));
    $html .= $row_table('Remaining (' . $label_s2  . ')', $fmt_money($aid['remainingCosts']['school2'] ?? ''));
    $html .= $row_table('Remaining (' . $label_s3  . ')', $fmt_money($aid['remainingCosts']['school3'] ?? ''));
    $html .= '</div>';
  }

  $html .= '</div></div>';

  // Footer metadata
  $html .= '<div style="margin-top:8px;font-size:13px;color:#555">';
  if (!empty($payload['resident'])) $html .= '<div><strong>Residency:</strong> ' . $resident . '</div>';
  if (!empty($payload['campus'])) $html .= '<div><strong>Campus:</strong> ' . $campus . '</div>';
  $html .= '<div><strong>Snapshot:</strong> ' . $date . '</div>';
  $html .= '</div>';

  $html .= '</div>'; // wrapper end

  // Build simple plain-text fallback (stacked)
  $plain = "Cost Comparison Snapshot\n\n";
  foreach ($cost_names as $id => $label) {
    if (empty($costs[$id])) continue;
    $plain .= $label . ":\n";
    $plain .= "  {$label_asu}: " . ($costs[$id]['asu'] ?? '') . "\n";
    $plain .= "  {$label_s2}: " . ($costs[$id]['school2'] ?? '') . "\n";
    $plain .= "  {$label_s3}: " . ($costs[$id]['school3'] ?? '') . "\n";
    $plain .= "\n";
  }
  if (!empty($totals)) {
    $plain .= "Totals: ASU " . ($totals['asu'] ?? '') . " | School2 " . ($totals['school2'] ?? '') . " | School3 " . ($totals['school3'] ?? '') . "\n";
  }
  $date_formatter = \Drupal::service('date.formatter');

  $plain .= 'Submitted on ' .$date. "\n";
  return [
    'body_html' => $html,
    'body_plain' => $plain,
  ];
}

}