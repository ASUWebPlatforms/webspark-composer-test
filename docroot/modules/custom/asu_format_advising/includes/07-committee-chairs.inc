<?php
// Field Committee Chairs
$field = $this->fieldDetailsService->getFieldDetails('field_committee_chairs');
$form['field_committee_chairs'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#options' => $field['options'],
  '#default_value' => $default_values['field_committee_chairs'] ?? NULL
];

$form['committee_label'] = [
  '#type' => 'html_tag',
  '#tag' => 'label',
  '#value' => $this->t('Who is on your committee?')
];

$form['committee_help'] = [
  '#type' => 'html_tag',
  '#tag' => 'p',
  '#value' => $this->t('Please enter your committee members.'),
  '#attributes' => ['class' => ['help-block']],
];

// Field Committee Chair Name
$field = $this->fieldDetailsService->getFieldDetails('field_committee_chair_name');
$form['field_committee_chair_name'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#default_value' => $default_values['field_committee_chair_name'] ?? ''
];

// Field Committee Chair Name
$field = $this->fieldDetailsService->getFieldDetails('field_committee_mem_first');
$form['field_committee_mem_first'] = [
  '#type' => $field['type'],
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#default_value' => $default_values['field_committee_mem_first'] ?? NULL
];

$form['members'] = [
  '#type' => 'fieldset',
  '#title' => $this->t('Members'),
  '#prefix' => '<div id="members-wrapper" class="mt-3">',
  '#suffix' => '</div>'
];

$member_count = 1;
$formMemberCount = $form_state->get('member_count');
if ($formMemberCount !== null) {
    $member_count = $formMemberCount;
} elseif (isset($default_values['members']) && is_array($default_values['members'])) {
    $member_count = count($default_values['members']);
}

// Add the hidden field to the form
$form['member_count'] = [
  '#type' => 'hidden',
  '#value' => $member_count, // Assign the calculated count
];

for ($i = 1; $i <= $member_count; $i++) {
  $form['members']["member_$i"] = [
    '#type' => 'textfield',
    '#title' => $this->t('Committee Member'),
    '#attributes' => [
      'class' => ['mb-2']
    ],
    '#default_value' => $default_values['members']["member_$i"] ?? NULL
  ];
}

$form['members']['add_one'] = [
  '#name' => 'member_add_one',
  '#type' => 'submit',
  '#value' => $this->t('Add another committee member'),
  '#submit' => ['::addOne'],
  '#attributes' => [
    'class' => ['mt-3 bg-gold text-dark border-0']
  ],
  '#ajax' => [
    'callback' => '::fieldsetAjaxCallback',
    'wrapper' => 'members-wrapper',
  ],
];

if ($member_count > 1) {
  $form['members']['remove_one'] = [
    '#name' => 'member_remove_one',
    '#type' => 'submit',
    '#value' => $this->t('Remove last'),
    '#submit' => ['::removeOne'],
    '#attributes' => [
      'class' => ['mt-3 bg-gold text-dark border-0']
    ],
    '#ajax' => [
      'callback' => '::fieldsetAjaxCallback',
      'wrapper' => 'members-wrapper',
    ],
  ];
}