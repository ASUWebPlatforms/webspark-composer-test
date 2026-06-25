<?php
/**
 * @file
 * Contains \Drupal\csv_og_upload\Form\OgForm.
 */
namespace Drupal\delete_cas_users\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use  \Drupal\user\Entity\User;




class RedirectUrlForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redirect_url_form';
  }

   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Redirect Updates'),
      '#button_type' => 'primary',
    );
    return $form;
  }


 /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
 
    $table = 'redirect';


    // $result = \Drupal::database()->select($table)
    // ->fields(array('status_code'))
    // ->execute();
    // print_r($result); exit();

    \Drupal::database()->update($table)
    ->fields(array('status_code' => '301'))
    ->execute();
    }




}