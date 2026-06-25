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




class DeleteCasUserForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_user_cas';
  }

   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['total_users'] = array(
      '#type' => 'textfield',
      '#title' => t('Number of users to update:'),
      '#required' => TRUE,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#button_type' => 'primary',
    );
    return $form;
  }


 /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $role = 'cas';
    $num = $form_state->getValue('total_users');
    $users_id =[];
    $connection = \Drupal::service('database');
    $cas_user_manager = \Drupal::service('cas.user_manager');
    $ids = \Drupal::entityQuery('user')
          ->accessCheck(FALSE)
          ->condition('status', 1)
          ->range($num, $num+5000)
          ->execute();
      foreach($ids as $id) {
        $user = User::load($id);
        $email= $user->getEmail();
        $parts = explode("@", $email);
        $query = $connection->select('authmap', 'n');
        $query->condition('authname', $parts[0]);
        $query->fields('n', ['uid']);
        $result = $query->execute();
       
        $existing_uid = $cas_user_manager->getUidForCasUsername($parts[0]);
        $exists[] = $existing_uid;
   
        if( $id != $result->fetchField()){
          $users_id[] = $id;
         
          $result = $connection->insert('authmap')
          ->fields([
            'uid' => $id,
            'provider' => 'cas',
            'authname' => $parts[0],
            'data' => $parts[0].$user->getEmail(),
          ])
          ->execute();
  
        }
       


     }
     print_r( $users_id); exit();

    
     
    }




}