<?php

// Field Abstract
$field = $this->fieldDetailsService->getFieldDetails('field_abstract');
$form['field_abstract'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#required' => FALSE,
  '#default_value' => $default_values['field_abstract'] ?? NULL
];