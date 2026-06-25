<?php

$settings['file_temp_path'] = "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/tmp";

if (isset($GLOBALS['gardens_site_settings']['flags']['sitename'])) {
  $sitename = $GLOBALS['gardens_site_settings']['flags']['sitename'];
  $settings['config_sync_directory'] = '../config/' . $sitename;
}

if (file_exists("/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/nobackup/" . $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] . "/secrets.settings.php")) {
  require "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/nobackup/" . $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] . "/secrets.settings.php";
}
