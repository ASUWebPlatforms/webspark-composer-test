<?php

namespace Drupal\asu_provost_aprview\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
//use Drupal\views\Plugin\views\area;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("custom_apr_views_field")
 */
class AprViewsField extends FieldPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['custom_php_field']['default'] = '';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['custom_php_field'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom php field'),
      '#default_value' => $this->options['custom_php_field'],
      '#format' => 'php',
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid the field being used in the query.
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $node = $values->_entity;
    if ($node->bundle() !== 'apr_pages') {
      return '';
    }
    // Get tid for Academic Year taxonomy term
    $tid = $values->paragraphs_item_field_data_node__field_apr_program__paragrap;
//    ksm($tid, "tid - cstest"); // From looking at /upra/academic-program-review/schedules, it seems to be working.
	  
    // Return embedded another view
//    return views_embed_view('academic_program_review_apr_colleges', 'block_1', $tid);
    return views_embed_view('academic_program_review_apr_colleges', 'block_1', $tid);
//    return views_embed_view('academic_program_review_apr_colleges', 'block_1', '24');
//    return views_embed_view('academic_program_review_apr_colleges', 'default', array('24'));    
  }

}

