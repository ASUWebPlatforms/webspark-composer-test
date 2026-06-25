<?php

namespace Drupal\asu_format_advising;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;

class FieldDetailsService {

  protected $entityFieldManager;
  protected $entityTypeManager;
  protected $entityDisplayRepository;

  protected $sameIndex = [
    'field_degree',
    'field_document_type',
    'field_approved_font',
    'field_style_guide',
    'field_template_name'
  ];

  public function __construct(
    EntityFieldManagerInterface $entityFieldManager, 
    EntityTypeManagerInterface $entityTypeManager, 
    EntityDisplayRepositoryInterface $entityDisplayRepository
  ) {
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityDisplayRepository = $entityDisplayRepository;
  }

  public function getFieldDetails($fieldName, $contentType = 'formatadvising') {
    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions('node', $contentType);

    if (isset($fieldDefinitions[$fieldName])) {
      $fieldDefinition = $fieldDefinitions[$fieldName];
      
      // Get the form display for the content type
      $formDisplay = $this->entityDisplayRepository->getFormDisplay('node', $contentType, 'default');
      
      // Get the widget type for the field
      $widgetType = '';
      if ($component = $formDisplay->getComponent($fieldName)) {
        $widgetType = $component['type'];
      }

      $description = $fieldDefinition->getDescription();
      $split_description = explode(PHP_EOL, $description);
      $description = implode('', array_slice($split_description, 1));

      $fieldDetails = [
        'question' => $split_description[0],
        'title' => $fieldDefinition->getLabel(),
        'description' => $description,
        'type' => $this->getFormApiType($widgetType), // Add widget type
        'options' => $this->getAllowedValues($fieldDefinition, $fieldName)
      ];

      return $fieldDetails;
    }

    return NULL;
  }

  private function getFormApiType($widgetType){
    $map = [
      'select2' => 'select',
      'options_select' => 'select',
      'options_buttons' => 'checkboxes',
      'string_textfield' => 'textfield',
      'string_textarea' => 'textarea'
    ];
    return !empty($map[$widgetType]) ? $map[$widgetType] : $widgetType;
  }

  /**
   * Retrieves allowed values for a field definition if applicable.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition The field definition.
   *
   * @return array Allowed values for the field, or an empty array if not applicable.
   */
  protected function getAllowedValues($fieldDefinition, $fieldName) {
    $allowedValues = [];
    // Check if this field type supports allowed values.
    if (in_array($fieldDefinition->getType(), ['list_string', 'list_integer'])) {
      $fieldStorageDefinition = $fieldDefinition->getFieldStorageDefinition();
      $allowedValues = $fieldStorageDefinition->getSetting('allowed_values');
    }
    // Additional field types with allowed values can be handled here as needed.

    // if(in_array($fieldName, $this->sameIndex)){
    //   $allowedValues = array_values($allowedValues);
    //   $allowedValues = array_combine($allowedValues, $allowedValues);
    // }

    return $allowedValues;
  }
}
