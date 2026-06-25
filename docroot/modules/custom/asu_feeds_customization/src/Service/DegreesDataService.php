<?php

namespace Drupal\asu_feeds_customization\Service;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\DatabaseExceptionWrapper;

class DegreesDataService {
  protected $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Insert or update a degree record.
   *
   * @param array $record
   *   The record to insert or update.
   */
  public function degreeInsertOrUpdate(array $record) {
	  $maxRetries = 5;
      $attempt = 0;
	  $existing = $this->connection->select('asu_academic_plans', 'm')
				->fields('m', ['acad_plan_code'])
				->condition('acad_plan_code', $record['acad_plan_code'])
				->execute()
				->fetchAssoc();

	 if ($existing) {
				// Update the existing record
				$this->connection->update('asu_academic_plans')
				  ->fields(['acad_plan_value' => $record['acad_plan_value']])
				  ->condition('acad_plan_code', $record['acad_plan_code'])
				  ->execute();
	 } else {
				// Insert a new record
				$this->connection->insert('asu_academic_plans')
				  ->fields($record)
				  ->execute();
	  }
		
  }	
  
}
