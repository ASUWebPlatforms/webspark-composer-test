<?php

/**
 * @file
 * SimpleSamlPhp Acquia Configuration.
 *
 * This file was last modified on Nov 4, 2015.
 *
 * All custom changes below. Modify as needed.
 */

/**
 * Defines Acquia account specific options in $ah_options keys.
 *
 *   - 'database_name': Should be the Acquia Cloud workflow database name which
 *     will store SAML session information.set
 *     You can use any database that you have defined in your workflow.
 *     Use the database "role" without the stage ("dev", "stage", "test", etc.)
 *   - 'session_store': Define the session storage service to use in each
 *     Acquia environment ("memcache" or "database").
 */
$ah_options = [
  'database_name' => 'mydatabasename',
  'session_store' => [
    'prod' => 'database',
    'test' => 'database',
    'dev'  => 'database',
  ],
];

// If we are on ACSF but not in a Drupal request, load sites.php file
// to get $GLOBALS['gardens_site_settings']
if (!isset($GLOBALS['gardens_site_settings']) && file_exists($sites_file = '/var/www/html/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/docroot/sites/sites.php')) {
    require_once $sites_file;
}
if (empty($GLOBALS['gardens_site_settings'])) {
  $GLOBALS['gardens_site_settings'] = [
    "site" => $_ENV['AH_SITE_GROUP'],
    "env" => $_ENV['AH_SITE_ENVIRONMENT'],
    "conf" => [
      "gardens_site_id" => 471,
      "gardens_db_name" => "litvpz471",
      "acsf_site_id" => 471,
      "acsf_db_name" => "litvpz471",
    ],
  ];
}

$meta_path = '/var/www/html/'
  . (getenv('IS_DDEV_PROJECT') ? '' : "{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/")
  . 'private/simplesamlphp';
$config['metadatadir'] = $meta_path . '/metadata';
$certs_path = getenv('IS_DDEV_PROJECT') ? $meta_path : "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/nobackup/" . $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'];
$config['certdir'] = file_exists($certs_path . '/certs') ? $certs_path . '/certs' : $meta_path . '/certs';

// If we are on ACSF set database name dynamically
if (isset($GLOBALS['gardens_site_settings'])) {
    $ah_options['database_name'] = $GLOBALS['gardens_site_settings']['conf']['gardens_db_name'];
}

// Set some security and other configs that are set above, however we
// overwrite them here to keep all changes in one area.
$config['technicalcontact_name'] = "Acquia Support";
$config['technicalcontact_email'] = "support@acquia.com";

// Change these for your installation.
$config['secretsalt'] = 'Z8F3X5CEMh3M3yyNuW1V5uChLO4IcxyS';
$config['auth.adminpassword'] = 'gEyqtR8eN8PwFVVtIVZvxlBJA1QXj7FL';


$config['admin.protectindexpage'] = TRUE;
// Prevent Varnish from interfering with SimpleSAMLphp.
// SSL terminated at the ELB/balancer so we correctly set the SERVER_PORT
// and HTTPS for SimpleSAMLphp baseurl configuration.
$protocol = 'http://';
$port = ':80';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $_SERVER['SERVER_PORT'] = 443;
  $_SERVER['HTTPS'] = 'true';
  $protocol = 'https://';
  $port = ':' . $_SERVER['SERVER_PORT'];
}
elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 'true')) {
  $protocol = 'https://';
  $port = ':443';
}


/**
 * Multi-site installs.
 *
 * Support multi-site installations at different base URLs.
 */
$config['baseurlpath'] = $protocol . $_SERVER['SERVER_NAME'] . $port . '/simplesaml/';

/**
 * Cookies No Cache.
 *
 * Allow users to be automatically logged in if they signed in via the same
 * SAML provider on another site.
 *
 * Warning: This has performance implications for anonymous users.
 *
 * @link https://docs.acquia.com/articles/using-simplesamlphp-acquia-cloud-site
 */
# setcookie('NO_CACHE', '1');

if (!getenv('AH_SITE_ENVIRONMENT')) {
  // Add your local configuration here.
  $db_cred = [
    'db_url_ha' => ['db' => 'db'],
    'port' => 3306,
    'name' => 'db',
    'user' => 'db',
    'pass' => 'db',
  ];
  $creds = !empty($db_cred) ? $db_cred : db_info($database_name);
  $config['store.type'] = 'sql';
  $config['store.sql.dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', array_key_first($creds['db_url_ha']), $creds['port'], $creds['name']);
  $config['store.sql.username'] = $creds['user'];
  $config['store.sql.password'] = $creds['pass'];
  $config['store.sql.prefix'] = 'simplesaml';
}
else {
  $ah_options['env'] = getenv('AH_SITE_ENVIRONMENT');

  // Default to database session storage.
  if (!isset($ah_options['session_store'][$ah_options['env']])) {
    $ah_options['session_store'][$ah_options['env']] = 'database';
  }

  $config = acquia_logging_config($config);
  $config = acquia_session_store_config($config, $ah_options);

  // Allow changes in server side configuration files on Acquia cloud.
  $acquia_config_folder = '/mnt/gfs/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'];
  if (file_exists("{$acquia_config_folder}/config/saml/config.php")) {
    require("{$acquia_config_folder}/config/saml/config.php");
  }
}

