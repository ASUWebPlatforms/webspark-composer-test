<?php

namespace Drupal\analytics_operations\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Exception;

class CreateReportSyncForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_create_report_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    // TODO: Add option to choose which endpoint to use (prod or dev)
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will initiate a new AWS sync process at a user\'s choice.<br /><br />Please select Source System(s) and a time period in hours to look into the past.  If there was previously an error the system will look to restart at that step.'
      ),
    ];
    // TODO: Change this to checkboxes
    $form['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Source System (multiple allowed)'),
      '#options' => [
        'tableau' => 'Tableau',
        'powerbi' => 'PowerBI / Report Server',
        'onedrive' => 'Microsoft OneDrive / SharePoint'
      ],
      '#required' => true,
      '#multiple' => true,
    ];
    $form['period'] = [
      '#type' => 'number',
      '#title' => $this->t('Time Period (in whole number hours)'),
      '#default_value' => 1,
      '#min' => 1,
      '#required' => true,
    ];
    $form['step_help'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        '<strong>If you want to do a full scan</strong> (back to the beginning of these source systems), use a time period of 0.  Please be aware that these sync processes can take much longer because they are working through many more reports.'
      ),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    // TODO: Extract this into its own class, like the rest of the operations
    $source = $form_state->getValue('source');
    $period = (int)$form_state->getValue('period');
    $sourceObj = [];

    foreach ($source as $s) {
      $sourceObj[$s] = true;
    }

    $inputObj = [
      "period" => $period,
      "source" => $sourceObj,
      "status" => "start"
    ];

    // TODO: Move this into Pantheon Secrets
    $apiKey = "hCBYcX4MibafaBDi0J3DsyOoNQuTx6r63gGmnETc"; // prod

    $reqBody = json_encode([
      "input" => json_encode($inputObj),
      "stateMachineArn" => "arn:aws:states:us-west-2:047447279153:stateMachine:analytics-portal-step-function-prod",
    ]);
    try {
      $client = Drupal::httpClient();
      $result = $client->request(
        'POST',
        'https://2mvbnc2n40.execute-api.us-west-2.amazonaws.com/prod/state_machine_api',
        [
          'body' => $reqBody,
          'headers' => [
            "Content-Type" => "application/json",
            "x-api-key" => $apiKey
          ]
        ]
      );
      // TODO: Move the result body into a Drupal log message instead
      Drupal::logger('analytics_operations')->notice('Create Report Sync operation complete.');
      Drupal::messenger()->addMessage(
        t("Please wait 15 minutes or so for the Sync Process to run. API request response body: " . $result->getBody())
      );
    } catch (Exception $error) {
      $logger = Drupal::logger('HTTP Client error');
      $logger->error($error->getMessage());
      Drupal::messenger()->addMessage(t("Error!" . $error->getMessage()));
      $result = $error->getMessage();
    }

    if (!str_contains($result->getBody(), "startDate")) {
      Drupal::messenger()->addMessage(
        t(
          "There was an error with this request at AWS.  Please provide the request response body above to Grace Gu (guirongg@asu.edu) or someone on her team."
        )
      );
    }
  }
}
