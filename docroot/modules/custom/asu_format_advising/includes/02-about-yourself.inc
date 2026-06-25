<?php

// Field Full Name
$field = $this->fieldDetailsService->getFieldDetails('field_full_name');
$form['field_full_name'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#required' => TRUE,
  '#default_value' => $default_values['field_full_name'] ?? NULL
];

// Field Degree
$field = $this->fieldDetailsService->getFieldDetails('field_degree');
$form['field_degree'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#options' => $field['options'],
  '#required' => TRUE,
  '#default_value' => $default_values['field_degree'] ?? NULL
];

// Field Degree Other
$field = $this->fieldDetailsService->getFieldDetails('field_degree_other');
$form['field_degree_other'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#states' => [
    'visible' => [
      // Make this field visible only if 'Other' is selected in 'field_degree' dropdown
      ':input[name="field_degree"]' => ['value' => 'other'],
    ],
  ],
  '#default_value' => $default_values['field_degree_other'] ?? NULL
];

// Field Defense Date
$field = $this->fieldDetailsService->getFieldDetails('field_defense_date');
$form['field_defense_date'] = [
  '#type' => 'datelist',
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#date_part_order' => ['month','year'],
  '#date_year_range' => $this->getYearRange(),
  '#prefix' => '<div class="formatadvising-date">',
  '#suffix' => '</div>',
  '#default_value' => isset($default_values) && isset($default_values['field_defense_date']) ? $datetime->createFromFormat('Y-m-d', $default_values['field_defense_date']) : NULL
];

// Field Graduation Date
$field = $this->fieldDetailsService->getFieldDetails('field_graduation_date');
$form['field_graduation_date'] = [
  '#type' => 'datelist',
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#date_part_order' => ['month','year'],
  '#date_year_range' => $this->getYearRange(),
  '#prefix' => '<div class="formatadvising-date">',
  '#suffix' => '</div>',
  '#default_value' => isset($default_values) && isset($default_values['field_graduation_date']) ? $datetime->createFromFormat('Y-m-d', $default_values['field_graduation_date']) : NULL
];