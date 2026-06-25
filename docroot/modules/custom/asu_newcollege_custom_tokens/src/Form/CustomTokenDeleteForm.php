<?php

namespace Drupal\asu_newcollege_custom_tokens\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Confirmation form for deleting a Custom Token.
 */
class CustomTokenDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the token %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t(
      'This will permanently delete the token <code>[asu_newcollege:%id]</code>. '
        . 'Any rich text content referencing it will render an empty string.',
      ['%id' => $this->entity->id()]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return Url::fromRoute('entity.asu_newcollege_custom_token.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete token');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $label = $this->entity->label();
    $this->entity->delete();
    $this->messenger()->addStatus($this->t('Token <strong>%label</strong> has been deleted.', ['%label' => $label]));
    $form_state->setRedirectUrl(Url::fromRoute('entity.asu_newcollege_custom_token.collection'));
  }

}
