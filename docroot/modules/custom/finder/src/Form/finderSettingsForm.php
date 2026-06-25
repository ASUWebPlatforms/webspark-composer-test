<?PHP

// see
// https://www.drupal.org/docs/8/api/configuration-api/working-with-configuration-forms
//

namespace Drupal\finder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class finderSettingsForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'finder_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'finder.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('finder.settings');

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title'),
    );  

    $form['subtitle'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Subtitle'),
      '#default_value' => $config->get('subtitle'),
    ); 

    $form['question_header'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Question Header'),
      '#default_value' => $config->get('question_header'),
    );  

    $form['service_header'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Service Header'),
      '#default_value' => $config->get('service_header'),
    );

    $form['service_detail'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Service Detail'),
      '#format' => 'full_html',
      '#default_value' => $config->get('service_detail'),
    );

    $form['chart_header'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Chart Header'),
      '#default_value' => $config->get('chart_header'),
    );

    $form['email_form_header'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Email Header'),
      '#default_value' => $config->get('email_form_header'),
    );

    $form['email_address'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email Address'),
      '#default_value' => $config->get('email_address'),
    );

    $form['email_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email Name'),
      '#default_value' => $config->get('email_name'),
    );

    $form['email_body'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email Body'),
      '#default_value' => $config->get('email_body'),
    );

    $form['main_header'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Main header (text in green)'),
      '#default_value' => $config->get('main_header'),
    );

    $form['button_select_all'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Text in the "Select All" button'),
      '#default_value' => $config->get('button_select_all'),
    );

    $form['button_clear_selections'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Text in the "Clear Selections" button'),
      '#default_value' => $config->get('button_clear_selections'),
    );

    $form['footer_disclaimers'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Footer Disclaimers'),
      '#format' => 'full_html',
      '#default_value' => $config->get('footer_disclaimers'),
    );

    $form['asu_responsibilities'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('ASU Responsibilities'),
      '#format' => 'full_html',
      '#default_value' => $config->get('asu_responsibilities'),
    );

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
      // Retrieve the configuration
      \Drupal::configFactory()->getEditable('finder.settings')
      // Set the submitted configuration setting
      // You can set multiple configurations at once by making
      // multiple calls to set()
      ->set('title', $form_state->getValue('title'))
      ->set('subtitle', $form_state->getValue('subtitle'))
      ->set('question_header', $form_state->getValue('question_header'))
      ->set('service_header', $form_state->getValue('service_header'))
      ->set('service_detail', $values['service_detail']['value'])
      ->set('chart_header', $form_state->getValue('chart_header'))
      ->set('email_form_header', $form_state->getValue('email_form_header'))
      ->set('email_address', $form_state->getValue('email_address'))
      ->set('email_name', $form_state->getValue('email_name'))
      ->set('email_body', $form_state->getValue('email_body'))
      ->set('main_header', $form_state->getValue('main_header'))
      ->set('button_select_all', $form_state->getValue('button_select_all'))
      ->set('button_clear_selections', $form_state->getValue('button_clear_selections'))
      ->set('footer_disclaimers', $values['footer_disclaimers']['value'])
      ->set('asu_responsibilities', $values['asu_responsibilities']['value'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
