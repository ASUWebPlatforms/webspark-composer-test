<?php

namespace Drupal\contact_cron_email\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\Response;

class CronEmailController extends ControllerBase {

  /**
   * Generate and serve the CSV file for download.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The CSV file response.
   */
  public function downloadCsv() {
    $webform_id = 'constant_contact';

    // Fetch webform submissions.
    $submissions = WebformSubmission::loadMultiple(
      \Drupal::entityQuery('webform_submission')
        ->condition('webform_id', $webform_id)
        ->accessCheck(false)
        ->execute()
    );

    // Create CSV content.
    $header = ['Submission ID', 'Submitted At', 'Data'];
    $rows = [];
    foreach ($submissions as $submission) {
      /** @var \Drupal\webform\Entity\WebformSubmission $submission */
      $data = $submission->getData();
      $rows[] = [
        $submission->id(),
        date('Y-m-d H:i:s', $submission->getCreatedTime()),
        json_encode($data),
      ];
    }

    // Create the CSV content in memory.
    $csv_content = fopen('php://temp', 'r+');
    fputcsv($csv_content, $header);
    foreach ($rows as $row) {
      fputcsv($csv_content, $row);
    }
    rewind($csv_content);
    $csv_data = stream_get_contents($csv_content);
    fclose($csv_content);

    // Create a response for the CSV file download.
    $response = new Response($csv_data);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="constant_contact_results.csv"');

    return $response;
  }

}
