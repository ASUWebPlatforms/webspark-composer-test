<?php

namespace Drupal\asuaec_asulocal\Service;

use Psr\Log\LoggerInterface;

class DegreeResolver {

  public function __construct(
    protected AsuProgramsClient $client,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Load full degree data by plan code (URL param).
   */
  public function getDegreeByPlanCode(string $code): ?array {
    $code = strtolower(trim($code));

    try {
      $result = $this->client->listAllPrograms(1000, NULL);
      $items = $result['items'] ?? [];
    }
    catch (\Throwable $e) {
      $this->logger->error('Failed to load program list: @m', ['@m' => $e->getMessage()]);
      return NULL;
    }

    foreach ($items as $item) {
      $candidate = strtolower(
        $item['code'] ?? $item['plan_code'] ?? $item['program_code'] ?? ''
      );

      if ($candidate === $code && !empty($item['id'])) {
        // Fetch full record by UUID.
        try {
          return $this->client->getProgramById($item['id']);
        }
        catch (\Throwable $e) {
          $this->logger->error('Failed to load program detail for UUID @id: @m', [
            '@id' => $item['id'],
            '@m' => $e->getMessage(),
          ]);
          return NULL;
        }
      }
    }

    return NULL;
  }

}