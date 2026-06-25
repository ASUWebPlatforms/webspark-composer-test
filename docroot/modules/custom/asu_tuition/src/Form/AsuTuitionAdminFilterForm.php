<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;

/**
 *
 */
class AsuTuitionAdminFilterForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_admin_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_tuition.admin_filter_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table = NULL) {

    $config = $this->config('asu_tuition.admin_filter_settings');

    $first_tab = TRUE;

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $initial_table = $path_args[6];
    $table = 'asu_tuition_' . $path_args[6];

    // ksm($entities);
    // $table = 'asu_tuition_acad_career';.
    $form['filters'] = [
      '#type' => 'details',
      '#title' => t('Filters'),
      '#collapsible' => TRUE,
      '#collapsed' => empty($_SESSION['asu_tuition_edit_table_filter'][$table]),
    ];

    $form['filters']['#table'] = $table;

    $form['filters']['fields'] = [
      '#type' => 'container',
    ];

    $db_tables = \Drupal::service('editTableFields')->editTableFields($table);
    // ksm($entity);
    // ksm($db_tables);
    foreach ($db_tables as $field_name => $field) {
      // ksm($field_name);
      // ksm($field);
      $filter_field_options = \Drupal::service('getFilterOptions')->getFilterOptions($table, $field_name);
      // ksm(array_values($filter_field_options));
      $null_array = [NULL => '- Select one -'];
      // $filter_array = array_merge( $null_array,$filter_field_options);
      $filter_array = [0 => "Select one"] + $filter_field_options;

      switch ($field['asu_tuition']['filter_type']) {
        case 'textfield':
          $form['filters']['fields'][$field_name] = [
            '#type' => 'textfield',
            '#title' => $field_name,
            '#default_value' => !empty($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$field_name]) ? $_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$field_name] : NULL,
          ];
          break;

        case 'select':
          // ksm($filter_array);
          $form['filters']['fields'][$field_name] = [
            '#type' => 'select',
            '#title' => $field_name,
            '#options' => $filter_array,
          // '#options' => array(NULL => t('- Select one -')),
            '#default_value' => !empty($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$field_name]) ? $_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$field_name] : NULL,
          ];
          break;
      }
    }

    // Add query limit selection field.
    $form['filters']['query_limit'] = [
      '#type' => 'select',
      '#title' => t('Rows to display'),
    // '#options' => drupal_map_assoc(range(10, 200, 5)),
      '#default_value' => !empty($_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit']) ? $_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit'] : 20,
    ];

    // Add form actions.
    $form['filters']['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['filters']['actions']['save'] = [
      '#type' => 'submit',
      '#value' => t('Filter'),
    ];

    if (!empty($_SESSION['asu_tuition_edit_table_filter'])) {
      $form['filters']['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => t('Reset'),
      ];
    }

    // Return parent::buildForm($form, $form_state);.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $op = $form_state->getValue('op');
    $table = $form['filters']['#table'];
    $fields = \Drupal::service('editTableFields')->editTableFields($table);

    switch ($op) {
      case 'Filter':
        foreach ($fields as $field_name => $field) {
          $url_values[$field_name] = $form_state->getValue($field_name);
          if (!empty($form_state->getValue($field_name)) && $form_state->getValue($field_name)) {
            $_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$field_name] = $form_state->getValue($field_name);
          }
          else {
            // Clear the field from the session when the field is no longer being used.
            unset($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$field_name]);
          }
        }

        $_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit'] = $form_state->getValue('query_limit');
        break;

      case 'Reset':
        $_SESSION['asu_tuition_edit_table_filter'][$table] = [];
        break;
    }
    // Remove asu_tuition_ from table name.
    $part_of_table = explode('asu_tuition_', $table);
    $table_arr = ['table_name' => $part_of_table[1]];
    $new_url_query = array_merge($table_arr, $url_values);

    // $url1 = \Drupal\Core\Url::fromRoute('asu_tuition.admin_table_tab_settings')->setRouteParameters(['table_name' => 'acad_career'],$url_values);
    $url1 = Url::fromRoute('asu_tuition.admin_table_tab_settings')->setRouteParameters($new_url_query);
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $uri = $_SERVER['REQUEST_URI'];

    $full_uri = $host . $uri;
    // $url = Url::fromRoute($host.$uri.\Drupal::service('path.current')->getPath());
    $url = Url::fromUri($full_uri, $url_values);
    // ksm($url1);.
    $query = [
          // Specify a different callback route if you want to eg. add extra actions on saving a social account.
      'callback_route' => 'asu_tuition.admin_table_tab_settings',
          // Specify a different redirect route if you want to redirect somewhere other than /user after authorisation is finished.
      'redirect_route' => 'entity.node.canonical',
          // You can specify params for the redirect route - these get added as ROUTE parameters (not query)
      'redirect_params' => [
    // Redirect to node/1.
        'table_name' => $table,
      ],
    ];

    $form_state->setRedirectUrl($url1);
    // $form_state->setRedirect('asu_tuition.admin_table_tab_settings', [], ['query' => $query]);
  }

}
