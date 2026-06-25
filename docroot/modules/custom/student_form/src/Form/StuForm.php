<?php

namespace Drupal\student_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

use Drupal\webform\Entity\WebformSubmission;

class StuForm extends FormBase {


    protected $currentRequest;

    public function __construct(Request $currentRequest) {
        $this->currentRequest = $currentRequest;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('request_stack')->getCurrentRequest()
        );
    }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_student_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $submission_id = NULL) {

    // Get submission_id from URL (GET param)
    // $submission_id = $this->currentRequest->query->get('submission_id');
    // $submission_id = $this->currentRequest->query->get('submission_id');

    // Store submission_id in form state
    $form_state->set('submission_id', $submission_id);

      // Load the submission entity.

      $webform_submission = WebformSubmission::load($submission_id);
      $student_name="";
      $content = "";

      if ($webform_submission) {

        // Get all submitted data.
        $data = $webform_submission->getData();

        // Access specific fields.
        $student_name = $data['student_name'] ?? '';
        $student_email = $data['student_email'] ?? '';
        $asu_id = $data['asu_id'] ?? '';
        $agreed_to_pay = $data['agreed_to_pay'] ?? '';
        $start_time_as_per_record = $data['start_time_as_per_record'] ?? '';
        $month = $data['month'] ?? '';
        $beginning	 = $data['beginning'] ?? '';
        $waived_amount	 = $data['waived_amount'] ?? '';
        // $branch = $data['branch'] ?? '';

        // Print or use the data.
        \Drupal::logger('custom_form')->info('Name: @name, Email: @email', [
          '@name' => $student_name,
          '@email' => $student_email,
        ]);

        $content = "<h5>ASU ID: ".$asu_id."</h5>
            <h5>Dear ".$student_name.",</h5>
            <h6>Our records indicate that you recently entered into a voluntary payment plan with our office as of ".$start_time_as_per_record.". As a reminder, you have agreed to pay <b>$".$agreed_to_pay."</b> per month by the ".$month." of each month, beginning ".$beginning.", in order to avoid collection agency assignment, which includes credit bureau reporting.</h6>
            <h6>Please confirm your understanding and acceptance of the following terms and conditions of this plan below so that our office may update your account to reflect this plan accordingly:</h6>
            <ul>
                <li>While participating in this payment plan, your access to university services such as registration and diploma release will be blocked.</li>
                <li>As a courtesy, no additional late fees will assess while you participate in this plan. We have waived ".$waived_amount." in additional late fees so that only 5 remain due from you as part of your balance. 
                <b><u>The late fee appeal will no longer be an option once this payment plan is accepted by you.</u></b></li>
                <li>Payments are not deducted from your banking account automatically, and you must remit on a monthly basis. For more information on payment options and how-to videos, please click here.</li>
                <li>Missed or shorted payments will result in default, and your account may be subject to outsourcing to an outside collection agency which includes negative credit reporting.</li>
                <li>If you are unable to meet the payment plan terms, please contact our office immediately to discuss your circumstances and options to avoid default.</li>
            </ul>";

      }
      else {
        \Drupal::logger('custom_form')->error('Webform submission not found with ID: @id', ['@id' => $submission_id]);
      }

      

    $form['content'] = [
      '#type' => 'markup',
      '#markup' => $content
    ];

    // $form['form_submission_id'] = array (
    //   '#type' => 'hidden',
    //   '#title' => t('submission_id'),
    //   '#default_value' => $submission_id,
    // );

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#value' => $student_name,
      '#prefix' => '<div style="padding-top:20px;">',
      '#suffix' => '</div>',
    ];

    $form['acknowledgement'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I acknowledge the terms and conditions.'),
      '#required' => TRUE,
      '#value' => true,
      '#prefix' => '<div style="padding-top:20px;">',
      '#suffix' => '</div>',
    ];

    // $form['button'] = [
    //   '#type' => 'button',
    //   '#value' => $this->t('Submit'),
    //   '#prefix' => '<div style="padding-top:20px;">',
    //   '#suffix' => '</div>',
    // ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#prefix' => '<div style="padding-top:20px;">',
      '#suffix' => '</div>',
    ];

    $form['content2'] = [
      '#type' => 'markup',
      '#markup' => '<div style="padding-top:20px;"><p><b>Student Business Services<br>
                    Arizona State University</b><br>
                    (480) 965-5220<br>
                    sbs@asu.edu</p></div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submission_id = $form_state->get('submission_id');
    
    $name = $form_state->getValue('name');
    $acknowledgement = $form_state->getValue('acknowledgement');
    $acknowledged_by = \Drupal::currentUser()->id();

    $webform_submission = WebformSubmission::load($submission_id);
    $data = $webform_submission->getData();
    $data["student_entered_name"] = $name;
    $data["student_acknowledgement"] = $acknowledgement;
    $data["form_submitted_by"] = $acknowledged_by;
    $webform_submission->setData($data);
    $webform_submission->save();

    $this->messenger()->addMessage($this->t('Thank you, @name, your form has been submitted.', ['@name' => $name]));
  }

}
