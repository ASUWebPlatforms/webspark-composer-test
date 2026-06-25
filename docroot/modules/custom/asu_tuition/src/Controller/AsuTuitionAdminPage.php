<?php

namespace Drupal\asu_tuition\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class AsuTuitionAdminPage extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function data_page() {
    /*$table = "asu_tuition_acad_career";
    $form['table'] = [
    '#type' => 'table',
    '#header' => $header,
    '#rows' => get_data($table),
    '#empty' => $this->t('No users found'),
    ];

    return $form;*/
    $config = $this->config('asu_tuition.admin_tab_settings');
    $entities = \Drupal::service('getEntityInfo')->getEntityInfo();
    $table_name = "asu_tuition_acad_career";
    /*ksort($entities);
    //ksm($entities);
    //ksm($table_name['fields']);
    $db_tables = \Drupal::service('editTableFields')->editTableFields($table_name);
    //ksm($db_tables);
    foreach($db_tables as $key => $fname){
    $keys[$key] = $key;
    }
    ksm($keys);*/
    $query = \Drupal::database()->select($table_name, 'tb');
    $query->fields('tb');
    $results = $query->execute()->fetchAll();
    // ksm($vars);
    // $results = get_object_vars ( $vars );
    // ksm($results);
    foreach ($results as $id_key => $object) {
      // ksm($id_key);
      $data_values[] = get_object_vars($object);
      // ksm($data_values[$id_key]);.
      $form[$id_key] = [
        '#title' => $id_key,
        '#type' => 'textfield',
        '#default_value' => $data_values[$id_key],
      ];
    }

    // $header = $options;
    $data = get_data($table_name);

    foreach ($results as $rkey => $result_header) {
      $hkeys = get_object_vars($result_header);
      $hvalues = $result_header;

      $output = [];
      foreach ($hkeys as $id_key => $value) {
        $options[$id_key] = $id_key;
        $values[$id_key] = [
          $hvalues->$id_key,
        ];

        $header = $options;
        $output[$id_key] = $hvalues;
        // ksm($values);
      }
    }

    $output1[] = [$values];

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $data_values,
    // '#empty' => t('No users found'),
    ];

    return $form;
  }

}
