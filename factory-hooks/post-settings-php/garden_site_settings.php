<?php

// Briteverify key set for diff envs.
if (isset($GLOBALS['gardens_site_settings']['flags']['briteverify_key'])) {
$config['asuaec_rfib2.settings']['briteverify_key_prod'] = $GLOBALS['gardens_site_settings']['flags']['briteverify_key'];
$config['asuaec_rfib2.settings']['briteverify_key_dev'] = $GLOBALS['gardens_site_settings']['flags']['briteverify_key'];
}
