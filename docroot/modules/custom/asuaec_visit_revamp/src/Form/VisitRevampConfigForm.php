<?php

namespace Drupal\asuaec_visit_revamp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin config page for Visir Revamp.
 */
class VisitRevampConfigForm extends ConfigFormBase {

  /**
   * Get form ID.
   */
  public function getFormId() {
    return 'visit_revamp_config_form';
  }

  /**
   * Get editable config names.
   */
  protected function getEditableConfigNames() {
    return ['asuaec_visit_revamp.settings'];
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asuaec_visit_revamp.settings');

    // Added on 4/17/2026.
    // Site name.
    $form['heading1'] = [
      '#markup' => "<h3>Site name</h3>",
    ];
    $form['sitename'] = [
      '#type' => 'textfield',
      '#title' => 'Acquia site name',
      '#maxlength' => 100,
      '#description' => t("For example: visitasu"),
      '#default_value' => $config->get('sitename') != '' ? $config->get('sitename') : 'visitasu',
    ];

    // Prod domain.
    $form['heading2'] = [
      '#markup' => "<h3>Prod domain</h3>",
    ];
    $form['proddomain'] = [
      '#type' => 'textfield',
      '#title' => 'Prod domain',
      '#maxlength' => 100,
      '#description' => t("For example: https://visit.asu.edu"),
      '#default_value' => $config->get('proddomain') != '' ? $config->get('proddomain') : 'https://visit.asu.edu',
    ];

    $form['heading3'] = [
      '#markup' => "<h3>End point</h3>",
    ];

    $form['post_url_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Production Post URL'),
      '#default_value' => $config->get('post_url_prod'),
      '#description' => $this->t('Endpoint for production: https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/register'),
    ];

