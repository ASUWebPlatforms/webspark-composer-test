<?php

namespace Drupal\asu_graduate_faculty\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\asu_graduate_faculty\GraduateQueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GraduateFacultyForm extends FormBase {

  protected $graduateQueryService;

  public function __construct(GraduateQueryService $graduateQueryService) {
    $this->graduateQueryService = $graduateQueryService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asu_graduate_faculty.graduate_query_service')
    );
  }

  public function getFormId() {
    return 'asu_graduate_faculty_search_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('asu_graduate_faculty.settings');

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $config->get('name_title'),
      '#description' => $config->get('name_description'),
      '#wrapper_attributes' => ['class' => ['mt-3']],
    ];

    $form['name_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
      '#submit' => ['::submitFormName'],
      '#name' => 'name_submit',
    ];

    $form['program'] = [
      '#type' => 'select',
      '#title' => $config->get('phd_program_title'),
      '#description' => $config->get('phd_program_description'),
      '#wrapper_attributes' => ['class' => ['mt-3']],
      '#options' => $this->graduateQueryService->getDegreeOptions('- Select -'),
    ];

    $form['program_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
      '#submit' => ['::submitFormProgram'],
      '#name' => 'program_submit',
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $config->get('category_title'),
      '#description' => $config->get('category_description'),
      '#wrapper_attributes' => ['class' => ['mt-3']],
      '#options' => $this->graduateQueryService->getCategoryOptions('- Select -'),
    ];

    $form['category_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
      '#submit' => ['::submitFormCategory'],
      '#name' => 'category_submit',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle form submission
  }

  public function submitFormName(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $form_state->setRedirect('asu_graduate_faculty.person', ['person' => $name]);
  }

  public function submitFormProgram(array &$form, FormStateInterface $form_state) {
    $degree = $form_state->getValue('program');
    $form_state->setRedirect('asu_graduate_faculty.degree', ['degree' => $degree]);
  }

  public function submitFormCategory(array &$form, FormStateInterface $form_state) {
    $category = $form_state->getValue('category');
    $form_state->setRedirect('asu_graduate_faculty.category', ['category' => $category]);
  }

}
