<?php

namespace Drupal\asu_newcollege_custom_tokens;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Builds the list table for Custom Token entities.
 */
class CustomTokenListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Token');
    $header['type'] = $this->t('Type');
    $header['preview'] = $this->t('Current value');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\asu_newcollege_custom_tokens\Entity\CustomToken $entity */
    $row['label'] = $entity->label();
    $row['id'] = '[asu_newcollege:' . $entity->id() . ']';
    $row['type'] = match ($entity->get('type')) {
      'date' => $this->t('Date'),
      'html' => $this->t('HTML'),
      default => $this->t('Text'),
    };
    $row['preview'] = $entity->getResolvedValue() ?: '—';
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();

    // Add token button at the top.
    $build['add_button'] = [
      '#type' => 'link',
      '#title' => $this->t('Add custom token'),
      '#url' => Url::fromRoute('entity.asu_newcollege_custom_token.add_form'),
      '#attributes' => ['class' => ['button', 'button--primary', 'button--action']],
      '#weight' => -10,
    ];

    $build['table']['#empty'] = $this->t('No custom tokens defined yet. <a href=":add">Add your first token</a>.', [
      ':add' => Url::fromRoute('entity.asu_newcollege_custom_token.add_form')->toString(),
    ]);

    // Move button above the table.
    $build['add_button']['#weight'] = -10;
    $build['table']['#weight'] = 0;

    return $build;
  }

}