    $form['post_url_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Development Post URL'),
      '#default_value' => $config->get('post_url_dev'),
      '#description' => $this->t('For example, endpoint for QA: https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register'),
    ];

    $form['heading4'] = [
      '#markup' => "<h3>Other Visit site configurations</h3>",
    ];

    $form['max_month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum month/year'),
      '#default_value' => $config->get('max_month'),
      '#description' => $this->t('Format: MM/YYYY, e.g. 09/2025. Allows dropdown to go up to this month.'),
    ];

    // Normalize calendar_paths from config to an array of lines.
    $stored_paths = $config->get('calendar_paths');
    if (is_array($stored_paths)) {
      $paths_array = $stored_paths;
    }
    elseif (is_string($stored_paths)) {
      // Support old versions where we saved a plain string.
      // Normalize line endings and split into lines.
      $normalized = str_replace(["\r\n", "\r"], "\n", $stored_paths);
      $paths_array = array_values(array_filter(array_map('trim', explode("\n", $normalized))));
      if (!$paths_array) {
        $paths_array = ['react-calendar'];
      }
    }
    else {
      // Default if nothing set yet.
      $paths_array = ['react-calendar'];
    }

    $form['calendar_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Calendar URLs'),
      '#description' => $this->t('One path per line, without the domain. Example: schedule, react-calendar'),
      '#default_value' => implode("\n", $paths_array),
    ];

    // Form IDs for Exp ASU form and "Other" form (new and old) (1/16/2026)
    $default_form_ids = [
    // Old Exp ASU form.
      'webform_submission_visit_form_node_24_add_form',
    // Old "Other" form.
      'webform_submission_registration_form_node_217080_add_form',
    // New Exp ASU form.
      'webform_submission_visit_form_revamp_node_282750_add_form',
    // New "Other" form.
      'webform_submission_registration_form_other_revamp_node_356301_add_form',
    ];

    $stored_form_ids = $config->get('form_ids_expasu_and_other');

    if (is_array($stored_form_ids)) {
      $form_ids_array = $stored_form_ids;
    }
    elseif (is_string($stored_form_ids)) {
      $normalized = str_replace(["\r\n", "\r"], "\n", $stored_form_ids);
      $form_ids_array = array_values(array_filter(array_map('trim', explode("\n", $normalized))));
    }
    else {
      // Default: show these IDs already in the textarea.
      $form_ids_array = $default_form_ids;
    }

    // If config exists but is empty, still fall back to defaults.
    if (empty($form_ids_array)) {
      $form_ids_array = $default_form_ids;
    }

    $form['form_ids_expasu_and_other'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Webform form IDs to pre-populate (Cancel flow)'),
      '#description' => $this->t('One form_id per line. These are the Webform form IDs that should be pre-populated when c-sid is present when user comes from Cancel form.'),
      '#default_value' => implode("\n", $form_ids_array),
    ];

    // ============================================================
    // Calendar filter config. Added on 5/11/2026.
    // ============================================================

    $form['heading6'] = [
      '#markup' => "<h3>Calendar filter presets</h3>",
    ];

    $form['calendar_interests'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Master interests JSON'),
      '#description' => $this->t('JSON map of student level (grad/ugrad) to interest keys and labels. Example: {"grad":{"stem":"STEM","arts":"Arts"},"ugrad":{"72":"Fashion","73":"Film and Media"}}'),
      '#default_value' => $config->get('calendar_interests') ? json_encode($config->get('calendar_interests'), JSON_PRETTY_PRINT) : "{\n  \"grad\": {\n    \"architecture_construction\": \"Architecture and Construction\",\n    \"arts\": \"Arts\",\n    \"business\": \"Business\",\n    \"communication_media\": \"Communication and Media\",\n    \"computing_mathematics\": \"Computing and Mathematics\",\n    \"education_teaching\": \"Education and Teaching\",\n    \"engineering_technology\": \"Engineering and Technology\",\n    \"entrepreneurship\": \"Entrepreneurship\",\n    \"health_wellness\": \"Health and Wellness\",\n    \"humanities\": \"Humanities\",\n    \"interdisciplinary_studies\": \"Interdisciplinary Studies\",\n    \"law_justice_public_service\": \"Law, Justice and Public Service\",\n    \"science\": \"Science\",\n    \"social_behavioral_sciences\": \"Social and Behavioral Sciences\",\n    \"sustainability\": \"Sustainability\",\n    \"stem\": \"STEM\"\n  },\n  \"ugrad\": {\n    \"25\": \"Anthropology, Sociology and Cultural Studies\",\n    \"26\": \"Architecture, Construction and Design\",\n    \"27\": \"Business\",\n    \"28\": \"Communication and Languages\",\n    \"29\": \"Computer Science, Software Engineering and Mathematics\",\n    \"30\": \"Criminology and Forensics\",\n    \"31\": \"Earth, Space and Flight\",\n    \"32\": \"Education and Teaching\",\n    \"33\": \"Engineering\",\n    \"34\": \"Undecided/Exploratory/Many Interests\",\n    \"35\": \"Fine Arts and Performance\",\n    \"36\": \"Health and Wellness\",\n    \"37\": \"History, Philosophy and Humanities\",\n    \"38\": \"Journalism\",\n    \"39\": \"Nursing\",\n    \"40\": \"Pre-health\",\n    \"41\": \"Pre-law\",\n    \"42\": \"Psychology\",\n    \"43\": \"Public Service and Political Science\",\n    \"44\": \"Science\",\n    \"45\": \"Sports, Tourism and Recreation\",\n    \"46\": \"Sustainability\",\n    \"72\": \"Fashion\",\n    \"73\": \"Film and Media\",\n    \"76\": \"Global Management and Leadership\"\n  }\n}",
    ];

    $form['calendar_campuses'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Master campuses JSON'),
      '#description' => $this->t('JSON map of campus machine keys to display labels. These campus keys are used by calendar presets allowed_campuses/default_campus. Example: {"losan":"ASU California Center in downtown L.A. campus","downtown_phx":"Downtown Phoenix","poly":"Polytechnic","tempe":"Tempe","west":"West Valley"}'),
      '#default_value' => $config->get('calendar_campuses') ? json_encode($config->get('calendar_campuses'), JSON_PRETTY_PRINT) : "{\n  \"losan\": \"ASU California Center in downtown L.A. campus\",\n  \"downtown_phx\": \"Downtown Phoenix\",\n  \"poly\": \"Polytechnic\",\n  \"tempe\": \"Tempe\",\n  \"west\": \"West Valley\"\n}",
    ];

    $form['calendar_presets'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Page presets JSON'),
      '#description' => $this->t('JSON map of route keys to calendar filter presets. Use "all" to allow all interests or campuses. For allowed_interests, use "all" or an object with grad and ugrad arrays. Campus values must use keys from Master campuses JSON. Example: {"schedule":{"allowed_interests":"all","allowed_campuses":"all"},"asuinla":{"allowed_interests":{"grad":["arts","stem"],"ugrad":["72","73"]},"allowed_campuses":["losan"],"default_campus":"losan","lock_campus":true}}'),
      '#default_value' => $config->get('calendar_presets') ? json_encode($config->get('calendar_presets'), JSON_PRETTY_PRINT) : "{\n  \"schedule\": {\n    \"allowed_interests\": \"all\",\n    \"allowed_campuses\": \"all\"\n  },\n  \"asuinla\": {\n    \"allowed_interests\": {\n      \"grad\": [\"arts\", \"business\", \"communication_media\", \"computing_mathematics\", \"stem\"],\n      \"ugrad\": [\"72\", \"73\"]\n    },\n    \"allowed_campuses\": [\"losan\"],\n    \"default_campus\": \"losan\",\n    \"lock_campus\": true\n  }\n}",
    ];

    // Added on 6/1/2026.
    $form['default_conf_email_nid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default confirmation email template nid'),
      '#default_value' => $config->get('default_conf_email_nid') ?? '283539',
      '#description' => $this->t('Node ID of the default Conf email master content node. Used when attendee_conf_email does not have field_conf_email_nid. In other words, this will be used if no conf_email_nid is provided in the preset. For example, 283539.'),
    ];

    // ============================================================
    // Barrett descriptions config. Added on 3/11/2026.
    // ============================================================

    $form['heading5'] = [
      '#markup' => "<h3>Barrett descriptions</h3>",
    ];

    $form['barrett_descriptions'] = [
      '#type' => 'details',
      '#title' => $this->t('Barrett Descriptions'),
      '#open' => TRUE,
    ];

    $form['barrett_descriptions']['barrett_top_level'] = [
      '#type' => 'details',
      '#title' => $this->t('Top-level Barrett tour descriptions'),
      '#open' => TRUE,
    ];

    $form['barrett_descriptions']['barrett_top_level']['barrett_top_tempe'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Tempe'),
      '#default_value' => $config->get('barrett_top_tempe') ?? '',
    ];

    $form['barrett_descriptions']['barrett_top_level']['barrett_top_dpc'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Phoenix Downtown'),
      '#default_value' => $config->get('barrett_top_dpc') ?? '',
    ];

    $form['barrett_descriptions']['barrett_top_level']['barrett_top_west'] = [
      '#type' => 'textarea',
      '#title' => $this->t('West Valley'),
      '#default_value' => $config->get('barrett_top_west') ?? '',
    ];

    $form['barrett_descriptions']['barrett_top_level']['barrett_top_poly'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Polytechnic'),
      '#default_value' => $config->get('barrett_top_poly') ?? '',
    ];

    $form['barrett_descriptions']['barrett_nested'] = [
      '#type' => 'details',
      '#title' => $this->t('Nested Barrett tour descriptions (under Experience ASU)'),
      '#open' => TRUE,
    ];

    $form['barrett_descriptions']['barrett_nested']['barrett_nested_tempe'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Tempe'),
      '#default_value' => $config->get('barrett_nested_tempe') ?? '',
    ];

    $form['barrett_descriptions']['barrett_nested']['barrett_nested_dpc'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Phoenix Downtown'),
      '#default_value' => $config->get('barrett_nested_dpc') ?? '',
    ];

    $form['barrett_descriptions']['barrett_nested']['barrett_nested_west'] = [
      '#type' => 'textarea',
      '#title' => $this->t('West Valley'),
      '#default_value' => $config->get('barrett_nested_west') ?? '',
    ];

    $form['barrett_descriptions']['barrett_nested']['barrett_nested_poly'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Polytechnic'),
      '#default_value' => $config->get('barrett_nested_poly') ?? '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // ---- Parse calendar paths ----
    // Get textarea as string
    $raw_paths = $form_state->getValue('calendar_paths');

    // Convert to array: split by line, trim, and remove empties.
    $paths_array = [];
    if (is_string($raw_paths)) {
      $normalized = str_replace(["\r\n", "\r"], "\n", $raw_paths);
      $lines = explode("\n", $normalized);
      $paths_array = array_values(array_filter(array_map('trim', $lines)));
    }

    // ---- Parse Form IDs ---- (1/16/2026)
    // form_ids_expasu_and_other
    $raw_form_ids = $form_state->getValue('form_ids_expasu_and_other');

    $form_ids_array = [];
    if (is_string($raw_form_ids)) {
      $normalized = str_replace(["\r\n", "\r"], "\n", $raw_form_ids);
      $lines = explode("\n", $normalized);
      $form_ids_array = array_values(array_filter(array_map('trim', $lines)));
    }

    // Calendar filter config. Added on 5/11/2026.
    $calendar_interests = json_decode($form_state->getValue('calendar_interests'), TRUE) ?: [];
    $calendar_campuses = json_decode($form_state->getValue('calendar_campuses'), TRUE) ?: [];
    $calendar_presets = json_decode($form_state->getValue('calendar_presets'), TRUE) ?: [];

    $this->config('asuaec_visit_revamp.settings')
      // Added on 4/17/2026.
      ->set('sitename', $form_state->getValue('sitename'))
      ->set('proddomain', $form_state->getValue('proddomain'))

      ->set('post_url_prod', $form_state->getValue('post_url_prod'))
      ->set('post_url_dev', $form_state->getValue('post_url_dev'))
      ->set('max_month', $form_state->getValue('max_month'))
      ->set('calendar_paths', $paths_array)
      ->set('form_ids_expasu_and_other', $form_ids_array)

      // Barrett descriptions. Added on 3/11/2026.
      ->set('barrett_top_tempe', $form_state->getValue('barrett_top_tempe'))
      ->set('barrett_top_dpc', $form_state->getValue('barrett_top_dpc'))
      ->set('barrett_top_west', $form_state->getValue('barrett_top_west'))
      ->set('barrett_top_poly', $form_state->getValue('barrett_top_poly'))
      ->set('barrett_nested_tempe', $form_state->getValue('barrett_nested_tempe'))
      ->set('barrett_nested_dpc', $form_state->getValue('barrett_nested_dpc'))
      ->set('barrett_nested_west', $form_state->getValue('barrett_nested_west'))
      ->set('barrett_nested_poly', $form_state->getValue('barrett_nested_poly'))

      // Calendar filters. Added on 5/11/2026.
      ->set('calendar_interests', $calendar_interests)
      ->set('calendar_campuses', $calendar_campuses)
      ->set('calendar_presets', $calendar_presets)

      // Added on 6/1/2026.
      ->set('default_conf_email_nid', $form_state->getValue('default_conf_email_nid'))

      ->save();

    parent::submitForm($form, $form_state);
  }

}
