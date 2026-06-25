<?php

// Field Document Sections
$field = $this->fieldDetailsService->getFieldDetails('field_document_sections');
$form['field_document_sections'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#options' => $field['options'],
  '#default_value' => $default_values['field_document_sections'] ?? []
];