/**
 * Get session storage configuration defined by Acquia.
 *
 * @param array $config
 *   Current configuration.
 * @param array $ah_options
 *   Acquia account specific options.
 *
 * @return array
 *   Updated configuration.
 */
function acquia_session_store_config(array $config, array $ah_options) {
  if ($ah_options['session_store'][$ah_options['env']] == 'memcache') {
    $config = mc_session_store($config);
  }
  elseif ($ah_options['session_store'][$ah_options['env']] == 'database') {
    $config = sql_session_store($config, $ah_options['database_name']);
  }

  return $config;
}

/**
 * Get logging configuration defined by Acquia.
 *
 * @param array $config
 *   Current configuration.
 *
 * @return array
 *   Updated configuration.
 */
function acquia_logging_config(array $config) {
  $config['logging.handler'] = 'file';
  $config['loggingdir'] = dirname(getenv('ACQUIA_HOSTING_DRUPAL_LOG'));
  $config['logging.logfile'] = 'simplesamlphp-' . date('Ymd') . '.log';

  return $config;
}

/**
 * Get memcache session storage config.
 *
 * @param array $config
 *   Current configuration.
 *
 * @return array
 *   Updated configuration.
 */
function mc_session_store(array $config) {
  $config['store.type'] = 'memcache';
  $config['memcache_store.servers'] = mc_info();
  $config['memcache_store.prefix'] = $ah_options['database_name'];

  return $config;
}

/**
 * Get memcache information.
 *
 * @return array
 *   Memcache server pool information.
 */
function mc_info() {
  $creds_json = file_get_contents('/mnt/gfs/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/files-private/sites.json');
  $creds = json_decode($creds_json, TRUE);
  $mc_server = [];
  $mc_pool = [];
  foreach ($creds['memcached_servers'] as $fqdn) {
    $mc_server['hostname'] = preg_replace('/:.*?$/', '', $fqdn);
    array_push($mc_pool, $mc_server);
  }

  return [$mc_pool];
}

/**
 * Set SQL database session storage.
 *
 * @param array $config
 *   Current configuration.
 * @param string $database_name
 *   The name of a MySQL database.
 *
 * @return array
 *   Updated configuration.
 */
function sql_session_store(array $config, $database_name) {
  $drupal_version = match (true) {
    version_compare(\Drupal::VERSION, '11', '>=') => 11,
    version_compare(\Drupal::VERSION, '10', '>=') && version_compare(\Drupal::VERSION, '11', '<') => 10,
    version_compare(\Drupal::VERSION, '9', '>=') && version_compare(\Drupal::VERSION, '10', '<') => 9,
    default => 8,
  };
  $site_settings = !empty($GLOBALS['gardens_site_settings'])
    ? $GLOBALS['gardens_site_settings']
    : ['site' => '', 'env' => '', 'conf' => ['acsf_db_name' => '']];
  $_acsf_include_file = "/var/www/site-php/{$site_settings['site']}.{$site_settings['env']}/D{$drupal_version}-{$site_settings['env']}-{$site_settings['conf']['acsf_db_name']}-settings.inc";
  if (file_exists($_acsf_include_file)) {
    $app_root = '/var/www/html' . $site_settings['site'] . '.' . $site_settings['env'] . '/docroot';
    $site_path = 'sites/g/files/' . $site_settings['conf']['acsf_db_name'];
    include $_acsf_include_file;
  }
  $db_cred = $conf['acquia_hosting_site_info']['db'] ?? [];
  $creds = !empty($db_cred) ? $db_cred : db_info($database_name);
  $config['store.type'] = 'sql';
  $config['store.sql.dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', array_key_first($creds['db_url_ha']), $creds['port'], $creds['name']);
  $config['store.sql.username'] = $creds['user'];
  $config['store.sql.password'] = $creds['pass'];
  $config['store.sql.prefix'] = 'simplesaml';

  return $config;
}

/**
 * Get SQL database information.
 *
 * @param string $db_name
 *   The name of a MySQL database.
 *
 * @return array
 *   Database information.
 */
function db_info($db_name) {
  $creds_json = file_get_contents('/mnt/gfs/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/files-private/sites.json');
  $databases = json_decode($creds_json, TRUE);
  $db = $databases['databases'][$db_name];
  $db['host'] = ($host = ah_db_current_host($db['db_cluster_id'])) ? $host : key($db['db_url_ha']);

  return $db;
}

/**
 * Get the SQL database current host.
 *
 * @param string $db_cluster_id
 *   The MySQL database cluster id.
 *
 * @return string
 *   The database host to use if found else empty.
 */
function ah_db_current_host($db_cluster_id) {
  require_once '/usr/share/php/Net/DNS2_wrapper.php';
  try {
    $resolver = new \Net_DNS2_Resolver([
      'nameservers' => [
        '127.0.0.1',
        'dns-master',
      ],
    ]);
    $response = $resolver->query("cluster-{$db_cluster_id}.mysql", 'CNAME');
    $cached_id = $response->answer[0]->cname;
  }
  catch (\Net_DNS2_Exception $e) {
    $cached_id = '';
  }

  return $cached_id;
}
