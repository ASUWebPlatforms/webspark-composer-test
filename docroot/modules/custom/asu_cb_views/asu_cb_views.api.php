<?php
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Alters the list of options to be displayed for a field.
 *
 * This hook can notably be used to change the label of the empty option.
 *
 * @param array $options
 *   The array of options for the field, as returned by
 *   \Drupal\Core\TypedData\OptionsProviderInterface::getSettableOptions(). An
 *   empty option (_none) might have been added, depending on the field
 *   properties.
 * @param array $context
 *   An associative array containing:
 *   - fieldDefinition: The field definition
 *     (\Drupal\Core\Field\FieldDefinitionInterface).
 *   - entity: The entity object the field is attached to
 *     (\Drupal\Core\Entity\EntityInterface).
 *
 * @ingroup hooks
 * @see hook_options_list()
 */

 
/*function asu_cb_views_options_list_alter(array &$options, array $context) {
   // Check if this is the field we want to change.
  if ($context['fieldDefinition']->id() == 'field_academic_plans') {
    $plans = array();
    $url = 'https://webapp4.asu.edu/myasu/wsanon/getAllActivePlans';
    $xml = simpleXML_load_file($url);

    if($xml !==  FALSE)
    {
      foreach($xml as $plan) {
        $acadPlan = (string)$plan['acadPlan'];
        $plans[$acadPlan] = $acadPlan." : ".(string)$plan;
      }
    }
    // Sort academic plans by code
    ksort($plans);
    $all_plans = array_merge(array('All' => '- Any -'), $plans);
    // Change the label of the empty option.
    $options = $all_plans;
  }
}


//load your field
   $field_plans = \Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_academic_plans');
     //subscribe new options to this field
    $field_plans->setSetting('allowed_values_function','asu_cb_views_allowed_values_plan' );
    //save configuration
    $field_plans->save();
    
function asu_cb_views_allowed_values_plan() {
   $plans = array();
    $url = 'https://webapp4.asu.edu/myasu/wsanon/getAllActivePlans';
    $xml = simpleXML_load_file($url);

    if($xml !==  FALSE)
    {
      foreach($xml as $plan) {
        $acadPlan = (string)$plan['acadPlan'];
        $plans[$acadPlan] = $acadPlan." : ".(string)$plan;
      }
    }
    // Sort academic plans by code
    ksort($plans);
    $all_plans = array_merge(array('All' => '- Any -'), $plans);
    
    
    
    return $all_plans;
 
}*/
