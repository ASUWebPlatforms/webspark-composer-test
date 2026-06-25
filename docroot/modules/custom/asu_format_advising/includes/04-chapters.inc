<?php
$field = $this->fieldDetailsService->getFieldDetails('field_chapter_headings');

$form['field_chapter_headings'] = [
  '#type' => 'checkbox',
  '#title' => $field['question'],
  '#description' => $field['description'],
  '#default_value' => $default_values['field_chapter_headings'] ?? NULL
];

$form['chapters'] = [
  '#type' => 'fieldset',
  '#title' => $this->t('Chapters'),
  '#prefix' => '<div id="chapters-wrapper">',
  '#suffix' => '</div>',
  '#states' => [
    'visible' => [
      ':input[name="field_chapter_headings"]' => ['checked' => TRUE],
    ],
  ],
];

$chapter_count = 1;
$formChapterCount = $form_state->get('chapter_count');
if ($formChapterCount !== null) {
    $chapter_count = $formChapterCount;
} elseif (isset($default_values['chapters']) && is_array($default_values['chapters'])) {
    $chapter_count = count($default_values['chapters']);
}

// Add the hidden field to the form
$form['chapter_count'] = [
  '#type' => 'hidden',
  '#value' => $chapter_count, // Assign the calculated count
];

for ($i = 1; $i <= $chapter_count; $i++) {
  $form['chapters']["chapter_$i"] = [
    '#type' => 'textfield',
    '#title' => $this->t('Chapter @num', ['@num' => $i]),
    '#attributes' => [
      'class' => ['mb-2']
    ],
    '#default_value' => $default_values['chapters']["chapter_$i"] ?? NULL
  ];
}

$form['chapters']['add_one'] = [
  '#name' => 'chapter_add_one',
  '#type' => 'submit',
  '#value' => $this->t('Add one'),
  '#submit' => ['::addOne'],
  '#attributes' => [
    'class' => ['mt-3 bg-gold text-dark border-0']
  ],
  '#ajax' => [
    'callback' => '::fieldsetAjaxCallback',
    'wrapper' => 'chapters-wrapper',
  ],
];

if ($chapter_count > 1) {
  $form['chapters']['remove_one'] = [
    '#name' => 'chapter_remove_one',
    '#type' => 'submit',
    '#value' => $this->t('Remove last'),
    '#submit' => ['::removeOne'],
    '#attributes' => [
      'class' => ['mt-3 bg-gold text-dark border-0']
    ],
    '#ajax' => [
      'callback' => '::fieldsetAjaxCallback',
      'wrapper' => 'chapters-wrapper',
    ],
  ];
}