<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 *
 */
class AsuTuitionAdminTablesSettingsForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_admin_table_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_tuition.admin_table_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table = NULL) {
    $config = $this->config('asu_tuition.admin_table_settings');

    $first_tab = TRUE;
    $entities = \Drupal::service('getEntityInfo')->getEntityInfo();
    // ksm($entities);
    // ksort($entities);
    /* ksm($entities);
    foreach ($entities as $entity_type => $entity) {
    //ksm($entity);
    ksm($entity_type);
    $table = $entity_type;
    ksm($table);
    }*/

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $initial_table = $path_args[6];
    // $table = 'asu_tuition_'.$path_args[6];
    $table = 'asu_tuition_acad_career';
    ksort($entities);

    foreach ($entities as $t_name => $table_values) {
      $table_name = $t_name;
      $explode_table = explode('asu_tuition_', $table_name);
      $explode_table_name = $explode_table[1];
      // ksm($explode_table);
      $form['tablenames'][$table_name] = [
        '#markup' => t("<button class='button'><a href='/admin/config/content/tuition/tables/$explode_table_name'>$explode_table_name</a>&nbsp;&nbsp;</button>"),
      ];

    }
    // $table = 'asu_tuition_acad_career';
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
      // Extract column names from the $header array.
      $column_names = array_keys($header);

      // SELECT * FROM $table with pager and tablesort.
      $db = \Drupal::database();
      $query = $db->select($table)->fields($table);
      $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->extend('Drupal\Core\Database\Query\TableSortExtender');
      if (!empty($filter['where'])) {
        $query->where($filter['where'], $filter['args']);
      }
      // $results = $query->range($limit)->orderBy($column_names)->execute()->fetchAll();
      $results = $query->execute()->fetchAll();
      // ksm($results);
      $results[] = (object) ['id' => 'new'];
      $count = 0;
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
          // ksm($element);
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

          $options[$row->id][$field_name] = ['data' => $element];
        }
        $count++;
      }
    }

    // Used for processing form.
    $form['#table'] = $table;
    $form['#fields'] = $fields;
    $form[$table]['rows'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => t('The !table table is empty.', ['!table' => $table]),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    // '#submit' => array('asu_tuition_save_table_data_submit'),
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete'),
    // '#submit' => array('asu_tuition_edit_table_form_delete_submit'),
    ];
    static $pager_element = 0;

    return $form;
  }

  /*
   **{@inheritdoc}
   */

  /**
   * Public function asu_tuition_save_table_data_submit(array &$form, FormStateInterface $form_state){.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // parent::submitForm($form, $form_state);.
    $op = $form_state->getValue('op');
    $values = $form_state->getValues();
    $entity_type = $form['#table'];

    $fields = \Drupal::service('editTableFields')->editTableFields($entity_type);
    // dpm($fields);
    foreach ($fields as $field_name => $field) {

      $array_diff = array_diff($values['rows'], [0, 'new']);
      foreach ($array_diff as $array_key => $aaray_value) {
        $update_row = $array_key;
      }

      $data_values = [];
      $op = (string) $form_state->getValue('op');

      unset($values['save']);
      unset($values['delete']);
      unset($values['form_build_id']);
      unset($values['form_token']);
      unset($values['form_id']);
      unset($values['op']);

      foreach ($values as $key => $kvalues) {
        $data_values[$key] = $values[$key][$update_row];
        $data_values['id'] = $data_values['rows'];
        unset($data_values['rows']);
      }
    }
    $connection = \Drupal::database();
    // $database = \Drupal::database();
    if ($op == "Save") {
      \Drupal::database()->update($entity_type)->fields($data_values)->condition('id', $data_values['id'])->execute();
    }

  }

}
