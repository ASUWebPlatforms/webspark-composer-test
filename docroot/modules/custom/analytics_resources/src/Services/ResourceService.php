<?php

namespace Drupal\analytics_resources\Services;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Throwable;

class ResourceService
{
  /**
   * Load Resource By External ID.
   *
   * @param string $resource_type
   * @param string $external_id
   *
   * @return array
   */
  public function loadResourceIdByExternalId(string $resource_type, string $external_id): array
  {
    $query = Drupal::entityQuery('node')
      ->condition('type', $resource_type)
      ->condition('field_external_id', $external_id)
      ->accessCheck(false)
      ->execute();

    return array_values($query);
  }

  /**
   * Load a node by ID.
   *
   * @param string|int $id
   *
   * @return EntityInterface|null
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function loadResourceById(string|int $id): ?EntityInterface
  {
    return Drupal::entityTypeManager()->getStorage('node')->load($id);
  }

  /**
   * Load Report Manager ID By Email.
   *
   * @param string $email
   *
   * @return int|null
   * @throws Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadReportManagerIdByEmail(string $email): ?int
  {
    if (!$email) {
      return null;
    }

    $entity_type_manager = Drupal::entityTypeManager();
    $node_storage = $entity_type_manager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'report_manager')
      ->condition('field_email', $email)
      ->accessCheck(false)
      ->range(0, 1);

    $entity_ids = $query->execute();

    if (empty($entity_ids)) {
      return null;
    }

    return reset($entity_ids);
  }

  /**
   * Build Resource Object.
   * Converts a Drupal node into a JSON string.
   *
   * @param Node $node
   *
   * @return string
   */
  public function buildResourceObject(Node $node): string
  {
    return Drupal::service('serializer')->serialize($node, 'json');
  }

  /**
   * Get a list of term IDs.
   * If a term does not exist, it will be created.
   *
   * @param array $keywords
   *
   * @return array
   */
  public function processKeywords(array $keywords): array
  {
    $kids = [];

    foreach ($keywords as $keyword) {
      $query = Drupal::entityQuery('taxonomy_term')
        ->condition('vid', 'keyword')
        ->condition('name', $keyword)
        ->accessCheck(false)
        ->execute();
      $kid = array_values($query);

      if (empty($kid)) {
        try {
          $term = Term::create(['name' => $keyword, 'vid' => 'keyword']);
          $term->save();
          $kids[] = $term->id();
        } catch (Throwable $t) {
          Drupal::logger('asu_analytics_api')->error($t->getMessage());
        }
      } else {
        $kids[] = reset($kid);
      }
    }

    return $kids;
  }

  /**
   * Load Term IDs by Names.
   *
   * @param string $vocabulary_name
   * @param array|string $term_names
   *
   * @return array
   */
  public function loadTermIdsByNames(string $vocabulary_name, array|string $term_names): array
  {
    if (!is_array($term_names)) {
      $term_names = [$term_names];
    }

    $query = Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocabulary_name)
      ->condition('name', $term_names, 'IN')
      ->accessCheck(false)
      ->execute();

    return array_values($query);
  }

  /**
   * Build the objects for the workflow resources.
   *
   * @param int $limit Number of items to return
   * @param int $offset Starting position
   *
   * @return array
   */
  public function loadWorkflowResources(int $limit = 50, int $offset = 0): array
  {
    $query = Drupal::entityQuery("node")
      ->condition("type", "report")
      ->accessCheck(false)
      ->sort('created', 'DESC')
      ->range($offset, $limit);

    $nids = $query->execute();

    if (empty($nids)) {
      return [];
    }

    $nodes = Node::loadMultiple($nids);
    return $this->processNodes($nodes);
  }

  /**
   * Process a batch of nodes.
   *
   * @param array $nodes
   *
   * @return array
   */
  private function processNodes(array $nodes): array
  {
    $data = [];

    foreach ($nodes as $node) {
      $data[] = [
        "nid" => $node->id(),
        "uuid" => $node->uuid(),
        "status" => $node->get("status")->value,
        "title" => $node->getTitle(),
        "created" => $node->getCreatedTime(),
        "changed" => $node->getChangedTime(),
        "body" => $node->get("body")->value,
        "field_contact" => $node->get("field_contact")->value,
        "field_created_by" => $node->get("field_created_by")->value,
        "field_description" => $node->get("field_description")->value,
        "field_embedded_url" => $node->get("field_embedded_url")->value,
        "field_external_id" => $node->get("field_external_id")->value,
        "field_hidden_from_gsearch" => $node->get("field_hidden_from_gsearch")->value,
        "field_parent_id" => $node->get("field_parent_id")->value,
        "field_parent_name" => $node->get("field_parent_name")->value,
        "field_src_container_id" => $node->get("field_src_container_id")->value,
        "field_url" => $node->get("field_url")->value,
        "field_data_area" => $this->getReferencedTermTitles($node->get("field_data_area")),
        "field_enterprise" => $this->getReferencedTermTitles($node->get("field_enterprise")),
        "field_group" => $this->getReferencedGroupTitles($node->get("field_group")),
        "field_keywords" => $this->getReferencedTermTitles($node->get("field_keywords")),
        "field_level_of_detail" => $this->getReferencedTermTitles(
          $node->get("field_level_of_detail")
        ),
        "field_purpose" => $this->getReferencedTermTitles($node->get("field_purpose")),
        "field_related_resources" => $this->getReferencedNodeTitles(
          $node->get("field_related_resources")
        ),
        "field_report_type" => $this->getReferencedTermTitles(
          $node->get("field_report_type")
        ),
        "field_source_system" => $this->getReferencedTermTitles(
          $node->get("field_source_system")
        ),
        "field_subject_domain" => $this->getReferencedTermTitles(
          $node->get("field_subject_domain")
        ),
      ];
    }

    return $data;
  }

  /**
   * Provide human-readable titles for referenced terms.
   *
   * @param $field
   *
   * @return array
   */
  public function getReferencedTermTitles($field): array
  {
    $data = [];

    foreach ($field->referencedEntities() as $term) {
      $data[] = $term->getName();
    }

    return $data;
  }

  /**
   * Provide human-readable titles for referenced groups.
   *
   * @param $field
   *
   * @return array
   */
  public function getReferencedGroupTitles($field): array
  {
    $data = [];

    foreach ($field->referencedEntities() as $group) {
      $data[] = $group->label();
    }

    return $data;
  }

  /**
   * Provide human-readable titles for referenced nodes.
   *
   * @param $field
   *
   * @return array
   */
  public function getReferencedNodeTitles($field): array
  {
    $data = [];

    foreach ($field->referencedEntities() as $group) {
      $data[] = $group->label();
    }

    return $data;
  }

  /**
   * Get total count of workflow resources.
   *
   * @return int
   */
  public function getWorkflowResourcesCount(): int
  {
    $query = Drupal::entityQuery("node")
      ->condition("type", "report")
      ->accessCheck(false)
      ->count();

    return (int)$query->execute();
  }
}
