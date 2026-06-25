<?php

namespace Drupal\analytics_resources\Plugin\rest\resource;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Provides a Report Resource
 * @RestResource(
 *  id = "asu_analytics_report_resource",
 *  label = @Translation("ASU Analytics Report Resource"),
 *  uri_paths = {
 *    "canonical" = "/asu_analytics_api/report/{id}",
 *    "create" = "/asu_analytics_api/report",
 *    "update" = "/asu_analytics_api/report/{id}",
 *    "delete" = "/asu_analytics_api/report/{id}"
 *  }
 * )
 */
class ReportResource extends AnalyticsResourceBase
{
  /**
   * Responds to POST requests.
   *
   * @param Request $request
   *
   * @return ModifiedResourceResponse|Response
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   * @throws EntityStorageException
   */
  public function post(Request $request): ModifiedResourceResponse|Response
  {
    $data = Json::decode($request->getContent(), true);

    // Check for duplicates
    $resource_service = Drupal::service('analytics_resources.resource_service');
    $pre_existing_report_ids = $resource_service->loadResourceIdByExternalId('report', $data['field_external_id']);
    if (!empty($pre_existing_report_ids)) {
      return new ModifiedResourceResponse([
        'message' => 'This external ID is already mapped to an existing report(s)',
        'drupal_nid' => $pre_existing_report_ids,
        'status' => Response::HTTP_PRECONDITION_FAILED
      ], Response::HTTP_PRECONDITION_FAILED);
    }

    // TODO: Consider changing these checks to use isset() or array_key_exists()
    if ($data['field_keywords']) {
      $keyword_ids = $resource_service->processKeywords($data['field_keywords']);
    }

    // We only load these, because we do not want them to be user generated
    if ($data['field_tool']) {
      $tool_ids = $resource_service->loadTermIdsByNames('tool', $data['field_tool']);
    }
    if ($data['field_unit']) {
      $unit_ids = $resource_service->loadTermIdsByNames('unit', $data['field_unit']);
    }
    if ($data['field_enterprise']) {
      $enterprise_ids = $resource_service->loadTermIdsByNames('enterprise', $data['field_enterprise']);
    }

    try {
      $report = Node::create([
        'type' => 'report',
        'title' => $data['title'] ?? null,
        'field_description' => $data['field_description'] ?? null,
        'field_embedded_url' => $data['field_embedded_url'] ?? null,
        'field_external_id' => $data['field_external_id'] ?? null,
        'field_source_system' => $data['field_source_system'] ?? null,
        'field_tool' => $tool_ids ?? null,
        'field_unit' => $unit_ids ?? null,
        'field_keywords' => $keyword_ids ?? null,
        'field_enterprise' => $enterprise_ids ?? null,
        'field_group' => $data['field_group'] ?? null,
        'field_url' => $data['field_url'] ?? null,
        'field_report_type' => $data['field_report_type'] ?? null,
        'field_src_container_id' => $data['field_src_container_id'] ?? null,
        'field_parent_id' => $data['field_parent_id'] ?? null,
        'field_parent_name' => $data['field_parent_name'] ?? null,
        'field_created_by' => $data['field_created_by'] ?? null,
        'field_contact' => [
          $data['field_contact'] ?? null
        ],
        'field_hidden_from_gsearch' => 0,
        'status' => 0,
      ]);
      $report->save();
    } catch (Throwable $t) {
      Drupal::logger('asu_analytics_api')->error($t->getMessage());
      return new ModifiedResourceResponse([
        'message' => $t->getMessage(),
        'drupal_nid' => [],
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return new ModifiedResourceResponse([
      'message' => 'Report created successfully',
      'drupal_nid' => [
        $report->id()
      ],
      'status' => Response::HTTP_CREATED
    ], Response::HTTP_CREATED);
  }

  /**
   * Responds to PATCH requests.
   *
   * @param string $id The Drupal node ID.
   * @param Request $request
   *
   * @return ModifiedResourceResponse
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   * @throws EntityStorageException
   */
  public function patch(string $id, Request $request): ModifiedResourceResponse
  {
    $data = Json::decode($request->getContent(), true);

    $resource_service = Drupal::service('analytics_resources.resource_service');
    $report_node = $resource_service->loadResourceById($id);
    if (!$report_node) {
      Drupal::logger('asu_analytics_api')->notice('Attempted to PATCH unknown report: @id', ['@id' => $id]);
      return new ModifiedResourceResponse([
        'message' => 'Report not found',
        'drupal_nid' => [],
        'status' => Response::HTTP_NOT_FOUND
      ], Response::HTTP_NOT_FOUND);
    }

    // Check for duplicates
    $pre_existing_report_ids = $resource_service->loadResourceIdByExternalId(
      'report',
      $report_node->field_external_id->value
    );
    if (count($pre_existing_report_ids) > 1) {
      return new ModifiedResourceResponse([
        'message' => 'This external ID is already mapped to an existing report(s)',
        'drupal_nid' => $pre_existing_report_ids,
        'status' => Response::HTTP_PRECONDITION_FAILED
      ], Response::HTTP_PRECONDITION_FAILED);
    }

    // Although we loop through all fields, we only update certain ones
    foreach (array_keys($data) as $field) {
      // This field is currently not being shown, but let's keep it here for future use
      if ($field == 'field_tool') {
        $new_value = $resource_service->loadTermIdsByNames('tool', $data[$field]);
        if ($new_value) {
          $report_node->set($field, $new_value);
        }
      }

      if (in_array(
        $field,
        [
          'field_url',
          'field_embedded_url',
          'field_report_type',
          'field_group'
        ]
      )) {
        $report_node->set($field, $data[$field]);
      }

      if ($field == 'field_keywords' && !empty($data['field_keywords'])) {
        $keywords = [];
        $existing_keywords = $report_node->get('field_keywords')->getValue();
        foreach ($existing_keywords as $e_keyword) {
          $keywords[] = $e_keyword['target_id'];
        }

        $new_keywords = $resource_service->processKeywords($data['field_keywords']);
        $keyword_ids = array_merge($keywords, $new_keywords);
        $keyword_ids = array_unique($keyword_ids);

        $report_node->set('field_keywords', $keyword_ids);
      }

      if ($field == 'field_contact' && !empty($data[$field])) {
        $emails = [];
        $existing_contacts = $report_node->get('field_contact')->getValue();
        foreach ($existing_contacts as $e_contact) {
          $emails[] = $e_contact['value'];
        }

        // TODO: Extract into a processEmails function
        if (!is_array($data[$field])) {
          $data[$field] = [$data[$field]];
        }

        foreach ($data[$field] as $supplied_email) {
          $emails[] = $supplied_email;
        }

        // Drupal is set to allow UP TO 5 contacts per report
        $emails = array_slice(array_unique($emails), 0, 5);
        $report_node->set($field, $emails);
      }
    }

    $violations = [];
    $node_violations = $report_node->validate();
    if (count($node_violations) > 0) {
      foreach ($node_violations as $violation) {
        $violations[] = [
          'field' => $violation->getPropertyPath(),
          'error' => $violation->getMessage()
        ];
      }
    }
    if (count($violations) > 0) {
      return new ModifiedResourceResponse([
        'message' => 'Report validation failed',
        'violations' => $violations,
        'drupal_nid' => [],
        'status' => Response::HTTP_PRECONDITION_FAILED
      ], Response::HTTP_PRECONDITION_FAILED);
    }

    $report_node->save();

    return new ModifiedResourceResponse([
      'message' => 'Report updated successfully',
      'drupal_nid' => [
        $report_node->id()
      ],
      'status' => Response::HTTP_OK
    ], Response::HTTP_OK);
  }

  /**
   * Responds to GET requests.
   *
   * @param string $id The report/external ID, not the Drupal node ID.
   *
   * @return ModifiedResourceResponse
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function get(string $id, Request $request): ModifiedResourceResponse
  {
    $resource_service = Drupal::service('analytics_resources.resource_service');
    $nids = $resource_service->loadResourceIdByExternalId('report', $id);

    return new ModifiedResourceResponse([
      'message' => 'Found the following report(s)',
      'drupal_nid' => $nids,
      'status' => Response::HTTP_OK
    ]);
  }

  /**
   * Responds to DELETE requests.
   *
   * @param string $id The node ID.
   *
   * @return ModifiedResourceResponse|ResourceResponse
   */
  public function delete(string $id, Request $request): ModifiedResourceResponse|ResourceResponse
  {
    $resource_service = Drupal::service('analytics_resources.resource_service');
    $node = $resource_service->loadResourceById($id);
    if ((!$node) || $node->bundle() !== 'report') {
      Drupal::logger('asu_analytics_api')->notice('Attempted to DELETE unknown report ID: @id', ['@id' => $id]);
      return new ModifiedResourceResponse([
        'message' => 'Report not found',
        'drupal_nid' => [],
        'status' => Response::HTTP_NOT_FOUND
      ], Response::HTTP_NOT_FOUND);
    }

    try {
      $node->delete();
    } catch (Throwable $t) {
      Drupal::logger('asu_analytics_api')->error($t->getMessage());
      return new ModifiedResourceResponse([
        'message' => $t->getMessage(),
        'drupal_nid' => [],
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return new ResourceResponse([
      'message' => 'Report deleted successfully',
      'drupal_nid' => [],
      'status' => Response::HTTP_NO_CONTENT
    ], Response::HTTP_NO_CONTENT);
  }
}
