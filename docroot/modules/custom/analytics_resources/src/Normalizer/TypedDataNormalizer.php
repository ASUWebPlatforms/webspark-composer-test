<?php

namespace Drupal\analytics_resources\Normalizer;

use Drupal;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\serialization\Normalizer\NormalizerBase;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\Serializer\Serializer;

/**
 * Converts typed data objects to arrays.
 */
class TypedDataNormalizer extends NormalizerBase
{
  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\TypedData\TypedDataInterface';

  /**
   * {@inheritdoc}
   * @param FieldItemList $object
   */
  public function normalize($object, $format = null, array $context = [])
  {
    $value = $object->getValue();
    $altered = false;
    $field_definition = null;
    $new_value = null;

    // Check if the object has the getFieldDefinition method before calling it
    if (method_exists($object, 'getFieldDefinition')) {
      /** @var FieldDefinitionInterface $field_definition */
      $field_definition = $object->getFieldDefinition();
    }

    if (is_array($value)) {
      $new_value = [];

      foreach ($value as $val) {
        // Fields
        if (isset($val['value'])) {
          $new_value[] = $val['value'];
          $altered = true;
        }

        // Entity Relationships
        if (isset($val['target_id']) && $field_definition) {
          $field_name = $field_definition->getName();
          /** @var Serializer $serializer */
          $serializer = Drupal::service('serializer');

          // TODO: Expand these to ensure we reference all of our custom Entity References and Taxonomy Vocabularies
          $targeted_node_fields = ["field_resource", "field_columns"];
          $targeted_taxonomy_fields = [
            "field_certified_asset",
            "field_data_area",
            "field_enterprise",
            "field_keywords",
            "field_source_system",
            "field_tool",
            "field_unit",
          ];
          if (in_array($field_name, $targeted_node_fields)) {
            // Node References
            $node = Node::load($val['target_id']);
            $node_json = $serializer->normalize($node, null);
            $new_value[] = $node_json;
          } elseif (in_array($field_name, $targeted_taxonomy_fields)) {
            // Taxonomy References
            $term = Term::load($val['target_id']);
            $term_name = $term->getName();
            $new_value[] = $term_name;
          } else {
            $new_value = $val['target_id'];
          }

          $altered = true;
        }
      }

      if (is_array($new_value) && sizeof($new_value) === 1) {
        $new_value = $new_value[0];
      }
    }

    return $altered ? $new_value : $value;
  }
}
