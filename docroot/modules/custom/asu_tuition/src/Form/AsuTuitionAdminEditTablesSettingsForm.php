<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 *
 */
class AsuTuitionAdminEditTablesSettingsForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    // $forms = array('asu_tuition_admin_tables_tabs_settings_form','asu_tuition_admin_tables_filters_settings_form');
    return 'asu_tuition_admin_table_edit_settings_form';
    // Return $forms;.
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_tuition.admin_table_edit_settings',

    ];
  }

  /**
   *
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table_name = NULL) {

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $initial_table = $path_args[6];
    $table = 'asu_tuition_' . $path_args[6];
    // ksm($table);
    // add table names.
    $entities = \Drupal::service('getEntityInfo')->getEntityInfo();
    ksort($entities);
    foreach ($entities as $t_name => $table_values) {
      $table_name = $t_name;
      $explode_table = explode('asu_tuition_', $table_name);
      $explode_table_name = $explode_table[1];
      // ksm($explode_table);
      $form['tablenames'][$table_name] = [
        '#markup' => t("<button><a href='/admin/config/content/tuition/tables/$explode_table_name'>$explode_table_name</a></button>&nbsp;&nbsp;"),

      ];

    }

    $form['markup'] = [
      '#markup' => "<p>&nbsp;</p>",

    ];

    // Build table headers.
    $fields = \Drupal::service('editTableFields')->editTableFields($table);

    $header = [];
    $options = [];
    if (count($fields)) {
      $count = 0;
      foreach ($fields as $field_name => $field) {
        $header[$field_name] = $field_name;
        $form[$field_name] = [
          '#type' => 'value',
        ];
      }
      $header['copy_link'] = 'Copy';

      // Build table rows.
      $config = \Drupal::config('asu_tuition.admin_settings');
      $row_count = $config->get('asu_tuition_edit_table_add_count_max', []);
      // ksm($row_count);
      $rows = [];

      // Get count of table data.
      $records = \Drupal::service('countRecords')->countRecords($table);
      // ksm($records);
      $test = $delta = $records + 1;
      if ($records < $row_count) {
        $delta_value = $records + 1;
      }
      else {
        $delta_value = 5;
      }
      for ($delta = $records + 1; $delta < $records + 10; $delta++) {
        // ksm($delta);
        $rows[$delta] = (object) ['id' => $delta];
      }

      // Keep track of copy link info.
      /*$first_key = key($rows);
      $previous_row_id = NULL;*/

      foreach ($fields as $field_name => $field) {
        // ksm($field_name);
        //    ksm($field);
        foreach ($rows as $key => $row) {
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
              'data-field-name' => $field_name,
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

          // Add copy data link.
          /*if ($key != $first_key) {
          $options[$row->id]['copy_link'] = array('data' => t('<a href="#" class="copy-link" data-copy-to-row-id="@row_id" data-copy-from-row-id="@previous_id">copy from &uarr;</a>', array('@row_id' => $row->id, '@previous_id' => $previous_row_id)));
          }*/
          $previous_row_id = $row->id;
        }
        // ksm($options);
      }
    }
    // ksm($table);
    // Used for processing form.
    $form['#table'] = $table;
    $form['#fields'] = $fields;

    $form[$table]['rows'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => t('The !table table is empty.', ['!table' => $table]),
    ];

    // Form actions.
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
      '#submit' => [[$this, 'submitForm']],
    ];
    // $link_url = Url::fromRoute($edit_path)->toString());
    /*$form['actions']['cancel'] = array(
    '#type' => 'markup',
    //'#markup' => t('<a href="@link">Cancel</a>', array('@link' => url($edit_path))),Url::fromRoute('block.admin_display)->toString()))
    // '#markup' => t('<a href="@link">Cancel</a>', array('@link' => \Drupal\Core\Url::fromRoute($edit_path)->toString()))
    );*/

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $op = $form_state->getValue('op');
    // $values = $form_state->getValues();
    $values = $form_state->getUserInput();
    $current_path = \Drupal::service('path.current')->getPath();
    $new_path_args = explode('/add', $current_path);
    $entity_type = $form['#table'];
    $fields = \Drupal::service('editTableFields')->editTableFields($entity_type);
    // $records = \Drupal::service('readRecords')->readRecords($entity_type);
    foreach ($fields as $field_name => $field) {
      $array_diff = array_diff($values['rows'], [0, 'new']);

      $filter_array = array_filter($array_diff);
      foreach ($filter_array as $array_key => $aaray_value) {
        $update_row[$array_key] = $array_key;
      }

      $data_values = [];
      // $op = (string) $form_state->getValue('op');
      unset($values['save']);
      unset($values['delete']);
      unset($values['form_build_id']);
      unset($values['form_token']);
      unset($values['form_id']);
      unset($values['op']);

    }
    /*  ksm($values);
    foreach($values as $key => $kvalues){
    ksm($key);
    ksm($update_row);
    if(!empty($update_row)){
    $data_values[$key] = $values[$key][$update_row];
    $data_values['id'] = $data_values['rows'];
    // unset($data_values['rows']);
    }

    } */

    $new_updated_array = array_unique($update_row);

    foreach ($new_updated_array as $urows) {

      foreach ($values as $nkey => $nkvalues) {
        $newdata_values[$nkey] = $values[$nkey][$urows];
        $newdata_values['id'] = $newdata_values['rows'];
        unset($newdata_values['rows']);
      }

      if ($op == "Save") {

        \Drupal::database()->insert($entity_type)->fields($newdata_values)->execute();
      }
    }

  }

}
