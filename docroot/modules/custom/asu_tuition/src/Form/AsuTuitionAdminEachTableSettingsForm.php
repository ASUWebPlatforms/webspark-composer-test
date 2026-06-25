<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 *
 */
class AsuTuitionAdminEachTableSettingsForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    // $forms = array('asu_tuition_admin_tables_tabs_settings_form','asu_tuition_admin_tables_filters_settings_form');
    return 'asu_tuition_admin_each_table_settings_form';
    // Return $forms;.
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    /* return [
    'asu_tuition.admin_single_table_settings',

    ];*/
    return ['asu_tuition.admin_each_table_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table_name = NULL) {
    // $config = $this->config('asu_tuition.admin_single_table_settings');
    // $form = parent::buildForm($form, $form_state);
    $config = $this->config('asu_tuition.admin_each_table_settings');

    $entities = \Drupal::service('getEntityInfo')->getEntityInfo();
    // $table = 'asu_tuition_acad_career';
    // ksm($table_name);
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $initial_table = $path_args[6];
    $table = 'asu_tuition_' . $path_args[6];
    // ksm($table);
    $limit = $_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit'] ?? 20;
    // $limit = 20;
    $form['#attached']['library'][] = 'asu_tuition/tuitionAdminPage';

    ksort($entities);
    // ksm($entities);
    unset($entities['asu_tuition_corporate_partner']);
    unset($entities['asu_tuition_corporate_partner_award']);

    foreach ($entities as $t_name => $table_values) {
      $table_name = $t_name;
      $explode_table = explode('asu_tuition_', $table_name);
      $explode_table_name = $explode_table[1];
      // ksm($explode_table);
      // ksm($explode_table_name);
      if ($path_args[6] == $explode_table_name) {
        $class = "active-admin-table-tab";
        $aclass = "is_active_link";
      }
      else {
        $class = '';
        $aclass = '';
      }
      $form['tablenames'][$table_name] = [
        '#markup' => t("<button class='$class button'><a href='/admin/config/content/tuition/tables/$explode_table_name'>$explode_table_name</a>&nbsp;&nbsp;</button>"),
      ];

    }

    $form['add_items'] = [
      '#markup' => t("<p><a href='/admin/config/content/tuition/tables/$initial_table/add'>+ Add multiple rows</a>&nbsp;&nbsp;</p>"),
    ];

    // If (($initial_table == 'fee_rate') || ($initial_table == 'fee_code') || ($initial_table == 'tuition_group')) {.
    $config = $this->config('asu_tuition.admin_settings');
    $enableQueryMode = $config->get('asu_tuition_operations_mode');
    if ($enableQueryMode) {
      $form['query_items'] = [
        '#markup' => t("<p><a href='/admin/config/content/tuition/tables/$initial_table/query'>Query $initial_table</a>&nbsp;&nbsp;</p>"),
      ];

      $form['data_input'] = [
        '#markup' => t("<p><a href='/admin/config/content/tuition/tables/$initial_table/insert'>Insert data into $initial_table</a>&nbsp;&nbsp;</p>"),
      ];
    }
    // }
    $form['table_name'] = [
      '#markup' => t("<p><strong>$path_args[6]</strong></p>"),
    ];

    // Filter form.
    $first_tab = TRUE;
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $initial_table = $path_args[6];
    $table = 'asu_tuition_' . $path_args[6];
    // dpm($table);
    $filter = \Drupal::service('buildFilterQuery')->buildFilterQuery($table);

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
    foreach ($db_tables as $f_field_name => $field) {
      // ksm($f_field_name);
      // ksm($field);
      $filter_title = "filter_" . $f_field_name;
      $filter_field_options = \Drupal::service('getFilterOptions')->getFilterOptions($table, $f_field_name);
      // ksm(array_values($filter_field_options));
      $null_array = [NULL => '- Select one -'];
      $filter_array = array_merge($null_array ?? [], $filter_field_options ?? []);

      switch ($field['asu_tuition']['filter_type']) {
        case 'textfield':
          $form['filters']['fields'][$filter_title] = [
            '#type' => 'textfield',
            '#title' => $f_field_name,
            '#default_value' => !empty($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$f_field_name]) ? $_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$f_field_name] : NULL,
          ];
          break;

        case 'select':
          // ksm($filter_array);
          $form['filters']['fields'][$filter_title] = [
            '#type' => 'select',
            '#title' => $f_field_name,
            '#options' => $filter_array,
          // '#options' => array(NULL => t('- Select one -')),
            '#default_value' => !empty($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$f_field_name]) ? $_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$f_field_name] : NULL,
          ];
          break;
      }
    }

    // Add query limit selection field.
    $form['filters']['query_limit'] = [
      '#type' => 'select',
      '#title' => t('Rows to display'),
    // '#options' => array_map(range(10, 200, 5)),
      '#default_value' => !empty($_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit']) ? $_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit'] : 20,
    ];

    // Add form actions.
    /*$form['filters']['actions'] = array(
    '#type' => 'actions',
    '#attributes' => array('class' => array('container-inline')),
    );
     */
    $form['filters']['actions']['submit1'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#submit' => [[$this, 'filter_submit']],
    ];

    if (!empty($_SESSION['asu_tuition_edit_table_filter'])) {
      $form['filters']['actions']['save'] = [
        '#type' => 'submit',
        '#value' => t('Reset'),
        '#submit' => [[$this, 'filter_submit']],
      ];
    }

    // End of filter form.
    $fields = \Drupal::service('editTableFields')->editTableFields($table);
    $header = [];
    $options = [];
    if (count($fields)) {
      $count = 0;
      foreach ($fields as $field_name => $field) {
        $header[$field_name] = [
          'data' => $field_name,
          'field' => $field_name,
        ];
        $form[$field_name] = [
          '#type' => 'value',
        ];
        if (!$count++) {
          // Set default sort column as first field.
          $header[$field_name]['sort'] = 'asc';
        }
      }

      // SELECT * FROM $table with pager and tablesort.
      $db = \Drupal::database();
      // $query = $db->select($table)->fields($table);
      $query = $db->select($table)->fields($table)->extend('Drupal\Core\Database\Query\PagerSelectExtender')->extend('Drupal\Core\Database\Query\TableSortExtender');

      // ksm($filter['where']);.
      if (!empty($filter['where'])) {
        // $query->where($filter['where'], $filter['args']);
        foreach ($filter['where'] as $key => $where_value) {
          foreach ($where_value as $wkey => $arg_val) {
            // ksm($arg_val);
            // $argument = $arg_val;.
            if (($arg_val != 0) || (!empty($arg_val))) {
              $query->condition($wkey, $arg_val);
            }

          }
        }

      }

      $results = $query->orderByHeader($header)->execute()->fetchAll();

      $results[] = (object) ['id' => 'new'];

      foreach ($fields as $field_name => $field) {
        foreach ($results as $row) {
          // ksm($row);
          $element = [
            '#value' => $row->$field_name ?? NULL,
            '#title' => $field_name,
            '#title_display' => 'invisible',
            '#id' => 'edit-' . strtr($field_name . '-' . $row->id, '_', '-'),
            '#name' => $field_name . '[' . $row->id . ']',
            '#disabled' => isset($field['asu_tuition']['editable']) ? !$field['asu_tuition']['editable'] : FALSE,
            '#attributes' => [
              'data-row-id' => $row->id,
            ],
          ];
          if (isset($field['asu_tuition']['editable']) && !$field['asu_tuition']['editable']) {
            $element['#attributes']['disabled'] = 'disabled';
          }

          switch ($field['type']) {
            case 'serial':
              $element['#type'] = 'textfield';
              $element['#size'] = 6;
              break;

            case 'text':
              $element['#type'] = 'textarea';
              $element['#cols'] = 30;
              $element['#resizable'] = TRUE;
              break;

            case 'char':
            case 'int':
            case 'float':
            case 'numeric':
              $element['#type'] = 'textfield';
              $element['#size'] = 10;
              break;

            default:
              $element['#type'] = 'textfield';
          }
          // ksm($element);
          $options[$row->id][$field_name] = ['data' => $element];
        }
        // ksm($options);
        $count++;
      }

      // }
      // Used for processing form.
      $form['#table'] = $table;
      $form['#fields'] = $fields;
      $form[$table]['rows'] = [
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $options,
        '#empty' => t('The !table table is empty.', ['!table' => $table]),

      ];

      /*$form[$table] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => t('The !table table is empty.', array('!table' => $table)),

      );*/
    }
    $form['pager'] = [
      '#type' => 'pager',
    ];

    // $form['#pager'] = ['#type' => 'pager']; // Here is the pager
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['actions']['submit_button'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 10,
      '#submit' => [[$this, 'submitFormValues']],
    ];

    $form['actions']['submit3'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete row'),
      '#weight' => 20,
      '#submit' => [[$this, 'submitFormValues']],
    // '#submit' => array('asu_tuition_edit_table_delete_confirm_form_submit'),
    ];

    // Return parent::buildForm($form, $form_state);.
    return $form;

  }

  /*
   **{@inheritdoc}
   */

  /**
   * Public function asu_tuition_edit_table_delete_confirm_form_submit(array &$form, FormStateInterface $form_state){
   *
   * }
   */
  public function filter_submit(array &$form, FormStateInterface $form_state) {
    // Filter function
    // ksm($form_state);
    $op = $form_state->getValue('op');
    $table = $form['filters']['#table'];
    $fields = \Drupal::service('editTableFields')->editTableFields($table);
    // ksm($form_state);
    switch ($op) {
      case 'Filter':
        foreach ($fields as $filter_field_name => $filter_field) {
          // ksm($filter_field_name);
          $url_values[$filter_field_name] = $form_state->getValue('filter_' . $filter_field_name);
          // ksm($form_state->getValue($filter_field_name));
          if (!empty($form_state->getValue('filter_' . $filter_field_name)) && $form_state->getValue('filter_' . $filter_field_name)) {
            $_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$filter_field_name] = $form_state->getValue('filter_' . $filter_field_name);
          }
          else {
            // Clear the field from the session when the field is no longer being used.
            unset($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'][$filter_field_name]);
          }
        }
        // ksm($url_values);
        $_SESSION['asu_tuition_edit_table_filter'][$table]['query_limit'] = $form_state->getValue('query_limit');
        // Remove asu_tuition_ from table name.
        $part_of_table = explode('asu_tuition_', $table);
        $table_arr = ['table_name' => $part_of_table[1]];
        $new_url_query = array_merge($table_arr, $url_values);
        $url1 = Url::fromRoute('asu_tuition.admin_table_tab_settings')->setRouteParameters($new_url_query);
        // $form_state->setRedirectUrl($url1);
        break;

      case 'Reset':
        // $_SESSION['asu_tuition_edit_table_filter'][$table] = array();
        unset($_SESSION['asu_tuition_edit_table_filter'][$table]);

        break;
    }
    // ksm($_SESSION['asu_tuition_edit_table_filter']);
    // end of filter function.
  }

  /**
   *
   */
  public function submitFormValues(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $op = $form_state->getValue('op');
    // ksm($op);
    // ksm($form_state->getValues());
    $values = $form_state->getUserInput();
    // ksm($values);
    $entity_type = $form['#table'];
    $fields = \Drupal::service('editTableFields')->editTableFields($entity_type);
    $connection = \Drupal::database();
    // Update records.
    foreach ($fields as $field_name => $field) {
      $array_diff = array_diff($values['rows'], [0, 'new']);

      $filter_array = array_filter($array_diff);
      // ksm($filter_array);
      foreach ($filter_array as $array_key => $aaray_value) {
        $update_row = $array_key;
        // ksm($update_row);
        $data_values = [];
        // $op = (string) $form_state->getValue('op');
        unset($values['save']);
        unset($values['delete']);
        unset($values['form_build_id']);
        unset($values['form_token']);
        unset($values['form_id']);
        unset($values['op']);
        foreach ($values as $key => $kvalues) {

          if (is_array($values[$key])) {
            // ksm($key);
            $data_values[$key] = $values[$key][$update_row];
            $data_values['id'] = $array_key;
          }
          // ksm($data_values);
          unset($data_values['rows']);
        }
        // ksm($data_values);
        if ($op == "Save") {
          \Drupal::database()->update($entity_type)->fields($data_values)->condition('id', $data_values['id'])->execute();
        }

        if ($op == "Delete row") {
          \Drupal::database()->delete($entity_type)->condition('id', $data_values['id'])->execute();
        }

      }
    }

    // Create new record.
    if (array_intersect($values['rows'], ['new'])) {

      $entity_values = [];
      // ksm($form['#fields']);.
      foreach ($form['#fields'] as $field_name => $field) {
        $entity_values[$field_name] = $values[$field_name]['new'];
      }

      $records = \Drupal::service('countRecords')->countRecords($entity_type);
      $get_max_row_id_value = $connection->query("SELECT id FROM $entity_type WHERE id = (SELECT MAX(id) FROM $entity_type)");
      $get_max_row_id = $get_max_row_id_value->fetchField();
      $entity_values['id'] = ++$get_max_row_id;
      \Drupal::database()->insert($entity_type)->fields($entity_values)->execute();

    }
    parent::submitForm($form, $form_state);
  }

}
