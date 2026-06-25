<?php

// Configure unique database per multisite. The root user can access
// or create any database in DDEV.
$databases['default']['default'] = [
  'database' => $_ENV['SYS_AID'],
  'username' => 'root',
  'password' => 'root',
  'host' => 'db',
  'port' => '3306',
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

// Set public files path per multisite within the default files directory.
$settings['file_public_path'] = "sites/{$_ENV['SYS_AID']}/files";

// Set temporary files path per multisite
if (!is_dir(dirname(DRUPAL_ROOT) . "/tmp/{$_ENV['SYS_AID']}") && !mkdir($concurrentDirectory = dirname(DRUPAL_ROOT) . "/tmp/{$_ENV['SYS_AID']}", 0777, TRUE) && !is_dir($concurrentDirectory)) {
  throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
}
$settings['file_temp_path'] = dirname(DRUPAL_ROOT) . "/tmp/{$_ENV['SYS_AID']}";

// Set private files path per multisite. This could also live within
// the directory /app/files-private.
if (!is_dir(dirname(DRUPAL_ROOT) . "/tmp/private-{$_ENV['SYS_AID']}") && !mkdir($concurrentDirectory = dirname(DRUPAL_ROOT) . "/tmp/private-{$_ENV['SYS_AID']}", 0777, TRUE) && !is_dir($concurrentDirectory)) {
  throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
}
$settings['file_private_path'] = dirname(DRUPAL_ROOT) . "/tmp/private-{$_ENV['SYS_AID']}";
