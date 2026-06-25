<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 *
 */
class AsuTuitionAdminAllTablesForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_admin_all_tables_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['asu_tuition.admin_all_tables'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table = NULL) {
    $config = $this->config('asu_tuition.admin_all_tables');

    $entities = \Drupal::service('getEntityInfo')->getEntityInfo();

    $table = 'asu_tuition_acad_career';
    ksort($entities);
    // \Drupal::logger('entity')->notice(print_r($entities, TRUE));
    foreach ($entities as $t_name => $table_values) {
      $table_name = $t_name;
      $explode_table = explode('asu_tuition_', $table_name);
      $explode_table_name = $explode_table[1];
      // ksm($explode_table);
      $form['tablenames'][$table_name] = [
        '#markup' => t("<button class='button'><a href='/admin/config/content/tuition/tables/$explode_table_name'>$explode_table_name</a>&nbsp;&nbsp;</button>"),
      ];

    }

    return $form;
  }

}
