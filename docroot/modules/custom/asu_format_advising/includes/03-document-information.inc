<?php

        // Field Template Name
        $field = $this->fieldDetailsService->getFieldDetails('field_template_name');
        $form['field_template_name'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#options' => $field['options'],
          '#required' => TRUE,
          '#default_value' => $default_values['field_template_name'] ?? NULL
        ];

        // Field Style Guide
        $field = $this->fieldDetailsService->getFieldDetails('field_style_guide');
        $form['field_style_guide'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#options' => $field['options'],
          '#required' => TRUE,
          '#default_value' => $default_values['field_style_guide'] ?? NULL
        ];

        // Field Document Type
        $field = $this->fieldDetailsService->getFieldDetails('field_document_type');
        $form['field_document_type'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#options' => $field['options'],
          '#required' => TRUE,
          '#default_value' => $default_values['field_document_type'] ?? NULL
        ];

        // Field Approved Font
        $field = $this->fieldDetailsService->getFieldDetails('field_approved_font');
        $form['field_approved_font'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#options' => $field['options'],
          '#required' => TRUE,
          '#default_value' => $default_values['field_approved_font'] ?? NULL
        ];

        // Field Font Size
        $field = $this->fieldDetailsService->getFieldDetails('field_font_size');
        $form['field_font_size'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#options' => $field['options'],
          '#states' => [
            'visible' => [
              ':input[name="field_approved_font"]' => ['!value' => ''],
            ],
          ],
          '#required' => TRUE,
          '#default_value' => $default_values['field_font_size'] ?? NULL
        ];

        // Field First Title
        $field = $this->fieldDetailsService->getFieldDetails('field_first_title');
        $form['field_first_title'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#maxlength' => 90,
          '#attributes' => [
            'class' => ['js-textfield-character-limit'],
          ],
          '#default_value' => $default_values['field_first_title'] ?? NULL
        ];

        // Field Second Title
        $field = $this->fieldDetailsService->getFieldDetails('field_second_title');
        $form['field_second_title'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#maxlength' => 90,
          '#attributes' => [
            'class' => ['js-textfield-character-limit'],
          ],
          '#default_value' => $default_values['field_second_title'] ?? NULL
        ];

        // Field Third Title
        $field = $this->fieldDetailsService->getFieldDetails('field_third_title');
        $form['field_third_title'] = [
          '#type' => $field['type'],
          '#title' => $field['question'],
          '#description' => $field['description'],
          '#maxlength' => 90,
          '#attributes' => [
            'class' => ['js-textfield-character-limit'],
          ],
          '#default_value' => $default_values['field_third_title'] ?? NULL
        ];