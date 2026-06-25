<?php

// Execute settings.php in DDEV environments
$local_settings = __DIR__ . '/../../docroot/sites/default/settings.php';
if (getenv('IS_DDEV_PROJECT') == 'true' && is_readable($local_settings)) {
  require $local_settings;
}
