<?php

namespace Drupal\asu_edu_components\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;

/**
 * Provides a ASU.edu Components form.
 */
class FindMyDegreeProgramForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asu_edu_components_find_my_degree_program';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Location - added June 2022 per WWWCMS-39
    $form['location'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      // prefix added Aug 2022 per WWWCMS-39
      '#field_prefix' => $this->t('I would like to take most or all classes'),
      '#options' => [
        'inperson' => $this->t('In person'),
        'online' => $this->t('Online'),
      ],
      'inperson' => [
        '#attributes' => $this->get_options_attributes('radio button','select a location','in-person'),
      ],
      'online' => [
        '#attributes' => $this->get_options_attributes('radio button','select a location','online'),
      ],
      '#default_value' => 'inperson',
    ];

    // Keyword search box for in-person
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search by keyword'),
      '#size' => 60,
      '#maxlength' => 128,
      '#field_suffix' => $this->t('<div class="key-or-int"><strong>or</strong></div>'),
      // '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Enter keywords'),
        'class' => [
          'search-it',
        ],
        'data-ga-rankings-carousel-form-event' => 'search',
        'data-ga-rankings-carousel-form-action' => 'type',
        'data-ga-rankings-carousel-form-name' => 'onenter',
        'data-ga-rankings-carousel-form-type' => 'keyword search',
        'data-ga-rankings-carousel-form-region' => 'main content',
        'data-ga-rankings-carousel-form-section' => 'find my degree program',
      ],
      '#states' => [
        'invisible' => [
          ':input[name="location"]' => ['value' => 'online'],
        ],
        'disabled' => [
          ':input[name="interestarea"]' => ['!value' => '_none'],
        ],
      ],
    ];

    // Search for in-person degrees by interest area
    $form['interestarea'] = [
      '#type' => 'select',
      '#title' => $this->t('Search by interest area'),
      // '#required' => TRUE,
      '#options' => $this->get_options_inperson(),
      '#attributes' => [
        'class' => [
          'form-control',
        ],
        'data-ga-rankings-carousel-form-event' => 'select',
        'data-ga-rankings-carousel-form-action' => 'click',
        'data-ga-rankings-carousel-form-name' => 'onclick',
        'data-ga-rankings-carousel-form-type' => 'interest area',
        'data-ga-rankings-carousel-form-region' => 'main content',
        'data-ga-rankings-carousel-form-section' =>'find my degree program',
      ],
      '#states' => [
        'invisible' => [
          ':input[name="location"]' => ['value' => 'online'],
        ],
      ],
      '#default_value' => '_none',
    ];

    // Search for online degrees by interest area
    $form['interestarea_online'] = [
      '#type' => 'select',
      '#title' => $this->t('Search by interest area (Online)'),
      // '#required' => TRUE,
      '#options' => $this->get_options_online(),
      '#attributes' => [
        'class' => [
          'form-control',
        ],
        'data-ga-rankings-carousel-form-event' => 'select',
        'data-ga-rankings-carousel-form-action' => 'click',
        'data-ga-rankings-carousel-form-name' => 'onclick',
        'data-ga-rankings-carousel-form-type' => 'interest area',
        'data-ga-rankings-carousel-form-region' => 'main content',
        'data-ga-rankings-carousel-form-section' =>'find my degree program',
      ],
      '#states' => [
        'invisible' => [
          ':input[name="location"]' => ['value' => 'inperson'],
        ],
        'disabled' => [
          ':input[name="location"]' => ['value' => 'inperson'],
        ],
      ],
    ];

    // undergrad or grad
    $form['standing'] = [
      '#type' => 'radios',
      '#title' => $this->t('Degree type'),
      '#options' => [
        'undergrad' => $this->t('Undergraduate'),
        'graduate' => $this->t('Graduate'),
      ],
      'undergrad' => [
        '#attributes' => $this->get_options_attributes('checkbox','find my degree program ^ degree type','undergraduate'),
      ],
      'graduate' => [
        '#attributes' => $this->get_options_attributes('checkbox','find my degree program ^ degree type','graduate'),
      ],
      '#default_value' => 'undergrad',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // submit the form
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'data-ga-rankings-carousel-event' => 'link',
        'data-ga-rankings-carousel-action' => 'click',
        'data-ga-rankings-carousel-name' => 'onclick',
        'data-ga-rankings-carousel-type' => 'internal link',
        'data-ga-rankings-carousel-region' => 'main content',
        'data-ga-rankings-carousel-section' => 'find my degree program',
        'data-ga-rankings-carousel' => 'submit',
      ],

    ];
    
    // Add reset button for better UX
    $form['actions']['reset'] = [
      '#type' => 'button',
      '#button_type' => 'reset',
      '#value' => $this->t('Reset'),
      '#attributes' => [
        'class' => [
          'degree-reset',
        ],
        'name' => 'button_reset',
        'onclick' => 'this.form.reset(); return false;',
        'data-ga-rankings-carousel-form-event' => 'link',
        'data-ga-rankings-carousel-form-action' => 'click',
        'data-ga-rankings-carousel-form-name' => 'onclick',
        'data-ga-rankings-carousel-form-type' => 'internal link',
        'data-ga-rankings-carousel-form-region' => 'main content',
        'data-ga-rankings-carousel-form-section' => 'find my degree program',
        'data-ga-rankings-carousel-form-text' => 'reset',
      ],
      '#states' => [
        'invisible' => [
          ':input[name="location"]' => ['value' => 'online'],
        ],
      ],
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /* FOR TESTING VALUES
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addStatus($key . ': ' . $value);
    }
    */

    // in person or online
    $location = $form_state->getValue('location');
    // in-person keyword
    $search = $form_state->getValue('search');
    // in-person interest
    $interest_area = $form_state->getValue('interestarea');
    // online interest
    $interest_online = $form_state->getValue('interestarea_online');
    // ugrad or grad
    $standing = $form_state->getValue('standing');

    // check if in-person or online, set up URLs

    if ($location == 'inperson') {
      $url = 'https://degrees.apps.asu.edu';
        if ($standing == 'undergrad') {
          $url = $url . '/bachelors';
            if (!empty($search)) {
              $search = preg_replace('/\x20{2,}/', '%20', $search);
              $search = Html::escape($search);
              $url = $url . '/major-list/keyword/' . $search;
            } elseif ($interest_area !== '_none') {
              $url = $url . '/major-list/interest-area/' . $interest_area;
            }
        } else {
          $url = $url . '/masters-phd';
            if (!empty($search)) {
              $search = preg_replace('/\x20{2,}/', '%20', $search);
              $url = $url . '/major-list/keyword/' . $search;
            } elseif ($interest_area !== '_none') {
              $url = $url . '/major-list/interest-area/' . $interest_area;
            }
        }
    } elseif ($location == 'online') {
      $url = 'https://asuonline.asu.edu';
        if (($standing == 'undergrad') && ($interest_online !== 'undecided')) {
          $url = $url . '/online-degree-programs/?degree=undergraduate';
            if (($interest_online !== '_none') || ($interest_online !== 'undecided')) {
              $url = $url . '&interest=' . $interest_online;
            }
        } elseif (($standing == 'undergrad') && ($interest_online == 'undecided')) {
          $url = 'https://asuonline.asu.edu/admission/undecided/';
        } else {
          $url = $url . '/online-degree-programs/?degree=graduate';
            if ($interest_online !== '_none') {
              $url = $url . '&interest=' . $interest_online;
            }
        }
    } else {
      // do nothing
    }
    $response = new TrustedRedirectResponse($url);
    $response->send();
  }

  function get_options_attributes($type, $section, $option) {
    $options_attributes = [
      'data-ga-rankings-carousel-form-event' => 'select',
      'data-ga-rankings-carousel-form-action' => 'click',
      'data-ga-rankings-carousel-form-name' => 'onclick',
      'data-ga-rankings-carousel-form-type' => $type,
      'data-ga-rankings-carousel-form-region' => 'main content',
      'data-ga-rankings-carousel-form-section' => $section,
      'data-ga-rankings-carousel-form-text' => $option,
      'data-ga-rankings-carousel-form-component' => 'find my degree program',
    ];
    return $options_attributes;
  }

  function get_options_inperson() {
    $options_inperson = [
      '_none' => $this->t('Select one...'),
      '01' => $this->t('Architecture & Construction'),
      '02' => $this->t('Arts'),
      '04' => $this->t('Business'),
      '05' => $this->t('Communication & Media'),
      '06' => $this->t('Computing & Mathematics'),
      '07' => $this->t('Education & training'),
      '08' => $this->t('Engineering & Technology'),
      '21' => $this->t('Entrepreneurship'),
      // exploratory only for undergrad
      '14' => $this->t('Exploratory'),
      '03' => $this->t('Health & Wellness'),
      '11' => $this->t('Humanities'),
      '10' => $this->t('Interdisciplinary Studies'),
      '12' => $this->t('Law, Justice & Public Service'),
      '18' => $this->t('Science'),
      '13' => $this->t('Social & Behavioral Sciences'),
      '15' => $this->t('Sustainability'),
      '20' => $this->t('STEM'),
      // added per request by Casey Ambrose and Cindi Farmer July 2022
      '14%20' => $this->t('Undecided'),
    ];
    return $options_inperson;

  }

  function get_options_online() {
    $options_online = [
      // online interest areas updated Jan 2024 per WWWCMS-112
      '_none' => $this->t('Select area of interest'),
      'all-interest-areas' => $this->t('All interest areas'),
      'business-degrees' => $this->t('Business'),
      'education-degrees' => $this->t('Education'),
      'engineering-degrees' => $this->t('Engineering'),
      'health-and-nursing' => $this->t('Health and nursing'),
      'humanities-degrees' => $this->t('Humanities and arts'),
      'law-degrees-public-service' => $this->t('Law and public service'),
      'science-degrees' => $this->t('Science'),
      'social-sciences-degrees' => $this->t('Social and behavioral sciences'),
      'technology-degrees' => $this->t('Technology'),
      // added per request by Casey Ambrose and Cindi Farmer July 2022
      'undecided' => $this->t('Undecided'),
    ];
    return $options_online;
  }

}