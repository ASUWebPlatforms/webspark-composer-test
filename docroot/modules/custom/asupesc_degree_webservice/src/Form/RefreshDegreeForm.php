<?php
namespace Drupal\asupesc_degree_webservice\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the RefreshDegreeForm form controller.
 *
 * This form is used to refresh degrees in the following tables:
 * - asu_ugrad_interest_category_degrees
 * - asu_grad_interest_category_degrees
 * - asu_online_degrees

 * @see \Drupal\Core\Form\FormBase
 */
class RefreshDegreeForm extends FormBase {

    /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Refresh the asu degrees table in the database'),
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['ugrad_degree_refresh'] = [
        '#type' => 'submit',
        '#value' => $this->t('Populate/refresh asu UNDERGRAD degrees table'),
        '#submit' => ['::ugradFormSubmit'],
    ];
    $form['actions']['grad_degree_refresh'] = [
        '#type' => 'submit',
        '#value' => $this->t('Populate/refresh asu GRAD degrees table'),
        '#submit' => ['::gradFormSubmit'],
    ];
    $form['actions']['online_degree_refresh'] = [
      '#type' => 'submit',
      '#value' => $this->t('Populate/refresh asu ONLINE degrees table'),
      '#submit' => ['::onlineFormSubmit'],
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'asupesc_degree_webservice_refresh_degree_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * This would normally be replaced by code that actually does something
     * with the title.
     */
  }

    /**
     * Implements submit callback for Undergrad refresh button.
     *
     * @param array $form
     *   Form render array.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Current state of the form.
     */
    public function ugradFormSubmit(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();

    //        /** @var \Drupal\Core\Database\Connection $connection */
    //        $connection = \Drupal::database();
    //        $connection->truncate('asu_ugrad_interest_category_degrees')->execute(); //Empty the table content before refreshing the table with new data

        $batch = $this->generateBatch1();
        batch_set($batch);
    }

    /**
     * Generate Batch 1.
     *
     * Batch 1 will process one item at a time.
     *
     * This creates an operations array defining what batch 1 should do, including
     * what it should do when it's finished. In this case, each operation is the
     * same and by chance even has the same $nid to operate on, but we could have
     * a mix of different types of operations in the operations array.
     *
     */
    public function generateBatch1() {
        $operations = [];
        //foreach($all_categories as $cat_id => $cat_name) {
            // Each operation is an array consisting of
            // - The function to call.
            // - An array of arguments to that function.
            $operations[] = [
                'asupesc_degree_webservice_data_insert_ugrad',
                [],
            ];
        //}

        $batch = [
            'title' => $this->t('Getting ugrad degrees.', []),
            'operations' => $operations,
            'finished' => 'asupesc_degree_webservice_finished',
        ];
        return $batch;
    }

    /**
     * Implements submit callback for Grad refresh button.
     *
     * @param array $form
     *   Form render array.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Current state of the form.
     */
    public function gradFormSubmit(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();

//        /** @var \Drupal\Core\Database\Connection $connection */
//        $connection = \Drupal::database();
//        $connection->truncate('asu_grad_interest_category_degrees')->execute(); //Empty the table content before refreshing the table with new data

        $batch = $this->generateBatch2();
        batch_set($batch);
    }

    public function generateBatch2() {
        $operations = [];
        $operations[] = [
            'asupesc_degree_webservice_data_insert_grad',
            [],
        ];
        $batch = [
            'title' => $this->t('Getting grad degrees.', []),
            'operations' => $operations,
            'finished' => 'asupesc_degree_webservice_finished',
        ];
        return $batch;
    }

    /**
     * Implements submit callback for Grad refresh button.
     *
     * @param array $form
     *   Form render array.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Current state of the form.
     */
    public function onlineFormSubmit(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();

//        /** @var \Drupal\Core\Database\Connection $connection */
//        $connection = \Drupal::database();
//        $connection->truncate('asu_online_degrees')->execute(); //Empty the table content before refreshing the table with new data

        $batch = $this->generateBatch3();
        batch_set($batch);
    }

    public function generateBatch3() {
        $operations = [];
        $operations[] = [
            'asupesc_degree_webservice_data_insert_online',
            [],
        ];
        $batch = [
            'title' => $this->t('Getting online degrees.', []),
            'operations' => $operations,
            'finished' => 'asupesc_degree_webservice_finished',
        ];
        return $batch;
    }
}
